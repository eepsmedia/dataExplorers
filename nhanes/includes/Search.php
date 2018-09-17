<?php

class		Search	{


//	members
public	$variableNameArray;     //  what variables are we querying about? e.g., NAME
public	$variableQueryArray;	//	what variables are we querying about? -- but with the table prefixes, e.g, t1.NAME
public	$filterArray;			//	what are our relational phrases (tables)? WHERE t1.SEQN = t2.SEQN, etc.
public	$chooseCasesArray;		//	what are our filter phrases (data restrictions)? (age > 10)
public	$tableReferences;		//	 FROM bmx AS t2, etc.

//	methods

public	function	__construct( )	{
	$variableNameArray = array( );
	$variableQueryArray = array( );
	$filterArray = array( );
	$chooseCasesArray = array( );
}

public	function	GetVariableString( )	{
	$r = NULL;
	
	foreach	($this->variableQueryArray as $v)	{
		if ($r == NULL)	{
			$r = $v;
		}	else	{
			$r	.=	", ".$v;
		}
	}
	return	$r;
}

	
public	function	GetFilterString( )	{
	$r = NULL;
	$flist = [];
	
	foreach($this->filterArray as $f)	{ $flist[] = $f;}		
	foreach($this->chooseCasesArray as $f)	{ $flist[] = $f;}		
//	$flist = array_merge($this->filterArray, $this->chooseCasesArray);
	
	if (count($flist) < 1)	{
		return $r;
		}
		
	foreach($flist as $f)	{
		if ($r == NULL)	{
			$r = " WHERE ".$f;
			}	else	{
			$r .= " AND ".$f;
			}
		}
	return	$r;
	}
	
	
public	function	GetTableReferences( )	{
	return	$this->tableReferences;
}



public	function	GetSearch( )	{
	$r = NULL;
	$r =	"<strong>Variables: </strong>".$this->getVariableString()."<br>";
	$r	.=	"<strong>Filter: </strong>".$this->getFilterString()."<br>";
	return $r;
}


public	function	GetDescription($theTable, $DBH)	{

	//	assemble a description of the search in the variable $s.			
	foreach	($this->variableNameArray as $v)	{

	    $params = [ "v" => $v ];
		$row	=	eeps_MySQL_getOneRow($DBH, "SELECT * FROM $theTable WHERE NAME = :v", $params);
		$oName = $row['NAMEOUT'];
		$Desc = $row['DESCRIPTION'];
		$units = $row['UNITS'];

		$unitsPhrase = "";
		if ($units) {
		    $unitsPhrase = " ($units)";
        }
		$s .= "\n<strong>$oName</strong>: $Desc $unitsPhrase<br>";
	}	
				
	return	$s;
}

}		//		end of class



?>