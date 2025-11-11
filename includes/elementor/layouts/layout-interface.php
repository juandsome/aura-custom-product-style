<?php
/**
 * Layout Interface Documentation
 *
 * This file documents the structure and requirements for creating new layouts
 * for the Aura Custom Product Style plugin.
 *
 * @package Aura_Custom_Product_Style
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ========================================
 * LAYOUT TEMPLATE INTERFACE
 * ========================================
 *
 * All layout templates must follow this interface to ensure compatibility
 * with the widget JavaScript and styling system.
 *
 * ========================================
 * FILE NAMING CONVENTION
 * ========================================
 *
 * Layout files must be named: layout-{type}.php
 * Where {type} is the layout identifier (e.g., card, list, grid, carousel)
 *
 * Example:
 * - layout-card.php
 * - layout-list.php
 * - layout-grid.php
 *
 * ========================================
 * AVAILABLE VARIABLES
 * ========================================
 *
 * All layout templates receive these variables:
 *
 * @var array $products       - Array of WC_Product objects to display
 * @var array $settings       - Widget settings from Elementor
 * @var string $widget_id     - Unique widget instance ID
 *
 * ========================================
 * SETTINGS ARRAY STRUCTURE
 * ========================================
 *
 * The $settings array contains all widget configuration:
 *
 * Layout Settings:
 * - $settings['layout_type']        (string) Layout type identifier
 *
 * Display Settings:
 * - $settings['columns_mode']       (string) 'auto' or 'fixed'
 * - $settings['columns_count']      (int)    Number of columns (if fixed mode)
 * - $settings['card_width']         (array)  ['size' => int, 'unit' => 'px'] (if auto mode)
 * - $settings['rows_visible']       (int)    Number of rows before show more
 * - $settings['gap']                (array)  ['size' => int, 'unit' => 'px']
 *
 * Show More Settings:
 * - $settings['show_more_text']     (string) Show more button text
 * - $settings['show_less_text']     (string) Show less button text
 *
 * Filters:
 * - $settings['product_category']   (array)  Selected category slugs
 * - $settings['product_ids']        (string) Comma-separated product IDs
 * - $settings['filter_logic']       (string) 'and' or 'or'
 * - $settings['max_products']       (int)    Maximum products to display
 *
 * ========================================
 * REQUIRED HTML STRUCTURE
 * ========================================
 *
 * All layouts MUST include these elements with specific classes:
 *
 * 1. WRAPPER CONTAINER
 *    <div class="aura-products-wrapper" data-widget-id="<?php echo esc_attr( $widget_id ); ?>">
 *
 * 2. GRID CONTAINER
 *    <div class="aura-products-grid"
 *         data-columns-mode="<?php echo esc_attr( $columns_mode ); ?>"
 *         data-columns-count="<?php echo esc_attr( $columns_count ); ?>"
 *         data-card-width="<?php echo esc_attr( $card_width ); ?>"
 *         data-rows-visible="<?php echo esc_attr( $rows_visible ); ?>">
 *
 * 3. PRODUCT CARD (for each product)
 *    <div class="aura-product-card [hidden-item]"
 *         data-product-id="<?php echo esc_attr( $product_id ); ?>"
 *         data-product-price="<?php echo esc_attr( $product_price ); ?>">
 *
 * 4. QUANTITY CONTROLS
 *    <button class="aura-btn aura-btn-minus" data-product-id="<?php echo esc_attr( $product_id ); ?>">
 *    <span class="aura-quantity-display" data-product-id="<?php echo esc_attr( $product_id ); ?>">
 *    <button class="aura-btn aura-btn-plus" data-product-id="<?php echo esc_attr( $product_id ); ?>">
 *
 * 5. TOTAL PRICE DISPLAY
 *    <span class="aura-total-price" data-product-id="<?php echo esc_attr( $product_id ); ?>">
 *
 * 6. SHOW MORE BUTTON (if products > visible items)
 *    <div class="aura-show-more-wrapper">
 *        <button class="aura-show-more-btn"
 *                data-show-more-text="<?php echo esc_attr( $settings['show_more_text'] ); ?>"
 *                data-show-less-text="<?php echo esc_attr( $settings['show_less_text'] ); ?>">
 *            <span class="aura-btn-text">Show More</span>
 *            <span class="aura-chevron">▼</span>
 *        </button>
 *    </div>
 *
 * ========================================
 * REQUIRED CSS CLASSES
 * ========================================
 *
 * JavaScript functionality depends on these classes:
 *
 * Container Classes:
 * - .aura-products-wrapper      Main wrapper
 * - .aura-products-grid         Grid container
 * - .aura-product-card          Individual product card
 * - .hidden-item                Items hidden by "show more" feature
 *
 * Product Info Classes:
 * - .aura-product-title         Product title
 * - .aura-product-description   Product short description
 * - .aura-product-price-row     Price per unit row
 * - .aura-product-total-row     Total price row
 *
 * Interactive Elements:
 * - .aura-btn                   Base button class
 * - .aura-btn-minus             Decrease quantity button
 * - .aura-btn-plus              Increase quantity button
 * - .aura-quantity-display      Current quantity display
 * - .aura-total-price           Total price display
 *
 * Show More Elements:
 * - .aura-show-more-wrapper     Show more button wrapper
 * - .aura-show-more-btn         Show more/less button
 * - .aura-btn-text              Button text span
 * - .aura-chevron               Chevron icon span
 *
 * State Classes (applied by JavaScript):
 * - .loading                    Applied during AJAX operations
 * - .expanded                   Applied to grid when showing all items
 *
 * ========================================
 * DATA ATTRIBUTES
 * ========================================
 *
 * Required data attributes for JavaScript:
 *
 * On .aura-products-wrapper:
 * - data-widget-id              Unique widget instance ID
 *
 * On .aura-products-grid:
 * - data-columns-mode           'auto' or 'fixed'
 * - data-columns-count          Number of columns
 * - data-card-width             Card width in pixels (auto mode)
 * - data-rows-visible           Number of visible rows
 *
 * On .aura-product-card:
 * - data-product-id             WooCommerce product ID
 * - data-product-price          Product price (numeric)
 *
 * On buttons and displays:
 * - data-product-id             Product ID for cart operations
 *
 * On .aura-show-more-btn:
 * - data-show-more-text         Text for "show more" state
 * - data-show-less-text         Text for "show less" state
 *
 * ========================================
 * HELPER FUNCTIONS AVAILABLE
 * ========================================
 *
 * Use these helper functions in your layout:
 *
 * Product Data:
 * - aura_cps_get_product( $product_id )
 *   Returns WC_Product object or false
 *
 * - aura_cps_get_product_price( $product )
 *   Returns formatted product price
 *
 * - aura_cps_get_product_image_url( $product, $size )
 *   Returns product image URL
 *
 * Cart Functions:
 * - aura_cps_get_all_cart_quantities()
 *   Returns array of product_id => quantity
 *
 * - aura_cps_get_product_quantity_in_cart( $product_id )
 *   Returns quantity of specific product in cart
 *
 * ========================================
 * ACCESSIBILITY REQUIREMENTS
 * ========================================
 *
 * Ensure your layout includes:
 *
 * 1. Proper ARIA labels on buttons:
 *    aria-label="<?php esc_attr_e( 'Increase quantity', 'aura-custom-product-style' ); ?>"
 *
 * 2. Alt text on images:
 *    alt="<?php echo esc_attr( $product_name ); ?>"
 *
 * 3. Semantic HTML (h3 for titles, buttons for actions)
 *
 * 4. Loading states for screen readers (handled by JavaScript)
 *
 * ========================================
 * RESPONSIVE DESIGN
 * ========================================
 *
 * Layouts should be responsive:
 *
 * - In 'auto' mode, JavaScript calculates columns based on card width
 * - In 'fixed' mode, use CSS to adjust columns on smaller screens
 * - Consider mobile-first approach with media queries
 *
 * ========================================
 * SECURITY BEST PRACTICES
 * ========================================
 *
 * Always escape output:
 *
 * - esc_html()       For text content
 * - esc_attr()       For HTML attributes
 * - esc_url()        For URLs
 * - wp_kses_post()   For HTML content (descriptions)
 *
 * Never output raw user input or settings without escaping!
 *
 * ========================================
 * EXAMPLE LAYOUT STRUCTURE
 * ========================================
 *
 * <?php
 * // Calculate display settings
 * $columns_mode = $settings['columns_mode'];
 * $visible_items = $rows_visible * $columns_count;
 * $cart_quantities = aura_cps_get_all_cart_quantities();
 * ?>
 *
 * <div class="aura-products-wrapper" data-widget-id="<?php echo esc_attr( $widget_id ); ?>">
 *     <div class="aura-products-grid" data-columns-mode="<?php echo esc_attr( $columns_mode ); ?>">
 *
 *         <?php foreach ( $products as $index => $product ) :
 *             $product_id = $product->get_id();
 *             $is_hidden = ( $index >= $visible_items ) ? 'hidden-item' : '';
 *         ?>
 *
 *         <div class="aura-product-card <?php echo esc_attr( $is_hidden ); ?>"
 *              data-product-id="<?php echo esc_attr( $product_id ); ?>">
 *
 *             <!-- Your custom layout structure here -->
 *
 *             <button class="aura-btn aura-btn-minus" data-product-id="<?php echo esc_attr( $product_id ); ?>">
 *             <span class="aura-quantity-display" data-product-id="<?php echo esc_attr( $product_id ); ?>">
 *             <button class="aura-btn aura-btn-plus" data-product-id="<?php echo esc_attr( $product_id ); ?>">
 *
 *             <span class="aura-total-price" data-product-id="<?php echo esc_attr( $product_id ); ?>">
 *
 *         </div>
 *
 *         <?php endforeach; ?>
 *
 *     </div>
 *
 *     <?php if ( count( $products ) > $visible_items ) : ?>
 *         <div class="aura-show-more-wrapper">
 *             <button class="aura-show-more-btn">
 *                 <span class="aura-btn-text">Show More</span>
 *                 <span class="aura-chevron">▼</span>
 *             </button>
 *         </div>
 *     <?php endif; ?>
 *
 * </div>
 *
 * ========================================
 * ADDING NEW LAYOUTS
 * ========================================
 *
 * To add a new layout:
 *
 * 1. Create layout-{type}.php in this directory
 * 2. Follow the structure requirements above
 * 3. Create corresponding CSS file in assets/css/layout-{type}.css
 * 4. Add layout option to widget controls in class-products-widget.php:
 *
 *    'options' => array(
 *        'card' => esc_html__( 'Card Layout', 'aura-custom-product-style' ),
 *        'your-new-layout' => esc_html__( 'Your New Layout', 'aura-custom-product-style' ),
 *    ),
 *
 * 5. Enqueue CSS in main plugin file if needed
 * 6. Test with Elementor preview and frontend
 *
 * ========================================
 * TROUBLESHOOTING
 * ========================================
 *
 * Common issues:
 *
 * - Buttons not working?
 *   Check data-product-id attributes on buttons and displays
 *
 * - Quantities not updating?
 *   Verify .aura-quantity-display and .aura-total-price classes
 *
 * - Show more not working?
 *   Ensure .hidden-item class on items beyond visible rows
 *
 * - Layout breaks in Elementor?
 *   Check that all required data attributes are present
 *
 * ========================================
 */
