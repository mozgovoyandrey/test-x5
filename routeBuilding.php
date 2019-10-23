<?php

/**
 * Карта
 * Class Map
 */
class Map
{
    /** @var int Высота карты */
    private $height = 0;

    /** @var int Ширина карты */
    private $width = 0;

    /** @var array бинарное представление карты */
    private $map = [];

    /**
     * Map constructor.
     * Создание карты заданной высоты и ширины
     *
     * @param int $height
     * @param int $width
     */
    public function __construct(int $height, int $width)
    {
        $this->height = $height;
        $this->width = $width;

        $this->map = array_fill(0, $height, array_fill(0, $width, 0));
    }

    /**
     * Добавление непроходимого блока на карту
     * @param $x
     * @param $y
     * @param Block $block
     */
    public function setBlock($x, $y, Block $block)
    {
        if ($x >= $this->height || $y >= $this->width) {
            return;
        }

        if ($x < 0 || $y < 0) {
            return;
        }
        $blockHeight = $block->height;
        if ($x + $blockHeight > $this->height) {
            $blockHeight = $this->height - $x;
        }

        $blockWidth = $block->width;
        for ($i = 0; $i < $blockHeight; $i++) {
            for ($j = 0; $j < $blockWidth; $j++) {
                $this->map[$x + $i][$j + $y] = 1;
            }
        }
    }

    /**
     * Получение бинарного представления карты
     * @return array
     */
    public function getBinaryMap(): array
    {
        return $this->map;
    }

    /**
     * Проверка на проходимость точки
     * @param $x
     * @param $y
     * @return bool
     */
    public function checkAvailable($x, $y): bool
    {
        if (isset($this->map[$x][$y]) && $this->map[$x][$y] === 0) {
            return true;
        }

        return false;
    }
}

/**
 * Механизм отображения карты
 * Class MapPrinter
 */
class MapPrinter
{
    /** @var array Карта */
    private $map;

    /**
     * MapPrinter constructor.
     * @param Map $map
     */
    public function __construct(Map $map)
    {
        $this->map = $map->getBinaryMap();
    }

    /**
     * Печать карты
     */
    public function print()
    {
        echo '<pre>';
        foreach ($this->map as $row) {
            foreach ($row as $cell) {
                echo $cell;
            }
            echo PHP_EOL;
        }
        echo '</pre>';
    }

    /**
     * Печать карты с добавление на неё маршрута
     * @param Route $route
     */
    public function printWithRoute(Route $route)
    {
        echo '<pre>';
        foreach ($this->map as $x => $row) {
            foreach ($row as $y => $cell) {
                echo $route->inRoute($x, $y) ? '*' : $cell;
            }
            echo PHP_EOL;
        }
        echo '</pre>';
    }
}

/**
 * Блок
 * Class Block
 */
class Block
{
    /** @var int Высота блока */
    public $height = 0;

    /** @var int Ширина блока */
    public $width = 0;

    /**
     * Block constructor.
     * @param int $height
     * @param int $width
     */
    public function __construct(int $height, int $width)
    {
        $this->height = $height;
        $this->width = $width;
    }
}

/**
 * Точка
 * Class Point
 */
class Point
{
    /** @var int Координата по высоте */
    public $x;

    /** @var int Координата по ширине */
    public $y;

    /**
     * Point constructor.
     * @param int $x
     * @param int $y
     */
    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
    }
}

/**
 * Построитель маршрутов
 * Class Router
 */
class Router
{
    /** @var bool Возможность передвижения по диагонали */
    const DIAGONAL_MOVEMENT = true;

    /** @var Map Карта */
    private $map;

    /**
     * Router constructor.
     * @param Map $map
     */
    public function __construct(Map $map)
    {
        $this->map = $map;
    }

    /**
     * Построение маршрута по двум точкам
     * @param Point $start
     * @param Point $finish
     * @return bool|Route
     */
    public function route(Point $start, Point $finish)
    {
        $route = new Route($start);
        $passedPoints = [$start];
        return $this->run($finish, [$route], $passedPoints);
    }

    /**
     * Алгоритм построения маршрута
     * @param Point $to Точка назначения
     * @param Route[] $routes Текущие варианты маршрутов
     * @param Point[] $passedPoints Пройденные точки
     * @return bool|Route
     */
    public function run(Point $to, $routes = [], &$passedPoints = [])
    {
        $newRoutes = [];
        foreach ($routes as $route) {
            $lastPoint = $route->getLastPoint();
            $points = $this->getAvailablePoints($lastPoint, $passedPoints);
            if (in_array($to, $points)) {
                $route->addPoint($to);
                return $route;
            }

            foreach ($points as $point) {
                $passedPoints[] = $point;

                $newRoute = clone $route;
                $newRoute->addPoint($point);
                $newRoutes[] = $newRoute;
            }
        }

        if (count($newRoutes) == 0) {
            return false;
        }

        return $this->run($to, $newRoutes, $passedPoints);
    }

    /**
     * Получить доступные для перемещения точки относительно заданной позиции
     * с учетом пройденных ранее точек
     * @param Point $point Текущая позиция
     * @param Point[] $passedPoints Пройденные точки
     * @return array
     */
    private function getAvailablePoints(Point $point, $passedPoints)
    {
        $x = $point->x;
        $y = $point->y;

        $candidates = [
            [$x + 1, $y],
            [$x - 1, $y],
            [$x, $y + 1],
            [$x, $y - 1],
        ];
        if (self::DIAGONAL_MOVEMENT) {
            $candidates = array_merge($candidates, [
                [$x + 1, $y + 1],
                [$x - 1, $y - 1],
                [$x - 1, $y + 1],
                [$x + 1, $y - 1],
            ]);
        }

        $steps = [];

        foreach ($candidates as $candidate) {
            if ($this->map->checkAvailable($candidate[0], $candidate[1])) {
                $point = new Point(
                    $candidate[0],
                    $candidate[1]
                );

                if (!in_array($point, $passedPoints)) {
                    $steps[] = $point;
                }
            }
        }

        return $steps;
    }

    public function multiRoute(Point $start, $points, Point $finish = null)
    {
        $routes = [];

        $this->runMulti($start, $points);

        foreach ($points as $key => $pointFrom) {
            for ($i = $key + 1; $i < count($points); $i++) {
                $pointTo = $points[$i];
                $route = $this->route($pointFrom, $pointTo);

                $routes[$key + 1][$i + 1] = $route;
                $routesLength[$key + 1][$i + 1] = $route->getStep();
            }
        }

        var_dump($routesLength);
        die;
    }

    private function runMulti(Point $start, $points)
    {
        $routes = [];
        $routesLength = [];

        foreach ($points as $key => $pointTo) {
            $route = $this->route($start, $pointTo);
            if ($route === false) {
                return false;
            }
            $routes[$key] = $route;
            $routesLength[$key] = $route->getStep();
        }
    }
}

/**
 * Маршрут
 * Class Route
 */
class Route
{
    /** @var array Точки маршрута */
    private $route = [];
    /** @var array Точки маршрута с привязкой по координатам */
    private $routeMap = [];

    /**
     * Инициализация маршрута с передачей стартовой точки
     * Route constructor.
     * @param Point $point
     */
    public function __construct(Point $point)
    {
        $this->addPoint($point);
    }

    /**
     * Добавление точки в маршрут
     * @param Point $point
     */
    public function addPoint(Point $point)
    {
        $this->route[] = $point;
        $this->routeMap[$point->x][$point->y] = $point;
    }

    /**
     * Получение последней точки
     * @return mixed
     */
    public function getLastPoint()
    {
        return end($this->route);
    }

    /**
     * Длина маршрута
     * @return int
     */
    public function getStep()
    {
        return count($this->route);
    }

    /**
     * Проверка наличия точки в маршруте по координатам
     * @param $x
     * @param $y
     * @return bool
     */
    public function inRoute($x, $y)
    {
        return isset($this->routeMap[$x][$y]);
    }
}


$map = new Map(40, 200);

$map->setBlock(3, 2, new Block(5, 2));
$map->setBlock(5, 6, new Block(5, 2));
$map->setBlock(8, 10, new Block(5, 2));
$map->setBlock(12, 15, new Block(5, 2));
$map->setBlock(1, 0, new Block(2, 2));
$map->setBlock(9, 0, new Block(2, 2));
$map->setBlock(5, 40, new Block(30, 2));

$mapPrinter = new MapPrinter($map);
$mapPrinter->print();

$router = new Router($map);

$route = $router->route(
    new Point(6, 1),
    new Point(20, 150)
);

if ($route) {
    $mapPrinter->printWithRoute($route);
}

//$routeMulti = $router->multiRoute( new Point(6,1), [
//    new Point(20,50),
//    new Point(10,38),
//    new Point(2,10),
//]);
