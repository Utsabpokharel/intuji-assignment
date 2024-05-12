<?php
session_start();
require_once 'vendor/autoload.php'; // Include Google API PHP Client Library

$client = new Google_Client();
$client->setAuthConfig('client_secret.json');
$client->addScope(Google_Service_Calendar::CALENDAR);
$client->setRedirectUri('http://localhost/intuji/callback.php');

if (!isset($_SESSION['access_token']) || !$_SESSION['access_token']) {
    header('Location: index.php');
    exit;
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

$client->setAccessToken($_SESSION['access_token']);
$service = new Google_Service_Calendar($client);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $summary = $_POST['summary'];
    $startDateTime = $_POST['start_date'] . ':00+05:45';
    $endDateTime = $_POST['end_date'] . ':00+05:45';
    $location = $_POST['location'];
    $description = $_POST['description'];

    $event = new Google_Service_Calendar_Event(array(
        'summary' => $summary,
        'location' => $location,
        'description' => $description,
        'start' => array(
            'dateTime' => $startDateTime,
            'timeZone' => 'Asia/Kathmandu',
        ),
        'end' => array(
            'dateTime' => $endDateTime,
            'timeZone' => 'Asia/Kathmandu',
        ),

    ));

    $calendarId = 'primary';
    $event = $service->events->insert($calendarId, $event);
    header('Location: index.php?action=list');
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Create Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between">
            <?php
            echo '<a href="index.php" class="btn btn-secondary btn-sm">List Events</a><br>';
            echo '<a href="?action=create" class="btn btn-success btn-sm">Create Event</a><br>';

            echo '<a href="?logout" class="btn btn-danger btn-sm">Disconnect</a>';
            ?>
        </div>
        <hr>
        <h3>Create Event</h3>
        <form method="POST" action="createevents.php">
            <div class="row">
                <div class="col-md-6">
                    <label for="summary">Title:</label>
                    <input type="text" class="form-control" name="summary" required><br>
                </div>
                <div class="col-md-6">
                    <label for="location">Location:</label>
                    <input type="text" class="form-control" name="location" value=""><br>
                </div>
                <div class="col-md-6">
                    <label for="start_date">Start Date:</label>
                    <input type="datetime-local" name="start_date" class="form-control" onclick="this.showPicker();"> <br>
                </div>
                <div class="col-md-6">
                    <label for="start_time">End Date:</label>
                    <input type="datetime-local" name="end_date" class="form-control" onclick="this.showPicker();"> <br>
                </div>

                <div class="col-md-12">
                    <label for="description">Description:</label><br>
                    <textarea id="description" name="description" rows="4" cols="50" class="form-control"></textarea><br>
                </div>
            </div>
            <input type="submit" value="Create Event" class="btn btn-primary">
        </form>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>