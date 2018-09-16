<?php
include('../includes/Search.php');		//	define mySQL access functions etc, moved to varsetup

	$page_title = "NHANES data explorer: see all results";
	
	$modDate =  date("l jS F Y h:i A", getlastmod());	
//	echo	$page_title;
	
	include('../includes/varsetup1.php');		//	set up variables
//
//	do pre-page calculations
//
	//		extract the search info.
	//
	$theID = $_GET['theID'];
	$theSampleSize = $_GET['sampleSize'];
	if (is_numeric($theSampleSize) AND $theSampleSize > 0)	{
		$limitForQuery = " ORDER BY RAND( ) LIMIT $theSampleSize ";
		}
	
	$row = nhanes_getOneRow("SELECT * FROM $searchTable WHERE ID = '".$theID."'");
	$theSearch = unserialize($row['SEARCH']);
	$vararray = $theSearch->variableNameArray;

	$varListForQuery = $theSearch->GetVariableString( );
	$filterForQuery  = $theSearch->GetFilterString( );
	$tableReferences	=	$theSearch->GetTableReferences( );

	//	start assembling the table
	
	//	begin with the header row
	
	$tableheader = "\n<tr>";
	foreach($vararray as $v)	{
		$row = nhanes_getOneRow("SELECT * FROM $varTable WHERE NAME = '".$v."'");
		if ($row)	{	//	it was in the list
			$varLabelArray[$v] = $row['NAMEOUT'];
			$tableheader	.= " <th>$varLabelArray[$v]</th> ";	
			}
		}
	$tableheader	.=	"</tr>";
	// use $v to query $metaTable and get its stuff; put that into the array, assemble query.

	//	here is the MySQL query, constructed from its components...
	
	$theQuery = "SELECT $varListForQuery FROM $tableReferences $filterForQuery $limitForQuery";
	$r	=	nhanes_getQueryResult($theQuery);

//	construct table guts. We evalueate the results of the query one row at a time...

	$tableguts = NULL;
	$rownumber = 0;
	while ($row = mysql_fetch_array($r))	{
		$rownumber++;
		$tableguts	.=	"\n<tr>";
		foreach($vararray as $v)	{
			$rawval = $row[$v];
			
			//	decode the raw values from the table by looking them up in $recodeTable
			$decodeRow = nhanes_getOneRow( "SELECT * FROM $recodeTable WHERE (VARNAME = '"
				. $v . "') AND (CODE = '" . $rawval . "')" );
			if($decodeRow)	{
				$val = $decodeRow['RESULT'];
				}	else	{
				$val = $rawval;
				}
			$tableguts	.=	"<td>$val</td>";
			}
		$tableguts	.=	"</tr>";
		}
//	prepare informational message for the footer
//
	$footerGuts = "<h4>Entire MySQL query</h4>";
	$footerGuts	.=	"SELECT $varListForQuery <br> FROM $tableReferences <br> $filterForQuery $limitForQuery";
	$footerGuts	.=	"<br>";
//
//	actual page stuff starts here ---------------------
	include('../includes/header.php');
?>

<h4>Data Results</h4>	
<?php
	//echo	$guts."<br>\n";
	
	$s = $theSearch->getDescription($varTable);
	
	echo	$s;
	
	echo	"\n<br>This sample has a total of $rownumber cases.<br><br>";
	echo	"\n<table> $tableheader $tableguts </table>";
	
?>
</td>
<td id = "rightcol" width="120"> 

	<p>Last modified<br>
		<!-- #BeginDate format:Am1 -->July 16, 2007<!-- #EndDate -->
	
		<!-- PAGE ENDS -->
		<?php
	include('../includes/footer.php');
?>