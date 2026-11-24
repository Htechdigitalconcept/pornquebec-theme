<?php
/**
 * Hover image swap for WooCommerce product cards.
 *
 * Adds the first gallery image on top of the main image inside each shop-loop
 * card. The CSS only swaps images on cards that have one, so products without
 * a gallery keep their first image on hover.
 *
 * @package PornQuebec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Track whether we are inside a product card in a shop loop.
 *
 * @param bool|null $set Set the flag, or null to read it.
 * @return bool
 */
function pq_in_card( $set = null ) {
	static $in_card = false;
	if ( null !== $set ) {
		$in_card = (bool) $set;
	}
	return $in_card;
}

add_action(
	'woocommerce_before_shop_loop_item',
	function () {
		pq_in_card( true );
	},
	1
);
add_action(
	'woocommerce_after_shop_loop_item',
	function () {
		pq_in_card( false );
	},
	99
);

/**
 * Append the second image to the card thumbnail markup.
 *
 * @param string     $image   Image HTML.
 * @param WC_Product $product Product.
 * @return string
 */
function pq_add_hover_image( $image, $product ) {
	if ( ! pq_in_card() || ! $product instanceof WC_Product ) {
		return $image;
	}

	$gallery = $product->get_gallery_image_ids();
	if ( empty( $gallery ) ) {
		return $image;
	}

	$hover = wp_get_attachment_image(
		(int) $gallery[0],
		'woocommerce_thumbnail',
		false,
		array(
			'class'    => 'pq-hover-img',
			'alt'      => '',
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);

	return $image . $hover;
}
add_filter( 'woocommerce_product_get_image', 'pq_add_hover_image', 10, 2 );
