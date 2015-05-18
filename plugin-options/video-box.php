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

global $yith_livechat;

$videobox = defined( 'YLC_PREMIUM' ) ? array() : array(
    'name'      => __( 'Upgrade to the PREMIUM VERSION', 'ylc' ),
	'type'    => 'videobox',
	'default' => array(
        'plugin_name'               => __( 'YITH Live Chat', 'ylc' ),
        'title_first_column'        => __( 'Discover the Advanced Features', 'ylc' ),
        'description_first_column'  => __( 'Upgrade to the PREMIUM VERSION of YITH Live Chat to benefit from all the features!', 'ylc' ),
		'video'                     => array(
            'video_id'          => '127461393',
            'video_image_url'   =>  YLC_ASSETS_URL.'/images/yith-live-chat.jpg',
            'video_description' => __( 'YITH Live Chat', 'ylc' )
		),
        'title_second_column'       => __( 'Get Support and Pro Features', 'ylc' ),
        'description_second_column' => __( 'By purchasing the premium version of the plugin, you will take advantage of the advanced features of the product, and you will get one year of free updates and support through our platform available 24h/24.', 'ylc' ),
        'button'                    => array(
            'href'  => $yith_livechat->get_premium_landing_uri(),
			'title' => 'Get Support and Pro Features'
		)
	),
	'id'      => 'ylc_general_videobox'
);

return $videobox;
