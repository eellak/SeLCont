<?php


/**
 * The public-facing functionality of the plugin.
 *
 */
class Selcont_Public {

	private $plugin_name;
	private $version;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

        $this->scripts();
	}

    public function init_public() {

        $this->scripts();
        $this->shortcodes();

    }

    public function scripts() {

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

    }

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name . '-public', plugin_dir_url( __FILE__ ) . 'css/selcont-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name . '-public', plugin_dir_url( __FILE__ ) . 'js/selcont-public.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script( 'timesheets', plugin_dir_url( __FILE__ ) . 'js/timesheets.js', array( ), false, false );
        wp_enqueue_script( 'timesheets-controls', plugin_dir_url( __FILE__ ) . 'js/timesheets-controls.js', array( ), false, false );
        wp_enqueue_script( 'timesheets-navigation', plugin_dir_url( __FILE__ ) . 'js/timesheets-navigation.js', array( ), false, false );

	}

    public function shortcodes() {

        add_shortcode( 'selcont_list_lectures', array( $this, 'register_shortcode' ) );

    }

    public function register_shortcode() {
        $loop = new WP_Query(
            array(
                'post_type' => 'selcont_lecture_type',
                'orderby' => 'title'
            )
        );

        if ( $loop->have_posts() ) {
            $output = '<ul class="lectures-list">';

            while( $loop->have_posts() ) {
                $loop->the_post();
                $meta = get_post_meta(get_the_id(), '');

                $output .= '
                    <li>
                        <a href="' . get_permalink() . '">
                            ' . get_the_title() . ' | ' . $meta['instructor_name_meta_box'][0] . '
                        </a>
                        <div>' . get_the_excerpt() . '</div>
                    </li>
                ';
            }

        }

        return $output;

    }


}
