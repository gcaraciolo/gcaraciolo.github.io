---
extends: _layouts.post
section: content
title: Free geographical location and currency of website visitors
date: 2023-11-22
language: en
---

Many of the applications I have worked with wanted to know the location of it's users. Until know, my way to go to gather this information was using some geoip api wrapped by [laravel-geoip](https://github.com/Torann/laravel-geoip). It's a simple to use this lib: just setup the environment variables of the api you choose and start collecting user location based on their ips.

```php
geoip(request()->ip());
```

It's a nice solution and works fine for small websites. But it has downsides for production workloads: rate limit and latency. 

So, recently I was wondering.. [Cloudflare](https://www.cloudflare.com/en-gb/) shows the location of **every** request made to a website. Wouldn't be nice if they just provide that information straight in the request?? Indeed, they do!

I discovered the [cf-ipcountry](https://developers.cloudflare.com/fundamentals/reference/http-request-headers/#cf-ipcountry) header in the Cloudflare docs . It gives the [iso code](https://www.iso.org/iso-3166-country-codes.html) of the country where the request was made. It's really nice information, comes for free with a Cloudflare setup and has a really good accuracy (except if a request was made under VPN).

The docs also mention that if you need more geolocation data, you can just enable a feature on you domain called [managed transformations](https://developers.cloudflare.com/rules/transform/managed-transforms/configure/). It provides: `"cf-region-code", "cf-region", "cf-postal-code", "cf-iplongitude", "cf-iplatitude", "cf-ipcountry", "cf-ipcontinent", "cf-ipcity", "cf-timezone" `!
All of that with **zero latency** on your request and, I believe, a really good **accuracy**. Only currency is not provided. But currency is directly related to a country so a simple dictionary with `country iso code => currency` solves the problem. The same applies if you need the country name.


```php
<?php

namespace App;

use Spatie\LaravelData\Data;

class GuestId extends Data
{
    public string $country;
    public string $currency;

    public function __construct(
        public string $ip,
        public string $iso_code,
        public string $city,
        public string $state,
        public string $state_name,
        public string $postal_code,
        public string $lat,
        public string $lon,
        public string $timezone,
        public string $continent,
    ) {

        $this->country = $this->defineCountry();
        $this->currency = $this->defineCurrency();
    }

	public static function fromRequest(): self
    {
	    // although cloudflare always send these headers, I really like to have defaults in place.
	    // just in case..
        return self::from([
            'ip' => request()->ip(),
            'iso_code' => request()->header('cf-ipcountry', 'BR'),
            'city' => request()->header('cf-ipcity', 'São Paulo'),
            'state' => request()->header('cf-region-code', 'SP'),
            'state_name' => request()->header('cf-region', 'São Paulo'),
            'postal_code' => request()->header('cf-postal-code', '20000-000'),
            'lat' => request()->header('cf-iplatitude', '-22.8777'),
            'lon' => request()->header('cf-iplongitude', '-43.3078'),
            'timezone' => request()->header('cf-timezone', 'America/Sao_Paulo'),
            'continent' => request()->header('cf-ipcontinent', 'SA'),
        ]);
    }

	protected function defineCountry()
    {
        return config('countries.iso_code.' . strtoupper($this->iso_code), 'Brazil');
    }

    protected function defineCurrency()
    {
        return strtolower(config(
            'countries.currency.' . strtoupper($this->iso_code),
            config('stripe.foreign_location')
        ));
    }
}
```

After this solution, I no longer use geoip apis! A few of the benefits were:   
1. **No more rate limits**. If my site suffer an DDoS's, I don't loose track of my real customers.   
2. **No more extra latency**. In fact, I didn't have this before because I offload the operation in a Laravel Job.   
3. **No more inaccuracy**. Many requests failed and I loose track of the visitors location.   

There's one downside to this approach. If you have multiple domains in your application you need to enable this feature in all of them.