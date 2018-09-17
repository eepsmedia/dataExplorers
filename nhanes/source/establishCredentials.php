<?php
/**
 * Created by IntelliJ IDEA.
 * User: tim
 * Date: 9/16/18
 * Time: 12:46
 */

//  echo print_r($_SERVER, true);       //  un-comment to find the actual pathname of the server

$whence = "local";

$credentialFileNames = [
    "local" => "/Applications/MAMP/cred/nhanesCred.php"
];

$thisFileName = $credentialFileNames[$whence];

//  echo "Credential file: $thisFileName <br>";

include_once($thisFileName);

$user = $credentials[$whence]["user"];
$pass = $credentials[$whence]["pass"];
$dbname = $credentials[$whence]["dbname"];

//  echo "dbname : $dbname, user : $user, pass : $pass <br>";      //  to verify values



