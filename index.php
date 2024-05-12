<?php
session_start();
require_once 'vendor/autoload.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$client = new Google_Client();
$client->setAuthConfig('client_secret.json');
$client->addScope(Google_Service_Calendar::CALENDAR);
$client->setRedirectUri('http://localhost/intuji/callback.php');

if (isset($_GET['logout'])) {
    unset($_SESSION['access_token']);
}


if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);
    $service = new Google_Service_Calendar($client);
    $events = $service->events->listEvents('primary');

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'list':
                require('list.php');
                break;
            case 'create':
                header('Location: createevents.php');
                exit;
                break;
            case 'delete':
                if (isset($_GET['event_id'])) {
                    $calendarId = 'primary';
                    $eventId = $_GET['event_id'];
                    $service->events->delete($calendarId, $eventId);
                    header('Location: index.php?action=list');
                    exit;
                } else {
                    echo "Event ID not provided.";
                }
                break;
        }
    } else {
        // echo '<div class="container mt-5">';
        // echo '<h3>Google Calendar Integration</h3>';
        // echo ' <div class="d-flex">';

        // echo '<a href="?action=list" class="btn btn-secondary btn-sm">List Events</a>&nbsp;';
        // echo '<a href="?action=create" class="btn btn-success btn-sm">Create Event</a>&nbsp;';

        // echo '<a href="?logout" class="btn btn-danger btn-sm">Disconnect</a>';

        // echo ' </div>';
        // echo ' </div>';
        require('list.php');
    }
} else {
    $authUrl = $client->createAuthUrl();
    echo '<h1>Google Calendar Integration</h1>';
    echo '<a href="' . $authUrl . '">Connect to Google Calendar</a>';
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">