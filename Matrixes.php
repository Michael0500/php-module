<?php

// Обычная прямоугольная матрица
class Matrix
{
    private array $matrix = [];
    private int $col = 0;
    private int $row = 0;

    public function __construct(int $col, int $row)
    {
        $this->col = $col;
        $this->row = $row;
    }

    public function setElement(int $i, int $j, float $value): void
    {
        if ($i < 0 || $j < 0 || $i > $this->col-1 || $j > $this->row-1) {
            throw new OutOfRangeException("Индекс выходит за границы: [{$i},{$j}]");
        }

        $this->matrix[$i][$j] = $value;
    }

    public function getElement(int $i, int $j): float
    {
        if ($i < 0 || $j < 0 || $i > $this->col-1 || $j > $this->row-1) {
            throw new OutOfRangeException("Индекс выходит за границы: [{$i},{$j}]");
        }

        return $this->matrix[$i][$j];
    }

    public function getCol(): int
    {
        return $this->col;
    }

    public function getRow(): int
    {
        return $this->row;
    }

    public function show(): void
    {
        for ($i=0; $i<$this->col; $i++) {
            for ($j=0; $j<$this->row; $j++) {
                echo sprintf("[%d][%d] = %3.2f\t\t", $i+1, $j+1, $this->getElement($i, $j));
            }
            echo PHP_EOL;
        }
    }
}

//Квадратная матрица
class SquareMatrix extends Matrix
{
    public function __construct(int $col)
    {
        parent::__construct($col, $col);
    }
}

// Класс для создания матрицы
class MatrixBuilder
{
    public static function buildFromArray(array $items): Matrix
    {
        $col = count($items);
        if (0 === $col) {
            throw new InvalidArgumentException("Матрица должна содержать хотя бы один элемент col: {$col}");
        }

        $row = count($items[0]);
        if (0 === $row) {
            throw new InvalidArgumentException("Матрица должна содержать хотя бы один элемент row: {$row}");
        }

        $matrix = new Matrix($col, $row);
        for ($i=0; $i<$col; $i++) {
            for ($j=0; $j<$row; $j++) {
                $matrix->setElement($i, $j, $items[$i][$j]);
            }
        }

        return $matrix;
    }

    public static function buildSquare(Matrix $matrix): SquareMatrix
    {
        if ($matrix->getRow() !== $matrix->getCol()) {
            throw new InvalidArgumentException('Матрица должна быть квадратной');
        }

        $square = new SquareMatrix($matrix->getCol());
        for ($i=0; $i<$matrix->getCol(); $i++) {
            for ($j=0; $j<$matrix->getRow(); $j++) {
                $square->setElement($i, $j, $matrix->getElement($i, $j));
            }
        }

        return $square;
    }
}

// Класс с различными операциями над матрицей
class MatrixOperation
{
    public static function removeCol(int $col, Matrix $matrix): Matrix
    {
        $M = $matrix->getCol() - 1;
        $N = $matrix->getRow();
        $result = new Matrix($M, $N);

        for ($i = 0; $i < $col; $i++) {
            for ($j = 0; $j < $N; $j++) {
                $result->setElement($i, $j, $matrix->getElement($i, $j));
            }
        }

        for ($i = $col; $i < $M; $i++) {
            for ($j = 0; $j < $N; $j++) {
                $result->setElement($i, $j, $matrix->getElement($i + 1, $j));
            }
        }

        return $result;
    }

    public static function removeRow(int $row, Matrix $matrix): Matrix
    {
        $M = $matrix->getCol();
        $N = $matrix->getRow() - 1;
        $result = new Matrix($M, $N);

        for ($i = 0; $i < $M; $i++) {
            for ($j = 0; $j < $row; $j++) {
                $result->setElement($i, $j, $matrix->getElement($i, $j));
            }

            for ($j = $row; $j < $N; $j++) {
                $result->setElement($i, $j, $matrix->getElement($i, $j + 1));
            }
        }

        return $result;
    }

    public static function removeColRow(int $col, int $row, Matrix $matrix): Matrix
    {
        return self::removeCol($col, self::removeRow($row, $matrix));
    }

    public static function minor(int $i, int $j, SquareMatrix $matrix): SquareMatrix
    {
        return MatrixBuilder::buildSquare(self::removeColRow($i, $j, $matrix));
    }

    public static function det2(SquareMatrix $matrix): float
    {
        if (2 !== $matrix->getRow() && 2 !== $matrix->getCol()) {
            throw new InvalidArgumentException('Матрица должна быть 2x2');
        }

        return $matrix->getElement(0, 0) * $matrix->getElement(1, 1) - $matrix->getElement(0, 1) * $matrix->getElement(1, 0);
    }

    public static function det(SquareMatrix $matrix): float
    {
        if (2 === $matrix->getCol()) {
            return self::det2($matrix);
        }

        $sum = 0.0;
        $i = 0;
        for ($j = 0; $j < $matrix->getRow(); $j++) {
            $minor = self::det(self::minor($i, $j, $matrix));
            $aij = pow(-1, $i + 1 + $j + 1) * $matrix->getElement($i, $j);
            //echo sprintf("%3.2f + %3.2f * %3.2f\n", $sum, $aij, $minor);
            $sum += $aij * $minor;
        }

        return $sum;
    }
}


// Проверка методов работы с матрицами
$matrix = MatrixBuilder::buildFromArray([
    [1, 2, 3],
    [2, 3, 4],
    [3, 4, 5],
]);
$matrix->show();

echo PHP_EOL;

$i = 0;
$j = 0;

$m = MatrixOperation::removeCol($i, $matrix);
$m->show();

echo PHP_EOL;

$m = MatrixOperation::removeRow($j, $matrix);
$m->show();

echo PHP_EOL;

$m = MatrixOperation::removeColRow($i, $j, $matrix);
$m->show();


$matrix = MatrixBuilder::buildFromArray([
    [2, 2, 3],
    [-3, 3, 4],
    [3, 4, 5],
]);

$matrix->show();
echo MatrixOperation::det(MatrixBuilder::buildSquare($matrix));
