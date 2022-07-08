<?php


class TokenType
{
    private string $type;

    private const TOKEN_TYPES = [
        'PLUS' => 'PLUS',
        'MINUS' => 'MINUS',
        'MULTIPLY' => 'MULTIPLY',
        'DIVIDE' => 'DIVIDE',
        'NUMBER' => 'NUMBER',
    ];

    /**
     * TokenType constructor.
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = self::TOKEN_TYPES[$type];
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getType();
    }
}
