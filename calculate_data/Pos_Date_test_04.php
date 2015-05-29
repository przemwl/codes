<?php
/**
 * Created by PhpStorm.
 * User: siemek
 * Date: 5/20/15
 * Time: 9:57 AM
 */

require_once 'Pos_Date.php';
try{
    $date = new Pos_Date();
    $date->setDate(2008, 7, 4);

    echo '<p> getMDY() : ' . $date->getMDY() . '</p>';
    echo '<p> getMDY(1) : ' . $date->getMDY(1) . '</p>';
    echo '<p> getDMY() : ' . $date->getDMY() . '</p>';
    echo '<p> getDMY(1) : ' . $date->getDMY(1) . '</p>';
    echo '<p> getMySQLFormat() : ' . $date->getMySQLFormat() . '</p>';
} catch (Exception $e) {
    echo $e;
}