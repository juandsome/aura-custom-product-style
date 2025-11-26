<?php
/**
 * Form Layout Template
 * Layout with single textarea for arrival details
 * Saves data to session and adds as order note on checkout
 *
 * Variables available:
 * @var array $products Array of WC_Product objects (not used for actual products, just for display cards)
 * @var array $settings Widget settings
 * @var string $widget_id Unique widget ID
 *
 * @package Aura_Custom_Product_Style
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Calculate columns based on mode
$columns_mode = ! empty( $settings['columns_mode'] ) ? $settings['columns_mode'] : 'auto';
$columns_count = 2; // Default
$card_width = 350; // Default
$auto_columns_count = 0; // Default

if ( $columns_mode === 'fixed' && ! empty( $settings['columns_count'] ) ) {
	$columns_count = intval( $settings['columns_count'] );
} elseif ( $columns_mode === 'auto' ) {
	// Check if auto_columns_count is set
	if ( ! empty( $settings['auto_columns_count'] ) && intval( $settings['auto_columns_count'] ) > 0 ) {
		$auto_columns_count = intval( $settings['auto_columns_count'] );
		$columns_count = $auto_columns_count;
		$card_width = 0; // Use flex distribution
	} elseif ( ! empty( $settings['card_width']['size'] ) ) {
		$card_width = intval( $settings['card_width']['size'] );
		// If card_width is 0, use flex distribution
		if ( $card_width === 0 ) {
			$columns_count = 2; // Default for flex distribution
		}
	}
}

$rows_visible = ! empty( $settings['rows_visible'] ) ? intval( $settings['rows_visible'] ) : 2;
$visible_items = $rows_visible * $columns_count;

// Get saved form data from session if exists
$saved_form_data = WC()->session ? WC()->session->get( 'aura_arrival_details', '' ) : '';
$is_confirmed = WC()->session ? WC()->session->get( 'aura_arrival_confirmed', false ) : false;
?>

<div class="aura-products-wrapper" data-widget-id="<?php echo esc_attr( $widget_id ); ?>">
	<div class="aura-products-grid"
		 data-columns-mode="<?php echo esc_attr( $columns_mode ); ?>"
		 data-columns-count="<?php echo esc_attr( $columns_count ); ?>"
		 data-card-width="<?php echo esc_attr( $card_width ); ?>"
		 data-rows-visible="<?php echo esc_attr( $rows_visible ); ?>">

		<?php foreach ( $products as $index => $product ) :
			$product_id = $product->get_id();
			$product_name = $product->get_name();
			$product_description = $product->get_short_description();
			$is_hidden = ( $index >= $visible_items ) ? 'hidden-item' : '';
		?>

		<div class="aura-product-card aura-form-card <?php echo esc_attr( $is_hidden ); ?> <?php echo $is_confirmed ? 'confirmed' : ''; ?>"
			 data-product-id="<?php echo esc_attr( $product_id ); ?>">

			<!-- Title -->
			<h3 class="aura-product-title">
				<?php echo esc_html( $product_name ); ?>
			</h3>

			<!-- Description -->
			<?php if ( ! empty( $product_description ) ) : ?>
				<div class="aura-form-description">
					<?php echo wp_kses_post( $product_description ); ?>
				</div>
			<?php endif; ?>

			<!-- Form Fields -->
			<div class="aura-form-fields">
				<!-- Details Field -->
				<div class="aura-form-field">
					<label class="aura-form-label" for="aura-form-details-<?php echo esc_attr( $product_id ); ?>">
						<?php esc_html_e( 'Details about your arrival', 'aura-custom-product-style' ); ?>
					</label>
					<textarea id="aura-form-details-<?php echo esc_attr( $product_id ); ?>"
							  class="aura-form-textarea aura-form-details"
							  data-product-id="<?php echo esc_attr( $product_id ); ?>"
							  placeholder="<?php esc_attr_e( 'Time, Location, Flight Number, etc.', 'aura-custom-product-style' ); ?>"
							  rows="6"
							  <?php echo $is_confirmed ? 'disabled' : ''; ?>><?php echo esc_textarea( $saved_form_data ); ?></textarea>
				</div>
			</div>

			<!-- Confirm Button -->
			<div class="aura-form-footer">
				<div class="aura-form-confirm-wrapper">
					<button class="aura-form-checkbox-btn"
							data-product-id="<?php echo esc_attr( $product_id ); ?>"
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

		<?php endforeach; ?>

	</div><!-- .aura-products-grid -->

	<!-- Show More/Less Button -->
	<?php if ( count( $products ) > $visible_items ) : ?>
		<div class="aura-show-more-wrapper">
			<button class="aura-show-more-btn"
					data-show-more-text="<?php echo esc_attr( $settings['show_more_text'] ); ?>"
					data-show-less-text="<?php echo esc_attr( $settings['show_less_text'] ); ?>">
				<span class="aura-btn-text"><?php echo esc_html( $settings['show_more_text'] ); ?></span>
				<span class="aura-chevron">▼</span>
			</button>
		</div>
	<?php endif; ?>

</div><!-- .aura-products-wrapper -->
