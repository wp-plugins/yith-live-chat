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

if ( ! function_exists( 'ylc_ajax_callback' ) ) {

    /**
     * Manage AJAX Callbacks
     *
     * @return  array
     * @since   1.0.0
     * @author  Alberto Ruggiero
     */
    function ylc_ajax_callback() {

        // Response var
        $resp = array();

        try {

            // Handling the supported actions:
            switch ( $_GET['mode'] ) {

                case 'get_token':
                    $resp = ajax_get_token();
                    break;

                case 'save_chat':
                    $resp = ajax_save_chat( $_POST );
                    break;

                default:
                    throw new Exception( 'Wrong action: ' . @$_REQUEST['mode'] );
            }

        } catch ( Exception $e ) {
            $resp['err_code'] = $e->getCode();
            $resp['error']    = $e->getMessage();
        }

        // Response output
        header( "Content-Type: application/json" );

        echo json_encode( $resp );

        exit;

    }

}

if ( ! function_exists( 'ajax_get_token' ) ) {

    /**
     * Get token
     *
     * @return  array
     * @since   1.0.0
     * @author  Alberto Ruggiero
     */
    function ajax_get_token() {
        global $yith_livechat;

        $token = $yith_livechat->user_auth();

        return array( 'token' => $token );
    }

}

if ( ! function_exists( 'ajax_save_chat' ) ) {

    /**
     * Save chat transcripts if premium active
     *
     * @return  array
     * @since   1.0.0
     * @author  Alberto Ruggiero
     */
    function ajax_save_chat( $data ) {

        $msg = __( 'Successfully closed!', 'ylc' );

        if ( defined( 'YLC_PREMIUM' ) ) {
            $msg = ''; // TODO: premium save function
        }

        return array( 'msg' => $msg );
    }

}