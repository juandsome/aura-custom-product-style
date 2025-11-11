<?php
/**
 * Checkbox Layout Template
 * 2-column layout: Image left (30%), Content right (70%)
 * Content: Title top, Icon + Short description (vertically centered), Checkbox + Price bottom
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
$product_image_url = $product_image_id ? wp_get_attachment_image_url( $product_image_id, 'medium' ) : wc_placeholder_img_src();

$currency = get_woocommerce_currency_symbol();

// Get icon from settings (Elementor icon or uploaded image)
$icon_type = isset( $settings['icon_type'] ) ? $settings['icon_type'] : 'icon';
$icon_library = isset( $settings['product_icon'] ) ? $settings['product_icon'] : array();
$icon_image = isset( $settings['icon_image']['url'] ) ? $settings['icon_image']['url'] : '';
?>

<div class="aura-product-card aura-checkbox-card"
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
			<?php if ( ! empty( $product_desc ) ) : ?>
				<div class="aura-product-description">
					<?php echo wp_kses_post( $product_desc ); ?>
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
						aria-checked="false">
					<svg class="aura-checkbox-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2" fill="none"/>
						<path class="aura-checkmark" d="M7 12L10.5 15.5L17 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0"/>
					</svg>
				</button>
				<span class="aura-checkbox-label"><?php echo esc_html__( 'Add to cart', 'aura-custom-product-style' ); ?></span>
			</div>

			<div class="aura-product-price">
				<strong><?php echo esc_html( number_format( $product_price, 2 ) ); ?> <?php echo esc_html( $currency ); ?></strong>
			</div>
		</div>

	</div>
</div>
