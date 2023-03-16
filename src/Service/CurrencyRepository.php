<?php
declare(strict_types=1);

namespace Drupal\bankservice\Service;

use Drupal\Core\Database\Connection;
use stdClass;

class CurrencyRepository
{
    /**
     * @var Connection
     */
    protected Connection $database;

    /**
     * @param Connection $database
     */
    public function __construct(
        Connection $database,
    )
    {
        $this->database = $database;
    }

    /**
     * @param $fromCurrency
     * @param $toCurrency
     * @return stdClass|array|null
     * @throws \Exception
     */
    public function get($fromCurrency, $toCurrency): mixed
    {
        $rate = $this->database->select('currency_rate', 'cr')
            ->fields('cr')
            ->condition('from', $fromCurrency)
            ->condition('to', $toCurrency)
            ->execute()->fetchObject();

        if (empty($rate)) {
            //todo: создать класс-exception NotFoundCurrencyRateException
            throw new \Exception('todo: NotFoundCurrencyRateException');
        }

        return $rate;
    }

    /**
     * @param array $fields
     * @return int|string|null
     * @throws \Exception
     */
    public function create(array $fields): int|string|null
    {
        return $this->database->insert('currency_rate')
            ->fields($fields)
            ->execute();
    }

    /**
     * @param int $id
     * @param array $fields
     * @return void
     */
    public function update(array $fields){
        $this->database->update('currency_rate')->fields($fields)->condition('id', $fields['id'])->execute();
    }

    /**
     * @return array
     */
    public function getRates(): array
    {
        $query = $this->database->select('currency_rate', 'cr')
            ->fields('cr')
            ->execute();

        return $query->fetchAll();
    }
}