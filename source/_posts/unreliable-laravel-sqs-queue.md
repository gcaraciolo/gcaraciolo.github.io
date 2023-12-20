---
extends: _layouts.post
section: content
title: Unreliable Laravel SQS Queue
date: 2022-02-05
language: en
---

Recently I was trying to understanding why some Laravel Jobs weren't being executed. Looking at the failed jobs
I realized that some jobs haven't even started in the first place, which made me thought that the problem wasn't
in my code.

So I decided to create a fresh Proof of Concept (PoC) Laravel project to dig deeper, with the goal to understand how
Laravel decides when a job should be processed or not.

The PoC is simple enough. 2 Models, 1 Controller, 1 Job and 1 Command. One `Deposit` model with an `amount` column and one `Balance` model with also an `amount` column. When a request arrives at the controller, it should register a new `deposit` and dispatch a job to synchronize the `balannce`. The Job should increment that `deposit` in the balance asynchronously as this consumes more from the database due to the integrity check necessary to the operation. The goal of the command is to show infos about queue and database data.

It'll also be necessary an AWS account with an Standard SQS setup. I will assume you know how to do this.
And I recommend to put this app under Nginx to accept concurrent requests.

Ok, lets setup the environment.

# Setup

First run this commands to create a fresh Laravel project with all required dependencies.

```
$ laravel new deposits
$ cd deposits
$ composer require aws/aws-sdk-php laravel/telescope predis/predis
$ php artisan make:model Deposit -mc
$ php artisan make:model Balance -m
$ php artisan make:job SyncBalanceJob
$ php artisan make:command DataMonitorCommand
$ touch database/database.sqlite
```

Change these file contents:

_create_deposits_table.php_
```php
    public function up()
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('amount');
        });
    }
```

_create_balances_table.php_

```php
public function up()
    {
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('amount');
        });
    }
```

_routes/api.php_

```php
Route::get('deposit', [\App\Http\Controllers\DepositController::class, 'store'])->name('deposit');
```

**Disable api throttle middleware**  
_Http/Kernel.php_

```php
'api' => [
    // 'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

_DepositController.php_

```php
class DepositController extends Controller
{
    public function store()
    {
        request()->validate([
            'amount' => 'required|integer'
        ]);

        $deposit = \App\Models\Deposit::create(request()->all());

        dispatch(new \App\Jobs\SyncBalanceJob($deposit));

        return response()->json(['message' => __('Deposit confimed')]);
    }
}
```

_Deposit.php_

```php
class Deposit extends Model
{
    protected $fillable = ['amount'];
}
```

_Balance.php_

```php
class Balance extends Model
{
    protected $fillable = ['amount'];
}
```

_SyncBalanceJob.php_

```php
class SyncBalanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Deposit $deposit)
    {
    }

    public function handle()
    {
        Balance::firstOrCreate(['amount' => 0])
          ->increment('amount', $this->deposit->amount);
    }
}
```

_DataMonitorCommand.php_

```php
class DataMonitorCommand extends Command
{
    protected $signature = 'data:monitor';
    protected $description = 'Show queue and database data info';

    public function handle()
    {
        while (true) {
            $this->table([
                'queue-size',
                'balance',
                'sum deposits'
            ], [
                [
                    'queue-size' => Queue::size(),
                    'balance' => \App\Models\Balance::sum('amount'),
                    'sum deposits' => \App\Models\Deposit::sum('amount')
                ]
            ]);
            sleep(1);
        }
    }
}

```

Change this environment variables
_.env_

```
CACHE_DRIVER=redis
QUEUE_CONNECTION=sqs
REDIS_CLIENT=predis

DB_CONNECTION=sqlite

AWS_ACCESS_KEY_ID=<AWS KEY>
AWS_SECRET_ACCESS_KEY=<AWS SECRET>
AWS_DEFAULT_REGION=<AWS REGION>

SQS_PREFIX=<SQS PREFIX>
SQS_QUEUE=<QUEUE NAME>
```

Run migrations

```
$ php artisan migrate
```

# Exposing the problem

Alright.. let's see some erros! Open your terminal and split it into six views.  
4 views for laravel works

```
$ php artisan queue:work
```

1 view for data monitoring

```
$ php artisan data:monitor
```

1 view to send requests. Use this command to send 1000 requests to deposit $100 with 10 requests in parallel:

```
$ xargs -I % -P 10 curl http://sqs-queue.test/api/deposit\?amount\=100 < <(printf '%s\n' {1..1000})
```

Soon enough some jobs will start to fail and your balance will be corrupted. **What a shame!**

{{< figure src="/laravel-queue-fail.gif" alt="Multiple terminals opened" caption="Laravel jogs running in parallel" >}}


My final data was:

| queue-size | balance | sum deposits |
| ---------- | ------- | ------------ |
| 0          | 94000   | 100000       |

**A 0.06% of rating error.**

# Digging Deeper

First thing I looked at was the `laravel.log` file to see the stack trace of the error. But no errros were found.
Well, this is just that the Job failed before it even execute my code. I've used Laravel Telescope to see the error and found the message:

```
App\Jobs\SyncBalanceJob has been attempted too many times or run too long. The job may have previously timed out.
```

But how come! The job haven't even been executed. How can it be attempted too many times or even ran too long?

Let's dig deeper into Laravels framework!  
Jump to `laravel/framework/src/Illuminate/Queue/Worker.php:504`
and add a print statement just before the exception is raised, line 503

```php
    protected function markJobAsFailedIfAlreadyExceedsMaxAttempts($connectionName, $job, $maxTries)
    {
        $maxTries = ! is_null($job->maxTries()) ? $job->maxTries() : $maxTries;

        $retryUntil = $job->retryUntil();

        if ($retryUntil && Carbon::now()->getTimestamp() <= $retryUntil) {
            return;
        }

        if (! $retryUntil && ($maxTries === 0 || $job->attempts() <= $maxTries)) {
            return;
        }
        // PRINT STATEMENT HERE
        info(['attempts' => $job->attempts(), 'tries' => $maxTries]);
        $this->failJob($job, $e = $this->maxAttemptsExceededException($job));

        throw $e;
    }

```

Repeat the steps on `exposing the problem` and wait a job to fail. Then jump again to `laravel.log` and you´ll probably see something like:

```
[2022-02-05 15:42:56] local.INFO: array (
  'attempts' => 2,
  'tries' => 1,
)
```

`$job->attempts()` returned 2 and the job could only be executed once.. uh... intersting.. How Laravel decides how many times a SQS messagem was delivered?

Jump to `laravel/framework/src/Illuminate/Queue/Jobs/SqsJob.php:81`.

```php
public function attempts()
{
    return (int) $this->job['Attributes']['ApproximateReceiveCount'];
}
```

So... Laravel SQS Job uses AWS SQS messagem `ApproximateReceiveCount` to decide if a job was executed or not. What does [AWS SQS Docs](https://docs.aws.amazon.com/AWSSimpleQueueService/latest/APIReference/API_ReceiveMessage.html) says about this property?

```
- ApproximateReceiveCount – Returns the number of times a message has been received across all queues but not deleted.
```

It seems the right thing, but.. SQS itself is a service that promises [eventual consistency](https://docs.aws.amazon.com/AWSSimpleQueueService/latest/APIReference/API_GetQueueAttributes.html)! And the parameter itself says `Approximate`..

Knowing this we can't rely on this attribute. Indeed, a best practice is to make our job [idempotent](https://en.wikipedia.org/wiki/Idempotence) so it can be executed many times but always produce the same result.

# Fixing the problem

First thing to do is to tell Laravel that our job could be executed many times, using the `$tries` property of the job.

_SyncBalanceJob.php_

```php

class SyncBalanceJob implements ShouldQueue
{
    ...
    public $tries = 0;.
    ...
}
```

Next, we shall make sure that this job is idempotent. We can't just rely that it will execute only once after had failed.. the caoes theory says that if something could go wrong, in a large environment, it'll certanly go wrong.

The easiest way to do this is adding a `sync_at` column in the `Deposit` model and wrap the sync balance operation in a transaction:

_SyncBalanceJob.php_

```php
class SyncBalanceJob implements ShouldQueue
{
     public function handle()
    {
        DB::transaction(function () {
            $deposit = Deposit::lockForUpdate()->find($this->deposit->getKey());
            if (!$deposit->isSync()) {
                Balance::firstOrCreate(['amount' => 0])->increment('amount', $this->deposit->amount);

                $deposit->sync_at = now();
                $deposit->save();
            }
        });
    }
}
```

_Deposit.php_

```php
class Deposit extends Model
{
    protected $fillable = ['amount'];
    protected $dates = ['sync_at'];

    public function isSync()
    {
        return boolval($this->sync_at);
    }
}

```

_create_deposits_table.php_

```php
    public function up()
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('amount');
            $table->dateTime('sync_at')->nullable(); //new column
        });
    }
```

And now everything should work properly. :D
