# Elastic APM: PHP Agent


---

**Please note 1:** This is not an official Elastic APM agent, the PHP APM agent is a [community developed agent](https://github.com/elastic/apm-contrib#community-developed-agents).
**Please note 2:** This is a forked repository from [original repo](https://github.com/philkra/elastic-apm-php-agent). But I've modified too many things so I can not create PR to merge it back :). Anyway, thank to @philkra.

---

This is a PHP agent for Elastic.co's APM product: https://www.elastic.co/solutions/apm. **It's working on v2 APM' apis only**.

## Installation
The recommended way to install the agent is through [Composer](http://getcomposer.org).

Run the following composer command

```bash
php composer.phar require ivoglent/elastic-apm-php-agent
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

## Usage

### Initialize the Agent with minimal Config
```php
$config = [
    'name' => 'Service Name',
    'secretToken' => 'APM api token',
    'transport' => [
        'host' => 'APM server host',
        'config' => [
            'base_uri' => 'base URL to APM server',
        ],
    ],
    'framework' => [
        'name' => 'Framework Name',
        'version' => 'Framework version',
    ]
];
$contexts = [
    'user' => [
        'id' => 'USER_ID',
        'username' => 'USER_NAME',
        'email' => 'EMAIL'
    ]
];
$agent = new \PhilKra\Agent($config, $contexts);

```

### Capture Errors and Exceptions
The agent can capture all types or errors and exceptions that are implemented from the interface `Throwable` (http://php.net/manual/en/class.throwable.php).
```php
$error = $agent->factory()->newError(new \Exception());
$error->setTransaction($transaction);
$error->setParentId($transaction->getId());
$agent->register($error);
```

### Usage of transaction and spans
Addings spans (https://www.elastic.co/guide/en/apm/server/current/transactions.html#transaction-spans) is easy.
Please consult the documentation for your exact needs. Below is an example for adding a MySQL span.

```php
//Create APM agent
$agent = new \PhilKra\Agent($config, $contexts);

//Create new transaction
$transaction = $agent->factory()->newTransaction($transactionName, $transactionType);
//Start current transaction
$transaction->start();

//Create new span
$span = $agent->factory()->newSpan($spanName, $spanType, $spanAction);
$span->setTransaction($transaction);
$span->setParentId($transaction->getId());

//Start span
$span->start();

//Add sql context to span
$context = new \PhilKra\Traces\SpanContexts\SpanContext();
$context->statement = 'SQL Query String';
$trace->addContext('db', $context);


//Stop span
$span->stop();

//Register span to agent
$agent->register($span);


//Stop transaction
$transaction->stop();

//Register transaction to agent
$agent->register($transaction);

//Send all data to APM server
$agent->send();
```


## Tests
```bash
vendor/bin/phpunit
```

## Knowledgebase

### Disable Agent for CLI
In case you want to disable the agent dynamically for hybrid SAPI usage, please use the following snippet.
```php
'active' => PHP_SAPI !== 'cli'
```
In case for the Laravel APM provider:
```php
'active' => PHP_SAPI !== 'cli' && env('ELASTIC_APM_ACTIVE', false)
```
Thank you to @jblotus, (https://github.com/philkra/elastic-apm-laravel/issues/19)
