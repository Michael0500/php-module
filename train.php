<?php

/**
 * Круговой (замкнутый) поезд
 */
class CircleList {
    private array $data; // вагоны
    private int $index; // индекс текущего просматриваемого вагона
    private int $length; // количество вагонов

    /**
     * @param array $data
     * @throws Exception
     */
    public function __construct(array $data) {
        foreach ($data as $item) {
            if (!is_bool($item)) {
                throw new Exception('Элементы должны быть логического типа');
            }
        }
        $this->index = 0;
        $this->length = count($data);
        $this->data = $data;
    }

    /**
     * Текущий вагон
     * @return bool
     */
    public function current(): bool {
        return $this->data[$this->index];
    }

    /**
     * Переходит в следующий вагон
     * @return bool
     */
    public function next(): bool {
        $this->index++;
        if ($this->index > $this->length - 1) {
            $this->index = 0;
        }

        return $this->data[$this->index];
    }

    /**
     * Переходит в предыдущий вагон
     * @return bool
     */
    public function prev(): bool {
        $this->index--;
        if ($this->index < 0) {
            $this->index = $this->length - 1;
        }

        return $this->data[$this->index];
    }

    /**
     * Выключить свет в текущем вагоне
     */
    public function off() {
        $this->data[$this->index] = false;
    }

    /**
     * Включить свет в текущем вагоне
     */
    public function on() {
        $this->data[$this->index] = true;
    }
}

// Массив с вагонами (true - свет в вагоне включен, false - свет в вагоне выключен)
$arr = [true, false, true, true, true, false, true, false, false, true];
$train = new CircleList($arr);

$train->on(); // включаем свет в первом вагоне
$length = 1;

while (true) {
    // Идем вперед пока не встретим вагон с работающим освещением
    while (!$train->next()) {
        // при этом считаем пройденные вагоны
        $length++;
    }
    // выключаем в найденном вагоне свет
    $train->off();

    // и идём обратно к начальному вагону
    for ($i = 0; $i < $length; $i++) {
        $train->prev();
    }

    if (true === $train->current()) {
        // если в нём свет горит, то повторяем операцию
        $length=1;
    } else {
        // если свет не горит, значит мы прошли полный круг и знаем длину поезда
       break;
    }
}

echo 'Длина поезда: ' . $length . PHP_EOL;
