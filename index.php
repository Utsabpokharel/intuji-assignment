<?php
// Include Google API Client Library
require __DIR__ . '/vendor/autoload.php';

// Initialize the session
session_start();

// Initialize the Google Client
$client = new Google_Client();
$client->setAuthConfig('client_secret.json'); // Path to your client secret JSON file
$client->setAccessType('offline'); // Allow for offline access
$client->addScope(Google_Service_Calendar::CALENDAR);
$client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');

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