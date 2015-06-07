<?php
/**
 * Created by PhpStorm.
 * User: siemek
 * Date: 6/7/15
 * Time: 4:29 PM
 */

/*********************************************************
 * Add following part of code to functions.php in wordpress
 * to create metabox on each page which one is adding an pdf attachment
 */

$id = get_the_ID();
add_action( 'admin_init', 'add_custom_meta_boxes' );


add_action('post_edit_form_tag', 'update_edit_form');
function update_edit_form() {
    echo ' enctype="multipart/form-data"';
} // end update_edit_form

function add_custom_meta_boxes() {
    add_meta_box(
        'wp_custom_attachment',
        'Karta Podarunkowa',
        'wp_custom_attachment',
        'page',
        'normal'
    );
}

function wp_custom_attachment() {

    wp_nonce_field(plugin_basename(__FILE__), 'wp_custom_attachment_nonce');

    $html = '<p class="description">';
    $html .= 'Dodaj kartę podrunkową tutaj (format PDF).';
    $html .= '</p>';
    $html .= '<input type="file" id="wp_custom_attachment" name="wp_custom_attachment" value="" size="100" />';

    echo $html;


    //var_dump(get_post_meta($id));
}


function save_custom_meta_data($id) {

    /* --- security verification --- */
    if(!wp_verify_nonce($_POST['wp_custom_attachment_nonce'], plugin_basename(__FILE__))) {
        return $id;
    } // end if

    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $id;
    } // end if

    if('page' == $_POST['post_type']) {
        if(!current_user_can('edit_page', $id)) {
            return $id;
        } // end if
    } else {
        if(!current_user_can('edit_page', $id)) {
            return $id;
        } // end if
    } // end if
    /* - end security verification - */

    // Make sure the file array isn't empty
    if(!empty($_FILES['wp_custom_attachment']['name'])) {

        // Setup the array of supported file types. In this case, it's just PDF.
        $supported_types = array('application/pdf');

        // Get the file type of the upload
        $arr_file_type = wp_check_filetype(basename($_FILES['wp_custom_attachment']['name']));
        $uploaded_type = $arr_file_type['type'];

        // Check if the type is supported. If not, throw an error.
        if(in_array($uploaded_type, $supported_types)) {

            // Use the WordPress API to upload the file
            $upload = wp_upload_bits($_FILES['wp_custom_attachment']['name'], null, file_get_contents($_FILES['wp_custom_attachment']['tmp_name']));

            if(isset($upload['error']) && $upload['error'] != 0) {
                wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
            } else {

                update_post_meta($id, 'wp_custom_attachment', $upload);
            }


        } else {
            wp_die("The file type that you've uploaded is not a PDF.");
        } // end if/else

    } // end if

} // end save_custom_meta_data
add_action('save_post', 'save_custom_meta_data');
