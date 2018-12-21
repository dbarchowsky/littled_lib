(function ($) {
	
	/**
	 * static properties 
	 */
	let settings = {
		uris: {
			keyword_autocomplete: 'utils/keyword_autocomplete.php',
			record_details: 'details.php'
		},
		keys: {
			record_id: 'id',
			parent_id: 'pid',
			content_type: 'tid',
			page: 'p',
			operation: 'op',
			csrf: 'csrf',
		},
		dom: {
			/* start using these over the top-level settings */
			listings_container: '.listings',
			page_error_container: '.alert-error:first',
			error_container: '.alert-error',
			filters_form: '.listings-filters:first form',
			csrf_selelctor: '#csrf-token'
		},
		selectCallback: null
	};

	let methods = {

		/**
		 * sets up autocomplete for the keyword filter textbox 
		 * @param {object} options (Optional) collection of settings that will
		 * override the library's default settings.
		 */
		bindAutocomplete: function(options) {

			let lclSettings = $.extend(true, {}, settings, options || {});

			if (typeof(lclSettings.selectCallback) !== 'function') {
				lclSettings.selectCallback = methods.selectCallback;
			}

			return this.each(function() {

				let name = $(this).prop('name');
				let fd = {};
				fd[lclSettings.keys.content_type] = $(this).data(lclSettings.keys.content_type);
				fd[lclSettings.keys.csrf] = $(lclSettings.dom.csrf_selector).html();

				$(this).autocomplete({
					minLength: 3,
					source: function(request, response) {
						fd[name] = request.term;
						$.ajax({
							type: 'post',
							url: $.littled.getRelativePath() + lclSettings.ajax.script_path + lclSettings.uris.keyword_autocomplete,
							data: fd,
							dataType: 'json'
						})
						.success(function(data) {
							if (data.error) {
								$(lclSettings.dom.page_error_container).littled('displayError', data.error);
								return;
							}
							response($.map(data, function(item) {
								return ({
									item: item.id,
									label: item.title,
									value: item.id,
									term: request.term
								});
							}));
						})
						.fail(function(xhr) {
							$(lclSettings.dom.page_error_container).littled('ajaxError', xhr);
						});
					},
					select: lclSettings.selectCallback
				});
			});
		},
		
		selectCallback: function(event, ui) {

			let lclSettings = $.extend(true, {}, settings, event.data || {});

			/* update the textbox value with the name of the matching item, not its numeric id */ 
			event.preventDefault();
			$(this).val(ui.item.term+'*');

			/* redirect to details page for the selected item, preserving the current listings filters */
			let id = ui.item.value;
			let fa = $(lclSettings.dom.filters_form).serializeArray();
			let qa = $.map(fa, function(item) {
				return(item.name+'='+encodeURIComponent(item.value));
			});
			window.location = lclSettings.uris.record_details+'?'+qa.join('&')+'&id='+id;
		}
	};
	
	$.fn.keywordFilter= function( method ) {
	
		/* method calling logic */
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		}
		else if ( typeof method === 'object' || !method ) {
			return methods.init.apply(this, arguments);
		}
		else {
			$.error('Method ' + method + ' does not exist on jQuery.keywordFilter.');
		}
		return (false);
	};
	
}) ( jQuery );

