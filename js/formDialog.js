/*
 * formDialog jQuery extension.
 */
(function($) {

	let settings = {
		event: 'click',
		width: 'auto',
		height: 'auto',
		dialogClass: '',
		formLoadCB: null,
		formDismissCB: null,
		preContentUpdateCB: null,
		postContentUpdateCB: null,
		pagesContainer: '.pages-container',
		/** @depreacted Use dom.dialog_container in its place */
		dialogSelector: '#globalDialog',
		/** @depreacted Use dom.dialog_error_container in its place */
		errorContainer: '#globalDialog .alert-error:first',
		/** @depreacted Use dom.page_error_container in its place */
		pageErrorContainer: '.alert-error:first',
		/** @deprecated Use dom.listings_container in its place */
		listingsContainer: '.listings',
		/** @var {string} container for page navigation elements */
		navigationContainer: '.page-links-container',
		/** @deprecated Use dom.listings_filters in its place */
		filtersSelector: '.listings-filters form', /* default selector for listings filters form */
		/** @var {string} Selector for current csrf token vlaue */
		csrfSelector: '#csrf-token',
		/** @var {boolean} Flag to supress warning messages. */
		displayWarnings: true,
		/** @var {object} Key values. */
		keys: {
			record_id: 'id',
			content_type_id: 'tid',
			parent_id: 'pid',
			operation: 'op',
			page: 'p',
			commit: 'commit',
			cancel: 'cancel',
			csrf: 'csrf'
		},
		dom: {
			dialog_container: '#globalDialog',
			listings_container: '.listings',
			listings_filters: '.listings-filters form',
			dialog_error_container: '#globalDialog .alert-error:first',
			page_error_container: '.alert-error:first',
			error_container: '.alert-error',
			status_container: '.alert-info:first'
		},
		callbacks: {
			update_content: null
		}
	};

	let methods = {

		/**
		 * Binds event handler to element that will open a dialog on that event.
		 * By default, the event is a 'click' event, but it can be overridden with
		 * options.event. 
		 * @param {Object} options
		 * @returns
		 */
		init: function(options) {
			let lclSettings = $.extend(true, {}, settings, options || {});
			return this
				.off(lclSettings.event, methods.open)
				.on(lclSettings.event, options, methods.open);
		},
		
		/**
		 * Disables the dialog event handler.
		 * @param {Object} options
		 * @returns
		 */
		cancel: function(options) {
			let lclSettings = $.extend(true, {}, settings, options || {});
			/* un-bind event handler */
			return this.off(lclSettings.event, methods.open);
		},

		open: function(evt) {

			evt.preventDefault();
			let options = evt.data || {};
			let lclSettings = $.extend(true, {}, settings, options);

			/* hide existing error messages */
			methods.dismissErrorMessages(options);
			
			/* retrieve operation properties */
			let nav = {};
			nav[lclSettings.keys.operation] = ((lclSettings.hasOwnProperty('scriptData') && lclSettings.scriptData.hasOwnProperty(lclSettings.keys.operation))?(lclSettings.scriptData.op):($(this).data('op')));
			nav[lclSettings.keys.record_id] = ((lclSettings.hasOwnProperty('scriptData') && lclSettings.scriptData.hasOwnProperty(lclSettings.keys.record_id))?(lclSettings.scriptData.id):($(this).data('id')));
			nav[lclSettings.keys.content_type_id] = ((lclSettings.hasOwnProperty('scriptData') && lclSettings.scriptData.hasOwnProperty(lclSettings.keys.content_type_id))?(lclSettings.scriptData.tid):($(this).data('tid')));
			nav.ssid = nav[lclSettings.keys.content_type_id];
			nav[lclSettings.keys.parent_id] = ((lclSettings.hasOwnProperty('scriptData') && lclSettings.scriptData.hasOwnProperty(lclSettings.keys.parent_id))?(lclSettings.scriptData.pid):($(this).data('pid')));
			nav[lclSettings.keys.page] = $(lclSettings.dom.listings_container+' table:first').data(lclSettings.keys.page);
			nav[lclSettings.keys.csrf] = $(lclSettings.csrfSelector).html();

			if (nav[lclSettings.keys.operation]===undefined) {
				$(lclSettings.dom.page_error_container).littled('displayError', 'Operation not specified.');
				return;
			}
			if (nav[lclSettings.keys.content_type_id]===undefined) {
				$(lclSettings.dom.page_error_container).littled('displayError', 'Content type not specified.');
				return;
			}

			/* get a pointer to the element for reference inside the callback routines */
			let $e = $(this);

			$.littled.retrieveContentOperations(nav[lclSettings.keys.content_type_id], function(data1) {

                if (data1.error) {
					$(lclSettings.dom.page_error_container).littled('displayError', data1.error);
					return;
                }
                let dialog_url = methods.getOperationURI(nav[lclSettings.keys.operation], data1);
				if (!dialog_url) {
					$(lclSettings.dom.page_error_container).littled('displayError', 'Operation handler not specified.');
					return;
				}

                nav[data1.id_param] = nav[lclSettings.keys.record_id];
                $.extend(nav, lclSettings.scriptData || {});
				let fd = $(lclSettings.dom.listings_filters).formDialog('retrieveFormFilters', options);
				if (Object.keys(fd).length > 0) {
					$.extend(fd, nav);
				}
				else {
					fd = $.littled.getQueryArray(nav);
				}
				delete(fd['commit']);

                $.ajax({
					type: 'post',
					url: dialog_url,
					data: fd,
					dataType: 'json'
				})
				.success(function(data2) {
					data2.settings = options;
					$(lclSettings.dom.dialog_container).formDialog('display', data2);
				})
				.fail(function(xhr) {
					$(lclSettings.dom.page_error_container).littled('ajaxError', xhr);
				});
			});
		},

		display: function(data) {

			let lclSettings = $.extend(true, {}, settings, data.settings || {});
			if (data.error) {
				$(lclSettings.dom.page_error_container).littled('displayError', data.error);
				return false;
			}

			return this.each(function() {

				/**
				 * load content into dialog container before opening dialog so it can position itself correctly 
				 */
				$('#global-modal-content').html($.littled.htmldecode(data.content));

				/* open dialog */
				$(this).dialog({
					title: data.label,
					minWidth: 360,
					width: lclSettings.width,
					height: lclSettings.height,
					dialogClass: lclSettings.dialogClass,
					closeOnEscape: true,
					modal: true,
					open: function(event, ui) {
						/* close the dialog on mouseclick outside of dialog */
						$('.ui-widget-overlay').click(function(evt) {
							evt.preventDefault();
							$(lclSettings.dom.dialog_container).formDialog('close');							
						});

						/* standard dialog handlers */
						$(this).formDialog('bindDialogHandlers', data.settings);

						/* hook for code after the dialog has been displayed */
						if (lclSettings.formLoadCB) {
							lclSettings.formLoadCB.apply(this, Array.prototype.slice.call( arguments, 1 ));
						}
					},
					close: function(event, ui) {
						$('.ui-widget-overlay').unbind('click');
					}
				});
			});
		},

		bindDialogHandlers: function(options) {
			let lclSettings = $.extend(true, {}, settings, options || {});
			return this.each(function() {
				$('.datepicker', $(this)).datepicker();
				$('.dlg-commit-btn', $(this)).button().on('click', options, methods.commitOperation);
				$('.dlg-cancel-btn', $(this)).button().on('click', options, function(evt) {
					evt.preventDefault();
					$(lclSettings.dom.dialog_container).formDialog('close');
				});
			});
		},

		collectValue: function( key, $f ) {
		
			let value = $(this).data(key);
			if (!value) {
				if (typeof $f !== 'object') {
					$f = $(this).closest('form');
				}
				if ($f.length) {
					let $i = $('input[name="'+key+'"]', $f);
					if ($i.length) {
						value = $i.val();
					}
				}
			}
			return (value);
		},

		commitOperation: function(evt) {
			
			evt.preventDefault();
			let options = evt.data || {};
			let lclSettings = $.extend(true, {}, settings, options);
			
			methods.dismissErrorMessages(options);

			let $f = $(this).closest('form');
			let tid = $(this).formDialog('collectValue', lclSettings.keys.content_type_id, $f);
			let op = $(this).formDialog('collectValue', lclSettings.keys.operation, $f);

			if (!tid) { 
				methods.displayError('Content type not specified.', options);
				return;
			}
			if (!op) { 
				methods.displayError('Operation not specified.', options);
				return;
			}

			$.littled.retrieveContentOperations(tid, function(data1) {

				if (data1.error) {
					methods.displayError(data1.error, options);
					return;
				}

				let url = methods.getOperationURI(op, data1);
				if (!url) {
					methods.displayError('Invalid operation.', options);
					return;
				}
				url = $.littled.addProtocolAndDomain(url);

				/* hook for code before form data is submitted */
				if (lclSettings.preContentUpdateCB) {
					lclSettings.preContentUpdateCB.apply(this, Array.prototype.slice.call( arguments, 1 ));
				}

				$f.ajaxSubmit({
					url: url,
					type: 'post',
					dataType: 'json',
					success: function(data2) {
						if (data2===null) {
							methods.displayError('No data returned.', options);
							return;
						}
						data2.settings = options;
						methods.onOperationSuccess(data2);
					},
					error: methods.ajaxError
				});
			});
		},

		onOperationSuccess: function(data) {

			let options = data.settings || {};
			let lclSettings = $.extend(true, {}, settings, options);

			/* clear existing status messages */
			methods.dismissErrorMessages(options);

			/* display any errors */
			if (data.error) {
				$(lclSettings.dom.dialog_error_container).littled('displayError', data.error);
				return;
			}

			/* update page content */
			data.settings = options;
			if (typeof(lclSettings.callbacks.update_content) === 'function') {
				lclSettings.callbacks.update_content.apply(this, Array.prototype.slice.call(arguments));
			}
			else {
				$(lclSettings.dom.listings_container).listings('updateListingsContent', data);
			}

			/* hook for code after page content is refreshed */
			if (lclSettings.postContentUpdateCB) {
				lclSettings.postContentUpdateCB.apply(this, arguments);
			}

			/* close the dialog */
			$(lclSettings.dom.dialog_container).formDialog('close', options);
		},

		close: function( ) {
			return this.each(function() {
				$(this).dialog('close');
			});
		},

		dismissErrorMessages: function( options ) {
			let lclSettings = $.extend(true, {}, settings, options || {});
			/* removing this element is removed while the dialog
			 * is open causes the page to shift up. too distracting. */
			// $(lclSettings.dom.status_container+':visible').hide('fast');
			$(lclSettings.dom.error_container+':visible').hide('fast');
			$(lclSettings.dom.page_error_container+':visible').hide('fast');
			$(lclSettings.dom.dialog_error_container+':visible').hide('fast');
		},

		clearPageContent: function( data ) {
			let lclSettings = $.extend(true, {}, settings, data.settings || {});
			return this.each(function() {
				if (data.error) {
					$(lclSettings.dom.page_error_container).littled('displayError', data.error);
					return;
				}
				/* after successfully deleting the record, clear the 
				 * page content and display any status messages */
				$(this).html('<div><!-- --></div>');
				$(lclSettings.dom.status_container).littled('displayStatus', data.status);
			});
		},

		retrieveFormFilters: function( options ) {
			let lclSettings = $.extend(true, {}, settings, options || {});
			let filters = {};
			this.each(function() {
				filters = $(this).serializeObject();
				$.extend(filters, {
					id: $(this).data('id'),
					pid: $(this).data(lclSettings.keys.parent_id),						/* parent id */
					tid: $(this).data(lclSettings.keys.content_type_id),						/* content type id */
					p: $(lclSettings.dom.listings_container+' table:first').data(lclSettings.keys.page)	/* current page within listings */
				});
			});
			return (filters);
		},

        /**
		 * @deprecated Use $.littled.retrieveContentOperations() instead.
		 * First makes an AJAX call to get section operations based on the value of tid. 
		 * Then executes callback function cb.
		 * @param {integer} tid Section id used to retrieve section settings.
		 * @param {function} cb Callback used to execute as "success" handler 
		 * after the section's properties have been successfully retrieved.
		 * @param {object} options (Optional) collection of settings that will
		 * override the library's default settings.
		 */
        retrieveContentOperations: function(tid, cb, opts) {
			$.littled.retrieveContentOperations(tid, cb, opts);
        },

		clearMessages: function() {
			return this.each(function() {
				$('.message', $(this)).html('').hide('fast');
				$('.error', $(this)).html('').hide('fast');
			});
		},

		getOperationURI: function(op, data) {
			switch (op) {
				case 'edit':
					return(data.edit_uri);
				case 'upload':
					return ((data.upload_uri)?(data.upload_uri):(data.edit_uri));
				case 'delete':
					return(data.delete_uri);
				case 'view':
				case 'details':
					return(data.details_uri);
				default:
					return('');
			}
		},
		
		ajaxError: function(xhr, error) {
			let err = '[' + xhr.status + ' ' + xhr.statusText + '] ' + xhr.responseText;
			methods.displayError(err);
		},
		
		displayError: function( error, options ) {
			let lclSettings = $.extend(true, {}, settings, options || {});
			if ($(lclSettings.dom.dialog_container).is(':visible')) {
				$(lclSettings.dom.dialog_error_container).littled('displayError', error);
			}
			else {
				$(lclSettings.dom.page_error_container).littled('displayError', error);
			}
		}
	};

	$.fn.formDialog = function( method ) {

		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.formDialog' );
		}
	};

})(jQuery);