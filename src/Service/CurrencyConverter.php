<?php

namespace Drupal\bankservice\Service;

use Drupal\Core\Database\Connection;
use GuzzleHttp\ClientInterface;

class CurrencyConverter
{
    public static array $ratesCache = [];

    protected CurrencyUpdater $currencyUpdater;
    protected CurrencyRepository $currencyRepository;

    public function __construct(
        CurrencyUpdater $currencyUpdater,
        CurrencyRepository $currencyRepository,
    )
    {
        $this->currencyUpdater = $currencyUpdater;
        $this->currencyRepository = $currencyRepository;
    }

    public function convert($amount, $fromCurrency, $toCurrency, $precision = 2)
    {
        return round($amount * $this->getRateNumber($fromCurrency, $toCurrency), $precision);
    }

    public function getRateNumber($fromCurrency, $toCurrency)
    {
        $cacheKey = $this->getCacheKey($fromCurrency, $toCurrency);

        if (!empty($this::$ratesCache[$cacheKey])) {
            return $this::$ratesCache[$cacheKey];
        }

        try {
            $rateModel = $this->currencyRepository->get($fromCurrency, $toCurrency);
            self::$ratesCache[$cacheKey] = $rateModel->rate;
        } catch (\Exception $exception) {
            //todo: создать класс-exception NotFoundCurrencyRateException и проверять на него
            if ($this->currencyUpdater->updateRateFor($fromCurrency, $toCurrency)) {
                $rateModel =  $this->currencyRepository->get($fromCurrency, $toCurrency);
                self::$ratesCache[$cacheKey] = $rateModel->rate;
            }else{
                //todo: throw NotFoundCurrencyRateException
            }
        }

        return self::$ratesCache[$cacheKey];
    }

    protected function getCacheKey($fromCurrency, $toCurrency)
    {
        return $fromCurrency . $toCurrency;
    }
}
