<?php

class CircleList {
    private array $data; // элементы
    private int $index; // индекс текущего просматриваемого элемента
    private int $length; // количество элементов

    /**
     * CircleList constructor.
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

    public function current(): bool {
        $result = $this->data[$this->index];

        echo 'index: ' . $this->index . ' value: ' . $result . PHP_EOL;

        return $result;
    }

    public function next(): bool {
        if (++$this->index > $this->length - 1) {
            $this->index = $this->index - $this->length;
        }

        $result = $this->data[$this->index];

        //echo 'index: ' . $this->index . ' value: ' . $result . PHP_EOL;

        return $result;
    }

    public function prev(): bool {
        if (--$this->index < 0) {
            $this->index = $this->length - 1;
        }

        $result = $this->data[$this->index];

        //echo 'index: ' . $this->index . ' value: ' . $result . PHP_EOL;


        return $result;
    }

    public function getIndex(): int {
        return $this->index;
    }

    public function off() {
        $this->data[$this->index] = false;
    }

    public function on() {
        $this->data[$this->index] = true;
    }

    public function toggle() {
        $this->data[$this->index] = !$this->data[$this->index];
    }
}


$arr = [false, false, true, true, false, false, true, false, true, true, true];
//$arr = [false, false, true];
$train = new CircleList($arr);

//$i=0;
//while (true) {
//    echo 'i: ' . $i . ' ';
//    $train->current();
//    $train->next();
//    if (++$i > 10) break;
//}
//
//echo PHP_EOL;
//
//$i=0;
//while (true) {
//    echo 'i: ' . $i . ' ';
//    $train->current();
//    $train->prev();
//    if (++$i > 10) break;
//}
//var_dump($train);
//exit;


$train->on(true); // включаем свет в первом вагоне
$length = 1;
echo PHP_EOL;

while (true) {
    $train->next();
    $length++;
    if (true === $train->current()) {
        $train->off();
        for ($i=0; $i<$length; $i++) {
            $train->prev();
        }

        if (true === $train->current()) {
            continue;
        } else {
            break;
        }
    }
}

var_dump($train);
var_dump($length);
exit;





for ($i = 1; $i<=3; $i++) {
    $s = $train->current();
    var_dump($s);
    var_dump($train->getIndex());
    $train->next();
}

echo PHP_EOL;

for ($i = 1; $i<=10; $i++) {
    $s = $train->current();
    var_dump($s);
    var_dump($train->getIndedx());
    $train->prev();
}



exit;

// Заполняем вагоны случайными значениями (вкл/выкл свет)
function generationLightList(SplDoublyLinkedList $list) {
    $length = rand(1, 10);

    for ($i=0; $i<$length; $i++) {
        $list->add($i, rand(0, 1));
    }
}


// Поезд
$train = new SplDoublyLinkedList();
generationLightList($train);
var_dump($train);

// Нужно включить свет в начальном вагоне, сли он ещё не горит
$i = 0;
if ('0' === $train[$i]) {
    $train[$i] = 1;
}

foreach ($train as $item) {

}
