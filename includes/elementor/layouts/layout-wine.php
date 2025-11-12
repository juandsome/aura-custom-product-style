<?php
/**
 * Wine Layout Template
 * 2-column layout: Image left (20%), Content right (80%)
 * Content: Row 1 (text_before_title + title, color circle), Row 2 (description), Row 3 (+/- buttons, price)
 *
 * @package Aura_Custom_Product_Style
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_id       = isset( $product ) ? $product->get_id() : 0;
$product_name     = isset( $product ) ? $product->get_name() : '';
$product_price    = isset( $product ) ? (float) $product->get_price() : 0;
$product_desc     = isset( $product ) ? $product->get_short_description() : '';
$product_image_id = isset( $product ) ? $product->get_image_id() : 0;
$product_image_url = $product_image_id ? wp_get_attachment_image_url( $product_image_id, 'large' ) : wc_placeholder_img_src();

$currency = get_woocommerce_currency_symbol();

// Get product meta fields
$text_before_title = get_post_meta( $product_id, 'text_before_title', true );
$product_color = get_post_meta( $product_id, 'color', true );

// Get current quantity in cart
$cart_item_key = '';
$quantity_in_cart = 0;

if ( ! empty( WC()->cart ) ) {
	foreach ( WC()->cart->get_cart() as $key => $item ) {
		if ( $item['product_id'] === $product_id ) {
			$cart_item_key = $key;
			$quantity_in_cart = $item['quantity'];
			break;
		}
	}
}
?>

<div class="aura-product-card aura-wine-card"
	 data-product-id="<?php echo esc_attr( $product_id ); ?>"
	 data-product-price="<?php echo esc_attr( $product_price ); ?>"
	 data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>">

	<!-- Image Column (20%) -->
	<div class="aura-product-image"
		 style="background-image: url('<?php echo esc_url( $product_image_url ); ?>');"
		 role="img"
		 aria-label="<?php echo esc_attr( $product_name ); ?>">
	</div>

	<!-- Content Column (80%) -->
	<div class="aura-product-content">

		<!-- Row 1: Title section + Color circle -->
		<div class="aura-wine-header">
			<div class="aura-wine-title-section">
				<?php if ( ! empty( $text_before_title ) ) : ?>
					<span class="aura-wine-text-before"><?php echo esc_html( $text_before_title ); ?></span>
				<?php endif; ?>
				<h3 class="aura-product-title">
					<?php echo esc_html( $product_name ); ?>
				</h3>
			</div>

			<?php if ( ! empty( $product_color ) ) : ?>
				<div class="aura-wine-color-circle" style="background-color: <?php echo esc_attr( $product_color ); ?>;" aria-label="<?php echo esc_attr__( 'Product color', 'aura-custom-product-style' ); ?>"></div>
			<?php endif; ?>
		</div>

		<!-- Row 2: Description -->
		<?php if ( ! empty( $product_desc ) ) : ?>
			<div class="aura-product-description">
				<?php echo wp_kses_post( $product_desc ); ?>
			</div>
		<?php endif; ?>

		<!-- Row 3: Quantity controls + Price -->
		<div class="aura-wine-footer">
			<div class="aura-quantity-controls">
				<button class="aura-qty-btn aura-qty-minus"
						data-product-id="<?php echo esc_attr( $product_id ); ?>"
						data-action="decrease"
						aria-label="<?php echo esc_attr__( 'Decrease quantity', 'aura-custom-product-style' ); ?>">
					<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M5 10H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</button>

				<span class="aura-quantity-display"><?php echo esc_html( $quantity_in_cart ); ?></span>

				<button class="aura-qty-btn aura-qty-plus"
						data-product-id="<?php echo esc_attr( $product_id ); ?>"
						data-action="increase"
						aria-label="<?php echo esc_attr__( 'Increase quantity', 'aura-custom-product-style' ); ?>">
					<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M10 5V15M5 10H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</button>
			</div>

			<div class="aura-wine-price">
				<strong><?php echo esc_html( number_format( $product_price, 2 ) ); ?> <?php echo esc_html( $currency ); ?></strong>
			</div>
		</div>

	</div>
</div>
