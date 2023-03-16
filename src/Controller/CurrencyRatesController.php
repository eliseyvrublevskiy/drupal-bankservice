<?php

namespace Drupal\bankservice\Controller;

use Drupal\bankservice\Service\CurrencyRepository;
use Drupal\bankservice\Service\CurrencyUpdater;
use Drupal\Core\Controller\ControllerBase;
use Drupal\bankservice\Service\CurrencyConverter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CurrencyRatesController extends ControllerBase
{
    protected CurrencyConverter $currencyConverter;
    protected CurrencyUpdater $currencyUpdater;
    protected CurrencyRepository $currencyRepository;

    public function __construct(
        CurrencyConverter  $currencyConverter,
        CurrencyUpdater    $currencyUpdater,
        CurrencyRepository $currencyRepository
    )
    {
        $this->currencyConverter = $currencyConverter;
        $this->currencyUpdater = $currencyUpdater;
        $this->currencyRepository = $currencyRepository;
    }

    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('bankservice.converter'),
            $container->get('bankservice.updater'),
            $container->get('bankservice.repository'),
        );
    }

    public function display()
    {
        $build['last_time'] = [
            '#type' => 'details',
            '#title' => $this->t('Last cron update'),
            '#description' => $this->config('bankservice.settings')->get('last_time'),
        ];

        $build['calc'] = [
            '#type' => 'details',
            '#title' => $this->t('Convector'),
            '#description' => $this->currencyConverter
                ->convert(444, 'PLN', 'USD'),
        ];

        $rows = array_map(
            function ($row) {
                return ['from' => $row->from, 'to' => $row->to, 'rate' => $row->rate];
            },
            $this->currencyRepository->getRates()
        );

        $build['currency_rate_table'] = [
            '#type' => 'table',
            '#header' => [
                'from' => $this->t('From'),
                'to' => $this->t('To'),
                'rate' => $this->t('Value'),
            ],
            '#rows' => $rows,
            '#empty' => $this->t('No currency rates found.'),
        ];

        return $build;
    }


}

