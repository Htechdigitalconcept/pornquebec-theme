<?php
/**
 * Setup: copies theme images into the uploads folder the Elementor templates
 * point to, and adds a small Appearance > PornQuebec setup page.
 *
 * @package PornQuebec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Copy images to wp-content/uploads/2026/11/ so the template image URLs work.
 *
 * @return int Number of files copied.
 */
function pq_copy_images() {
	$uploads = wp_upload_dir();
	$target  = trailingslashit( $uploads['basedir'] ) . '2026/11/';
	$source  = PQ_DIR . '/assets/images/';

	if ( ! wp_mkdir_p( $target ) ) {
		return 0;
	}

	$copied = 0;
	foreach ( glob( $source . 'pq-*.{png,jpg,jpeg}', GLOB_BRACE ) as $file ) {
		$dest = $target . basename( $file );
		if ( ! file_exists( $dest ) && copy( $file, $dest ) ) {
			$copied++;
		}
	}
	return $copied;
}

add_action( 'after_switch_theme', 'pq_copy_images' );

/**
 * Setup page.
 */
function pq_setup_menu() {
	add_theme_page( 'PornQuebec setup', 'PornQuebec', 'manage_options', 'pq-setup', 'pq_setup_page' );
}
add_action( 'admin_menu', 'pq_setup_menu' );

/**
 * Render the setup page.
 */
function pq_setup_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$notice = '';
	if ( isset( $_POST['pq_copy'] ) && check_admin_referer( 'pq_copy_images' ) ) {
		$n      = pq_copy_images();
		$notice = sprintf(
			/* translators: %d: number of files */
			esc_html__( 'Images checked. %d new file(s) copied.', 'pornquebec' ),
			$n
		);
	}

	$templates = array(
		'01-homepage.json'             => 'Homepage',
		'02-about.json'                => 'About',
		'03-privacy-policy.json'       => 'Privacy Policy',
		'04-terms-and-conditions.json' => 'Terms and Conditions',
		'05-contact.json'              => 'Contact',
	);

	$checks = array(
		'Astra parent theme'          => 'astra' === get_template(),
		'WooCommerce active'          => class_exists( 'WooCommerce' ),
		'Elementor active'            => did_action( 'elementor/loaded' ) > 0,
	);
	?>
	<div class="wrap">
		<h1>PornQuebec theme setup</h1>
		<?php if ( $notice ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>

		<h2>Status</h2>
		<ul>
			<?php foreach ( $checks as $label => $ok ) : ?>
				<li><?php echo $ok ? '&#10003;' : '&#10007;'; ?> <?php echo esc_html( $label ); ?></li>
			<?php endforeach; ?>
		</ul>

		<h2>Page templates</h2>
		<p>Download a file, then in Elementor go to <strong>Templates &gt; Saved Templates &gt; Import Templates</strong>, import it, and insert it on a blank page. Create the page first (for example &ldquo;Contact&rdquo; with the slug <code>contact</code>).</p>
		<ul>
			<?php foreach ( $templates as $file => $label ) : ?>
				<li><a href="<?php echo esc_url( PQ_URI . '/elementor-templates/' . $file ); ?>" download><?php echo esc_html( $label ); ?></a></li>
			<?php endforeach; ?>
		</ul>

		<h2>Images</h2>
		<p>The templates use images from <code>wp-content/uploads/2026/11/</code>. They were copied there when the theme was activated. If any are missing, copy them again.</p>
		<form method="post">
			<?php wp_nonce_field( 'pq_copy_images' ); ?>
			<p><button type="submit" name="pq_copy" value="1" class="button button-primary">Copy images again</button></p>
		</form>

		<h2>Contact form</h2>
		<p>The Contact template uses the built-in shortcode <code>[pq_contact_form]</code>. Messages are emailed to <strong><?php echo esc_html( get_option( 'admin_email' ) ); ?></strong> (change it under Settings &gt; General).</p>
	</div>
	<?php
}
