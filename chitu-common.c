#include "chitu-common.h"


// Создает пустое значение chitu_val типа chitu_type
chitu_val chitu_init_val(chitu_type t) {
	chitu_val result;
	// Инициализируем пустые значения
	chitu_bool b = chitu_empty_bool();
	chitu_float f = chitu_empty_float();
	chitu_int i = chitu_empty_int();
	chitu_string s = chitu_empty_string();
	// По умолчанию: Публичная
	chitu_attribute attr = ATTR_PUBLIC;
	// По умолчанию: Не константа
	int8_t is_const = 0;
	
	result.type = t;
	result.attribute = attr;
	result.is_const = is_const;
	
		switch (t) {
			case TYPE_BOOL: result.val.boolVal = b; break;	
			case TYPE_FLOAT: result.val.floatVal = f; break;
			case TYPE_INT: result.val.intVal = i; break;
			case TYPE_STRING: result.val.strVal = s; break;
			default: result.val.intVal = i; break;
	}
	
	return result;
}

// Устанавливает значение константой и возвращает его
chitu_val chitu_set_const(chitu_val z) {
	chitu_val result;
	
	result = z;
	result.is_const = 1;
	
	return result;
}

// 
void chitu_dump(chitu_val z) {	
	switch (CHITU_GET_TYPE(z)) {
		case TYPE_BOOL: printf("(bool) = %s\n", CHITU_TRUE == chitu_get_raw_bool(z) ? "true" : "false"); break;	
		case TYPE_FLOAT: printf("(float) = %.5f\n", chitu_get_raw_float(z)); break;
		case TYPE_INT: printf("(int) = %d\n", chitu_get_raw_int(z)); break;
		case TYPE_STRING: printf("(string) = %s\n", chitu_get_raw_string(z)); break;
		default: printf("(int) = %d\n", chitu_get_raw_int(z)); break;
	}
	
	if (0 == z.is_const) {
		printf("type = %s\n", "VAR");
	} else {
		printf("type = %s\n", "CONST");
	}
	
	switch (z.attribute) {
		case ATTR_PUBLIC: printf("attribute = %s\n", "PUBLIC"); break;	
		case ATTR_PRIVATE: printf("attribute = %s\n", "PRIVATE"); break;
		default: printf("attribute = %s\n", "PUBLIC"); break;
	}
	
	return;
}
