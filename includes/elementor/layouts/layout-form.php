<?php
/**
 * Form Layout Template
 * Simple form with single textarea for arrival details
 * Saves data to session and adds as order note on checkout
 * This layout does NOT use products - it's a standalone form
 *
 * Variables available:
 * @var array $settings Widget settings
 * @var string $widget_id Unique widget ID
 *
 * @package Aura_Custom_Product_Style
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get saved form data from session if exists
$saved_form_data = WC()->session ? WC()->session->get( 'aura_arrival_details', '' ) : '';
$is_confirmed = WC()->session ? WC()->session->get( 'aura_arrival_confirmed', false ) : false;
?>

<div class="aura-products-wrapper aura-form-wrapper" data-widget-id="<?php echo esc_attr( $widget_id ); ?>">

	<div class="aura-product-card aura-form-card <?php echo $is_confirmed ? 'confirmed' : ''; ?>">

		<!-- Form Fields -->
		<div class="aura-form-fields">
			<!-- Details Field -->
			<div class="aura-form-field">
				<label class="aura-form-label" for="aura-form-details-<?php echo esc_attr( $widget_id ); ?>">
					<?php esc_html_e( 'Details about your arrival', 'aura-custom-product-style' ); ?>
				</label>
				<textarea id="aura-form-details-<?php echo esc_attr( $widget_id ); ?>"
						  class="aura-form-textarea aura-form-details"
						  placeholder="<?php esc_attr_e( 'Time, Location, Flight Number, etc.', 'aura-custom-product-style' ); ?>"
						  rows="6"
						  <?php echo $is_confirmed ? 'disabled' : ''; ?>><?php echo esc_textarea( $saved_form_data ); ?></textarea>
			</div>
		</div>

		<!-- Confirm Button -->
		<div class="aura-form-footer">
			<div class="aura-form-confirm-wrapper">
				<button class="aura-form-checkbox-btn"
						role="checkbox"
						aria-checked="<?php echo $is_confirmed ? 'true' : 'false'; ?>"
						aria-label="<?php esc_attr_e( 'Confirm details', 'aura-custom-product-style' ); ?>"
						disabled>
					<span class="aura-form-checkmark">✓</span>
				</button>
				<span class="aura-form-confirm-text">
					<?php esc_html_e( 'Confirm Details', 'aura-custom-product-style' ); ?>
				</span>
			</div>
		</div>

	</div><!-- .aura-form-card -->

</div><!-- .aura-products-wrapper -->
