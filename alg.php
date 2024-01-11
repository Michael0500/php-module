<?php

/**
 * Бинарный поиск
 * Возвращает индекс массива $data с заданным значением $number, либо -1 если его нет
 * @param array $data
 * @param int $number
 * @return int
 */
function search(array $data, int $number): int
{
    $start = 0;
    $end = count($data) - 1;

    while ($start <= $end) {
        $mid = floor(($start + $end) / 2); // находим середину

        if ($number == $data[$mid]) { // если она = нашему искомому числу то возвращаем его индекс
            return $mid;
        } elseif ($number > $data[$mid]) { // если наше число больше, тогда нужно искать в правой половине списка (массива)
            $start = $mid + 1;
        } else {
            $end = $mid - 1; // иначе нужно искать в левой половине списка (массива)
        }
    }

    return -1; // если элемент не найден, тогда -1
}

/*
var_dump(search([3, 6, 8, 10, 44, 56, 58, 89, 90, 300], 6));
*/

/**
 * Поиск выходных
 * Возвращает количество выходных дней в заданном диапазоне дат
 * @param string $begin
 * @param string $end
 * @return int
 */
function weekend(string $begin, string $end): int
{
    $SATURDAY = 6;
    $SUNDAY = 7;

    $beginDate = strtotime($begin);
    $endDate = strtotime($end);
    $weekdayCount = 0;

    while ($beginDate <= $endDate) {
        $weekday = date("N", $beginDate); // номер дня недели
        if ($weekday == $SATURDAY || $weekday == $SUNDAY) {
            $weekdayCount++; // увеличиваем количество выходных если это суббота или воскресенье
        }
        $beginDate += (24 * 3600); // переходим к следующему дню (+1 day)

    }

    return $weekdayCount; // количество выходных дней
}

/*
var_dump(weekend('06.06.2020', '06.06.2020'));
*/

/**
 * RGB
 * Возвращает целое число в которое упаковано цветовые составляющие rgb (три однобайтовых числа)
 * @param int $r
 * @param int $g
 * @param int $b
 * @return int
 */
function rgb(int $r, int $g, int $b): int
{
    // элементарная проверка чтоб числа влазили в байт
    if ($r < 0 || $r > 255 ||
        $g < 0 || $g > 255 ||
        $b < 0 || $b > 255) {
        return 0;
    }

    $packed = $r; // устанавливаем красную компоненту
    $packed = ($packed << 8) + $g; // устанавливаем зеленую компоненту
    $packed = ($packed << 8) + $b; // устанавливаем синюю компоненту

    return $packed;
}

/*
var_dump(rgb(255, 0, 255));
*/

/**
 * Последовательность Фибоначчи
 * Возвращает числа Фибоначчи ограниченные заданным числом
 * @param int $limit
 * @return string
 */
function fiborow(int $limit): string
{
    $fib = '';

    for ($i = 0; $i < $limit; $i++) {
        // http://en.wikipedia.org/wiki/Fibonacci_number#Computation_by_rounding
        $num = round(pow((sqrt(5) + 1) / 2, $i) / sqrt(5));
        if ($num < $limit) { // проверяем чтоб число было ограничено заданным
            $fib .= $num . ' ';
        } else {
            break;
        }
    }

    return trim($fib); // удаляем конечный пробел
}

/*
var_dump(fiborow(10));
*/
