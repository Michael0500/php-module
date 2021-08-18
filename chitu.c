#include <stdio.h>
#include <stdint.h>
#include <stdbool.h>

// Макросы для добавления префикса "chitu_" к какому-либо значению
#define CHITU_PREFIX(prefix, value) prefix##_##value
#define CHI(z) CHITU_PREFIX(CHITU, z)
#define chi(z) CHITU_PREFIX(chitu, z)

#define CHITU_TRUE true
#define CHITU_FALSE false

//----------------------------------------------------------------------------
// Возможные типы данных
typedef enum CHI(type) {
	TYPE_INT,
	TYPE_FLOAT,
	TYPE_BOOL,
	TYPE_STRING
} chitu_type;

// Тип целочисленного значения
typedef union CHI(int) {
	// signed
	int8_t i8;
	int16_t i16;
	int32_t i32;
	int64_t i64;
	// unsigned
	uint8_t ui8;
	uint16_t ui16;
	uint32_t ui32;
	uint64_t ui64;
} chitu_int;

// Тип вещественного значения
typedef union CHI(float) {
	float f; // не использовать!
	double d;
} chitu_float;

// Тип логического значения
typedef struct CHI(bool) {
	bool b;
} chitu_bool;

// Тип строкового значения
typedef struct CHI(string) {
	char *str;
	int32_t length;
} chitu_string;

typedef struct CHI(val) {
	chitu_type type;
	union {
		chitu_int intVal;
		chitu_float floatVal;
		chitu_bool boolVal;
		chitu_string strVal;
	} val;
} chitu_val;

//----------------------------------------------------------------------------
// Прототипы функций
chitu_int chitu_empty_int();
chitu_float chitu_empty_float();
chitu_bool chitu_empty_bool();
chitu_string chitu_empty_string();

//----------------------------------------------------------------------------
// Реализация функций
chitu_int chitu_empty_int() {
	chitu_int v;
	
	v.i32 = 0;
	
	return v;
}

chitu_float chitu_empty_float() {
	chitu_float v;
	
	v.d = 0.0;
	
	return v;
}

chitu_bool chitu_empty_bool() {
	chitu_bool v;
	
	v.b = CHITU_FALSE;
	
	return v;
}

chitu_string chitu_empty_string() {
	chitu_string v;
	
	v.str = NULL;
	v.length = 0;
	
	return v;
}

//----------------------------------------------------------------------------
int main(int argc, char *argv[])
{
	chitu_val a;
	
	printf("********* Chitushka Language! *********\n");
	
	printf("sizeof(a.val) = %lld bytes", sizeof(a.val));

	return 0;
}
