OhExchange PHP Library
======================

OhExchange is a simple library to retrieve exchange rates from the European Central Bank.

### Retrieving the data

```php
<?php
require 'vendor/autoload.php';

use OhExchange\OhExchangeException;
use OhExchange\OhExchangeService;

try {
    $models = OhExchangeService::getLatestRates();
    foreach ($models as $model) {
        echo $model->date->format('Y-m-d') . PHP_EOL;
        foreach ($model->rates as $currency => $exchange) {
            echo '  ' . $currency . ' -> ' . $exchange . PHP_EOL;
        }
        // Persist this model, using the date as a unique identifier.
    }
} catch (OhExchangeException $e) {
    echo 'Error:' . PHP_EOL;
    echo '  ' . $e;
}
```


### Converting between currencies:

```php
<?php
$model = new \OhExchange\OhExchangeDto(); // Should be retrieved from your database.

$valueInUsd = $model->convertAmountFromTo(1, 'GBP', 'USD');
```
