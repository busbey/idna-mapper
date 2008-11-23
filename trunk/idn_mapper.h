#include <stdlib.h>
#include <stdio.h>
#include <fcntl.h>
#include <string.h>
#include <locale.h>
#include <idna.h>
#define DEFAULT_BUFFER_SIZE 128
#ifndef strnlen
	#define strnlen(str,max) strlen(str)
#endif
