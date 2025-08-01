<?php
/**
 * @wordpress-plugin
 * Plugin Name: Customer Email Verification for WooCommerce 
 * Plugin URI: https://www.zorem.com/products/customer-email-verification-for-woocommerce/ 
 * Description: The Customer verification helps WooCommerce store owners to reduce registration spam by requiring customers to verify their email address when they register an account on your store, before they can access their account area.
 * Version: 2.6.4
 * Author: zorem
 * Author URI: https://www.zorem.com 
 * License: GPL-2.0+
 * License URI: 
 * Text Domain: customer-email-verification-for-woocommerce
 * Domain Path: /lang/
 * WC tested up to: 10.0.4
 * Requires Plugins: woocommerce
*/


class Zorem_Woo_Customer_Email_Verification {
	/**
	 * Customer verification for WooCommerce version.
	 *
	 * @var string
	 */
	public $version = '2.6.4';
	public $plugin_file;
	public $plugin_path;
	public $my_account;
	public $email;
	public $preview;
	public $admin;
	public $signup;
	public $install;
	/**
	 * Initialize the main plugin function
	*/
	public function __construct() {
		
		$this->plugin_file = __FILE__;
		// Add your templates to this array.
		
		if ( !defined( 'CUSTOMER_EMAIL_VERIFICATION_PATH' ) ) {
			define( 'CUSTOMER_EMAIL_VERIFICATION_PATH', $this->get_plugin_path() );
		}
			
		$this->my_account = get_option( 'woocommerce_myaccount_page_id' );

		if ( '' === $this->my_account ) {
			$this->my_account = get_option( 'page_on_front' );
		}
		
		if ( ! $this->is_cev_pro_active() || ! $this->cev_pro_version_check() ) {
				
			if ( $this->is_wc_active() ) {
			
			// Include required files.
				$this->includes();			
				
				//start adding hooks
				$this->init();						
			
				$this->admin->init();	
				
				$this->email->init();
				
				$this->preview->init();		
				
			}
			add_action( 'init', array( $this, 'customer_email_verification_load_textdomain'));
		}
	}
	
	
	
	/**
	 * Check Customer email verification pro version
	 *	 
	 * @since  1.0.0
	 * @return bool
	*/
	private function cev_pro_version_check() {

		// Ensure the function 'is_plugin_active' is available
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		
		// Check if Pro plugin file exists
		$pro_plugin_file = WP_PLUGIN_DIR . '/customer-email-verification-pro/customer-email-verification-pro.php';
		if ( ! file_exists( $pro_plugin_file ) ) {
			return false;
		}
		
		// Get plugin data if the Pro plugin file exists
		$plugin_data = get_plugin_data( $pro_plugin_file );
		$cev_pro_version = $plugin_data['Version'];
		
		if (version_compare( $cev_pro_version , '1.0.5', '>=')) {
			$is_version = true;
		} else {
			$is_version = false;
		}		
	
		return $is_version;
	}
	
	
	/**
	 * Check if WooCommerce is active
	 *	 
	 * @since  1.0.0
	 * @return bool
	*/
	private function is_wc_active() {
		
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$is_active = true;
		} else {
			$is_active = false;
		}
		

		// Do the WC active check
		if ( false === $is_active ) {
			add_action( 'admin_init', array( $this, 'notice_activate_wc' ) );
		}		
		return $is_active;
	}
	
	/**
	 * Display WC active notice
	 *	 
	 * @since  1.0.0
	*/
	public function notice_activate_wc() {
		?>
		<div class="error">			
			<p>
			<?php 
			/* translators: %s: search WooCommerce plugin link */
			printf( esc_html__( 'Please install and activate %1$sWooCommerce%2$s for Customer Email Verification for WooCommerce!', 'customer-email-verification-for-woocommerce' ), '<a href="' . esc_url( admin_url( 'plugin-install.php?tab=search&s=WooCommerce&plugin-search-input=Search+Plugins' ) ) . '">', '</a>' ); 
			?>
			</p>
		</div>
		<?php
	}
	
	/**
	 * Check if CEV PRO is active
	 *	 
	 * @since  1.0.0
	 * @return bool
	*/
	private function is_cev_pro_active() {
		
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active( 'customer-email-verification-pro/customer-email-verification-pro.php' ) || is_plugin_active( 'customer-email-verification/customer-email-verification.php' ) ) {
			$is_active = true;
		} else {
			$is_active = false;
		}

			
		return $is_active;
	}
	
	/**
	 * Gets the absolute plugin path without a trailing slash, e.g.
	 * /path/to/wp-content/plugins/plugin-directory.
	 *
	 * @return string plugin path
	 */
	public function get_plugin_path() {
		if ( isset( $this->plugin_path ) ) {
			return $this->plugin_path;
		}

		$this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );

		return $this->plugin_path;
	}
	
	/**
	 * Gets the absolute plugin url.
	 */	
	public function plugin_dir_url() {
		return plugin_dir_url( __FILE__ );
	}
	
	/*
	* init when class loaded
	*/
	public function init() {
		//Custom Woocomerce menu
		add_action('admin_menu', array( $this->admin, 'register_woocommerce_menu' ), 99 );
		
		//load css js 
		add_action( 'admin_enqueue_scripts', array( $this->admin, 'admin_styles' ), 20);	
		add_filter( 'woocommerce_account_menu_items', array( $this, 'cev_account_menu_items' ), 10, 1 );	
		add_filter( 'woocommerce_account_menu_items', array( $this, 'hide_cev_menu_my_account' ), 999 );
		add_action( 'init', array( $this, 'cev_add_my_account_endpoint' ) );		
		add_action( 'woocommerce_account_email-verification_endpoint', array( $this, 'cev_email_verification_endpoint_content' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'front_styles' ));
		
		//callback for add action link for plugin page	
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this , 'my_plugin_action_links' ));
		
	}
	/*** Method load Language file ***/
	public function customer_email_verification_load_textdomain() {
		if ( ! did_action( 'init' ) && ! doing_action( 'init' ) ) {
			// Avoid loading text domain too early unless necessary
			return;
		}
		load_plugin_textdomain( 'customer-email-verification-for-woocommerce', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
	}
	
	/*
	* include files
	*/
	private function includes() {

		require_once $this->get_plugin_path() . '/includes/class-wc-customer-email-verification-installation.php';
		$this->install = CEV_Installation::get_instance(__FILE__);

		require_once $this->get_plugin_path() . '/includes/class-wc-customer-email-verification-admin.php';
		$this->admin = WC_customer_email_verification_admin::get_instance();

		require_once $this->get_plugin_path() . '/includes/class-wc-customer-email-verification-signup.php';
		$this->signup = CEV_Signup_Verification::get_instance();
		
		require_once $this->get_plugin_path() . '/includes/class-wc-customer-email-verification-email.php';
		$this->email = WC_customer_email_verification_email::get_instance();	
		
		require_once $this->get_plugin_path() . '/includes/class-wc-customer-email-verification-preview-front.php';
		$this->preview = WC_customer_email_verification_preview::get_instance();		
		
		require_once $this->get_plugin_path() . '/includes/class-wc-customer-email-verification-email-common.php';

		require_once $this->get_plugin_path() . '/includes/customizer/class-customer-verification-new-customizer.php';
		require_once $this->get_plugin_path() . '/includes/customizer/class-cev-customizer.php';
		require_once $this->get_plugin_path() . '/includes/customizer/verification-widget-style.php';	
		require_once $this->get_plugin_path() . '/includes/customizer/verification-widget-message.php';	
		require_once $this->get_plugin_path() . '/includes/cev-wc-admin-notices.php';		
	}

	
	
	/**
	 * Include front js and css
	*/
	public function front_styles() {				
		wp_register_script( 'cev-front-js', woo_customer_email_verification()->plugin_dir_url() . 'assets/js/front.js', array( 'jquery' ), woo_customer_email_verification()->version );
		wp_localize_script( 'cev-front-js', 'cev_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		wp_register_style( 'cev_front_style', woo_customer_email_verification()->plugin_dir_url() . 'assets/css/front.css', array(), woo_customer_email_verification()->version );		
		
		global $wp;	
		$current_slug = add_query_arg( array(), $wp->request );
		$email_verification_url = rtrim( wc_get_account_endpoint_url( 'email-verification' ), '/' );
		
		if ( home_url( $wp->request ) == $email_verification_url ) {	
			wp_enqueue_style( 'cev_front_style' );			
			wp_enqueue_script( 'cev-front-js' );			
		}	
	
		
		if ( is_checkout() ) {	
			wp_enqueue_style( 'cev_front_style' );			
			wp_enqueue_script( 'cev-front-js' );			
		}
		if ( is_cart() ) {	
			wp_enqueue_style( 'cev_front_style' );			
			wp_enqueue_script( 'cev-front-js' );			
		}	
		
	}
	
	/**
	* Check if user is administrator
	*
	* @param int $user_id
	* @return bool
	*/
	public function is_admin_user( $user_id ) {
		
		$user = get_user_by( 'id', $user_id );
		
		if ( !$user ) {
			return false;
		}
		
		$roles = $user->roles;
		
		if ( in_array( 'administrator', (array) $roles ) ) {
			return true;	
		}
		return false;
	}
	
	public function is_verification_skip_for_user( $user_id ) {
		
		$user = get_user_by( 'id', $user_id );
		
		if ( !$user ) {
			return false;
		}
		
		$roles = $user->roles;
		$cev_skip_verification_for_selected_roles = get_option('cev_skip_verification_for_selected_roles');		
		
		foreach ( (array) $cev_skip_verification_for_selected_roles as $role => $val ) {
			if ( in_array( $role, (array) $roles ) && 1 == $val ) {
				return true;
			}
		}
		return false;
	}
	
	/**
	* Account menu items
	*
	* @param arr $items
	* @return arr
	*/
	public function cev_account_menu_items( $items ) {
		$items['email-verification'] = __( 'Sign up email verification', 'customer-email-verification-for-woocommerce' );
		return $items;
	}
	
	/**
	* Hide Edit Address Tab @ My Account	
	*/			
	public function hide_cev_menu_my_account( $items ) {
		unset($items['email-verification']);
		return $items;
	}
	
	/**
	* Add endpoint
	*/
	public function cev_add_my_account_endpoint() {
		add_rewrite_endpoint( 'email-verification', EP_PAGES );
		// Original code commented out; flush moved to activation hook in installation class
		/*
		if ( version_compare( get_option( 'cev_version' ), '1.5', '<' ) ) {
			global $wp_rewrite;
			$wp_rewrite->set_permalink_structure('/%postname%/');
			$wp_rewrite->flush_rules();
			update_option( 'cev_version', '1.5');				
		}
		*/
	}
	
	/**
	* Information content
	*/
	public function cev_email_verification_endpoint_content() {
		
		$current_user = wp_get_current_user();
		$email = $current_user->user_email;						
		$verified  = get_user_meta( get_current_user_id(), 'customer_email_verified', true );
		
		$cev_verification_widget_style = new cev_verification_widget_message();
		$cev_verification_overlay_color = get_option( 'cev_verification_popup_overlay_background_color', $cev_verification_widget_style->defaults['cev_verification_popup_overlay_background_color'] );
			
		if ( $this->is_admin_user( get_current_user_id() ) || $this->is_verification_skip_for_user( get_current_user_id() ) ) {
			return;
		}
		
		if ( 'true' === $verified ) {
			return;
		}
		?>
		<style>
		.cev-authorization-grid__visual{
			background: <?php echo esc_html( $this->hex2rgba( $cev_verification_overlay_color, '0.7' ) ); ?>;	
		}		
		</style>
		<?php 	
		$cev_button_color_widget_header =  get_option( 'cev_button_color_widget_header', '#212121' );
		$cev_button_text_color_widget_header =  get_option( 'cev_button_text_color_widget_header', '#ffffff' );
		$cev_button_text_size_widget_header =  get_option( 'cev_button_text_size_widget_header', '15' );
		$cev_widget_header_image_width =  get_option( 'cev_widget_header_image_width', '150' );
		$cev_button_text_header_font_size = get_option( 'cev_button_text_header_font_size', '22' );
		
		require_once $this->get_plugin_path() . '/includes/views/cev_admin_endpoint_popup_template.php';
	}	
	
	/* Convert hexdec color string to rgb(a) string */
 
	public function hex2rgba( $color, $opacity = false ) {
	
		$default = 'rgba(116,194,225,0.7)';
	
		//Return default if no color provided
		if ( empty( $color ) ) {
			return $default;
		}
	
		//Sanitize $color if "#" is provided 
		if ( '#' == $color[0] ) {
			$color = substr( $color, 1 );
		}
	
		//Check if color has 6 or 3 characters and get values
		if (strlen($color) == 6) {
				$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) == 3 ) {
				$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
				return $default;
		}
	
		//Convert hexadec to rgb
		$rgb =  array_map('hexdec', $hex);
	
		//Check if opacity is set(rgba or rgb)
		if ( $opacity ) {
			if ( abs( $opacity ) > 1 ) {
				$opacity = 1.0;
			}
			$output = 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ' )';
		} else {
			$output = 'rgb(' . implode( ',', $rgb ) . ')';
		}
	
		//Return rgb(a) color string
		return $output;
	}	
	/**
	 * Add plugin action links.
	 *
	 * Add a link to the settings page on the plugins.php page.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $links List of existing plugin action links.
	 * @return array         List of modified plugin action links.
	 */
	public function my_plugin_action_links( $links ) {
		// Delay translation until init to avoid early textdomain loading
		if ( ! did_action( 'init' ) ) {
			return $links;
		}
		
		$links = array_merge( array(
			'<a href="' . esc_url( admin_url( '/admin.php?page=customer-email-verification-for-woocommerce' ) ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>'
		), $links );
		
		if ( !class_exists( 'customer_email_verification_pro' ) ) {
			$links = array_merge( $links, array(
				'<a target="_blank" style="color: green; font-weight: bold;" href="' . esc_url( 'https://www.zorem.com/product/customer-verification-for-woocommerce/?utm_source=wp-admin&utm_medium=CEVPRO&utm_campaign=add-ons') . '">' . __( 'Go Pro', 'woocommerce' ) . '</a>'
			) );
		}
		
		return $links;
	}

}

/**
 * Returns an instance of zorem_woo_il_post.
 *
 * @since 1.0
 * @version 1.0
 *
 * @return zorem_woo_il_post
*/
/**
 * Returns an instance of Zorem_Woo_Customer_Email_Verification.
 *
 * @since 1.0
 * @version 1.0
 *
 * @return Zorem_Woo_Customer_Email_Verification
 */
function woo_customer_email_verification() {
	static $instance;

	if ( ! isset( $instance ) ) {
		$instance = new Zorem_Woo_Customer_Email_Verification();
	}

	return $instance;
}

/**
 * Bootstrap the plugin on plugins_loaded.
 */
add_action( 'init', 'woo_customer_email_verification' );

/**
 * Declare HPOS compatibility.
 */
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

/**
 * Init Zorem Tracking.
 */
if ( ! function_exists( 'zorem_tracking' ) ) {
	function zorem_tracking() {
		require_once dirname(__FILE__) . '/zorem-tracking/zorem-tracking.php';
		$plugin_name = 'Customer Email Verification for WooCommerce';
		$plugin_slug = 'customer-email-verification-for-woocommerce-free';
		$user_id = '1';
		$setting_page_type = 'top-level';
		$setting_page_location =  'A custom top-level admin menu (admin.php)';
		$parent_menu_type = '';
		$menu_slug = 'customer-email-verification-for-woocommerce';
		$plugin_id = '18';

		$zorem_tracking = WC_Trackers::get_instance(
			$plugin_name,
			$plugin_slug,
			$user_id,
			$setting_page_type,
			$setting_page_location,
			$parent_menu_type,
			$menu_slug,
			$plugin_id
		);

		return $zorem_tracking;
	}
	zorem_tracking();
}
