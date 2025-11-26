/**
 * Aura Custom Product Style - Products Widget JavaScript
 * Handles cart interactions, show more/less, and dynamic updates
 *
 * @package Aura_Custom_Product_Style
 */

(function($) {
	'use strict';

	/**
	 * Main Widget Handler Object
	 */
	const AuraProductsWidget = {

		/**
		 * Events initialized flag
		 */
		eventsInitialized: false,

		/**
		 * Debounce timer for resize
		 */
		resizeTimer: null,

		/**
		 * Initialize the widget
		 */
		init: function() {
			// Bind events only once
			if (!this.eventsInitialized) {
				this.bindEvents();
				this.eventsInitialized = true;
			}

			// Update visible rows for all widgets
			this.updateAllVisibleRows();

			// Load initial quantities from cart
			this.loadInitialQuantities();
		},

		/**
		 * Bind event handlers using delegation
		 */
		bindEvents: function() {
			const self = this;

			// Plus button click
			$(document).off('click.auraCps', '.aura-btn-plus')
					   .on('click.auraCps', '.aura-btn-plus', function(e) {
				e.preventDefault();
				self.handlePlusClick($(this));
			});

			// Minus button click
			$(document).off('click.auraCps', '.aura-btn-minus')
					   .on('click.auraCps', '.aura-btn-minus', function(e) {
				e.preventDefault();
				self.handleMinusClick($(this));
			});

			// Checkbox button click (for checkbox layout)
			$(document).off('click.auraCps', '.aura-checkbox-btn')
					   .on('click.auraCps', '.aura-checkbox-btn', function(e) {
				e.preventDefault();
				self.handleCheckboxClick($(this));
			});

			// Checkbox label click (for checkbox layout)
			$(document).off('click.auraCps', '.aura-checkbox-label')
					   .on('click.auraCps', '.aura-checkbox-label', function(e) {
				e.preventDefault();
				const $button = $(this).siblings('.aura-checkbox-btn');
				self.handleCheckboxClick($button);
			});

			// Moments checkbox button click
			$(document).off('click.auraCps', '.aura-moments-checkbox-btn')
					   .on('click.auraCps', '.aura-moments-checkbox-btn', function(e) {
				e.preventDefault();
				self.handleCheckboxClick($(this));
			});

			// Moments "Add to Cart" text click
			$(document).off('click.auraCps', '.aura-moments-add-text')
					   .on('click.auraCps', '.aura-moments-add-text', function(e) {
				e.preventDefault();
				const $button = $(this).siblings('.aura-moments-checkbox-btn');
				self.handleCheckboxClick($button);
			});

			// Form layout: input changes (validation)
			$(document).off('input.auraCps change.auraCps', '.aura-form-input, .aura-form-textarea')
					   .on('input.auraCps change.auraCps', '.aura-form-input, .aura-form-textarea', function() {
				const $input = $(this);
				const $card = $input.closest('.aura-form-card');
				self.validateForm($card);
			});

			// Form layout: confirm button click
			$(document).off('click.auraCps', '.aura-form-checkbox-btn')
					   .on('click.auraCps', '.aura-form-checkbox-btn', function(e) {
				e.preventDefault();
				const $button = $(this);
				if (!$button.prop('disabled')) {
					self.handleFormConfirm($button);
				}
			});

			// Form layout: confirm text click
			$(document).off('click.auraCps', '.aura-form-confirm-text')
					   .on('click.auraCps', '.aura-form-confirm-text', function(e) {
				e.preventDefault();
				const $button = $(this).siblings('.aura-form-checkbox-btn');
				if (!$button.prop('disabled')) {
					self.handleFormConfirm($button);
				}
			});

			// Show more/less button click
			$(document).off('click.auraCps', '.aura-show-more-btn')
					   .on('click.auraCps', '.aura-show-more-btn', function(e) {
				e.preventDefault();
				self.handleShowMore($(this));
			});

			// Window resize (debounced)
			$(window).off('resize.auraCps')
					 .on('resize.auraCps', function() {
				clearTimeout(self.resizeTimer);
				self.resizeTimer = setTimeout(function() {
					self.handleResize();
				}, 250);
			});

			// WooCommerce cart updated event
			$(document.body).off('wc_fragments_refreshed.auraCps updated_cart_totals.auraCps')
						   .on('wc_fragments_refreshed.auraCps updated_cart_totals.auraCps', function() {
				self.loadInitialQuantities();
			});
		},

		/**
		 * Calculate columns for all auto-mode widgets
		 */
		calculateAllColumns: function() {
			$('.aura-products-grid[data-columns-mode="auto"]').each(function() {
				const $grid = $(this);
				const cardWidth = parseInt($grid.attr('data-card-width')) || 350;
				const containerWidth = $grid.width();
				const gap = parseInt($grid.css('gap')) || 20;

				// Calculate how many cards fit
				let columns = Math.floor((containerWidth + gap) / (cardWidth + gap));
				columns = Math.max(1, columns); // At least 1 column

				// Set CSS custom property for grid
				$grid.css('--aura-card-width', cardWidth + 'px');
				$grid.attr('data-calculated-columns', columns);
			});
		},

		/**
		 * Update visible rows for all widgets
		 */
		updateAllVisibleRows: function() {
			$('.aura-products-grid').each(function() {
				const $grid = $(this);
				const columnsMode = $grid.attr('data-columns-mode');
				const rowsVisible = parseInt($grid.attr('data-rows-visible')) || 2;
				const $cards = $grid.find('.aura-product-card');
				const totalCards = $cards.length;
				const cardWidth = parseInt($grid.attr('data-card-width')) || 350;

				let columns;
				if (columnsMode === 'auto') {
					// If card_width is 0, use data-columns-count (Items Per Row mode)
					if (cardWidth === 0) {
						columns = parseInt($grid.attr('data-columns-count')) || 2;
					} else {
						// Calculate columns based on actual card positions
						if ($cards.length > 1) {
							const firstCardTop = $cards.eq(0).offset().top;
							let cardsInFirstRow = 1;

							for (let i = 1; i < $cards.length; i++) {
								if (Math.abs($cards.eq(i).offset().top - firstCardTop) < 5) {
									cardsInFirstRow++;
								} else {
									break;
								}
							}
							columns = cardsInFirstRow;
						} else {
							columns = 1;
						}
					}
				} else {
					columns = parseInt($grid.attr('data-columns-count')) || 2;
				}

				const visibleItems = rowsVisible * columns;

				// Show/hide cards based on visible items
				$cards.each(function(index) {
					const $card = $(this);
					if (index < visibleItems || $grid.hasClass('expanded')) {
						$card.removeClass('hidden-item').show();
					} else {
						$card.addClass('hidden-item').hide();
					}
				});

				// Show/hide "show more" button
				const $wrapper = $grid.closest('.aura-products-wrapper');
				const $showMoreWrapper = $wrapper.find('.aura-show-more-wrapper');

				if (totalCards > visibleItems) {
					$showMoreWrapper.show();
				} else {
					$showMoreWrapper.hide();
				}
			});
		},

		/**
		 * Handle plus button click
		 */
		handlePlusClick: function($button) {
			const productId = $button.data('product-id');
			const $card = $button.closest('.aura-product-card');
			const $quantityDisplay = $card.find('.aura-quantity-display[data-product-id="' + productId + '"]');
			const currentQuantity = parseInt($quantityDisplay.text()) || 0;
			const newQuantity = currentQuantity + 1;

			// Optimistic UI update
			this.updateProductUI($card, productId, newQuantity);

			// Update cart via AJAX
			this.updateCart(productId, newQuantity, $card);
		},

		/**
		 * Handle minus button click
		 */
		handleMinusClick: function($button) {
			const productId = $button.data('product-id');
			const $card = $button.closest('.aura-product-card');
			const $quantityDisplay = $card.find('.aura-quantity-display[data-product-id="' + productId + '"]');
			const currentQuantity = parseInt($quantityDisplay.text()) || 0;

			if (currentQuantity <= 0) {
				return; // Can't go below 0
			}

			const newQuantity = currentQuantity - 1;

			// Optimistic UI update
			this.updateProductUI($card, productId, newQuantity);

			// Update cart via AJAX
			this.updateCart(productId, newQuantity, $card);
		},

		/**
		 * Handle checkbox button click (checkbox and moments layouts)
		 */
		handleCheckboxClick: function($button) {
			const productId = $button.data('product-id');
			const $card = $button.closest('.aura-checkbox-card, .aura-moments-card');
			const isChecked = $button.attr('aria-checked') === 'true';
			const newQuantity = isChecked ? 0 : 1;

			// Toggle checked state (optimistic UI update)
			$button.attr('aria-checked', !isChecked);

			// Update cart via AJAX
			this.updateCheckboxCart(productId, newQuantity, $card, $button);
		},

		/**
		 * Update cart for checkbox layout
		 */
		updateCheckboxCart: function(productId, quantity, $card, $button) {
			const self = this;

			// Add loading state
			$card.addClass('loading');

			$.ajax({
				url: auraCpsData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'aura_cps_update_product_quantity',
					nonce: auraCpsData.nonce,
					product_id: productId,
					quantity: quantity
				},
				success: function(response) {
					$card.removeClass('loading');

					if (response.success) {
						// Trigger WooCommerce cart update
						$(document.body).trigger('wc_fragment_refresh');

						// Update stored cart quantities
						if (typeof auraCpsData.cartQuantities !== 'undefined') {
							auraCpsData.cartQuantities[productId] = quantity;
						}
					} else {
						// Revert checkbox state on error
						const isChecked = $button.attr('aria-checked') === 'true';
						$button.attr('aria-checked', !isChecked);
						alert(response.data.message || 'Error updating cart');
					}
				},
				error: function() {
					$card.removeClass('loading');
					// Revert checkbox state on error
					const isChecked = $button.attr('aria-checked') === 'true';
					$button.attr('aria-checked', !isChecked);
					alert('An error occurred while updating the cart');
				}
			});
		},

		/**
		 * Update product UI (quantity and total)
		 */
		updateProductUI: function($card, productId, quantity) {
			const price = parseFloat($card.attr('data-product-price')) || 0;
			const total = price * quantity;
			const currency = auraCpsData.currency || '€';

			// Update quantity display
			$card.find('.aura-quantity-display[data-product-id="' + productId + '"]').text(quantity);

			// Update total price
			$card.find('.aura-total-price[data-product-id="' + productId + '"]').text(
				currency + ' ' + total.toFixed(2)
			);
		},

		/**
		 * Update cart via AJAX
		 */
		updateCart: function(productId, quantity, $card) {
			const self = this;

			// Add loading state
			$card.addClass('loading');

			$.ajax({
				url: auraCpsData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'aura_cps_update_product_quantity',
					nonce: auraCpsData.nonce,
					product_id: productId,
					quantity: quantity
				},
				success: function(response) {
					$card.removeClass('loading');

					if (response.success) {
						// Trigger WooCommerce cart update
						$(document.body).trigger('wc_fragment_refresh');

						// Update stored cart quantities
						if (typeof auraCpsData.cartQuantities !== 'undefined') {
							auraCpsData.cartQuantities[productId] = quantity;
						}
					} else {
						// Revert UI on error
						self.revertProductUI($card, productId);
						alert(response.data.message || 'Error updating cart');
					}
				},
				error: function() {
					$card.removeClass('loading');
					self.revertProductUI($card, productId);
					alert('An error occurred while updating the cart');
				}
			});
		},

		/**
		 * Revert product UI to cart state
		 */
		revertProductUI: function($card, productId) {
			const cartQuantity = (auraCpsData.cartQuantities && auraCpsData.cartQuantities[productId])
								? auraCpsData.cartQuantities[productId]
								: 0;
			this.updateProductUI($card, productId, cartQuantity);
		},

		/**
		 * Handle show more/less button click
		 */
		handleShowMore: function($button) {
			const self = this;
			const $wrapper = $button.closest('.aura-products-wrapper');
			const $grid = $wrapper.find('.aura-products-grid');
			const $btnText = $button.find('.aura-btn-text');
			const showMoreText = $button.attr('data-show-more-text') || 'Show More';
			const showLessText = $button.attr('data-show-less-text') || 'Show Less';
			const $hiddenItems = $grid.find('.aura-product-card.hidden-item');

			// Toggle expanded state
			if ($grid.hasClass('expanded')) {
				// Collapse
				$grid.removeClass('expanded');
				$btnText.text(showMoreText);

				// Scroll to top of widget
				$('html, body').animate({
					scrollTop: $wrapper.offset().top - 100
				}, 500);
			} else {
				// Expand
				$grid.addClass('expanded');
				$btnText.text(showLessText);
			}
		},

		/**
		 * Validate form fields and enable/disable confirm button
		 */
		validateForm: function($card) {
			const $detailsInput = $card.find('.aura-form-details');
			const $confirmBtn = $card.find('.aura-form-checkbox-btn');

			const details = $detailsInput.val().trim();

			// Enable button only if details field has content
			const isValid = details.length > 0;
			$confirmBtn.prop('disabled', !isValid);
		},

		/**
		 * Handle form confirm button click
		 */
		handleFormConfirm: function($button) {
			const $card = $button.closest('.aura-form-card');
			const isChecked = $button.attr('aria-checked') === 'true';

			// Get form data
			const $detailsInput = $card.find('.aura-form-details');
			const arrivalDetails = $detailsInput.val().trim();

			if (isChecked) {
				// Uncheck: unlock form, clear session, keep values
				$button.attr('aria-checked', 'false');
				$card.removeClass('confirmed');

				// Enable form field
				$detailsInput.prop('disabled', false);

				// Clear from session
				this.saveArrivalDetails('', false, $card, $button);
			} else {
				// Check: lock form, save to session
				$button.attr('aria-checked', 'true');
				$card.addClass('confirmed');

				// Disable form field
				$detailsInput.prop('disabled', true);

				// Save to session
				this.saveArrivalDetails(arrivalDetails, true, $card, $button);
			}
		},

		/**
		 * Save arrival details to session
		 */
		saveArrivalDetails: function(details, confirmed, $card, $button) {
			const self = this;

			// Add loading state
			$card.addClass('loading');

			$.ajax({
				url: auraCpsData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'aura_cps_save_arrival_details',
					nonce: auraCpsData.nonce,
					arrival_details: details,
					confirmed: confirmed
				},
				success: function(response) {
					$card.removeClass('loading');

					if (!response.success) {
						// Revert checkbox and form state on error
						const isChecked = $button.attr('aria-checked') === 'true';
						$button.attr('aria-checked', !isChecked);

						if (isChecked) {
							$card.removeClass('confirmed');
							$card.find('.aura-form-textarea').prop('disabled', false);
						} else {
							$card.addClass('confirmed');
							$card.find('.aura-form-textarea').prop('disabled', true);
						}

						alert(response.data.message || 'Error saving arrival details');
					}
				},
				error: function() {
					$card.removeClass('loading');

					// Revert checkbox and form state on error
					const isChecked = $button.attr('aria-checked') === 'true';
					$button.attr('aria-checked', !isChecked);

					if (isChecked) {
						$card.removeClass('confirmed');
						$card.find('.aura-form-textarea').prop('disabled', false);
					} else {
						$card.addClass('confirmed');
						$card.find('.aura-form-textarea').prop('disabled', true);
					}

					alert('An error occurred while saving arrival details');
				}
			});
		},

		/**
		 * Load initial quantities from cart
		 */
		loadInitialQuantities: function() {
			const self = this;

			// If cart quantities are already loaded, use them
			if (auraCpsData.cartQuantities) {
				$('.aura-product-card').each(function() {
					const $card = $(this);
					const productId = $card.attr('data-product-id');
					const quantity = auraCpsData.cartQuantities[productId] || 0;

					// Update card layout quantity
					self.updateProductUI($card, productId, quantity);

					// Update checkbox layout state
					const $checkbox = $card.find('.aura-checkbox-btn[data-product-id="' + productId + '"]');
					if ($checkbox.length) {
						$checkbox.attr('aria-checked', quantity > 0 ? 'true' : 'false');
					}

					// Update moments layout checkbox state
					const $momentsCheckbox = $card.find('.aura-moments-checkbox-btn[data-product-id="' + productId + '"]');
					if ($momentsCheckbox.length) {
						$momentsCheckbox.attr('aria-checked', quantity > 0 ? 'true' : 'false');
					}

					// Update form layout state (no cart dependency, just validate)
					const $formCheckbox = $card.find('.aura-form-checkbox-btn');
					if ($formCheckbox.length) {
						// Validate form to set button state
						self.validateForm($card);
					}
				});
				return;
			}

			// Otherwise fetch from server
			$.ajax({
				url: auraCpsData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'aura_cps_get_cart_quantities',
					nonce: auraCpsData.nonce
				},
				success: function(response) {
					if (response.success && response.data.quantities) {
						auraCpsData.cartQuantities = response.data.quantities;

						$('.aura-product-card').each(function() {
							const $card = $(this);
							const productId = $card.attr('data-product-id');
							const quantity = response.data.quantities[productId] || 0;

							// Update card layout quantity
							self.updateProductUI($card, productId, quantity);

							// Update checkbox layout state
							const $checkbox = $card.find('.aura-checkbox-btn[data-product-id="' + productId + '"]');
							if ($checkbox.length) {
								$checkbox.attr('aria-checked', quantity > 0 ? 'true' : 'false');
							}

							// Update moments layout checkbox state
							const $momentsCheckbox = $card.find('.aura-moments-checkbox-btn[data-product-id="' + productId + '"]');
							if ($momentsCheckbox.length) {
								$momentsCheckbox.attr('aria-checked', quantity > 0 ? 'true' : 'false');
							}

							// Update form layout state (no cart dependency, just validate)
							const $formCheckbox = $card.find('.aura-form-checkbox-btn');
							if ($formCheckbox.length) {
								// Validate form to set button state
								self.validateForm($card);
							}
						});
					}
				}
			});
		},

		/**
		 * Handle window resize
		 */
		handleResize: function() {
			// Update visible rows
			this.updateAllVisibleRows();
		}
	};

	/**
	 * Initialize on document ready
	 */
	$(document).ready(function() {
		AuraProductsWidget.init();
	});

	/**
	 * Re-initialize for Elementor preview
	 */
	$(window).on('elementor/frontend/init', function() {
		elementorFrontend.hooks.addAction('frontend/element_ready/aura-products.default', function($scope) {
			AuraProductsWidget.init();
		});
	});

})(jQuery);
