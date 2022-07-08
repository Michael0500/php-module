<?php

include_once 'Lexer.php';
include_once 'Token.php';
include_once 'TokenType.php';

$input = '2+2*5/10';
$lexer = new Lexer($input);
echo $lexer;
