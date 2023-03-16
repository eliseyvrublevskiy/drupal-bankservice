<?php
declare(strict_types=1);

namespace Drupal\bankservice\Service\CurrencyUpdater;


interface CurrencyProviderInterface
{
    /**
     * @param array $config
     * @return mixed
     * todo: описать подробнее
     * return array of [
     * 'from' => string,
     * 'to' => string,
     *'rate' => string
     * ]
     */
    public function updateRate(array $config);
}