<?php
// Initialize the session
session_start();

// Check if form is submitted for client secret file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["upload_client_secret"])) {
    // Check if file was uploaded without errors
    if (isset($_FILES["client_secret_file"]) && $_FILES["client_secret_file"]["error"] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["client_secret_file"]["name"]);
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file already exists
        if (file_exists($target_file)) {
            echo "Sorry, file already exists.";
            $uploadOk = 0;
        }

        // Allow only JSON files
        if ($fileType != "json") {
            echo "Sorry, only JSON files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
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

// If client secret file is not uploaded or invalid, ask for it
if (!isset($_SESSION['client_secret_path']) || !file_exists($_SESSION['client_secret_path'])) {
    echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' enctype='multipart/form-data'>";
    echo "Please upload your client secret JSON file: <input type='file' name='client_secret_file' required>";
    echo "<input type='submit' name='upload_client_secret' value='Upload'>";
    echo "</form>";
    exit;
}

// Include Google API Client Library
require __DIR__ . '/vendor/autoload.php';

// Initialize the Google Client
$client = new Google_Client();
$client->setAuthConfig($_SESSION['client_secret_path']); // Path to client secret JSON file
$client->setAccessType('offline'); // Allow for offline access
$client->addScope(Google_Service_Calendar::CALENDAR);
$client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob'); // Set redirect URI to "urn:ietf:wg:oauth:2.0:oob"

// If user is not authenticated, redirect to Google OAuth consent screen
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
    // If access token expired, refresh it
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        $_SESSION['access_token'] = $client->getAccessToken();
    }
}

// Check if form is submitted for creating event
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_event"])) {
    $eventSummary = $_POST['event_summary'];
    $eventLocation = $_POST['event_location'];
    $eventDescription = $_POST['event_description'];
    $eventStartTime = $_POST['event_start_time'];
    $eventEndTime = $_POST['event_end_time'];

    // Create an event on the user's calendar
    $service = new Google_Service_Calendar($client);
    $event = new Google_Service_Calendar_Event([
        'summary' => $eventSummary,
        'location' => $eventLocation,
        'description' => $eventDescription,
        'start' => [
            'dateTime' => $eventStartTime,
            'timeZone' => 'America/Los_Angeles', // Change timezone if needed
        ],
        'end' => [
            'dateTime' => $eventEndTime,
            'timeZone' => 'America/Los_Angeles', // Change timezone if needed
        ],
    ]);
    $calendarId = 'primary'; // Use 'primary' for the primary calendar
    $event = $service->events->insert($calendarId, $event);

    echo "Event created successfully!";
}

// Display form for creating event
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
// Display option to disconnect account
echo "<br><a href='{$_SERVER['PHP_SELF']}?disconnect=true'>Disconnect from Google Calendar</a>";

// Disconnect account if requested
if (isset($_GET['disconnect'])) {
    unset($_SESSION['access_token']);
    // Optionally, revoke the token if desired
    $client->revokeToken();
    echo "<br>Disconnected from Google Calendar!";
}
?>