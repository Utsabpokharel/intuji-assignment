<?php

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["upload_client_secret"])) {

    if (isset($_FILES["client_secret_file"]) && $_FILES["client_secret_file"]["error"] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["client_secret_file"]["name"]);
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));


        if (file_exists($target_file)) {
            echo "Sorry, file already exists.";
            $uploadOk = 0;
        }

        if ($fileType != "json") {
            echo "Sorry, only JSON files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        } else {
            if (move_uploaded_file($_FILES["client_secret_file"]["tmp_name"], $target_file)) {
                $_SESSION['client_secret_path'] = $target_file;
                echo "The file " . htmlspecialchars(basename($_FILES["client_secret_file"]["name"])) . " has been uploaded.";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }
}

if (!isset($_SESSION['client_secret_path']) || !file_exists($_SESSION['client_secret_path'])) {
    echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' enctype='multipart/form-data'>";
    echo "Please upload your client secret JSON file: <input type='file' name='client_secret_file' required>";
    echo "<input type='submit' name='upload_client_secret' value='Upload'>";
    echo "</form>";
    exit;
}

require __DIR__ . '/vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig($_SESSION['client_secret_path']);
$client->setAccessType('offline');
$client->addScope(Google_Service_Calendar::CALENDAR);

if (!isset($_SESSION['access_token'])) {
    if (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $_SESSION['access_token'] = $token;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $authUrl = $client->createAuthUrl();
        echo "<a href='$authUrl'>Connect to Google Calendar</a>";
        exit;
    }
} else {
    $client->setAccessToken($_SESSION['access_token']); // Set access token if available
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        $_SESSION['access_token'] = $client->getAccessToken();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_event"])) {
    $eventSummary = $_POST['event_summary'];
    $eventLocation = $_POST['event_location'];
    $eventDescription = $_POST['event_description'];
    $eventStartTime = $_POST['event_start_time'];
    $eventEndTime = $_POST['event_end_time'];

    $service = new Google_Service_Calendar($client);
    $event = new Google_Service_Calendar_Event([
        'summary' => $eventSummary,
        'location' => $eventLocation,
        'description' => $eventDescription,
        'start' => [
            'dateTime' => $eventStartTime,
            'timeZone' => 'Asia/Kathmandu',
        ],
        'end' => [
            'dateTime' => $eventEndTime,
            'timeZone' => 'Asia/Kathmandu',
        ],
    ]);
    $calendarId = 'primary';
    $event = $service->events->insert($calendarId, $event);

    echo "Event created successfully!";
}

?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    Event Summary: <input type="text" name="event_summary" required><br>
    Event Location: <input type="text" name="event_location"><br>
    Event Description: <textarea name="event_description"></textarea><br>
    Event Start Time: <input type="datetime-local" name="event_start_time" required><br>
    Event End Time: <input type="datetime-local" name="event_end_time" required><br>
    <input type="submit" name="create_event" value="Create Event">
</form>

<?php

echo "<br><a href='{$_SERVER['PHP_SELF']}?disconnect=true'>Disconnect from Google Calendar</a>";

if (isset($_GET['disconnect'])) {
    unset($_SESSION['access_token']);

    $client->revokeToken();
    echo "<br>Disconnected from Google Calendar!";
}
?>