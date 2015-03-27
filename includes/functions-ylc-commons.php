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

if ( ! function_exists( 'sanitize_text' ) ) {

    /**
     * Sanitize strings
     *
     * @since   1.0.0
     * @param   $string
     * @param   $html
     * @return  string
     * @author  Alberto ruggiero
     */
    function sanitize_text( $string, $html = false ) {
        if( $html )
            return html_entity_decode( addslashes( $string ) );
        else
            return addslashes( $string );
    }

}

if ( ! function_exists( 'get_plugin_options' ) ) {

    /**
     * Get plugin options
     *
     * @since   1.0.0
     * @return  array
     * @author  Alberto ruggiero
     */
    function get_plugin_options() {
        global $yith_livechat;

        $options = $yith_livechat->options;

        // Prepare callbacks
        $after_load = array( '_FUNC_' => 'function() {}' );
        $new_msg    = array( '_FUNC_' => 'function() {}' );

        $user_info = null;

        if( !empty( $yith_livechat->user ) ) {

            // Add 'usr-' prefix, because user_id must be string
            $xtra_prefix = ( is_user_logged_in() && !defined( 'YLC_OPERATOR' ) ) ? 'usr-' : '';

            // Get user prefix
            $user_prefix = ( defined( 'YLC_OPERATOR' ) && is_admin() ) ? 'op-' : '';

            $user_info = array(
                'id'        => $xtra_prefix . $user_prefix . $yith_livechat->user->ID,
                'name'      => $yith_livechat->user->display_name,
                'email'     => $yith_livechat->user->user_email,
                'gravatar'  => ( !empty( $yith_livechat->user->user_email ) ) ? md5( $yith_livechat->user->user_email ) : null
            );
        }


        /**
         * Get plugin options
         */
        return apply_filters( 'ylc_plugin_opts', array(
                'app_id'        => $options['firebase-appurl'],
                'users_list_id' => '',
                'user_info'     => $user_info,
                'after_load'    => $after_load,
                'new_msg'       => $new_msg
            )
        );
    }
}


if ( ! function_exists( 'print_plugin_options' ) ) {

    /**
     * Print plugin options
     *
     * @since   1.0.0
     * @param   $options
     * @param   $property
     * @return  array
     * @author  Alberto ruggiero
     */
    function print_plugin_options( $options, $property = null ) {
        $total_opts = count( $options );

        if ( $property ) {
            echo $property . ": {\n\t\t\t\t";
        }

        $i = 1;
        foreach ( $options as $option_key => $option_value ) {

            $comma = ( $i < $total_opts ) ? ",\n\t\t\t" : "\n";

            if ( !is_array( $option_value ) or !empty( $option_value['_FUNC_'] ) ) {                                        // Print single line option

                if ( is_array( $option_value ) and !empty( $option_value['_FUNC_'] ) ) {                                    // It is a callback / function?
                    $val = $option_value['_FUNC_'];
                } else {
                    $val = ( is_int( $option_value ) or is_numeric( $option_value ) ) ? $option_value : "'$option_value'";  // Sanitize value
                }

                echo $option_key . ': ' . $val . $comma;                                                                    // Print option

            } else {                                                                                                        // Print array option

                print_plugin_options( $option_value, $option_key );

            }

            $i ++;
        }

        if ( $property ) {
            echo "},\n\t\t\t";
        }

    }

}
