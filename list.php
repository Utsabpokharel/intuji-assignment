<?php
$events = $service->events->listEvents('primary');

?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>



<div class="container mt-5">
    <div class="d-flex justify-content-between">
        <?php
        // echo '<a href="?action=list" class="btn btn-secondary btn-sm">List Events</a><br>';
        echo '<a href="?action=create" class="btn btn-success btn-sm">Create Event</a><br>';

        echo '<a href="?logout" class="btn btn-danger btn-sm">Disconnect</a>';
        ?>
    </div>
    <hr>
    <div class="col-md-12 mb-3 pb-4">
        <h3>Google Calendar Events</h3>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-success">
                    <tr>
                        <th>S.N</th>
                        <th>Event</th>
                        <th>Start Date Time</th>
                        <th>End Date Time</th>
                        <th>Location</th>
                        <th>Details</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <ul>
                        <?php
                        $eventsList = $events->getItems();

                        usort($eventsList, function ($a, $b) {
                            $createdDateA = new DateTime($a->created);
                            $createdDateB = new DateTime($b->created);
                            return $createdDateB <=> $createdDateA; // Compare created dates
                        });
                        $i = 1;
                        foreach ($eventsList as $event) {
                            $eventStartDate = new DateTime($event->start->dateTime);
                            $eventEndDate = new DateTime($event->end->dateTime);
                            $currentDate = new DateTime();

                        ?>


                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= ($event->getSummary()) ?></td>
                                <td><?= ($eventStartDate->format('Y-m-d')) . ' ' . ($eventStartDate->format('H:i:s')) ?></td>
                                <td><?= ($eventEndDate->format('Y-m-d')) . ' ' . ($eventEndDate->format('H:i:s')) ?></td>
                                <td><?= ($event->location) ?></td>
                                <td><?= ($event->description) ?></td>
                                <td><a href='?action=delete&event_id=<?= $event->id ?>' style="text-decoration: none;">Delete</a> </td>
                            </tr>

                        <?php
                        }

                        ?>
                    </ul>
                </tbody>
            </table>
        </div>
    </div>
</div>