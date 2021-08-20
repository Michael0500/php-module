#ifndef CHITU_FLOAT_HEADER
#define CHITU_FLOAT_HEADER

#include "chitu-common.h"

// Прототипы функций
chitu_float chitu_empty_float();
double chitu_get_raw_float(chitu_val z);
chitu_float chitu_set_raw_float(chitu_val z, double d);
chitu_float chitu_get_float(chitu_val z);
chitu_val chitu_set_float(chitu_val z, chitu_float f);

#endif // CHITU_FLOAT_HEADER
