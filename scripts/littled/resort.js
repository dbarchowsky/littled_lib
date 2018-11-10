(function ($) {
	
	/**
	 * static properties 
	 */
	var settings = {
		uris: {
			resort: '/_ajax/utils.resort.php'
		},
		dom: {
			listings_container: '.listings',
			sortable_selector: 'tr.rec-row',
			status_container: '.alert-info:first',
			page_error_container: '.alert-error:first',
			error_container: '.alert-error',
			filters_form: '.listings-filters:first form',
			csrf_selector: '#csrf-token'
		},
		keys: {
			record_id: 'id',
			parent_id: 'pid',
			content_type: 'tid',
			page_size: 'pl',
			position_offset: 'po',
			operation: 'op',
			csrf: 'csrf'
		}
	};

	var methods = {

		init: function( options ) {

			var lclSettings = $.extend(true, {}, settings, options || {});

			return this.each(function() {
				
				$(this)
				.sortable({
					items: lclSettings.dom.sortable_selector,
					start: function(evt, ui) {

						$(lclSettings.dom.status_container+':visible').fadeOut('fast');

						var id = ui.helper.data('rid');
						if (!id) {return;} /* parent record id not set */

						var $c = $('#pages-row-' + id);
						if ($c.length < 1) {return;} /* child row not available */

						/* hide children as the parent is dragged */
						if ($c.is('visible')) {
							$c.addClass('reopen');
							$c.hide();
						}
					},
					update: function(evt, ui) {
						evt.data = options;
						$(this).resort('resort', evt, ui); 
					}
				});
			});
		},
		
		/**
		 * @deprecated Use default init() method in its place.
		 */
		bindResort: function( ) {
			return methods.init.apply(this, arguments);
		},

		/**
		 * Callback for the jQuery .sortable handler.
		 * @param {Event} evt
		 * @param {Object} ui information about the element being manipulated
		 */
        resort: function(evt, ui) {

			var options = evt.data || {};
			var lclSettings = $.extend(true, {}, settings, options);

			return this.each(function() {

				$(lclSettings.dom.page_error_container).hide();

				var edit_selector = ui.item.attr('id');
				if (edit_selector) {
					edit_selector = '#' + edit_selector;
				}
				$(this).resort('refreshSortableRowStyles', edit_selector);

				/* prevent any more edits until db has been updated */
				$(this).sortable('disable');

				/* show spinning wheel while waiting for ajax response */
				$(this).parent().children('.progress-msg')
				.show()
				.position({
					my: 'center',
					at: 'center',
					of: $(this).parent()
				});

				/* retrieve listings properties */
				var content_type_id = $(this).data(lclSettings.keys.content_type);
				var position_offset = $(this).data(lclSettings.keys.position_offset);
				if (content_type_id === undefined || position_offset === undefined) {
					$(lclSettings.dom.page_error_container).littled('displayError', 'Section id or page offset values not set!');
					$(this).resort('rollbackSort', edit_selector);
					return;
				}

				/* retrieve row id's */
				var arID = new Array();
				var a = $(this).sortable('toArray');
				if (a.length > 0) {

					var rid;
					for (var id in a) {

						if (a[id]) {
							/* retrieve record id's in their new positions */
							rid = $('#' + a[id]).data('rid');
							if (parseInt(rid) > 0) {
								arID.push(rid);
							}
						}
					}
				}

				if (arID.length < 1) {
					/* nothing found to sort */
					$(this).resort('rollbackSort', edit_selector);
					return;
				}
				
				var $e = $(this);

				$.littled.retrieveContentOperations(content_type_id, function(data1) {

					if (data1.error) {
						$(lclSettings.dom.page_error_container).littled('displayError', data1.error);
						return;
					} else if (!data1.sorting_uri) {
						$(lclSettings.dom.page_error_container).littled('displayError', 'Handler not defined.');
						return;
					}

					var fd = {}, $f = $(lclSettings.dom.filters_form);
					if ($f.length > 0) {
						fd = $f.littled('collectFormDataWithCSRF', lclSettings);
					}
					var extras = {
						ssid: content_type_id,
						rid: edit_selector
					};
					extras[lclSettings.keys.parent_id] = $(this).data(lclSettings.keys.parent_id);
					extras[lclSettings.keys.record_id] = JSON.stringify(arID);
					extras[lclSettings.keys.position_offset] = position_offset;
					$.extend(fd, extras);

					/* ajax call to re-sort list */
					$.post(
						data1.sorting_uri,
						fd,
						function(data) {
							data.settings = options || {};
							$e.resort('onResortComplete', data); 
						},
						'json'
					)
					.fail(function(xhr) {
						$(lclSettings.dom.page_error_container).littled('ajaxError', xhr);
					});
				});
			});
        },

		refreshSortableRowStyles: function(edit_selector) {

			return this.each(function() {

				if ($(this).is('table')===false) {
					return;
				}

				/* refresh row styles with alternating background colors */
				$('tr.rec-row:odd', $(this)).removeClass('list list-alt').addClass('list');
				$('tr.rec-row:even', $(this)).removeClass('list list-alt').addClass('list-alt');
				$('tr.page-row:odd', $(this)).removeClass('list list-alt').addClass('list');
				$('tr.page-row:even', $(this)).removeClass('list list-alt').addClass('list-alt');

				if (edit_selector) {

					var $e = $(edit_selector);
					if ($e.length < 1) {return;} /* parent row */

					var $prev = $e.prev();
					if ($prev.length < 0) {return;} /* previous row */

					var id = $prev.data('rid');
					var $c = $('#pages-row-' + id); /* previous row's child */
					if ($c.length > 0) {
					if ($c.is(':hidden')) { /* no need to move it if it's visible */
						/* return the previous row's child row to its position below its parent */
						$c.insertAfter($prev);
					}
				}

					id = $e.data('rid');
					$c = $('#pages-row-' + id); /* dragged row's child */
					if ($c.length < 1) {return;} /* element not available */

					/* move the dragged row's child row back under the dragged row */
					$c.insertAfter($e);

					/* if the child row was expanded previously, re-open it */
					if ($c.hasClass('reopen')) {
						$c.slideToggle();
						$c.removeClass('reopen');
					}
				}
			});
		},

        onResortComplete: function(data) {

			var options = data.settings || {};
			var lclSettings = $.extend(true, {}, settings, options);
			return this.each(function() {
				$(this).parent().children('.progress-msg').hide();

				if (data.error) {
					$(this).resort('rollbackSort', data.id);
					$(lclSettings.dom.page_error_container).littled('displayError', data.error);
					return;
				}

				$(this)
				.resort('refreshSortableRowStyles')
				.sortable('enable');

				if (data.status) {
					$(lclSettings.dom.status_container).littled('displayStatus', data.status);
				}
			});
        },


        onResortFailure: function(xhr, options) {
			var lclSettings = $.extend({}, settings, options || {});
            $(lclSettings.dom.page_error_container).littled('displayError', xhr.responseText);
            return this.each(function() {
				$(this).parent().children('.progress-msg').hide();
				$(this).sortable('enable');
			});
        },

		
		rollbackSort: function(edit_selector, $w) {

			if ($w) {
				$w.hide();
			}
			return this.each(function() {
				$(this)
				.sortable('cancel')
				.sortable('enable')
				.resort('refreshSortableRowStyles', edit_selector);
			});
        }
	};
	
	$.fn.resort = function( method ) {
	
		/* method calling logic */
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		}
		else if ( typeof method === 'object' || !method ) {
			return methods.init.apply(this, arguments);
		}
		else {
			$.error('Method ' + method + ' does not exist on jQuery.resort.');
		}
		return (false);
	};
	
}) ( jQuery );
