/**
 * Aura Custom Product Style - Equipment Rental Layout
 *
 * Handles:
 * - Flatpickr date range selection per product
 * - Total calculation: Quantity × Price × Days
 * - AJAX cart operations with rental dates metadata
 *
 * @package Aura_Custom_Product_Style
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		initEquipmentLayout();
	});

	/**
	 * Initialize Equipment Layout
	 */
	function initEquipmentLayout() {
		initDateRangePickers();
		initQuantityControls();
		initDateInputStates();
	}

	/**
	 * Initialize Flatpickr for each equipment item
	 */
	function initDateRangePickers() {
		const equipmentCards = $('.aura-equipment-card');

		if (equipmentCards.length === 0) {
			return;
		}

		// Get booking dates from grid data attributes
		const grid = $('.aura-equipment-wrapper .aura-products-grid');
		const startDate = grid.attr('data-start-date') || '';
		const endDate = grid.attr('data-end-date') || '';
		const hasBooking = startDate && endDate;

		equipmentCards.each(function() {
			const card = $(this);
			const dateInput = card.find('.aura-equipment-date-range');
			const productId = card.attr('data-product-id');

			if (!dateInput.length) {
				return;
			}

			// Flatpickr configuration
			const config = {
				mode: 'range',
				dateFormat: 'Y-m-d',
				altInput: true,
				altFormat: 'F j, Y',
				minDate: 'today',
				locale: {
					rangeSeparator: ' to '
				},
				onChange: function(selectedDates, dateStr, instance) {
					handleDateChange(card, selectedDates, dateStr, productId);
				}
			};

			// If villa has booking dates, restrict to that range
			if (hasBooking) {
				config.minDate = startDate;
				config.maxDate = endDate;
			}

			// Initialize Flatpickr and store instance
			const flatpickrInstance = flatpickr(dateInput[0], config);
			dateInput.data('flatpickr', flatpickrInstance);
		});
	}

	/**
	 * Initialize date input states based on quantity
	 */
	function initDateInputStates() {
		$('.aura-equipment-card').each(function() {
			const card = $(this);
			const productId = card.attr('data-product-id');
			updateDateInputState(card, productId);
		});
	}

	/**
	 * Update date input state (enabled/disabled) based on quantity
	 *
	 * Logic: Date input should only be active when quantity > 0
	 * When quantity is 0, disable the date input
	 * When quantity becomes > 0, enable the date input
	 */
	function updateDateInputState(card, productId) {
		const quantity = parseInt(card.find('.aura-quantity-display[data-product-id="' + productId + '"]').text()) || 0;
		const dateInput = card.find('.aura-equipment-date-range');
		const minusBtn = card.find('.aura-btn-minus[data-product-id="' + productId + '"]');

		// Enable/disable minus button based on quantity
		if (quantity === 0) {
			minusBtn.prop('disabled', true);
			minusBtn.addClass('disabled');
		} else {
			minusBtn.prop('disabled', false);
			minusBtn.removeClass('disabled');
		}

		// Enable date input only when quantity > 0
		if (quantity > 0) {
			dateInput.prop('disabled', false);
			dateInput.removeClass('disabled');
		} else {
			dateInput.prop('disabled', true);
			dateInput.addClass('disabled');
		}
	}

	/**
	 * Handle date range change
	 */
	function handleDateChange(card, selectedDates, dateStr, productId) {
		if (selectedDates.length !== 2) {
			return;
		}

		const startDate = selectedDates[0];
		const endDate = selectedDates[1];

		// Calculate days (including both start and end date)
		const diffTime = Math.abs(endDate - startDate);
		const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

		// Update card data attribute
		card.attr('data-rental-days', diffDays);

		// Recalculate total
		updateTotal(card, productId);

		// Update date input state (keep enabled now that dates are selected)
		updateDateInputState(card, productId);

		// Get current quantity
		const quantity = parseInt(card.find('.aura-quantity-display[data-product-id="' + productId + '"]').text()) || 0;

		// If quantity > 0, update cart with new dates
		if (quantity > 0) {
			updateCartDates(productId, dateStr.split(' to ')[0], dateStr.split(' to ')[1], card);
		}
	}

	/**
	 * Initialize quantity control handlers
	 */
	function initQuantityControls() {
		// Plus button
		$(document).on('click', '.aura-equipment-card .aura-btn-plus', function() {
			const btn = $(this);
			const card = btn.closest('.aura-equipment-card');
			const productId = btn.attr('data-product-id');
			const dateInput = card.find('.aura-equipment-date-range');
			const dateStr = dateInput.val();
			const currentQuantity = parseInt(card.find('.aura-quantity-display[data-product-id="' + productId + '"]').text()) || 0;

			// If quantity > 0, check if dates are selected
			if (currentQuantity > 0) {
				if (!dateStr || !dateStr.includes(' to ')) {
					// Open the date picker to help user
					const flatpickrInstance = dateInput.data('flatpickr');
					if (flatpickrInstance) {
						setTimeout(function() {
							flatpickrInstance.open();
						}, 100);
					}
					return;
				}
			}

			// Get dates if they exist, otherwise use empty strings (will be set later)
			let startDate = '';
			let endDate = '';
			if (dateStr && dateStr.includes(' to ')) {
				const dates = dateStr.split(' to ');
				startDate = dates[0].trim();
				endDate = dates[1].trim();
			}

			increaseQuantity(productId, startDate, endDate, card);
		});

		// Minus button
		$(document).on('click', '.aura-equipment-card .aura-btn-minus', function() {
			const btn = $(this);
			const card = btn.closest('.aura-equipment-card');
			const productId = btn.attr('data-product-id');

			decreaseQuantity(productId, card);
		});
	}

	/**
	 * Increase quantity and add/update cart
	 */
	function increaseQuantity(productId, startDate, endDate, card) {
		card.addClass('loading');

		$.ajax({
			url: auraCPS.ajaxUrl,
			type: 'POST',
			data: {
				action: 'aura_cps_increase_equipment',
				nonce: auraCPS.nonce,
				product_id: productId,
				rental_start_date: startDate,
				rental_end_date: endDate
			},
			success: function(response) {
				card.removeClass('loading');

				if (response.success) {
					const newQuantity = response.data.quantity;
					updateQuantityDisplay(productId, newQuantity);
					updateTotal(card, productId);
					updateDateInputState(card, productId);
				} else {
					const message = response.data && response.data.message ? response.data.message : 'Error updating cart';
					showNotification(card, message, 'error');
				}
			},
			error: function() {
				card.removeClass('loading');
				showNotification(card, 'Error updating cart', 'error');
			}
		});
	}

	/**
	 * Decrease quantity or remove from cart
	 */
	function decreaseQuantity(productId, card) {
		card.addClass('loading');

		$.ajax({
			url: auraCPS.ajaxUrl,
			type: 'POST',
			data: {
				action: 'aura_cps_decrease_equipment',
				nonce: auraCPS.nonce,
				product_id: productId
			},
			success: function(response) {
				card.removeClass('loading');

				if (response.success) {
					const newQuantity = response.data.quantity;
					updateQuantityDisplay(productId, newQuantity);
					updateTotal(card, productId);
					updateDateInputState(card, productId);
				} else {
					const message = response.data && response.data.message ? response.data.message : 'Error updating cart';
					showNotification(card, message, 'error');
				}
			},
			error: function() {
				card.removeClass('loading');
				showNotification(card, 'Error updating cart', 'error');
			}
		});
	}

	/**
	 * Update cart dates (when user changes dates after adding to cart)
	 */
	function updateCartDates(productId, startDate, endDate, card) {
		$.ajax({
			url: auraCPS.ajaxUrl,
			type: 'POST',
			data: {
				action: 'aura_cps_update_equipment_dates',
				nonce: auraCPS.nonce,
				product_id: productId,
				rental_start_date: startDate,
				rental_end_date: endDate
			},
			success: function(response) {
				if (response.success) {
					updateTotal(card, productId);
				}
			}
		});
	}

	/**
	 * Update quantity display
	 */
	function updateQuantityDisplay(productId, newQuantity) {
		$('.aura-quantity-display[data-product-id="' + productId + '"]').text(newQuantity);
	}

	/**
	 * Update total price (Quantity × Price × Days)
	 */
	function updateTotal(card, productId) {
		const quantity = parseInt(card.find('.aura-quantity-display[data-product-id="' + productId + '"]').text()) || 0;
		const price = parseFloat(card.attr('data-product-price')) || 0;
		const days = parseInt(card.attr('data-rental-days')) || 1;

		const total = quantity * price * days;

		// Update total display
		const totalElement = card.find('.aura-total-price[data-product-id="' + productId + '"]');
		const currencySymbol = totalElement.text().match(/[^\d.,\s]+$/)?.[0] || '';
		totalElement.html(total.toFixed(2) + ' ' + currencySymbol);
	}

	/**
	 * Show notification
	 */
	function showNotification(card, message, type) {
		// Remove any existing notification
		card.find('.aura-equipment-notification').remove();

		// Create notification element
		const notification = $('<div class="aura-equipment-notification ' + type + '">' + message + '</div>');

		// Add to card
		card.append(notification);

		// Show with animation
		setTimeout(function() {
			notification.addClass('show');
		}, 10);

		// Hide after 3 seconds
		setTimeout(function() {
			notification.removeClass('show');
			setTimeout(function() {
				notification.remove();
			}, 300);
		}, 3000);
	}

})(jQuery);
