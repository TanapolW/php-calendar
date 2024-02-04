<head>
    <link href="calendar.css" type="text/css" rel="stylesheet" />
</head>
<?php
include 'calendar.php';

$calendar = new Calendar();

echo $calendar->show();
?>