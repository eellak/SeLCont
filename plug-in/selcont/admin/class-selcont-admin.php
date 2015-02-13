<?php

/**
 * The dashboard-specific functionality of the plugin.
 */
class Selcont_Admin {

    private $plugin_name;
    private $version;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

	public function init_admin() {

        $this->scripts();
        $this->selcont_post_type();
        $this->taxonomies();
        $this->metaboxes();

	}

    public function scripts() {

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

    }

	/**
	 * Register the stylesheets for the Dashboard.
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'css/selcont-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'js/selcont-admin.js', array( 'jquery' ), $this->version, false );

	}


    /**
     * Register Custom Post Type for the dashboard.
     *
     */
    public function selcont_post_type() {

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
            'show_ui' => true,
            'show_in_menu' => true,
            //'menu_position' => 25,
            'menu_icon' => plugins_url( '../assets/icon.png', __FILE__ ),
            'supports' => array(
                'title',
                'editor',
                'thumbnail'
            )
        );

        register_post_type( $this->plugin_name . '_lecture_type', $args);

    }

    public function taxonomies() {

        $this->register_courses_taxonomy();
        $this->register_institutions_taxonomy();

    }

    public function register_courses_taxonomy() {

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

        $courses_taxonomy = array(
            'hierarchical' => true,
            'query_var' => 'lecture_course',
            'rewrite' => array(
                'slug' => 'courses'
            ),
            'labels' => $labels
        );

        register_taxonomy( 'courses', array('selcont_lecture_type'), $courses_taxonomy );

    }

    public function register_institutions_taxonomy() {

        $labels = array(
            'name'              => 'Institutions',
            'singular_name'     => 'Institution',
            'search_items'      => 'Search Institutions',
            'all_items'         => 'All Institutions',
            'edit_item'         => 'Edit Institution',
            'update_item'       => 'Update Institution',
            'add_new_item'      => 'Add New Institution',
            'new_item_name'     => 'New Institution',
            'menu_name'         => 'Institutions'
        );

        $institutions_taxonomy = array(
            'hierarchical' => true,
            'query_var' => 'lecture_institution',
            'rewrite' => array(
                'slug' => 'institutions'
            ),
            'labels' => $labels
        );

        register_taxonomy( 'institutions', array('selcont_lecture_type'), $institutions_taxonomy );

    }

    public function metaboxes() {

        add_action( 'add_meta_boxes', array( $this, 'add_instructor_name_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_instructor_name_meta_box' ) );

        add_action( 'add_meta_boxes', array( $this, 'add_video_url_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_video_url_meta_box' ) );

        add_action( 'add_meta_boxes', array( $this, 'add_school_name_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_school_name_meta_box' ) );

        add_action( 'add_meta_boxes', array( $this, 'add_image_file_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_image_file_meta_box' ) );

        add_action( 'add_meta_boxes', array( $this, 'add_xml_file_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_xml_file_meta_box' ) );

    }

    public function add_instructor_name_meta_box( $post_type ) {
        $post_types = array('selcont_lecture_type');     //limit meta box to certain post type

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
        if ( 'page' == $_POST['selcont_lecture_type'] ) {
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
        $post_types = array('selcont_lecture_type');     //limit meta box to certain post type
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
        if ( 'page' == $_POST['selcont_lecture_type'] ) {
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

    public function add_school_name_meta_box( $post_type ) {
        $post_types = array('selcont_lecture_type');     //limit meta box to certain post type
        if ( in_array( $post_type, $post_types )) {
            add_meta_box(
                'school_name_meta_box',
                'School',
                array( $this, 'render_school_name_meta_box' ),
                $post_type,
                'advanced',
                'high'
            );
        }
    }
    public function save_school_name_meta_box( $post_id ) {
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
        if ( 'page' == $_POST['selcont_lecture_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) )
                return $post_id;
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) )
                return $post_id;
        }

        // Sanitize the user input.
        $name = sanitize_text_field( $_POST['school_name_meta_box'] );

        // Update the meta field.
        update_post_meta( $post_id, 'school_name_meta_box', $name );
    }
    public function render_school_name_meta_box( $post ) {

        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'netmode_selcont', 'netmode_selcont_nonce' );

        // Use get_post_meta to retrieve an existing value from the database.
        $value = get_post_meta( $post->ID, 'school_name_meta_box', true );
        ?>

        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row">
                    <label for="school_name_meta_box">School:</label>
                </th>
                <td>
                    <input type="text" id="school_name_meta_box" class="widefat" name="school_name_meta_box" value=" <?php echo esc_attr( $value ) ?>" />
                    <p class="description">Enter school's name (e.g. School of Electrical Engineering).</p>
                </td>
            </tr>
            </tbody>
        </table>

    <?php
    }

    public function add_image_file_meta_box( $post_type ) {
        $post_types = array('selcont_lecture_type');     //limit meta box to certain post type
        if ( in_array( $post_type, $post_types )) {
            add_meta_box(
                'slide_image_meta_box',
                'Presentation File',
                array( $this, 'render_image_file_meta_box' ),
                $post_type,
                'advanced',
                'high'
            );
        }
    }
    public function render_image_file_meta_box( $post ) {

        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'netmode_selcont', 'netmode_selcont_nonce' );

        $html = '<input id="slide_image_meta_box" class="button" type="file" name="slide_image_meta_box" value="" />';

        $html .= '<p class="description">';
        if( '' == get_post_meta( $post->ID, 'umb_file', true ) ) {
            $html .= 'You have no file attached to this post.';
        } else {
            $html .= get_post_meta( $post->ID, 'umb_file', true );
        }
        $html .= '</p>';

        echo $html;

    }
    public function save_image_file_meta_box( $post_id ) {
        // Check if our nonce is set.
        if ( ! isset( $_POST['netmode_selcont_nonce'] ) )
            return $post_id;

        $nonce = $_POST['netmode_selcont_nonce'];

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'netmode_selcont' ) )
            return $post_id;

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;

        // Check the user's permissions.
        if ( 'page' == $_POST['selcont_lecture_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) )
                return $post_id;
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) )
                return $post_id;
        }

        // If the user uploaded an image, let's upload it to the server
        if( ! empty( $_FILES ) && isset( $_FILES['slide_image_meta_box'] ) ) {

            // Upload the goal image to the uploads directory, resize the image, then upload the resized version
            $goal_image_file = wp_upload_bits( $_FILES['slide_image_meta_box']['name'], null, file_get_contents( $_FILES['slide_image_meta_box']['tmp_name'] ) );
            // Set post meta about this image. Need the comment ID and need the path.
            if( false == $goal_image_file['error'] ) {

                //add_post_meta($post_id, 'umb_file', $goal_image_file['url'] );
                update_post_meta($post_id, 'umb_file', $goal_image_file['url'] );

            }
        }
    }








}
