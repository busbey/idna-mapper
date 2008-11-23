<?php
	/** @brief testing harness for mod_rewrite mapper
	 * takes one file and feeds it line by line to a mapping utility
	 * then compares output to expected results.
	 */
	function print_help()
	{
		echo "usage php tester.php [--mapper /path/to/util] [--idn idn.txt] [--expected answers.txt] [--loop]";
		phpinfo();
	}
	$idn_file = 'test.txt';
	$expected_file = 'expected.txt';
	$mapper = 'idn_mapper';
	$loop = false;
	if(1 === $_SERVER['argc'])
	{
		print_help();
		exit(0);
	}
	for($i=1;$i<$_SERVER['argc'];$i++)
	{
		switch($_SERVER['argv'][$i])
		{
			case '-i':
			case '--idn':
				$i++;
				$idn_file = $_SERVER['argv'][$i];
				break;
			case '-e':
			case '--expected';
				$i++;
				$expected_file=$_SERVER['argv'][$i];
				break;
			case '-l':
			case '--loop':
				$loop = true;
				break;
			case '-m':
			case '--mapper':
				$i++;
				$mapper = $_SERVER['argv'][$i];
				break;
			default:
				print_help();
				exit(0);
				break;
		}
	}
	$idn = file($idn_file);
	$expected = file($expected_file);
	$desc = array(
		0 => array("pipe", "r"),
		1 => array("pipe", "w"),
		2 => array("file", "error-log.txt", "a")
	);
	$proc = proc_open($mapper, $desc, $pipes);
	if(is_resource($proc))
	{
		do
		{
			for($i=0; $i < count($idn);$i++)
			{
				echo "running test ${i}: ";
				fwrite($pipes[0], $idn[$i]);
				$result=fgets($pipes[1]);
				if($result===$expected[$i])
				{
					echo "passed\n";
				}
				else
				{
					echo "failed\n";
					fprintf(STDERR, "\texpected '".trim($expected[$i])."', but got '".trim($result)."'\n"); 
				}
			}
		} while($loop);
	}
	else
	{
		fprintf(STDERR, "failed to launch mapper.\n");
	}
	foreach($pipes as $pipe)
	{
		fclose($pipe);
	}
	proc_close($proc);
?>
