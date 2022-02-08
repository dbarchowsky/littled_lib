(function ($) {

    let script_path = '/vendor/dbarchowsky/littled_cms/ajax/';

	let settings = {
		site_label: '',
		album_type: '',
		layout: '',
		uris: {
			page_set: script_path+'book-viewer/page-set.php'
		},
		dom: {
			album_container: '.album-container',
			page_set: '.page-set',
			page_previews: '.page-previews',
			book_previews: '.book-previews',
			left_page: '.left-page',
			right_page: '.right-page',
			page_thumbnail: '.page-tn',
			page_title: '.page-title',
			page_number: '.page-number',
			page_navigation: '.page-links-container',
			prev_page_button: '.prev-pg-btn',
			next_page_button: '.next-pg-btn',
			minimized_header: '.minimized-header',
			scroll_pane: '.scroll-pane',
			scrollbar: '.scroll-bar',
			scroll_content: '.scroll-content',
			preload: '.preload-',
			error_container: '.alert-error',
			csrf_container: '#csrf-token'
		},
		keys: {
			record_id: 'id',
			operation: 'op',
			page: 'p',
			type: 'type',
			class: 'class',
			csrf: 'csrf'
		},
		layouts: {
			one_page: '1-up',
			two_page: '2-up'
		}
	};

	let methods = {
		
		/**
		 * Binds jQuery handlers for page set, page previews, and book previews 
		 * components.
		 * @param {Object} options
		 * @returns {_L1.methods@call;each}
		 */
		bindBookSetHandlers: function( options ) {
			
			let lclSettings = $.extend(true, {}, settings, options || {});

			return this.each(function() {
				$(lclSettings.dom.page_set, $(this)).bookviewer('bindPageSetHandlers', options);
				$(lclSettings.dom.page_navigation, $(this)).bookviewer('bindNavigationHandlers', options);
				$(lclSettings.dom.page_previews, $(this)).bookviewer('bindPagePreviewsHandlers', options);
				$(lclSettings.dom.book_previews, $(this)).bookviewer('bindBookPreviewsHandlers', options);
			});
		},
		
		/**
		 * Binds jQuery handlers for elements within the page set component.
		 * @param {Object} options (Optional) settings to override the library's
		 * default settings.
		 * @returns {undefined}
		 */
        bindPageSetHandlers: function( options ) {
			let lclSettings = $.extend(true, {}, settings, options || {});
			return this.each(function() {
				
				$(this)
				.on('click', lclSettings.dom.left_page, options, methods.changePage)
				.on('click', lclSettings.dom.right_page, options, methods.changePage);
			});
        },

		/**
		 * Binds jQuery handlers for elements within the page set component.
		 * @param {Object} options (Optional) settings to override the library's
		 * default settings.
		 * @returns {undefined}
		 */
        bindNavigationHandlers: function( options ) {
			let lclSettings = $.extend(true, {}, settings, options || {});
			return this.each(function() {
				$(this)
				.on('click', lclSettings.dom.prev_page_button, options, methods.changePage)
				.on('click', lclSettings.dom.next_page_button, options, methods.changePage);
			});
        },

        bindPagePreviewsHandlers: function( options ) {
			let lclSettings = $.extend(true, {}, settings, options || {});
			return this
				.bookviewer('initScrollbar', options)
				.on('click', lclSettings.dom.page_thumbnail, options, methods.changePage);
		},

        bindBookPreviewsHandlers: function( options ) {
			return this.bookviewer('initScrollbar', options);
		},

		bindKeyedNavigation: function( options ) {
			if (0) { /* jQuery mobile too much of a headache alongside normal jQuery */
				$(document)
				.on('swipeleft', options, function(evt) {
					evt.preventDefault();
					if ($(evt.target).hasClass('ui-slider-handle')) {
						/* ignore any events if they come through the jQuery UI slider widget */
						return;
					}
					let lclSettings = $.extend(true, {}, settings, evt.data || {});
					$(lclSettings.dom.prev_page_button).trigger('click');
				})
				.on('swiperight', options, function(evt) {
					evt.preventDefault();
					if ($(evt.target).hasClass('ui-slider-handle')) {
						/* ignore any events if they come through the jQuery UI slider widget */
						return;
					}
					let lclSettings = $.extend(true, {}, settings, evt.data || {});
					$(lclSettings.dom.next_page_button).trigger('click');
				});
			}
			return this.on('keydown', options , methods.keyNavigation);
		},

		/**
		 * Handler for any page element that causes the page set to change.
		 * @param {Event} evt
		 * @returns {undefined}
		 */
		changePage: function( evt ) {
			
			evt.preventDefault();
			let options = evt.data || {};
			let lclSettings = $.extend(true, {}, settings, options);
			
			/* album properties are stored in parent element */
			let $p = $(this).closest(lclSettings.dom.album_container);
			
			/* next page set properties, passed in request to ajax script that 
			 * fetches the page set 
			 */
			let fd = {};
			fd[lclSettings.keys.record_id] = $p.data(lclSettings.keys.record_id);
			fd[lclSettings.keys.class] = $p.data(lclSettings.keys.type);
			fd[lclSettings.keys.page] = $(this).data(lclSettings.keys.page);
			fd[lclSettings.keys.operation] = $(this).data(lclSettings.keys.operation);
			fd[lclSettings.keys.csrf] = $(lclSettings.dom.csrf_container).html();

			/* call to ajax script that will fetch the next page set's properties */
            $.ajax({
				method: 'post',
				url: lclSettings.uris.page_set,
				data: fd,
				dataType: 'json'
			})
			.success(function(data) {
				data.settings = options;
				methods.updateBookContent(data);
			})
			.fail(function(xhr) {
				$(lclSettings.dom.error_container).littled('ajaxError', xhr);
			});
		},

		/**
		 * Update the page set content using response from ajax script.
		 * @param {Object} data JSON response from ajax script containing page
		 * set properties
		 * @returns {undefined}
		 */
		updateBookContent: function(data) {
			
			let lclSettings = $.extend(true, {}, settings, data.settings || {});
			
            if (data.error) {
				$(lclSettings.dom.error_container).littled('displayError', data.error);
				return;
            }

			/* google analytics tracking */
            if (typeof _gaq !== 'undefined') {
                _gaq.push(['_trackEvent', data.album_type, 'Nav', data.page_title]);
            }

            if (data.gallery === undefined) {
                /* no pages to load */
                return;
            }

            /* update page content, including data- values and page title */
            if (data.gallery.list.length > 1 && !data.gallery.list[0].full.path && !data.gallery.list[1].full.path) {
				/* no pages to load */
                return;
            }

            switch (data.layout) {

                case lclSettings.layouts.one_page:
                    methods.updateOneUpBookContent(data);
                    break;
                case lclSettings.layouts.two_page:
                    methods.updateTwoUpBookContent(data);
                    break;
                default:
                    alert('Unhandled layout type.');
                    return;
            }

            /* update page numbers */
            $(lclSettings.dom.page_number).html(methods.formatPageSetLabel(data));

            /* update page title */
			document.title = methods.formatDocumentTitle(data);
            let $pt = $(lclSettings.dom.page_title);
            if ($pt.length > 0) {
                /* don't update page title if it's an image */
                if ($('img', $pt).length < 1) {
                    $pt.html(data.page_title);
                }
            }

            /* after page title is updated, register hit with google analytics */
            if (typeof _gaq !== 'undefined') {
                _gaq.push(['_trackPageview']);
            }
		},
		
		updateOneUpBookContent: function( data ) {
			
			let options = data.settings || {};
			let lclSettings = $.extend(true, {}, settings, options);

            if (!data.gallery.list[0].full.path) {
				$(lclSettings.dom.error_container).littled('displayError', 'Page image unavailable.');
                return;
            }

			$(lclSettings.dom.page_set+' '+lclSettings.dom.right_page)
			.bookviewer('updatePageState', data.gallery.list[0], data.gallery.list[1], options);
	
			$(lclSettings.dom.page_navigation)
			.bookviewer('updatePageNavigation', data);
		},
		
		updateTwoUpBookContent: function( data ) {

			let options = data.settings || {};
			let lclSettings = $.extend(true, {}, settings, options);

            if (data.gallery.list[0].full.path) {
                /* load left page */
				$(lclSettings.dom.page_set+' '+lclSettings.dom.left_page)
				.bookviewer('updatePageState', data.gallery.list[0], data.gallery.list[2], options);
            }
            else {
                /* no left page */
				$(lclSettings.dom.page_set+' '+lclSettings.dom.left_page)
				.bookviewer('updateAdjacentPageState', data.gallery.list[1], data.gallery.list[2], options);
            }

            if (data.gallery.list[1].full.path) {
                /* load right page */
				$(lclSettings.dom.page_set+' '+lclSettings.dom.right_page)
				.bookviewer('updatePageState', data.gallery.list[1], data.gallery.list[3], options);
            }
            else {
                /* no right page */
				$(lclSettings.dom.page_set+' '+lclSettings.dom.right_page)
				.bookviewer('updateAdjacentPageState', data.gallery.list[0], data.gallery.list[3], options);
            }

			$(lclSettings.dom.page_navigation)
			.bookviewer('updatePageNavigation', data);
		},
		
		/**
		 * Formats title string to be used to update the document title
		 * @param {Object} data JSON response returned from AJAX script
		 * @returns {String|data.page_title|value.page_title}
		 */
		formatDocumentTitle: function( data ) {
			let lclSettings = $.extend(true, {}, settings, data.settings || {});
			let title = data.page_title;
			if (lclSettings.site_label) {
				title += ' | ' + lclSettings.album_type + ' | ' + lclSettings.site_label;
			}
			return (title);
		},

		/**
		 * Formats string that can be used as the current page set label.
		 * @param {Object} data JSON data response from page set ajax script
		 * @returns {String} Current page set label
		 */
		formatPageSetLabel: function( data) {
			let lclSettings = $.extend(true, {}, settings, data.settings || {});
			let label = '';
			if (data.layout===lclSettings.layouts.one_page) {
				return(data.gallery.list[0].title);
			}
			if (data.gallery.list[0].title) {
				/* left-hand page title */
				label = data.gallery.list[0].title;
			}
			if (data.gallery.list[1].title)
			{
				if(label) { 
					/* when combining left and right page titles, strip everything
					 * except the page number from the right-hand page title
					 */
					label += '/'+data.gallery.list[1].title.replace(/[^\d]/g, '');
				}
				else {
					/* we only have a page on the right */
					label = data.gallery.list[1].title;
				}
			}
			return (label);
		},

		updatePageState: function(current_page, preload_page, options) {

			let lclSettings = $.extend(true, {}, settings, options || {});
			return this.each(function() {

				/* create a page image */
				let $img = $('<img />')
						.prop({
							src: current_page.full.path,
							width: current_page.full.width,
							height: current_page.full.height,
							alt: current_page.title
						});

				/* insert page image into the DOM */
				$(this).html($img);
				
				/* update page values on navigation elements */
				$(this).data(lclSettings.keys.page, current_page.id);
				//if ($(this).hasClass(lclSettings.dom.right_page.substr(1))) {
					$(lclSettings.dom.next_page_button).data(lclSettings.keys.page, current_page.id);
				//}
				//else {
					$(lclSettings.dom.prev_page_button).data(lclSettings.keys.page, current_page.id);
				//}

				/**
				 * This could be set up better:
				 * - Logic for which pages to preload is moved to the AJAX script.
				 * - I don't think that it is pre-loading the page before in the 
				 *   case of 1-up layouts for example. 
				 * - Display pages are returned in the 'gallery' property of the 
				 *   JSON data. 
				 * - And preload pages (basically the next and previous spreads)
				 *   are returned in a 'preload' property of the JSON data.
				 * - All objects in the 'preload' array are attached to the DOM
				 *   with CSS class 'preload' to make them *visually* hidden, 
				 *   i.e. don't use "display:hidden" property for that CSS class.
				 */
				$('img', $(this)).load(function() {
					/* preload next page after the current one is finished loading */
					let preload_selector = lclSettings.dom.preload+(($(this).parent().hasClass(lclSettings.dom.right_page.substr(1)))?('1'):('0'));
					$(preload_selector).bookviewer('preloadPage', preload_page);
				});

				/* if previously a page image was unavailable in this slot */
				$(this).removeClass('page-unavailable');

				/* disable clicking when we're on the last page */
				if (current_page.is_first_page || current_page.is_last_page) {
					$(this)
					.addClass('page-oob')
					.data('enabled', 'disabled');
				}
				else if ($(this).hasClass('page-oob')) {
					$(this)
					.removeClass('page-oob')
					.data('enabled', 'enabled');
				}
			});
		},
		
        updateAdjacentPageState: function(current_page, preload_page, options) {

			let lclSettings = $.extend(true, {}, settings, options || {});
			return this.each(function() {

				$(this).data('p', current_page.id);
				let dir = $(this).data(lclSettings.keys.operation);
				if ($(this).data(lclSettings.keys.operation)==='fwd' && current_page.is_last_page) {
					/* at the last page of the book */
					$(this).addClass('page-oob').removeClass('page-unavailable');
					$(this).data('enabled', 'disabled');
					$(this).html('<!-- -->');
				}
				else if (dir==='back' && current_page.is_first_page) {
					/* at the first page of the book */
					$(this).addClass('page-oob').removeClass('page-unavailable');
					$(this).data('enabled', 'disabled');
					$(this).html('<!-- -->');
				}
				else {
					if ($(this).hasClass('page-oob')) {
						$(this).removeClass('page-oob');
						$(this).data('enabled', 'enabled');
					}
					$(this).addClass('page-unavailable');
					$(this).html('page unavailable<br />' + ((dir==='back') ? ('&lt;&lt; more') : ('more &gt;&gt;')));
					$(lclSettings.dom.preload+((dir==='back')?('0'):('1'))).bookviewer('preloadPage', preload_page, options);
				}
			});
        },

		updatePageNavigation: function(data) {

			let options = data.settings || {};
			let lclSettings = $.extend(true, {}, settings, options);

			return this.each(function() {

				let $prev_btn = $(lclSettings.dom.prev_page_button, $(this));
				let $next_btn = $(lclSettings.dom.next_page_button, $(this));

				if (data.layout===lclSettings.layouts.one_page) {
					/* sync up the page property value on the back & next 
					 * buttons with the new page 
					 **/
					$prev_btn.data(lclSettings.keys.page, data.gallery.list[0].id);
					$next_btn.data(lclSettings.keys.page, data.gallery.list[0].id);
				}

				if (data.gallery.list[0].is_first_page || (data.layout!==lclSettings.layouts.one_page && data.gallery.list[1].is_first_page)) {
					/* hide previous page button when we're on the first page of the album */
					$prev_btn.off('click', methods.changePage).hide();
				}
				else if ($prev_btn.is(':hidden')) {
					/* resore previous page button when we're moving off the first page of the album */
					$prev_btn.on('click', options, methods.changePage).show();
				}
				if (data.gallery.list[0].is_last_page || (data.layout!==lclSettings.layouts.one_page && data.gallery.list[1].is_last_page)) {
					/* hide next page button when we're on the last page of the album */
					$next_btn.off('click', methods.changePage).hide();
				}
				else if ($next_btn.is(':hidden')) {
					/* resore next page button when we're moving off the last page of the album */
					$next_btn.on('click', options, methods.changePage).show();
				}
			});
		},
		
		setPageSetSize: function( options ) {
			let lclSettings = $.extend(true, {}, settings, options || {});
			switch(lclSettings.layout) {
				case lclSettings.layouts.two_page:
					return (this.bookviewer('set2upPageSetSize', options));
					break;
				case lclSettings.layouts.one_page:
					return (this.bookviewer('set1upPageSetSize', options));
					break;
				default:
					$(lclSettings.dom.error_container).littled('displayError', 'Invalid page layout setting.');
					return;
			}
		},

		set2upPageSetSize: function( options ) {
			let lclSettings = $.extend(true, {}, settings, options || {});
			return this.each(function() {
				let $lp = $(this).find(lclSettings.dom.left_page+' img');
				let $rp = $(this).find(lclSettings.dom.right_page+' img');
				if ($rp.length < 1) {
					return;
				}
				let w = $rp.width();
				let h = $rp.height();
				if ($lp.length > 0) {
					if ($lp.width() > w) {
						w = $lp.width();
					}
					if ($lp.height() > h) {
						h = $lp.height();
					}
				}
				$(this).css('width', (w*2)+'px').css('height', h+'px');
				$(lclSettings.dom.left_page, $(this)).css('width', w+'px').css('height', h+'px');
				$(lclSettings.dom.right_page, $(this)).css('width', w+'px').css('height', h+'px');
				let pw = $(this).closest(lclSettings.dom.album_container).width();
				if (pw < (w*2)) {
					$(this).css('margin-left', '-'+Math.floor(((w*2)-pw)/2)+'px');
				}
			});
		},

		set1upPageSetSize: function( options ) {
			let lclSettings = $.extend(true, {}, settings, options || {});
			return this.each(function() {
				let $rp = $(this).find(lclSettings.dom.right_page+' img');
				if ($rp.length < 1) {
					return;
				}
				let w = $rp.width();
				let h = $rp.height();
				if (w > $(this).width()) {
					$(this).css('width', w+'px');
				}
				if (h > $(this).height()) {
					$(this).css('height', h+'px');
				}
				let $a = $(this).closest(lclSettings.dom.album_container);
				if ($a.length > 0) {
					if ($a.width() < w) {
						$(this).css('margin-left', '-'+Math.floor((w-$a.width())/2)+'px');
					}
				}
				if ($(this).height() > h) {
					$(lclSettings.dom.right_page, $(this)).css('margin-top', Math.floor(($(this).height() - h)/2)+'px');
				}
			});
		},
		
		preloadPage: function( preload_page ) {
			return this.css('background', 'url('+preload_page.full.path+') no-repeat -9999px -9999px');
		},

		/**
		 * Hook for hiding the header element when viewing the book, and revealing
		 * it again when the mouse moves over its minimized element.
		 * @param {int} delayOffset Amount of delay (in milisections) before the 
		 * "normal" header is hidden.
		 * @param {Object} options
		 * @returns {undefined}
		 */
        toggleHeader: function(delayOffset, options) {
			
			let lclSettings = $.extend(true, {}, settings, options || {});
			
            if (delayOffset === undefined) {
                delayOffset = 1000;
            }
			return this.each(function() {
				
				let $e = $(this);
				setTimeout(function() {
					
					/* hide the header element after a delay */
					$e
					.slideToggle('slow', function() {
						$(lclSettings.dom.minimized_header).show('slow');
					})
					
					/* also set up handler on the "normal" header element 
					 * that will set off an effect that will minimize the 
					 * "normal" header and replace it with a minimized version
					 * when the move leaves the "normal" header
					 */
					.mouseleave(function() {
						if ($(this).is(':visible')) {
							$(this).slideToggle('slow');
							$(lclSettings.dom.minimized_header).show('slow');
						}
					});
				}, delayOffset);
				
				/* handler that will hide the minimized header and 
				 * restore the "normal" header when the mouse moves over the 
				 * minimized header element. 
				 */
				$(lclSettings.dom.minimized_header)
				.on('mouseenter', function() {
					$(this).hide('slow', function() {
						if ($e.is(':hidden')) {
							$e.slideToggle('slow');
						}
					});
				});
			});
			
        },

		/**
		 * Handler for navigation with arrow keys.
		 * @param {Event} evt
		 * @returns {undefined}
		 */
		keyNavigation: function(evt) {
			let lclSettings = $.extend(true, {}, settings, evt.data || {});
			let arrows = { left: 37, up: 38, right: 39, down: 40 };
			let keyCode = evt.keyCode || evt.which;
			switch (keyCode) {
				case arrows.left:
					$(lclSettings.dom.page_set+' '+lclSettings.dom.prev_page_button)
					.trigger('click');
					break;
				case arrows.right:
					$(lclSettings.dom.page_set+' '+lclSettings.dom.next_page_button)
					.trigger('click');
					break;
				default:
					/* pass through */
					break;
			}
		},
		
        /** 
         * using JQuery UI Slider to create scrollbar functionality 
		 * @param {Object} options
         */
        initScrollbar: function(options) {

			let lclSettings = $.extend(true, {}, settings, options || {});
			
			return this.each(function() {

				let $scrollPane = $(lclSettings.dom.scroll_pane, $(this)),
					$scrollContent = $(lclSettings.dom.scroll_content, $(this));
				if ($scrollPane.length < 0 || $scrollContent.length < 0) {
					return;
				}

				/* don't build slider if the content doesn't overflow its container */
				if ($scrollContent.width() < $scrollPane.width()) {
					return;
				}

				//build slider
				let $scrollbar = $(lclSettings.dom.scrollbar, $(this)).slider({
					slide: function(e, ui) {
						if ($scrollContent.width() > $scrollPane.width()) {
							$scrollContent.css('margin-left', Math.round(ui.value / 100 * ($scrollPane.width() - $scrollContent.width())) + 'px');
						}
						else {
							$scrollContent.css('margin-left', 0);
						}
					}
				});

				//append icon to handle
				let handleHelper = $scrollbar.parent().find('.ui-slider-handle')
				.mousedown(function() {
					$scrollbar.width(handleHelper.width());
				})
				.mouseup(function() {
					$scrollbar.width('100%');
				})
				.append('<span class="ui-icon ui-icon-grip-dotted-vertical"></span>')
				.wrap('<div class="ui-handle-helper-parent"></div>').parent();

				//change overflow to hidden now that slider handles the scrolling
				$scrollPane.css('overflow', 'hidden');

				//size scrollbar and handle proportionally to scroll distance
				function sizeScrollbar() {

					let remainder = $scrollContent.width() - $scrollPane.width();
					if (remainder < 0) {
						remainder = 0;
					}
					let proportion = remainder / $scrollContent.width();
					let handleSize = $scrollPane.width() - (proportion * $scrollPane.width());
					$scrollbar.parent().find('.ui-slider-handle').css({
						width: handleSize,
						'margin-left': -handleSize / 2
					});
					handleHelper.width('').width($scrollbar.width() - handleSize);
				}

				//reset slider value based on scroll content position
				function resetValue() {
					let remainder = $scrollPane.width() - $scrollContent.width();
					let leftVal = $scrollContent.css('margin-left')==='auto' ? 0 : parseInt($scrollContent.css('margin-left'));
					let percentage = Math.round(leftVal / remainder * 100);
					$scrollbar.slider("value", percentage);
				}
				//if the slider is 100% and window gets larger, reveal content
				function reflowContent() {
					let showing = $scrollContent.width() + parseInt($scrollContent.css('margin-left'));
					let gap = $scrollPane.width() - showing;
					if (gap > 0) {
						$scrollContent.css('margin-left', parseInt($scrollContent.css('margin-left')) + gap);
					}
				}

				//change handle position on window resize
				$(window)
				.resize(function() {
					resetValue();
					sizeScrollbar();
					reflowContent();
				});

				//init scrollbar size
				setTimeout(sizeScrollbar, 10); //safari wants a timeout
			});
        }
	};

	$.fn.bookviewer = function( method ) {
 
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
			$.error('Method ' + method + ' does not exist on jQuery.bookviewer.');
		}
		return (false);
	};
}) ( jQuery );
