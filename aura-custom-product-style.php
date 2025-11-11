<?php
/**
 * Plugin Name: Aura Custom Product Style
 * Plugin URI: https://collectionaura.com
 * Description: Elementor widgets to display WooCommerce products related to villas in cart with multiple layout options
 * Version: 1.0.4
 * Author: Collection Aura
 * Author URI: https://collectionaura.com
 * License: GPL v2 or later
 * Text Domain: aura-custom-product-style
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'AURA_CPS_VERSION', '1.0.4' );
define( 'AURA_CPS_DIR', plugin_dir_path( __FILE__ ) );
define( 'AURA_CPS_URL', plugin_dir_url( __FILE__ ) );
define( 'AURA_CPS_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main Plugin Class - Singleton Pattern
 */
class Aura_Custom_Product_Style {

	/**
	 * Single instance of the class
	 *
	 * @var Aura_Custom_Product_Style
	 */
	private static $instance = null;

	/**
	 * Get single instance of the class
	 *
	 * @return Aura_Custom_Product_Style
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init_hooks();
		$this->includes();
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		// Plugin initialization
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Elementor hooks
		add_action( 'elementor/widgets/register', array( $this, 'register_elementor_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'add_elementor_widget_categories' ) );

		// AJAX hooks
		add_action( 'wp_ajax_aura_cps_update_product_quantity', array( $this, 'ajax_update_product_quantity' ) );
		add_action( 'wp_ajax_nopriv_aura_cps_update_product_quantity', array( $this, 'ajax_update_product_quantity' ) );
		add_action( 'wp_ajax_aura_cps_get_cart_quantities', array( $this, 'ajax_get_cart_quantities' ) );
		add_action( 'wp_ajax_nopriv_aura_cps_get_cart_quantities', array( $this, 'ajax_get_cart_quantities' ) );
	}

	/**
	 * Include required files
	 */
	private function includes() {
		require_once AURA_CPS_DIR . 'includes/helpers.php';
		require_once AURA_CPS_DIR . 'includes/cart-functions.php';
	}

	/**
	 * Load plugin textdomain for translations
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'aura-custom-product-style',
			false,
			dirname( AURA_CPS_BASENAME ) . '/languages'
		);
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts() {
		// Only enqueue if WooCommerce is active
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		// Enqueue base CSS
		wp_enqueue_style(
			'aura-cps-widget-base',
			AURA_CPS_URL . 'assets/css/widget-base.css',
			array(),
			AURA_CPS_VERSION
		);

		// Enqueue card layout CSS
		wp_enqueue_style(
			'aura-cps-layout-card',
			AURA_CPS_URL . 'assets/css/layout-card.css',
			array( 'aura-cps-widget-base' ),
			AURA_CPS_VERSION
		);

		// Enqueue JavaScript
		wp_enqueue_script(
			'aura-cps-widget-js',
			AURA_CPS_URL . 'assets/js/products-widget.js',
			array( 'jquery' ),
			AURA_CPS_VERSION,
			true
		);

		// Get cart quantities for initial state
		$cart_quantities = array();
		if ( WC()->cart ) {
			foreach ( WC()->cart->get_cart() as $cart_item ) {
				$product_id = $cart_item['product_id'];
				if ( ! isset( $cart_quantities[ $product_id ] ) ) {
					$cart_quantities[ $product_id ] = 0;
				}
				$cart_quantities[ $product_id ] += $cart_item['quantity'];
			}
		}

		// Localize script
		wp_localize_script(
			'aura-cps-widget-js',
			'auraCpsData',
			array(
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'nonce'         => wp_create_nonce( 'aura_cps_nonce' ),
				'cartQuantities' => $cart_quantities,
				'currency'      => get_woocommerce_currency_symbol(),
			)
		);
	}

	/**
	 * Register Elementor widgets
	 *
	 * @param object $widgets_manager Elementor widgets manager
	 */
	public function register_elementor_widgets( $widgets_manager ) {
		require_once AURA_CPS_DIR . 'includes/elementor/class-products-widget.php';
		$widgets_manager->register( new \Aura_CPS\Elementor\Products_Widget() );
	}

	/**
	 * Add custom Elementor widget category
	 *
	 * @param object $elements_manager Elementor elements manager
	 */
	public function add_elementor_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'aura-rental',
			array(
				'title' => esc_html__( 'Aura Rental', 'aura-custom-product-style' ),
				'icon'  => 'fa fa-plug',
			)
		);
	}

	/**
	 * AJAX: Update product quantity in cart
	 */
	public function ajax_update_product_quantity() {
		// Verify nonce
		check_ajax_referer( 'aura_cps_nonce', 'nonce' );

		// Check required parameters
		if ( ! isset( $_POST['product_id'] ) || ! isset( $_POST['quantity'] ) ) {
			wp_send_json_error( array(
				'message' => esc_html__( 'Missing required parameters', 'aura-custom-product-style' ),
			) );
		}

		$product_id = intval( $_POST['product_id'] );
		$quantity   = intval( $_POST['quantity'] );

		// Update cart
		$result = aura_cps_update_cart_product( $product_id, $quantity );

		if ( $result['success'] ) {
			// Get updated cart total
			$cart_total = WC()->cart ? WC()->cart->get_cart_total() : '';

			wp_send_json_success( array(
				'quantity'   => $quantity,
				'action'     => $result['action'],
				'cart_total' => $cart_total,
				'message'    => $result['message'],
			) );
		} else {
			wp_send_json_error( array(
				'message' => $result['message'],
			) );
		}
	}

	/**
	 * AJAX: Get current cart quantities
	 */
	public function ajax_get_cart_quantities() {
		// Verify nonce
		check_ajax_referer( 'aura_cps_nonce', 'nonce' );

		$cart_quantities = array();
		if ( function_exists( 'WC' ) && WC()->cart ) {
			foreach ( WC()->cart->get_cart() as $cart_item ) {
				$product_id = $cart_item['product_id'];
				if ( ! isset( $cart_quantities[ $product_id ] ) ) {
					$cart_quantities[ $product_id ] = 0;
				}
				$cart_quantities[ $product_id ] += $cart_item['quantity'];
			}
		}

		wp_send_json_success( array(
			'quantities' => $cart_quantities,
		) );
	}
}

/**
 * Initialize the plugin
 *
 * @return Aura_Custom_Product_Style
 */
function aura_custom_product_style_init() {
	return Aura_Custom_Product_Style::get_instance();
}

// Initialize on plugins_loaded
add_action( 'plugins_loaded', 'aura_custom_product_style_init' );
