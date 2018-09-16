<?php
	include('../includes/varsetup1.php');		//	set up variables, defeine functions
	include('../includes/Search.php');		//	define Search class

	$page_title = "NHANES data explorer: preview data";
	
	$modDate =  date("l jS F Y h:i A", getlastmod());	
//	echo	$page_title;
	
//
//	do pre-page calculations
//
	$guts = NULL;
	$tableReferences = NULL;			//		MySQL table refs, e.g., DEMOG as t1
	$varQueryArray = NULL;				//		MySQL variable names with table ID, eg: t1.RIAGENDR
	$varLabelArray = NULL;				//		English names of variables for headers on tables
	$filterArray = NULL;					//		relational phrases, e.g., t1.SEQN = t2.SEQN
	//	all of the above get figured out from the postkeys session variable.
	//	only the next (chooseCasesArray) actually has been figured out in Choose.php
	
	$chooseCasesArray = array( );			//			e.g., AGE > 10 (from the CHOOSE page)
	if (isset($_SESSION['chooseCasesArray']))	{
		$chooseCasesArray = $_SESSION['chooseCasesArray'];
	}
		
/*
echo	"<h4>IN PREVIEW</H4>";
echo	"<br><strong>Postkeys</strong>:";
print_r($postkeys);		//	debug
echo	"<br><strong>SESSION</strong>:";
print_r($_SESSION);		//	debug		
echo	"<br><strong>POST</strong>:";
print_r($_POST);		//	debug

eeps_printr($postkeys, 0);
echo	"<br>chooseCasesArray: ";	print_r($chooseCasesArray);
*/
	
	//
	//		assemble an array of the variables that were checked.
	//
	
	$tableheader = "\n<tr>";
	$postkeys = $_SESSION['choosePostKeys'];		//	what are the keys? They will contain the names of variable checkboxes.
	$tableSet = NULL;		//	the set of which tableIDs this query uses.

	//	loop over all the variables in the checkboxes
	foreach($postkeys as $v)	{
		$row = nhanes_getOneRow("SELECT * FROM $varTable WHERE NAME = '".$v."'");
		if ($row)	{	//	it was in the list
			$tableNumber = $row['TABLEID'];				//	the numeric table ID to which this variable belongs
			$tableSet[$tableNumber] = true;				//	this table is included in the set of tables
			$varQueryArray[] = "t$tableNumber.".$v;		//	the variable names we use in queries, VARNAME.t3, etc.
			$varNameArray[]	=	$v;						//	the raw SQL names without table IDs.
			$varLabelArray[$v] = $row['NAMEOUT'];		//	this is the translated "English" name of the variable for output
			$tableheader	.= " <th>$varLabelArray[$v]</th> ";	//	put the "output" name in the table header for display (and export)	
			}
		}
	
	//	in case nothing was selected, get sex and age.
	if (count($varNameArray) == 0)	{
		$varQueryArray[] = "t1.RIAGENDR";
		$varQueryArray[] = "t1.RIDAGEYR";
		$varNameArray[]	=	"RIAGENDR";
		$varNameArray[]	=	"RIDAGEYR";
		$tableheader	.= " <th>Sex</th> <th>Age</th>";
		$tableSet[1] = true;	
		}
	$tableheader	.=	"</tr>";

	//		construct a universal table selector
	//		and filter parts
	//		depends on which tables were represented in the set of variables (use tableSet)
	
	
	$r	=	nhanes_getQueryResult("SELECT * FROM $metaTable");
	while ($row = mysql_fetch_array($r))	{
		$theID = $row['ID'];
		$theName = $row['tableName'];
		if	($theID == 1)	{
			$tableReferences	.=	$theName." AS t".$theID;
		} else if	($tableSet[$theID])	{
			$tableReferences	.=	", ";
			$filterItem = "(t1.SEQN = t".$theID.".SEQN)";
			$filterArray[]	=	$filterItem;		//	add to the filterArray[] array
			//debug
			$tableReferences	.=	$theName." AS t".$theID;
		}
	}
	$guts	.=	"Table refs: ".$tableReferences;			//	debug
	
//
//	Store the Search
//
	if	(!isset($theSearch))	{
		$theSearch = new Search( );
	}
	
	//	var_dump($theSearch);

	$theSearch->variableNameArray = $varNameArray;
	$theSearch->variableQueryArray = $varQueryArray;
	$theSearch->filterArray = $filterArray;
	$theSearch->chooseCasesArray = $chooseCasesArray;
	$theSearch->tableReferences = $tableReferences;

	$guts 	.= 	"<br> FILTER ARRAY STUFF";			//	debug
	foreach($filterArray as $f)	{	$guts 	.= 	"<br>".$f;	}		//	debug
	
	
	$insertQ = "INSERT INTO $searchTable (SEARCH, TIME) VALUES ('"
			. serialize($theSearch) . "', NOW()) ";
		
			
	$r	=	nhanes_getQueryResult($insertQ);
	
	$theSearchID = mysql_insert_id( );
	$_SESSION['theSearch'] = $theSearch;


	$varListForQuery = $theSearch->GetVariableString( );
	$filterForQuery  = $theSearch->GetFilterString( );
	$tableReferences	=	$theSearch->GetTableReferences( );
	
	$Q = "SELECT $varListForQuery ".
				"FROM $tableReferences $filterForQuery";
	$guts	.=	"<br>QUERY: $Q<br>";			//	debug
	//	echo	$guts;
	
	$r	=	nhanes_getQueryResult($Q);
	$nRows = mysql_num_rows($r);
	$r	=	nhanes_getQueryResult("$Q ORDER BY RAND() LIMIT 10");

//	construct table guts 
	$tableguts = NULL;


	while ($row = mysql_fetch_array($r))	{
		$tableguts	.=	"\n<tr>";
		foreach($varNameArray as $v)	{
			$rawval = $row[$v];
			$decodeRow = nhanes_getOneRow("SELECT * FROM $recodeTable ".
							"WHERE (VARNAME = '".$v."') AND (CODE = '".$rawval."')");
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

	$footerGuts = "<h4>Search specification</h4>";
	$footerGuts .=	$theSearch->GetSearch( );
	$footerGuts	.=	"<br>";
//
//	actual page stuff starts here -------------------------
//

	include('../includes/header.php');
	
//	echo	"POST: "; print_r($_POST);
//	echo	$guts;
?>



<h4>Preview data</h4>

<p>This preview page shows ten cases. The whole set has <?php echo $nRows; ?> cases.</p>

<form action="results.php" method="get">
	<input type="submit" name="getResults" value="Get entire sample">
	<input type="hidden" value="<?php echo $theSearchID ?>" name = "theID">
	 Sample size: <input type="text" value="100" name="sampleSize" size="5">
</form>
<?php
	
	echo	"\n<table> $tableheader $tableguts </table>";
	
?>
</td>

<!-- 		Right column						 -->

<td id = "rightcol" width="120"> 

  <?php
	
	echo	"<p>Insert ID = $theSearchID";
?>
	<p>Last modified:<br> 
		<!-- #BeginDate format:Am1 -->July 16, 2007<!-- #EndDate -->
	
		<!-- PAGE ENDS -->
		<?php
	include('../includes/footer.php');
?>