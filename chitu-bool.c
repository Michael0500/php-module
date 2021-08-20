#include "chitu-bool.h"

// Реализация функций
chitu_bool chitu_empty_bool() {
	chitu_bool result;
	
	result.b = CHITU_FALSE;
	
	return result;
}

// Получаем логическое (bool) значение из chitu_val
bool chitu_get_raw_bool(chitu_val z) {
	bool result;
	
	result = CHITU_BOOL_VAL(z).b;
	
	return result;
}

// 
chitu_bool chitu_set_raw_bool(chitu_val z, bool b) {
	chitu_bool result;
	
	result = CHITU_BOOL_VAL(z);
	result.b = b;
	
	return result;
}

// 
chitu_bool chitu_get_bool(chitu_val z) {
	chitu_bool result;
	bool b;
	double d;
	int64_t i;
	
	switch (CHITU_GET_TYPE(z)) {
		case TYPE_BOOL: b = chitu_get_raw_bool(z); break;	
		case TYPE_FLOAT:
			d = chitu_get_raw_float(z);
			// надо заменить константу 0.000001 на машинный ноль
			if (fabs(d) < 0.000001) {
				b = CHITU_FALSE;
			} else {
				b = CHITU_TRUE;
			}
			break;
		case TYPE_INT:
			i = chitu_get_raw_int(z);
			if (0 == i) {
				b = CHITU_FALSE;
			} else {
				b = CHITU_TRUE;
			}			
			break;
		case TYPE_STRING: b = CHITU_FALSE; break;
		default: b = CHITU_FALSE; break;
	}
	result = chitu_set_raw_bool(z, b);
	
	return result;	
}

//
chitu_val chitu_set_bool(chitu_val z, chitu_bool b) {
	chitu_val result;
	
	result = z;
	result.type = TYPE_BOOL;
	result.val.boolVal = b;
	
	return result;
}
