# logtrace-php

PHP client for the Logtrace API. Requires PHP ≥ 8.1.

## Install

```bash
composer require logtrace/logtrace-php
```

## Usage

```php
use Logtrace\Client;
use Logtrace\CreateEventRequest;

$client = new Client(getenv('LOGTRACE_API_KEY'));

$client->createEvent(new CreateEventRequest(
    name:      'user.signup',
    user_id:          '123',
    metadata:        ['plan' => 'pro'],
));

$client->createSession(new CreateSessionRequest(...));
$client->createAuditLog(new CreateAuditLogRequest(...));
```

## PSR-15 middleware

Automatically attaches request context (IP, method, endpoint, headers, status code) to every call made inside a handler.

```php
use Logtrace\Middleware;

$app->add(new Middleware($client));
```

Inside any handler:

```php
$rc = $request->getAttribute(Middleware::ATTRIBUTE);

$rc->createEvent(new CreateEventRequest(
    name: 'order.placed',
    // ...
));
```

## Error handling

```php
use Logtrace\LogtraceException;

try {
    $client->createEvent($req);
} catch (LogtraceException $e) {
    echo $e->statusCode; // HTTP status
    echo $e->getMessage();
}
```
