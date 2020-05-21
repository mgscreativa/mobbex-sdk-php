# Mobbex SDK Api Integration Library

* [Install](#install)
* [Specific methods](#specific-methods)

<a name="install"></a>
## Install

### With Composer

From command line

```
composer require mobbex/sdk:0.1.0
```

As a dependency in your project's composer.json

```json
{
    "require": {
        "mobbex/sdk": "0.1.0"
    }
}
```

### By downloading

1. Clone/download this repository
2. Copy `lib` folder to your project's desired folder.

<a name="specific-methods"></a>
## Specific methods

### Return Values
Api returns all values as **array**

### Configure your credentials

* Get your **API KEY** and **ACCESS TOKEN** from Mobbex

```php
require_once ('lib/mobbex.php');

try {
    $mb = new MB('API_KEY', 'ACCESS_TOKEN');
} catch (Exception $e) {
    echo $e->getMessage() . ' ' . $e->getCode();
}
```

### Methods

#### Get Clientes Lista

```php
$checkout_data = array(
    'total' => 100.5,
    'currency' => 'ARS',
    'description' => 'Checkout description',
    'return_url' => 'https://www.yourstore.com/mobbex-custom-return-url',
    'reference' => 'QJYZ1234',
    'webhook' => 'https://www.yourstore.com/mobbex-custom-webhook-url',
    'redirect' => false,
    'test' => true, // True, testing, false, production
    'options' => array(
        'theme' => array(
            'type' => 'light', // dark or light color scheme
            'showHeader' => true,
            'header' => array(
                'name' => 'Your brand name',
                'logo' => 'https://www.yourstore.com/store-logo.jpg', // Must be https!
            ),
        ),
    ),
);

$result = $mb->mobbex_checkout($checkout_data);

print_r($result);
```

#### Get Transaction Status

```php
$result = $mb->get_transaction_status('TRANSACTION_ID');

print_r($result);
```
