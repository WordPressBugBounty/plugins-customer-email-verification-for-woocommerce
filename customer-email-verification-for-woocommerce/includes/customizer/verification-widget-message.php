<?php
/**
 * Customizer Setup and Custom Controls
 *
 * @package Customer_Email_Verification_For_WooCommerce
 */

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class Cev_Verification_Widget_Message {
	// Get our default values
	private static $order_ids = null;
	public $defaults;

	public function __construct() {
		// Load text domain on init to avoid early loading
		add_action('init', [$this, 'load_textdomain']);

		// Initialize defaults on init to avoid early translation calls
		add_action('init', [$this, 'initialize_defaults']);

		// Register our sample default controls
		add_action('customize_register', [$this, 'cev_my_verification_widget_message']);

		// Only proceed with customizer actions if this is our own request
		if (!self::is_own_customizer_request() && !self::is_own_preview_request()) {
			return;
		}

		// Register our sections
		add_action('customize_register', [wc_cev_customizer(), 'cev_add_customizer_sections']);

		// Remove unrelated components
		add_filter('customize_loaded_components', [wc_cev_customizer(), 'remove_unrelated_components'], 99, 2);

		// Remove unrelated sections
		add_filter('customize_section_active', [wc_cev_customizer(), 'remove_unrelated_sections'], 10, 2);

		// Unhook Divi front end
		add_action('woomail_footer', [wc_cev_customizer(), 'unhook_divi'], 10);

		// Unhook Flatsome JS
		add_action('customize_preview_init', [wc_cev_customizer(), 'unhook_flatsome'], 50);

		// Enqueue customizer scripts
		add_filter('customize_controls_enqueue_scripts', [wc_cev_customizer(), 'enqueue_customizer_scripts']);

		// Enqueue preview scripts
		add_action('customize_preview_init', [$this, 'enqueue_preview_scripts']);
	}

	/**
	 * Load the plugin text domain
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'customer-email-verification-for-woocommerce',
			false,
			dirname(plugin_basename(__FILE__)) . '/languages/'
		);
	}

	/**
	 * Initialize defaults after init hook
	 */
	public function initialize_defaults() {
		$this->defaults = $this->cev_generate_defaults();
	}

	/**
	 * Add CSS and JS for preview
	 */
	public function enqueue_preview_scripts() {
		wp_enqueue_style(
			'cev-pro-preview-styles',
			woo_customer_email_verification()->plugin_dir_url() . 'assets/css/preview-styles.css',
			[],
			woo_customer_email_verification()->version
		);
	}

	/**
	 * Checks to see if we are opening our custom customizer preview
	 *
	 * @return bool
	 */
	public static function is_own_preview_request() {
		return is_customize_preview() && isset($_REQUEST['action']) &&
			( 'preview_cev_verification_lightbox' === $_REQUEST['action'] || 'guest_user_preview_cev_verification_lightbox' === $_REQUEST['action'] );
	}

	/**
	 * Checks to see if we are opening our custom customizer controls
	 *
	 * @return bool
	 */
	public static function is_own_customizer_request() {
		return is_customize_preview() && isset($_REQUEST['section']) && 'cev_verification_widget_messages' === $_REQUEST['section'];
	}

	/**
	 * Get Customizer URL
	 *
	 * @param string $section
	 * @return string
	 */
	public static function get_customizer_url( $section ) {
		$return_tab = 'settings'; // Adjust as needed
		$customizer_url = add_query_arg(
			[
				'cev-customizer' => '1',
				'section' => $section,
				'autofocus[section]' => 'cev_verification_widget_messages',
				'url' => urlencode(add_query_arg(['action' => 'preview_cev_verification_lightbox'], home_url('/'))),
				'return' => urlencode(self::get_cev_widget_message_page_url($return_tab)),
			],
			admin_url('customize.php')
		);

		return $customizer_url;
	}

	/**
	 * Get WooCommerce email settings page URL
	 *
	 * @param string $return_tab
	 * @return string
	 */
	public static function get_cev_widget_message_page_url( $return_tab ) {
		return admin_url('admin.php?page=customer-email-verification-for-woocommerce&tab=' . $return_tab);
	}

	/**
	 * Generate default values for customizer
	 *
	 * @return array
	 */
	public function cev_generate_defaults() {
		$customizer_defaults = [
			'cev_verification_popup_background_color' => '#f5f5f5',
			'cev_verification_popup_overlay_background_color' => '#ffffff',
			'cev_verification_header' => __('Verify its you.', 'customer-email-verification-for-woocommerce'),
			'cev_verification_message' => __('We sent a verification code. To verify your email address, please check your inbox and enter the code below.', 'customer-email-verification-for-woocommerce'),
			'cev_verification_widget_footer' => __("Didn't receive an email? {cev_resend_verification}", 'customer-email-verification-for-woocommerce'),
		];
		return $customizer_defaults;
	}

	/**
	 * Register our sample default controls
	 *
	 * @param WP_Customize_Manager $wp_customize
	 */
	public function cev_my_verification_widget_message( $wp_customize ) {
		// Load all our Customizer Custom Controls
		require_once trailingslashit(dirname(__FILE__)) . 'custom-controls.php';

		// Overlay Background Color
		$wp_customize->add_setting(
			'cev_verification_popup_overlay_background_color',
			[
				'default' => $this->defaults['cev_verification_popup_overlay_background_color'],
				'transport' => 'refresh',
				'sanitize_callback' => 'sanitize_hex_color',
				'type' => 'option',
			]
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'cev_verification_popup_overlay_background_color',
				[
					'label' => __('Overlay Background Color', 'customer-email-verification-for-woocommerce'),
					'section' => 'cev_verification_widget_messages',
					'priority' => 1,
				]
			)
		);

		// Widget Background Color
		$wp_customize->add_setting(
			'cev_verification_popup_background_color',
			[
				'default' => $this->defaults['cev_verification_popup_background_color'],
				'transport' => 'refresh',
				'sanitize_callback' => 'sanitize_hex_color',
				'type' => 'option',
			]
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'cev_verification_popup_background_color',
				[
					'label' => __('Widget Background Color', 'customer-email-verification-for-woocommerce'),
					'section' => 'cev_verification_widget_messages',
					'priority' => 2,
				]
			)
		);

		// Header Text
		$wp_customize->add_setting(
			'cev_verification_header',
			[
				'default' => $this->defaults['cev_verification_header'],
				'transport' => 'refresh',
				'sanitize_callback' => 'sanitize_text_field',
				'type' => 'option',
			]
		);
		$wp_customize->add_control(
			'cev_verification_header',
			[
				'label' => __('Header Text', 'customer-email-verification-for-woocommerce'),
				'section' => 'cev_verification_widget_messages',
				'type' => 'text',
				'input_attrs' => [
					'placeholder' => $this->defaults['cev_verification_header'],
				],
			]
		);

		// Message Content
		$wp_customize->add_setting(
			'cev_verification_message',
			[
				'default' => $this->defaults['cev_verification_message'],
				'transport' => 'refresh',
				'sanitize_callback' => 'sanitize_textarea_field',
				'type' => 'option',
			]
		);
		$wp_customize->add_control(
			'cev_verification_message',
			[
				'label' => __('Message', 'customer-email-verification-for-woocommerce'),
				'section' => 'cev_verification_widget_messages',
				'type' => 'textarea',
				'input_attrs' => [
					'placeholder' => $this->defaults['cev_verification_message'],
				],
			]
		);

		// Footer Content
		$wp_customize->add_setting(
			'cev_verification_widget_footer',
			[
				'default' => $this->defaults['cev_verification_widget_footer'],
				'transport' => 'refresh',
				'sanitize_callback' => 'sanitize_textarea_field',
				'type' => 'option',
			]
		);
		$wp_customize->add_control(
			'cev_verification_widget_footer',
			[
				'label' => __('Footer Content', 'customer-email-verification-for-woocommerce'),
				'section' => 'cev_verification_widget_messages',
				'type' => 'textarea',
				'input_attrs' => [
					'placeholder' => $this->defaults['cev_verification_widget_footer'],
				],
			]
		);

		// Available Variables Info Block
		$wp_customize->add_setting(
			'cev_widzet_code_block',
			[
				'default' => '',
				'transport' => 'postMessage',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);
		$wp_customize->add_control(
			new WP_Customize_cev_codeinfoblock_Control(
				$wp_customize,
				'cev_widzet_code_block',
				[
					'label' => __('Available Variables', 'customer-email-verification-for-woocommerce'),
					'description' => '<code>{cev_resend_verification}</code><br>You can use HTML tags: <strong>, <i>',
					'section' => 'cev_verification_widget_messages',
				]
			)
		);
	}
}

/**
 * Initialise our Customizer settings
 */
$cev_verification_widget_message = new Cev_Verification_Widget_Message();
