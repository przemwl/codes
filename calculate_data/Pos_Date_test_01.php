<?php
/**
 * Created by PhpStorm.
 * User: siemek
 * Date: 5/20/15
 * Time: 8:24 AM
 */

require_once 'Pos_Date.php';
try {
    // create a Pos_Date object for the default time zone
    $local = new Pos_Date();
    // use the inhereited format() method to display the date and time
    echo '<p>Local time: ' . $local->format('F jS, Y h:i A') . '</p>';


    // create DateTimeZone
    $tz = new DateTimeZone('Asia/Tokyo');
    // create a new Pos_Date object and pass the time zone as an arument
    $Tokyo = new Pos_Date($tz);
    echo '<p>Tokyo time: ' . $Tokyo->format('F jS, Y h:i A') . '</p>';
} catch (Exception $e) {
    echo $e;
}