<?php
	require_once("Ruby.php");

	class Ruby__ExceptionProxy extends Ruby__Object
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

	class Ruby__Exception extends Exception
	{
		function __construct(Ruby__String $message)
		{
			$proxyObject = new Ruby__ExceptionProxy();
			if(Ruby__NIL !== $message)
			{
				parent::__construct($message->toPhp());
			}
			else
			{
				parent::__construct(static::getClass().substr(6));
			}
		}

		/* Need to pretend we extend Object */
		protected /*Ruby__Object*/ $proxyObject;
		public function __call($func, $args)
		{
			return $proxyObject->__call($func, $args);
		}
		public static function __callStatic($func, $args)
		{
			return Ruby__ExceptionProxy::__callStatic($func, $args);
		}
		
		/* Back to normal functions */
		function exception(Ruby__String $message )
		{
			if(!$message || ($message->toPhp() === $this->getMessage()))
			{
				return $this;
			}
			else
			{
				$klass = static::getClass();
				return new $klass($message->__call('to_s'));
			}
		}
		
		function backtrace()
		{
			return new Ruby__Array(array_map(Ruby__String::fromPhp, $this->getTrace()));
		}

		function inspect()
		{
			return Ruby__String::fromPhp('#<'.static::getClass().': '.$this->getMessage().'>');
		}

		function message()
		{
			return $this->__call('to_s');
		}

		function to_str()
		{
			return $this->__call('to_s');
		}

		function set_backtrace(Ruby__Array $backtrace)
		{
			throw new Ruby__NotImplementedError();
		}
		
		function to_s()
		{
			return (Ruby__String::fromPhp($this->getMessage()));
		}
	}

	class Ruby__StandardError extends Ruby__Exception { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}} 
	class Ruby__ScriptError extends Ruby__Exception { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}} 
	class Ruby__SignalException extends Ruby__Exception { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}} 
	class Ruby__MemoryError extends Ruby__Exception { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}}
	class Ruby__fatal extends Ruby__Exception { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}}

	class Ruby__ArguementError extends Ruby__StandardError { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}}
	class Ruby__IOError extends Ruby__StandardError { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}}
	class Ruby__IndexError extends Ruby__StandardError { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}}
	class Ruby__RangeError extends Ruby__StandardError { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}}
	class Ruby__RegexpError extends Ruby__StandardError { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}}
	class Ruby__RuntimeError extends Ruby__StandardError { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}}
	class Ruby__SecurityError extends Ruby__StandardError { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}}
	class Ruby__SystemStackError extends Ruby__StandardError { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}}
	class Ruby__ThreadError extends Ruby__StandardError { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}}
	class Ruby__TypeError extends Ruby__StandardError { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}}
	class Ruby__ZeroDivisionError extends Ruby__StandardError { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}}
	
	class Ruby__FloatDomainError extends Ruby__RangeError { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}}
	class Ruby__LoadError extends Ruby__ScriptError { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}}
	class Ruby__NotImplementedError extends Ruby__ScriptError { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}}
	class Ruby__SyntaxError extends Ruby__ScriptError { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}}
	
	class Ruby__EOFError extends Ruby__IOError { function __construct(Ruby__String $message){ parent::__construct($message);} static function getClass() { return __CLASS__;}}

	class Ruby__Errno extends Ruby__Module
	{
		function __construct()
		{
			parent::__construct();
			$this->constants = array_merge($this->constants, array());
		}
	static function getClass() { return __CLASS__;}}

	/* Todo this is where I left off http://www.ruby-doc.org/core/classes/SystemCallError.html */
	class Ruby__SystemCallError extends Ruby__StandardError 
	{ 
		protected /*Ruby__Fixnum*/ $errno;
		function __construct(Ruby__String $message, Ruby__Fixnum $errno)
		{ 
			parent::__construct($message);
			/* Todo map to system errno and subclass */
			$this->errno = $errno;
		} 
static function getClass() { return __CLASS__;}
		function equalequalequal(Ruby__Object $other)
		{
			if(static::getClass()===getClass() || $this->errno->__call("equalequalequal",$other->__call("errno")))
			{
				return Ruby__TRUE;
			}
			return Ruby__FALSE;
		}

		function errno()
		{
			return $this->errno;
		}
	}

	class Ruby__NameError extends Ruby__StandardError 
	{ 
		protected /*Ruby__String*/ $name;
		function __construct(Ruby__String $message, Ruby__String $name )
		{ 
			parent::__construct($message);
			$this->name = $name;
		} 
static function getClass() { return __CLASS__;}
		function name()
		{
			return $this->name;
		}

		function to_s()
		{
			return parent::to_s()->__call('concat', $this->__call('name'));
		}
	}
	class Ruby__NoMethodError extends Ruby__NameError
	{
		protected /*Ruby__Object*/ $args;
		function __construct(Ruby__String $message, Ruby__String $name, Ruby__Object $args )
		{ 
			parent::__construct($message, $name);
			$this->args = $args;
		}
static function getClass() { return __CLASS__;}
		function args()
		{
			return $this->args;
		}
	}
?>
