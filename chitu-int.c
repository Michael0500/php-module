#include "chitu-int.h"

// Возвращает значение chitu_int равное нулю
chitu_int chitu_empty_int() {
	chitu_int result;
	
	result.i64 = 0;
	
	return result;
}

// Устанавливает значение int и возвращает новое его
chitu_int chitu_set_raw_int(chitu_val z, int64_t i) {
	chitu_int result;
	
	result = CHITU_INT_VAL(z);
	result.i64 = i; 
	
	return result;
}

// Возвращает значение int из chitu_val
int64_t chitu_get_raw_int(chitu_val z) {
	int64_t result;
	
	result = CHITU_INT_VAL(z).i64;
	
	return result;
}

// Возвращает chitu_int из chitu_val приводя тип
chitu_int chitu_get_int(chitu_val z) {
	chitu_int result;
	int64_t i;
	
	switch (CHITU_GET_TYPE(z)) {
		case TYPE_BOOL:
			if (CHITU_TRUE == chitu_get_raw_bool(z)) {
				i = 1;
			} else {
				i = 0;
			}
			break;	
		case TYPE_FLOAT: i = (int64_t)chitu_get_raw_float(z); break;
		case TYPE_INT: i = chitu_get_raw_int(z); break;
		case TYPE_STRING: i = atol(chitu_get_raw_string(z)); break;
		default: i = 0; break;
	}
	result = chitu_set_raw_int(z, i);
	
	return result;
}

//
chitu_val chitu_set_int(chitu_val z, chitu_int i) {
	chitu_val result;
	
	result = z;
	result.type = TYPE_INT;
	result.val.intVal = i;
	
	return result;
}
