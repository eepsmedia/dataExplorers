<?php

//session_start();
include('../includes/varsetup1.php');            //	set up variables
include('../includes/Search.php');            //	define mySQL access functions etc, moved to varsetup


unset($_SESSION['theSearch']);        //	debug


$debug = NULL;
$chooseCasesArray = array();
//
//		deal with incoming POST
//
$checkingDefaultVars = false;                            //	in general, check those vars who were checked before.
$forcingDefaultVars = false;                            //	in general, check those vars who were checked before.

if (count($_POST) > 0) {
    $postkeys = array_keys($_POST);        //	what are the keys? They will contain the names of variable checkboxes.
    $debug .= "Rich POST array. We'll use the variable info.<br>";
    //
    //		assemble the rest of the chooseCasesArray (WHERE clause)
    //

    $AgeLowLimit = $_POST['ageLowLimit'];
    $AgeHiLimit = $_POST['ageHiLimit'];
    if (is_numeric($AgeLowLimit)) {
        $chooseCasesArray[] = "(RIDAGEYR > $AgeLowLimit)";
    }
    if (is_numeric($AgeHiLimit)) {
        $chooseCasesArray[] = "(RIDAGEYR < $AgeHiLimit)";
    }

} else {    //	No POST. We came from outside. We'll use the variables we remember from the SESSION var
    $debug .= "empty POST array. Came in from outside.<br>";

    if (isset($_SESSION['choosePostKeys'])) {
        $postkeys = $_SESSION['choosePostKeys'];
    } else {
        $postkeys = array();
    }

    if (count($postkeys) == 0) {    //	but we don't remember what variables were checked. Better use defaults.
        $checkingDefaultVars = true;
    }
}


if ($checkingDefaultVars) {
    $debug .= "<br>...We are checking default variables";
} else {
    $debug .= "<br>...We are NOT checking default variables";
}

//
//		If the Preview button was pushed, we redirect to the preview page.
//		note that the session variables would have been set before we return here.
//

if (array_key_exists("Preview", $_POST)) {
    $_SESSION['choosePostKeys'] = $postkeys;
    header('Location: preview.php');
}
if (array_key_exists("Defaults", $_POST)) {
    $forcingDefaultVars = true;
}

/*
echo	$debug;
echo	"<br>------------<br>";
*/

$page_title = "NHANES data explorer: choose your data";

$modDate = date("l jS F Y h:i A", getlastmod());

//
//	more variable setup
//
if (isset($_SESSION['showWholeSetOfVarsFrom'])) {
    $showWholeSetOfVarsFrom = $_SESSION['showWholeSetOfVarsFrom'];
}

//
//	LOOP over all tables, construct the form guts
//	
$checkboxguts = NULL;            //	this will hold the HTML code for the whole table/variable form guts


$tableRows = eeps_MySQL_getQueryResult($DBH,"SELECT * FROM $metaTable", array());
$tableCount = count($tableRows);

if (isset($showWholeSetOfVarsFrom)) {
    $firstTimeThrough = false;
} else {
    $firstTimeThrough = true;
}

/*
echo	"<h4>IN CHOOSE</H4>";
echo	"<br><strong>Postkeys</strong>:";
print_r($postkeys);		//	debug
echo	"<br><strong>SESSION</strong>:";
print_r($_SESSION);		//	debug		
echo	"<br><strong>POST</strong>:";
print_r($_POST);		//	debug
*/

foreach ($tableRows as $thisTable) {
//  while ($row = mysql_fetch_array($tableRows)) {
    $theTableID = $thisTable['ID'];
    $theTableName = $thisTable['tableName'];

    $theTablePurpose = $thisTable['purpose'];
    $chooseVarHeading = "Choose variables from the $theTablePurpose table";

    $checkboxguts .= "\n\n<h4>$chooseVarHeading";

    if ($firstTimeThrough) {
        $showWholeSetOfVarsFrom[$theTableID] = false;
    } else {        //	check to see if THIS table's expand/collapse button was pushed
        if (array_key_exists($theTableName, $_POST)) {
            $showWholeSetOfVarsFrom[$theTableID] = !$showWholeSetOfVarsFrom[$theTableID];        //	reverse the sense
        }
    }

    //
    //	put an expand or collapse button at the end of the <h4> header line
    //	depending on whether this table is expanded.
    //
    if ($showWholeSetOfVarsFrom[$theTableID]) {
        $checkboxguts .= "\n\t<input type='submit' name='" . $theTableName . "' value='collapse'>";
    } else {
        $checkboxguts .= "\n\t<input type='submit' name='" . $theTableName . "' value='expand'>";
    }

    $checkboxguts .= "</h4>";            //	done with table's header line, ready to loop over vars.

    //
    //	loop over all variables within each table (ID = $theTable)
    //

    $params = ["tt" => $theTableID];
    $query = "SELECT * FROM varlist WHERE TABLEID = :tt";
    //  $query = "SELECT * FROM varlist WHERE TABLEID = 1";

    $variablesFromThisTable = eeps_MySQL_getQueryResult( $DBH, $query, $params);
    foreach($variablesFromThisTable as $thisVariable) {
    //while ($row = mysql_fetch_array($r)) {
        $oName = $thisVariable['NAMEOUT'];            //	output (English, header) name for variable
        $Desc = $thisVariable['DESCRIPTION'];    //	text comment or description of what it means
        $Name = $thisVariable['NAME'];                    //	internal, MySQL (and NHANES) name
        $defCheck = $thisVariable['DEFCHECK'];        //	is it checked by default? (1 = yes)
        $defShow = $thisVariable['DEFSHOW'];        //	do we show it by default?
        $units = $thisVariable['UNITS'];                //	what units is it in?
        $Show = $defShow;                            //	we show what is shown by default (to begin with)

        $Check = ($checkingDefaultVars && $defCheck);        //	boolean, do we check this variable?
        if (in_array($Name, $postkeys)) {
            $Check = true;
        }        //	is it was in the list of checked vars before, keep it checked

        if ($forcingDefaultVars) {
            $Check = ($forcingDefaultVars && $defCheck);    //	but if we're forcing it, nothing else matters
        }

        if (strlen($units) > 0) {
            $unitsString = " (" . $units . ") ";
        } else {
            $unitsString = NULL;
        }
        $checkboxLabel = "<strong>$oName</strong>: " . $Desc . $unitsString;


        if ($Check) {
            $Show = 1;        //		a checked variable always stays shown.
            $checkstring = "checked";
        } else {
            $checkstring = "";
        }

        if ($Show or $showWholeSetOfVarsFrom[$theTableID]) {
            $checkboxguts .= "\n\t" . '<input name="' . $Name . '" type = "checkbox" value = "' . $Name . '" ' . $checkstring . '>';
            $checkboxguts .= "  " . $checkboxLabel . "<br>";
        }
    }
    //----------------	end of variable loop
}
//------------------	end of table loop

//	Save session variables
//			recording the state of expansion....
//			recording the list of post keys (variables that have been checked)

$_SESSION['showWholeSetOfVarsFrom'] = $showWholeSetOfVarsFrom;
$_SESSION['choosePostKeys'] = $postkeys;
$_SESSION['chooseCasesArray'] = $chooseCasesArray;


//	--------------------------------------------------------------------------
//	actual page stuff starts here
include('../includes/header.php');
?>
    <form action="choose.php" method="post" enctype="multipart/form-data" name="ChooseVars">
        <input type='submit' value='preview the data' name='Preview'>
        <input type='submit' value='check default variables' name='Defaults'><br>
        <?php
        //echo	$debug;
        echo $checkboxguts;
        ?>

        </td>

        <!-- 	Right-hand column							 -->
        <td id="rightcol">
            <h4>Specify cases</h4>

            <input type="text" value="<?php echo $AgeLowLimit ?>"
                   name='ageLowLimit' maxlength="5" size="5">
            &lt; AGE &lt;
            <input type="text" value="<?php echo $AgeHiLimit ?>"
                   name='ageHiLimit' maxlength="5" size="5">
    </form>

    <p>Last modified
        <!-- #BeginDate format:Am1 -->September 16, 2018<!-- #EndDate -->

        <!-- PAGE ENDS -->
<?php
include('../includes/footer.php');
?>