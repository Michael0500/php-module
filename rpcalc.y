%{
// Транслятор обратной польской нотации

// Сборка:
// bison rpcalc.y
// gcc rpcalc.tab.c --std=c99 -Wall -o rpcalc.exe
// rpcalc.exe

#include <ctype.h>
#include <stdio.h>
#define YYSTYPE double

int yylex(void);
void yyerror(const char *s);
%}

%token NUM

%%

input:	/* ---- */
	| input line
;

line:	'\n'
	| exp '\n'	{ printf("\t%.3f\n", $1); } 
;

exp:	NUM		{ $$ = $1; }
	| exp exp '+' 	{ $$ = $1 + $2; }
	| exp exp '-' 	{ $$ = $1 - $2; }
	| exp exp '*' 	{ $$ = $1 * $2; }
	| exp exp '/' 	{ $$ = $1 / $2; }
;

%%

int yylex(void) {
	int c;

	while ((c=getchar()) == ' ' || '\t' == c)
	;
	if ('.' == c || isdigit(c)) {
		ungetc(c, stdin);
		scanf("%lf", &yylval);

		return NUM;
	}
	if (EOF == c) return 0;

	return c;
}

void yyerror(const char *s) {
	printf("%s\n", s);
}

int main(int argc, char *argv[]) {
	return yyparse();
}
