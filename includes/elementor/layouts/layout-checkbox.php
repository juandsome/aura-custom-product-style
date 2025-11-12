<?php
/**
 * Checkbox Layout Template
 * 2-column layout: Image left (30%), Content right (70%)
 * Content: Title top, Icon + Short description (vertically centered), Checkbox + Price bottom
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

// Get cart quantities for initial state
$cart_quantities = aura_cps_get_all_cart_quantities();

// Get icon from settings (Elementor icon or uploaded image)
$icon_type = isset( $settings['icon_type'] ) ? $settings['icon_type'] : 'icon';
$icon_library = isset( $settings['product_icon'] ) ? $settings['product_icon'] : array();
$icon_image = isset( $settings['icon_image']['url'] ) ? $settings['icon_image']['url'] : '';
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
			$product_price = $product->get_price();
			$product_description = $product->get_short_description();
			$product_image_id = $product->get_image_id();
			$product_image_url = $product_image_id ? wp_get_attachment_image_url( $product_image_id, 'large' ) : wc_placeholder_img_src();
			$initial_quantity = isset( $cart_quantities[ $product_id ] ) ? $cart_quantities[ $product_id ] : 0;
			$is_hidden = ( $index >= $visible_items ) ? 'hidden-item' : '';
			$is_in_cart = $initial_quantity > 0;
		?>

		<div class="aura-product-card aura-checkbox-card <?php echo esc_attr( $is_hidden ); ?>"
			 data-product-id="<?php echo esc_attr( $product_id ); ?>"
			 data-product-price="<?php echo esc_attr( $product_price ); ?>">

			<!-- Image Column (30%) -->
			<div class="aura-product-image"
				 style="background-image: url('<?php echo esc_url( $product_image_url ); ?>');"
				 role="img"
				 aria-label="<?php echo esc_attr( $product_name ); ?>">
			</div>

			<!-- Content Column (70%) -->
			<div class="aura-product-content">

				<!-- Header Row: Title + Icon -->
				<div class="aura-product-header">
					<h3 class="aura-product-title">
						<?php echo esc_html( $product_name ); ?>
					</h3>

					<?php if ( 'icon' === $icon_type && ! empty( $icon_library['value'] ) ) : ?>
						<div class="aura-product-icon">
							<?php \Elementor\Icons_Manager::render_icon( $icon_library, array( 'aria-hidden' => 'true' ) ); ?>
						</div>
					<?php elseif ( 'image' === $icon_type && ! empty( $icon_image ) ) : ?>
						<div class="aura-product-icon">
							<img src="<?php echo esc_url( $icon_image ); ?>" alt="<?php echo esc_attr( $product_name ); ?>">
						</div>
					<?php endif; ?>
				</div>

				<!-- Description (vertically centered) -->
				<div class="aura-product-middle">
					<?php if ( ! empty( $product_description ) ) : ?>
						<div class="aura-product-description">
							<?php echo wp_kses_post( $product_description ); ?>
						</div>
					<?php endif; ?>
				</div>

				<!-- Checkbox + Price (bottom-aligned) -->
				<div class="aura-product-footer">
					<div class="aura-checkbox-wrapper">
						<button class="aura-checkbox-btn"
								data-product-id="<?php echo esc_attr( $product_id ); ?>"
								aria-label="<?php echo esc_attr__( 'Add to cart', 'aura-custom-product-style' ); ?>"
								role="checkbox"
								aria-checked="<?php echo $is_in_cart ? 'true' : 'false'; ?>">
							<svg class="aura-checkbox-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2" fill="none"/>
								<path class="aura-checkmark" d="M7 12L10.5 15.5L17 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="<?php echo $is_in_cart ? '1' : '0'; ?>"/>
							</svg>
						</button>
						<span class="aura-checkbox-label"><?php echo esc_html__( 'Add to cart', 'aura-custom-product-style' ); ?></span>
					</div>

					<div class="aura-product-price">
						<strong><?php echo esc_html( number_format( floatval( $product_price ), 2 ) ); ?> <?php echo esc_html( get_woocommerce_currency_symbol() ); ?></strong>
					</div>
				</div>

			</div><!-- .aura-product-content -->

		</div><!-- .aura-checkbox-card -->

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
