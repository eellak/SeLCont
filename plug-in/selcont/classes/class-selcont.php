<?php

//
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );


class NETMODE_Selcont {

    private $type = 'lecture_type';
    private $nonce = 'gr_ntua_netmode_selcont';

    function __construct() {
        $this->register_lecture_type();
        $this->scripts();
        $this->metaboxes();
        $this->taxonomies();
    }

    public function register_lecture_type() {
        $labels = array(
            'name' => 'Lectures',
            'singular_name' => 'Lecture',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New',
            'edit_item' => 'Edit Item',
            'new_item' => 'Add New Item',
            'view_item' => 'View Item',
            'search_items' => 'Search Lectures',
            'not_found' => 'No Lectures Found',
            'not_found_in_trash' => 'No Lectures Found In Trash'
        );

        $args = array(
            'labels' => $labels,
            'query_var' => 'lectures',
            'rewrite' => array(
                'slug' => 'lectures',
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            //'menu_position' => 25,
            'menu_icon' => plugins_url( 'images/icon.png', __FILE__ ),
            'supports' => array(
                'title',
                'editor',
                'thumbnail'
            )
        );

        register_post_type($this->type, $args);
    }

    public function scripts() {
        add_action( 'admin_enqueue_scripts', array( $this, 'selcont_register_admin_scripts' ) );
    }

    public function selcont_register_admin_scripts() {
        wp_enqueue_script( 'selcont-admin', plugins_url( 'selcont/js/admin.js' ), array( 'jquery' ) );
    }

    public function taxonomies() {
        $labels = array(
            'name'              => 'Courses',
            'singular_name'     => 'Course',
            'search_items'      => 'Search Courses',
            'all_items'         => 'All Courses',
            'edit_item'         => 'Edit Course',
            'update_item'       => 'Update Course',
            'add_new_item'      => 'Add New Course',
            'new_item_name'     => 'New Course Name',
            'menu_name'         => 'Courses'
        );

        $taxonomies = array(
            'hierarchical' => true,
            'query_var' => 'lecture_course',
            'rewrite' => array(
                'slug' => 'course'
            ),
            'labels' => $labels
        );

        register_taxonomy( 'courses', array('lecture_type'), $taxonomies );
    }

    public function metaboxes() {
        add_action( 'add_meta_boxes', array( $this, 'add_instructor_name_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_instructor_name_meta_box' ) );

        //add_action( 'add_meta_boxes', array( $this, 'add_lecture_file_meta_box' ) );
        //add_action( 'save_post', array( $this, 'save_lecture_file_meta_box' ) );

        add_action( 'add_meta_boxes', array( $this, 'add_video_url_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_video_url_meta_box' ) );
    }

    public function add_lecture_file_meta_box() {
        add_meta_box(
            'pres_file_meta_box',
            'Presentation File',
            array( $this, 'render_lecture_file_meta_box' ),
            $this->type,
            'side'
        );
    }
    public function render_lecture_file_meta_box( $post ) {
        wp_nonce_field( plugin_basename( __FILE__ ), $this->nonce );

        $html = '<input id="pres_file_meta_box" type="file" name="pres_file_meta_box" class="" value="" />';

        $html .= '<p class="description">';
        if( '' == get_post_meta( $post->ID, 'pres_file', true ) ) {
            $html .= 'You have no file attached to this post.';
        } else {
            $html .= get_post_meta( $post->ID, 'pres_file', true );
        }
        $html .= '</p>';

        echo $html;
    }
    /*
    public function save_lecture_file_meta_box( $post_id ) {
        // Check if our nonce is set.
        if ( ! isset( $_POST['netmode_selcont_nonce'] ) )
            return $post_id;

        $nonce = $_POST['netmode_selcont_nonce'];

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'netmode_selcont' ) )
            return $post_id;

        // If this is an autosave, our form has not been submitted,
        //     so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;

        // Check the user's permissions.
        if ( 'page' == $_POST['lecture_type'] ) {

            if ( ! current_user_can( 'edit_page', $post_id ) )
                return $post_id;

        } else {

            if ( ! current_user_can( 'edit_post', $post_id ) )
                return $post_id;
        }

        // If the user uploaded an image, let's upload it to the server
        if( ! empty( $_FILES ) && isset( $_FILES['pres_file_meta_box'] ) ) {
            // Upload the goal image to the uploads directory, resize the image, then upload the resized version
            $upload = wp_upload_bits( $_FILES['pres_file_meta_box']['name'], null, wp_remote_get( $_FILES['pres_file_meta_box']['tmp_name'] ) );
            // Set post meta about this image. Need the comment ID and need the path.
            if( false == $upload['error'] ) {
                // Since we've already added the key for this, we'll just update it with the file.
                update_post_meta( $post_id, 'pres_file', $upload['url'] );
            }
        }
    }
    */

    public function add_instructor_name_meta_box( $post_type ) {
        $post_types = array('lecture_type');     //limit meta box to certain post type
        if ( in_array( $post_type, $post_types )) {
            add_meta_box(
                'instructor_name_meta_box',
                'Instructor',
                array( $this, 'render_instructor_name_meta_box' ),
                $post_type,
                'advanced',
                'high'
            );
        }
    }
    public function save_instructor_name_meta_box( $post_id ) {
        // Check if our nonce is set.
        if ( ! isset( $_POST['netmode_selcont_nonce'] ) )
            return $post_id;

        $nonce = $_POST['netmode_selcont_nonce'];

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'netmode_selcont' ) )
            return $post_id;

        // If this is an autosave, our form has not been submitted,
        //     so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;

        // Check the user's permissions.
        if ( 'page' == $_POST['lecture_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) )
                return $post_id;
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) )
                return $post_id;
        }

        // Sanitize the user input.
        $name = sanitize_text_field( $_POST['instructor_name_meta_box'] );

        // Update the meta field.
        update_post_meta( $post_id, 'instructor_name_meta_box', $name );
    }
    public function render_instructor_name_meta_box( $post ) {
        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'netmode_selcont', 'netmode_selcont_nonce' );

        // Use get_post_meta to retrieve an existing value from the database.
        $value = get_post_meta( $post->ID, 'instructor_name_meta_box', true );
        ?>

        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row">
                        <label for="instructor_name_meta_box">Instructor's Name:</label>
                    </th>
                    <td>
                        <input type="text" id="instructor_name_meta_box" class="widefat" name="instructor_name_meta_box" value=" <?php echo esc_attr( $value ) ?>" />
                        <p class="description">Enter the instructor's name.</p>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php
    }


    public function add_video_url_meta_box( $post_type ) {
        $post_types = array('lecture_type');     //limit meta box to certain post type
        if ( in_array( $post_type, $post_types )) {
            add_meta_box(
                'video_url_meta_box',
                'Video',
                array( $this, 'render_video_url_meta_box' ),
                $post_type,
                'advanced',
                'high'
            );
        }
    }
    public function save_video_url_meta_box( $post_id ) {
        // Check if our nonce is set.
        if ( ! isset( $_POST['netmode_selcont_nonce'] ) )
            return $post_id;

        $nonce = $_POST['netmode_selcont_nonce'];

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'netmode_selcont' ) )
            return $post_id;

        // If this is an autosave, our form has not been submitted,
        //     so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;

        // Check the user's permissions.
        if ( 'page' == $_POST['lecture_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) )
                return $post_id;
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) )
                return $post_id;
        }

        // Sanitize the user input.
        $name = sanitize_text_field( $_POST['video_url_meta_box'] );

        // Update the meta field.
        update_post_meta( $post_id, 'video_url_meta_box', $name );
    }
    public function render_video_url_meta_box( $post ) {
        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'netmode_selcont', 'netmode_selcont_nonce' );

        // Use get_post_meta to retrieve an existing value from the database.
        $value = get_post_meta( $post->ID, 'video_url_meta_box', true );

        ?>

        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row">
                    <label for="video_url_meta_box">Video URL:</label>
                </th>
                <td>
                    <input type="text" id="video_url_meta_box" class="widefat" name="video_url_meta_box" value="<?php echo esc_attr( $value ) ?>" />
                    <p class="description">Enter the URL of the corresponding video.</p>
                </td>
            </tr>
            </tbody>
        </table>

    <?php
    }
}
