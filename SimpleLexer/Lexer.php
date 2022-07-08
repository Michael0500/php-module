<?php


class Lexer
{
    private const OPERATOR_CHARS = ['+', '-', '*', '/'];
    private const OPERATOR_TOKENS = ['PLUS', 'MINUS', 'MULTIPLY', 'DIVIDE'];
    private string $input;
    private int $length;
    private int $position;
    private array $tokens;

    /**
     * Lexer constructor.
     * @param string $input
     */
    public function __construct(string $input)
    {
        $this->input = $input;
        $this->length = strlen($input);
        $this->tokens = [];
        $this->position = 0;
    }

    /**
     * @return Token[]
     */
    public function getTokens(): array
    {
        while ($this->position < $this->length) {
            $current = $this->peek(0);
            if (ctype_digit($current)) {
                $this->tokenNumber();
            } elseif (in_array($current, self::OPERATOR_CHARS)) {
                $this->tokenOperator();
            } else { // whitespaces
                $this->next();
            }
        }

        return $this->tokens;
    }

    /**
     * Возвращает следующий входной символ
     * @return string
     */
    private function next(): string
    {
        $this->position++;

        return $this->peek(0);
    }

    /***
     * Возвращает символ находящийся на $relativePosition позиции относительно текущего
     * @param int $relativePosition
     * @return string
     */
    private function peek(int $relativePosition): string
    {
        $position = $this->position + $relativePosition;
        if ($position >= $this->length) {
            return '\0';
        }

        return $this->input[$position];
    }

    /**
     * Добавить новый прочитанный токен
     * @param TokenType $type
     * @param string $text
     */
    private function addToken(TokenType $type, string $text): void
    {
        $this->tokens[] = new Token($type, $text);
    }

    /**
     * Добавляет токен числа в список токенов
     */
    private function tokenNumber()
    {
        $current = $this->peek(0);
        $number = '';
        while (ctype_digit($current)) {
            $number .= $current;
            $current = $this->next();
        }
        $this->addToken(new TokenType('NUMBER'), $number);
    }

    /**
     * Добавляет токен оператора в список токенов
     */
    private function tokenOperator()
    {
        // получаем позицию прочитанного оператора в массиве операторов
        $position = array_search($this->peek(0), self::OPERATOR_CHARS, true);
        // добавляем соответствующий токен из массива токенов операторов
        $this->addToken(new TokenType(self::OPERATOR_TOKENS[$position]), self::OPERATOR_CHARS[$position]);
        $this->next();
    }

    /**
     * Возвращаем все токены в текстовом виде
     * @return string
     */
    public function __toString()
    {
        $result = '';
        foreach ($this->getTokens() as $token) {
            $result .= $token . PHP_EOL;
        }

        return $result;
    }
}
