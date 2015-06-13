<?php
/**
 * Created by PhpStorm.
 * User: siemek
 * Date: 6/13/15
 * Time: 4:25 PM
 */

echo '<h1> Brush up Your programming skills </h1>';
$xml = simplexml_load_file('simplexml/inventory.xml');
foreach ($xml->book as $book) {
    echo '<h2>' . $book->title . '</h2>';

    echo '<p class="author">';
    if (is_array($book->author)) {
        echo implode(', ',$book->author);
    } else {
        echo $book->author;
    }
    echo '</p>';

    echo '<p class="publisher"' . $book->publisher . '</p>';
    echo '<p class="publisher"> ISBN: ' . $book['isbn13'] . '</p>';

    echo '<p>' . $book->description .'</p>';
}