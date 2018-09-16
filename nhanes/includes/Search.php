<?php

class		Search	{


//	members
public	$variableNameArray;
public	$variableQueryArray;		//	what variables are we querying about?
public	$filterArray;				//	what are our filter phrases (tables)?
public	$chooseCasesArray;			//	what are our filter phrases (data restrictions)? (age > 10)
public	$tableReferences;				//	

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
	$flist = NULL;
	
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


public	function	GetDescription($theTable)	{

	//	assemble a description of the search in the variable $s.			
	foreach	($this->variableNameArray as $v)	{
		$row	=	nhanes_getOneRow("SELECT * FROM $theTable WHERE NAME = '".$v."'");
		$oName = $row['NAMEOUT'];
		$Desc = $row['DESCRIPTION'];
		$units = $row['UNITS'];
		$s .= "\n<strong>$oName</strong>: $Desc<br>";
	}	
				
	return	$s;
}

}		//		end of class



?>