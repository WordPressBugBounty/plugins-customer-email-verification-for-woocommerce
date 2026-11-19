<?php
// This template is include()d from inside a class method, so the variables
// below are function-scoped, not globals. PHPCS analyses the file in isolation
// and cannot see that, hence the false positives.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
/**
 * Black Friday CEV PRO banner (Settings + Unverified Users tabs).
 *
 * Left: dark "Black Friday 40% off CEV PRO" block. Middle: headline and the
 * offer line (coupon, price, terms, end date). Right: the upgrade CTA.
 * Not dismissible. The caller only includes it while the sale is running,
 * so it disappears on its own after the sale ends.
 *
 * Expects from the including scope:
 *   $cev_offer_visible (bool) Whether the current tab shows the banner.
 *
 * @package Customer_Email_Verification_For_WooCommerce
 * @since 2.8.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cev_offer_url = WC_CEV_Admin_Notices_Under_WC_Admin::get_bfcm_url( 'bfcm-settings-banner' );
$cev_offer_pct = rtrim( WC_CEV_Admin_Notices_Under_WC_Admin::BFCM_DISCOUNT, '%' );
?>
<section class="cev-offer cev-offer--bfcm" id="cev-offer-banner" aria-labelledby="cev-offer-title" <?php echo $cev_offer_visible ? '' : 'hidden'; ?>>
	<div class="cev-offer__badge">
		<span class="cev-offer__save"><?php esc_html_e( 'Black Friday', 'customer-email-verification-for-woocommerce' ); ?></span>
		<span class="cev-offer__pct"><?php echo esc_html( $cev_offer_pct ); ?><small>%</small></span>
		<span class="cev-offer__on"><?php esc_html_e( 'off CEV PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
	</div>

	<div class="cev-offer__body">
		<h3 class="cev-offer__title" id="cev-offer-title"><?php echo esc_html( WC_CEV_Admin_Notices_Under_WC_Admin::get_bfcm_title() ); ?></h3>
		<?php WC_CEV_Admin_Notices_Under_WC_Admin::render_bfcm_meta(); ?>
	</div>

	<div class="cev-offer__action">
		<a class="zui-btn-primary cev-offer__cta" href="<?php echo esc_url( $cev_offer_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( WC_CEV_Admin_Notices_Under_WC_Admin::get_bfcm_button_label() ); ?></a>
	</div>
</section>
