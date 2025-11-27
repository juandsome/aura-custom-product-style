<?php
/**
 * Elementor Arrival Form Widget
 * Standalone widget for collecting arrival information
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
 * Aura Arrival Form Widget Class
 */
class Arrival_Form_Widget extends Widget_Base {

	/**
	 * Get widget name
	 *
	 * @return string Widget name
	 */
	public function get_name() {
		return 'aura-arrival-form';
	}

	/**
	 * Get widget title
	 *
	 * @return string Widget title
	 */
	public function get_title() {
		return esc_html__( 'Aura Arrival Form', 'aura-custom-product-style' );
	}

	/**
	 * Get widget icon
	 *
	 * @return string Widget icon
	 */
	public function get_icon() {
		return 'eicon-form-horizontal';
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
		return array( 'aura', 'form', 'arrival', 'details', 'note' );
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
		return array(
			'aura-cps-widget-base',
			'aura-cps-layout-form'
		);
	}

	/**
	 * Register widget controls
	 */
	protected function register_controls() {
		// ========================================
		// SECTION: Content Settings
		// ========================================
		$this->start_controls_section(
			'content_section',
			array(
				'label' => esc_html__( 'Form Settings', 'aura-custom-product-style' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'form_label',
			array(
				'label'       => esc_html__( 'Form Label', 'aura-custom-product-style' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Details about your arrival', 'aura-custom-product-style' ),
				'placeholder' => esc_html__( 'Enter form label', 'aura-custom-product-style' ),
			)
		);

		$this->add_control(
			'form_placeholder',
			array(
				'label'       => esc_html__( 'Placeholder Text', 'aura-custom-product-style' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Time, Location, Flight Number, etc.', 'aura-custom-product-style' ),
				'placeholder' => esc_html__( 'Enter placeholder text', 'aura-custom-product-style' ),
			)
		);

		$this->add_control(
			'confirm_text',
			array(
				'label'       => esc_html__( 'Confirm Button Text', 'aura-custom-product-style' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Confirm Details', 'aura-custom-product-style' ),
				'placeholder' => esc_html__( 'Enter button text', 'aura-custom-product-style' ),
			)
		);

		$this->add_control(
			'textarea_rows',
			array(
				'label'   => esc_html__( 'Textarea Rows', 'aura-custom-product-style' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 6,
				'min'     => 3,
				'max'     => 20,
			)
		);

		$this->end_controls_section();

		// ========================================
		// SECTION: Style Settings
		// ========================================
		$this->start_controls_section(
			'style_section',
			array(
				'label' => esc_html__( 'Style', 'aura-custom-product-style' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'form_width',
			array(
				'label'      => esc_html__( 'Form Width', 'aura-custom-product-style' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 200,
						'max' => 1200,
					),
					'%' => array(
						'min' => 10,
						'max' => 100,
					),
				),
				'default' => array(
					'unit' => '%',
					'size' => 100,
				),
				'selectors' => array(
					'{{WRAPPER}} .aura-form-card' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$widget_id = $this->get_id();

		// Get saved form data from session if exists
		$saved_form_data = WC()->session ? WC()->session->get( 'aura_arrival_details', '' ) : '';
		$is_confirmed = WC()->session ? WC()->session->get( 'aura_arrival_confirmed', false ) : false;

		// Get settings with defaults
		$form_label = ! empty( $settings['form_label'] ) ? $settings['form_label'] : esc_html__( 'Details about your arrival', 'aura-custom-product-style' );
		$form_placeholder = ! empty( $settings['form_placeholder'] ) ? $settings['form_placeholder'] : esc_html__( 'Time, Location, Flight Number, etc.', 'aura-custom-product-style' );
		$confirm_text = ! empty( $settings['confirm_text'] ) ? $settings['confirm_text'] : esc_html__( 'Confirm Details', 'aura-custom-product-style' );
		$textarea_rows = ! empty( $settings['textarea_rows'] ) ? intval( $settings['textarea_rows'] ) : 6;
		?>

		<div class="aura-products-wrapper aura-form-wrapper" data-widget-id="<?php echo esc_attr( $widget_id ); ?>">

			<div class="aura-product-card aura-form-card <?php echo $is_confirmed ? 'confirmed' : ''; ?>">

				<!-- Form Fields -->
				<div class="aura-form-fields">
					<!-- Details Field -->
					<div class="aura-form-field">
						<label class="aura-form-label" for="aura-form-details-<?php echo esc_attr( $widget_id ); ?>">
							<?php echo esc_html( $form_label ); ?>
						</label>
						<textarea id="aura-form-details-<?php echo esc_attr( $widget_id ); ?>"
								  class="aura-form-textarea aura-form-details"
								  placeholder="<?php echo esc_attr( $form_placeholder ); ?>"
								  rows="<?php echo esc_attr( $textarea_rows ); ?>"
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
							<?php echo esc_html( $confirm_text ); ?>
						</span>
					</div>
				</div>

			</div><!-- .aura-form-card -->

		</div><!-- .aura-products-wrapper -->

		<?php
	}

	/**
	 * Render widget output in the editor
	 */
	protected function content_template() {
		?>
		<#
		var widget_id = 'elementor-preview-' + view.getID();
		var form_label = settings.form_label || '<?php echo esc_js( __( 'Details about your arrival', 'aura-custom-product-style' ) ); ?>';
		var form_placeholder = settings.form_placeholder || '<?php echo esc_js( __( 'Time, Location, Flight Number, etc.', 'aura-custom-product-style' ) ); ?>';
		var confirm_text = settings.confirm_text || '<?php echo esc_js( __( 'Confirm Details', 'aura-custom-product-style' ) ); ?>';
		var textarea_rows = settings.textarea_rows || 6;
		#>

		<div class="aura-products-wrapper aura-form-wrapper" data-widget-id="{{{ widget_id }}}">

			<div class="aura-product-card aura-form-card">

				<!-- Form Fields -->
				<div class="aura-form-fields">
					<!-- Details Field -->
					<div class="aura-form-field">
						<label class="aura-form-label" for="aura-form-details-{{{ widget_id }}}">
							{{{ form_label }}}
						</label>
						<textarea id="aura-form-details-{{{ widget_id }}}"
								  class="aura-form-textarea aura-form-details"
								  placeholder="{{{ form_placeholder }}}"
								  rows="{{{ textarea_rows }}}"></textarea>
					</div>
				</div>

				<!-- Confirm Button -->
				<div class="aura-form-footer">
					<div class="aura-form-confirm-wrapper">
						<button class="aura-form-checkbox-btn"
								role="checkbox"
								aria-checked="false"
								aria-label="<?php esc_attr_e( 'Confirm details', 'aura-custom-product-style' ); ?>"
								disabled>
							<span class="aura-form-checkmark">✓</span>
						</button>
						<span class="aura-form-confirm-text">
							{{{ confirm_text }}}
						</span>
					</div>
				</div>

			</div><!-- .aura-form-card -->

		</div><!-- .aura-products-wrapper -->
		<?php
	}
}
