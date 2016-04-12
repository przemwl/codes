<?php
/**
* Plugin Name: Zajebista wtyczka - hit!
* Description: Zajebista wtyczka do blokowania prawego przycisku myszy - hit!
* Version: 1.0 or whatever version of the plugin (pretty self explanatory)
* Author:  Przemysław Wleklik
* License:  GPL
*/

function add_the_collest_script_ever(){ ?>
	<script>
	$(document).ready(function() {
	$("body").on("contextmenu",function(e){
	return false;
	}); 
	}); 
	</script>

<?php }  ?>

add_action('wp_enqueue_scripts', 'add_the_collest_script_ever');

