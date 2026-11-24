<?php
/**
 * PornQuebec child theme for Astra.
 *
 * @package PornQuebec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PQ_VERSION', '1.0.0' );
define( 'PQ_DIR', get_stylesheet_directory() );
define( 'PQ_URI', get_stylesheet_directory_uri() );

require_once PQ_DIR . '/inc/setup.php';
require_once PQ_DIR . '/inc/hover-image.php';
require_once PQ_DIR . '/inc/contact-form.php';

/**
 * Styles: Google Fonts first, then the theme stylesheet after Astra's.
 */
function pq_enqueue_assets() {
	wp_enqueue_style(
		'pq-fonts',
		'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Lobster+Two:ital,wght@1,700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'pq-style',
		PQ_URI . '/assets/css/pornquebec.css',
		array( 'pq-fonts' ),
		filemtime( PQ_DIR . '/assets/css/pornquebec.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'pq_enqueue_assets', 20 );
