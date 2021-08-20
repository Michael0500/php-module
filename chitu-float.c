#include "chitu-float.h"

// Возвращает значение chitu_float равное нулю
chitu_float chitu_empty_float() {
	chitu_float result;
	
	result.d = 0.0;
	
	return result;
}

// Устанавливает значение double и возвращает новое его
chitu_float chitu_set_raw_float(chitu_val z, double d) {
	chitu_float result;
	
	result = CHITU_FLOAT_VAL(z);
	result.d = d; 
	
	return result;
}

// Возвращает значение double из chitu_val
double chitu_get_raw_float(chitu_val z) {
	double result;
	
	result = CHITU_FLOAT_VAL(z).d;
	
	return result;
}

// Возвращает chitu_float из chitu_val приводя тип
chitu_float chitu_get_float(chitu_val z) {
	chitu_float result;
	double d;
	
	switch (CHITU_GET_TYPE(z)) {
		case TYPE_BOOL:
			if (CHITU_TRUE == chitu_get_raw_bool(z)) {
				d = 1.0;
			} else {
				d = 0.0;
			}
			break;	
		case TYPE_FLOAT: d = chitu_get_raw_float(z); break;
		case TYPE_INT: d = (double)chitu_get_raw_int(z); break;
		case TYPE_STRING: d = atof(chitu_get_raw_string(z)); break;
		default: d = 0.0; break;
	}
	result = chitu_set_raw_float(z, d);
	
	return result;
}

//
chitu_val chitu_set_float(chitu_val z, chitu_float f) {
	chitu_val result;
	
	result = z;
	result.type = TYPE_FLOAT;
	result.val.floatVal = f;
	
	return result;
}
