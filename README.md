<div align="center">
    <h1>LogSnag PHP</h1>
	<p>A PHP SDK for the LogSnag API – <a href="https://logsnag.com">logsnag.com</a></p>
</div>

## Installation

```sh
composer require hallewood/logsnag-php
```

## Usage

### Initialize Client

```php
use Hallewood\LogSnag\Client;

$logsnag = new Client('7f568d735724351757637b1dbf108e5', 'my-project');
```

The project name will be auto-injected in all requests.

### Log
```php

//
// The channel and the event name are the only required parameters.
$logsnag->log('subscriptions', 'User subscribed!');

//
// Other parameters can be added when needed.
$logsnag->log(
    channel: 'subscriptions',
    event: 'User subscribed!',
    userId: '123-456',
    description: 'A new user subscribed to the **premium plan**.',
    icon: '👍🏼',
    notify: true,
    tags: [
        'payment-method': 'card',
        'plan': 'monthly',
    ],
    parser: 'markdown',
    timestamp: 1709842921,
);
```

### Identify

```php

//
// Both the user id and the properties are required.
$logsnag->identify(
    userId: '123-456',
    properties: [
        'active': 'yes',
        'signed-in': 'no',
    ],
);
```

### Insight

```php

//
// The title and the value are the only required parameters.
$logsnag->insight('Subscribed Users', 12);

//
// Other parameters can be added when needed.
$logsnag->log(
    title: 'Status',
    value: 'watered',
    icon: '🪴',
);
```

### Insight Mutate

```php

//
// The title and at least one mutation is required.
$logsnag->insight('Subscribed Users', inc: 3);

//
// Other parameters can be added when needed.
$logsnag->log(
    title: 'Subscribed Users',
    inc: -2,
    icon: '👍🏼',
);
```
