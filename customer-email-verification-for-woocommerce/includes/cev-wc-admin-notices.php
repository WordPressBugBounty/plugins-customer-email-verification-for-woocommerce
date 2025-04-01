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
		
	// Display an admin notice for Pro version update.
		add_action( 'admin_notices', array( $this, 'admin_notice_pro_update' ) );

		// Display an admin notice for verification process changes.
		add_action( 'admin_notices', array( $this, 'admin_notice_verification_process_changed_update' ) );

		// Handle dismiss action for the Pro version update notice.
		add_action('admin_init', array( $this, 'cev_pro_notice_ignore' ) );

		// Handle dismiss action for the verification process change notice.
		add_action('admin_init', array( $this, 'wc_cev_verification_process_changed_notice_ignore' ) );

	}

	
	/*
	* Display an admin notice about the verification process update.
	*/
	public function admin_notice_verification_process_changed_update() {
		
		// Check if the notice has already been dismissed, and if so, do not display it.
		if ( get_option('wc_cev_verification_process_changed_notice_ignore') ) {
			return;
		}

		// Check if we are currently on the "customer-email-verification-for-woocommerce" settings page.
		// If so, do not show the notice to avoid redundancy.
		if ( isset( $_GET['page'] ) && 'customer-email-verification-for-woocommerce' === $_GET['page'] ) {
			return;
		}

		// Generate a URL to dismiss the notice by adding a query parameter.
		$dismissable_url = esc_url( add_query_arg( 'wc-cev-verification-process-changed-notice-ignore', 'true' ) );
		?>
		
		<style>
		/* Styling for the dismissable admin notice */
		.wp-core-ui .notice.cev-dismissable-notice {
			position: relative;
			padding-right: 38px;
			border-left-color: #3b64d3;
		}

		/* Styling for the dismiss button */
		.wp-core-ui .notice.cev-dismissable-notice a.notice-dismiss {
			padding: 9px;
			text-decoration: none;
		} 

		/* Styling for the action buttons */
		.wp-core-ui .button-primary.btn_pro_notice {
			background: transparent;
			color: #395da4;
			border-color: #395da4;
			text-transform: uppercase;
			padding: 0 11px;
			font-size: 12px;
			height: 30px;
			line-height: 28px;
			margin: 5px 0 15px;
		}
		</style>

		<!-- Admin notice about the verification process change -->
		<div class="notice updated notice-success cev-dismissable-notice">
			
			<!-- Dismiss button -->
			<a href="<?php echo esc_url( $dismissable_url ); ?>" class="notice-dismiss">
				<span class="screen-reader-text">Dismiss this notice.</span>
			</a>
			
			<!-- Notice content -->
			<h3 style="margin-top: 10px;">Important Update: Verification Process Changed!</h3>
			<p>
				We've updated <b>Customer Email Verification for WooCommerce</b> with a new <b>verification flow</b>. 
				Customers must now verify their email <b>before</b> account creation. 
				Some settings and customization options have also been removed.
			</p>
			<p>
				<b>Action Required:</b> Review your <b>plugin settings</b> and <b>customizer settings</b> to align with the update.
			</p>
			
			<!-- Settings button -->
			<a class="button-primary btn_pro_notice" href="<?php echo esc_url( admin_url( 'admin.php?page=customer-email-verification-for-woocommerce' ) ); ?>">
				Settings
			</a>
			
			<!-- Dismiss button -->
			<a class="button-primary ast_notice_btn" href="<?php esc_html_e( $dismissable_url ); ?>">Dismiss</a>
		</div>
		
		<?php
	}

	/*
	* Hide admin notice when the "ignore" parameter for the verification process change is set in the URL.
	* This prevents the notice from being displayed again.
	*/
	public function wc_cev_verification_process_changed_notice_ignore() {
		if ( isset( $_GET['wc-cev-verification-process-changed-notice-ignore'] ) ) {
			update_option( 'wc_cev_verification_process_changed_notice_ignore', 'true' );
		}
	}

	/*
	* Display an admin notice on plugin install or update
	*/
	public function admin_notice_pro_update() { 		
		
		// Check if the notice has been dismissed before, and if so, do not display it.
		if ( get_option('wc_cev_pro_notice_ignore_1_5') ) {
			return;
		}
		
		// Check if we are on the "customer-email-verification-for-woocommerce" settings page.
		// If so, do not display the notice there.
		if ( isset( $_GET['page'] ) && 'customer-email-verification-for-woocommerce' === $_GET['page'] ) {
			return;
		}
		
		// Generate the dismissable URL with a query parameter to ignore the notice.
		$dismissable_url = esc_url( add_query_arg( 'wc-cev-pro-ignore-notice-1-5', 'true' ) );
		?>
		
		<style>		
		/* Styling for the dismissable admin notice */
		.wp-core-ui .notice.cev-dismissable-notice {
			position: relative;
			padding-right: 38px;
			border-left-color: #3b64d3;
		}
		
		/* Styling for the dismiss button */
		.wp-core-ui .notice.cev-dismissable-notice a.notice-dismiss {
			padding: 9px;
			text-decoration: none;
		} 
		
		/* Styling for the Pro upgrade button */
		.wp-core-ui .button-primary.btn_pro_notice {
			background: transparent;
			color: #395da4;
			border-color: #395da4;
			text-transform: uppercase;
			padding: 0 11px;
			font-size: 12px;
			height: 30px;
			line-height: 28px;
			margin: 5px 0 15px;
		}
		</style>
		
		<?php 
		// Display the notice only if the Pro version of the plugin is not active.
		if ( !class_exists( 'customer_email_verification_pro' ) ) { 
			?>
			<div class="notice updated notice-success cev-dismissable-notice">
				<!-- Dismiss button -->
				<a href="<?php echo esc_url( $dismissable_url ); ?>" class="notice-dismiss">
					<span class="screen-reader-text">Dismiss this notice.</span>
				</a>
				
				<!-- Notice content -->
				<h3 style="margin-top: 10px; color:#3b64d3;font-size:16px">✅ Secure & Verify with Customer Email Verification PRO!</h3>
				<p>Upgrade to <b>Customer Email Verification PRO</b> to prevent fake registrations, require email verification at checkout, enable login authentication, and customize verification emails for a seamless experience.</p>
				<p>🎉 <b>Get 20% Off*!</b> Use code <b>CEVPRO20</b> at checkout.</p>
				
				<!-- Upgrade button -->
				<a class="button-primary btn_pro_notice" target="_blank" 
					href="https://www.zorem.com/product/customer-email-verification/" style="background:#3b64d3;font-size:14px; border:1px solid #3b64d3; margin-bottom:0; color:#fff;">Upgrade Now ></a>
				
				<!-- Dismiss button -->
				<a class="button-primary ast_notice_btn" href="<?php esc_html_e( $dismissable_url ); ?>" style="background:#3b64d3;font-size:14px; border:1px solid #3b64d3; margin-bottom:0;" >Dismiss</a>
				<p style="margin-bottom: 10px;">⭐ for new customers only</p>
			</div>
		<?php
		}
	}
	/*
	* Hide admin notice when the "ignore" parameter is set in the URL.
	* This prevents the notice from being displayed again.
	*/
	public function cev_pro_notice_ignore() {
		if ( isset( $_GET['wc-cev-pro-ignore-notice-1-5'] ) ) {
			update_option( 'wc_cev_pro_notice_ignore_1_5', 'true' );
		}
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
