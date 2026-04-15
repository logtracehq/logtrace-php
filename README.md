# logtrace-sdk

PHP SDK for the [Logtrace](https://logtracehq.com) developer API.

## Install

```bash
composer require logtracehq/logtrace-sdk
```

## Usage

```php
<?php

use Logtrace\Logtrace;

$client = new Logtrace('your-api-key');

// Create an event
$client->createEvent([
    'action_name' => 'user.login',
    'username' => 'jane_doe',
    'http_method' => 'POST',
    'http_status' => '200',
    'client_ip' => '192.168.1.1',
    'client_user_agent' => 'Mozilla/5.0',
]);

// Create a session
$client->createSession([
    'login_at' => date('c'),
    'status' => 'ACTIVE',
    'username' => 'jane_doe',
]);

// Create an audit log
$client->createAuditLog([
    'action' => 'user.deleted',
    'timestamp' => date('c'),
    'username' => 'jane_doe',
    'metadata' => [
        'event' => 'deletion',
        'type' => 'user',
        'description' => 'User account was deleted',
    ],
]);
```

## Custom Base URL

```php
$client = new Logtrace('your-api-key', 'https://your-instance.com/v1/developers');
```
