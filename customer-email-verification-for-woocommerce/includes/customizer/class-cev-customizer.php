<?php
/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class Cev_Initialise_Customizer_Settings {
	// Get our default values
	private static $order_ids = null;
	public $defaults;

	public function __construct() {
		// Initialize defaults without translations to avoid early textdomain loading
		$this->defaults = array(
			'cev_verification_email_heading' => 'Please Verify Your Email Address',
			'cev_verification_email_subject' => 'Please Verify Your Email Address on {site_title}',
			'cev_verification_email_body' => 'Thank you for signing up for {site_title}. To activate your account, we need to verify your email address. <p>Your verification code: <strong>{cev_user_verification_pin}</strong></p>',
		);

		// Apply translations to defaults on a later hook
		add_action('init', array($this, 'apply_translations_to_defaults'));

		// Register our sample default controls
		add_action('customize_register', array($this, 'cev_register_sample_default_controls'));

		// Only proceed if this is own request
		if (!self::is_own_customizer_request() && !self::is_own_preview_request()) {
			return;
		}

		// Register our sections
		add_action('customize_register', array(wc_cev_customizer(), 'cev_add_customizer_sections'));

		// Remove unrelated components
		add_filter('customize_loaded_components', array(wc_cev_customizer(), 'remove_unrelated_components'), 99, 2);

		// Remove unrelated sections
		add_filter('customize_section_active', array(wc_cev_customizer(), 'remove_unrelated_sections'), 10, 2);

		// Unhook Divi front end
		add_action('woomail_footer', array(wc_cev_customizer(), 'unhook_divi'), 10);

		// Unhook Flatsome JS
		add_action('customize_preview_init', array(wc_cev_customizer(), 'unhook_flatsome'), 50);

		// Enqueue customizer scripts
		add_filter('customize_controls_enqueue_scripts', array(wc_cev_customizer(), 'enqueue_customizer_scripts'));

		// Set up preview
		add_action('parse_request', array($this, 'set_up_preview'));

		// Enqueue preview scripts
		add_action('customize_preview_init', array($this, 'enqueue_preview_scripts'));
	}

	/**
	 * Apply translations to defaults after text domain is loaded
	 */
	public function apply_translations_to_defaults() {
		$this->defaults = array(
			'cev_verification_email_heading' => __('Please Verify Your Email Address', 'customer-email-verification-for-woocommerce'),
			'cev_verification_email_subject' => __('Please Verify Your Email Address on {site_title}', 'customer-email-verification-for-woocommerce'),
			'cev_verification_email_body' => __('Thank you for signing up for {site_title}. To activate your account, we need to verify your email address. <p>Your verification code: <strong>{cev_user_verification_pin}</strong></p>', 'customer-email-verification-for-woocommerce'),
		);
	}

	/**
	 * Add CSS and JS for preview
	 */
	public function enqueue_preview_scripts() {
		wp_enqueue_style('cev-preview-styles', woo_customer_email_verification()->plugin_dir_url() . 'assets/css/preview-styles.css', array(), time());
	}

	/**
	 * Checks to see if we are opening our custom customizer preview
	 *
	 * @return bool
	 */
	public static function is_own_preview_request() {
		return isset($_REQUEST['cev-email-preview']) && '1' === $_REQUEST['cev-email-preview'];
	}

	/**
	 * Checks to see if we are opening our custom customizer controls
	 *
	 * @return bool
	 */
	public static function is_own_customizer_request() {
		return isset($_REQUEST['section']) && 'cev_main_controls_section' === $_REQUEST['section'];
	}

	/**
	 * Get Customizer URL
	 */
	public static function get_customizer_url( $section ) {
		$customizer_url = add_query_arg(
			array(
				'cev-customizer' => '1',
				'section' => $section,
				'url' => urlencode(add_query_arg(array('cev-email-preview' => '1'), home_url('/'))),
			),
			admin_url('customize.php')
		);

		return $customizer_url;
	}

	/**
	 * Register our sample default controls
	 */
	public function cev_register_sample_default_controls( $wp_customize ) {
		/**
		 * Load all our Customizer Custom Controls
		 */
		require_once trailingslashit(dirname(__FILE__)) . 'custom-controls.php';

		// Email Subject
		$wp_customize->add_setting('cev_verification_email_subject', array(
			'default' => $this->defaults['cev_verification_email_subject'],
			'transport' => 'refresh',
			'type' => 'option',
			'sanitize_callback' => ''
		));
		$wp_customize->add_control('cev_verification_email_subject', array(
			'label' => __('Subject', 'woocommerce'),
			'description' => '',
			'section' => 'cev_controls_section',
			'type' => 'text',
			'priority' => 1,
			'input_attrs' => array(
				'class' => '',
				'style' => '',
				'placeholder' => $this->defaults['cev_verification_email_subject'],
			),
		));

		// Email Heading
		$wp_customize->add_setting('cev_verification_email_heading', array(
			'default' => $this->defaults['cev_verification_email_heading'],
			'transport' => 'refresh',
			'type' => 'option',
			'sanitize_callback' => ''
		));
		$wp_customize->add_control('cev_verification_email_heading', array(
			'label' => __('Email Heading', 'customer-email-verification-for-woocommerce'),
			'description' => '',
			'section' => 'cev_controls_section',
			'type' => 'text',
			'priority' => 6,
			'input_attrs' => array(
				'class' => '',
				'style' => '',
				'placeholder' => $this->defaults['cev_verification_email_heading'],
			),
		));

		// Email Content
		$wp_customize->add_setting('cev_verification_email_body', array(
			'default' => $this->defaults['cev_verification_email_body'],
			'transport' => 'refresh',
			'type' => 'option',
			'sanitize_callback' => ''
		));
		$wp_customize->add_control('cev_verification_email_body', array(
			'label' => __('Verification Message', 'customer-email-verification-for-woocommerce'),
			'description' => '',
			'section' => 'cev_controls_section',
			'type' => 'textarea',
			'input_attrs' => array(
				'class' => '',
				'style' => '',
				'placeholder' => $this->defaults['cev_verification_email_body'],
			),
		));

		// Available Variables
		$wp_customize->add_setting('cev_email_code_block', array(
			'default' => '',
			'transport' => 'postMessage',
			'sanitize_callback' => ''
		));
		$wp_customize->add_control(new WP_Customize_cev_codeinfoblock_Control($wp_customize, 'cev_email_code_block', array(
			'label' => __('Available variables', 'customer-email-verification-for-woocommerce'),
			'description' => '<code>{site_title}<br>{cev_user_verification_pin}</code>, You can use HTML tag : <strong>, <i>',
			'section' => 'cev_controls_section',
		)));
	}

	/**
	 * Set up preview
	 */
	public function set_up_preview() {
		// Make sure this is own preview request
		if (!self::is_own_preview_request()) {
			return;
		}
		include woo_customer_email_verification()->get_plugin_path() . '/includes/customizer/preview/preview.php';
		exit;
	}

	/**
	 * Preview of verification email in customizer
	 */
	public function preview_account_email() {
		// Load WooCommerce emails
		$wc_emails = WC_Emails::instance();
		$emails = $wc_emails->get_emails();
		WC_customer_email_verification_email_Common()->wuev_user_id = 1;

		$email_heading = get_option('cev_verification_email_heading', $this->defaults['cev_verification_email_heading']);
		$email_heading = WC_customer_email_verification_email_Common()->maybe_parse_merge_tags($email_heading);

		$email_content = get_option('cev_verification_email_body', $this->defaults['cev_verification_email_body']);
		$email_content = WC_customer_email_verification_email_Common()->maybe_parse_merge_tags($email_content);
		$email_content = apply_filters('cev_verification_email_content', $email_content);
		$email_content = wpautop($email_content);
		$email_content = wp_kses_post($email_content);

		$mailer = WC()->mailer();
		$email = new WC_Email();
		$email->id = 'Customer_New_Account';

		// Wrap the content with the email template and add styles
		$message = apply_filters('woocommerce_mail_content', $email->style_inline($mailer->wrap_message($email_heading, $email_content)));
		$message = apply_filters('wc_cev_decode_html_content', $message);
		echo wp_kses_post($message);
	}
}

/**
 * Initialise our Customizer settings
 */
$cev_customizer_settings = new Cev_Initialise_Customizer_Settings();
