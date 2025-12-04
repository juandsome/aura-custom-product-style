<?php
/**
 * Equipment Rental Layout Template
 * Similar to Card layout but with date range selector instead of description
 *
 * Variables available:
 * @var array $products Array of WC_Product objects
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
$card_width = 350; // Default
$auto_columns_count = 0; // Default

// Responsive columns for fixed mode
$columns_count = 2; // Default desktop
$columns_count_tablet = 2; // Default tablet
$columns_count_mobile = 1; // Default mobile

if ( $columns_mode === 'fixed' ) {
	$columns_count = ! empty( $settings['columns_count'] ) ? intval( $settings['columns_count'] ) : 2;
	$columns_count_tablet = ! empty( $settings['columns_count_tablet'] ) ? intval( $settings['columns_count_tablet'] ) : 2;
	$columns_count_mobile = ! empty( $settings['columns_count_mobile'] ) ? intval( $settings['columns_count_mobile'] ) : 1;
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

// Responsive rows visible
$rows_visible = ! empty( $settings['rows_visible'] ) ? intval( $settings['rows_visible'] ) : 2;
$rows_visible_tablet = ! empty( $settings['rows_visible_tablet'] ) ? intval( $settings['rows_visible_tablet'] ) : 2;
$rows_visible_mobile = ! empty( $settings['rows_visible_mobile'] ) ? intval( $settings['rows_visible_mobile'] ) : 3;

$visible_items = $rows_visible * $columns_count;

// Get cart quantities and rental dates for initial state
$cart_data = aura_cps_get_all_cart_quantities_with_dates();

// Get booking dates from villa (similar to car rental)
$booking_dates = array();
if ( function_exists( 'aura_get_villa_booking_dates' ) ) {
	$booking_dates = aura_get_villa_booking_dates();
}

$start_date = $booking_dates['start'] ?? '';
$end_date = $booking_dates['end'] ?? '';
?>

<div class="aura-products-wrapper aura-equipment-wrapper" data-widget-id="<?php echo esc_attr( $widget_id ); ?>">
	<div class="aura-products-grid"
		 data-columns-mode="<?php echo esc_attr( $columns_mode ); ?>"
		 data-columns-count="<?php echo esc_attr( $columns_count ); ?>"
		 data-columns-count-tablet="<?php echo esc_attr( $columns_count_tablet ); ?>"
		 data-columns-count-mobile="<?php echo esc_attr( $columns_count_mobile ); ?>"
		 data-card-width="<?php echo esc_attr( $card_width ); ?>"
		 data-rows-visible="<?php echo esc_attr( $rows_visible ); ?>"
		 data-rows-visible-tablet="<?php echo esc_attr( $rows_visible_tablet ); ?>"
		 data-rows-visible-mobile="<?php echo esc_attr( $rows_visible_mobile ); ?>"
		 data-start-date="<?php echo esc_attr( $start_date ); ?>"
		 data-end-date="<?php echo esc_attr( $end_date ); ?>">

		<?php foreach ( $products as $index => $product ) :
			$product_id = $product->get_id();
			$product_name = $product->get_name();
			$product_price = $product->get_price();
			$product_image_url = aura_cps_get_product_image_url( $product, 'large' );
			$initial_quantity = isset( $cart_data[ $product_id ]['quantity'] ) ? $cart_data[ $product_id ]['quantity'] : 0;
			$rental_start = isset( $cart_data[ $product_id ]['rental_start'] ) ? $cart_data[ $product_id ]['rental_start'] : '';
			$rental_end = isset( $cart_data[ $product_id ]['rental_end'] ) ? $cart_data[ $product_id ]['rental_end'] : '';
			$is_hidden = ( $index >= $visible_items ) ? 'hidden-item' : '';

			// Calculate initial days and total
			$initial_days = 1;
			if ( $rental_start && $rental_end ) {
				$start_dt = new DateTime( $rental_start );
				$end_dt = new DateTime( $rental_end );
				$diff = $start_dt->diff( $end_dt );
				$initial_days = $diff->days + 1; // Include both start and end dates
			}
			$initial_total = $initial_quantity * floatval( $product_price ) * $initial_days;
		?>

		<div class="aura-product-card aura-equipment-card <?php echo esc_attr( $is_hidden ); ?>"
			 data-product-id="<?php echo esc_attr( $product_id ); ?>"
			 data-product-price="<?php echo esc_attr( $product_price ); ?>"
			 data-rental-days="<?php echo esc_attr( $initial_days ); ?>">

			<!-- Left Column: Image (30%) -->
			<div class="aura-product-image"
				 style="background-image: url('<?php echo esc_url( $product_image_url ); ?>');"
				 role="img"
				 aria-label="<?php echo esc_attr( $product_name ); ?>">
			</div>

			<!-- Right Column: Product Info (70%) -->
			<div class="aura-product-info">

				<!-- Row 1: Title + Quantity Controls -->
				<div class="aura-product-header">
					<h3 class="aura-product-title"><?php echo esc_html( $product_name ); ?></h3>

					<div class="aura-product-controls">
						<button class="aura-btn aura-btn-minus"
								data-product-id="<?php echo esc_attr( $product_id ); ?>"
								aria-label="<?php esc_attr_e( 'Decrease quantity', 'aura-custom-product-style' ); ?>">
							<img src="https://collectionaura.com/wp-content/uploads/2025/11/minus.svg"
								 alt="<?php esc_attr_e( 'Minus', 'aura-custom-product-style' ); ?>" />
						</button>

						<span class="aura-quantity-display"
							  data-product-id="<?php echo esc_attr( $product_id ); ?>">
							<?php echo esc_html( $initial_quantity ); ?>
						</span>

						<button class="aura-btn aura-btn-plus"
								data-product-id="<?php echo esc_attr( $product_id ); ?>"
								aria-label="<?php esc_attr_e( 'Increase quantity', 'aura-custom-product-style' ); ?>">
							<img src="https://collectionaura.com/wp-content/uploads/2025/11/plus.svg"
								 alt="<?php esc_attr_e( 'Plus', 'aura-custom-product-style' ); ?>" />
						</button>
					</div>
				</div>

				<!-- Row 2: Date Selector (left) + Price per day (right) -->
				<div class="aura-equipment-date-price-row">
					<div class="aura-equipment-date-section">
						<label class="aura-equipment-date-label">
							<?php esc_html_e( 'For how many days?', 'aura-custom-product-style' ); ?>
						</label>
						<input type="text"
							   class="aura-equipment-date-range"
							   data-product-id="<?php echo esc_attr( $product_id ); ?>"
							   placeholder="<?php esc_attr_e( 'Select days', 'aura-custom-product-style' ); ?>"
							   <?php if ( $rental_start && $rental_end ) : ?>
							   value="<?php echo esc_attr( $rental_start . ' to ' . $rental_end ); ?>"
							   <?php endif; ?>
							   readonly />
					</div>
					<span class="aura-equipment-unit-price">
						<strong><?php echo esc_html( $product_price ); ?> <?php echo esc_html( get_woocommerce_currency_symbol() ); ?></strong>
						<?php esc_html_e( 'per day', 'aura-custom-product-style' ); ?>
					</span>
				</div>

				<!-- Row 4: Total (Quantity × Price × Days) -->
				<div class="aura-product-total-row">
					<span class="aura-total-label">
						<?php
						$total_label_text = ! empty( $settings['total_label_text'] ) ? $settings['total_label_text'] : 'Total';
						echo esc_html( $total_label_text );
						?>
					</span>
					<span class="aura-total-price" data-product-id="<?php echo esc_attr( $product_id ); ?>">
						<?php echo esc_html( number_format( $initial_total, 2 ) ); ?> <?php echo esc_html( get_woocommerce_currency_symbol() ); ?>
					</span>
				</div>

				<!-- Row 5: Confirm Rent Checkbox -->
				<div class="aura-equipment-confirm-row">
					<div class="aura-equipment-confirm-wrapper">
						<button type="button"
								class="aura-equipment-confirm-btn"
								data-product-id="<?php echo esc_attr( $product_id ); ?>"
								aria-checked="<?php echo $initial_quantity > 0 ? 'true' : 'false'; ?>"
								aria-label="<?php esc_attr_e( 'Confirm rental', 'aura-custom-product-style' ); ?>">
							<span class="aura-equipment-checkmark">✓</span>
						</button>
						<span class="aura-equipment-confirm-text">
							<?php esc_html_e( 'Confirm Rent', 'aura-custom-product-style' ); ?>
						</span>
					</div>
				</div>

			</div><!-- .aura-product-info -->

		</div><!-- .aura-equipment-card -->

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

</div><!-- .aura-equipment-wrapper -->
