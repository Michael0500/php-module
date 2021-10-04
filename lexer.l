%{
#include <stdio.h>

int stringLength=0;
%}

%option noyywrap

LNUM	[0-9]+(_[0-9]+)*
DNUM	({LNUM}?"."{LNUM})|({LNUM}"."{LNUM}?)
EXPONENT_DNUM	(({LNUM}|{DNUM})[eE][+-]?{LNUM})
HNUM	"0x"[0-9a-fA-F]+(_[0-9a-fA-F]+)*
BNUM	"0b"[01]+(_[01]+)*
ONUM	"0o"[0-7]+(_[0-7]+)*
VARIABLE	"$"[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*
IDENTIFIER	[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*
WHITESPACE [ \n\r\t]+
TABS_AND_SPACES [ \t]*
TOKENS [;:,.|^&+-*/=%!~$<>?@]
ANY_CHAR [^]
NEWLINE ("\r"|"\n"|"\r\n"|"\n\r")

%x S_COMMENT
%x S_STRING

%%

"fn"		{ printf(" FN "); }
"function" 	{ printf(" FN "); }
"var" 		{ printf(" VARIABLE "); }
"const" 	{ printf(" CONSTANT "); }
"return" 	{ printf(" RETURN "); }
"class" 	{ printf(" CLASS "); }
"extends" 	{ printf(" EXTENDS "); }
"interface" { printf(" INTERFACE "); }
"implements" { printf( "IMPLEMENTS "); }
"string" 	{ printf(" STRING_TYPE "); }
"float" 	{ printf(" FLOAT_TYPE "); }
"int" 		{ printf(" INT_TYPE "); }
"bool" 		{ printf(" BOOL_TYPE "); }
"true" 		{ printf(" TRUE_CONST "); }
"false" 	{ printf(" FALSE_CONST "); }
"null" 		{ printf(" NULL_CONST "); }
"if" 		{ printf(" IF "); }
"else" 		{ printf(" ELSE "); }
"elseif" 	{ printf(" ELSE_IF "); }
"while" 	{ printf(" WHILE "); }
"for" 		{ printf(" FOR "); }
"foreach" 	{ printf(" FOREACH "); }
"break" 	{ printf(" BREAK "); }
"continue" 	{ printf(" CONTINUE "); }
"switch" 	{ printf(" SWITCH "); }
"case" 		{ printf(" CASE "); }
"match" 	{ printf(" MATCH "); }

"->" 	{ printf(" OBJECT_OP "); }
"==" 	{ printf(" EQUAL_OP "); }
"!=" 	{ printf(" NOT_EQUAL_OP "); }
"<=" 	{ printf(" LTE_OP "); }
">=" 	{ printf(" GTE_OP "); }
"<" 	{ printf(" LT_OP "); }
">" 	{ printf(" GT_OP "); }
"<=>" 	{ printf(" SPACESHIP_OP "); }
"++" 	{ printf(" INCREMENT_OP "); }
"--" 	{ printf(" DECREMENT_OP "); }
"+=" 	{ printf(" PLUS_EQUAL_OP "); }
"-=" 	{ printf(" MINUS_EQUAL_OP "); }
"*=" 	{ printf(" MULT_EQUAL_OP "); }
"/=" 	{ printf(" DIV_EQUAL_OP "); }
"%=" 	{ printf(" MOD_EQUAL_OP "); }

{LNUM}				{ printf("LNUM(%s)", yytext); }
{DNUM}				{ printf("DNUM(%s)", yytext); }
{EXPONENT_DNUM}		{ printf("EXPONENT_DNUM(%s)", yytext); }
{HNUM}				{ printf("HNUM(%s)", yytext); }
{BNUM}				{ printf("BNUM(%s)", yytext); }
{ONUM}				{ printf("ONUM(%s)", yytext); }
{VARIABLE}			{ printf("VARIABLE(%s)", yytext); }
{IDENTIFIER}		{ printf("IDENTIFIER(%s)", yytext); }

"/*"				{ BEGIN(S_COMMENT); printf("/* COMMENT_BEGIN"); }
<S_COMMENT>{
"*/"				{ BEGIN(INITIAL); printf(" COMMENT_END */"); }
.					; /* удаляем комментарий */
}

"\""				{ BEGIN(S_STRING); printf("STRING(\""); stringLength=0; } /* помечаем о начале строки и обннуляем длину строки в переменной stringLength */
<S_STRING>{
["]					{ BEGIN(INITIAL); printf("\")[%d]", stringLength); } /* помечаем о конце строки и выводим длину строки */
[^"]*				{ printf("%s", yytext); stringLength += yyleng; } /* выводим саму строку и увеличиваем длину в переменной stringLength */
}

%%
int main(int argc, char *argv[]) {
	yylex();
	return 0;
}
