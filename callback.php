<?php
session_start();
require_once 'vendor/autoload.php'; // Include Google API PHP Client Library

$client = new Google_Client();
$client->setAuthConfig('client_secret.json');
$client->addScope(Google_Service_Calendar::CALENDAR);
$client->setRedirectUri('http://localhost/intuji/callback.php');

if (!isset($_GET['code'])) {
    header('Location: index.php');
    exit;
}

$client->authenticate($_GET['code']);
$_SESSION['access_token'] = $client->getAccessToken();
header('Location: index.php');
