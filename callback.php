<?php
session_start();
require_once 'vendor/autoload.php'; // Include Google API PHP Client Library

$client = new Google_Client();
if (isset($_SESSION['client_secret_file'])) {
    $client->setAuthConfig($_SESSION['client_secret_file']);
    $client->setRedirectUri('http://127.0.0.1/intuji/callback.php');
    $client->addScope(Google_Service_Calendar::CALENDAR);
}

if (!isset($_GET['code'])) {
    header('Location: index.php');
    exit;
}

$client->authenticate($_GET['code']);
$_SESSION['access_token'] = $client->getAccessToken();
header('Location: index.php');
