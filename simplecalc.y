%{
// Простой калькулятор целых чисел

// Сборка:
// bison simplecalc.y
// gcc simplecalc.tab.c --std=c99 -Wall -o simplecalc.exe
// simplecalc.exe

#include <stdio.h>

int yylex(void);
void yyerror(const char *s);
%}

%%
input:	/* --- */
	| input line
;
line: '\n'
	| exp '\n'	{ printf("\t%d\n", $$); }
;
exp:	term
	| exp '+' term	{ $$ = $1 + $3; }
	| exp '-' term	{ $$ = $1 - $3; }
;
term:	num
	| term '*' num	{ $$ = $1 * $3; }
	| term '/' num	{ $$ = $1 / $3; }
;
num:	digit
	| num digit	{ $$ = $1*10+$2; }
;
digit: '0'	{ $$ = 0; }
	| '1'	{ $$ = 1; }
	| '2'	{ $$ = 2; }
	| '3'	{ $$ = 3; }
	| '4'	{ $$ = 4; }
	| '5'	{ $$ = 5; }
	| '6'	{ $$ = 6; }
	| '7'	{ $$ = 7; }
	| '8'	{ $$ = 8; }
	| '9'	{ $$ = 9; }
;
%%

int yylex(void) {
	return getc(stdin);
}

void yyerror(const char *s) {
	printf("%s\n", s);
}

int main(int argc, char *argv[]) {
	return yyparse();
}
