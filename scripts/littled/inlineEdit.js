(function ($) {

	var settings = {
		edit_uri: '',	/* to be defined with init() */
		input_key: '',	/* to be defined with init() */
		events: {
			to_edit: 'dblclick',
			to_commit: 'change'
		},
		dom: {
			edit_container: '.inline-edit',
			status_container: '.alert-info',
			page_error_container: '.alert-error:first',
			error_container: '.alert-error',
			csrf_container: '#csrf-token'
		}
	};

	var methods = {

		init: function( options ) {
			var lclSettings = $.extend({}, settings, options || {});
			return this.each(function() {
				$(this)
				.off(lclSettings.events.to_edit, methods.edit)
				.on(lclSettings.events.to_edit, options, methods.edit);
			});
		},

		/**
		 * Handler for editing "in stock" values inline.
		 * @param {eventObject} evt
		 * @returns {undefined}
		 */
		edit: function( evt ) {

			evt.preventDefault();
			var options = evt.data;
			var lclSettings = $.extend(true, {}, settings, options || {});

			var $e = $(this);
			var d = {
				id: $(this).attr('data-id'),
				csrf: $(lclSettings.dom.csrf_container).html()
			};

            $.ajax({
				type: 'post',
				url: lclSettings.edit_uri,
                data: d,
				dataType: 'json'
			})
			.success(function(data) {
				if (data.error) {
					$(lclSettings.dom.error_container, $e).littled('displayError', data.error);
					return;
				}
				$e.html($.littled.htmldecode(data.content));
				switch(lclSettings.events.to_commit) {
					case 'change':
						$('select[name='+lclSettings.input_key+']').on('change', options, methods.commitEdit);
						break;
					case 'key':
						; /* tk */
						break;
					default:
						$(lclSettings.dom.page_error_container).littled('displayError', 'Unhandled event.');
				}
			})
			.fail(function(xhr, status, error) {
				$(lclSettings.dom.page_error_container).littled('displayError', status+': '+error.message);
			});
		},

		commitEdit: function( evt ) {

			evt.preventDefault();
			var lclSettings = $.extend(true, {}, settings, evt.data || {});

			var d = $(this).closest('form').serializeObject();
			$.extend(d, { csrf: $(lclSettings.dom.csrf_container).html()});
			
			var $e = $(this);
						
			$.ajax({
				type: 'post',
				url: lclSettings.edit_uri,
				data: d,
				dataType: 'json'
			})
			.success(function(data) {
				$e.closest(lclSettings.dom.edit_container).littled('displayAjaxResult', data);
			})
			.fail(function(xhr, status, error) {
				$(lclSettings.dom.page_error_container).littled('displayError', status+': '+error.message);
			});
		}
	};
 
	$.fn.inlineEdit = function( method ) {
 
		/* method calling logic */
		if (method === 'extendMethods') {
			$.extend(true, methods, arguments[1]);
		}
		else if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		}
		else if ( typeof method === 'object' || !method ) {
			return methods.init.apply(this, arguments);
		}
		else {
			$.error('Method ' + method + ' does not exist on jQuery.inlineEdit.');
		}
		return (false);
	};
}) ( jQuery );