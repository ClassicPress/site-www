'use strict';

jQuery(document).ready(function($) {
	cpdo_init();

	$(document).on('click touch mouseover', '.cpdo-variations', function() {
		$(this).attr('data-click', 1);
	});

	$('body').on('click', '.cpdo-variation-radio', function() {
		var _this = $(this);
		var _variations = _this.closest('.cpdo-variations');
		var _click = parseInt(_variations.attr('data-click'));
		var _variations_form = _this.closest('.variations_form');
		
		cpdo_do_select(_this, _variations, _variations_form, _click);

		_this.find('input[type="radio"]').prop('checked', true);
		_this.addClass('active').siblings().removeClass('active');
	});
});

jQuery(document).on('found_variation', function(e, t) {
	var variation_id = t['variation_id'];
	var $variations_default = jQuery(e['target']).find('.cpdo-variations-default');

	if ($variations_default.length) {
		// radio
		if ( parseInt($variations_default.attr('data-click')) < 1 ) {
			$variations_default.find( '.cpdo-variation-radio[data-id="' + variation_id + '"] input[type="radio"]').prop('checked', true);
		}
	}
});

function cpdo_init() {
	jQuery('.cpdo-variations').each(function() {
		var _variations = jQuery(this);
		var _variations_form = jQuery(this).closest('.variations_form');
	});
}

function cpdo_do_select(selected, variations, variations_form, click) {
	if (click > 0) {
		if (!variations.closest('.cpdo_variations_form').length) {
			// reset first
			variations_form.find('.reset_variations').trigger('click');

			if (selected.attr('data-attrs') !== '') {
				var attrs = jQuery.parseJSON(selected.attr('data-attrs'));

				if (attrs !== null) {
					for (var key in attrs) {
						variations_form.find('select[name="' + key + '"]').val(attrs[key]).trigger('change');
					}
				}
			}
		}
	}

	jQuery(document).trigger('cpdo_selected', [selected, variations, variations_form]);
}
