(function ($) {
	
	/**
	 * static properties 
	 */
	var settings = {
		displayWarnings: true,
		inlineOps: {
			nameURL: '_ajax/utils/edit_name.php',
			dateURL: '_ajax/utils/edit_date.php',
			accessURL: '_ajax/utils/edit_access.php',
			slotURL: '_ajax/utils/edit_slot.php',
			pageURL: '_ajax/utils/edit_page.php',
			statusURL: '_ajax/utils/edit_status.php'
		},
		uris: {
			/* refactor "inlineOps" and use this collection in its place */
			edit_name: '',
			record_details: 'details.php'
		},
		listingsContainer: '[deprecated]',
		pagesContainer: '[deprecated]', /* selector used to locate filters for ajax edits */
		errorContainer: '[deprecated]',
		statusContainer: '[deprecated]',
		filtersSelector: '[deprecated]',
		sortableSelector: '[deprecated]',
		keys: {
			record_id: 'id',
			parent_id: 'pid',
			content_type: 'tid',
			page: 'p',
			operation: 'op',
			csrf: 'csrf'
		},
		dom: {
			/* start using these over the top-level settings */
			listings_container: '.listings',
			pages_container: '.pages-container',
			page_error_container: '.alert-error:first',
			error_container: '.alert-error',
			status_container: '.alert-info:first',
			filters_form: '.listings-filters:first form',
			sortable_selector: 'tr.rec-row',
			csrfSelector: '#csrf-token'
		}
	};

	var methods = {

        bindListingsHandlers: function (options) {

			var lclSettings = $.extend(true, {}, settings, options || {});

			return this.each(function() {

				$(this)

				/* re-bind these handlers if the listings content changes */
				.off('contentUpdate', methods.listingsUpdateCB)
				.on('contentUpdate', lclSettings, methods.listingsUpdateCB)

				/* enable inline edits on dbl-click */
				.off('dblclick', '.inline-edit-cell', methods.editCell)
				.on('dblclick', '.inline-edit-cell', options, methods.editCell)

				/* page navigation */
				.off('click', '.page-btn', methods.gotoPage)
				.on('click', '.page-btn', options, methods.gotoPage)

				/* non-anchor elements that link to details pages */
				.off('click', '.details-cell', methods.gotoDetailsPage)
				.on('click', '.details-cell', options, methods.gotoDetailsPage)

				/* other utility handlers */
				.off('click', 'button.update-cache-btn', $.littled.updateCache)
				.on('click', 'button.update-cache-btn', {
					errorContainer: lclSettings.dom.error_container,
					statusContainer: lclSettings.dom.status_container
				}, $.littled.updateCache);

				/* ajax edits within dialogs */
                $('button.edit-btn', $(this))
				.formDialog('cancel', options)
				.formDialog(options);
                $('button.trash-btn', $(this))
				.formDialog('cancel', options)
				.formDialog($.extend({}, options, {
					dialogClass: 'ui-dialog-delete'
                }));

				/* utility buttons */
                $('.edit-btn', $(this)).iconButton('edit');
				$('.details-btn', $(this)).iconButton('details');
				$('.preview-btn', $(this)).iconButton('link');
				$('.print-btn', $(this)).iconButton('print');
				$('.update-cache-btn', $(this)).iconButton('cache');
                $('.trash-btn', $(this)).iconButton('trash');
            });
        },
		
		listingsUpdateCB: function( evt ) {
			$(this).listings('bindListingsHandlers', (evt.data || {}));
		},

        editCell: function( evt ) {

			evt.preventDefault();
			var lclSettings = $.extend(true, {}, settings, evt.data || {});

            var $p = $(this).parent();
			var op = $(this).data(lclSettings.keys.operation);
			var fd = {t: $(this).data('t')};
			fd[lclSettings.keys.record_id] = $(this).data(lclSettings.keys.record_id),
			fd[lclSettings.keys.operation] = op;
			fd[lclSettings.keys.csrf] = $(lclSettings.dom.csrfSelector).html();
            var url = methods.getInlineURL(op, evt.data||{});

            $.post(
				url,
                fd,
                function(data) {
					if (data.error) {
						$(lclSettings.dom.error_container).littled('displayError', data.error);
						return;
					}
					
					/* update cell content */
					$p.html($.littled.htmldecode(data.content));
					$p.listings('bindEditHandlers');
                    
					if (op==='date') {
                        $('.datepicker', $p).datepicker({
                            onClose: methods.saveEdit
                        });
                    }
                },
				'json'
			)
            .fail(function(xhr) {
				$(lclSettings.dom.error_container).littled('ajaxError', xhr);
            });
        },

        bindEditHandlers: function() {
			return this.each(function() {

				$('input[type=text],input[type=number]', $(this))
				.off('keyup', methods.testKeyedEntry)
				.off('keypress', methods.testKeyedEntry)
				.off('commit', methods.saveEdit)
				.on('keyup', methods.testKeyedEntry)		/* for escape key */
				.on('keypress', methods.testKeyedEntry)		/* for enter key */
				.on('commit', methods.saveEdit);

				$('select', $(this))
				.off('change', methods.saveEdit)
				.on('change', methods.saveEdit);
			});
        },
		
		testKeyedEntry: function(evt) {

            var id = $(this).data('id');
            var t = $(this).data('t');
            var op = $(this).data('op');
			var $p = $(this).closest('.inline-edit-cell').parent();

            var kp = ((window.event) ? (window.event.keyCode) : (evt.which));
            switch (kp) {

                case 13:

                    /* save on enter key */
					evt.preventDefault();
                    $(this).trigger('commit');
                    break;

                case 27:

                    /* cancel on escape key */
					evt.preventDefault();
					var url = methods.getInlineURL(op);
                    $.ajax({
                        type: 'get',
                        url: url,
                        data: {id: id, t: t, op: op, cancel: 1},
                        dataType: 'json',
                        success: function(data) {
							if (data.error) {
								$(settings.dom.error_container).littled('displayError', data.error);
								return;
							}
							$p.html($.littled.htmldecode(data.content));
                        },
                        error: function(xhr) {
							$(settings.dom.error_container).littled('ajaxError', xhr);
						}
                    });
                    break;
                default:
                    /* continue with all other keys */
            }
        },

        saveEdit: function(options) {

			var lclSettings = $.extend({}, settings, options || {});
            var op = $(this).data('op');
            var $f = $(this).closest('form');
			var $p = $(this).closest('.inline-edit-cell').parent();
            var url = methods.getInlineURL(op, options);

            $.ajax({
                type: 'post',
                url: url,
                data: $f.serializeObject(),
                dataType: 'json',
                success: function(data) {
					if (data.error) {
						$(lclSettings.dom.error_container).littled('displayError', data.error);
						return;
					}
					$p.html($.littled.htmldecode(data.content));
					$('.inline-edit-cell', $p)
					.off('dblclick', methods.editCell)
					.on('dblclick', methods.editCell);
				},
                error: function(xhr) {
					$(lclSettings.dom.error_container).littled('ajaxError', xhr);
				}
            });
        },
		
		getInlineURL: function(op, options) {
			var lclSettings = $.extend({}, settings, options || {});
			var url;
            switch (op) {
                case 'name':
                    url = $.littled.getRelativePath() + lclSettings.inlineOps.nameURL;
                    break;
                case 'date':
                    url = $.littled.getRelativePath() + lclSettings.inlineOps.dateURL;
                    break;
                case 'access':
                    url = $.littled.getRelativePath() + lclSettings.inlineOps.accessURL;
                    break;
                case 'slot':
                    url = $.littled.getRelativePath() + lclSettings.inlineOps.slotURL;
                    break;
                case 'page':
                    url = $.littled.getRelativePath() + lclSettings.inlineOps.pageURL;
                    break;
                case 'status':
                    url = $.littled.getRelativePath() + lclSettings.inlineOps.statusURL;
                    break;
                default:
                    $(settings.dom.error_container).littled('displayError', 'Invalid operation: ' + op);
            }
			return (url);
		},

		saveDate: function(evt, dateText, inst) {

			var options = evt.data || {};
			var lclSettings = $.extend(true, {}, settings, options);

            var $f = $(this).parents('form:first');
            $.post(
				$.littled.getRelativePath() + settings.date_url,
                $f.serializeObject(),
                function(data) {
					data.settings = options;
					$(lclSettings.dom.listings_container).listings('updateListingsContent', data);
				},
				'json'
			)
			.fail(function(xhr) {
				$(lclSettings.dom.page_error_container).littled('ajaxError', xhr);
            });
        },

		updateListingsContent: function(data) {

			var lclSettings = $.extend(true, {}, settings, data.settings || {});

			if (data.error) {
				$(lclSettings.dom.error_container).littled('displayError', data.error);
				return;
			}

			var selector = lclSettings.dom.listings_container || data.container_id;
			var $e = $(selector);
			if ($e.length > 0 ) {
				$e
				.html($.littled.htmldecode(data.content))
				.triggerHandler('contentUpdate');
			}
			else {
				if (lclSettings.displayWarnings === true) {
					$(lclSettings.dom.error_container).littled('displayError', 'Content container not found: "'+selector+'"');
				}
			}
			$(lclSettings.dom.status_container).littled('displayStatus', data.status);
		},

		/**
		 * Handler for button elements that navigate to different pages within
		 * the listings.
		 * @param {Event} evt
		 * @returns {undefined}
		 */
        gotoPage: function( evt ) {

			evt.preventDefault();
			var options = evt.data || {};
			var lclSettings = $.extend(true, {}, settings, options);

			/* clear any status messages */
			$(lclSettings.dom.status_container).fadeOut('fast');
			
			/* pointer to element within listings that stores the content type
			 * id and parent id values
			 */
			var $l = $(lclSettings.dom.listings_container);

			/* retrieve active listings filters */
			var fd = $(lclSettings.dom.filters_form).littled('collectFormDataWithCSRF', evt.data||{});
			fd[lclSettings.keys.page] = $(this).data(lclSettings.keys.page);
			fd[lclSettings.keys.content_type] = $('table:first', $l).data(lclSettings.keys.content_type);
			fd[lclSettings.keys.parent_id] = $('table:first', $l).data(lclSettings.keys.parent_id);

			/* retrieve operations uris for this content type */
			$.littled.retrieveContentOperations(fd[lclSettings.keys.content_type], function(data) {

				if (data.error) {
					$(lclSettings.dom.page_error_container).littled('displayError', data.error);
					return;
				} else if (!data.listings_uri) {
					$(lclSettings.dom.page_error_container).littled('displayError', "Handler not defined.");
					return;
				}

				/* make request to listings ajax script for this content type 
				 * to retrieve the updated listings content
				 */
				$.post(
					data.listings_uri,
					fd,
					function(data2) {
						
						if (data2.error) {
							$(lclSettings.dom.page_error_container).littled('displayError', data2.error);
							return;
						}
						
						/* refresh listings content and trigger handler that will
						 * re-bind the jQuery handlers within the listings.
						 */
						$(lclSettings.dom.listings_container)
						.littled('displayAjaxResult', data2)
						.triggerHandler('contentUpdate');
				
						/* update page value in query string.
						 * NB this might have to happen instead in the filters 
						 * form in the future.
						 */
						methods.updateNavigationValue(lclSettings.keys.page, fd[lclSettings.keys.page], options);
					},
					'json'
				)
				.fail(function(xhr) {
					$(lclSettings.dom.page_error_container).littled('ajaxError', xhr);
				});
			});
        },

		/**
		 * Handler for non-anchor element typically within listings that can 
		 * lead to the details page for that record.
		 * @param {Event} evt
		 * @returns {undefined}
		 */
		gotoDetailsPage: function(evt) {
			evt.preventDefault();
			var lclSettings = $.extend(true, {}, settings, evt.data || {});
			var id = $(this).data(lclSettings.keys.record_id);
			if (!id) {
				$(this).closest(lclSettings.dom.error_container)
				.littled('displayError', 'A record id was not provided.');
				return;
			}
			if (lclSettings.uris.record_details==='') {
				$(this).closest(lclSettings.dom.error_container)
				.littled('displayError', 'An URI was not provided.');
				return;
			}
			var url = lclSettings.uris.record_details +
				'?'+lclSettings.keys.record_id+'='+id +
				'&' + $(lclSettings.dom.filters_form).serialize();
			window.location = url;
		},

		/**
		 * Updates a key/value pair in the query string, esp. the current page
		 * value.
		 * @param {string} key Key to update in the query string.
		 * @param {mixed} value New value to assign to the key.
		 * @returns {undefined}
		 */
		updateNavigationValue: function( key, value, options ) {
			var lclSettings = $.extend(true, {}, settings, options || {});
			var new_url, 
				src_url = window.location.href,
				re = new RegExp("([?|&])" + key + "=.*?(&|$)","i");
			if (src_url.match(re)) {
				new_url = src_url.replace(re,'$1' + key + "=" + value + '$2');
			}
			else {
				new_url = src_url + ((src_url.indexOf('?') > 0)?('&'):('?')) + key + "=" + value;
			}
			history.pushState(null, null, new_url);
		},

		bindKeyPageNavigation: function (options) {
			return this.on('keydown', function(evt) {
				var keyCode = evt.keyCode || evt.which;
				var arrow = {left: 37, up: 38, right: 39, down: 40 };
				switch (keyCode) {
					case arrow.left:
						if (options.hasOwnProperty('leftKeyURL') && options.leftKeyURL) {
							window.location = options.leftKeyURL;
						}
						break;
					case arrow.right:
						if (options.hasOwnProperty('rightKeyURL') && options.rightKeyURL) {
							window.location = options.rightKeyURL;
						}
						break;
					case arrow.up:
						if (options.hasOwnProperty('upKeyURL') && options.upKeyURL) {
							window.location = options.upKeyURL;
						}
						break;
					case arrow.down:
						if (options.hasOwnProperty('downKeyURL') && options.downKeyURL) {
							window.location = options.downKeyURL;
						}
						break;
				}
			});
		}
	};
	
	$.fn.listings = function( method ) {
	
		/* method calling logic */
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		}
		else if ( typeof method === 'object' || !method ) {
			return methods.init.apply(this, arguments);
		}
		else {
			$.error('Method ' + method + ' does not exist on jQuery.listings.');
		}
		return (false);
	};
	
}) ( jQuery );

