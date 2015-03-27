<?php

if ( ! defined( 'ABSPATH' ) ) {
exit;
} // Exit if accessed directly

/**
 * Main class
 *
 * @class   YITH_Livechat
 * @package Yithemes
 * @since   1.0.0
 * @author  Your Inspiration Themes
 */

if ( ! class_exists( 'YITH_Livechat' ) ) {

    class YITH_Livechat {

        /**
         * @var string $_options_name The name for the options db entry
         */
        protected $_options_name = 'live_chat';

        /**
         * Panel object
         *
         * @var /Yit_Plugin_Panel object
         * @since 1.0
         * @see plugin-fw/lib/yit-plugin-panel.php
         */
        protected $_panel = null;

        /**
         * @var $_premium string Premium tab template file name
         */
        protected $_premium = 'premium.php';

        /**
         * @var string Premium version landing link
         */
        protected $_premium_landing = 'http://yithemes.com/themes/plugins/yith-live-chat/';

        /**
         * @var string Plugin official documentation
         */
        protected $_official_documentation = 'http://yithemes.com/docs-plugins/yith-live-chat/';

        /**
         * @var string Yith Live Chat panel page
         */
        protected $_panel_page = 'yith_live_chat_panel';

        /**
         * @var string Yith Live Chat console page
         */
        protected $_console_page = 'yith_live_chat';

        /**
         * Constructor
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @return mixed
         * @since  1.0.0
         * @access public
         */
        public function __construct() {
            /* === Actions === */
            add_action( 'after_setup_theme', array( $this, 'plugin_fw_loader' ), 1 );
            //Add action links
            add_filter( 'plugin_action_links_' . plugin_basename( YLC_DIR . '/' . basename( YLC_FILE ) ), array( $this, 'action_links' ) );
            add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

            $this->options = get_option( 'yit_' . $this->_options_name . '_options' );

            /* === Actions === */
            add_action( 'admin_menu', array( $this, 'add_menu_page' ), 5 );
            add_action( 'yith_live_chat_premium', array( $this, 'premium_tab' ) );
            add_action( 'yit_panel_custom-text', array( $this, 'custom_text_template' ), 10, 2  );


            if ( $this->options['plugin-enable'] == 'yes' ) {

                add_action( 'admin_menu', array( $this, 'add_console_page' ), 5 );
                add_action( 'admin_init', array( &$this, 'admin_init' ), 5 );

                // Include required files
                $this->includes();

                $this->session = new YLC_Session();

                // register admin notices
                add_action( 'admin_notices', array( $this, 'admin_notices' ) );
                add_action( 'init', array( &$this, 'init' ), 0 );

            }

        }

        /**
         * Include required core files
         *
         * @access public
         * @return void
         */
        function includes() {

            // Back-end includes
            if(  is_admin() ) {

            }

            // Include core files
            require_once( 'includes/firebase/firebase-token-generator.php' );
            require_once( 'includes/class-ylc-user.php' );
            require_once( 'includes/class-ylc-session.php' );
            require_once( 'includes/functions-ylc-server.php' );
            require_once( 'includes/functions-ylc-commons.php' );
            require_once( 'includes/functions-ylc-ajax.php' );

        }

        /**
         * Add a panel under YITH Plugins tab
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use     /Yit_Plugin_Panel class
         * @see      plugin-fw/lib/yit-plugin-panel.php
         */
        public function add_menu_page() {

            /* === Add Settings Page === */
            if ( ! empty( $this->_panel ) ) {
                return;
            }

            $admin_tabs = array(
                'settings'  => __( 'General', 'ylc' ),
                'texts'     => __( 'Texts', 'ylc' )
            );

            if ( defined( 'YLC_PREMIUM' ) ) {
              //  $admin_tabs['customize']        = __( 'Chat', 'ylc' );
               // $admin_tabs['exclusions']       = __( 'Form', 'ylc' );
            } else {
                //$admin_tabs['premium-landing']  = __( 'Premium Version', 'ylc' );
            }

            $args = array(
                'create_menu_page' => true,
                'parent_slug'      => '',
                'page_title'       => __( 'Live Chat', 'ylc' ),
                'menu_title'       => __( 'Live Chat', 'ylc' ),
                'capability'       => 'manage_options',
                'parent'           => $this->_options_name,
                'parent_page'      => 'yit_plugin_panel',
                'page'             => $this->_panel_page,
                'admin-tabs'       => $admin_tabs,
                'options-path'     => YLC_DIR . '/plugin-options'
            );

            $this->_panel = new YIT_Plugin_Panel( $args );
        }

        /**
         * Add YITH Live Chat console page
         *
         * @return   void
         * @since    1.0.0
         * @author   Alberto Ruggiero
         */
        public function add_console_page() {

            $page_title = __( 'YITH Live Chat', 'ylc');

            /* === Add Chat Console Page === */
            if( current_user_can( 'manage_options' ) ) {

                add_menu_page( $page_title, $page_title, 'manage_options', $this->_console_page, array( $this, 'get_console_template' ), YLC_ASSETS_URL  . '/images/favicon.png', 61 );

            } else if( current_user_can( 'answer_chat' ) ){

                add_menu_page( $page_title, $page_title, 'ylc_chat_op', $this->_console_page, array( $this, 'get_console_template' ), YLC_ASSETS_URL  . '/images/favicon.png', 61 );

            }

        }

        /**
         * Advise if the plugin cannot be performed
         *
         * @return  void
         * @since   1.0.0
         */
        public function admin_notices() {
            if ( empty( $this->options['firebase-appurl'] ) || empty( $this->options['firebase-appsecret'] ) ) : ?>
                <div class="error">
                    <p>
                        <?php _e( 'Please enter Firebase App URL and Firebase App Secret for YITH Live Chat', 'ylc' ); ?>
                    </p>
                </div>
            <?php
            endif;
        }

        /**
         * Plugin Init
         *
         * @since   1.0.0
         * @author  Alberto Ruggiero
         * @return  void
         */
        function init() {

            $this->current_page = get_current_page_url();
            $this->ip           = get_ip_address();

            if( current_user_can( 'answer_chat' ) ) {
                define( 'YLC_OPERATOR', true );
            } else {
                define( 'YLC_GUEST', true );
            }

            if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {

                add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );

            }

            add_action( 'wp_ajax_ylc_ajax_callback', 'ylc_ajax_callback' );
            add_action( 'wp_ajax_nopriv_ylc_ajax_callback', 'ylc_ajax_callback' );

           // Save user info
            if( is_user_logged_in() ) {

                global $current_user;

                get_currentuserinfo(); // Get currently logged user info

                $this->user = $current_user;

                // Visitor info
            } else {

                $visitor_id = $this->session->get( 'visitor_id' );

                if( empty( $visitor_id ) ) {

                    $visitor_id = uniqid( rand(), false ); // Create new unique ID
                    $this->session->set( 'visitor_id', $visitor_id ); // Save id into the session

                }

                $this->user = ( object ) array(
                    'ID'            => $visitor_id,
                    'display_name'  => null,
                    'user_email'    => null
                );

            }

            // Render chat box
            add_action( 'wp_footer', array( &$this, 'show_chat') );

        }

        /**
         * Enqueue Scripts
         *
         * Add styles and scripts to frontend
         *
         * @return void
         * @since  1.0.0
         * @author Alberto Ruggiero
         */
        public function enqueue_scripts() {

            $this->load_fontawesome();

            wp_register_style( 'ylc-style', YLC_ASSETS_URL  . '/css/ylc-frontend.css' );
            wp_enqueue_style( 'ylc-style' );

            wp_enqueue_script( 'jquery' );

            // Application JS
            $this->load_livechat_js();

            // § Plug-in
            wp_register_script( 'jquery-autosize', YLC_ASSETS_URL . '/js/lib/jquery.autosize.min.js', array( 'jquery' ), '1.17.1' );
            wp_enqueue_script( 'jquery-autosize' );

        }

        /**
         * Initialization Live Chat back-end
         *
         * @return  void
         * @since   1.0.0
         */
        public function admin_init() {

            $this->load_fontawesome();

            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

        }

        /**
         * Admin Enqueue Scripts
         *
         * Add styles and scripts to admin
         *
         * @return void
         *
         * @since  1.0.0
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function admin_enqueue_scripts(){

            $page = '';

            // Get currently logged user info
            get_currentuserinfo();

            // Application JS
            $this->load_livechat_js();

            // Get current page
            if( !empty( $_GET['page'] ) )
                $page = $_GET['page'];

            // Load in chat console
            if( $page == $this->_console_page  ) {

                // Console stylesheet
                wp_register_style( 'ylc-console', YLC_ASSETS_URL . '/css/ylc-console.css' );
                wp_enqueue_style( 'ylc-console' );

                // AutoSize Plug-in
                wp_register_script( 'jquery-autosize', YLC_ASSETS_URL . '/js/lib/jquery.autosize.min.js', array( 'jquery' ), '1.17.1' );
                wp_enqueue_script( 'jquery-autosize' );

                // Tipsy Plug-in
                wp_register_script( 'jquery-tipsy', YLC_ASSETS_URL . '/js/lib/jquery.tipsy.min.js', array( 'jquery' ), '1.0' );
                wp_enqueue_script( 'jquery-tipsy' );

                // Console JS
                wp_register_script( 'ylc-console', YLC_ASSETS_URL . '/js/ylc-console.js', array( 'jquery' ) );
                wp_enqueue_script( 'ylc-console' );

            } else if ( $page == $this->_panel_page ) {
                wp_register_style( 'ylc-styles', YLC_ASSETS_URL . '/css/ylc-styles.css' );
                wp_enqueue_style( 'ylc-styles' );
            }



        }

        /**
         * Load Live Chat scripts
         *
         * @since   1.0.0
         * @author  Alberto Ruggiero
         * @return  void
         */
        public function load_livechat_js() {

            wp_register_script( 'ylc-firebase', YLC_ASSETS_URL . '/js/firebase.js' );
            wp_enqueue_script( 'ylc-firebase' );

            if ( defined( 'YLC_PREMIUM' ) ) {
                $this->load_livechat_js_premium();
            }

            wp_register_script( 'ylc-engine', YLC_ASSETS_URL . '/js/ylc-engine.js', array( 'jquery', 'ylc-firebase' ) );
            wp_enqueue_script( 'ylc-engine' );

            $options = $this->options;

             // Custom Data
             $js_vars = array(
                 'app_id'           => $options['firebase-appurl'],
                 'ajax_url'   		=> str_replace( array('https:', 'http:'), '', admin_url( 'admin-ajax.php' ) ),
                 'plugin_url'   	=> YLC_ASSETS_URL,
                 'is_front_end' 	=> ( !is_admin() ) ? true : null,
                 'is_op' 			=> ( defined( 'YLC_OPERATOR' ) && is_admin() ) ? true : false,
                 'is_premium' 		=> ( defined( 'YLC_PREMIUM' ) ) ? true : false,
                 'current_page'		=> $this->current_page,
                 'company_avatar'	=> '',
                 'ip' 				=> $this->ip,
                 'max_guests'       => ( !empty( $options['max-chat-users'] ) ) ? $options['max-chat-users'] : 2,
                 'templates'        => array(
                     'chat_popup'           => file_get_contents( YLC_URL . 'templates/chat-frontend/chat-popup.php' ),
                     'connecting'           => file_get_contents( YLC_URL . 'templates/chat-frontend/connecting.php' ),
                     'btn'                  => file_get_contents( YLC_URL . 'templates/chat-frontend/btn.php' ),
                     'offline'              => file_get_contents( YLC_URL . 'templates/chat-frontend/offline.php' ),
                     'login'                => file_get_contents( YLC_URL . 'templates/chat-frontend/login.php' ),
                     'online_user'          => file_get_contents( YLC_URL . 'templates/chat-frontend/online-user.php' ),
                     'chat_line'            => file_get_contents( YLC_URL . 'templates/chat-frontend/chat-line.php' ),
                     'user_item'            => file_get_contents( YLC_URL . 'templates/chat-backend/user-item.php' ),
                     'online_basic'         => file_get_contents( YLC_URL . 'templates/chat-backend/online-basic.php' ),
                     'chat_user_meta_info'  => file_get_contents( YLC_URL . 'templates/chat-backend/chat-user-meta-info.php' ),
                     'chat_user_meta_tools' => file_get_contents( YLC_URL . 'templates/chat-backend/chat-user-meta-tools.php' ),
                     'chat_user_meta_page'  => file_get_contents( YLC_URL . 'templates/chat-backend/chat-user-meta-page.php' ),
                     'premium'              => ( defined( 'YLC_PREMIUM' ) ) ? $this->get_premium_templates() : '',
                 ),
                 'strings'          => array(
                     'months'       => array(
                         'jan' => __( 'January', 'ylc' ),
                         'feb' => __( 'February', 'ylc' ),
                         'mar' => __( 'March', 'ylc' ),
                         'apr' => __( 'April', 'ylc' ),
                         'may' => __( 'May', 'ylc' ),
                         'jun' => __( 'June', 'ylc' ),
                         'jul' => __( 'July', 'ylc' ),
                         'aug' => __( 'August', 'ylc' ),
                         'sep' => __( 'September', 'ylc' ),
                         'oct' => __( 'October', 'ylc' ),
                         'nov' => __( 'November', 'ylc' ),
                         'dec' => __( 'December', 'ylc' )
                     ),
                     'months_short' => array(
                         'jan' => __( 'Jan', 'ylc' ),
                         'feb' => __( 'Feb', 'ylc' ),
                         'mar' => __( 'Mar', 'ylc' ),
                         'apr' => __( 'Apr', 'ylc' ),
                         'may' => __( 'May', 'ylc' ),
                         'jun' => __( 'Jun', 'ylc' ),
                         'jul' => __( 'Jul', 'ylc' ),
                         'aug' => __( 'Aug', 'ylc' ),
                         'sep' => __( 'Sep', 'ylc' ),
                         'oct' => __( 'Oct', 'ylc' ),
                         'nov' => __( 'Nov', 'ylc' ),
                         'dec' => __( 'Dec', 'ylc' )
                     ),
                     'time'         => array(
                         'suffix'   => __( 'ago', 'ylc' ),
                         'seconds'  => __( 'less than a minute', 'ylc' ),
                         'minute'   => __( 'about a minute', 'ylc' ),
                         'minutes'  => __( '%d minutes', 'ylc' ),
                         'hour'     => __( 'about an hour', 'ylc' ),
                         'hours'    => __( 'about %d hours', 'ylc' ),
                         'day'      => __( 'a day', 'ylc' ),
                         'days'     => __( '%d days', 'ylc' ),
                         'month'    => __( 'about a month', 'ylc' ),
                         'months'   => __( '%d months', 'ylc' ),
                         'year'     => __( 'about a year', 'ylc' ),
                         'years'    => __( '%d years', 'ylc' ),
                     ),
                     'fields'       => array(
                         'name'         => __( 'Your Name', 'ylc'),
                         'name_ph'      => __( 'Please enter your name', 'ylc'),
                         'email'        => __( 'Your Email', 'ylc'),
                         'email_ph'     => __( 'Please enter your email', 'ylc'),
                         'message'      => __( 'Your Message', 'ylc'),
                         'message_ph'   => __( 'Write your question', 'ylc'),
                     ),
                     'msg'          => array(
                         'chat_title'           => sanitize_text( $options['text-chat-title'] ),
                         'prechat_msg'          => sanitize_text( esc_html( $options['text-welcome'] ), true ),
                         'welc_msg'             => sanitize_text( esc_html( $options['text-start-chat'] ), true ),
                         'start_chat'           => __( 'Start Chat', 'ylc' ),
                         'offline_body'         => sanitize_text( esc_html( $options['text-offline'] ), true ),
                         'busy_body'            => sanitize_text( esc_html( $options['text-busy'] ), true ),
                         'close_msg'            => sanitize_text( esc_html( $options['text-close'] ), true ),
                         'close_msg_user'       => __( 'The user has closed the conversation', 'ylc'),
                         'reply_ph'             => __( 'Type here and hit enter to chat', 'ylc' ),
                         'send_btn'             => __( 'Send', 'ylc' ),
                         'no_op'                => __( 'No operators online', 'ylc' ),
                         'no_msg'               => __( 'No messages found', 'ylc' ),
                         'sending'              => __( 'Sending', 'ylc' ),
                         'connecting'           => __( 'Connecting', 'ylc' ),
                         'writing'              => __( '%s is writing', 'ylc' ),
                         'please_wait'          => __( 'Please wait', 'ylc' ),
                         'chat_online'          => __( 'Chat Online', 'ylc' ),
                         'chat_offline'         => __( 'Chat Offline', 'ylc' ),
                         'your_msg'             => __( 'Your message', 'ylc' ),
                         'end_chat'             => __( 'End chat', 'ylc' ),
                         'conn_err'             => __( 'Connection error!', 'ylc' ),
                         'you'                  => __( 'You', 'ylc' ),
                         'online_btn'           => __( 'Online', 'ylc' ),
                         'offline_btn'          => __( 'Offline', 'ylc' ),
                         'field_empty'          => __( 'Please fill out all required fields', 'ylc' ),
                         'invalid_email'        => __( 'Email is invalid', 'ylc' ),
                         'op_not_allowed'       => __( 'Operators cannot chat from this window, only visitors can. If you want to test chat box, you should use two different browsers or computers', 'ylc' ),
                         'user_email'           => __( 'User Email', 'ylc' ),
                         'user_ip'              => __( 'IP Address', 'ylc' ),
                         'user_page'            => __( 'Current Page', 'ylc' ),
                         'connect'              => __( 'Connect', 'ylc' ),
                         'disconnect'           => __( 'Disconnect', 'ylc' ),
                         'you_offline'          => __( 'You are offline', 'ylc' ),
                         'save_chat'            => __( 'Save chat', 'ylc' ),
                         'ntf_close_console'    => __( 'If you leave, you will be logged out of the chat. However you will be able to save the conversations into your server when you will be back to the console!', 'ylc' ),
                         'new_msg'              => __( 'New Message', 'ylc' ),
                         'new_user_online'      => __( 'New User Online', 'ylc' ),
                         'saving'               => __( 'Saving', 'ylc' ),
                         'waiting_users'        => ( defined( 'YLC_PREMIUM' ) ) ? __( 'User queue: %d', 'ylc') : __( 'There are people waiting to talk', 'ylc'),
                     )
                 )
             );

             // Add user information
             if( is_user_logged_in() ) {

                 // Get user prefix
                 $user_prefix = ( defined( 'YLC_OPERATOR' ) && is_admin() ) ? 'ylc-op-' : '';

                 $js_vars['user_id']            = $user_prefix . $this->user->ID;
                 $js_vars['user_name']          = ( ! defined( 'YLC_PREMIUM' ) ) ? $this->user->display_name : $this->get_operator_name() ;
                 $js_vars['user_email']         = $this->user->user_email;
                 $js_vars['user_email_hash']    = md5( $this->user->user_email );

             }

             wp_localize_script( 'ylc-engine', 'ylc', $js_vars );
        }

        /**
         * Load FontAwesome
         *
         * @since   1.0.0
         * @author  Alberto Ruggiero
         * @return  void
         */
        public function load_fontawesome() {

            if ( ! class_exists( 'YIT_Asset' ) ){

                $css_prefix = is_admin() ? 'yit-' : '';
                wp_enqueue_style( $css_prefix . 'font-awesome', YLC_ASSETS_URL . '/css/font-awesome.min.css' );

            }

        }

        /**
         * Load Chat Box
         *
         * @since   1.0.0
         * @author  Alberto Ruggiero
         * @return  void
         */
        function show_chat() {

            $plugin_opts = get_plugin_options();
            ?>

            <div id="YLC"></div>

            <script type="text/javascript">
                (function ($) {

                    $(document).ready(function () {

                        $('#YLC').ylc({
                            <?php print_plugin_options( $plugin_opts ); ?>
                        });

                    });

                } (window.jQuery || window.Zepto));

            </script>
        <?php
        }

        /**
         * Load Console Template
         *
         * @since   1.0.0
         * @author  Alberto Ruggiero
         * @return  void
         */
        function get_console_template() {

            require_once ( YLC_TEMPLATE_PATH . '/chat-backend/chat-console.php' );

        }

        /**
         * Load Custom Text Template
         *
         * @since   1.0.0
         * @author  Alberto Ruggiero
         * @return  void
         */
        function custom_text_template( $option, $db_value ) {

            require_once ( YLC_TEMPLATE_PATH . '/admin/custom-text.php' );

        }

        /**
         * Authentication user
         *
         * @since   1.0.0
         * @author  Alberto Ruggiero
         * @return  string Auth token
         */
        public function user_auth() {
            global $wpdb;


            if( empty( $this->options['firebase-appsecret'] ) ) {
                return;
            }
            // FireBase authentication
            $token_gen = new Services_FirebaseTokenGenerator( $this->options['firebase-appsecret'] );

            $prefix = ( is_user_logged_in() && !defined( 'YLC_OPERATOR' ) ) ? 'usr-' : '';

            // An object or array of data you wish to associate with the token. It will be available as the variable "auth" in the Firebase rules engine.
            $data = array(
                'uid' 		  => $prefix . $this->user->ID,
                'is_operator' => ( defined( 'YLC_OPERATOR' ) ) ? true : false,
            );

            // Options
            $opts = array(
                'admin'	=> ( current_user_can( 'manage_options' ) ) ? true : false,
                'debug' => true
            );

            // Create secure auth token
            return $token_gen->createToken( $data, $opts );

        }

        /**
         * Create / Update Chat Operator Role
         *
         * @param   $role string Default operator role
         * @since   1.0.0
         * @access  public
         * @return  void
         * @author  Alberto ruggiero
         */
        public function ylc_operator_role( $role ) {

            remove_role( 'ylc_chat_op' ); // First clean role
            $op_role = add_role( 'ylc_chat_op', __( 'YITH Live Chat Operator', 'ylc' ) ); // Create operator role
            $op_role->add_cap( 'answer_chat' ); // Add common operator capability

            switch( $role ) {

                /** N/A */
                case 'none':
                    $op_role->add_cap( 'read' );
                    break;
                /** Other roles */
                default:
                    $r = get_role( $role ); // Get editor role

                    // Add editor caps to chat operator
                    foreach( $r->capabilities as $custom_role => $v ) {
                        $op_role->add_cap( $custom_role );
                    }
            }
        }

        /**
         * Enqueue css file
         *
         * @since  1.0.0
         * @access public
         * @return void
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function plugin_fw_loader() {
            if ( ! defined( 'YIT' ) || ! defined( 'YIT_CORE_PLUGIN' ) ) {
                require_once( 'plugin-fw/yit-plugin.php' );
            }
        }

        /**
         * Premium Tab Template
         *
         * Load the premium tab template on admin page
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  void
         */
        public function premium_tab() {
            $premium_tab_template = YLC_TEMPLATE_PATH . '/admin/' . $this->_premium;
            if ( file_exists( $premium_tab_template ) ) {
                include_once( $premium_tab_template );
            }
        }

        /**
         * Get the premium landing uri
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  string The premium landing link
         */
        public function get_premium_landing_uri() {
            return defined( 'YITH_REFER_ID' ) ? $this->_premium_landing . '?refer_id=' . YITH_REFER_ID : $this->_premium_landing;
        }

        /**
         * Action Links
         *
         * add the action links to plugin admin page
         *
         * @param $links | links plugin array
         *
         * @return   mixed Array
         * @since    1.0.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @return mixed
         * @use plugin_action_links_{$plugin_file_name}
         */
        public function action_links( $links ) {

            $links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'ylc' ) . '</a>';

            if ( defined( 'YLC_FREE_INIT' ) ) {
                $links[] = '<a href="' . $this->get_premium_landing_uri() . '" target="_blank">' . __( 'Premium Version', 'ylc' ) . '</a>';
            }

            return $links;
        }

        /**
         * plugin_row_meta
         *
         * add the action links to plugin admin page
         *
         * @param $plugin_meta
         * @param $plugin_file
         * @param $plugin_data
         * @param $status
         *
         * @return   Array
         * @since    1.0.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use plugin_row_meta
         */
        public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
            if ( ( defined( 'YLC_INIT' ) && ( YLC_INIT == $plugin_file ) ) ||
                ( defined( 'YLC_FREE_INIT' ) && ( YLC_FREE_INIT == $plugin_file ) )
            ) {

                $plugin_meta[] = '<a href="' . $this->_official_documentation . '" target="_blank">' . __( 'Plugin Documentation', 'ylc' ) . '</a>';
            }

            return $plugin_meta;
        }
    }
}