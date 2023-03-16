<?php

namespace Drupal\bankservice\Service;

use Drupal\bankservice\Service\CurrencyUpdater\CurrencyProviderInterface;
use Drupal\Core\Database\Connection;
use GuzzleHttp\ClientInterface;

class CurrencyUpdater
{
    protected Connection $database;
    protected CurrencyProviderInterface $currencyProvider;
    protected CurrencyRepository $currencyRepository;

    public function __construct(
        Connection                $database,
        CurrencyProviderInterface $currencyProvider,
        CurrencyRepository        $currencyRepository
    )
    {
        $this->database = $database;
        $this->currencyProvider = $currencyProvider;
        $this->currencyRepository = $currencyRepository;
    }

    public function updateRates()
    {
        $currencies = $this->currencyRepository->getRates();

        $compositeArray = [];
        foreach ($currencies as $currency) {
            $compositeArray[$currency->from][] = $currency->to;
        }

        foreach ($compositeArray as $from => $to) {
            $this->updateRateFor($from, $to);
        }
    }

    public function updateRateFor($base, $to = [])
    {
        if(is_string($to)){
            $to = [$to];
        }

        $status = false;

        try {
            $rates = $this->currencyProvider->updateRate(['from' => $base, 'to' => $to]);

            foreach ($rates as $rate) {
                try {
                    $rateModel = $this->currencyRepository->get($rate['from'], $rate['to']);
                    $rateModel->rate = $rate['rate'];
                    $this->currencyRepository->update((array)$rateModel);
                    $status = true;
                } catch (\Exception $exception) {
                    //todo: создать класс-exception NotFoundCurrencyRateException и проверять на него
                    if ($exception->getMessage() === 'todo: NotFoundCurrencyRateException') {
                        if (!empty($this->currencyRepository->create($rate))) {
                            $status = true;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            //todo: обработка ошибок
        }

        return $status;
    }
}
