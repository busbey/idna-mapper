#!/usr/local/bin/php
<?php
	/* XXX I'm currently assuming that I'll be able to use ruby_parser
		( http://parsetree.rubyforge.org/ruby_parser/ ) to take ruby source and generate an AST.

		I'll use ruby to write out that AST as php code that uses this php code for base classes.

		I'll get eval by running that ruby compiler through the compiler.

		OMG i love compilers, this idea is madness.
	*/

	define(Ruby__FALSE, false);
	define(Ruby__TRUE, true);
	define(Ruby__NIL, NULL);
	define(Ruby__UNDEF, "__UNDEFINED__");
	
	require_once("Object.php");
	require_once("Error.php");
	require_once("Array.php");
	require_once("Numeric.php");
	require_once("Bignum.php");
	/*
	require_once("Ruby_Object.php");
	require_once("Ruby_Array.php");
	require_once("Ruby_Class.php");
	require_once("Ruby_Comparable.php");
	require_once("Ruby_Enumerable.php");
	require_once("Ruby_Enumerator.php");
	require_once("Ruby_Io.php");
	require_once("Ruby_Dir.php");
	require_once("Ruby_File.php");
	require_once("Ruby_Exception.php");
	require_once("Ruby_Numeric.php");
	require_once("Ruby_GC.php");
	require_once("Ruby_Hash.php");
	require_once("Ruby_Marshal.php");
	require_once("Ruby_Pack.php");
	require_once("Ruby_RegularExpression.php");
	require_once("Ruby_Math.php");
	require_once("Ruby_Eval.php");
	require_once("Ruby_Error.php");
	require_once("Ruby_String.php");
	require_once("Ruby_Time.php");
	require_once("Ruby_Sprintf.php");
	require_once("Ruby_Util.php");
	require_once("Ruby_Precision.php");
	require_once("Ruby_Process.php");
	require_once("Ruby_Random.php");
	require_once("Ruby_Range.php");
	require_once("Ruby_Signal.php");
	require_once("Ruby_Struct.php");
	require_once("Ruby_Symbol.php");
	*/

	class Ruby__Loader
	{
		protected $install = "/Users/sabusbey/project/scrappile/ruby";
	
		public static function load($ruby_class)
		{
			$components = explode("__", $ruby_class);
			if('Ruby' === $components[0])
			{
				$components.shift();
				require_once($install.'/'.(implode("/",$components)).".php");
				return true;
			}
			return false;
		}
	}
	spl_autoload_register('Ruby__Loader::load');
?>
