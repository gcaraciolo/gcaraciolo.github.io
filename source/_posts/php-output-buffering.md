---
extends: _layouts.post
section: content
title: PHP Output Buffering
date: 2024-07-12
language: en
---
https://www.php.net/manual/en/outcontrol.output-buffering.php
	Output buffering is the buffering (temporary storage) of output before it is flushed (sent and discarded) to the browser (in a web context) or to the shell (on the command line). While output buffering is active no output is sent from the script, instead the output is stored in an internal buffer.

In computer science a buffer is just a reserved space in the computer's memory.

### PHP Buffering

If output buffering is turned off, then `echo` will send data immediately to the Browser.
<img src="/assets/img/php-output-buffering/1.png">

If output buffering is turned on, then an `echo` will send data to the output buffer before sending it to the Browser.
<img src="/assets/img/php-output-buffering/2.png">
reference: https://stackoverflow.com/a/53042023/4301651
### Use cases
**Adding headers to a response after writing the body**
Headers cannot normally be sent to the browser after data has already been sent.

```php
  
ob_start();  

echo "Hello\n";  

// --------------------------------------------------------------
// If no output buffer was used, this would cause an error, as
// http protocol/browsers doesn't accept headers after the body. 
// --------------------------------------------------------------
setcookie("cookiename", "cookiedata");  
  
ob_end_flush();
```

**Caching the result of compute/time intensive scripts**
Simple example of generating static html pages

```php
  

// --------------------------------------------------------------
// First we check if a cached version of the content exists.
// If it does, just return it, aliviating the server CPU.
//
// If the cached version does not exist, we proceed to execute the script.
// --------------------------------------------------------------
if (file_exists('cached_content.html')) {
    echo file_get_contents('cached_content.html');
} else {
    // --------------------------------------------------------------
	// While output buffering is active no output is sent from the script, 
	// instead the output is stored in an internal buffer.
	//
    // So before the script starts, we first create a buffer to capture
    // all the content that would otherwise be sent to the browser.
    // --------------------------------------------------------------
    ob_start();

    echo '<h1>Hello, world!</h1>';
    // --------------------------------------------------------------
    // We simulate a long running script by sleeping for 3 seconds.
    //
    // This can by any thing like making external API calls,
    // querying a database with a lot of data, etc.
    // --------------------------------------------------------------
    sleep(3);
    echo '<p>Your result is: 3 seconds...</p>';

    // --------------------------------------------------------------
    // After the script has finished executing, we capture the content
    // from the buffer and save it to a file.
    //
    // This is the file that we will check for existence at the start
    // of the script.
    // --------------------------------------------------------------
    $contents = ob_get_contents();
    file_put_contents('cached_content.html', $contents);

    // --------------------------------------------------------------
    // Finally, we send the content to the browser by flushing the buffer.
    // --------------------------------------------------------------
    ob_end_flush();
}
```

Caching the content of webpages is a really common thing. 
Real world examples of page cache:

- Laravel Views [initial version](https://github.com/laravel/laravel/blob/a188d62105532fcf2a2839309fb71b862d904612/system/view.php#L138)
- Laravel Caching Views: precompiles all blade view files so that they are not compiled on demand.
- RoR Russian Doll Caching: a technique to cache fragments of long running scripts


**Extracting information from functions that would otherwise produce output (e.g. phpinfo)**

```php
// --------------------------------------------------------------
// extracting information from functions that would otherwise produce output (e.g. phpinfo())
// --------------------------------------------------------------
ob_start();

// --------------------------------------------------------------
// phpinfo() is a function that outputs a large amount of information
// about the current state of PHP directly to the browser.
//
// Without output buffering, this would be displayed immediately.
// --------------------------------------------------------------
phpinfo();

$info = ob_get_contents();

ob_get_clean();

// --------------------------------------------------------------
// Here we can manipulate the content of phpinfo() output and
// send only the desired information to the browser.
// --------------------------------------------------------------
echo substr($info, 1000, 2000);
```