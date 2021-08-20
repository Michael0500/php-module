#ifndef CHITU_COMMON_HEADER
#define CHITU_COMMON_HEADER

#include <stdio.h>
#include <stdint.h>
#include <stdbool.h>
#include <stdlib.h>
#include <string.h>
#include <math.h>

// Макросы для добавления префикса "chitu_" к какому-либо значению
#define CHITU_PREFIX(prefix, value) prefix##_##value
#define CHI(z) CHITU_PREFIX(CHITU, z)
#define chi(z) CHITU_PREFIX(chitu, z)

#define CHITU_TRUE (true)
#define CHITU_FALSE (false)

// Получение типа переменной
#define CHITU_GET_TYPE(z) (z).type

// Получение значения переменной определенного типа
#define CHITU_BOOL_VAL(z) (z).val.boolVal
#define CHITU_FLOAT_VAL(z) (z).val.floatVal
#define CHITU_INT_VAL(z) (z).val.intVal
#define CHITU_STRING_VAL(z) (z).val.strVal

// Возможные типы данных
typedef enum CHI(type) {
	TYPE_BOOL,	
	TYPE_FLOAT,
	TYPE_INT,	
	TYPE_STRING
} chitu_type;

// Тип логического значения
typedef struct CHI(bool) {
	bool b;
} chitu_bool;

// Тип вещественного значения
typedef union CHI(float) {
	float f; // не использовать!
	double d;
} chitu_float;

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

// Тип строкового значения
typedef struct CHI(string) {
	char *str;
	int64_t length;
} chitu_string;

// Возможные спецификаторы видимости
typedef enum CHI(attribute) {
	ATTR_PUBLIC,
	ATTR_PRIVATE
} chitu_attribute;

typedef struct CHI(val) {
	chitu_type type; // тип значения
	union {
		chitu_bool boolVal;
		chitu_float floatVal;
		chitu_int intVal;
		chitu_string strVal;
	} val;
	chitu_attribute attribute; // видимость
	int8_t is_const; // является ли переменная константой (0-нет, 1-да является)
} chitu_val;

//----------------------------------------------------------------------------
// Прототипы функций
chitu_val chitu_init_val(chitu_type t);
chitu_val chitu_set_const(chitu_val z);
void chitu_dump(chitu_val z);

#include "chitu-bool.h"
#include "chitu-float.h"
#include "chitu-int.h"
#include "chitu-string.h"

#endif // CHITU_COMMON_HEADER
