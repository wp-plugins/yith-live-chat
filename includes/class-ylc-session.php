<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements sessions for YITH Live Chat
 *
 * @class   YLC_Session
 * @package Yithemes
 * @since   1.0.0
 * @author  Your Inspiration Themes
 *
 */
class YLC_Session {

    private $session        = array();
    private $PHP_sessions   = YLC_PHP_SESSIONS;

    /**
     * Constructor
     *
     * @author Alberto Ruggiero
     * @return mixed
     * @since  1.0.0
     * @access public
     */
    public function __construct() {

        // Session control on user data
        add_action( 'wp_login', array ( $this, 'destroy_session' ) );
        add_action( 'wp_logout', array ( $this, 'logout' ) );

        // Use native PHP sessions
        if( $this->PHP_sessions ) {

            if( !session_id() )
                add_action( 'init', 'session_start', -2 );

            // Use WP Session Manager
        } else {

            // Let users change the session cookie name
            if( ! defined( 'WP_SESSION_COOKIE' ) )
                define( 'WP_SESSION_COOKIE', '_wp_session' );

            if ( ! class_exists( 'Recursive_ArrayAccess' ) ) {
                require_once( YLC_DIR . 'includes/wp-session-manager/class-recursive-arrayaccess.php' );
            }

            // Only include the functionality if it's not pre-defined.
            if ( ! class_exists( 'WP_Session' ) ) {
                require_once( YLC_DIR . 'includes/wp-session-manager/class-wp-session.php' );
                require_once( YLC_DIR . 'includes/wp-session-manager/wp-session.php' );
            }

        }

        // Initialize the session
        if ( empty( $this->session ) && ! $this->PHP_sessions ) {
            add_action( 'plugins_loaded', array( $this, 'init' ), -1 );
        } else {
            add_action( 'init', array( $this, 'init' ), -1 );
        }

    }

    /**
     * Set the instance of WP_Session
     *
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @return array
     */
    public function init() {

        if( $this->PHP_sessions )
            $this->session = isset( $_SESSION['yith_live_chat'] ) && is_array( $_SESSION['yith_live_chat'] ) ? $_SESSION['yith_live_chat'] : array();
        else
            $this->session = WP_Session::get_instance();

        return $this->session;
    }

    /**
     * Get session ID
     *
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @return string Session ID
     */
    public function get_id() {
        return $this->session->session_id;
    }

    /**
     * Get a session variable
     *
     * @param string $key Session key
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @return  string Session variable
     */
    public function get( $key ) {

        return isset( $this->session[ $key ] ) ? maybe_unserialize( $this->session[ $key ] ) : false;

    }

    /**
     * Set a session variable
     *
     * @param string $key Session key
     * @param mixed $value Session variable
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @return mixed Session variable
     */
    public function set( $key, $value ) {

        // Set value
        $this->session[ $key ] = $value;

        if( $this->PHP_sessions )
            $_SESSION['yith_live_chat'] = $this->session;

        return $this->session[ $key ];
    }

    /**
     * Destroy current session
     *
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @return  array Session variable
     */
    public function logout() {

        global $yith_livechat;

        $this->destroy_session();

        $sess_user = array( 'user_disconnected' => true ); // We should know user disconnected by clicking logout!

        $yith_livechat->session->set( 'user_data', $sess_user ); // Update user in session

    }

    /**
     * Destroy Session
     *
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @return  void
     */
    public function destroy_session() {

        global $yith_livechat;

        if( YLC_PHP_SESSIONS ) {

            $yith_livechat->session->set( 'user_data', NULL );

            session_destroy();

        } else {

            wp_session_unset(); // Destroy session

            wp_session_cleanup(); // Clean expired sessions from DB

            $yith_livechat->session = WP_Session::get_instance(); // Reassign WP Session

        }

    }

}