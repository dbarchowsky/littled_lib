/*
 * jQuery added functionality $.littled library
 */
(function($) {

	let settings = {
		errorContainer: 			'.alert-error',
		statusContainer: 			'.alert-info',
		csrfSelector: 				'#csrf-token',
		progress_markup: 			'<div class="dialog-in-process"></div>',
		ajax: {
		    script_path: 			'/vendor/dbarchowsky/littled_cms/ajax/',
			content_operations_uri: 'utils/script-properties.php',
			routes_uri:				'utils/routes.php'
		},
		dom: {
			page_error_container: 	'.alert-error:first'
		},
		keys: {
			id:						'id',	/* matches LittledGlobals::ID_KEY 			*/
			content_type_id: 		'tid', 	/* matches LittledGlobals::CONTENT_TYPE_KEY */
			operation:				'op',
			csrf: 					'csrf'	/* matches LittledGlobals::CSRF_KEY			*/
		}
	};

    /**
	 * $.serializeObject()
	 * Returns associative array of all values in the form. (As opposed to built-in $.serializeArray() which returns an object.)
	 * usage:   'src' Optional associative array containing properties that will be added to those found in the form.
	 *			'exclude' one-dimensional array of of form elements to skip over and ignore
	 */
	$.fn.serializeObject = function(src, exclude) {

		let fd = {};

		this.each(function() {

			$.each($(this).serializeArray(), function(i, d) {
				if (d.name in fd) {
					if (fd[d.name] instanceof Array) {
						fd[d.name].push(d.value);
					}
					else {
						fd[d.name] = [fd[d.name], d.value];
					}
				} 
				else {
					fd[d.name] = d.value;
				}
			});
			if (typeof(exclude) !== 'undefined') {
				$.each(exclude, function(i, d) {
					if (d in fd) {
						delete fd[d];
					}
				});
			}
			fd = $.extend({}, fd, src || {});
		});
		return (fd);
	};

	/**
	 * @deprecated Use $.littled('setTextboxMessage') instead.
	 */
	$.fn.setTextboxMessage = function(msg) {
		this.littled('setTextboxMessage', msg)
	};

	$.fn.iconButton = function(button_type) {
		return this
			.button({
			icons: {primary: 'ui-icon-'+button_type},
			text: false
		});
	};

	$.fn.allData = function() {
		let intID = jQuery.data(this.get(0));
		return($.cache[intID]);	
	};

	$.littled = {

		/**
		 * Extract values from the event object that will override the global settings within an event handler.
		 * @param {Event} evt
		 * @param {object} options
		 * @returns {object}
		 */
		configureLocalizedSettings: function(evt, options={}) {
			if (evt) {
				evt.preventDefault();
			}
			return ($.extend(true, {}, settings, evt.data || {}, options));
		},

		getDomain: function() {
			return ((window.document.domain === undefined) ? (window.document.hostname) : (window.document.domain));
		},

		getRelativePath: function() {
			let path = '';
			let url = window.location.href;
			url = url.replace(/^.*:\/\/.*?\/(.*\/).*$/, '$1');
			if (url) {
				let depth = url.split('/').length - 1;
				for (let i=0; i < depth; i++) {
					path += '../';
				}
			}
			return (path);
		},

		addProtocolAndDomain: function(path) {
			return (location.protocol + '//' + $.littled.getDomain() + path);
		},

		htmldecode: function(html) {
			if (html === undefined) {
				return ("");
			}
			return (html.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"'));
		},
		
		htmlentities: function( html ) {
			return String(html).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
		},

		getQueryArray: function(arAppend, arExclude) {
			let e = ['id', 'msg'];
			if (arExclude !== undefined && arExclude.length >= 0) {
				e = e.concat(arExclude);
			}
			let q = LITTLED.ptq(document.location.search.substring(1).replace(/\+/g, ' '), e);
			for (let i in q) {
				if (q[i] === '') {
					delete q[i];
				}
			}
			if (arAppend !== undefined && typeof (arAppend) === 'object') {
				for (i in arAppend) {
					q[i] = arAppend[i];
				}
			}
			return q;
		},

		getEventOptions: function(evt, options) {
			let lclSettings = options || {};
			if (evt.hasOwnProperty('data')) {
				if (evt.data !== undefined && evt.data.hasOwnProperty('options')) {
					$.extend(lclSettings, evt.data.options);
				}
			}
			return (lclSettings);
		},

        /**
        * First makes an AJAX call to get section operations based on the value of tid. 
        * Then executes callback function cb.
        * @param {int} content_type_id Section id used to retrieve section settings.
        * @param {function} cb Callback used to execute as "success" handler 
		* after the section's properties have been successfully retrieved.
		* @param {object} opts (Optional) collection of settings that will
		* override the library's default settings.
        */
        retrieveContentOperations: function(content_type_id, cb, opts) {

			let lclSettings = $.extend(true, {}, settings, opts || {});
			let url = $.littled.getRelativePath()+lclSettings.ajax.script_path+lclSettings.ajax.content_operations_uri;
			let pd = {};
			pd[lclSettings.keys.content_type_id] = content_type_id;
			pd[lclSettings.keys.csrf] = $(lclSettings.csrfSelector).html();

			/* ajax call to get script properties */
            $.ajax({
                type: 'post',
                url: url,
                dataType: 'json',
                data: pd,
                success: cb
            })
			.fail(function(xhr) {
				$(lclSettings.dom.page_error_container).littled('ajaxError', xhr);
			});
        },

		/**
		 * Retrieves the route matching operation and content_type_id. Inserts record_id into the route if appropriate.
		 * @param {string} operation
		 * @param {int} content_type_id
		 * @param {int} record_id
		 * @param {object} options
		 * @returns {string}
		 */
		retrieveRoute: function(operation, content_type_id, record_id, options)
		{
			let lclSettings = $.littled.configureLocalizedSettings(null, options);
			let post_data = {};
			post_data[lclSettings.keys.operation] = operation;
			post_data[lclSettings.keys.content_type_id] = content_type_id;
			post_data[lclSettings.keys.id] = record_id;

			fetch(lclSettings.ajax.routes_uri, {
					method: 'post',
					body: JSON.stringify(post_data)
				}
			).then(response => response.json()
			).then(response => {
				if (response.error) {
					throw Error(response.error);
				}
				return response.route;
			}).catch(e => $(lclSettings.errorContainer).littled('displayError', e.message));
		},

		/**
		 * Calls ajax to retrieve URL of caching script, then makes ajax
		 * call to that scirpt, thereby updating the content cache. 
		 * - TODO: Add property to library 'settings' property that will 
		 * store the caching script url. Then apply that setting when binding 
		 * handlers after a page is loaded. (i.e. remove $.retrieveContentOperations()
		 * call & thereby reduce ajax calls.)
		 * @param {event} evt
		 * @returns {undefined}
		 */
		updateCache: function( evt ) {

			evt.preventDefault();
			let lclSettings = $.extend(true, {}, settings, evt.data || {});

			$(lclSettings.errorContainer+':visible').fadeOut('fast');
			$(lclSettings.statusContainer+':visible').fadeOut('fast');

			let tid = $(this).data('tid');
			let id = $(this).data('id');
			if (!tid) {
				$(lclSettings.errorContainer).littled('displayError', 'Content type not specified.');
				return;
			}

			$.littled.retrieveContentOperations(tid, function(data1) {

				if (data1.error) {
					$(lclSettings.errorContainer).littled('displayError', data1.error);
					return;
				} 
				else if (!data1.cache_uri) {
					$(lclSettings.errorContainer).littled('displayError', 'Cache handler not set.');
					return;
				}

				$.post(
					data1.cache_uri,
					{
						tid: tid, 
						id: id, 
						csrf: $(lclSettings.csrfSelector).html()
					},
					function(data2) {
						if (data2.error) {
							$(lclSettings.errorContainer).littled('displayError', data2.error);
							return;
						}
						$(lclSettings.statusContainer).littled('displayStatus', data2.status);
					},
					'json'
				)
				.fail(function(xhr, status, error) {
					$(lclSettings.errorContainer).littled('displayError', status+': '+error.message);
				});
			}, lclSettings);
		}
	};

	let methods = {

		displayAjaxResult: function(data) {

			return this.each(function() {
				if (data.error) {
					let $err = $('.error', $(this));
					if ($err.length > 0) {
						$err.html(data.error).show();
					}
					else {
						$(this).html(data.error).addClass('error');
					}
				}
				else {
					$(this).html($.littled.htmldecode(data.content)).show();
				}
			});
		},

		/**
		 * Displays error message in element(s).
		 * @param {string} err Error message to display.
         * @returns {*}
         */
		displayError: function(err) {
			err = err.replace(/\n/mg, '<br />').replace(/<br \/>$/, '');
			err += '<br /><button class="dismiss-btn smtext">dismiss</button>';
			this.html(err).show('slow');
			return this.each(function() {
				let $e = $(this);
				$('button.dismiss-btn', $(this)).click(function() {
					$(this).unbind('click');
					$e.hide('fast');
				});
			});
		},

		ajaxError: function(xhr) {
			if (xhr.status==='200') {
				return this.little('displayError', $.littled.htmlentities(xhr.responseText));
			} else {
				return this.littled('displayError', '[' + xhr.status + ' ' + xhr.statusText + '] ' + xhr.responseText);
			}
		},

		/**
		 * Displays status message in element(s). "Unhides" the elements if 
		 * they are hidden.
		 * @param {string} statusMsg Status message to display.
		 * @returns {*}
		 */
		displayStatus: function(statusMsg) {
			return this.each(function() {
				if (statusMsg) {
					$(this)
					.show('slow')
					.html($.littled.htmldecode(statusMsg).replace(/\n/g, '<br />\n'));
				}
			});
		},

		dismissError: function() {
			$(this).html('').hide('fast');
		},

		collectFormDataWithCSRF: function( lclSettings, extras ) {
			let lclExtras = $.extend({}, {csrf: $(lclSettings.csrfSelector).html()}, extras || {});
			return($.extend(lclExtras, $('input, select, textarea', this).serializeObject()));
		},

		keyNavigation: function(options) {
			return this.on('keydown', function(e) {

				let keyCode = e.keyCode || e.which;
				let arrow = {left: 37, up: 38, right: 39, down: 40};
				switch (keyCode) {
					case arrow.left:
						if (options.backURL) {
							e.preventDefault();				
							window.location = options.backURL;
						}
						break;
					case arrow.right:
						if (options.nextURL) {
							e.preventDefault();				
							window.location = options.nextURL;
						}
						break;
					case arrow.up:
						if (options.upURL) {
							e.preventDefault();				
							window.location = options.upURL;
						}
						break;
					case arrow.down:
						if (options.downURL) {
							e.preventDefault();				
							window.location = options.downURL;
						}
						break;
				}
			});
		},

		setTextboxMessage: function(msg) {

			return this.each(function() {
				if ($(this).val() === '') {
					$(this)
					.val(msg)
					.addClass('dimtext');
				}
				$(this)
				.blur(function() {
					if ($(this).val() === '') {
						$(this)
						.val(msg)
						.addClass('dimtext');
					}
				})
				.focus(function() {
					if ($(this).val() === msg) {
						$(this)
						.val('')
						.removeClass('dimtext');
					}
				});
			});
		},
	
		bindImageRollovers: function() {

			return this
				.on('mouseenter', function() {
					$(this).prop('src', $(this).prop('src').replace(/\.([^.]+)$/, '-over.$1'));
				})
				.on('mouseleave', function() {
					$(this).prop('src', $(this).prop('src').replace(/-over\.([^.]+)$/, '.$1'));
				});
		},
	
		displayProcessWheel: function(_form_id) {
			return this.each(function() {
				let $f = $(this).closest('form');
				$('.error', $f).html('').hide('fast');
				$('.status', $f).html(settings.progress_markup).show();
			});
		},

		formatQueryString: function() {
			let q = document.location.search.toString();
			q = q.replace(/^[\?]/, '&');
			q = q.replace(new RegExp('&(id|msg|' + LITTLED.id_param + ')=.*?&'), '&');
			q = q.replace(new RegExp('&(id|msg|' + LITTLED.id_param + ')=.*$'), '');
			q = q.replace(/^[\?&]/, '');
			return (q);
		},

		/**
		 * Tests DOM for "selector" element relative to $(this) element. If the
		 * "parent_selector" argument has a value, the search will start relative
		 * to the first element above $(this) that matches, otherwise the 
		 * search will start with $(this). If the desired element doesn't 
		 * exist in the dom, it will be created. It will be assigned any CSS 
		 * classes found in the "class_array" argument. 
		 * @param {string} selector Selector to search for.
		 * @param {string} parent_selector (Optional) parent of $(this) that will
		 * serve as the top-level element to search from. If this is not provided,
		 * $(this) will be the top-level element.
		 * @param {array} class_array List of CSS classes to apply to the new 
		 * element if it is created.
		 * @returns {*}
		 */
		testAndAddElement: function(selector, parent_selector, class_array) {
			
			return this.each(function() {
				
				/* get pointer to top-level element */
				let $p = null;
				if (parent_selector && ! parent_selector instanceof Array) {
					$p = $(this).closest(parent_selector);
					if ($p.length<1) {
						return;
					}
				}
				else {
					if (parent_selector instanceof Array) {
						class_array = parent_selector;
					}
					$p = $(this);
				}

				/* test if target element exists */
				let $e = $p.first(selector);
				if ($e.length<1) {
					
					/* create target element if it doesn't exist already */
					let $new = '<div></div>';
					if (selector.indexOf('#')===0) {
						$new.addClass(selector).substr(1);
					}
					else if (selector.indexOf('.')===0) {
						$new.attr('id', selector.substr(1));
					}
					else {
						return;
					}
					
					/* add target element to the dom */
					if (class_array instanceof Array) {
						for(let class_name in class_array) {
							$new.addClass(class_name);
						}
					}
					$p.prepend($new);
				}
			});			
		}
	};
	
	$.fn.littled = function( method ) {

		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.littled' );
		}    

	};

})(jQuery);
