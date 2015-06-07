<?php
/**
 * Created by PhpStorm.
 * User: siemek
 * Date: 6/2/15
 * Time: 8:44 AM
 */

require_once 'Pos_RemotConnector.php';
$url = 'http://stackoverflow.com/';
try {
    $output = new Pos_RemoteConnector($url);
    echo $output;
 } catch (Exception $e) {
    echo $e->getMessage();
}
