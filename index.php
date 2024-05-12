<?php
session_start();
require_once 'vendor/autoload.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

use Google\Client;
use Google\Service\Calendar;

$client = new Client();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['client_secret_file'])) {
    // Handle file upload
    $directory = __DIR__ . '/uploads/' . $_POST['email'] . '/';
    $uploadDir = mkdir($directory, 0777, true);
    $uploadFile = $directory . basename($_FILES['client_secret_file']['name']);

    if (move_uploaded_file($_FILES['client_secret_file']['tmp_name'], $uploadFile)) {
        // File uploaded successfully, set auth config
        $client->setAuthConfig($uploadFile);
        $client->addScope(Calendar::CALENDAR);
        $redirectUri = 'http://127.0.0.1/intuji/callback.php';
        $client->setRedirectUri($redirectUri);
        $_SESSION['client_secret_file'] = $uploadFile;
        $authUrl = $client->createAuthUrl();
        header('Location: ' . $authUrl);
        exit;
    } else {
        echo "Error uploading file.";
    }
} elseif (isset($_SESSION['client_secret_file'])) {
    // Use stored client secret file
    $client->setAuthConfig($_SESSION['client_secret_file']);
    $client->addScope(Calendar::CALENDAR);
    $redirectUri = 'http://127.0.0.1/intuji/callback.php';
    $client->setRedirectUri($redirectUri);
    if (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $_SESSION['access_token'] = $token;
        header('Location: index.php');
        exit;
    }
} else {
    echo '<div class="container mt-5 text-center">';
    echo '<h3>Welcome to Event Calendar</h3>';
    echo '<form method="post" enctype="multipart/form-data" class="text-center mx-5">';
    echo '<input type="email" name="email" class="form-control mb-3" placeholder="Enter Your Email" required/>';
    echo '<input type="file" name="client_secret_file" class="form-control mb-3"/>';
    echo '<input type="submit" value="Upload" class="btn btn-primary"/>';
    echo '</form>';
    echo '</div>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">';
    exit;
}

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);
    $service = new Calendar($client);
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
        require('list.php');
    }
} else {
    echo '<div class="container mt-5 text-center">';
    echo '<h3>Welcome to Event Calendar</h3>';
    echo '<form method="post" enctype="multipart/form-data" class="text-center mx-5">';
    echo '<input type="email" name="email" class="form-control mb-3" placeholder="Enter Your Email" required/>';
    echo '<input type="file" name="client_secret_file" class="form-control mb-3"/>';
    echo '<input type="submit" value="Upload" class="btn btn-primary"/>';
    echo '</form>';
    echo '</div>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">';
    exit;
}
if (isset($_GET['logout'])) {
    unset($_SESSION['access_token']);
    header('Location: index.php');
    exit;
}
