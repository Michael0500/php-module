package example;

public class Matrixes {
    public static void main(String[] args) {
        Matrix matrix = new Matrix(new double[][]{
                {-1, 2, 3},
                {2, 3, 4},
                {3, 4, 59}
        });
        matrix.show();
        System.out.println();

        int i = 0;
        int j = 0;
        Matrix m;

        m = MatrixOperation.removeCol(i, matrix);
        m.show();
        System.out.println();

        m = MatrixOperation.removeRow(j, matrix);
        m.show();
        System.out.println();

        m = MatrixOperation.removeColRow(i, j, matrix);
        m.show();


        System.out.println(MatrixOperation.det(matrix)); // -376
    }
}

class Matrix {
    private final double[][] matrix;
    private final int col;
    private final int row;

    public Matrix(int col, int row) {
        this.col = col;
        this.row = row;
        this.matrix = new double[col][row];
    }

    public Matrix(int col) {
        this(col, col);
    }

    public Matrix(double[][] matrix) {
        this(matrix.length, matrix[0].length);

        for (int i = 0; i < this.getCol(); i++) {
            for (int j = 0; j < this.getRow(); j++) {
                this.setElement(i, j, matrix[i][j]);
            }
        }
    }

    public void setElement(int i, int j, double value) {
        this.matrix[i][j] = value;
    }

    public double getElement(int i, int j) {
        return this.matrix[i][j];
    }

    public int getCol() {
        return this.col;
    }

    public int getRow() {
        return this.row;
    }

    public void show() {
        for (int i = 0; i < this.getCol(); i++) {
            for (int j = 0; j < this.getRow(); j++) {
                System.out.format("[%d][%d] = %3.2f\t\t", i + 1, j + 1, this.getElement(i, j));
            }
            System.out.println();
        }
    }
}

class MatrixOperation {
    /**
     * @param col    индекс строки которую нужно удалить
     * @param matrix исходная матрица
     * @return Новая матрица из которой удаляется строка
     */
    public static Matrix removeCol(int col, Matrix matrix) {
        // Новая матрица на одну строку меньше размером
        int M = matrix.getCol() - 1;
        int N = matrix.getRow();
        Matrix result = new Matrix(M, N);

        // Заполняем новую матрицу элементами
        for (int i = 0; i < col; i++) {
            for (int j = 0; j < N; j++) {
                result.setElement(i, j, matrix.getElement(i, j));
            }
        }
        // пропуская ту строку, которая удаляется
        for (int i = col; i < M; i++) {
            for (int j = 0; j < N; j++) {
                result.setElement(i, j, matrix.getElement(i + 1, j));
            }
        }

        return result;
    }

    /**
     * @param row    индекс столбца который нужно удалить
     * @param matrix исходная матрица
     * @return Новая матрица из которой удаляется столбец
     */
    public static Matrix removeRow(int row, Matrix matrix) {
        // Новая матрица на один столбец меньше размером
        int M = matrix.getCol();
        int N = matrix.getRow() - 1;
        Matrix result = new Matrix(M, N);

        // Заполняем новую матрицу элементами
        for (int i = 0; i < M; i++) {
            for (int j = 0; j < row; j++) {
                result.setElement(i, j, matrix.getElement(i, j));
            }
            // пропуская удаляемый столбец
            for (int j = row; j < N; j++) {
                result.setElement(i, j, matrix.getElement(i, j + 1));
            }
        }

        return result;
    }

    /**
     * @param col    индекс строки которую нужно удалить
     * @param row    индекс столбца который нужно удалить
     * @param matrix исходная матрица
     * @return Новая матрица из которой удаляется строка и стобец
     */
    public static Matrix removeColRow(int col, int row, Matrix matrix) {
        return removeCol(col, removeRow(row, matrix));
    }

    public static Matrix minor(int col, int row, Matrix matrix) {
        return removeColRow(col, row, matrix);
    }

    /**
     * @param matrix Матрица 2x2
     * @return Определитель матрицы 2x2
     */
    public static double det2(Matrix matrix) {
        if (2 != matrix.getRow() && 2 != matrix.getCol()) {
            throw new IllegalArgumentException("Матрица должна быть 2x2");
        }

        return matrix.getElement(0, 0) * matrix.getElement(1, 1) - matrix.getElement(0, 1) * matrix.getElement(1, 0);
    }

    /**
     * @param matrix Матрица NxN
     * @return Определитель матрицы
     */
    public static double det(Matrix matrix) {
        if (matrix.getRow() != matrix.getCol()) {
            throw new IllegalArgumentException("Матрица должна быть квадратной");
        }

        if (2 == matrix.getCol()) {
            return det2(matrix);
        }

        double sum = 0.0;
        int i = 0;
        for (int j = 0; j < matrix.getRow(); j++) {
            double minor = det(minor(i, j, matrix));
            double aij = Math.pow(-1, i + 1 + j + 1) * matrix.getElement(i, j);
            //echo sprintf("%3.2f + %3.2f * %3.2f\n", $sum, $aij, $minor);
            sum += aij * minor;
        }

        return sum;
    }
}

