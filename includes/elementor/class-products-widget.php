<?php
/**
 * Elementor Products Widget
 *
 * @package Aura_Custom_Product_Style
 */

namespace Aura_CPS\Elementor;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Aura Products Widget Class
 */
class Products_Widget extends Widget_Base {

	/**
	 * Get widget name
	 *
	 * @return string Widget name
	 */
	public function get_name() {
		return 'aura-products';
	}

	/**
	 * Get widget title
	 *
	 * @return string Widget title
	 */
	public function get_title() {
		return esc_html__( 'Aura Products', 'aura-custom-product-style' );
	}

	/**
	 * Get widget icon
	 *
	 * @return string Widget icon
	 */
	public function get_icon() {
		return 'eicon-products';
	}

	/**
	 * Get widget categories
	 *
	 * @return array Widget categories
	 */
	public function get_categories() {
		return array( 'aura-rental' );
	}

	/**
	 * Get widget keywords
	 *
	 * @return array Widget keywords
	 */
	public function get_keywords() {
		return array( 'aura', 'products', 'woocommerce', 'villa', 'equipment', 'rental' );
	}

	/**
	 * Get script dependencies
	 *
	 * @return array Script dependencies
	 */
	public function get_script_depends() {
		return array( 'aura-cps-widget-js' );
	}

	/**
	 * Get style dependencies
	 *
	 * @return array Style dependencies
	 */
	public function get_style_depends() {
		// Load all layouts - let browser cache handle optimization
		return array(
			'aura-cps-widget-base',
			'aura-cps-layout-card',
			'aura-cps-layout-checkbox',
			'aura-cps-layout-wine',
			'aura-cps-layout-kids',
			'aura-cps-layout-moments'
		);
	}

	/**
	 * Register widget controls
	 */
	protected function register_controls() {
		// ========================================
		// SECTION 1: Layout
		// ========================================
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => esc_html__( 'Layout', 'aura-custom-product-style' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'layout_type',
			array(
				'label'   => esc_html__( 'Layout Type', 'aura-custom-product-style' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'card',
				'options' => array(
					'card'     => esc_html__( 'Card Layout', 'aura-custom-product-style' ),
					'checkbox' => esc_html__( 'Checkbox Layout', 'aura-custom-product-style' ),
					'wine'     => esc_html__( 'Wine Layout', 'aura-custom-product-style' ),
					'kids'     => esc_html__( 'Kids Corner Layout', 'aura-custom-product-style' ),
					'moments'  => esc_html__( 'Moments Layout', 'aura-custom-product-style' ),
				),
			)
		);

		$this->end_controls_section();

		// ========================================
		// SECTION 2: Icon Settings (Checkbox Layout Only)
		// ========================================
		$this->start_controls_section(
			'section_icon',
			array(
				'label'     => esc_html__( 'Product Icon', 'aura-custom-product-style' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array(
					'layout_type' => 'checkbox',
				),
			)
		);

		$this->add_control(
			'icon_type',
			array(
				'label'   => esc_html__( 'Icon Type', 'aura-custom-product-style' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'icon',
				'options' => array(
					'icon'  => esc_html__( 'Icon Library', 'aura-custom-product-style' ),
					'image' => esc_html__( 'Custom Image', 'aura-custom-product-style' ),
				),
			)
		);

		$this->add_control(
			'product_icon',
			array(
				'label'     => esc_html__( 'Choose Icon', 'aura-custom-product-style' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-check-circle',
					'library' => 'fa-solid',
				),
				'condition' => array(
					'icon_type' => 'icon',
				),
			)
		);

		$this->add_control(
			'icon_image',
			array(
				'label'     => esc_html__( 'Choose Image', 'aura-custom-product-style' ),
				'type'      => Controls_Manager::MEDIA,
				'default'   => array(
					'url' => '',
				),
				'condition' => array(
					'icon_type' => 'image',
				),
			)
		);

		$this->add_control(
			'icon_size',
			array(
				'label'      => esc_html__( 'Icon Size', 'aura-custom-product-style' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 16,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 48,
				),
				'selectors'  => array(
					'{{WRAPPER}} .aura-product-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .aura-product-icon img' => 'max-width: {{SIZE}}{{UNIT}}; max-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'aura-custom-product-style' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#333',
				'selectors' => array(
					'{{WRAPPER}} .aura-product-icon' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'icon_type' => 'icon',
				),
			)
		);

		$this->end_controls_section();

		// ========================================
		// SECTION 3: Product Filters
		// ========================================
		$this->start_controls_section(
			'section_filters',
			array(
				'label' => esc_html__( 'Product Filters', 'aura-custom-product-style' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'product_category',
			array(
				'label'       => esc_html__( 'Product Category', 'aura-custom-product-style' ),
				'type'        => Controls_Manager::SELECT2,
				'options'     => $this->get_product_categories(),
				'multiple'    => true,
				'label_block' => true,
				'description' => esc_html__( 'Filter products by category. Leave empty to show all related products.', 'aura-custom-product-style' ),
			)
		);

		$this->add_control(
			'product_ids',
			array(
				'label'       => esc_html__( 'Specific Product IDs', 'aura-custom-product-style' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'e.g., 123, 456, 789', 'aura-custom-product-style' ),
				'description' => esc_html__( 'Enter specific product IDs separated by commas. Leave empty to show all.', 'aura-custom-product-style' ),
			)
		);

		$this->add_control(
			'filter_logic',
			array(
				'label'   => esc_html__( 'Filter Logic', 'aura-custom-product-style' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'and',
				'options' => array(
					'and' => esc_html__( 'AND (Must match villa AND filters)', 'aura-custom-product-style' ),
					'or'  => esc_html__( 'OR (Match villa OR filters)', 'aura-custom-product-style' ),
				),
				'condition' => array(
					'product_ids!' => '',
				),
			)
		);

		$this->add_control(
			'max_products',
			array(
				'label'       => esc_html__( 'Maximum Products', 'aura-custom-product-style' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'description' => esc_html__( 'Maximum number of products to show. Set to 0 for unlimited.', 'aura-custom-product-style' ),
			)
		);

		$this->end_controls_section();

		// ========================================
		// SECTION 3: Display Settings
		// ========================================
		$this->start_controls_section(
			'section_display',
			array(
				'label' => esc_html__( 'Display Settings', 'aura-custom-product-style' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'columns_mode',
			array(
				'label'   => esc_html__( 'Columns Mode', 'aura-custom-product-style' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto'  => esc_html__( 'Auto (Fit by card width)', 'aura-custom-product-style' ),
					'fixed' => esc_html__( 'Fixed Columns', 'aura-custom-product-style' ),
				),
			)
		);

		$this->add_responsive_control(
			'columns_count',
			array(
				'label'           => esc_html__( 'Columns', 'aura-custom-product-style' ),
				'type'            => Controls_Manager::NUMBER,
				'min'             => 1,
				'max'             => 4,
				'default'         => 2,
				'tablet_default'  => 2,
				'mobile_default'  => 1,
				'condition'       => array(
					'columns_mode' => 'fixed',
				),
			)
		);

		$this->add_control(
			'auto_columns_count',
			array(
				'label'       => esc_html__( 'Items Per Row', 'aura-custom-product-style' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'max'         => 4,
				'description' => esc_html__( 'Number of items per row in auto mode. Set to 0 to use card width instead.', 'aura-custom-product-style' ),
				'condition'   => array(
					'columns_mode' => 'auto',
				),
			)
		);

		$this->add_control(
			'card_width',
			array(
				'label'      => esc_html__( 'Card Width (px)', 'aura-custom-product-style' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 600,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 350,
				),
				'description' => esc_html__( 'Set to 0 to distribute cards evenly. Only used if Items Per Row is 0.', 'aura-custom-product-style' ),
				'condition'  => array(
					'columns_mode' => 'auto',
					'auto_columns_count' => 0,
				),
			)
		);

		$this->add_responsive_control(
			'rows_visible',
			array(
				'label'          => esc_html__( 'Rows Visible', 'aura-custom-product-style' ),
				'type'           => Controls_Manager::NUMBER,
				'min'            => 1,
				'max'            => 10,
				'default'        => 2,
				'tablet_default' => 2,
				'mobile_default' => 3,
				'description'    => esc_html__( 'Number of rows to show before "Show More" button appears.', 'aura-custom-product-style' ),
			)
		);

		$this->add_control(
			'gap',
			array(
				'label'      => esc_html__( 'Gap Between Cards', 'aura-custom-product-style' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 20,
				),
				'selectors'  => array(
					'{{WRAPPER}} .aura-products-grid' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'card_min_height',
			array(
				'label'      => esc_html__( 'Card Min Height', 'aura-custom-product-style' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'vh', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
					'em' => array(
						'min' => 0,
						'max' => 50,
					),
					'vh' => array(
						'min' => 0,
						'max' => 100,
					),
					'%' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 0,
				),
				'selectors'  => array(
					'{{WRAPPER}} .aura-product-card' => 'min-height: {{SIZE}}{{UNIT}};',
				),
				'description' => esc_html__( 'Set minimum height for all cards. Use 0 for auto height.', 'aura-custom-product-style' ),
			)
		);

		$this->add_control(
			'price_unit_text',
			array(
				'label'       => esc_html__( 'Price Unit Text', 'aura-custom-product-style' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'each', 'aura-custom-product-style' ),
				'placeholder' => esc_html__( 'each', 'aura-custom-product-style' ),
				'description' => esc_html__( 'Text displayed after the price (e.g., "each", "per day", "per unit").', 'aura-custom-product-style' ),
			)
		);

		$this->add_control(
			'total_label_text',
			array(
				'label'       => esc_html__( 'Total Label Text', 'aura-custom-product-style' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Total', 'aura-custom-product-style' ),
				'placeholder' => esc_html__( 'Total', 'aura-custom-product-style' ),
				'description' => esc_html__( 'Label text for the total price row.', 'aura-custom-product-style' ),
			)
		);

		$this->end_controls_section();

		// ========================================
		// SECTION 4: Show More Button
		// ========================================
		$this->start_controls_section(
			'section_show_more',
			array(
				'label' => esc_html__( 'Show More Button', 'aura-custom-product-style' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_more_text',
			array(
				'label'   => esc_html__( 'Show More Text', 'aura-custom-product-style' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Show More', 'aura-custom-product-style' ),
			)
		);

		$this->add_control(
			'show_less_text',
			array(
				'label'   => esc_html__( 'Show Less Text', 'aura-custom-product-style' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Show Less', 'aura-custom-product-style' ),
			)
		);

		$this->add_control(
			'show_more_color',
			array(
				'label'     => esc_html__( 'Text Color', 'aura-custom-product-style' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#333333',
				'selectors' => array(
					'{{WRAPPER}} .aura-show-more-btn' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'divider_color',
			array(
				'label'     => esc_html__( 'Divider Color', 'aura-custom-product-style' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#B0A695',
				'selectors' => array(
					'{{WRAPPER}} .aura-show-more-wrapper' => 'border-top-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		// ========================================
		// SECTION 5: Messages
		// ========================================
		$this->start_controls_section(
			'section_messages',
			array(
				'label' => esc_html__( 'Messages', 'aura-custom-product-style' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'no_villa_message',
			array(
				'label'   => esc_html__( 'No Villa Message', 'aura-custom-product-style' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Please add a villa to your cart to see available products.', 'aura-custom-product-style' ),
			)
		);

		$this->add_control(
			'no_products_message',
			array(
				'label'   => esc_html__( 'No Products Message', 'aura-custom-product-style' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'No products found for this villa.', 'aura-custom-product-style' ),
			)
		);

		$this->end_controls_section();

		// ========================================
		// STYLE SECTION: Card Style
		// ========================================
		$this->start_controls_section(
			'section_card_style',
			array(
				'label' => esc_html__( 'Card Style', 'aura-custom-product-style' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'card_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'aura-custom-product-style' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#9A9A9A',
				'selectors' => array(
					'{{WRAPPER}} .aura-product-card' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'card_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'aura-custom-product-style' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 3,
				),
				'selectors'  => array(
					'{{WRAPPER}} .aura-product-card' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// ========================================
		// STYLE SECTION: Typography
		// ========================================
		$this->start_controls_section(
			'section_typography',
			array(
				'label' => esc_html__( 'Typography', 'aura-custom-product-style' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Title Color', 'aura-custom-product-style' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .aura-product-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'description_color',
			array(
				'label'     => esc_html__( 'Description Color', 'aura-custom-product-style' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .aura-product-description' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'price_color',
			array(
				'label'     => esc_html__( 'Price Color', 'aura-custom-product-style' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .aura-product-price-row' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Get WooCommerce product categories
	 *
	 * @return array Product categories
	 */
	private function get_product_categories() {
		if ( ! function_exists( 'get_terms' ) ) {
			return array();
		}

		$categories = get_terms( array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		) );

		$options = array();
		if ( ! is_wp_error( $categories ) ) {
			foreach ( $categories as $category ) {
				$options[ $category->slug ] = $category->name;
			}
		}

		return $options;
	}

	/**
	 * Render widget output on frontend
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$widget_id = $this->get_id();

		// Check if villa is in cart
		$villa_id = aura_cps_get_villa_id_from_cart();

		if ( ! $villa_id ) {
			echo '<div class="aura-products-notice">' . wp_kses_post( $settings['no_villa_message'] ) . '</div>';
			return;
		}

		// Get products
		$product_ids = aura_cps_get_products( $settings );

		if ( empty( $product_ids ) ) {
			echo '<div class="aura-products-notice">' . wp_kses_post( $settings['no_products_message'] ) . '</div>';
			return;
		}

		// Get product objects
		$products = array();
		foreach ( $product_ids as $product_id ) {
			$product = aura_cps_get_product( $product_id );
			if ( $product ) {
				$products[] = $product;
			}
		}

		if ( empty( $products ) ) {
			echo '<div class="aura-products-notice">' . wp_kses_post( $settings['no_products_message'] ) . '</div>';
			return;
		}

		// Include layout template
		$layout_type = $settings['layout_type'];
		$layout_file = AURA_CPS_DIR . 'includes/elementor/layouts/layout-' . $layout_type . '.php';

		if ( file_exists( $layout_file ) ) {
			include $layout_file;
		} else {
			echo '<div class="aura-products-notice">' . esc_html__( 'Layout template not found.', 'aura-custom-product-style' ) . '</div>';
		}
	}
}
