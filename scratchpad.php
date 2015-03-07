<?php
/*
 * Plugin Name: Scratchpad
 * Plugin URI: https://github.com/jk2228/scratchpad
 * Description: Scratchpad to assist composing a new post, displayed next to the editor
 * Author: Cornell FBOA
 * Version: 0.1
 * Author URI: http://example.com/make-scratchpad ?? ASK
 */

add_action( 'admin_init', 'scratchpad_init' );

function scratchpad_init() {
    wp_deregister_script('editor-expand');
    wp_enqueue_script( 'editor-expand-scratchpad', plugins_url( 'expand-editor-scratchpad.js', __FILE__ ),
        array( 'jquery') );
    wp_enqueue_style( 'scratchpad_css', plugins_url( 'scratchpad.css', __FILE__ ));
}

/* Fire our meta box setup function on the post editor screen. */
add_action( 'load-post.php', 'scratchpad_setup' );
add_action( 'load-post-new.php', 'scratchpad_setup' );
add_action( 'submitpost_box', 'scratchpad_add');


/* Scratchpad box setup function. */
function scratchpad_setup() {

    /* Add meta boxes on the 'add_meta_boxes' hook. */
    add_action( 'add_meta_boxes', 'scratchpad_add' );
    /* Save post meta on the 'save_post' hook. */
    add_action( 'save_post', 'scratchpad_save', 10, 2 );
}

/* Create the scratchpad box displayed next to the editor in distraction free mode. */
function scratchpad_add() {

    wp_nonce_field( basename( __FILE__ ), 'scratchpad_nonce' );
    /* Get the meta value of the custom field key. */

    $post_id = (isset($_GET['post'])) ? sanitize_html_class($_GET['post']) : '';
    $meta_key = 'scratchpad-class';
    $meta_value = get_post_meta( $post_id, $meta_key, true );
    ?>
    <div class = "scratchpad">
        <textarea class="widefat scratchpad-box" type="text" name="scratchpad-class" id="scratchpad-class" value="<?php
        echo esc_attr( get_post_meta( $post_id, 'scratchpad-class', true ) ); ?>" rows="26" ><?php echo esc_textarea($meta_value);?></textarea>
    </div>


<?php }

/* Save the scratchpad notes box */
function scratchpad_save($post_id, $post)
{

    /* Verify the nonce before proceeding. */
    if (!isset($_POST['scratchpad_nonce']) || !wp_verify_nonce($_POST['scratchpad_nonce'], basename(__FILE__)))
        return $post_id;

    /* Get the post type object. */
    $post_type = get_post_type_object($post->post_type);

    /* Check if the current user has permission to edit the post. */
    if (!current_user_can($post_type->cap->edit_post, $post_id))
        return $post_id;

    /* Get the posted data and sanitize it for use as an HTML class. */
    $new_meta_value = (isset($_POST['scratchpad-class']) ? $_POST['scratchpad-class'] : '');

    /* Get the meta key. */
    $meta_key = 'scratchpad-class';

    /* Get the meta value of the custom field key. */
    $meta_value = get_post_meta( $post_id, $meta_key, true );

    /* If a new meta value was added and there was no previous value, add it. */
    if ( $new_meta_value && '' == $meta_value )
        add_post_meta( $post_id, $meta_key, $new_meta_value, true );

    /* If the new meta value does not match the old value, update it. */
    elseif ( $new_meta_value && $new_meta_value != $meta_value )
        update_post_meta( $post_id, $meta_key, $new_meta_value );

    /* If there is no new meta value but an old value exists, delete it. */
    elseif ( '' == $new_meta_value && $meta_value )
        delete_post_meta( $post_id, $meta_key, $meta_value );
}

?>