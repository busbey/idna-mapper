<?php
	require_once("Ruby.php");

	class Ruby__Object
	{
		protected static $classFunctions = array();
		protected static $instanceTemplate = array();
		protected $instanceFunctions = array();
		
		protected static $classMembers = array();
		protected $instanceMembers = array();

		function __construct()
		{
			parent::__construct();
			$this->instanceFunctions = $instanceTemplate;
		}

		/* thanks for not letting me do static::__CLASS__ php. */
		static function getClass()
		{
			return __CLASS__;
		}

		/* Default method missing */
		public function method_missing($args)
		{
			throw new Ruby__NotImplementedException(get_class($this));
		}

		public static function class_method_missing($args)
		{
			throw new Ruby__NotImplementedException( static::getClass());
		}
		
		public function add_function($name, $func)
		{
			if($this)
			{
				$instanceFunctions[$name] = $func;
			}
			else
			{
				$classFunctions[$name] = $func;
			}
		}

		public function extend($other)
		{
			if($this)
			{
				/* other is an object */
				$instanceFunctions = array_merge($instanceFunctions, $other->instanceTemplate);
			}
			else
			{
				/* other is a string representing a class */
				$klass = static::getClass();
				$klass::$classFunctions = array_merge($klass::$classFunctions, $other::$classFunctions);
				$class = new ReflectionClass($other);
				$methods = $class->getMethods();
				$getname = create_function('$method', 'return $method->getName();');
				$methods = array_map($getname,$methods);
				$klass::$instanceTemplate = array_merge($klass::$instanceTemplate, $methods, $other::$instanceTemplate);
			}
		}

		/* Use php's hooks to allow for ruby's dynamic binding */
		public function __get($name)
		{
		}

		public function __set($name, $value)
		{
		}
		
		public function __call($func, $args)
		{
			if(is_callable($instanceFunctions[$func]))
			{
				return $instanceFunctions[$func]($args);
			}
			else if(is_callable(array($this, $func)))
			{
				return call_user_func_array(array($this,$func),$args);
			}
			else if(is_callable($instanceFunctions['method_missing']))
			{
				return $instanceFunctions['method_missing'](array_unshift($args, $func));
			}
			else
			{
				return static::method_missing(array_unshift($args, $func));
			}
		}

		public static function __callStatic($func, $args)
		{
			if(is_callable($classFunctions[$func]))
			{
				return $classFunctions[$func]($args);
			}
			else if(is_callable(array(static::getClass(), $func)))
			{
				return call_user_func_array(array(static::getClass(),$func),$args);
			}
			else if(is_callable($classFunctions['method_missing']))
			{
				return $classFunctions['method_missing'](array_unshift($args, $func));
			}
			else
			{
				return static::class_method_missing(array_unshift($args, $func));
			}
		}
	}

	class Ruby__Module extends Ruby__Object
	{
		protected $constants = array();
		function __construct()
		{
			parent::__construct();
		}
		static function getClass()
		{
			return __CLASS__;
		}
		function constants()
		{
			return $this->constants;
		}
	}

	class Ruby__Class extends Ruby__Module
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

	class Ruby__Kernel extends Ruby__Module
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
	Ruby__Object::extend(Ruby__Kernel);
?>
