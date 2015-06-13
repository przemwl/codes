<?php

$xml = simplexml_load_file('simplexml/inventory.xml');
$children = $xml->children();
foreach ($children as $child) {
    echo 'Node name: ' . $child->getName() . '</br>';
    $attributes = $child->attributes();
    foreach ($attributes as $attribute) {
        echo 'Attribute ' . $attribute->getName() . ": $attribute <br>";
    }
    if (false === $nexChildren = $child->children()) {
        echo "$child <br>";
    } else {
        foreach ($nexChildren as $nextChild) {
            echo $nextChild->getName() . ": $nextChild </br>";
        }
        echo '<br>';
    }
}