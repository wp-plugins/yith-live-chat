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
         * @var     /Yit_Plugin_Panel object
         * @since   1.0.0
         * @see     plugin-fw/lib/yit-plugin-panel.php
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
         * @since   1.0.0
         * @return  mixed
         * @author  Alberto Ruggiero
         */
        public function __construct() {

            add_action( 'after_setup_theme', array( $this, 'plugin_fw_loader' ), 1 );
            add_filter( 'plugin_action_links_' . plugin_basename( YLC_DIR . '/' . basename( YLC_FILE ) ), array( $this, 'action_links' ) );
            add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

            $this->options = get_option( 'yit_' . $this->_options_name . '_options' );

            add_action( 'admin_menu', array( $this, 'add_menu_page' ), 5 );
            add_action( 'yith_live_chat_premium', array( $this, 'premium_tab' ) );
            add_action( 'yit_panel_custom-text', array( $this, 'custom_text_template' ), 10, 3  );
            add_action( 'admin_enqueue_scripts', array( $this, 'options_panel_scripts' ) );

            // Include required files
            $this->includes();

            $plugin_enable = isset( $this->options['plugin-enable'] ) ? $this->options['plugin-enable'] : 'no';

            if ( $plugin_enable == 'yes' ) {

                add_action( 'admin_menu', array( $this, 'add_console_page' ), 5 );
                add_action( 'admin_enqueue_scripts', array( $this, 'chat_console_scripts' ) );

                $this->session = new YLC_Session();

                add_action( 'admin_notices', array( $this, 'admin_notices' ) );
                add_action( 'init', array( &$this, 'init' ), 0 );
                add_filter( 'ylc_js_console' , array( $this, 'console_resize') );
            }

        }

        /**
         * Include required core files
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function includes() {

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
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         * @use     /Yit_Plugin_Panel class
         * @see     plugin-fw/lib/yit-plugin-panel.php
         */
        public function add_menu_page() {

            if ( ! empty( $this->_panel ) ) {
                return;
            }

            $admin_tabs = array(
                'general'   => __( 'General', 'ylc' ),
                'texts'     => __( 'Messages', 'ylc' )
            );

            if ( defined( 'YLC_PREMIUM' ) ) {
                $admin_tabs['offline']          = __( 'Offline Messages', 'ylc' );
                $admin_tabs['transcript']       = __( 'Conversation', 'ylc' );
                $admin_tabs['style']            = __( 'Appearance', 'ylc' );
                //$admin_tabs['autoplay']         = __( 'Autoplay', 'ylc' );
                $admin_tabs['user']             = __( 'Users', 'ylc' );
            } else {
                $admin_tabs['premium-landing']  = __( 'Premium Version', 'ylc' );
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
                'plugin-url'       => YLC_URL,
                'options-path'     => YLC_DIR . 'plugin-options'
            );

            $this->_panel = new YIT_Plugin_Panel( $args );

        }

        /**
         * Add YITH Live Chat console page
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function add_console_page() {

            $page_title = __( 'YITH Live Chat', 'ylc');

            /* === Add Chat Console Page === */
            if( current_user_can( 'manage_options' ) ) {

                add_menu_page( $page_title, $page_title, 'manage_options', $this->_console_page, array( $this, 'get_console_template' ), YLC_ASSETS_URL  . '/images/favicon.png', 63 );

            } else if( current_user_can( 'answer_chat' ) ){

                add_menu_page( $page_title, $page_title, 'ylc_chat_op', $this->_console_page, array( $this, 'get_console_template' ), YLC_ASSETS_URL  . '/images/favicon.png', 63 );

            }

        }

        /**
         * Advise if the plugin cannot be performed
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
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
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function init() {

            $this->current_page = ylc_get_current_page_url();
            $this->ip           = ylc_get_ip_address();

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

            if( is_user_logged_in() ) {

                global $current_user;

                get_currentuserinfo();

                $this->user = $current_user;

            } else {

                $visitor_id = $this->session->get( 'visitor_id' );

                if( empty( $visitor_id ) ) {

                    $visitor_id = uniqid( rand(), false );
                    $this->session->set( 'visitor_id', $visitor_id );

                }

                $this->user = ( object ) array(
                    'ID'            => $visitor_id,
                    'display_name'  => null,
                    'user_email'    => null
                );

            }

            add_action( 'wp_footer', array( &$this, 'show_chat') );

        }

        /**
         * Enqueue Scripts
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function enqueue_scripts() {

            $this->load_fontawesome();

            wp_register_style( 'ylc-google-fonts','//fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,700italic,400,700,600', array(), null );
            wp_enqueue_style( 'ylc-google-fonts' );


            wp_register_style( 'ylc-frontend', YLC_ASSETS_URL  . '/css/ylc-frontend.css' );
            wp_enqueue_style( 'ylc-frontend' );

            wp_enqueue_script( 'jquery' );

            // § Plug-in
            wp_register_script( 'jquery-autosize', YLC_ASSETS_URL . '/js/jquery.autosize.min.js', array( 'jquery' ), '1.17.1' );
            wp_enqueue_script( 'jquery-autosize' );

            // Application JS
            $this->load_livechat_js();

        }

        /**
         * Add styles and scripts for options panel
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function options_panel_scripts() {

            if ( ylc_get_current_page() == $this->_panel_page ) {

                wp_register_style( 'ylc-styles', YLC_ASSETS_URL . '/css/ylc-styles.css' );
                wp_enqueue_style( 'ylc-styles' );

            }

        }

        /**
         * Add styles and scripts for Chat Console
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function chat_console_scripts() {

            // Load in chat console
            if( ylc_get_current_page() == $this->_console_page  ) {

                ylc_set_firebase_security();

                // Get currently logged user info
                get_currentuserinfo();

                //Load FontAwesome
                $this->load_fontawesome();

                // AutoSize Plug-in
                wp_register_script( 'jquery-autosize', YLC_ASSETS_URL . '/js/jquery.autosize.min.js', array( 'jquery' ), '1.17.1' );
                wp_enqueue_script( 'jquery-autosize' );

                // Application JS
                $this->load_livechat_js();

                wp_register_style( 'ylc-google-fonts','//fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,700italic,400,700,600', array(), null );
                wp_enqueue_style( 'ylc-google-fonts' );

                // Console stylesheet
                wp_register_style( 'ylc-console', YLC_ASSETS_URL . '/css/ylc-console.css' );
                wp_enqueue_style( 'ylc-console' );

            }

        }

        /**
         * Console Resize scripts
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function console_resize() {

            echo file_get_contents( YLC_DIR . 'assets/js/ylc-console.js' );

        }

        /**
         * Load Live Chat scripts
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function load_livechat_js() {

            wp_register_script( 'ylc-firebase', YLC_ASSETS_URL . '/js/firebase.js' );
            wp_enqueue_script( 'ylc-firebase' );

            wp_register_script( 'ylc-engine', YLC_ASSETS_URL . '/js/ylc-engine.js', array( 'jquery', 'ylc-firebase' ) );
            wp_enqueue_script( 'ylc-engine' );

            $options = $this->options;

            $js_vars = array(
                'app_id'            => esc_html( $options['firebase-appurl'] ),
                'ajax_url'   		=> str_replace( array('https:', 'http:'), '', admin_url( 'admin-ajax.php' ) ),
                'plugin_url'        => YLC_ASSETS_URL,
                'is_front_end' 	    => ( ! is_admin() ) ? true : null,
                'is_op' 			=> ( defined( 'YLC_OPERATOR' ) && is_admin() ) ? true : false,
                'is_premium' 		=> ( defined( 'YLC_PREMIUM' ) ) ? true : false,
                'current_page'		=> $this->current_page,
                'user_ip' 			=> $this->ip,
                'show_busy_form'    => apply_filters( 'ylc_busy_form', false ),
                'max_guests'        => apply_filters( 'ylc_max_guests', 2 ),
                'send_transcript'   => apply_filters( 'ylc_send_transcript', false ),
                'chat_evaluation'   => apply_filters( 'ylc_chat_evaluation', false ),
                'company_avatar'    => apply_filters( 'ylc_company_avatar', ''),
                'autoplay_opts'     => apply_filters( 'ylc_autoplay_opts', array() ),
                'templates'         => array(
                     'chat_popup'           => file_get_contents( YLC_DIR . 'templates/chat-frontend/chat-popup.php' ),
                     'chat_connecting'      => file_get_contents( YLC_DIR . 'templates/chat-frontend/chat-connecting.php' ),
                     'chat_btn'             => file_get_contents( YLC_DIR . 'templates/chat-frontend/chat-btn.php' ),
                     'chat_offline'         => file_get_contents( YLC_DIR . 'templates/chat-frontend/chat-offline.php' ),
                     'chat_login'           => file_get_contents( YLC_DIR . 'templates/chat-frontend/chat-login.php' ),
                     'chat_conversation'    => file_get_contents( YLC_DIR . 'templates/chat-frontend/chat-conversation.php' ),
                     'chat_line'            => file_get_contents( YLC_DIR . 'templates/chat-frontend/chat-line.php' ),
                     'console_user_item'    => file_get_contents( YLC_DIR . 'templates/chat-backend/console-user-item.php' ),
                     'console_conversation' => file_get_contents( YLC_DIR . 'templates/chat-backend/console-conversation.php' ),
                     'console_user_info'    => file_get_contents( YLC_DIR . 'templates/chat-backend/console-user-info.php' ),
                     'console_user_tools'   => file_get_contents( YLC_DIR . 'templates/chat-backend/console-user-tools.php' ),
                     'premium'              => apply_filters( 'ylc_templates_premium', array() ),
                 ),
                'strings'           => array(
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
                         'name'         => __( 'Your Name', 'ylc' ),
                         'name_ph'      => __( 'Please enter your name', 'ylc' ),
                         'email'        => __( 'Your Email', 'ylc' ),
                         'email_ph'     => __( 'Please enter your email', 'ylc' ),
                         'message'      => __( 'Your Message', 'ylc' ),
                         'message_ph'   => __( 'Write your question', 'ylc' ),
                     ),
                     'msg'          => array(
                         'chat_title'           => ylc_sanitize_text( esc_html( $options['text-chat-title'] ) ),
                         'prechat_msg'          => ylc_sanitize_text( esc_html( $options['text-welcome'] ), true ),
                         'welc_msg'             => ylc_sanitize_text( esc_html( $options['text-start-chat'] ), true ),
                         'start_chat'           => __( 'Start Chat', 'ylc' ),
                         'offline_body'         => ylc_sanitize_text( esc_html( $options['text-offline'] ), true ),
                         'busy_body'            => ylc_sanitize_text( esc_html( $options['text-busy'] ), true ),
                         'close_msg'            => ylc_sanitize_text( esc_html( $options['text-close'] ), true ),
                         'close_msg_user'       => __( 'The user has closed the conversation', 'ylc' ),
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
                         'invalid_username'     => __( 'Username is invalid', 'ylc' ),
                         'user_email'           => __( 'User Email', 'ylc' ),
                         'user_ip'              => __( 'IP Address', 'ylc' ),
                         'user_page'            => __( 'Current Page', 'ylc' ),
                         'connect'              => __( 'Connect', 'ylc' ),
                         'disconnect'           => __( 'Disconnect', 'ylc' ),
                         'you_offline'          => __( 'You are offline', 'ylc' ),
                         'save_chat'            => __( 'Save chat', 'ylc' ),
                         'ntf_close_console'    => __( 'If you leave the chat, you will be logged out. However you will be able to save the conversations into your server when you will come back in the console!', 'ylc' ),
                         'new_msg'              => __( 'New Message', 'ylc' ),
                         'new_user_online'      => __( 'New User Online', 'ylc' ),
                         'saving'               => __( 'Saving', 'ylc' ),
                         'waiting_users'        => ( defined( 'YLC_PREMIUM' ) ) ? __( 'User queue: %d', 'ylc' ) : __( 'There are people waiting to talk', 'ylc' ),
                         'good'                 => __( 'Good', 'ylc' ),
                         'bad'                  => __( 'Bad', 'ylc' ),
                         'chat_evaluation'      => __( 'Was this conversation useful? Vote this chat session.', 'ylc' ),
                         'talking_label'        => __( 'Talking with %s', 'ylc' ),
                         'timer'                => __( 'Elapsed time', 'ylc' ),
                         'chat_copy'            => __( 'Receive the copy of the chat via e-mail', 'ylc' ),
                         'already_logged'       => __( 'A user is already logged in with the same email address', 'ylc' )
                     )
                 )
            );

            if( is_user_logged_in() ) {

                if ( defined( 'YLC_OPERATOR' ) && is_admin() ) {

                    $user_prefix   = 'ylc-op-';
                    $user_type     = 'operator';

                } else {

                    $user_prefix   = '';
                    $user_type     = 'visitor';

                }

                $js_vars['user_id']            = $user_prefix . $this->user->ID;
                $js_vars['user_name']          = apply_filters( 'ylc_nickname', $this->user->display_name );
                $js_vars['user_email']         = $this->user->user_email;
                $js_vars['user_email_hash']    = md5( $this->user->user_email );
                $js_vars['user_type']          = $user_type;
                $js_vars['avatar_type']        = apply_filters( 'ylc_avatar_type', '' );
                $js_vars['avatar_image']       = apply_filters( 'ylc_avatar_image', '' );
                $js_vars['frontend_op_access'] = ( current_user_can( 'answer_chat' ) ) ? true : false;

            }

            wp_localize_script( 'ylc-engine', 'ylc', $js_vars );
        }

        /**
         * Load FontAwesome
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
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
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function show_chat() {

            require_once ( YLC_TEMPLATE_PATH . '/chat-frontend/chat-container.php' );

        }

        /**
         * Load Console Template
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function get_console_template() {

            require_once ( YLC_TEMPLATE_PATH . '/chat-backend/chat-console.php' );

        }

        /**
         * Load Custom Text Template
         *
         * @since   1.0.0
         * @param   $option
         * @param   $db_value
         * @param   $custom_attributes
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function custom_text_template( $option, $db_value, $custom_attributes ) {

            require_once ( YLC_TEMPLATE_PATH . '/admin/custom-text.php' );

        }

        /**
         * User Authentication
         *
         * @since   1.0.0
         * @return  string
         * @author  Alberto Ruggiero
         */
        public function user_auth() {

            if( empty( $this->options['firebase-appsecret'] ) ) {
                return;
            }

            $token_gen  = new Services_FirebaseTokenGenerator( esc_html( $this->options['firebase-appsecret'] ) );
            $prefix     = ( is_user_logged_in() && !defined( 'YLC_OPERATOR' ) ) ? 'usr-' : '';
            $data       = array(
                'uid' 		  => $prefix . $this->user->ID,
                'is_operator' => ( defined( 'YLC_OPERATOR' ) ) ? true : false,
            );
            $opts       = array(
                'admin'	=> ( current_user_can( 'manage_options' ) ) ? true : false,
                'debug' => true
            );

            return $token_gen->createToken( $data, $opts );

        }

        /**
         * Create / Update Chat Operator Role
         *
         * @since   1.0.0
         * @param   $role
         * @return  void
         * @author  Alberto Ruggiero
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
         * @since   1.0.0
         * @return  void
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
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
         * @return  void
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
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
         * @return  string The premium landing link
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function get_premium_landing_uri() {
            return defined( 'YITH_REFER_ID' ) ? $this->_premium_landing . '?refer_id=' . YITH_REFER_ID : $this->_premium_landing;
        }

        /**
         * Action Links
         *
         * add the action links to plugin admin page
         *
         * @since   1.0.0
         * @param   $links | links plugin array
         * @return  mixed
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @use     plugin_action_links_{$plugin_file_name}
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
         * @since   1.0.0
         * @param   $plugin_meta
         * @param   $plugin_file
         * @param   $plugin_data
         * @param   $status
         * @return  Array
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @use     plugin_row_meta
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