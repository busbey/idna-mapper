#include "idn_mapper.h"

int main(int argc, char** argv)
{
	int returnVal = -1;
	char* utf8buf = NULL;
	char* punybuf = NULL;
	char* inbuf = malloc(DEFAULT_BUFFER_SIZE);
	size_t len = 0;
	size_t lastlen = 0;
	char* locale = setlocale (LC_ALL, "en_US.UTF-8");
	if(0 != setvbuf(stdin, NULL, _IONBF, 0) ||
	   0 != setvbuf(stdout, NULL, _IONBF, 0)
	)
	{
		fprintf(stderr, "IDN mapper: ERROR, failed to set unbuffered mode.\n");
		fflush(stderr);
		goto cleanup;
	}
	if(NULL != locale)
	{
		fprintf(stderr, "IDN mapper: NOTE, started. locale is '%s'\n", locale);
	}
	else
	{
		fprintf(stderr, "IDN mapper: WARNING, started but could not set locale.\n");
	}
	fflush(stderr);

	while(!feof(stdin) && !ferror(stdin))
	{
		char* input = NULL;
		input = fgets(inbuf, DEFAULT_BUFFER_SIZE, stdin);
		if(NULL == input)
		{
			fprintf(stderr, "IDN mapper: ERROR, error reading stdin\n");
			fflush(stderr);
			goto cleanup;
		}
		len = strnlen(input, DEFAULT_BUFFER_SIZE);
		punybuf = realloc(punybuf, lastlen + len);
		memmove(punybuf + lastlen, input, len);
		lastlen += len;
		if('\n' == punybuf[lastlen - 1])
		{
			int status = IDNA_SUCCESS;
			punybuf[lastlen - 1] = '\0';
			status = idna_to_unicode_lzlz(punybuf, &utf8buf, 0);
			if(IDNA_SUCCESS == status)
			{
				size_t last = 0;
				last = strnlen(utf8buf, lastlen);
				utf8buf[last] = '\n';
				fwrite(utf8buf, 1, last+1, stdout);
				free(utf8buf);
				utf8buf = NULL;
			}
			else
			{
				fprintf(stderr, "IDN mapper: WARNING, failed to decode '%s'.  error returned by libidn: '%s'\n", punybuf, idna_strerror(status));
				fflush(stderr);
				fwrite("NULL\n", 1, 5, stdout);
			}
			free(punybuf);
			punybuf = NULL;
			lastlen = 0;
		}
	}
	returnVal = ferror(stdin);
cleanup:
	free(inbuf);
	if(NULL != punybuf)
	{
		free(punybuf);
	}
	return returnVal;
}
