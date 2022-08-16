<div align="center">
    <h1>LogSnag PHP</h1>
	<p>A PHP SDK for the LogSnag API – <a href="https://logsnag.com">logsnag.com</a></p>
</div>

## Installation

```sh
composer require roublez/logsnag-php
```

## Usage

### Initialize Client

```php
use Roublez\LogSnag\Client;

$logsnag = new Client('7f568d735724351757637b1dbf108e5');

//
// You may specify a default project and channel
$logsnag = new Client('7f568d735724351757637b1dbf108e5', 'my-project');
$logsnag = new Client('7f568d735724351757637b1dbf108e5', 'my-project', 'my-channel');
```

### Publish Event

```php

//
// At least the event name must be defined.
$logsnag->publish('Some Event');

//
// Description, icon, notify and tags can be defined as additional parameters.
$logsnag->publish('Some Event', icon: '🥳');
$logsnag->publish('Some Event', 'Description', '🥳', notify: true, [
    'user' => 123412,
    'example' => true
]);
```

> **Warning**
> The examples above only work if a default _project_ and default _channel_ are specified when instantiating. If these are not specified, an array with the corresponding data must be passed to the publish method:

```php
$logsnag->publish([
    'project' => 'awesome-saas',
    'channel' => 'monitoring',
    'event' => 'Some Event'
]);
```
_The data in this array overwrites all default values. In this case the `awesome-saas` project and `monitoring` channel will be used instead of the default project and channel._

### Publish Insight

Basically the same logic applies to the Insight API:

```php

//
// At least the title and value must be defined. The optional icon can be set as a third parameter
$logsnag->insight('Status', 'OK');
$logsnag->insight('Status', 'OK', '✅');
```

> **Warning**
> Again the _project_ will be auto-injected from the default project defined while instantiating. To override the project use the array notation:

```php
$logsnag->insight([
    'project' => 'awesome-saas',
    'title' => 'Status',
    'value' => 'OK'
]);
```

## Troubleshooting

The `event` and `insight` methods return a GuzzleHttp `ResponseInterface`. Throwing exceptions from Guzzle has been deactivated intentionally so the result can be used to retrieve the response data:

```php
$response = $logsnag->insight('Status');

$response->getStatusCode(); // 400
$response->getReasonPhrase(); // Bad Request

$contents = $response->getBody()->getContents();
json_decode($contents);
// [
//     'statusCode' => 400,
//     'error' => 'Bad Request',
//     'message' => 'Validation failed',
//     'validation' => [
//         'body' => [
//             'source' => 'body',
//             'keys' => ['value'],
//             'message' => '"value" is required'
//         ]
//     ]
// ]
```
