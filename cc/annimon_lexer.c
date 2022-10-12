#include <ctype.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

enum TokenType {
	NUMBER,
	
	PLUS,
	MINUS,
	MUL,
	DIV,
	
	T_EOF
};

//-----------------------------------------------------------------------------
struct Token lex_newToken(enum TokenType, char *);
enum TokenType lex_getType(struct Token *);
char* lex_getText(struct Token *);
void lex_setType(struct Token *, enum TokenType);
void lex_setText(struct Token *, char *);

struct Lexer *lex_newLexer(char *);
struct Token *lex_tokenize(struct Lexer *);
static void lex_tokenizeNumber(struct Lexer *);
static void lex_tokenizeOperator(struct Lexer *);
static void lex_addToken(struct Lexer *, enum TokenType, char *);
static char lex_peek(struct Lexer *, int);
static char lex_next(struct Lexer *);
//-----------------------------------------------------------------------------

struct Token {
	enum TokenType type;
	char *text;
};

struct Token lex_newToken(enum TokenType type, char *text) {
	struct Token token;
	
	token.type = type;
	token.text = text;
	
	return token;
}

enum TokenType lex_getType(struct Token *token) {
	return token->type;
}

char* lex_getText(struct Token *token) {
	return token->text;
}

void lex_setType(struct Token *token, enum TokenType type) {
	token->type = type;
}

void lex_setText(struct Token *token, char *text) {
	token->text = text;
}

//-----------------------------------------------------------------------------
#define TOKEN_LEN 1000

struct Lexer {
	char *input;
	int length;
	struct Token *tokens;
	int tokenCount;
	int pos;
};

struct Lexer *lex_newLexer(char *input) {
	struct Lexer *lexer;
	
	lexer = (struct Lexer *) malloc(sizeof(struct Lexer));
	
	lexer->input = input;
	lexer->length = strlen(input);
	lexer->tokens = (struct Token *) malloc(sizeof(struct Token) * TOKEN_LEN);
	lexer->tokenCount = 0;
	lexer->pos = 0;
	
	
	return lexer;
}

struct Token *lex_tokenize(struct Lexer *lexer) {
	while (lexer->pos < lexer->length) {
		const char current = lex_peek(lexer, 0);
		
		if (isdigit(current)) { lex_tokenizeNumber(lexer); }
		else if ('+' == current || '-' == current || '*' == current || '/' == current) { lex_tokenizeOperator(lexer); }
		else { /* whitespaces */ lex_next(lexer); }
	}
	
	return lexer->tokens;
}

static void lex_tokenizeNumber(struct Lexer *lexer) {
	char current = lex_peek(lexer, 0);
	char buffer[255];
	int i = 0;
	
	while (isdigit(current)) {
		buffer[i] = current;
		i++;
		current = lex_next(lexer);
	}
	buffer[i] = '\0';
	
	printf("-__-number: %s\n", buffer);
	
	lex_addToken(lexer, NUMBER, buffer);
}

static void lex_tokenizeOperator(struct Lexer *lexer) {
	const char current = lex_peek(lexer, 0);
	enum TokenType type;
	char buffer[2];
	buffer[0] = current;
	buffer[1] = '\0';
	
	switch (current) {
		case '+': type = PLUS; break;
		case '-': type = MINUS; break;
		case '*': type = MUL; break;
		case '/': type = DIV; break;
		default: {
			puts("Error: Invalid operator");
			exit(0);
			
			return;
		}
	}
	
	printf("-__-operator: %s\n", buffer);
	
	lex_addToken(lexer, type, buffer);
	lex_next(lexer);
}

static void lex_addToken(struct Lexer *lexer, enum TokenType type, char *text) {
	struct Token newToken;
	
	if (lexer->tokenCount >= TOKEN_LEN) {
		printf("Error: TOKEN LENGTH >= %d", lexer->tokenCount);
		exit(0);
	}
	
	printf("------added: %s\n", text);
	
	newToken = lex_newToken(type, text);
	lexer->tokens[lexer->tokenCount] = newToken;
	lexer->tokenCount++;
}

static char lex_peek(struct Lexer *lexer, int relativePosition) {	
	int position = lexer->pos + relativePosition;
	
	if (position >= lexer->length) {
		return '\0';
	}
	
	return lexer->input[position];
}

static char lex_next(struct Lexer *lexer) {
	lexer->pos++;

	return lex_peek(lexer, 0);
}
//-----------------------------------------------------------------------------

int main(int argv, char *argc[]) {
	char input[] = "1 + 2 + 3-4";
	struct Lexer *lexer = lex_newLexer(input);
	struct Token *tokens = lex_tokenize(lexer);
	
	for (int i=0; i<lexer->tokenCount; i++) {
		printf("%4d: %s\n", (tokens+i)->type, (tokens+i)->text);
	}
	
	free(tokens);
	free(lexer);
	
	return 0;
}
