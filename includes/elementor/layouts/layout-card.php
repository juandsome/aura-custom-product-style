<?php
/**
 * Card Layout Template
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

if ( $columns_mode === 'fixed' && ! empty( $settings['columns_count'] ) ) {
	$columns_count = intval( $settings['columns_count'] );
} elseif ( $columns_mode === 'auto' && ! empty( $settings['card_width']['size'] ) ) {
	$card_width = intval( $settings['card_width']['size'] );
}

$rows_visible = ! empty( $settings['rows_visible'] ) ? intval( $settings['rows_visible'] ) : 2;
$visible_items = $rows_visible * $columns_count;

// Get cart quantities for initial state
$cart_quantities = aura_cps_get_all_cart_quantities();
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
			$product_image_url = aura_cps_get_product_image_url( $product, 'medium' );
			$initial_quantity = isset( $cart_quantities[ $product_id ] ) ? $cart_quantities[ $product_id ] : 0;
			$is_hidden = ( $index >= $visible_items ) ? 'hidden-item' : '';
		?>

		<div class="aura-product-card <?php echo esc_attr( $is_hidden ); ?>"
			 data-product-id="<?php echo esc_attr( $product_id ); ?>"
			 data-product-price="<?php echo esc_attr( $product_price ); ?>">

			<!-- Left Column: Image (30%) -->
			<div class="aura-product-image">
				<img src="<?php echo esc_url( $product_image_url ); ?>"
					 alt="<?php echo esc_attr( $product_name ); ?>"
					 loading="lazy" />
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

				<!-- Row 2: Short Description -->
				<?php if ( $product_description ) : ?>
				<div class="aura-product-description">
					<?php echo wp_kses_post( $product_description ); ?>
				</div>
				<?php endif; ?>

				<!-- Row 3: Price per unit -->
				<div class="aura-product-price-row">
					<span class="aura-price">
						<strong><?php echo esc_html( $product_price ); ?> <?php echo esc_html( get_woocommerce_currency_symbol() ); ?></strong>
						<?php
						$price_unit_text = ! empty( $settings['price_unit_text'] ) ? $settings['price_unit_text'] : 'each';
						echo esc_html( $price_unit_text );
						?>
					</span>
				</div>

				<!-- Row 4: Total -->
				<div class="aura-product-total-row">
					<span class="aura-total-label">
						<?php
						$total_label_text = ! empty( $settings['total_label_text'] ) ? $settings['total_label_text'] : 'Total';
						echo esc_html( $total_label_text );
						?>
					</span>
					<span class="aura-total-price" data-product-id="<?php echo esc_attr( $product_id ); ?>">
						<?php
						$initial_total = $initial_quantity * floatval( $product_price );
						echo esc_html( number_format( $initial_total, 2 ) );
						?> <?php echo esc_html( get_woocommerce_currency_symbol() ); ?>
					</span>
				</div>

			</div><!-- .aura-product-info -->

		</div><!-- .aura-product-card -->

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
