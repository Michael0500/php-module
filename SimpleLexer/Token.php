<?php


class Token
{
    private TokenType $type;
    private string $text;

    /**
     * Token constructor.
     * @param TokenType $type
     * @param string $text
     */
    public function __construct(TokenType $type, string $text)
    {
        $this->type = $type;
        $this->text = $text;
    }

    /**
     * @return TokenType
     */
    public function getType(): TokenType
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "{$this->getType()} : {$this->getText()}";
    }
}
