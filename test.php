<?php
/**
 * OhExchange - PHP Exchange Rate Library for the European Central Bank
 *
 * @copyright Roger E Thomas
 *
 * @license This software and associated documentation (the "Software") may not be
 * used, copied, modified, distributed, published or licensed to any 3rd party
 * without the written permission of Roger E Thomas.
 *
 * The above copyright notice and this permission notice shall be included in
 * all licensed copies of the Software.
 */
require 'vendor/autoload.php';


use OhExchange\OhExchangeDto;
use OhExchange\OhExchangeException;
use OhExchange\OhExchangeService;

try {
    $models = OhExchangeService::getLatestRates();
    foreach ($models as $model) {
        echo $model->date->format('Y-m-d') . PHP_EOL;
        foreach ($model->rates as $currency => $exchange) {
            echo '  ' . $currency . ' -> ' . $exchange . PHP_EOL;
        }
    }

    echo 'Test rates exchange correctly:' . PHP_EOL;
    $tmpModel = new OhExchangeDto(new DateTime());
    $tmpModel->rates = ['EUR' => 1, 'GBP' => 0.50, 'USD' => 2.0];

    $tests = [
        [1.0, 'EUR', 'EUR', 1.0],
        [0.5, 'GBP', 'EUR', 1.0],
        [1.0, 'EUR', 'GBP', .5],
        [3, 'USD', 'GBP', .75]
    ];

    foreach ($tests as $data) {
        $val = $tmpModel->convertAmountFromTo(floatval($data[0]), $data[1], $data[2]);
        if ($val == $data[3]) {
            echo sprintf('     OK - %s %s === %s %s', $data[0], $data[1], $data[3], $data[2]) . PHP_EOL;
        } else {
            echo sprintf('! ERROR - %s %s !== %s %s', $data[0], $data[1], $data[3], $data[2]) . PHP_EOL;
        }
    }
    echo PHP_EOL;

} catch (OhExchangeException $e) {
    echo 'Error:' . PHP_EOL;
    echo '  ' . $e . PHP_EOL;
}
