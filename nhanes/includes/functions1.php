<?php 

function	nhanes_getQueryResult($query)	{
	$result = @mysql_query($query) 
				or die ("bad query in function: <small><tt>$query</tt></small><br/>".mysql_error() );
//		echo	"<br/>nos_getQueryResult: <tt>".$query."</tt><br/>";
	return $result;
	}

//
//	
function	nhanes_getOneRow($query)	{
	$result = nhanes_getQueryResult($query);
	$row = mysql_fetch_array($result);
	return $row;
	}
//

//	More general variable displayer

function	eeps_printr($a, $level)	{
	if (is_array($a))	{
		$name = key($a);
		echo	"<br>";
		for ($i = 0; $i < $level*2; $i++)	echo "&nbsp;&nbsp;";	
		echo "<strong>ARRAY $name</strong>";
		foreach ($a as $e)	{
			eeps_printr($e, $level + 1);
			}
		}
	else {
		if (strlen($a) != 0)	{
			echo	"<br>";
			for ($i = 0; $i < $level*2; $i++)	echo "&nbsp;&nbsp;";	
			echo "$a";
			}
		}
	}
?>
