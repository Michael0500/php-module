#ifndef CHITU_BOOL_HEADER
#define CHITU_BOOL_HEADER

#include "chitu-common.h"

// Прототипы функций
chitu_bool chitu_empty_bool();
bool chitu_get_raw_bool(chitu_val z);
chitu_bool chitu_set_raw_bool(chitu_val z, bool b);
chitu_bool chitu_get_bool(chitu_val z);
chitu_val chitu_set_bool(chitu_val z, chitu_bool b);

#endif // CHITU_BOOL_HEADER
