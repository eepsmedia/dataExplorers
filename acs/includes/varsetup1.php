<?php

/**
 * This is the varsetup1 file for the WEB ACS explorer
 */
	session_start( );
	
// set up mySQL

include('../includes/functions1.php');	//
include('../common/dbConnect.php');     //  define mySQL access functiions

require_once('../../../../connections/acsConnection.php');		//	connect to ACS db on eeps site


    $debugging = false;
    $debug = "Debugging: <br>";
    $footerguts = NULL;
    $textVersion = NULL;
	
    $varTable					= 'variables';
	$groupTable					=	'groups';
	$searchTable				=	'searches';
    $peepsTable                 =   'peeps';
    $recodeTable                 =   'decoder';
	
	define("DATE_FORMAT", "d M Y H:i");
    date_default_timezone_set("America/Los_Angeles");

//--------------------------------------------------------
?>
