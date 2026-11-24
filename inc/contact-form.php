<?php
/**
 * Built-in contact form: [pq_contact_form]
 *
 * No plugin needed. Messages are emailed to the site administrator address
 * (Settings > General > Administration Email Address).
 *
 * @package PornQuebec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Topics offered in the form.
 *
 * @return array
 */
function pq_contact_topics() {
	return array(
		'general' => __( 'General question', 'pornquebec' ),
		'product' => __( 'A product we feature', 'pornquebec' ),
		'privacy' => __( 'Privacy request', 'pornquebec' ),
		'other'   => __( 'Something else', 'pornquebec' ),
	);
}

/**
 * Render the form.
 *
 * @return string
 */
function pq_contact_form_shortcode() {
	$status = isset( $_GET['pq_status'] ) ? sanitize_key( wp_unslash( $_GET['pq_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

	$messages = array(
		'sent'    => array( 'ok', __( 'Thank you. Your message has been sent and we will get back to you.', 'pornquebec' ) ),
		'invalid' => array( 'err', __( 'Please fill in your name, a valid email, your message and tick the consent box.', 'pornquebec' ) ),
		'wait'    => array( 'err', __( 'Please wait a moment before sending another message.', 'pornquebec' ) ),
		'failed'  => array( 'err', __( 'Sorry, your message could not be sent. Please try again in a few minutes.', 'pornquebec' ) ),
	);

	$privacy_url = function_exists( 'get_privacy_policy_url' ) && get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/privacy-policy/' );

	ob_start();
	echo '<div class="pq-contact" id="pq-contact">';

	if ( isset( $messages[ $status ] ) ) {
		printf(
			'<div class="pq-notice %1$s" role="status">%2$s</div>',
			esc_attr( $messages[ $status ][0] ),
			esc_html( $messages[ $status ][1] )
		);
	}

	if ( 'sent' !== $status ) :
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="pq_contact">
			<input type="hidden" name="pq_ts" value="<?php echo esc_attr( time() ); ?>">
			<?php wp_nonce_field( 'pq_contact', 'pq_nonce' ); ?>

			<div class="pq-hp" aria-hidden="true">
				<label for="pq_website"><?php esc_html_e( 'Leave this field empty', 'pornquebec' ); ?></label>
				<input type="text" id="pq_website" name="pq_website" tabindex="-1" autocomplete="off">
			</div>

			<div class="pq-row">
				<div>
					<label for="pq_name"><?php esc_html_e( 'Your name', 'pornquebec' ); ?></label>
					<input type="text" id="pq_name" name="pq_name" required autocomplete="name">
				</div>
				<div>
					<label for="pq_email"><?php esc_html_e( 'Your email', 'pornquebec' ); ?></label>
					<input type="email" id="pq_email" name="pq_email" required autocomplete="email">
				</div>
			</div>

			<div>
				<label for="pq_topic"><?php esc_html_e( 'Topic', 'pornquebec' ); ?></label>
				<select id="pq_topic" name="pq_topic">
					<?php foreach ( pq_contact_topics() as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div>
				<label for="pq_message"><?php esc_html_e( 'Your message', 'pornquebec' ); ?></label>
				<textarea id="pq_message" name="pq_message" required></textarea>
			</div>

			<label class="pq-consent">
				<input type="checkbox" name="pq_consent" value="1" required>
				<span>
					<?php
					printf(
						/* translators: %s: link to the Privacy Policy */
						esc_html__( 'I agree that PornQuebec may use my details to reply to this message, as explained in the %s.', 'pornquebec' ),
						'<a href="' . esc_url( $privacy_url ) . '">' . esc_html__( 'Privacy Policy', 'pornquebec' ) . '</a>'
					);
					?>
				</span>
			</label>

			<button type="submit"><?php esc_html_e( 'Send message', 'pornquebec' ); ?></button>
		</form>
		<?php
	endif;

	echo '</div>';
	return ob_get_clean();
}
add_shortcode( 'pq_contact_form', 'pq_contact_form_shortcode' );

/**
 * Redirect back to the page with a status flag.
 *
 * @param string $status Status key.
 */
function pq_contact_redirect( $status ) {
	$back = wp_get_referer();
	if ( ! $back ) {
		$back = home_url( '/contact/' );
	}
	$back = remove_query_arg( 'pq_status', $back );
	wp_safe_redirect( add_query_arg( 'pq_status', $status, $back ) . '#pq-contact' );
	exit;
}

/**
 * Handle the submission.
 */
function pq_contact_handle() {
	if ( ! isset( $_POST['pq_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pq_nonce'] ) ), 'pq_contact' ) ) {
		pq_contact_redirect( 'failed' );
	}

	// Bots: filled honeypot or form submitted in under 3 seconds.
	$ts = isset( $_POST['pq_ts'] ) ? absint( $_POST['pq_ts'] ) : 0;
	if ( ! empty( $_POST['pq_website'] ) || ( time() - $ts ) < 3 ) {
		pq_contact_redirect( 'sent' ); // Look successful to bots.
	}

	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'pq_rl_' . md5( $ip );
	if ( get_transient( $key ) ) {
		pq_contact_redirect( 'wait' );
	}

	$name    = isset( $_POST['pq_name'] ) ? sanitize_text_field( wp_unslash( $_POST['pq_name'] ) ) : '';
	$email   = isset( $_POST['pq_email'] ) ? sanitize_email( wp_unslash( $_POST['pq_email'] ) ) : '';
	$message = isset( $_POST['pq_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pq_message'] ) ) : '';
	$topic   = isset( $_POST['pq_topic'] ) ? sanitize_key( wp_unslash( $_POST['pq_topic'] ) ) : 'general';
	$consent = ! empty( $_POST['pq_consent'] );

	$topics = pq_contact_topics();
	if ( ! isset( $topics[ $topic ] ) ) {
		$topic = 'general';
	}

	if ( '' === $name || ! is_email( $email ) || '' === $message || ! $consent ) {
		pq_contact_redirect( 'invalid' );
	}

	$subject = sprintf( '[%s] %s - %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $topics[ $topic ], $name );
	$body    = "Name: {$name}\nEmail: {$email}\nTopic: {$topics[ $topic ]}\n\n{$message}\n\n--\nSent from " . home_url( '/' );
	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );

	set_transient( $key, 1, 30 );

	if ( wp_mail( get_option( 'admin_email' ), $subject, $body, $headers ) ) {
		pq_contact_redirect( 'sent' );
	}
	pq_contact_redirect( 'failed' );
}
add_action( 'admin_post_nopriv_pq_contact', 'pq_contact_handle' );
add_action( 'admin_post_pq_contact', 'pq_contact_handle' );
