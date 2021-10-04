%{
#include <stdio.h>
%}

%option noyywrap

LNUM	[0-9]+(_[0-9]+)*
DNUM	({LNUM}?"."{LNUM})|({LNUM}"."{LNUM}?)
EXPONENT_DNUM	(({LNUM}|{DNUM})[eE][+-]?{LNUM})
HNUM	"0x"[0-9a-fA-F]+(_[0-9a-fA-F]+)*
BNUM	"0b"[01]+(_[01]+)*
ONUM	"0o"[0-7]+(_[0-7]+)*
VARIABLE	"$"[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*
LABEL	[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*
WHITESPACE [ \n\r\t]+
TABS_AND_SPACES [ \t]*
TOKENS [;:,.|^&+-*/=%!~$<>?@]
ANY_CHAR [^]
NEWLINE ("\r"|"\n"|"\r\n")

%x S_COMMENT
%x S_STRING

%%

{LNUM}				{ printf("LNUM(%s)", yytext); }
{DNUM}				{ printf("DNUM(%s)", yytext); }
{EXPONENT_DNUM}		{ printf("EXPONENT_DNUM(%s)", yytext); }
{HNUM}				{ printf("HNUM(%s)", yytext); }
{BNUM}				{ printf("BNUM(%s)", yytext); }
{ONUM}				{ printf("ONUM(%s)", yytext); }
{VARIABLE}			{ printf("VARIABLE(%s)", yytext); }
{LABEL}				{ printf("LABEL(%s)", yytext); }
"/*"				{ BEGIN(S_COMMENT); printf("/* COMMENT_BEGIN"); }

<S_COMMENT>{
"*/"				{ BEGIN(INITIAL); printf(" COMMENT_END */"); }
.					;
}

"\""				{ BEGIN(S_STRING); printf("STRING(\""); }

<S_STRING>{
"\""				{ BEGIN(INITIAL); printf("\")"); }
[^"]*				{ printf("%s", yytext); }
}

%%
int main(int argc, char *argv[]) {
	yylex();
	return 0;
}
