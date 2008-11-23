<?php
	require_once('Numeric.php');

	/* XXX Note that we're going to rely on the Gnu Multiple Precision php extension for this.  so compile wiht --with-gmp */
	if(defined("GMP_VERSION"))
	{
		if(!function_exists('gmp_testbit'))
		{
			function gmp_testbit($resource, $index)
			{
				return (gmp_scan1($resource, $index) == $index);
			}
		}
		
		class Ruby__Bignum extends Ruby__Integer
		{
			protected /* GMP_resource */ $gmp;

			function __construct($num, Ruby__Object $base=null)
			{
				if(!($num instanceof Ruby__Fixnum || $num instanceof Ruby__String || is_a($num, 'resource')))
				{
					throw new Ruby__ArguementError();
				}
				if(!(null === $base || Ruby__NIL === $base || $base instanceof Ruby__Fixnum))
				{
					throw new Ruby__ArguementError();
				}
				parent::__construct();
				if(is_a($num, 'resource'))
				{
					$this->gmp = $num;
				}
				else
				{
					if(null == $base || Ruby__NIL === $base)
					{
						$base = 0;
					}
					else
					{
						$base = $base->toPhp();
					}
					$num = $num->toPhp();
					$this->gmp = gmp_init($num, $base);
				}
			}

			static function getClass()
			{
				return __CLASS__;
			}
			
			function _clone()
			{
				$klass = static::getClass();
				/* GMP numbers are resources, so external.  assigment might not get us an independant one, so force the creation of a new one. */
				return new $klass(gmp_or($this->gmp, "0"));
			}

			function toPhp()
			{
				return $this->gmp;
			}

			static function fromPhp($gmp)
			{
				$klass = static::getClass();
				return new $klass($gmp);
			}

			static function ensureGmp(Ruby__Numeric $other)
			{
				if($other instanceof Ruby__Bignum)
				{
					return $other->toPhp();
				}
				else
				{
					return gmp_init((int)($other->toPhp()));
				}
			}

			/* % as well as modulo */
			public function modulo(Ruby__Numeric $other)
			{
				$klass = static::getClass();
				$gmp = $klass::ensureGmp($other);
				$mod = gmp_sign($gmp) * gmp_mod($this->gmp, $gmp);
				return new $klass($mod);
			}

			/* XXX for operands where the integer result of n/d doesn't fit in a fixnum, this isn't going to behave. */
			public function fdiv(Ruby__Numeric $other)
			{
				$klass = static::getClass();
				$gmp = $klass::ensureGmp($other);
				$res = gmp_div_qr($this->gmp, $gmp);
				return new Ruby__Float(((float) gmp_intval($res[0]) ) + (gmp_intval($res[1]) / ($other->toPhp())));
			}

			public function quo(Ruby__Numeric $other)
			{
				return $this->fdiv($other);
			}

			public function remainder(Ruby__Numeric $other)
			{
				$klass = static::getClass();
				$gmp = $klass::ensureGmp($other);
				return new $klass(gmp_div_r($this->gmp, $gmp));
			}

			public function size()
			{
				$size = 0;
				$sign = gmp_sign($this->gmp);
				$str = gmp_strval($this->gmp, 16);
				if(-1 === $sign)
				{
					$str = substr($str, 3);
				}
				else
				{
					$str = substr($str, 2);
				}
				return Ruby__Fixnum::fromPhp(ceil(strlen($str) / 2));
			}

			/* & */
			public function bitwise_and(Ruby__Numeric $other)
			{
				$klass = static::getClass();
				$gmp = $klass::ensureGmp($other);
				return new $klass(gmp_and($this->gmp, $gmp));
			}

			/* * */
			public function mul(Ruby__Numeric $other)
			{
				$klass = static::getClass();
				$gmp = $klass::ensureGmp($other);
				return new $klass(gmp_mul($this->gmp, $gmp));
			}

			/* ** */
			public function exp(Ruby__Numeric $other)
			{
				$klass = static::getClass();
				if($other instanceof Ruby__Bignum)
				{
					$gmp = gmp_intval($other->toPhp());
				}
				else
				{
					$gmp = (int) $other->toPhp();
				}
				return new $klass(gmp_exp($this->gmp, $gmp));
			}

			/* + */
			public function add(Ruby__Numeric $other)
			{
				$klass = static::getClass();
				$gmp = $klass::ensureGmp($other);
				return new $klass(gmp_add($this->gmp, $gmp));
			}

			/* - */
			public function sub(Ruby__Numeric $other)
			{
				$klass = static::getClass();
				$gmp = $klass::ensureGmp($other);
				return new $klass(gmp_sub($this->gmp, $gmp));
			}
			
			/* -(unary) */
			public function unary_minus()
			{
				$klass = static::getClass();
				return new $klass(gmp_neg($this->gmp));
			}

			/* / as well as div */
			public function div(Ruby__Numeric $other)
			{
				$klass = static::getClass();
				$gmp = $klass::ensureGmp($other);
				return new $klass(gmp_div($this->gmp, $gmp));
			}

			/* << */
			public function left_shift(Ruby__Numeric $other)
			{
				$klass = static::getClass();
				$gmp = $klass::ensureGmp($other);
				if(-1 === gmp_sign($gmp))
				{
					return new $klass(gmp_div($this->gmp, gmp_pow(2, gmp_abs($gmp))));
				}
				else
				{
					return new $klass(gmp_mul($this->gmp, gmp_pow(2, $gmp)));
				}
			}

			/* <=> */
			public function lessequalgreater(Ruby__Numeric $other)
			{
				$klass = static::getClass();
				$gmp = $klass::ensureGmp($other);
				return new Ruby__Fixnum(gmp_cmp($this->gmp, $gmp));
			}

			/* == */
			public function equalequal(Ruby__Numeric $other)
			{
				return 0 === ($this->lessequalgreater($other)->toPhp());
			}

			/* >> */
			public function right_shift(Ruby__Numeric $other)
			{
				$klass = static::getClass();
				$gmp = $klass::ensureGmp($other);
				if(-1 === gmp_sign($gmp))
				{
					return new $klass(gmp_mul($this->gmp, gmp_pow(2, gmp_abs($gmp))));
				}
				else
				{
					return new $klass(gmp_div($this->gmp, gmp_pow(2, $gmp)));
				}
			}

			/* [] */
			public function bit(Ruby__Fixnum $other)
			{
				$index = $other->toPhp();
				if(gmp_testbit($this->gmp, $index))
				{
					return new Ruby__Fixnum(1);
				}
				else
				{
					return new Ruby__Fixnum(0);
				}
			}

			/* ^ */ 
			public function exclusive_or(Ruby__Numeric $other)
			{
				$klass = static::getClass();
				$gmp = $klass::ensureGmp($other);
				return new $klass(gmp_xor($this->gmp, $gmp));
			}

			public function abs()
			{
				$klass = static::getClass();
				return new $klass(gmp_abs($this->gmp));
			}
			
			public function coerce(Ruby__Object $x, Ruby__Numeric $y)
			{
				$klass = static::getClass();
				if($y instanceof Ruby__Fixnum)
				{
					$y = new $klass($y->toPhp());
				}
				else if(!($y instanceof Ruby__Bignum))
				{
					throw new Ruby__TypeError("Can't coerce to bignum");
				}
				return Ruby__Array::literal($y, $x);
			}

			/* By my reading of the docs, this function actually wants to return the quotient and the remainder, not the modulus. */
			public function divmod(Ruby__Numeric $other)
			{
				$klass = static::getClass();
				$gmp = $klass::ensureGmp($other);
				$res = gmp_div_qr($this->gmp, $gmp);
				$res = array_map($res, create_function('$elem','return new $klass($elem);'));
				return new Ruby__Array($res);	
			}

			/* eql? */
			public function eqlquestion(Ruby_Numeric $other)
			{
				if($other instanceof Ruby__Bignum)
				{
					if(0 === gmp_cmp($this->gmp, $other))
					{
						return true;
					}
				}
				return false;
			}

			/* We're probably not going to be binary compatible with Ruby 1.8.  the hash of bignum is an xor of ints
				I'll try to do something similar at least
			*/
			public function hash()
			{
				$hash = 0;
				$str = gmp_strval($this->gmp);
				/* XXX the reversals are so that incomplete chunks will be in the MSB, and so that the 0x and sign, if present, will not count towards determining a word boundry. */
				$ints = str_split(strrev($str), 8);
				foreach($ints as $elem)
				{
					$hash ^= intval(strrev($elem), 16);
				}
				return $hash;
			}

			public function to_f()
			{
				return new Ruby__Float(floatval(gmp_strval($this->gmp)));
			}

			public function to_s(Ruby__Fixnum $base=null)
			{
				if(null === $base)
				{
					$base = 10;
				}
				else
				{
					$base = $base->toPhp();
				}
				if(2 < $base || 36 > $base)
				{
					throw new Ruby__RangeException();
				}
				return new Ruby__String(gmp_strval($this->gmp, $base));
			}

			/* | */
			public function bitwise_or (Ruby__Numeric $other)
			{
				$klass = static::getClass();
				$gmp = $klass::ensureGmp($other);
				return new $klass(gmp_or($this->gmp, $gmp));
			}

			/* ~ */
			public function bitwise_invert()
			{
				/* Assuming 2's complement */
				$this->gmp = gmp_neg(gmp_add($this->gmp,gmp_init(1)));
			}
		}
	}
	else
	{
		class Ruby__Bignum extends Ruby__Integer
		{
			public function __call($name, $args)
			{
				throw new Ruby__LoadError("Bignum requires the GMP extension to php.");
			}

			public static function __callStatic($name, $args)
			{
				throw new Ruby__LoadError("Bignum requires the GMP extension to php.");
			}
		}
	}
?>
