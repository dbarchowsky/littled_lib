(function ($) {
	
	var settings = {
		progressMarkup: '<div class="dialog-in-process"></div>',
		parentContainer: '.kw-container',
		contentContainer: '.kw-cell',
		errorContainer: '.alert-error',
		statusContainer: '.kw-status-container',
		csrfSelector: '#csrf-token'
	};
	
	var methods = {

        bindListingsHandlers: function(options) {

			return this.each(function() {

				$(this)

				/* re-bind keyword handlers if listings or content is changed. */
				.off('contentUpdate', methods.updateListingsHandlers)
				.on('contentUpdate', options, methods.updateListingsHandlers)

				/* inline edit button */
				.off('click', '.edit-kw-btn', methods.edit)
				.on('click', '.edit-kw-btn', options, methods.edit)

				/* save and cancel edits button */
				.off('click', '.kw-commit-btn', methods.save)
				.off('click', '.kw-cancel-btn', methods.cancel)
				.on('click', '.kw-commit-btn', options, methods.save)
				.on('click', '.kw-cancel-btn', options, methods.cancel);

				/* graphical elements */
				$('.edit-kw-btn', $(this)).iconButton('edit');
			});
        },
		
		updateListingsHandlers: function( evt ) {
			$(this).keywords('bindListingsHandlers', evt.data || {});
		},

		bindEditButtons: function() {
			return this.each(function() {
				var $t = $('textarea', $(this)); 
				$t.height($t.prop('scrollHeight'));
				$('.kw-commit-btn', $(this)).iconButton('circle-check');
				$('.kw-cancel-btn', $(this)).iconButton('circle-close');
			});
		},

        edit: function(evt) {

			evt.preventDefault();
			evt.stopPropagation();
			var lclSettings = $.extend(true, {}, settings, evt.data || {});
			$(lclSettings.errorContainer+':visible').slideToggle('fast');

			var $p = $(this).closest(lclSettings.parentContainer);
            var id = $(this).data('id');
            var tid = $(this).data('tid');

            if (!tid) {
				$(lclSettings.errorContainer).littled('displayError', 'Content type not set.');
				return;
            }

			$.littled.retrieveContentOperations(tid, function(data1) {

                if (data1.error) {
					$(lclSettings.errorContainer).littled('displayError', data1.error);
					return;
                } else if (!data1.keywords_uri) {
					$(lclSettings.errorContainer).littled('displayError', 'Handler not set.');
					return;
                }

				var fd = {
					csrf: $(lclSettings.csrfSelector).html(),
					kwpi: id,
					kwti: tid
				};

                $.post(
					data1.keywords_uri,
                    fd,
                    function(data2) {
						data2.settings = lclSettings;
						$p.keywords('updateKeywordContent', data2, function() {
							$p.keywords('bindEditButtons');
						});
                    },
					'json'
				)
				.fail(function(xhr) {
					$p.littled('ajaxError', xhr);
				});
				$p.keywords('displayProcessWheel');
            });
        },

		/**
		 * Displays error message within the keyword container.
		 * Expects to be called with the parent keyword container element, and 
		 * not with elements within the keyword container element.
		 * @param {string} msg
		 * @param {object} options
		 * @returns {_L1.methods@call;each}
		 */
		displayError: function(msg, options) {
			var lclSettings = $.extend(true, {}, settings, options || {});
			return this.each(function() {
				$(this).keywords('hideProcessWheel');
				$(lclSettings.errorContainer, $(this)).littled('displayError', msg);
			});
		},

		save: function(evt) {

			evt.preventDefault();
			var lclSettings = $.extend(true, {}, settings, evt.data || {});
			$(lclSettings.errorContainer+':visible').slideToggle('fast');

			var $p = $(this).closest(lclSettings.parentContainer);
			var $f = $(this).parents('form:first');
			var tid = $(this).data('tid');

			$.littled.retrieveContentOperations(tid, function(data) {

				var fd = $f.serializeObject({csrf: $(lclSettings.csrfSelector).html()});
				$.post(
					data.keywords_uri,
					fd,
					function(data) {
						data.settings = lclSettings;
						$p.keywords('updateKeywordContent', data, function() {
							$p.trigger('contentUpdate');
						});
					},
					'json'
				)
				.fail(function(xhr) {
					$p.littled('ajaxError', xhr);
				});
			});
		},

        cancel: function(evt) {

			evt.preventDefault();
			var lclSettings = $.extend(true, {}, settings, evt.data || {});

			var $p = $(this).closest(lclSettings.parentContainer);
            var $f = $(this).parents('form:first');
            var tid = $(this).data('tid');

			$(this).formDialog('retrieveContentOperations', tid, function(data) {

				var extras = {
					cancel: '1',
					csrf: $(lclSettings.csrfSelector).html()
				};
				var fd = $f.serializeObject(extras, ['commit']);
                $.post(
                    data.keywords_uri,
                    fd,
                    function(data) {
						data.settings = lclSettings;
                        $p.keywords('updateKeywordContent', data, function() {
							$p.trigger('contentUpdate');
						});
                    },
					'json'
				)
				.fail(function(xhr) {
					$p.littled('ajaxError', xhr);
				});
            });
        },

		updateKeywordContent: function(data, f) {

			var lclSettings = $.extend(true, {}, settings, data.settings || {});

			return this.each(function() {
				
				if (data.error) {
					$(lclSettings.errorContainer, $(this)).littled('displayError', data.error);
					return;
				}

				$(lclSettings.errorContainer + ':visible', $(this)).hide();
				$(lclSettings.statusContainer + ':visible', $(this)).hide();
				var $c = $(lclSettings.contentContainer, $(this));
				if ($c.length > 0) {
					$c.html($.littled.htmldecode(data.content));
				}
				else {
					$(this).keywords('displayError', "Keyword container ("+lclSettings.contentContainer+") not found. ");
				}
				
				/* callback to execute after content is updated */
				if (typeof(f) === 'function') {
					f();
				}
			});
        },

		displayProcessWheel: function( options ) {
			var lclSettings = $.extend(true, {}, settings, options || {});
			return this.each(function() {
				$(lclSettings.errorContainer, $(this)).fadeOut('fast');
				$(lclSettings.statusContainer, $(this))
				.fadeIn('slow')
				.html(lclSettings.progressMarkup);
			});
        },
		
		hideProcessWheel: function( options ) {
			var lclSettings = $.extend({}, settings, options || {});
			return this.each(function() {
				$(lclSettings.statusContainer, $(this)).fadeOut('fast');
			});
		}
	};
	
	$.fn.keywords = function( method ) {
	
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
	};
	
}) ( jQuery );