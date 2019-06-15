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
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use SimpleXMLElement;

class OhExchangeService
{
    /**
     * Get the latest cached exchange rate models from the European Central Bank.
     *
     * @return OhExchangeDto[]
     * @throws OhExchangeException
     */
    public static function getLatestRates()
    {
        try {
            $client = new Client();
            $response = $client->request(
                'GET',
                'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist-90d.xml'
            );
            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                throw new OhExchangeException('Invalid response');
            }
            $body = $response->getBody();
            if ($body === null || mb_strlen($body) === 0) {
                throw new OhExchangeException('Invalid response from the European Central Bank');
            }
            $stringedBody = $body->__toString();
            $parser = @simplexml_load_string($stringedBody);
            if (!$parser) {
                $error = '';
                foreach(@libxml_get_errors() as $singleError) {
                    $error .= $singleError->message . ', ';
                }
                $baseMessage = 'Invalid response from the European Central Bank';
                if (strlen($error) !== 0) {
                    $baseMessage .= ': ' . $error;
                }
                throw new OhExchangeException($baseMessage);
            }

            $models = [];
            $cubeObject = $parser->children();
            $cube = (array)$cubeObject;
            foreach ($cube['Cube']->Cube as $item) {
                /* @var $item SimpleXMLElement*/
                $date = $item->attributes()->{'time'}->__toString();
                $model = new OhExchangeDto(
                    DateTime::createFromFormat('Y-m-d', $date)
                );

                $singleCube = ((array)$item->children())['Cube'];
                foreach ($singleCube as $_ => $v) {
                    /* @var $v SimpleXMLElement */
                    $attrs = $v->attributes();
                    $model->addRate(
                        $attrs->{'currency'}->__toString(),
                        $attrs->{'rate'}->__toString()
                    );
                }
                $models[] = $model;
            }

            return $models;

        } catch (BadResponseException $e) {
            throw new OhExchangeException(
                sprintf(
                    'Invalid HTTP response from the European Central Bank: %s',
                    $e->getResponse()->getStatusCode()
                )
            );
        } catch (GuzzleException $ee) {
            throw new OhExchangeException(
                sprintf(
                    'GuzzleException: %s',
                    $ee->getMessage()
                )
            );
        }
    }
}
