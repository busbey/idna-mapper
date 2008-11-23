<?php
	require_once('Ruby.php');
	class Ruby__Array extends Ruby__Object
	{
		protected /*Array*/ $array;
		protected /*Boolean*/ $frozen = Ruby__FALSE;

		function __construct($arg0, $arg1)
		{
			parent::__construct();
			if(is_a($arg0,'array'))
			{
				$this->array = $arg0;
			}
			else if(Ruby__NIL === $arg0)
			{
				$this->array = array();
			}
			else if($arg0 instanceof Ruby__Array)
			{
				$arg0->__call('to_ary');	
			}
			else if($arg0 instanceof Ruby__Integer)
			{
				$this->array = array();
				for(;0 > $arg0;$arg0--)
				{
					if($arg1 instanceof Ruby__Block)
					{
						$this->array[] = $arg1->call(Ruby__Integer::fromPhp(count($this->array)));
					}
					else
					{
						$this->array[] &= $arg1;
					}
				}
			}
			else
			{
				throw new Ruby__ArguementError(Ruby__String::fromPhp("Constructor only takes elements or nil"));
			}
		}
		static function getClass()
		{
			return __CLASS__;
		}
		function toPhp()
		{
			return $this->array;
		}
		static function fromPhp(array $array)
		{
			$klass = static::getClass();
			return new $klass($array);
		}
		public function _clone()
		{
			$klass = static::getClass();
			return new $klass(array_merge($this->array));
		}

		/* XXX these might all have to become functions stored in our instanceTemplate array, so that we won't have to translate between their literal ruby form and these long names. */

		/* [foo,bar,catz] */
		static function literal()
		{
			$klass = static::getClass();
			$args = func_get_args();
			return new $klass($args);
		}

		/* & */
		function intersection(Ruby__Array $other)
		{
			$klass = static::getClass();
			return new $klass(array_intersect($this->array, $other->array));
		}

		/* * */
		function times(Ruby__Object $other)
		{
			$ret = Ruby__NIL;
			if($other instanceof Ruby__String)
			{
				$ret = $this->__call('join', $other);
			}
			else if($other instanceof Ruby__Integer)
			{
				$ret = array();
				for(;0 < $other; $other--)
				{
					$ret.push($this->array);
				}
				$klass = static::getClass();
				$ret = new $klass($ret);
			}
			else
			{
				throw new Ruby__ArguementError(Ruby__String::fromPhp("Have to call times on array with String or Integer"));
			}
			return $ret;
		}

		/* +  */
		function add(Ruby__Array $other)
		{
			$klass = static::getClass();
			return new $klass(array_merge($this->array, $other->array));
		}

		/* - */
		function minus(Ruby__Array $other)
		{
			$klass = static::getClass();
			return new $klass(array_diff($this->array, $other->array));
		}

		/* << */
		function lessless(Ruby__Object $elem)
		{
			$this->array[] = $elem;
			return $this;
		}

		/* <=> */
		function lessequalgreater(Ruby__Array $other)
		{
			 foreach($this->array as $key => $val)
			 {
			 	$cmp = $val->__call('lessequalgreater', $other[$key]); 
			 	if(0 !== $cmp)
				{
					return $cmp;
				}
			 }
			 $arr1 = count($this->array);
			 $arr2 = count($other->array);
			 if($arr1 < $arr2)
			 {
			 	return -1;
			 } else if($arr1 > $arr2)
			 {
			 	return 1;
			 }
			 return 0;
		}

		/* == */
		function equalequal(Ruby__Array $other)
		{
			foreach($this->array as $key => $val)
			{
				if(!$val->__call(array("Ruby__Object","equalequal"),$other[$key]))
				{
					return Ruby__FALSE;
				}
			}
			return Ruby__TRUE;
		}

		/* array[] as well as the name 'slice' */
		function slice (Ruby__Object $arg0, Ruby__Object $arg1 = null)
		{
			$klass = static::getClass();
			if($arg0 instanceof Ruby__Range)
			{
				$start = $arg0->__call('start')->toPhp();
				$end = $arg0->__call('end')->toPhp();
				$length = $end - $start;
				if(!($arg0->__call('exclude_end?')))
				{
					$length++;
				}
				return new $klass(array_slice($this->array, $start, $length));
			}
			else if($arg0 instanceof Ruby__Integer)
			{
				if($arg1 instanceof Ruby__Integer)
				{
					/* array.slice(start, length) */
					return new $klass(array_slice($this->array, $arg0->toPhp, $arg1->toPhp));
				}
				else if(Ruby__NIL === $arg1)
				{
					/* array.slice(index) */
					return $this->array[$arg0->toPhp];
				}
			}
			throw new Ruby__ArguementException("Invalid args to array.slice");
		}

		/* element assignment.  array[] = */
		function splice (Ruby__Object $elem, Ruby__Object $arg0, Ruby__Object $arg1)
		{
			$ins = array();
			$start = 0;
			$length = 0;
			$elemIns = true;
			if($arg0 instanceof Ruby__Range)
			{
				$start = $arg0->__call('start')->toPhp();
				$end = $arg0->__call('end')->toPhp();
				$length = $end - $start;
				if(!($arg0->__call('exclude_end?')))
				{
					$length++;
				}
			}
			else if($arg0 instanceof Ruby__Integer)
			{
				$start = $arg0->toPhp;
				if($arg1 instanceof Ruby__Integer)
				{
					$length = $arg1->toPhp();
				}
				else if(Ruby__NIL === $arg1)
				{
					/* array[index] = foo 
						Need to explicitly take care of padding.
					*/
					$index = $start;
					$start = count($this->array);
					if($start >= $index)
					{
						/* XXX don't need to pad in this case. */
						$this->array[$index] = $elem;
						return $elem;
					}
					/* replace insert array. */
					array_pad($ins, $index - $start - 1, Ruby__NIL);
					$ins[] = $elem;
					$length = count($ins);
					$elemIns = false;
				}
			}
			/* XXX This extra step is on purpose, so we can properly assign an element that is an array. */
			if($elemIns)
			{
				if($elem instanceof Ruby__Array)
				{
					$ins = $elem->toPhp();
				}
				else if(Ruby__NIL !== $elem)
				{
					$ins[] = $elem;
				}
			}
			array_splice($this->array, $start, $length, $ins);
			return $elem;
		}

		public function assoc(Ruby__Object $obj, $index = 0)
		{
			foreach($this->array as $elem)
			{
				if($elem instanceof Ruby__Array)
				{
					$elemArray = $elem->toPhp();
					if($obj->__call('equalequal', array($elemArray[$index])))
					{
						return $elem;
					}
				}
			}
			return Ruby__NIL;
		}

		public function at(Ruby__Integer $index)
		{
			if(isset($this->array[$index]))
			{
				return $this->array[$index];
			}
			return Ruby__NIL;
		}

		public function choice()
		{
			return array_rand($this->array, 1);
		}

		public function clear()
		{
			$this->array = array();
		}

		public function map(Ruby__Block $block)
		{
			$res = $this->_clone();
			$res->mapbang($block);
			return $res;
		}

		public function collect(Ruby__Block $block)
		{
			return static::map($block);
		}

		public function mapbang(Ruby__Block $block)
		{
			$res = array();
			foreach($this->array as $key => $elem)
			{
				$res[$key] = $block->call($elem);
			}
			$this->array = $res;
		}

		public function collectbang(Ruby__Block $block)
		{
			return static::mapbang($block);
		}

		public function combination(Ruby__Integer $len, Ruby__Block $block=null)
		{
			$len = $len->toPhp();
			if(null === $block)
			{
				/* TODO return enumerator object */
				throw new Ruby__NotImplementedException("Sorry, combination doesn't return an enumerator yet");
			}
			if(0 === len)
			{
				$block->call(new Ruby__Array(array()));
			}
			else if(1 === len)
			{
				foreach($this->array as $elem)
				{
					$block->call(new Ruby__Array(array($elem)));
				}
			}
			else
			{
				throw new Ruby__NotImplementedException("later");
			}
			return $this;
		}

		public function compact()
		{
			$res = $this->_clone();
			$res->compactbang();
			return $res;
		}

		public function compactbang()
		{
			$result = array_filter($this->array, create_function('$elem', 'return !($elem->__call(\'nilquestion\'));'));

			if($result === $this->array)
			{
				return Ruby__NIL;
			}
			$this->array = $result;
			return $this;
		}

		public function concat(Ruby__Array $other)
		{
			$this->array = array_merge($this->array, $other->array);
			return $this;
		}

		public function count(Ruby__Object $arg=null)
		{
			if(null === $arg)
			{
				return count($this->array);
			}
			$count = 0;
			foreach($this->array as $elem)
			{
				if($arg instanceof Ruby__Block)
				{
					if($arg->call($elem))
					{
						$count++;
					}
				}
				else
				{
					if($arg->__call('equalequal', array($elem)))
					{
						$count++;
					}
				}
			}
			return Ruby__Fixnum::fromPhp($count);
		}

		public function cycle(Ruby__Block $block, Ruby__Integer $times=null)
		{
			if(0 === count($this->array))
			{
				return Ruby__NIL;
			}
			if(null !== $times && Ruby__NIL !== $times)
			{
				$times = $times->toPhp();
				if(0 > $times)
				{
					return Ruby__NIL;
				}
				for(;0 < $times;$times--)
				{
					foreach($this->array as $elem)
					{
						$block->call($elem);
					}
				}
			}
			else
			{
				while(true)
				{
					foreach($this->array as $elem)
					{
						$block->call($elem);
					}
				}
			}
			return Ruby__NIL;
		}

		public function delete(Ruby__Object $obj, Ruby__Block $block=null)
		{
			$res = array_filter($this->array, create_function('$elem', 'return !($obj->__call(\'equalequal\', array($elem)));'));
			if($res === $this->array)
			{
				if(null !== $block)
				{
					return $block->call();
				}
				return Ruby__NIL;
			}
			else
			{
				$this->array = $res;
			}
			return $obj;
		}

		public function delete_at(Ruby__Integer $index)
		{
			$res = Ruby__NIL;
			$out = array_splice($this->array, $index, 1);
			if(0 <> count($out))
			{
				$res = $out[0];
			}
			return $res;
		}

		public function delete_if(Ruby__Block $block)
		{
			$this->array = array_filter($this->array, create_function('$elem', 'return $block->call($elem);'));
		}

		public function drop(Ruby__Integer $count)
		{
			$count = $count->toPhp();
			if(0 > $count)
			{
				throw new Ruby__ArguementException("can't drop negative number.");
			}
			$klass = static::getClass();
			return new $klass(array_slice($this->array, $count, count($this->array) - $count));
		}

		public function drop_while(Ruby__Block $block)
		{
			for($index = 0; $index < count($this->array); $index++)
			{
				if(!($block->call($this->array[$index])))
				{
					break;
				}
			}
			$klass = static::getClass();
			return new $klass(array_slice($this->array, $index, count($this->array) - $index));
		}

		public function each(Ruby__Block $block)
		{
			foreach(array_values($this->array) as $elem)
			{
				$block->call($elem);
			}
			return $this;
		}

		public function each_index(Ruby__Block $block)
		{
			foreach(array_keys($this->array) as $key)
			{
				$block->call($key);
			}
			return $this;
		}

		public function emptyquestion()
		{
			return 0 == count($this->array);
		}
		
		public function eqlquestion(Ruby__Array $array)
		{
			return ($this->array === $array->array);
		}

		public function fetch(Ruby__Integer $offset, Ruby__Object $arg = null)
		{
			$index = $offset->toPhp();
			if(isset($this->array[$index]))
			{
				return $this->array[$index];
			}
			if(null === $arg)
			{
				throw new Ruby__IndexException("Bad index in fetch call.");
			}
			if($arg instanceof Ruby__Block)
			{
				return $arg->call($offset);
			}
			return $arg;
		}

		/* six forms?  really? */
		public function fill(Ruby__Object $arg0, Ruby__Object $arg1, Ruby__Object $arg2)
		{
			$block = null;
			$object = null;
			$start = 0;
			$length = count($this->array);
			switch(func_num_args())
			{
				case 1:
					if($arg0 instanceof Ruby__Block)
					{
						/* form 4 */
						$block = $arg0;
					}
					else
					{
						/* form 1 */
						$object = $arg0;
					}
					break;
				case 2:
					$range = null;
					if($arg1 instanceof Ruby__Block)
					{
						$block = $arg1;
						if($arg0 instanceof Ruby__Range)
						{
							/* form 6 */
							$range = $arg0;
						}
						else if($arg0 instanceof Ruby__Integer)
						{
							/* form 5, optional length omitted */
							$start = $arg0->toPhp();
							$length -= $start;
						}
					}
					else if($arg1 instanceof Ruby__Integer || Ruby__NIL === $arg1)
					{
						/* form 2, optional length omitted */
						$object = $arg0;
						if(Ruby__NIL !== $arg1)
						{
							$start = $arg1->toPhp();
							$length -= $start;
						}
					}
					else if($arg1 instanceof Ruby__Range)
					{
						/* form 3 */
						$range = $arg1;
						$object = $arg0;
					}
					else
					{
						throw new Ruby__ArguementException("Invalid params of len 2");
					}
					if(null !== $range)
					{
						$start = $arg0->__call('start')->toPhp();
						$end = $arg0->__call('end')->toPhp();
						$length = $end - $start;
						if(!($arg0->__call('exclude_end?')))
						{
							$length++;
						}
					}
					break;
				case 3:
					if($arg2 instanceof Ruby__Block)
					{
						/* form 5 */
						$block = $arg2;
						if($arg0 instanceof Ruby__Integer)
						{
							$start = $arg0->toPhp();
							$length -= $start;
						}
						else if(Ruby__NIL !== $arg0)
						{
							throw new Ruby__ArguementException("invalid param type for param 0");
						}
						if($arg1 instanceof Ruby__Integer)
						{
							$length = $arg1->toPhp();
						}
						else if(Ruby__NIL !== $arg1)
						{
							throw new Ruby__ArguementException("invalid param type for param 1");
						}
					} 
					else if($arg2 instanceof Ruby__Integer)
					{
						/* form 2 */
						$object = $arg0;
						if($arg1 instanceof Ruby__Integer)
						{
							$start = $arg1->toPhp();
							$length -= $start;
						}
						else if(Ruby__NIL !== $arg1)
						{
							throw new Ruby__ArguementException("invalid param type for param 1");
						}
						if($arg2 instanceof Ruby__Integer)
						{
							$length = $arg2->toPhp();
						}
						else if(Ruby__NIL !== $arg2)
						{
							throw new Ruby__ArguementException("invalid param type for param 2");
						}
					}
					else
					{
						throw new Ruby__ArguementException("Invalid params of len 3");
					}
					break;
				default:
					throw new Ruby__ArguementException("invalid number of params");
					break;
			}
			if($block)
			{
				$end = $start + $length;
				for($index = $start; $index < $end; $index++)
				{
					$this->array[$index] = $block->call(Ruby__Integer::fromPhp($index));
				}
			}
			else if($object)
			{
				$ins = array_fill(0, $length, $object);
				$this->array = array_splice($this->array, $start, $length, $ins);
			}
			else
			{
				throw new Ruby_Exception("Fill doesn't have block nor object");
			}
			return $this;
		}

		public function find_index(Ruby__Object $arg)
		{
			$index = Ruby__NIL;
			foreach($this->array as $key => $elem)
			{
				if($arg instanceof Ruby__Block && Ruby__TRUE === $arg->call($elem))
				{
					$index = $key;
					break;
				}
				else if(Ruby__TRUE === $arg->__call('equalequal', array($elem)))
				{
					$index = $key;
					break;
				}
			}
			return $index;
		}

		public function first(Ruby__Integer $len)
		{
			if(0 === func_num_args())
			{
				if(isset($this->array[0]))
				{
					return $this->array[0];
				}
				else
				{
					return Ruby__NIL;
				}
			}
			else
			{
				$klass = static::getClass();
				return new $klass(array_slice($this->array, 0, $len));
			}
		}

		public function flatten(Ruby__Integer $level)
		{
			$res = $this->_clone();
			$res->flattenbang($level);
			return $res;
		}
		
		public function flattenbang(Ruby__Integer $level)
		{
			$change = false;
			if(0 === func_num_args())
			{
				do
				{
					$loop = false;
					foreach(array_keys($this->array) as $key)
					{
						$elem = $this->array[$key];
						if($elem instanceof Ruby__Array)
						{
							array_splice($this->array, $key, 1, $elem->toPhp());
							$loop = true;
							$change = true;
						}
						unset($elem);
					}
				} while($loop);
			
			}
			else
			{
				$level = $level->toPhp();
				for(;$level > 0; $level--)
				{
					foreach(array_keys($this->array) as $key)
					{
						$elem = $this->array[$key];
						if($elem instanceof Ruby__Array)
						{
							array_splice($this->array, $key, 1, $elem->toPhp());
						}
						unset($elem);
					}
				}
			}
			if($change)
			{
				return $this;
			}
			else
			{
				return Ruby__NIL;
			}
		}

		/* the docs here make it sound like ruby actually does locking... hurm... 
			if needed, use sem_get, sem_acquire, sem_release, etc
		*/
		public function frozenquestion()
		{
			return $this->frozen;
		}

		public function hash()
		{
			$hash = count($this->array);
			foreach($this->array as $elem)
			{
				$hash = $hash << 1 | $hash < 0  ? 1 : 0;
				$hash ^= $elem->__call('hash');
			}
			return $hash;
		}

		public function includequestion(Ruby__Object $obj)
		{
			foreach($this->array as $elem)
			{
				if($obj->__call('equaleqal', array($elem)))
				{
					return Ruby__TRUE;
				}
			}
			return Ruby__FALSE;
		}

		public function index (Ruby__Object $arg)
		{
			return find_index($arg);
		}

		public function replace(Ruby__Array $other)
		{
			$this->array = array_merge($other->toPhp());
			return $this;
		}

		public function insert(Ruby__Integer $index)
		{
			$args = func_get_args();
			array_shift($args);
			array_splice($this->array, $index, 0, $args);
			return $this;
		}

		public function inspect()
		{
			$str = null;
			foreach($this->array as $elem)
			{
				if(null !== $str)
				{
					$str .= ',';
				}
				else
				{
					$str = '';
				}
				$str.= ' ' . $elem->__call('inspect');
			}
			$str = 'Array [ ' . $str . ' ]';
			return Ruby__String::fromPhp($str);
		}

		public function join(Ruby__String $sep = null)
		{
			$str = null;
			if(func_num_args() > 0)
			{
				$sep = $sep->toPhp();
			}
			else
			{
				$sep = '';
			}
			foreach($this->array as $elem)
			{
				if(null !== $str)
				{
					$str .= $sep;
				}
				else
				{
					$str = '';
				}
				$str.= ' ' . $elem->__call('to_s');
			}
			return Ruby__String::fromPhp($str);
		}

		public function last(Ruby__Integer $len = null)
		{
			if(0 === func_num_args())
			{
				$end = end($this->array);
				if(null !== $end)
				{
					return $end;
				}
				else
				{
					return Ruby__NIL;
				}
			}
			else
			{
				if(0 > $len)
				{
					throw new Ruby__ArguementException("Can't ask for negative last elements.");
				}
				$klass = static::getClass();
				return new $klass(array_slice($this->array, -1 * $len, $len));
			}
		}

		public function length()
		{
			return count($this->array);
		}

		public function nitems()
		{
			$count = 0;
			foreach($this->array as $elem)
			{
				if(Ruby__FALSE === $elem->__call('nilquestion'))
				{
					$count++;
				}
			}
			return $count;
		}

		/* XXX this is not complete */
		public function pack(Ruby__String $template)
		{
			$args = array_merge($this->array);
			array_unshift($args, $template->toPhp());
			return call_user_func_array('pack', $args);
		}

		public function permutation (Ruby__Object $arg0 = null, Ruby__Object $arg1=null)
		{
			throw new Ruby__NotImplementedException();
		}

		public function pop(Ruby__Integer $len=null)
		{
			$res = Ruby__NIL;
			if(0 === func_num_args())
			{
				$val = array_pop($this->array);
				if(null !== $val)
				{
					$res = $val;
				}
			}
			else
			{
				$len = $len->toPhp();
				if(0 > $len)
				{
					throw new Ruby__ArguementException();
				}
				$klass = static::getClass();
				$res = new $klass(array_splice($this->array, -1*$len, $len));
			}
			return $res;
		}

		public function product(Ruby__Array $other)
		{
			$res = array();
			$klass = static::getClass();
			if(0 === func_num_args())
			{
				/* XXX premature optimization.  the else clause will do the same thing as this, just in more work. `*/
				foreach($this->array as $elem)
				{
					$res[] = new $klass(array($elem));
				}
			}
			else
			{
				$args = func_get_args();
				$numEntries = count($this->array);
				foreach($args as $array)
				{
					$numEntries *= count($array->array);
				}
				if(0 !== $numEntries)
				{
					$curCount = count($this->array);
					$numEach = $numEntries / $curCount;
					$temp = count($res);
					foreach($this->array as $elem)
					{
						$res = array_pad($res, $temp + $numEach, array($elem));
						$temp += $numEach;
					}
					$numparents = 1;
					while(0 < count($args))
					{
						$numparents *= $curCount;
						$cur = array_shift($args);
						$curCount = count($cur);
						$numEach /= $curCount;
						for($index = 0; $index < $numparents; $index++)
						{
							foreach($cur as $elem)
							{
								for($jndex = 0; $jndex < $numEach; $jndex++)
								{
									$res[$index * $numEach + $jndex][] = $elem;
								}
							}
						}
					}
				}
				$res = array_map(create_function('$elem', 'return new $klass($elem);'), $res);
			}
			return new $klass($res);
		}

		public function push(Ruby__Object $obj)
		{
			$args = func_get_args();
			array_unshift($args, &$this->array);
			call_user_func_array('array_push', $args);
			return $this;
		}

		public function rassoc(Ruby__Object $key)
		{
			return $this->assoc($key, 1);
		}

		public function reject(Ruby__Block $block)
		{
			$res = $this->clone();
			$res->rejectbang($block);
			return $res;
		}

		public function rejectbang(Ruby__Block $block)
		{
			$orig = $this->array;
			$this->delete_if($block);
			if($orig === $this->array)
			{
				return Ruby__NIL;
			}
			return $this;
		}

		public function reverse()
		{
			$res = $this->clone();
			$res->reversebang();
			return $res;
		}

		public function reversebang()
		{
			$this->array = array_reverse($this->array);
			return $this;
		}

		public function reverse_each(Ruby__Block $block)
		{
			foreach(array_reverse($this->array) as $elem)
			{
				$block->call($elem);
			}
			return $this;
		}

		public function rindex(Ruby__Object $arg)
		{
			foreach(array_reverse($this->array, true) as $key=>$elem)
			{
				if($arg instanceof Ruby__Block && Ruby__TRUE === $arg->call($elem))
				{
					return $key;
				}
				else if(Ruby__TRUE === $arg->__call('equalequal', array($elem)))
				{
					return $key;
				}
			}
			return Ruby__NIL;
		}

		public function select(Ruby__Block $block)
		{
			$res = array();
			foreach($this->array as $elem)
			{
				if(Ruby__TRUE === $block->call($elem))
				{
					$res[] = $elem;
				}
			}
			$klass = static::getClass();
			return new $klass($res);
		}

		public function shift(Ruby__Integer $len = null)
		{
			$res = Ruby__NIL;
			if(0 === func_num_args())
			{
				$val = array_shift($this->array);
				if(null !== $val)
				{
					$res = $val;
				}
			}
			else
			{
				$len = $len->toPhp();
				if(0 > $len)
				{
					throw new Ruby__ArguementException();
				}
				$klass = static::getClass();
				$res = new $klass(array_splice($this->array, 0, $len));
			}
			return $res;
		}

		public function shuffle()
		{
			$res = $this->clone();
			$res->shufflebang();
			return $res;
		}

		public function shufflebang()
		{
			shuffle($this->array);
			return $this;
		}

		public function size()
		{
			return $this->length();
		}

		public function slicebang(Ruby__Object $arg0, Ruby_Object $arg1 = null)
		{
			$res = $this->slice($arg0, $arg1);
			$this->splice(Ruby__NIL, $arg0, $arg1);
			return $res;
		}

		public function sort(Ruby_Block $block = null)
		{
			$res = $this->clone();
			$res->sortbang($block);
			return $res;
		}

		public function sortbang(Ruby__Block $block=null)
		{
			if(null !== $block)
			{
				usort($this->array, create_function('$a, $b','return $block->call($a, $b);'));
			}
			else
			{
				usort($this->array, create_function('$a, $b', 'return $a->__call(\'lessequalgreater\',array($b));'));
			}
		}

		public function take(Ruby__Integer $len)
		{
			return $this->first($len);
		}

		public function take_while(Ruby__Block $block)
		{
			$len = count($this->array);
			for($index = 0; $index < $len; $index++)
			{
				$res = $block->call($this->array[$index]);
				if(Ruby__NIL === $res || Ruby__FALSE === $res)
				{
					break;
				}
			}
			return $this->take(Ruby__Integer::fromPhp($index));
		}

		public function to_a()
		{
			return new Ruby__Array($this->toPhp());
		}

		public function to_ary()
		{
			return $this;
		}

		public function to_s()
		{
			return $this->join();
		}

		public function transpose()
		{
			$res = array();
			foreach($this->array as $key => $elem)
			{
				foreach($elem as $ikey => $ielem)
				{
					$res[$ikey][$key] = $ielem;
				}
			}
			$klass = static::getClass();
			return new $klass($res);
		}

		public function uniq()
		{
			$res = $this->clone();
			$res->uniqbang();
			return $res;
		}

		public function uniqbang()
		{
			$res = array_values(array_unique($this->array));
			if($res === $this->array)
			{
				return Ruby__NIL;
			}
			$this->array = $res;
			return $this;
		}

		public function unshift(Ruby__Object $first)
		{
			$args = func_get_args();
			array_unshift($args, &$this->array);	
			call_user_func_array('array_unshift', $args);
		}

		public function values_at(Ruby__Object $selector)
		{
			$selectors = func_get_args();
			$len = count($this->array);
			foreach($selectors as &$selector)
			{
				if($selector instanceof Ruby__Integer)
				{
					$selector = $selector->toPhp();
					if(0 > $selector)
					{
						$selector = $len + $selector;
					}
				}
				else if($selector instanceof Ruby__Range)
				{
					/* Todo convert negative ranges to the array's indices. */
				}
				else
				{
					throw new Ruby__ArguementException();
				}
			}
			$res = array();
			foreach($this->array as $key => $elem)
			{
				foreach($selectors as $selector)
				{
					if($selector instanceof Ruby__Range)
					{
						if(Ruby__TRUE === $selector->__call('includequestion', array($key)))
						{
							$res[] = $elem;
							break;
						}
					}
					else
					{
						if($key === $selector)
						{
							$res[] = $elem;
							break;
						}
					}
				}
			}
			$klass=static::getClass();
			return new $klass($res);
		}

		public function zip(Ruby__Block $block=null, Ruby__Object $arg1)
		{
			$args = func_get_args();
			$klass = static::getClass();
			$first = array_shift($args);
			if($first !== $block)
			{
				array_unshift($args, $first);
			}
			$args = array_map($args, create_function('$elem', 'return $elem->__call(\'to_a\');'));
			$res = array();
			$len = count($this->array);
			for($index =0 ; $index < $len; $index++)
			{
				$cur = array($this->array[$index]);
				foreach($args as $array)
				{
					if(isset($array[$index]))
					{
						$cur[] = $array[$index];
					}
					else
					{
						$cur[] = Ruby__NIL;
					}
				}
				$cur = new $klass($cur);
				if(null !== $block)
				{
					$res[] = $block->call($cur);
				}
				else
				{
					$res[] = $cur;
				}
			}
			return $res;
		}

		/* | */
		public function union(Ruby__Array $other)
		{
			$res = $this->clone();
			$res->array = array_unique($res->array + $other->array);
			return $res;
		}
	}
?>
