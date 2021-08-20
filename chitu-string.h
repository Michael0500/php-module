#ifndef CHITU_STRING_HEADER
#define CHITU_STRING_HEADER

#include "chitu-common.h"

// Прототипы функций
chitu_string chitu_empty_string();
char* chitu_get_raw_string(chitu_val z);
chitu_string chitu_set_raw_string(chitu_val z, char* str);
chitu_string chitu_get_string(chitu_val z);
chitu_val chitu_set_string(chitu_val z, chitu_string s);

#endif // CHITU_STRING_HEADER
