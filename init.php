<?php
/**
 * Plugin Name: YITH Live Chat
 * Plugin URI: http://yithemes.com/themes/plugins/yith-live-chat/
 * Description: Pre-sales question ? Needs support ? Chat with your customers!
 * Author: Yithemes
 * Text Domain: ylc
 * Version: 1.0.0
 * Author URI: http://yithemes.com/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

function ylc_install_free_admin_notice() {
    ?>
    <div class="error">
        <p><?php _e( 'You can\'t activate the free version of YITH Live Chat while you are using the premium one.', 'ylc' ); ?></p>
    </div>
<?php
}

if ( ! defined( 'YLC_VERSION' ) ) {
    define( 'YLC_VERSION', '1.0.0' );
}

if ( ! defined( 'YLC_FREE_INIT' ) ) {
    define( 'YLC_FREE_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YLC_FILE' ) ) {
    define( 'YLC_FILE', __FILE__ );
}

if ( ! defined( 'YLC_DIR' ) ) {
    define( 'YLC_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'YLC_URL' ) ) {
    define( 'YLC_URL', plugins_url( '/', __FILE__ ) );
}

if ( ! defined( 'YLC_ASSETS_URL' ) ) {
    define( 'YLC_ASSETS_URL', YLC_URL . 'assets' );
}

if ( ! defined( 'YLC_TEMPLATE_PATH' ) ) {
    define( 'YLC_TEMPLATE_PATH', YLC_DIR . 'templates' );
}

if ( ! defined( 'YLC_PHP_SESSIONS' ) ) {
    define( 'YLC_PHP_SESSIONS', true );
}

function ylc_free_init() {

    /* Load text domain */
    load_plugin_textdomain( 'ylc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    /* === Global YITH Live Chat Object  === */
    global $yith_livechat;
    $yith_livechat = new YITH_Livechat();
}
add_action( 'ylc_init', 'ylc_free_init' );

function ylc_install() {

    if ( defined( 'YLC_PREMIUM' ) ) {
        add_action( 'admin_notices', 'ylc_install_free_admin_notice' );
        deactivate_plugins( plugin_basename( __FILE__ ) );
    } else {
        do_action( 'ylc_init' );
    }
}
add_action( 'plugins_loaded', 'ylc_install', 11 );

/**
 * Init default plugin settings
 */
if ( !function_exists( 'yith_plugin_registration_hook' ) ) {
    require_once 'plugin-fw/yit-plugin-registration-hook.php';
}

require_once( YLC_DIR . 'class.yith-livechat.php' );

register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );
register_activation_hook( __FILE__, 'on_activation' );

function on_activation() {
    $ylc = new YITH_Livechat;
    $ylc->ylc_operator_role( 'editor' );

    /**
     * Administration role
     */
    $admin_role = get_role( 'administrator' );
    $admin_role->add_cap( 'answer_chat' );

    /**
     * Chat Operator role
     */
    $op_role = get_role( 'ylc_chat_op' );
    $op_role->add_cap( 'answer_chat' );
}

    //require_once CX_PATH . '/core/fn.setup.php'; // We need some functions inside
/*
    global $wpdb;

    $wpdb->hide_errors();

    $collate = '';

    if ( $wpdb->has_cap( 'collation' ) ) {
        if ( ! empty($wpdb->charset ) ) {
            $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if ( ! empty($wpdb->collate ) ) {
            $collate .= " COLLATE $wpdb->collate";
        }
    }

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $ywrr_tables = "
            CREATE TABLE {$wpdb->prefix}ywrr_email_blocklist (
              id int NOT NULL AUTO_INCREMENT,
              customer_email longtext NOT NULL,
              customer_id bigint(20) NOT NULL DEFAULT 0,
              PRIMARY KEY (id)
            ) $collate;
            CREATE TABLE {$wpdb->prefix}ywrr_email_schedule (
              id int NOT NULL AUTO_INCREMENT,
              order_id bigint(20) NOT NULL,
              order_date date NOT NULL DEFAULT '0000-00-00',
              scheduled_date date NOT NULL DEFAULT '0000-00-00',
              request_items longtext NOT NULL DEFAULT '',
              mail_status varchar(15) NOT NULL DEFAULT 'pending',
              PRIMARY KEY (id)
            ) $collate;
            ";

    dbDelta( $ywrr_tables );*/


    // Chat logs table
    /*$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . CX_PX . "chat_logs` (
		  `msg_id` varchar(30) NOT NULL DEFAULT '',
		  `cnv_id` varchar(30) NOT NULL,
		  `user_id` varchar(30) NOT NULL DEFAULT '',
		  `name` varchar(32) DEFAULT NULL,
		  `gravatar` char(32) DEFAULT NULL,
		  `msg` text NOT NULL,
		  `time` bigint(13) unsigned NOT NULL,
		  UNIQUE KEY `msg_id` (`msg_id`)
		) DEFAULT CHARSET=utf8;" );*/

    // Conversation table
    /*$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . CX_PX . "conversations` (
		  `cnv_id` varchar(30) NOT NULL DEFAULT '',
		  `user_id` varchar(30) NOT NULL DEFAULT '',
		  `created_at` bigint(13) unsigned NOT NULL,
		  UNIQUE KEY `cnv_id` (`cnv_id`),
		  KEY `created_at` (`created_at`)
		) DEFAULT CHARSET=utf8;" );*/

    // Users table
    /*$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . CX_PX . "users` (
		  `user_id` varchar(30) NOT NULL DEFAULT '',
		  `type` varchar(12) NOT NULL DEFAULT '',
		  `name` varchar(32) DEFAULT NULL,
		  `ip` int(11) unsigned DEFAULT NULL,
		  `email` varchar(90) DEFAULT NULL,
		  `last_online` bigint(13) unsigned DEFAULT NULL,
		  UNIQUE KEY `user_id` (`user_id`)
		) DEFAULT CHARSET=utf8;" );*/
