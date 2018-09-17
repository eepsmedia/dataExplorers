<?php
include('../includes/varsetup1.php');        //	set up variables, defeine functions
include('../includes/Search.php');        //	define Search class

$page_title = "NHANES data explorer: preview data";

$modDate = date("l jS F Y h:i A", getlastmod());
//	echo	$page_title;

//
//	do pre-page calculations
//
$guts = NULL;
$tableReferences = NULL;            //		MySQL table refs, e.g., DEMOG as t1
$varQueryArray = NULL;                //		MySQL variable names with table ID, eg: t1.RIAGENDR
$varLabelArray = NULL;                //		English names of variables for headers on tables
$filterArray = [];                    //		relational phrases, e.g., t1.SEQN = t2.SEQN
//	all of the above get figured out from the postkeys session variable.
//	only the next (chooseCasesArray) actually has been figured out in Choose.php

$chooseCasesArray = array();            //			e.g., AGE > 10 (from the CHOOSE page)

if (isset($_SESSION['chooseCasesArray'])) {
    $chooseCasesArray = $_SESSION['chooseCasesArray'];
}


//
//		assemble an array of the variables that were checked.
//

$tableheader = "\n<tr>";
$postkeys = $_SESSION['choosePostKeys'];        //	what are the keys? They will contain the names of variable checkboxes.
$tableSet = NULL;        //	the set of which tableIDs this query uses.

//	loop over all the variables in the checkboxes
foreach ($postkeys as $v) {

    $query = "SELECT * FROM $varTable WHERE NAME = :varName";
    $params = ["varName" => $v];

    $arrayOut = eeps_MySQL_getOneRow($DBH, $query, $params);

    if (count($arrayOut) > 0) {
        $row = $arrayOut[0];
        $tableNumber = $row['TABLEID'];                //	the numeric table ID to which this variable belongs
        $tableSet[$tableNumber] = true;                //	this table is included in the set of tables
        $varQueryArray[] = "t$tableNumber." . $v;        //	the variable names we use in queries, VARNAME.t3, etc.
        $varNameArray[] = $v;                        //	the raw SQL names without table IDs.
        $varLabelArray[$v] = $row['NAMEOUT'];        //	this is the translated "English" name of the variable for output
        $tableheader .= " <th>$varLabelArray[$v]</th> ";    //	put the "output" name in the table header for display (and export)
    }
}

//	in case nothing was selected, get sex and age.
if (count($varNameArray) == 0) {
    $varQueryArray[] = "t1.RIAGENDR";
    $varQueryArray[] = "t1.RIDAGEYR";
    $varNameArray[] = "RIAGENDR";
    $varNameArray[] = "RIDAGEYR";
    $tableheader .= " <th>Sex</th> <th>Age</th>";
    $tableSet[1] = true;
}
$tableheader .= "</tr>";

//		construct a universal table selector
//		and filter parts
//		depends on which tables were represented in the set of variables (use tableSet)


$params = array();
$allTables = eeps_MySQL_getQueryResult($DBH, "SELECT * FROM $metaTable", $params);
foreach ($allTables as $thisTable) {
    $theID = $thisTable['ID'];
    $theName = $thisTable['tableName'];
    if ($theID == 1) {
        $tableReferences .= $theName . " AS t" . $theID;
    } else if ($tableSet[$theID]) {
        $tableReferences .= ", ";
        $filterItem = "(t1.SEQN = t" . $theID . ".SEQN)";

        $filterArray[] = $filterItem;        //	add to the filterArray[] array
        //debug
        $tableReferences .= $theName . " AS t" . $theID;
    }
}
$guts .= "Table refs: " . $tableReferences;            //	debug

//
//	Store the Search
//
if (!isset($theSearch)) {
    $theSearch = new Search();
}

//	var_dump($theSearch);

$theSearch->variableNameArray = $varNameArray;
$theSearch->variableQueryArray = $varQueryArray;
$theSearch->filterArray = $filterArray;
$theSearch->chooseCasesArray = $chooseCasesArray;
$theSearch->tableReferences = $tableReferences;

$guts .= "<br> FILTER ARRAY STUFF";            //	debug
foreach ($filterArray as $f) {
    $guts .= "<br>" . $f;
}        //	debug



$insertQ = "INSERT INTO $searchTable (SEARCH, TIME) VALUES ('"
        . serialize($theSearch) . "', NOW()) ";

$params = [];

$r	=	eeps_MySQL_getQueryResult($DBH, $insertQ, $params);

$theSearchID = $DBH->lastInsertId();

$_SESSION['theSearch'] = $theSearch;


$varListForQuery = $theSearch->GetVariableString();
$filterForQuery = $theSearch->GetFilterString();
$tableReferences = $theSearch->GetTableReferences();

$Q = "SELECT $varListForQuery " .
    "FROM $tableReferences $filterForQuery";
$guts .= "<br>QUERY: $Q<br>";            //	debug
//	echo	$guts;
$params = [];

$r = eeps_MySQL_getQueryResult($DBH, "$Q ORDER BY RAND() LIMIT 10", $params);

//	construct table guts 
$tableguts = NULL;


foreach ($r as $row) {
    $tableguts .= "\n<tr>";
    foreach ($varNameArray as $v) {
        $val = decodeNHANES($row, $v, $recodeTable, $DBH);
        $tableguts .= "<td>$val</td>";
    }
    $tableguts .= "</tr>";
}
//	prepare informational message for the footer
//

$footerGuts = "<h4>Search specification</h4>";
$footerGuts .= $theSearch->GetSearch();
$footerGuts .= "<br>";
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
        <input type="hidden" value="<?php echo $theSearchID ?>" name="theID">
        Sample size: <input type="text" value="100" name="sampleSize" size="5">
    </form>
<?php

echo "\n<table> $tableheader $tableguts </table>";

?>
    </td>

    <!-- 		Right column						 -->

    <td id="rightcol" width="120">

<?php

echo "<p>Insert ID = $theSearchID";
?>
    <p>Last modified:<br>
        <!-- #BeginDate format:Am1 -->September 16, 2018<!-- #EndDate -->

        <!-- PAGE ENDS -->
<?php
include('../includes/footer.php');
?>