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
namespace OhExchange;

use DateTime;


class OhExchangeDto
{
    /**
     * @var DateTime
     */
    public $date;

    /**
     * @var array
     */
    public $rates = [];

    /**
     * Construct the DTO, passing a DateTime object.
     *
     * @param DateTime $date
     */
    public function __construct($date)
    {
        $date->setTime(0, 0, 0, 0);
        $this->date = $date;
        $this->rates['EUR'] = 1;
    }

    /**
     * Set all rates
     *
     * @param array $rates
     * @return OhExchangeDto
     */
    public function setRates($rates)
    {
        foreach ($rates as $k => $v) {
            $this->addRate($k, $v);
        }
        return $this;
    }

    /**
     * Add a single exchange rate into the model.
     *
     * @param string $code
     * @param float $rate
     * @return OhExchangeDto
     */
    public function addRate($code, $rate)
    {
        if (strlen($code) !== 3) {
            return $this;
        }
        $this->rates[strtoupper($code)] = floatval($rate);
        return $this;
    }

    /**
     * Does an exchange rate exist for a given code?
     *
     * @param string $code
     * @return bool
     */
    public function hasExchangeRateForCode($code)
    {
        return array_key_exists(strtoupper($code), $this->rates);
    }

    /**
     * Convert an amount from $fromCurrency to $toCurrency
     *
     * @param float $amount
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param int $decimals (optional) default 2
     * @return float|null
     */
    public function convertAmountFromTo($amount, $fromCurrency, $toCurrency, $decimals=2)
    {
        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency = strtoupper($toCurrency);
        $amount = (float)$amount;

        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        if ($this->hasExchangeRateForCode($fromCurrency) === false) {
            return null;
        }

        if ($this->hasExchangeRateForCode($toCurrency) === false) {
            return null;
        }

        if ($toCurrency === 'EUR') {
            return (float)number_format(($amount / $this->rates[$fromCurrency]), $decimals);
        }

        if ($fromCurrency === 'EUR') {
            return (float)number_format(($this->rates[$toCurrency]*$amount), $decimals);
        }

        $amountInEuros = $this->convertAmountFromTo(
            $amount,
            $fromCurrency,
            'EUR',
            $decimals
        );

        return (float)number_format(($amountInEuros * $this->rates[$toCurrency]), $decimals);
    }

    /**
     * Export the object as an array.
     *
     * @return array
     */
    public function asArray()
    {
        return [
            'date' => $this->date->format('Y-m-d'),
            'rates' => $this->rates
        ];
    }
}
