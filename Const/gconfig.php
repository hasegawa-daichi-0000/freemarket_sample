<?php
//Include Google Client Library for PHP autoload file
require_once 'vendor/autoload.php';

//Make object of Google API Client for call Google API
$google_client = new Google_Client();
 
//Set the OAuth 2.0 Client ID
$google_client->setClientId('424318302416-mf6i9c5at944nl08ngeva40o24sg4gjh.apps.googleusercontent.com');
 
//Set the OAuth 2.0 Client Secret key
$google_client->setClientSecret('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');
 
//Set the OAuth 2.0 Redirect URI
$google_client->setRedirectUri('http://localhost:8888/webservice_practice01/dashboard.php');
 
//
$google_client->addScope('email');
 
$google_client->addScope('profile');
 
//start session on web page
session_start();
 
?>