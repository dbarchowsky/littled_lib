(function ($) {

	var settings = {
		parentSelector: '.line-items-container',
		lineSelector: '.line-item',
		statusSelector: '.alert-info',
		errorSelector: '.alert-error',
		emptySetSelector: '.empty-set',
		csrfSelector: '#csrf-token',
		insertLocation: 'before',
		commitParam: 'commit',
		cancelParam: 'cancel',
		currentOperation: 'edit',
		add: {
			selector: '.add-line-btn',
			event: 'click',
			button: 'plus',
			callback: null,
			verbose: false,
			uri: $.littled.addProtocolAndDomain('/_ajax/utils/edit_line_item.php')
		},
		edit: {
			selector: '.line-item',
			event: 'dblclick',
			button: '',
			callback: null,
			verbose: false,
			uri: $.littled.addProtocolAndDomain('/_ajax/utils/edit_line_item.php')
		},
		del: {
			selector: '.delete-line-btn',
			event: 'click',
			button: 'trash',
			callback: null,
			verbose: true,
			uri: $.littled.addProtocolAndDomain('/_ajax/utils/delete_line_item.php')
		},
		save: {
			selector: '.save-line-btn',
			event: 'click',
			button: 'circle-check',
			callback: null,
			verbose: false,
			uri: $.littled.addProtocolAndDomain('/_ajax/utils/edit_line_item.php')
		},
		cancel: {
			selector: '.cancel-line-btn',
			event: 'click',
			button: 'circle-close',
			callback: null,
			verbose: false,
			uri: $.littled.addProtocolAndDomain('/_ajax/utils/edit_line_item.php')
		},
		callbacks: {
			postContentInsert: null
		}
	};

	var methods = {

		init: function( options ) {

			var lclSettings = $.extend({}, settings, options || {});
			if (typeof(lclSettings.add.callback) !== 'function') {
				lclSettings.add.callback = methods.edit;
			}
			if (typeof(lclSettings.edit.callback) !== 'function') {
				lclSettings.edit.callback = methods.edit;
			}
			if (typeof(lclSettings.del.callback) !== 'function') {
				lclSettings.del.callback = methods.remove;
			}
			if (typeof(lclSettings.save.callback) !== 'function') {
				lclSettings.save.callback = methods.save;
			}
			if (typeof(lclSettings.cancel.callback) !== 'function') {
				lclSettings.cancel.callback = methods.cancel;
			}

			return this.each(function() {

				/* module header handlers */
				$(this)
				.off(lclSettings.add.event, lclSettings.add.selector, lclSettings.add.callback)
				.off(lclSettings.edit.event, lclSettings.edit.selector, lclSettings.edit.callback)

				.on(lclSettings.add.event, lclSettings.add.selector, options, lclSettings.add.callback)
				.on(lclSettings.edit.event, lclSettings.edit.selector, options, lclSettings.edit.callback);

				/* buttons */
				if (lclSettings.add.event === 'click') {
					$(lclSettings.add.selector, $(this)).iconButton(lclSettings.add.button);
				}
				if (lclSettings.edit.event === 'click') {
					$(lclSettings.edit.selector, $(this)).iconButton(lclSettings.edit.button);
				}
				if (lclSettings.del.event === 'click')  {
					$(lclSettings.del.selector, $(this)).iconButton(lclSettings.del.button);
				}

				/* hook for custom initialization */
				if (lclSettings.hasOwnProperty('postInit')) {
					$(this).lineitems(lclSettings.postInit);
				}
			});
		},

		/**
		 * Handler for edit requests. It acts on the individual line item elements
		 * (as opposed to an edit button within the line item element). 
		 * - Makes request for edit form markup.
		 * - Swaps the form markup for the line item markup currently in the DOM.
		 * - Or, in the case of adding new line items, inserts the edit form
		 * element before or after the line item listings.
		 * @param {eventObject} evt
		 * @returns {nothing}
		 */
		edit: function( evt ) {

			evt.preventDefault();
			var options = evt.data || {};
			var lclSettings = $.extend(true, {}, settings, options);
			
			/* get pointer to this element for use within ajax callback */
			var $e = $(this);
			$e.lineitems('resetMessages', lclSettings);
			var fd = {
				id: $(this).data('id'),
				pid: $(this).data('pid'),
				class: $(this).data('type'),
				var: $(this).data('var'),
				csrf: $(lclSettings.csrfSelector).html()
			};

			/* AJAX call will retrieve record, generate markup, which is 
			 * returned in JSON string 
			 */
			$.ajax({
				type: 'post',
				url: lclSettings.edit.uri,
				data: fd,
				dataType: 'json'
			})
			.success(function(data) {
				data.settings = options;
				$e.lineitems('insertEditFormMarkup', data);
			})
			.fail(function(xhr, status, error) {
				$e.lineitems('displayError', status+': '+error.message);
			});
		},

		/**
		 * Callback for clicking on the delete button during line item edits.
		 * @param {eventObject} evt
		 * @returns {nothing}
		 */
		remove: function(evt) {

			evt.preventDefault();
			var options = evt.data || {};
			var lclSettings = $.extend(true, {}, settings, options);
			lclSettings.currentOperation = 'delete';

			/* get pointer to line element */
			var $e = $(this).closest(lclSettings.lineSelector);
			$e.lineitems('resetMessages', lclSettings);

			var fd = $(this).closest('form').littled('collectFormDataWithCSRF', lclSettings);
			if (fd.hasOwnProperty(lclSettings.commitParam)) {
			 	delete fd[lclSettings.commitParam];
			}

			/* AJAX call will retrieve record, generate markup, which is 
			 * returned in JSON string 
			 */
			$.ajax({
				type: 'post',
				url: lclSettings.del.uri,
				data: fd,
				dataType: 'json'
			})
			.success(function(data) {
				data.settings = options;
				data.settings.currentOperation = 'delete';
				$e.lineitems('insertEditFormMarkup', data);
			})
			.fail(function(xhr, status, error) {
				$e.lineitems('displayError', status+': '+error.message);
			});
		},
		
		/**
		 * Handler to commit changes during line item edit.
		 * @param {eventObject} evt
		 * @returns {nothing}
		 */
		save: function(evt) {

			evt.preventDefault();
			var lclSettings = $.extend({}, settings, evt.data || {});

			/* pointer to line item element for use in callback */
			var $e = $(this).closest(lclSettings.lineSelector);
			// console.log($(this).closest('form').serialize());
			$e.lineitems('resetMessages', lclSettings);

			/* call ajax script to save edits */
			var xhr = $.post(
				lclSettings.save.uri,
				$e.littled('collectFormDataWithCSRF', lclSettings),
				function(data) {
					data.settings = lclSettings;
					$e.lineitems('insertLineItemMarkup', data);
				},
				'json'
			)
			.fail(function(xhr, status, error) {
				$e.lineitems('displayError', status+': '+error.message);
			});
		},

		/** 
		 * Handler for "delete" operation of the delete confirmation form.
		 * This routine sends the AJAX request to actually perform the deletion.
		 * - After the record is deleted, it is removed from the DOM.
		 * @param {eventObject} evt
		 * @returns {nothing}
		 */
		commitDelete: function(evt) {

			evt.preventDefault();
			var lclSettings = $.extend({}, settings, evt.data || {});

			/* pointer to line item element for use in callback */
			var $e = $(this).closest(lclSettings.lineSelector);
			$e.lineitems('resetMessages', lclSettings);
			var fd = $e.littled('collectFormDataWithCSRF', lclSettings);

			/* call ajax script to save edits */
			$.ajax({
				type: 'post',
				url: lclSettings.del.uri,
				data: fd,
				dataType: 'json'
			})
			.success(function(data) {
				if (data.error) {
					$e.lineitems('displayError', data.error, lclSettings);
					return;
				}

				if ($e.length > 0) {
					/* hide line element, then destroy it */
					$e.slideToggle('fast', function() {
						$(this).remove();
					});
				}
					
				/* display result for confirmation */
				if (data.status && lclSettings.del.verbose) {
					$e.lineitems('displayStatus', data.status, lclSettings);
				}
			})
			.fail(function(xhr, status, error) {
				$e.lineitems('displayError', status+': '+error.message);
			});
		},

		/**
		 * Default handler for elements that cancel line item edits. 
		 * Override this routine with cancel.callback.
		 * @param {eventObject} evt
		 * @returns {nothing}
		 */
		cancel: function(evt) {

			evt.preventDefault();
			var lclSettings = $.extend({}, settings, evt.data || {});

			/* pointer to element for use within callback */
			var $e = $(this).closest('.line-item');
			$e.lineitems('resetMessages', lclSettings);

			var fd = $e.littled('collectFormDataWithCSRF', lclSettings, {cancel: '1'});
			if (fd.hasOwnProperty(lclSettings.commitParam)) {
				delete fd[lclSettings.commitParam];
			}

			/* make call to retrieve flattened line item markup */
			var xhr = $.post(
				lclSettings.cancel.uri,
				fd,
				function(data) {
					data.settings = lclSettings;
					$e.lineitems('insertLineItemMarkup', data);
				},
				'json'
			)
			.fail(function(xhr, status, error) {
				$e.lineitems('displayError', status+': '+error.message);
			});
		},

		/**
		 * Element method that inserts flattened line item markup into the DOM.
		 * - The line item markup will be swapped into the elements matching the 
		 * selector used to invoke this routine. 
		 * - Intended to be used in a callback for AJAX request that get line 
		 * item markup in response. 
		 * - Handlers for the flattened line item element are bound to it.
		 * - Localized settings are passed to this routine with data.settings.
		 * @param {object} data JSON collection returned from AJAX script.
		 * @returns {undefined|_L1.methods@call;each}
		 */
		insertLineItemMarkup: function(data) {

			var lclSettings = data.settings || settings;

			if (data.error) {
				this.lineitems('displayError', data.error);
				return;
			}

			return this.each(function() {
				var $new = $($.littled.htmldecode(data.content));
				if ($new.length > 0) {

					/* replace edit form element with new element */
					$(this).fadeOut('fast', function() {
						$(this).replaceWith($new);
						$new.fadeIn('slow');
					});

					/* parent element */
					var $p = $new.closest(lclSettings.parentSelector);

					/* bind flattened presentation handlers */
					$p.lineitems('bindNewItemHandlers', lclSettings);

					/* remove any existing messages in the line item module
					 * to the effect of "no items found", e.g. if this is the 
					 * first line item added to the group.
					 */
					$(lclSettings.emptySetSelector, $p).remove();

					if (data.status && lclSettings.save.verbose) {
						$p.lineitems('displayStatus', data.status);
					}
				}
				else {
					/* Case where an edit form was opened to add a new item
					 * to the list, then the edit was canceled. There is no
					 * new element to add to the DOM. Remove the edit form.
					 */
					$(this).fadeOut('fast', function() {
						$(this).remove();
					});
				}
			});
		},

		/**
		 * Inserts edit form markup into the DOM in place of the element this 
		 * routine is invoked on. Handlers are bound to the necessary elements 
		 * within the new element. 
		 * - Intended as callback for methods within the library that fetch edit 
		 * form markup from AJAX scripts.
		 * @param {object} data Collection of JSON data returned from AJAX script.
		 * @param {object} lclSettings
		 * @returns {_L1.methods@call;each}
		 */
		insertEditFormMarkup: function(data) {

			var lclSettings = $.extend(true, {}, settings, data.settings || {});

			/* check for errors */
			if (data.error) {
				this.lineitems('displayError', data.error);
				return;
			}

			return this.each(function() {

				/* insert form markup into DOM */
				var $new = $($.littled.htmldecode(data.content));
				if ($new.length) {
					if (data.id) {
						/* editing existing line item 
						 * replace the existing elemnent with the form 
						 */
						$(this).fadeOut('fast', function() {
							$(this).replaceWith($new);
							$new.fadeIn('slow');
						});
					}
					else {
						/* editing a new line item
						 * add it to the parent element in the specified location
						 */
						var $p = $(this).closest(lclSettings.parentSelector);
						if ($p.length > 0) {
							if (lclSettings.insertLocation==='before') {
								if ($('header', $p).length > 0) {
									/* put the new element after the module header, 
									 * if it exists.
									 */
									$('header', $p).after($new);
								}
								else {
									/* put the new element at the top of the list
									 * if there is no header.
									 */
									$p.prepend($new);
								}
							}
							else {
								/* place the new element at the end of the list, 
								 * if that is what's specified.
								 */
								$p.append($new);
							}
						}
					}

					/* bind any relevent handlers to the form once it is 
					 * added to the DOM */
					switch (lclSettings.currentOperation) {
						case 'delete':
							$new.lineitems('bindDeleteFormHandlers');
							break;
						default:
							$new.lineitems('bindEditFormHandlers');
							if (typeof(lclSettings.callbacks.postContentInsert)==='function') {
								lclSettings.callbacks.postContentInsert.apply($new);
							} 
					}
				}
			});
		},

		/**
		 * Binds handlers within line item presentation element after the 
		 * element has been added to the DOM.
		 * @param {object} lclSettings Localized operation configuration.
		 * @returns {_L1.methods@call;each}
		 */
		bindNewItemHandlers: function( lclSettings ) {
			return this.each(function() {
				$(this).lineitems('bindActionHandler', lclSettings.edit);
			});
		},
		
		/**
		 * Binds handlers within line item edit for after the form element has
		 * been added to the DOM.
		 * @param {object} options (Optional) settings to override default library settings.
		 * @returns {_L1.methods@call;each}
		 */
		bindEditFormHandlers: function( options ) {
			var lclSettings = $.extend({}, settings, options || {});
			return this.each(function() {
				$('.datepicker', $(this)).datepicker();
				$(this).lineitems('bindActionHandler', lclSettings.save);
				$(this).lineitems('bindActionHandler', lclSettings.del);
				$(this).lineitems('bindActionHandler', lclSettings.cancel);
			});
		},

		/**
		 * Binds handlers within line item edit for after the form element has
		 * been added to the DOM.
		 * @param {object} options (Optional) settings to override default library settings.
		 * @returns {_L1.methods@call;each}
		 */
		bindDeleteFormHandlers: function( options ) {
			var lclSettings = $.extend({}, settings, options || {});
			return this.each(function() {
				/* TODO: consolidate save/cancel/delete commit handlers. 
				 * They are all essentially identical with the exception of the 
				 * script that gets called within $.post()
				 */
				$(lclSettings.del.selector, $(this))
					.iconButton(lclSettings.del.button)
					.off(lclSettings.del.event, methods.commitDelete)
					.on(lclSettings.del.event, methods.commitDelete);
				$(this).lineitems('bindActionHandler', lclSettings.cancel);
			});
		},

		/**
		 * Binds callback to dom element as defined in library settings.
		 * @param {object} actionSettings
		 * @returns {nothing}
		 */
		bindActionHandler: function( actionSettings ) {
			return this.each(function() {
				if (actionSettings.event==='click') {
					$(actionSettings.selector, $(this)).iconButton(actionSettings.button);
				}
				$(actionSettings.selector, $(this))
				.off(actionSettings.event, actionSettings.callback)
				.on(actionSettings.event, actionSettings.callback);
			});
		},
		
		displayError: function( msg, options ) {
			var lclSettings = $.extend({}, settings, options || {});
			return this.each(function() {
				
				var $p = null;
				if ($(this).is("button")) {
					/* display error messages at the top of the group */
					$p = $(this).closest(lclSettings.parentSelector);
				}
				else {
					/* display an error message for a specific line item */
					$p = $(this);
				}

				var $e = $(lclSettings.errorSelector, $p);
				if ($e.length < 1) {
					/* TODO: accommodate selectors that are not class names */
					$(this).prepend("<div class=\"alert "+lclSettings.errorSelector.replace(/^\./, '')+"\"></div>");
					$e = $(lclSettings.errorSelector, $(this));
				}
				if ($e.is(':visible')) {
					$e.slideToggle('fast', function() {
						$(this).html(msg).slideToggle('slow');
					});
				}
				else {
					$e.html(msg).slideToggle('slow');
				}
			});
		},
		
		displayStatus: function( msg, options ) {
			
			var lclSettings = $.extend({}, settings, options || {});
			return this.each(function() {
				/* pointer to status element */
				var $s = $(lclSettings.statusSelector, $(this));
				if ($s.length > 0) {
					/* if there is an old status message being displayed, 
					 * hide it with an effect, wait until that transition is 
					 * finished, then display the new status with a separate 
					 * transition.
					 */
					if ($s.is(':visible')) {
						$s.slideToggle('fast', function() {
							$(this).html(msg).slideToggle('slow');
						});
					}
					else {
						/* when no older status is visible, just display the
						 * status message with a transition.
						 */
						$s.html(msg).slideToggle('slow');
					}
				}
			});
		},
		
		resetMessages: function(lclSettings) {
			return this.each(function() {
				var $status = $(lclSettings.statusSelector, $(this)).is(':visible');
				var $error = $(lclSettings.errorSelector, $(this)).is(':visible');
				if ($status.length > 0) {
					$status.slideToggle('fast');
				}
				if ($error.length > 0) {
					$error.slideToggle('fast');
				}
			});
		}
	};
 
	$.fn.lineitems = function( method ) {
 
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
			$.error('Method ' + method + ' does not exist on jQuery.lineitems.');
		}
		return (false);
	};
}) ( jQuery );