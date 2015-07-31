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
    'texts' => array(

        /* =================== HOME =================== */
        'home'    => array(
            array(
                'name'  => __( 'YITH Live Chat: Message Settings', 'ylc' ),
                'type'  => 'title'
            ),
            array(
                'type'  => 'close'
            )
        ),
        /* =================== END SKIN =================== */

        /* =================== MESSAGES =================== */
        'settings' => array(
            array(
                'name'              => __( 'Chat Title', 'ylc'),
                'desc'              => __( 'This text will appear in the chat button and the chat title', 'ylc' ),
                'id'                => 'text-chat-title',
                'type'              => 'text',
                'std'               => __( 'Chat with us', 'ylc' ),
                'custom_attributes' => array(
                    'required'  => 'required',
                    'style'     => 'width: 100%'
                )
            ),
            array(
                'name'              => __( 'Welcome Message', 'ylc' ),
                'desc'              => __( 'This text will appear in the login form', 'ylc' ),
                'id'                => 'text-welcome',
                'type'              => 'textarea',
                'std'               => __( 'Have you got question? Write to us!', 'ylc' ),
                'custom_attributes' => array(
                    'required'  => 'required',
                    'class'     => 'textareas'
                )
            ),
            array(
                'name'              => __( 'Starting Chat Message', 'ylc' ),
                'desc'              => __( 'This text will appear when the chat starts', 'ylc' ),
                'id'                => 'text-start-chat',
                'type'              => 'textarea',
                'std'               => __( 'Questions, doubts, issues? We\'re here to help you!', 'ylc' ),
                'custom_attributes' => array(
                    'required'  => 'required',
                    'class'     => 'textareas'
                )
            ),
            array(
                'name'              => __( 'Closing Chat Message', 'ylc' ),
                'desc'              => __( 'This text will appear at the end of the chat', 'ylc' ),
                'id'                => 'text-close',
                'type'              => 'textarea',
                'std'               => __( 'This chat session has ended', 'ylc' ),
                'custom_attributes' => array(
                    'required'  => 'required',
                    'class'     => 'textareas'
                )
            ),
            array(
                'name'              => __( 'Offline Message', 'ylc' ),
                'desc'              => __( 'This text will appear if no operator is online', 'ylc' ),
                'id'                => 'text-offline',
                'type'              => 'textarea',
                'std'               => __( 'None of our operators are available at the moment. Please, try again later.', 'ylc' ),
                'custom_attributes' => array(
                    'required'  => 'required',
                    'class'     => 'textareas'
                )
            ),
            array(
                'name'              => __( 'Busy Message', 'ylc' ),
                'desc'              => __( 'This text will appear if all operators are busy', 'ylc' ),
                'id'                => 'text-busy',
                'type'              => 'textarea',
                'std'               => __( 'Our operators are busy. Please try again later', 'ylc' ),
                'custom_attributes' => array(
                    'required'  => 'required',
                    'class'     => 'textareas'
                )
            ),
        ),
    )
);