<?php
declare(strict_types=1);

namespace Drupal\bankservice\Service\CurrencyUpdater;

use Drupal\Core\Database\Connection;
use GuzzleHttp\ClientInterface;

/**
 * FixerIoProvider - todo: bla bla bla
 */
class FixerIoProvider implements CurrencyProviderInterface
{
    protected $httpClient;

    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param $config
     * todo: описать подробне
     * @return bool|mixed
     */
    public function updateRate($config)
    {
        $api_key = \Drupal::config('bankservice.settings')->get('api_key'); //todo: вынести в конфигурацию
        $result = [];

        try {
            $response = $this->httpClient->get(
                sprintf(
                    "https://api.apilayer.com/fixer/latest?&base=%s&symbols=%s",
                    $config['from'], implode(',', $config['to'])
                ),
                ['headers' => ['apikey' => $api_key]]
            );

            $data = json_decode($response->getBody()->getContents());

            if (!$data->success) {
                //todo: создать класс-exception NoConnectionException
                throw new \Exception('todo: NoConnectionException');
            }

            foreach ($data->rates as $currency => $rate) {
                $result[] = [
                    'from' => $config['from'],
                    'to' => $currency,
                    'rate' => $rate
                ];
            }
        } catch (\Exception $e) {
            // Обработка ошибок.
        }

        return $result;
    }

}