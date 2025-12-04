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

			// Flatpickr configuration with European display format
			// Internal format stays as Y-m-d for compatibility with villa dates
			const config = {
				mode: 'range',
				dateFormat: 'Y-m-d', // Internal format: YYYY-MM-DD (for compatibility)
				altInput: true,
				altFormat: 'd/m/Y', // Display format: DD/MM/YYYY (European)
				minDate: 'today',
				locale: {
					rangeSeparator: ' to '
				},
				onChange: function(selectedDates, dateStr, instance) {
					// dateStr will be in Y-m-d format internally
					// But display shows d/m/Y format to user
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

		console.log('───────────────────────────────────────');
		console.log('updateDateInputState - Producto ID:', productId, '| Cantidad:', quantity);

		// Enable/disable minus button based on quantity
		if (quantity === 0) {
			minusBtn.prop('disabled', true);
			minusBtn.addClass('disabled');
			console.log('  → Botón (-) DESHABILITADO');
		} else {
			minusBtn.prop('disabled', false);
			minusBtn.removeClass('disabled');
			console.log('  → Botón (-) HABILITADO');
		}

		// Enable date input only when quantity > 0
		if (quantity > 0) {
			dateInput.prop('disabled', false);
			dateInput.removeClass('disabled');
			console.log('  → Input de fechas HABILITADO (cantidad > 0)');
			console.log('  → Estado actual disabled:', dateInput.prop('disabled'));
		} else {
			dateInput.prop('disabled', true);
			dateInput.addClass('disabled');
			console.log('  → Input de fechas DESHABILITADO (cantidad = 0)');
			console.log('  → Estado actual disabled:', dateInput.prop('disabled'));
		}
		console.log('───────────────────────────────────────');
	}

	/**
	 * Handle date range change (FRONTEND ONLY - no AJAX)
	 * Only updates the visual total, does NOT modify cart
	 */
	function handleDateChange(card, selectedDates, dateStr, productId) {
		console.log('Fecha cambiada - Producto ID:', productId);

		if (selectedDates.length !== 2) {
			return;
		}

		const startDate = selectedDates[0];
		const endDate = selectedDates[1];

		// Calculate days (including both start and end date)
		const diffTime = Math.abs(endDate - startDate);
		const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

		console.log('Días calculados:', diffDays, '| Fechas:', dateStr);

		// Update card data attribute
		card.attr('data-rental-days', diffDays);

		// Recalculate total (FRONTEND ONLY)
		updateTotal(card, productId);

		console.log('Total recalculado (solo visual, NO se actualizó el carrito)');

		// NO llamar a updateCartDates - solo el checkbox modifica el carrito
	}

	/**
	 * Initialize quantity control handlers
	 * NEW LOGIC: +/- buttons only modify quantity locally (no AJAX)
	 * AJAX is triggered ONLY when user clicks the confirm checkbox
	 */
	function initQuantityControls() {
		// Plus button - increases quantity locally
		$(document).on('click', '.aura-equipment-card .aura-btn-plus', function() {
			console.log('Click en botón + detectado');

			const btn = $(this);
			const card = btn.closest('.aura-equipment-card');
			const productId = btn.attr('data-product-id');
			const currentQuantity = parseInt(card.find('.aura-quantity-display[data-product-id="' + productId + '"]').text()) || 0;
			const newQuantity = currentQuantity + 1;

			console.log('Producto ID:', productId, '| Cantidad actual:', currentQuantity, '→ Nueva:', newQuantity);

			// Update display locally
			updateQuantityDisplay(productId, newQuantity);
			updateTotal(card, productId);
			updateDateInputState(card, productId);
		});

		// Minus button - decreases quantity locally
		$(document).on('click', '.aura-equipment-card .aura-btn-minus', function() {
			console.log('Click en botón - detectado');

			const btn = $(this);
			const card = btn.closest('.aura-equipment-card');
			const productId = btn.attr('data-product-id');
			const currentQuantity = parseInt(card.find('.aura-quantity-display[data-product-id="' + productId + '"]').text()) || 0;
			const newQuantity = Math.max(0, currentQuantity - 1);

			console.log('Producto ID:', productId, '| Cantidad actual:', currentQuantity, '→ Nueva:', newQuantity);

			// Update display locally
			updateQuantityDisplay(productId, newQuantity);
			updateTotal(card, productId);
			updateDateInputState(card, productId);
		});

		// Confirm checkbox - triggers AJAX to add/remove from cart
		$(document).on('click', '.aura-equipment-confirm-btn', function() {
			console.log('Click en botón de confirmación detectado');

			const btn = $(this);
			const card = btn.closest('.aura-equipment-card');
			const productId = btn.attr('data-product-id');
			const isCurrentlyChecked = btn.attr('aria-checked') === 'true';

			console.log('Producto ID:', productId, '| Estado actual:', isCurrentlyChecked ? 'checked' : 'unchecked');

			if (isCurrentlyChecked) {
				// User is unchecking - remove from cart
				removeFromCart(productId, card, btn);
			} else {
				// User is checking - validate and add to cart
				validateAndAddToCart(productId, card, btn);
			}
		});
	}

	/**
	 * Validate and add to cart (when user checks the confirm button)
	 * Validates: quantity > 0 AND dates are selected
	 */
	function validateAndAddToCart(productId, card, confirmBtn) {
		console.log('═══════════════════════════════════════');
		console.log('validateAndAddToCart - Producto ID:', productId);

		const quantity = parseInt(card.find('.aura-quantity-display[data-product-id="' + productId + '"]').text()) || 0;
		const dateInput = card.find('.aura-equipment-date-range');
		const dateStr = dateInput.val();

		console.log('Cantidad:', quantity, '| Fechas:', dateStr);

		// Validation 1: Check quantity > 0
		if (quantity === 0) {
			console.error('❌ Validación fallida: cantidad = 0');
			showNotification('Please add some equipment to rent', 'error');
			return;
		}

		// Validation 2: Check dates are selected
		if (!dateStr || !dateStr.includes(' to ')) {
			console.error('❌ Validación fallida: fechas no seleccionadas');
			showNotification('Please select rental dates', 'error');
			return;
		}

		// Parse dates
		const dates = dateStr.split(' to ');
		const startDate = dates[0].trim();
		const endDate = dates[1].trim();

		console.log('✓ Validación exitosa - Agregando al carrito...');
		console.log('  Cantidad:', quantity, '| Inicio:', startDate, '| Fin:', endDate);

		card.addClass('loading');
		confirmBtn.prop('disabled', true);

		// Add to cart via AJAX
		$.ajax({
			url: auraCPS.ajaxUrl,
			type: 'POST',
			data: {
				action: 'aura_cps_add_equipment_rental',
				nonce: auraCPS.nonce,
				product_id: productId,
				quantity: quantity,
				rental_start_date: startDate,
				rental_end_date: endDate
			},
			success: function(response) {
				card.removeClass('loading');
				confirmBtn.prop('disabled', false);

				if (response.success) {
					console.log('✓ Producto agregado al carrito exitosamente');
					// Mark as checked
					confirmBtn.attr('aria-checked', 'true');
					console.log('═══════════════════════════════════════');
				} else {
					console.error('❌ Error al agregar al carrito:', response);
					showNotification('Error adding to cart', 'error');
					console.log('═══════════════════════════════════════');
				}
			},
			error: function(xhr, status, error) {
				card.removeClass('loading');
				confirmBtn.prop('disabled', false);
				console.error('❌ Error AJAX:', { status, error, response: xhr.responseText });
				showNotification('Error adding to cart', 'error');
				console.log('═══════════════════════════════════════');
			}
		});
	}

	/**
	 * Remove from cart (when user unchecks the confirm button)
	 */
	function removeFromCart(productId, card, confirmBtn) {
		console.log('═══════════════════════════════════════');
		console.log('removeFromCart - Producto ID:', productId);

		card.addClass('loading');
		confirmBtn.prop('disabled', true);

		// Remove from cart via AJAX
		$.ajax({
			url: auraCPS.ajaxUrl,
			type: 'POST',
			data: {
				action: 'aura_cps_remove_equipment_rental',
				nonce: auraCPS.nonce,
				product_id: productId
			},
			success: function(response) {
				card.removeClass('loading');
				confirmBtn.prop('disabled', false);

				if (response.success) {
					console.log('✓ Producto eliminado del carrito exitosamente');
					// Mark as unchecked
					confirmBtn.attr('aria-checked', 'false');
					// Reset quantity to 0
					updateQuantityDisplay(productId, 0);
					updateTotal(card, productId);
					updateDateInputState(card, productId);
					console.log('═══════════════════════════════════════');
				} else {
					console.error('❌ Error al eliminar del carrito:', response);
					showNotification('Error removing from cart', 'error');
					console.log('═══════════════════════════════════════');
				}
			},
			error: function(xhr, status, error) {
				card.removeClass('loading');
				confirmBtn.prop('disabled', false);
				console.error('❌ Error AJAX:', { status, error, response: xhr.responseText });
				showNotification('Error removing from cart', 'error');
				console.log('═══════════════════════════════════════');
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
