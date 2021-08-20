#ifndef CHITU_INT_HEADER
#define CHITU_INT_HEADER

#include "chitu-common.h"

// Прототипы функций
chitu_int chitu_empty_int();
int64_t chitu_get_raw_int(chitu_val z);
chitu_int chitu_set_raw_int(chitu_val z, int64_t i);
chitu_int chitu_get_int(chitu_val z);
chitu_val chitu_set_int(chitu_val z, chitu_int i);

#endif // CHITU_INT_HEADER
