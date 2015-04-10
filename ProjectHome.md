a utility program for use with mod\_rewrite's external program type in the RewriteMap directive.

uses the idna functions that are a part of GNU libidn to take punycode encoded international domain names and turn them into utf-8 strings.

target use is for wild-card hosting of sites that make use of internationalized domain names.

ex:
> you host several sites on example.com, where each maps to a directory in your web root

> you link to 'http://☃.example.com' and the browser maps this to http://xn--n3h.example.com/

> with this mapper and enclosed example files you can then make the following translation:

> http://xn--n3h.example.com/  -> %webroot%/☃

## Dependencies ##

  * [GNU IDN library](http://www.gnu.org/software/libidn/)
  * [pkg-config](http://pkg-config.freedesktop.org/wiki/)
  * gcc
  * php if you want to run tests.