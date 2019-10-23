<?php

/**
 * Interface BankomatInterface
 */
interface BankomatInterface
{
    /**
     * BankomatInterface constructor.
     * Инициализация банкомата с установкой баланса
     *
     * @param array $balance
     */
    public function __construct(array $balance);

    /**
     * Получение денег
     * Возвращает массив купюр для выдачи либо false если выдача невозможна
     *
     * @param int $sum
     * @return array|bool
     */
    public function getCash(int $sum);
}

/**
 * Абстрактная реализация банкомата
 * Class BaseBankomat
 */
abstract class BaseBankomat implements BankomatInterface
{
    /** @var array Текущий баланс */
    protected $balance = [];

    /**
     * BaseBankomat constructor.
     * @param array $balance
     */
    public function __construct(array $balance)
    {
        $this->balance = $balance;
    }

    /**
     * Снятие денег с баланса (изменение баланса)
     * @param array $banknotes
     */
    protected function takeMoney(array $banknotes)
    {
        foreach ($banknotes as $nominal => $count) {
            $this->balance[$nominal] -= $count;
        }
    }

    /**
     * @inheritDoc
     */
    public function getCash(int $sum)
    {
        $banknotes = $this->getListBanknotes($sum);
        if (is_array($banknotes)) {
            $banknotes = array_filter($banknotes);
            $this->takeMoney($banknotes);
        }
        return $banknotes;
    }

    /**
     * Получение списка банкнот для выдачи
     * @param int $sum
     * @return bool
     */
    protected function getListBanknotes(int $sum)
    {
        return false;
    }
}

/**
 * Реализация алгоритма банкомата №1
 * Class Bankomat
 */
class Bankomat extends BaseBankomat
{
    /**
     * Получение списка банкнот для выдачи
     * @param int $sum
     * @return array|bool
     */
    protected function getListBanknotes(int $sum)
    {
        return $this->calc($sum);
    }

    /**
     * Механизм расчёта вариантов выдачи
     * @param $sum
     * @param array $banknotes
     * @return array|bool
     */
    private function calc($sum, $banknotes = [])
    {
        foreach ($this->balance as $nominal => $amount) {
            if (isset($banknotes[$nominal])) {
                continue;
            }

            $remainingAmount = $sum - $this->countBanknotes($banknotes);
            $count = intval($remainingAmount / $nominal);
            $count = $count > $amount ? $amount : $count;

            $banknotes[$nominal] = $count;
            while ($count > 0) {
                $banknotes[$nominal] = $count;

                $remainingAmount = $sum - $this->countBanknotes($banknotes);
                if ($remainingAmount == 0) {
                    return $banknotes;
                }

                $result = $this->calc($sum, $banknotes);
                if (is_array($result)) {
                    return $result;
                }

                $count--;
            }
        }

        return false;
    }

    /**
     * Подсчет суммы в списке банкнот
     * @param array $banknotes
     * @return int
     */
    private function countBanknotes(array $banknotes): int
    {
        $sum = 0;
        foreach ($banknotes as $nominal => $count) {
            $sum += $nominal * $count;
        }
        return $sum;
    }
}

/**
 * Реализация алгоритма банкомата №1
 * Class Bankomat2
 */
class Bankomat2 extends BaseBankomat
{
    /**
     * Получение списка банкнот для выдачи
     * @param int $sum
     * @return array|bool
     */
    protected function getListBanknotes(int $sum)
    {
        return $this->calc($sum, $this->balance);
    }

    /**
     * Механизм расчёта вариантов выдачи
     * @param $sum
     * @param array $balance
     * @return array|bool
     */
    private function calc($sum, $balance = [])
    {
        $banknotes = [];

        $count = 0;
        while ($count == 0) {
            $amount = reset($balance);
            $nominal = key($balance);
            unset($balance[$nominal]);
            $count = intval($sum / $nominal);
            $count = $count > $amount ? $amount : $count;
        }

        while ($count > 0) {
            $banknotes[$nominal] = $count;
            $remainingAmount = $sum - ($nominal * $count);
            if ($remainingAmount == 0) {
                return $banknotes;
            }
            
            $result = $this->calc($remainingAmount, $balance);
            if (is_array($result)) {
                return $banknotes + $result;
            }
            
            $count--;
        }

        return false;
    }
}


$balance = [
    500 => 2,
    100 => 10,
    50 => 10,
    10 => 10,
];

$bank = new Bankomat($balance);
$bank2 = new Bankomat2($balance);

echo '<pre>';

$start = microtime(1);
$banknotes = $bank->getCash(1160);
$time = microtime(1) - $start;
var_dump($banknotes, $time);

$start = microtime(1);
$banknotes = $bank->getCash(550);
$time = microtime(1) - $start;
var_dump($banknotes, $time);

$start = microtime(1);
$banknotes = $bank->getCash(550);
$time = microtime(1) - $start;
var_dump($banknotes, $time);

$start = microtime(1);
$banknotes = $bank->getCash(550);
$time = microtime(1) - $start;
var_dump($banknotes, $time);

$start = microtime(1);
$banknotes = $bank2->getCash(1160);
$time = microtime(1) - $start;
var_dump($banknotes, $time);
