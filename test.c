#include "chitu-common.h"

//----------------------------------------------------------------------------
int main(int argc, char *argv[])
{
	chitu_val a;
	chitu_type t = TYPE_FLOAT;
	
	printf("********* Chitushka Language! *********\n");
	a = chitu_init_val(t);
	a = chitu_set_const(a);
	a.val.floatVal.d = 2.5;
	chitu_dump(a);
	printf("\n");

	chitu_val b = chitu_set_bool(a, chitu_get_bool(a));
	chitu_dump(b);
	printf("\n");
	
	chitu_val c = chitu_set_string(a, chitu_get_string(a));
	chitu_dump(c);
	printf("\n");
	
	chitu_val d = chitu_set_int(a, chitu_get_int(a));
	chitu_dump(d);
	printf("\n");	
	
	chitu_val e = chitu_set_float(a, chitu_get_float(a));
	chitu_dump(e);
	printf("\n");

	return 0;
}
