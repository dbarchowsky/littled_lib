(function ($) {
	
	/**
	 * static properties 
	 */
	let settings = {
		displayWarnings: true,
		inlineOps: {
		    root: '/vendor/dbarchowsky/littled_cms/ajax/js/',
			nameURL: 'edit_name.php',
			dateURL: 'edit_date.php',
			accessURL: 'edit_access.php',
			slotURL: 'edit_slot.php',
			pageURL: 'edit_page.php',
			statusURL: 'edit_status.php'
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
			record_id: 'id',		/* matches LittledGlobals::ID_KEY			*/
			parent_id: 'pid',		/* matches LittleGlobals::PARENT_ID_KEY 	*/
			content_type: 'tid', 	/* matches LittledGlobals::CONTENT_TYPE_KEY */
			page: 'p',
			operation: 'op',
			csrf: 'csrf',
			uri: 'uri'
		},
		operations: {
			listings: 		'listings',
			details: 		'details'
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

	let methods = {

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

		bindListingsHandlers: function (options) {

			let lclSettings = $.littled.configureLocalizedSettings(null, options);

			return this.each(function() {

				$(this)

				/* re-bind these handlers if the listings content changes */
				.off('contentUpdate', '', methods.listingsUpdateCB)
				.on('contentUpdate', lclSettings, methods.listingsUpdateCB)

				/* enable inline edits on dbl-click */
				.off('dblclick', '.inline-edit-cell', methods.editCell)
				.on('dblclick', '.inline-edit-cell', options, methods.editCell)

				/* page navigation */
				.off('click', '.page-btn', methods.gotoPage)
				.on('click', '.page-btn', options, methods.gotoPage)

				/* non-anchor elements that link to a record's details page */
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

        editCell: function( evt ) {
			let lclSettings = $.littled.configureLocalizedSettings(evt);
            let $p = $(this).parent();
			let op = $(this).data(lclSettings.keys.operation);
			let fd = {t: $(this).data('t')};
			fd[lclSettings.keys.record_id] = $(this).data(lclSettings.keys.record_id);
			fd[lclSettings.keys.operation] = op;
			fd[lclSettings.keys.csrf] = $(lclSettings.dom.csrfSelector).html();
            let url = methods.getInlineURL(op, evt.data||{});

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

		getInlineURL: function(op, options) {
			let lclSettings = $.littled.configureLocalizedSettings(null, options)
			let url;
            switch (op) {
                case 'name':
                    url = lclSettings.inlineOps.root + lclSettings.inlineOps.nameURL;
                    break;
                case 'date':
                    url = lclSettings.inlineOps.root + lclSettings.inlineOps.dateURL;
                    break;
                case 'access':
                    url = lclSettings.inlineOps.root + lclSettings.inlineOps.accessURL;
                    break;
                case 'slot':
                    url = lclSettings.inlineOps.root + lclSettings.inlineOps.slotURL;
                    break;
                case 'page':
                    url = lclSettings.inlineOps.root + lclSettings.inlineOps.pageURL;
                    break;
                case 'status':
                    url = lclSettings.inlineOps.root + lclSettings.inlineOps.statusURL;
                    break;
                default:
                    $(settings.dom.error_container).littled('displayError', 'Invalid operation: ' + op);
            }
			return (url);
		},

        getListingsURI: function(data) {
            if (data.ajax_listings_uri) {
                return data.ajax_listings_uri;
            }
            if (data.routes) {
                for (let i=0; i<data.routes.length; $i++) {
                    if (data.routes[i].operation==='listings') {
                        return data.routes[i].url;
                    }
                }
            }
        },

		/**
		 * Handler for non-anchor element typically within listings that will redirect to a record's details page.
		 * @param {Event} evt
		 * @returns {undefined}
		 */
		gotoDetailsPage: function(evt) {
			let lclSettings = $.littled.configureLocalizedSettings(evt);
			let tid = $(this).data(lclSettings.keys.content_type);
			let id = $(this).data(lclSettings.keys.record_id);
			if (!tid) {
				throw Error('A content type was not provided.');
			}
			if (!id) {
				throw Error('A record id was not provided.');
			}
			window.location = $.littled.retrieveRoute(lclSettings.operations.details, tid, id);
		},

		/**
		 * Handler for button elements that navigate to different pages within
		 * the listings.
		 * @param {Event} evt
		 * @returns {undefined}
		 */
		gotoPage: function( evt ) {

			let lclSettings = $.littled.configureLocalizedSettings(evt);

			/* clear any status messages */
			$(lclSettings.dom.status_container).fadeOut('fast');

			/* pointer to element within listings that stores the content type
			 * id and parent id values
			 */
			let $l = $(lclSettings.dom.listings_container);

			/* retrieve active listings filters */
			let fd = $(lclSettings.dom.filters_form).littled('collectFormDataWithCSRF', evt.data||{});
			fd[lclSettings.keys.page] = $(this).data(lclSettings.keys.page);
			fd[lclSettings.keys.content_type] = $('table:first', $l).data(lclSettings.keys.content_type.toLowerCase());
			fd[lclSettings.keys.parent_id] = $('table:first', $l).data(lclSettings.keys.parent_id.toLowerCase());

			/* retrieve operations uris for this content type */
			$.littled.retrieveContentOperations(fd[lclSettings.keys.content_type], function(data) {

				let listings_uri = '';
				if (data.error) {
					$(lclSettings.dom.page_error_container).littled('displayError', data.error);
					return;
				} else {
					listings_uri = methods.getListingsURI(data);
					if (listings_uri==='') {
						$(lclSettings.dom.page_error_container).littled('displayError', "Listings route not found.");
						return;
					}
				}

				/* make request to listings ajax script for this content type
				 * to retrieve the updated listings content
				 */
				$.post(
					listings_uri,
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

		saveDate: function(evt, dateText, inst) {

			let lclSettings = $.littled.configureLocalizedSettings(evt);
			let $f = $(this).parents('form:first');
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

		listingsUpdateCB: function( evt ) {
			$(this).listings('bindListingsHandlers', (evt.data || {}));
		},

		saveEdit: function(options) {

			let lclSettings = $.extend({}, settings, options || {});
			let op = $(this).data('op');
			let $f = $(this).closest('form');
			let $p = $(this).closest('.inline-edit-cell').parent();
			let url = methods.getInlineURL(op, options);

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

		testKeyedEntry: function(evt) {

			let id = $(this).data('id');
			let t = $(this).data('t');
			let op = $(this).data('op');
			let $p = $(this).closest('.inline-edit-cell').parent();

			let kp = ((window.event) ? (window.event.keyCode) : (evt.which));
			switch (kp) {

				case 13:

					/* save on enter key */
					evt.preventDefault();
					$(this).trigger('commit');
					break;

				case 27:

					/* cancel on escape key */
					evt.preventDefault();
					let url = methods.getInlineURL(op);
					$.post(url,
						{id: id, t: t, op: op, cancel: 1},
						function(data) {
							if (data.error) {
								$(settings.dom.error_container).littled('displayError', data.error);
								return;
							}
							$p.html($.littled.htmldecode(data.content));
						},
						'json'
					)
						.fail(function(xhr) {
							$(settings.dom.error_container).littled('ajaxError', xhr);
						});

					break;
				default:
				/* continue with all other keys */
			}
		},

		updateListingsContent: function(data) {

			let lclSettings = $.littled.configureLocalizedSettings(null, data.settings);

			if (data.error) {
				$(lclSettings.dom.error_container).littled('displayError', data.error);
				return;
			}

			let selector = lclSettings.dom.listings_container || data.container_id;
			let $e = $(selector);
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
		 * Updates a key/value pair in the query string, esp. the current page
		 * value.
		 * @param {string} key Key to update in the query string.
		 * @param value New value to assign to the key.
         * @param options
		 * @returns {undefined}
		 */
		updateNavigationValue: function( key, value, options ) {
			let lclSettings = $.littled.configureLocalizedSettings(null, options);
			let new_url,
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
				let keyCode = evt.keyCode || evt.which;
				let arrow = {left: 37, up: 38, right: 39, down: 40 };
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
		return false;
	};
	
}) ( jQuery );

