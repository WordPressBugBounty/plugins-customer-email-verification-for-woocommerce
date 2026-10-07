<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_CEV_Admin_Notices_Under_WC_Admin {

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	/**
	 * Initialize the main plugin function
	*/
	public function __construct() {
		$this->init();
	}
	
	/**
	 * Get the class instance
	 *
	 * @return WC_CEV_Admin_Notices_Under_WC_Admin
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	/*
	* init from parent mail class
	*/
	public function init() {
		add_action( 'admin_notices', array( $this, 'admin_notice_pro_update' ) );

		// Handle dismiss action for the Pro version update notice.
		add_action('admin_init', array( $this, 'cev_pro_notice_ignore' ) );

		// Remember when the plugin was first seen, for the notice grace period.
		add_action( 'admin_init', array( $this, 'record_first_seen' ) );

	}

	/**
	 * Days after install before the upgrade notice may appear.
	 */
	const NOTICE_GRACE_DAYS = 7;

	/**
	 * Days a dismissed upgrade notice stays hidden.
	 */
	const NOTICE_SNOOZE_DAYS = 90;

	/**
	 * Option holding the timestamp the plugin was first seen in wp-admin.
	 */
	const FIRST_SEEN_OPTION = 'cev_free_first_seen_at';

	/**
	 * Option holding the timestamp the upgrade notice was last dismissed.
	 */
	const DISMISSED_OPTION = 'cev_pro_notice_dismissed_at';

	/**
	 * Store the first-seen timestamp once. Existing installs get it on the
	 * first admin page load after updating.
	 */
	public function record_first_seen() {
		if ( ! get_option( self::FIRST_SEEN_OPTION ) ) {
			update_option( self::FIRST_SEEN_OPTION, time(), false );
		}
	}

	/**
	 * Whether at least one customer has verified their email.
	 *
	 * Once true it is stored permanently; a negative answer is cached for
	 * 12 hours so the user query runs at most twice a day.
	 *
	 * @return bool
	 */
	public function has_verified_customer() {
		if ( get_option( 'cev_has_verified_customer' ) ) {
			return true;
		}
		if ( get_transient( 'cev_has_verified_customer_check' ) ) {
			return false;
		}

		$cev_verified = get_users(
			array(
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Single-row lookup, result cached in an option/transient.
				'meta_key'    => 'customer_email_verified',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Single-row lookup, result cached in an option/transient.
				'meta_value'  => 'true',
				'number'      => 1,
				'fields'      => 'ID',
				'count_total' => false,
			)
		);

		if ( ! empty( $cev_verified ) ) {
			update_option( 'cev_has_verified_customer', 1, false );
			return true;
		}

		set_transient( 'cev_has_verified_customer_check', 1, 12 * HOUR_IN_SECONDS );
		return false;
	}

	/**
	 * Whether the upgrade notice should show on this request.
	 *
	 * Rules: store managers only, not on the CEV page itself, not in the
	 * first 7 days, only after the first verified customer, and hidden for
	 * 90 days after a dismissal.
	 *
	 * @return bool
	 */
	public function should_show_upgrade_notice() {
		if ( class_exists( 'customer_email_verification_pro' ) || ! current_user_can( 'manage_woocommerce' ) ) {
			return false;
		}

		$cev_first_seen = (int) get_option( self::FIRST_SEEN_OPTION, 0 );
		if ( 0 === $cev_first_seen || ( time() - $cev_first_seen ) < self::NOTICE_GRACE_DAYS * DAY_IN_SECONDS ) {
			return false;
		}

		$cev_dismissed = (int) get_option( self::DISMISSED_OPTION, 0 );
		if ( 0 !== $cev_dismissed && ( time() - $cev_dismissed ) < self::NOTICE_SNOOZE_DAYS * DAY_IN_SECONDS ) {
			return false;
		}

		return $this->has_verified_customer();
	}

	/*
	* Display an admin notice on plugin install or update
	*/
	public function admin_notice_pro_update() {

		if ( ! $this->should_show_upgrade_notice() ) {
			return;
		}

		// Check if we are on the "customer-email-verification-for-woocommerce" settings page.
		// If so, do not display the notice there.
		// Read-only check of which admin screen we are on, not form processing.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
		$cev_current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

		if ( 'customer-email-verification-for-woocommerce' === $cev_current_page ) {
			return;
		}

		// Dismiss link, nonce-protected; handled by cev_pro_notice_ignore().
		$dismissable_url = wp_nonce_url( add_query_arg( 'cev-pro-notice-dismiss', '1' ), 'cev_pro_notice_dismiss', 'cev_notice_nonce' );
		$cev_upgrade_url = woo_customer_email_verification()->get_pro_upgrade_url( 'admin-notice' );
		?>
		
		<style>
		.cev-dismissable-notice {
			border-left-color: #3b64d3 !important;
			padding-right: 38px;
		}
		.cev-dismissable-notice h3.cev-notice-title {
			margin-top: 10px;
			margin-bottom: 4px;
			font-size: 15px;
			font-weight: 700;
			color: #3b64d3;
		}
		.cev-notice-actions {
			display: flex;
			align-items: center;
			flex-wrap: wrap;
			gap: 8px;
			margin: 12px 0 14px;
		}
		.cev-notice-btn {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 7px 16px !important;
			height: auto !important;
			line-height: 1.4 !important;
			font-size: 13px !important;
			font-weight: 600 !important;
			border-radius: 6px !important;
			text-decoration: none !important;
			cursor: pointer;
			transition: background .15s, border-color .15s, box-shadow .15s;
			box-shadow: none !important;
			outline-offset: 2px;
		}
		.cev-notice-btn-upgrade {
			background: #3b64d3 !important;
			border: 1px solid #3b64d3 !important;
			color: #fff !important;
		}
		.cev-notice-btn-upgrade:hover,
		.cev-notice-btn-upgrade:focus {
			background: #2d50b5 !important;
			border-color: #2d50b5 !important;
			color: #fff !important;
		}
		.cev-notice-btn-dismiss {
			background: #fff !important;
			border: 1px solid #c5cfe8 !important;
			color: #3b64d3 !important;
		}
		.cev-notice-btn-dismiss:hover,
		.cev-notice-btn-dismiss:focus {
			background: #eef2ff !important;
			border-color: #3b64d3 !important;
			color: #3b64d3 !important;
		}
		</style>

		<?php
		// Display the notice only if the Pro version of the plugin is not active.
		if ( ! class_exists( 'customer_email_verification_pro' ) ) {
			?>
			<div class="notice notice-info cev-dismissable-notice is-dismissible">
				<a href="<?php echo esc_url( $dismissable_url ); ?>" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'customer-email-verification-for-woocommerce' ); ?></span>
				</a>

				<h3 class="cev-notice-title">✉️ <?php esc_html_e( 'Upgrade to Customer Email Verification PRO – Secure Every Step of the Customer Journey!', 'customer-email-verification-for-woocommerce' ); ?></h3>
				<p><?php esc_html_e( 'Get advanced verification features to protect your store from spam, fake signups, and fraudulent orders:', 'customer-email-verification-for-woocommerce' ); ?></p>
				<p>
					✅ <?php esc_html_e( 'Verify emails during signup and checkout', 'customer-email-verification-for-woocommerce' ); ?><br>
					✅ <?php esc_html_e( 'OTP-based login authentication for secure access', 'customer-email-verification-for-woocommerce' ); ?><br>
					✅ <?php esc_html_e( 'Customizable checkout, login and reminder emails', 'customer-email-verification-for-woocommerce' ); ?><br>
					✅ <?php esc_html_e( 'Block fake registrations and unauthorized activity', 'customer-email-verification-for-woocommerce' ); ?>
				</p>
				<p>🎁 <?php esc_html_e( 'Special Offer: Get 20% OFF with coupon code CEVPRO20 – limited time only!', 'customer-email-verification-for-woocommerce' ); ?></p>

				<div class="cev-notice-actions">
					<a class="cev-notice-btn cev-notice-btn-upgrade" href="<?php echo esc_url( $cev_upgrade_url ); ?>" target="_blank" rel="noreferrer noopener">
						👉 <?php esc_html_e( 'Upgrade to CEV PRO Now', 'customer-email-verification-for-woocommerce' ); ?>
					</a>
					<a class="cev-notice-btn cev-notice-btn-dismiss" href="<?php echo esc_url( $dismissable_url ); ?>">
						<?php esc_html_e( 'Dismiss', 'customer-email-verification-for-woocommerce' ); ?>
					</a>
				</div>
			</div>
		<?php
		}
	}

	/*
	* Hide admin notice when the "ignore" parameter is set in the URL.
	* This prevents the notice from being displayed again.
	*/
	public function cev_pro_notice_ignore() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended -- Nonce is verified below before anything is saved.
		$cev_dismiss_requested = isset( $_GET['cev-pro-notice-dismiss'] );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended -- This is the nonce value itself.
		$cev_notice_nonce = isset( $_GET['cev_notice_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['cev_notice_nonce'] ) ) : '';

		if ( ! $cev_dismiss_requested || '' === $cev_notice_nonce ) {
			return;
		}
		if ( ! wp_verify_nonce( $cev_notice_nonce, 'cev_pro_notice_dismiss' ) || ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// Snooze for NOTICE_SNOOZE_DAYS; should_show_upgrade_notice() reads this.
		update_option( self::DISMISSED_OPTION, time(), false );
		wp_safe_redirect( remove_query_arg( array( 'cev-pro-notice-dismiss', 'cev_notice_nonce' ) ) );
		exit;
	}
}

/**
 * Returns an instance of WC_CEV_Admin_Notices_Under_WC_Admin.
 *
 * @since 1.6.5
 * @version 1.6.5
 *
 * @return WC_CEV_Admin_Notices_Under_WC_Admin
*/
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Established public API of this plugin; renaming breaks CEV PRO and customer code.
function WC_CEV_Admin_Notices_Under_WC_Admin() {
	static $instance;

	if ( ! isset( $instance ) ) {		
		$instance = new WC_CEV_Admin_Notices_Under_WC_Admin();
	}

	return $instance;
}

/**
 * Register this class globally.
 *
 * Backward compatibility.
*/
WC_CEV_Admin_Notices_Under_WC_Admin();
