<?php
/**
 * Created by PhpStorm.
 * User: siemek
 * Date: 5/20/15
 * Time: 9:26 AM
 */

require_once 'Pos_Date.php';
try {
    // create a Pos_Date object for the default time zone
    $local = new Pos_Date();
    // use the inhereited format() method to display the date time
    echo '<p>Time now: ' .  $local->format('F jS, Y h:i A') . '</p>';
    $local->setTime(12,30);
    $local->setDate(2008, 8, 8);
    echo '<p> Date and time reset: ' . $local->format('F jS, Y h:i A') . '</p>';
} catch (Exception $e){
    echo $e;
}