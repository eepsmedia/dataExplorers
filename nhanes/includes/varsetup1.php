<?php
session_start();

// set up mySQL


include('../source/establishCredentials.php');    //	define mySQL access functions etc.
include_once('../../common/dbConnect.php');

$DBH = eeps_MySQL_connect("localhost", $user, $pass, $dbname);     //  works under MAMP....

echo "We have credentials, and a DBH<br>";


$demographyTable = 'demog';
$varTable = 'varlist';
$recodeTable = 'decoder';
$searchTable = 'searches';
$metaTable = 'metatable';

define("DATE_FORMAT", "d M Y H:i");

//--------------------------------------------------------

//  include('../includes/functions1.php');    //	define mySQL access functions etc, moved to varsetup

?>
