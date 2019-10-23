<?php


/**
 * Interface TrainInterface
 */
interface TrainInterface
{
    /**
     * Перейти в следующий вагон
     * @return void
     */
    public function next();

    /**
     * Перейти в предыдущий вагон
     * @return void
     */
    public function prev();

    /**
     * Статус текущего вагона
     * @return bool
     */
    public function status();

    /**
     *  Переключить состояние в вагоне
     * @return void
     */
    public function toggle();

    /**
     * Длина поезда
     * @return void
     */
    public function length();

    /**
     * "Карта" поезда
     * @return string
     */
    public function map();
}

/**
 * Поезд
 * Class Train
 */
class Train implements TrainInterface
{
    /** @var int Текущее местоположение */
    private $current = 0;

    /** @var array Вагоны и состояние */
    private $wagons = [];

    /** @var int Допустимый лимит вагонов */
    const MAX_LENGTH = 100;

    /**
     * Train constructor.
     */
    public function __construct($length = null)
    {
        if (is_null($length)) {
            $length = rand(1, self::MAX_LENGTH);
        } elseif ($length > self::MAX_LENGTH) {
            $length = self::MAX_LENGTH;
        }

        for ($i = 0; $i < $length; $i++) {
            $this->wagons[] = (bool)rand(0, 1);
        }
    }

    /**
     * Перейти в следующий вагон
     * @return void
     */
    public function next()
    {
        $this->current++;
        if ($this->current >= $this->length()) {
            $this->current = 0;
        }
    }

    /**
     * Перейти в предыдущий вагон
     * @return void
     */
    public function prev()
    {
        if ($this->current <= 0) {
            $this->current = $this->length();
        }
        $this->current--;
    }

    /**
     * Статус текущего вагона
     * @return bool
     */
    public function status()
    {
        return $this->wagons[$this->current];
    }

    /**
     * Переключить состояние в вагоне
     * @return void
     */
    public function toggle()
    {
        $this->wagons[$this->current] = $this->wagons[$this->current] ? false : true;
    }

    /**
     * Длина поезда
     * @return int
     */
    public function length()
    {
        return sizeof($this->wagons);
    }

    /**
     * "Карта" поезда
     * @return string
     */
    public function map()
    {
        $map = '';
        foreach ($this->wagons as $wagon) {
            $map .= $wagon ? '1' : '0';
        }

        return $map;
    }
}

interface SmartTrainInterface
{
    /**
     * Количество пройденных вагонов
     * @return int
     */
    public function getSteps();

    /**
     * Количество пройденных вагонов
     * @return int
     */
    public function getToggles();

    /**
     * Количество пройденных вагонов
     * @return int
     */
    public function getChecks();
}

/**
 * Умный поезд который следит что в нём происходит
 * Class SmartTrain
 */
class SmartTrain extends Train implements SmartTrainInterface
{
    /** @var int Количество переключений */
    private $toggles = 0;

    /** @var int Количество шагов */
    private $steps = 0;

    /** @var int Количество проверок */
    private $checks = 0;

    /**
     * @inheritDoc
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * @inheritDoc
     */
    public function getToggles()
    {
        return $this->toggles;
    }

    /**
     * @inheritDoc
     */
    public function getChecks()
    {
        return $this->checks;
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->steps++;
        parent::next();
    }

    /**
     * @inheritDoc
     */
    public function prev()
    {
        $this->steps++;
        parent::prev();
    }

    /**
     * @inheritDoc
     */
    public function toggle()
    {
        $this->toggles++;
        parent::toggle();
    }

    /**
     * @inheritDoc
     */
    public function status()
    {
        $this->checks++;
        return parent::status();
    }
}

/**
 * Машинист-считальщик :)
 * Interface DriverInterface
 */
interface DriverInterface
{
    /**
     * Передаём поезд
     * @param TrainInterface $train
     * @return void
     */
    public function setTrain(TrainInterface $train);

    /**
     * Получить поезд
     * @return TrainInterface
     */
    public function getTrain(): TrainInterface;

    /**
     * Посчитать длину поезда
     * @return int
     */
    public function countUp(): int;
}

/**
 *
 * Class DriverMozgovoy
 */
class DriverMozgovoy implements DriverInterface
{
    /** @var string Движение вперед */
    const DIRECTION_FORWARD = 'forward';

    /** @var string Движение назад */
    const DIRECTION_BACK = 'back';

    /** @var Train поезд */
    private $train;

    /** @var int Посчитанная длина */
    private $length = 0;

    /** @var string Текущее направление */
    private $direction = self::DIRECTION_FORWARD;

    /**
     * Driver constructor.
     * @param Train $train
     */
    public function setTrain(TrainInterface $train)
    {
        $this->train = $train;
    }

    /**
     * Посчитать длину поезда
     * @return int
     */
    public function countUp(): int
    {
        $firstStatus = $this->train->status();

        while (1) {
            $this->move();
            $this->length++;

            while ($this->train->status() != $firstStatus) {
                $this->move();
                $this->length++;
            }

            $this->train->toggle();
            $lastStatus = $this->train->status();

            $this->reverse();
            $this->move($this->length);

            if ($this->train->status() == $lastStatus) {
                return $this->length;
            }

            $firstStatus = $lastStatus;
        }
    }

    /**
     * Развернуться
     * @return void
     */
    private function reverse()
    {
        $this->direction = $this->direction == self::DIRECTION_FORWARD ? self::DIRECTION_BACK : self::DIRECTION_FORWARD;
    }

    /**
     * Двигаться на N вагонов
     * @param int $count
     * @return void
     */
    private function move(int $count = 1)
    {
        while ($count > 0) {
            $this->{$this->direction}();
            $count--;
        }
    }

    /**
     * Двигаться назад
     * @return void
     */
    private function back()
    {
        $this->train->prev();
    }

    /**
     * Двигаться вперед
     * @return void
     */
    private function forward()
    {
        $this->train->next();
    }

    /**
     * Получить поезд
     * @return TrainInterface
     */
    public function getTrain(): TrainInterface
    {
        return $this->train;
    }
}


/**
 * Сравнение алгоритмов
 * Class Referee
 */
class Referee
{
    /** @var DriverInterface[] Машинисты */
    public $drivers = [];

    /** @var TrainInterface Поезд */
    public $train;

    public $results = [];

    /**
     * Referee constructor.
     * @param TrainInterface $train
     * @param DriverInterface[] $drivers
     */
    public function __construct(SmartTrainInterface $train = null, array $drivers = [])
    {
        $this->drivers = $drivers;
        $this->setTrain($train);
    }

    public function setTrain(SmartTrainInterface $train)
    {
        $this->train = $train;
        foreach ($this->drivers as $driver) {
            $driver->setTrain(clone $train);
        }
    }

    public function setDriver(DriverInterface $driver)
    {
        if (!is_null($this->train)) {
            $driver->setTrain(clone $this->train);
        }
        $this->drivers[] = $driver;
    }

    public function run()
    {
        foreach ($this->drivers as $driver) {
            $className = get_class($driver);

            $start = microtime(1);
            $length = $driver->countUp();
            $time = microtime(1) - $start;

            /** @var SmartTrain $train */
            $train = $driver->getTrain();

            $this->results[$className] = [
                'name' => $className,
                'length' => $length,
                'time' => $time,
                'steps' => $train->getSteps(),
                'toggles' => $train->getToggles(),
                'checks' => $train->getChecks(),
                'map' => $train->map(),
            ];
        }

        $this->report();
    }

    public function report()
    {
        echo '<table border="1">';

        echo '<tr>
                <th>Name</th>
                <th>Length</th>
                <th>Steps</th>
                <th>Toggles</th>
                <th>Checks</th>
                <th>Time</th>
            </tr>
            <tr>
                <th colspan="4">Map</th>
            </tr>';


        echo '<tr>
                <td>Train</td>
                <td>' . $this->train->length() . '</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="6">' . $this->train->map() . '</td>
            </tr>';

        foreach ($this->results as $result) {
            echo '<tr>
                <td>' . $result['name'] . '</td>
                <td>' . $result['length'] . '</td>
                <td>' . $result['steps'] . '</td>
                <td>' . $result['toggles'] . '</td>
                <td>' . $result['checks'] . '</td>
                <td>' . $result['time'] . '</td>
            </tr>
            <tr>
                <td colspan="6">' . $result['map'] . '</td>
            </tr>';
        }

        echo '</table>';
    }
}


/**
 * Класс клон для отображения принципа сравнения алгоритмов
 * Class DriverTest
 */
class DriverTest extends DriverMozgovoy
{

}

$train = new SmartTrain();

$referee = new Referee($train, [
    new DriverMozgovoy(),
    new DriverTest()
]);

$referee->run();
