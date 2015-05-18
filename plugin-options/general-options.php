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
    exit;
} // Exit if accessed directly

return array(
    'general' => array(
        /* =================== HOME =================== */
        'home'    => array(
            array(
                'name'  => __( 'YITH Live Chat: General Settings', 'ylc' ),
                'type'  => 'title'
            ),
            array(
                'type'  => 'close'
            )
        ),
        /* =================== END SKIN =================== */

        /* =================== GENERAL =================== */
        'settings' => array(
            array(
                'name'  => __( 'Enable Live Chat', 'ylc' ),
                'desc'  => __( 'Activate/Deactivate the live chat features. ', 'ylc' ),
                'id'    => 'plugin-enable',
                'type'  => 'on-off',
                'std'   => 'no'
            ),
            array(
                'name'              => __( 'Firebase App URL', 'ylc' ),
                'desc'              => __( 'URL of your Firebase application.', 'ylc' ),
                'id'                => 'firebase-appurl',
                'type'              => 'custom-text',
                'std'               => '',
                'custom_attributes' => array(
                    'required'  => 'required',
                    'style'     => 'width: 200px'
                )
            ),
            array(
                'name'              => __( 'Firebase App Secret', 'ylc' ),
                'desc'              => __( 'It can be found under the "Secrets" menu in your Firebase app dashboard', 'ylc' ),
                'id'                => 'firebase-appsecret',
                'type'              => 'text',
                'std'               => '',
                'custom_attributes' => array(
                    'required'  => 'required',
                    'style'     => 'width: 100%'
                )
            ),
        )
    )
);