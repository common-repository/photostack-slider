<?php
/*
Plugin Name: PhotoStack Slider
Plugin URI: https://tishonator.com/plugins/photostack-slider
Description: Configure a Responsive Photo Stack Slider and Insert it in any Page or Post as a Shortcode. Admin Slide Fields for Title, Text, and Image.
Author: tishonator
Version: 1.0.1
Author URI: http://tishonator.com/
Contributors: tishonator
Text Domain: photostack-slider
*/

if ( !class_exists('tishonator_PhotoStackSliderPlugin') ) :

    /**
     * Register the plugin.
     *
     * Display the administration panel, insert JavaScript etc.
     */
    class tishonator_PhotoStackSliderPlugin {
        
    	/**
    	 * Instance object
    	 *
    	 * @var object
    	 * @see get_instance()
    	 */
    	protected static $instance = NULL;

        /**
         * an array with all Slider settings
         */
        private $settings = array();

        /**
         * Constructor
         */
        public function __construct() {}

        /**
         * Setup
         */
        public function setup() {

            register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );

            if ( is_admin() ) { // admin actions

                add_action('admin_menu', array(&$this, 'add_admin_page'));

                add_action('admin_enqueue_scripts', array(&$this, 'admin_scripts'));
            }

            add_action( 'init', array(&$this, 'register_shortcode') );
        }

        public function register_shortcode() {

            add_shortcode( 'photostack-slider', array(&$this, 'display_shortcode') );
        }

        public function display_shortcode($atts) {

            $result = '';

            $options = get_option( 'photostack_slider_options' );
            
            if ( ! $options )
                return $result;

            // Modernizr JS
            wp_register_script('modernizr-js',
                plugins_url('js/modernizr.min.js', __FILE__), array('jquery'));

            wp_enqueue_script('modernizr-js',
                    plugins_url('js/modernizr.min.js', __FILE__), array('jquery') );

            // Add classie.js
            wp_register_script('classie-js',
                plugins_url('js/classie.js', __FILE__), array('jquery', 'modernizr-js') );

            wp_enqueue_script('classie-js',
                    plugins_url('js/classie.js', __FILE__), array('jquery') );

            // Add photostack.js
            wp_register_script('photostack-js',
                plugins_url('js/photostack.js', __FILE__), array('jquery', 'classie-js', 'modernizr-js') );

            wp_enqueue_script('photostack-js',
                    plugins_url('js/photostack.js', __FILE__), array('jquery') );

            // FontAwesome
            wp_register_style('font-awesome',
                plugins_url('css/font-awesome.min.css', __FILE__), true);

            wp_enqueue_style( 'font-awesome',
                plugins_url('css/font-awesome.min.css', __FILE__), array( ) );

            // Photostack Slider CSS
            wp_register_style( 'photostack-slider-css',
                plugins_url('css/photostack-slider.css', __FILE__), true);

            wp_enqueue_style( 'photostack-slider-css',
                plugins_url('css/photostack-slider.css', __FILE__), array() );

            $result .= '<div class="slider-container" id="slider-container">';
            $result .= '<section id="photostack" class="photostack">';
            $result .= '<div>';

            for ( $slideNumber = 1; $slideNumber <= 3; ++$slideNumber ) {

                $slideTitle = array_key_exists('slide_' . $slideNumber . '_title', $options)
                                ? $options[ 'slide_' . $slideNumber . '_title' ] : '';

                $slideText = array_key_exists('slide_' . $slideNumber . '_text', $options)
                                ? $options[ 'slide_' . $slideNumber . '_text' ] : '';

                $slideImage = array_key_exists('slide_' . $slideNumber . '_image', $options)
                                ? $options[ 'slide_' . $slideNumber . '_image' ] : '';

                if ( $slideImage ) {

                    $result .= '<figure>';

                    $result .= '<img src="' . esc_url($slideImage) . '" alt="'
                                            . esc_attr($slideTitle) . '" />';
                    $result .= '<figcaption>';
                    $result .= '<h2 class="photostack-title">' . esc_attr($slideTitle) . '</h2>';
                    $result .= '<div class="photostack-back">';
                    $result .= '<p>' . esc_attr($slideText) . '</p>';
                    
                    $result .= '</div>';
                    $result .= '</figcaption>';
                    $result .= '</figure>';

                }
            }

            $result .= '</div>';
            $result .= '</section>'; // .photostack
            $result .= '</div>'; // .slider-container

            return $result;
        }

        public function admin_scripts($hook) {

            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');

            wp_register_script('photostack_slider_upload_media',
                plugins_url('js/upload-media.js', __FILE__), array('jquery'));

            wp_enqueue_script('photostack_slider_upload_media');

            wp_enqueue_style('thickbox');
        }

    	/**
    	 * Used to access the instance
         *
         * @return object - class instance
    	 */
    	public static function get_instance() {

    		if ( NULL === self::$instance ) {
                self::$instance = new self();
            }

    		return self::$instance;
    	}

        /**
         * Unregister plugin settings on deactivating the plugin
         */
        public function deactivate() {

            unregister_setting('photostack_slider', 'photostack_slider_options');
        }

        /** 
         * Print the Section text
         */
        public function print_section_info() {}

        public function admin_init_settings() {
            
            register_setting('photostack_slider', 'photostack_slider_options');

            // add separate sections for each of Sliders
            add_settings_section( 'photostack_slider_section',
                __( 'Slider Settings', 'photostack-slider' ),
                array(&$this, 'print_section_info'),
                'photostack_slider' );

            for ( $i = 1; $i <= 3; ++$i ) {

                // Slide Title
                add_settings_field(
                    'slide_' . $i . '_title',
                    sprintf( __( 'Slide %s Title', 'photostack-slider' ), $i ),
                    array(&$this, 'input_callback'),
                    'photostack_slider',
                    'photostack_slider_section',
                    [ 'id' => 'slide_' . $i . '_title',
                      'page' =>  'photostack_slider_options' ]
                );

                // Slide Text
                add_settings_field(
                    'slide_' . $i . '_text',
                    sprintf( __( 'Slide %s Text', 'photostack-slider' ), $i ),
                    array(&$this, 'textarea_callback'),
                    'photostack_slider',
                    'photostack_slider_section',
                    [ 'id' => 'slide_' . $i . '_text',
                      'page' =>  'photostack_slider_options' ]
                );

                // Slide Image
                add_settings_field(
                    'slide_' . $i . '_image',
                    sprintf( __( 'Slide %s Image', 'photostack-slider' ), $i ),
                    array(&$this, 'image_callback'),
                    'photostack_slider',
                    'photostack_slider_section',
                    [ 'id' => 'slide_' . $i . '_image',
                      'page' =>  'photostack_slider_options' ]
                );
            }
        }

        public function textarea_callback($args) {

            // get the value of the setting we've registered with register_setting()
            $options = get_option( $args['page'] );
 
            // output the field

            $fieldValue = $options && $args['id'] && array_key_exists(esc_attr( $args['id'] ), $options)
                                ? $options[ esc_attr( $args['id'] ) ] : '';
            ?>

            <textarea id="<?php echo esc_attr( $args['page'] . '[' . $args['id'] . ']' ); ?>"
                name = "<?php echo esc_attr( $args['page'] . '[' . $args['id'] . ']' ); ?>"
                rows="10" cols="39"><?php echo esc_attr($fieldValue); ?></textarea>
            <?php
        }

        public function input_callback($args) {

            // get the value of the setting we've registered with register_setting()
            $options = get_option( $args['page'] );
 
            // output the field
            $fieldValue = ($options && $args['id'] && array_key_exists(esc_attr( $args['id'] ), $options))
                                ? $options[ esc_attr( $args['id'] ) ] : 
                                    (array_key_exists('default_val', $args) ? $args['default_val'] : '');
            ?>

            <input type="text" id="<?php echo esc_attr( $args['page'] . '[' . $args['id'] . ']' ); ?>"
                name="<?php echo esc_attr( $args['page'] . '[' . $args['id'] . ']' ); ?>"
                class="regular-text"
                value="<?php echo esc_attr( $fieldValue ); ?>" />
<?php
        }

        public function image_callback($args) {

            // get the value of the setting we've registered with register_setting()
            $options = get_option( $args['page'] );
 
            // output the field

            $fieldValue = $options && $args['id'] && array_key_exists(esc_attr( $args['id'] ), $options)
                                ? $options[ esc_attr( $args['id'] ) ] : '';
            ?>

            <input type="text" id="<?php echo esc_attr( $args['page'] . '[' . $args['id'] . ']' ); ?>"
                name="<?php echo esc_attr($args['page'] . '[' . $args['id'] . ']' ); ?>"
                class="regular-text"
                value="<?php echo esc_attr( $fieldValue ); ?>" />
            <input class="upload_image_button button button-primary" type="button"
                   value="<?php _e('Change Image', 'photostack-slider'); ?>" />

            <p><img class="slider-img-preview" <?php if ( $fieldValue ) : ?> src="<?php echo esc_attr($fieldValue); ?>" <?php endif; ?> style="max-width:300px;height:auto;" /><p>
<?php         
        }

        public function add_admin_page() {

            add_menu_page( __('PhotoStack Slider Settings', 'photostack-slider'),
                __('PhotoStack Slider', 'photostack-slider'), 'manage_options',
                'photostack-slider.php', array(&$this, 'show_settings'),
                'dashicons-format-gallery', 6 );

            //call register settings function
            add_action( 'admin_init', array(&$this, 'admin_init_settings') );
        }

        /**
         * Display the settings page.
         */
        public function show_settings() { ?>

            <div class="wrap">
                <div id="icon-options-general" class="icon32"></div>

                <div class="notice notice-info"> 
                    <p><strong><?php _e('Upgrade to PhotoStack Slider PRO Plugin', 'photostack-slider'); ?>:</strong></p>
                    <ul>
                        <li><?php _e('Configure Up to 10 Different Sliders', 'photostack-slider'); ?></li>
                        <li><?php _e('Insert Up to 10 Slides per Slider', 'photostack-slider'); ?></li>
                        <li><?php _e('Auto Play and Slides Delay Options', 'photostack-slider'); ?></li>
                        <li><?php _e('Color Options', 'photostack-slider'); ?></li>
                    </ul>
                    <a href="https://tishonator.com/plugins/photostack-slider" class="button-primary">
                        <?php _e('Upgrade to PhotoStack Slider PRO Plugin', 'photostack-slider'); ?>
                    </a>
                    <p></p>
                </div>

                <h2><?php _e('PhotoStack Slider Settings', 'photostack-slider'); ?></h2>

                <form action="options.php" method="post">
                    <?php settings_fields('photostack_slider'); ?>
                    <?php do_settings_sections('photostack_slider'); ?>
                    
                    <h3>
                      Usage
                    </h3>
                    <p>
                        <?php _e('Use the shortcode', 'photostack-slider'); ?> <code>[photostack-slider]</code> <?php echo _e( 'to display Slider to any page or post.', 'photostack-slider' ); ?>
                    </p>
                    <?php submit_button(); ?>
              </form>
            </div>
    <?php
        }
    }

endif; // tishonator_PhotoStackSliderPlugin

add_action('plugins_loaded', array( tishonator_PhotoStackSliderPlugin::get_instance(), 'setup' ), 10);
