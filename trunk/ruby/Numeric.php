<?
	require_once("Object.php");

	class Ruby__Numeric extends Ruby__Object
	{
		function __construct()
		{
			parent::__construct();
		}

		static function getClass()
		{
			return __CLASS__;
		}
	}

	class Ruby__Float extends Ruby__Numeric
	{
		function __construct()
		{
			parent::__construct();
		}

		static function getClass()
		{
			return __CLASS__;
		}
	}
	class Ruby__Integer extends Ruby__Numeric
	{
		function __construct()
		{
			parent::__construct();
		}

		static function getClass()
		{
			return __CLASS__;
		}
	}

	class Ruby__Fixnum extends Ruby__Numeric
	{
		protected $val;
		function __construct($arg)
		{
			if(is_a($arg, 'int'))
			{
				$this->val = $arg;
			}
			parent::__construct();
		}

		static function getClass()
		{
			return __CLASS__;
		}

		static function fromPhp($val)
		{
			$klass = static::getClass();
			return new $klass($val);
		}

		function toPhp()
		{
			return $this->val;
		}
	}
?>
