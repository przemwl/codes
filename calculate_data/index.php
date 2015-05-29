<?php
/**
 * Created by PhpStorm.
 * User: siemek
 * Date: 5/22/15
 * Time: 8:22 AM
 */
require_once 'Pos_Date.php';
try {
    // create a Pos_Date object
    $improved = new Pos_Date();
    // set the date to August 31, 2008
    $improved->setDate(2008, 8, 31);
    echo '<p>Starting date: ' . $improved->format('F jS, Y') . '</p>';
    $improved->subMonths(18);
    echo '<p>Subtract 18 months using Pos_Date::subMonths(): ' . $improved->format('F jS, Y') . '</p>';
    // create a DateTime object set to August 31, 2008
    $original = new DateTime('Aug 31, 2008');
    $original->modify('-18 months');
    echo '<p>Subtract 18 months using DateTime::modify(): ' . $original->format('F jS, Y') . '</p>';
} catch (Exception $e) {
    echo $e;
}