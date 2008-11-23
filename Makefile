CFLAGS=`pkg-config libidn --cflags`
LIBS=`pkg-config libidn --libs`
all: test 
test: debug
	php test.php --idn test.txt --expected expected.txt --mapper ./idn_mapper_debug
mapper: idn_mapper.c idn_mapper.h
	gcc -O3 -Wall -pedantic ${CFLAGS} idn_mapper.c ${LIBS} -o idn_mapper
debug: idn_mapper.c idn_mapper.h
	gcc -g -O0 -Wall -pedantic ${CFLAGS} idn_mapper.c ${LIBS} -o idn_mapper_debug
clean:
	rm -rf idn_mapper idn_mapper_debug idn_mapper.dSYM idn_mapper_debug.dSYM error-log.txt
