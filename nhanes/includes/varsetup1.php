<?php 
	session_start( );
	
// set up mySQL
include('../includes/functions1.php');	//	define mySQL access functions etc, moved to varsetup
require_once('../../../../connections/nhanesConnection.php');		//	connect to nhanes db


	$demographyTable		= 'demog';
	$varTable						= 'varlist';
	$recodeTable				=	'decoder';
	$searchTable				=	'searches';
	$metaTable					=	'metatable';
	
	define("DATE_FORMAT", "d M Y H:i");

//--------------------------------------------------------
?>
