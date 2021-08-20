#include "chitu-string.h"

// 
chitu_string chitu_empty_string() {
	chitu_string result;
	
	result.str = NULL;
	result.length = 0;
	
	return result;
}

// 
char* chitu_get_raw_string(chitu_val z) {
	char* result;
	
	result = CHITU_STRING_VAL(z).str;
	
	return result;
}

//
chitu_string chitu_set_raw_string(chitu_val z, char* str) {
	chitu_string result;
	
	result = CHITU_STRING_VAL(z);
	result.str = str;
	
	return result;
}

// 
chitu_string chitu_get_string(chitu_val z) {
	chitu_string result;
	char* str = malloc(100);
	
	switch (CHITU_GET_TYPE(z)) {
		case TYPE_BOOL:
			if (CHITU_TRUE == chitu_get_raw_bool(z)) {
				str = "1";
			} else {
				str = "0";
			}
			break;
		case TYPE_FLOAT: sprintf(str, "%f", chitu_get_raw_float(z)); break;
		case TYPE_INT: sprintf(str, "%d", chitu_get_raw_int(z)); break;
		case TYPE_STRING: str = chitu_get_raw_string(z); break;
		default: str = ""; break;
	}
	result = chitu_set_raw_string(z, str);

	return result;
}

//
chitu_val chitu_set_string(chitu_val z, chitu_string s) {
	chitu_val result;
	
	result = z;
	result.type = TYPE_STRING;
	result.val.strVal = s;
	
	return result;
}
