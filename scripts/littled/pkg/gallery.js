(function ($) {

	var settings = {
		allowSorting: true,
		editThumbnailURL: '_ajax/images/upload_image.php',
		linkThumbnailURL: '_ajax/images/set_thumbnail.php',
		unlinkThumbnailURL: '_ajax/images/unlink_thumbnail.php',
		deleteThumbnailURL: '_ajax/images/del_image.php',
		albumListingsURL: '_ajax/images/album_listings.php',
		galleryListingsURL: '_ajax/images/gallery_listings.php',
		/** @deprecated Use settings.dom.album_container instead. */
		albumContainer: '',
		/** @var string Selector used to target the element to be used to 
		 * display error messages specific to buttons, etc. */
		errorContainer: '.alert-error:first',
		statusContainer: '.alert-info:first',
		navigationContainer: '.page-links-container',
		/** @deprecated Use dom.album_filters in its place. */
		albumFiltersSelector: '.album-filters',
		/** @var {string} Selector for modal dialogs. */
		dialogContainer: '#globalDialog',
		csrfSelector: '#csrf-token',
		imageDialogClass: 'gallery-dialog',
		postGalleryUpdateCB: null,
		dom: {
			album_container: '.album-listings',
			album_filters: '.album-filters form:first',
			gallery_container: '.gallery-listings',
			gallery_filters: '.listings-filters',
			page_error_container: '.alert-error:first',
			error_container: '.alert-error',
			status_container: 'alert-info:first',
			dialog_container: 'tk',
			filters_form: '.album-filters form:first',
			csrf_selector: '#csrf-token'
		},
		listings: {
			album_listings_uri: 'tk',
			gallery_listings_uri: 'tk',
			album_container: 'tk',
			navigation_container: 'tk',
			album_filters_container: 'tk'
		},
		thumbnails: {
			edit_uri: 'tk',
			link_uri: 'tk',
			unlink_uri: 'tk',
			delete_uri: 'tk'
		},
		edit: { 
			/* defined in album_class or derived classes */
			id_param: 'abid',
			title_param: 'abti',
			slug_param: 'AlbumSlug',
			class_param: 'type',
			class_key: 'class',
			csrf_key: 'csrf',
			validate_slug_uri: '/_ajax/utils/validate_slug.php'
		},
		keys: {
			keyword_filter: 'flkw',
			record_id: 'id',
			content_type_id: 'tid',
			parent_id: 'pid',
			album_page: 'p',
			gallery_page: 'ip',
			operation: 'op',
			csrf: 'csrf'
		},
		previews: {
			/* tooltip thumbnail preview properties */
			rolloverSelector: '.gal-ro',
			className: 'tip-darkgray',
			alignTo: 'target',
			alignX: 'right',
			alignY: 'center',
			offsetX: 8,
			followCursor: true,
			content: function() {
				var $e = $('.tooltip-content', $(this).parent().parent());
				if ($e.length < 1) {
					updateCallback('Preview unavailable!');
				}
				return ($e.html());
			}
		}
	};

	var methods = {

		/**
		 * usage: 
		 * - options.animate property determines if the gallery is added to the page with an animation
		 * - options.postGalleryUpdateCB defines a callback function to run after the gallery content has been updated
		 * - options.galleryFilters associative array containing the filters used to fetch the gallery items
		 * @param {object} options (Optional) collection of settings that will
		 * override the library's default settings.
		 */
		retrieveGallery: function(options) {

			var lclSettings = $.extend(true, {}, settings, options || {});

			return this.each(function() {

				var $e = $(this);
				var pid = $e.data(lclSettings.keys.parent_id);
				var tid = $e.data(lclSettings.keys.content_type_id);
				if (!pid) {
					$(lclSettings.dom.page_error_container).littled('displayError', 'Parent not set.');
					return;
				}
				if (!tid) {
					$(lclSettings.dom.page_error_container).littled('displayError', 'Content type not set.');
					return;
				}
				if (lclSettings.hasOwnProperty('galleryFilters')===false) {
					lclSettings.galleryFilters = {};
				}
				var fd = $.extend({}, lclSettings.galleryFilters, {pid: pid, tid: tid});
				fd[lclSettings.edit.csrf_key] = $(lclSettings.csrfSelector).html(); 

				$.littled.retrieveContentOperations(tid, function(data) {

					var url = data.listings_uri || $.littled.getRelativePath() + settings.galleryListingsURL;

					/* ajax call to get pages */
					$.post(
						url,
						fd,
						function(data) {

							if (data.error) {
								$(lclSettings.dom.page_error_container).littled('displayError', data.error);
								return;
							}

							/* add pages to DOM */	
							$e.html($.littled.htmldecode(data.content));
							$e.galleries('bindGalleryHandlers', options);
							if (lclSettings.hasOwnProperty('animate') && lclSettings.animate===true) {
								$('tr.gallery-row[data-id='+pid+']').galleries('animateGalleryToggle', options);
							} 
						},
						'json'
					)
					.success(function() {
						/* hook for code after page content is refreshed */
						if (lclSettings.postGalleryUpdateCB) {
							lclSettings.postGalleryUpdateCB.apply(this, arguments);
						}
					})
					.fail(function(xhr) {
						/* xhr.status is 0 when navigating away from the page
						 * before the ajax script has send a response. Ignore 
						 * those errors. Not sure if there are errors that would 
						 * generate a status of 0 that should not be ignored tho?
						 */
						if (xhr.status !== 0) {
							$(lclSettings.dom.page_error_container).littled('ajaxError', xhr);
						}
					});
				});
			});
		},

		/**
		 * shows/hides a page from the gallery attached to a book 
		 * @param {eventObject} evt
		 * @param {object} options (Optional) collection of settings that will
		 * override the library's default settings.
		 */
		toggleGallery: function( evt, options ) {

			var lclSettings = $.extend(true, {}, settings, options || {});

			var id = $(this).data('id');
			var icons = $(this).button('option', 'icons');

			var $p = $('tr.gallery-row[data-id='+id+']');
			if ($p.length < 1) {
				$(lclSettings.dom.page_error_container).littled('displayError', 'Gallery could not be located.');
				return;
			}
			if (icons.primary === 'ui-icon-expand') {
				/* expanding */
				var $g = $(lclSettings.dom.gallery_container+' table:first', $p);
				/* test if gallery content for this gallery has already been retrieved. */
				if ($g.length < 1) {
					/* get album filters to apply them to the gallery */
					var af, $af = $(lclSettings.dom.album_filters);
					if ($af.length > 0) {
						af = $af.serializeObject({}, ['pl','next','filt']);
					}
					/* filters controlling content retrieved by ajax */
					var filters = $.extend({}, {pid: id}, af || {});

					/* retrieve gallery content */
					$(lclSettings.dom.gallery_container, $p).galleries('retrieveGallery', {galleryFilters: filters, animate: true});
				}
				else {
					var $l = $(lclSettings.dom.gallery_container, $p);
					if ($l.hasClass('functional')===false) {
						$l.galleries('bindGalleryHandlers');
					}
 					$p.galleries('animateGalleryToggle', options);
					$p.galleries('configureSorting', options);
				}
			} 
			else {
				/* collapsing */
				$p.galleries('animateGalleryToggle', options);
			}
		},

		/**
		 * Animates expanding or collapsing a gallery. Updates UI accordingly.
		 * @param {object} options
		 * @returns {gallery_L1.methods@call;each}
		 */
		animateGalleryToggle: function( options ) {
			var lclSettings = $.extend(true, {}, settings, options || {});
			return this.each(function() {
				var id = $(this).data('id');
				var $btn = $('#toggle-'+id+'-btn');
				var icons = $btn.button('option', 'icons');
				if (icons.primary==='ui-icon-expand') {
					/* expanding */
					$btn.button('option', 'icons', {primary: 'ui-icon-collapse'});
					$(this).show();
					$(lclSettings.dom.gallery_container, $(this)).slideToggle('slow');
				}
				else {
					/* collapsing */
					$(lclSettings.dom.gallery_container, $(this)).slideToggle('', function() {
						$(this).hide();
					});
					var $t = $(lclSettings.dom.gallery_container+' table:first', $(this));
					if ($t.length > 0) {
						if ($t.hasClass('ui-sortable')) {
							/* undo sorting */
							$t.sortable('destroy');
						}
					}
					$btn.button('option', 'icons', {primary: 'ui-icon-expand'});
				}
			});
		},

		/**
		 * Binds handlers for album listings.
		 * Intended to be called for the listings container after it's loaded within the document.
		 * @param {object} options (Optional) collection of settings that will
		 * override the library's default settings.
		 */
		bindAlbumListingsHandlers: function ( options ) {

			var lclSettings = $.extend(true, {}, settings, options || {});

			return this.each(function() {

				$(this)

				/* re-bind these handlers if the listings content changes */
				.unbind('contentUpdate', methods.updateAlbumListingsHandlers)
				.bind('contentUpdate', lclSettings, methods.updateAlbumListingsHandlers)

				/* enable inline keyword edits */
				.keywords('bindListingsHandlers')
				
				/* change styling of gallery listings on click to highlight elements in the listings */
				.off('click', 'tr.list', methods.toggleListHighlight)
				.on('click', 'tr.list', methods.toggleListHighlight)

				.off('click', 'tr.list-alt', methods.toggleListHighlightAlt)
				.on('click', 'tr.list-alt', methods.toggleListHighlightAlt)

				/* page navigation links */
				.off('click', '.alb-nav', methods.gotoAlbumPage)
				.on('click', '.alb-nav', options, methods.gotoAlbumPage)

				/* listings utility button handlers */
				.off('click', '.toggle-pages-btn', methods.toggleGallery)
				.on('click', '.toggle-pages-btn', methods.toggleGallery)

				.off('click', '.add-page-btn', methods.editImage)
				.on('click', '.add-page-btn', options, methods.editImage)

				.off('click', '.add-page-link', methods.editImage)
				.on('click', '.add-page-link', options, methods.editImage)

				.off('click', '.update-cache-btn', $.littled.updateCache)
				.on('click', '.update-cache-btn', {
					errorContainer: lclSettings.dom.error_container,
					statusContainer: lclSettings.statusContainer
				}, $.littled.updateCache)
				
				/* enable inline edits on dbl-click */
				.off('dblclick', '.inline-edit-cell', methods.editCell)
				.on('dblclick', '.inline-edit-cell', options, methods.editCell)

				.off('galleryLoaded', '.pages-row', methods.retrieveGallery)
				.on('galleryLoaded', '.pages-row', options, methods.retrieveGallery);

				/* drag'n'drop listings sorting */
				if(lclSettings.allowSorting===true) {
					$(this).galleries('bindAlbumResorting', options);
				}

				/* listings utility buttons */
				$('.details-btn', $(this)).iconButton('details');
				$('.edit-btn', $(this)).iconButton('edit');
				$('.print-btn', $(this)).iconButton('print');
				$('.trash-btn', $(this)).iconButton('trash');
				$('.preview-btn', $(this)).iconButton('link');
				$('.toggle-pages-btn', $(this)).iconButton('expand');
				$('.add-page-btn', $(this)).iconButton('addchild');
				$('.update-cache-btn', $(this)).iconButton('cache');

				/* roll-over thumbnail previews */
				$(lclSettings.previews.rolloverSelector, $(this)).poshytip(lclSettings.previews);

				$('.img-details-link', $(this)).formDialog({
					dialogClass: lclSettings.imageDialogClass,
					dom: { listings_container: lclSettings.dom.album_container }
				});

				$('.trash-btn', $(this)).formDialog({
					dialogClass: 'ui-dialog-delete',
					dom: { listings_container: lclSettings.dom.album_container }
				});

				/* hide the gallery containers on the listings page to prime 
				 * them for the slide-toggle effect (which will hide them if 
				 * they are visible initially) */
				$(lclSettings.dom.gallery_container).addClass('hidden');
			});
		},

		updateAlbumListingsHandlers: function( evt ) {
			$(this).galleries('bindAlbumListingsHandlers', evt.data || {});
		},

		/* 
		 * Binds handlers to buttons within gallery listings. 
		 * Should be called any time the gallery listings are updated.
		 * @param {object} options (Optional) list of settings that will 
		 * override the library's default settings.
		 */
		bindGalleryHandlers: function( options ) {

			var lclSettings = $.extend(true, {}, settings, options || {});

			return this.each(function() {
				/* utility buttons: details, edit, delete */
				$(this)

				/* rebind these handlers after content updates */
				.off('contentUpdate', methods.updateGalleryHandlers)
				.on('contentUpdate', lclSettings, methods.updateGalleryHandlers)

				.galleries('bindFilterElementsHandlers', options)
				.galleries('configureSorting', options)
				.galleries('bindImageOverlayHandlers', options)
				.keywords('bindListingsHandlers', options)

				/* enable inline edits on dbl-click */
				.off('dblclick', '.inline-edit-cell', methods.editCell)
				.on('dblclick', '.inline-edit-cell', methods.editCell)
		
				.off('click', '.add-img-btn', methods.editImage)
				.on('click', '.add-img-btn', options, methods.editImage)

				.off('click', '.edit-img-btn', methods.editImage)
				.on('click', '.edit-img-btn', options, methods.editImage)

				.off('click', '.update-cache_btn', $.littled.updateCache)
				.on('click', '.update-cache_btn', {
					errorContainer: lclSettings.dom.error_container,
					statusContainer: lclSettings.statusContainer
				}, $.littled.updateCache)

				.off('click', '.gal-nav', methods.gotoGalleryPage)
				.on('click', '.gal-nav', options, methods.gotoGalleryPage)
		
				.addClass('functional');

				/* operation button handlers */
				var imageDialogSettings = {
					dialogClass: lclSettings.imageDialogClass,
					formLoadCB: function() {
						var $d = $(lclSettings.dialogContainer+':visible');
						if ($d.length < 1) {
							return;
						}
						/* dismiss the full-sized image when clicked */
						$('.image-detail').on('click', function(evt) {
							evt.preventDefault();
							$d.formDialog('close');
						});
					},
					dom: { listings_container: lclSettings.dom.gallery_container }
				};
				$('.img-details-btn', $(this))
				.iconButton('details')
				.formDialog(imageDialogSettings);
				$('.img-details-link', $(this))
				.formDialog(imageDialogSettings);
				$('.add-img-btn', $(this)).iconButton('addchild');
				$('.edit-img-btn', $(this)).iconButton('edit');

				$('.delete-img-btn', $(this))
				.iconButton('trash')
				.formDialog({
					dialogClass: 'ui-dialog-delete',
					dom: { listings_container: lclSettings.dom.gallery_container},
					callbacks: { update_content: methods.updateGalleryListingsContent}
				});

				/* thumbnail previews on hover */
				if (jQuery().poshytip) {
					$(lclSettings.previews.rolloverSelector, $(this)).poshytip(lclSettings.previews);
				}

				/* date textboxes within gallery filters */
				$('.datepicker', $(this)).datepicker();
			});
		},

		updateGalleryHandlers: function( options ) {
			var lclSettings = $.extend(true, {}, settings, options || {});
			$(this).galleries('bindGalleryHandlers');

			/* hook for code after page content is refreshed */
			if (lclSettings.postGalleryUpdateCB) {
				lclSettings.postGalleryUpdateCB.apply(this, arguments);
			}
		},
		
		updateGalleryListingsContent: function( data ) {
			/* target the specific gallery when there is more than one on a page */
			var lclSettings = $.extend(true, {}, settings, data.settings || {});
			var gallery_selector = lclSettings.dom.gallery_container+'[data-pid='+data.parent_id+']';
			data.settings = {
				dom: {listings_container: gallery_selector}
			};
			$(gallery_selector).listings('updateListingsContent', data);
		},

		/* 
		 * Binds handlers for edit and delete buttons that sit on top of images
		 * @param {Object} options
		 */
		bindImageOverlayHandlers: function( options ) {

			var lclSettings = $.extend(true, {}, settings, options || {});

			return this.each(function() {

				/* re-bind handlers when the image is edited */
				$(this)
				.off('contentUpdate', methods.updateImageOverlayHandlers)
				.on('contentUpdate', lclSettings, methods.updateImageOverlayHandlers)

				/* record operations buttons */
				.off('click', '.img-upload-btn', methods.updateThumbnail)
				.on('click', '.img-upload-btn', options, methods.updateThumbnail)

				.off('click', '.sel-galltn-btn', methods.updateThumbnail)
				.on('click', '.sel-galltn-btn', options, methods.updateThumbnail)

				.off('click', '.add-img-btn', methods.editImage)
				.on('click', '.add-img-btn', options, methods.editImage)

				/**
				 * This is the class of the container for images in a gallery 
				 * that is displayed as a list of full-sized images, as 
				 * opposed to a table listing properties of the images 
				 * in separate columns 
				 **/
				.off('click', '.gallery-edit', methods.bindGalleryEditButtons)
				.off('dblclick', '.gallery-edit', methods.detailsDialog)
				.on('click', '.gallery-edit', options, methods.bindGalleryEditButtons)
				.on('dblclick', '.gallery-edit', options, methods.detailsDialog)

				/* gallery thumbnail */
				.off('click', '.gallery-tn-container', methods.bindThumbnailEditButtons)
				.off('dblclick', '.gallery-tn-container', methods.detailsDialog)
				.on('click', '.gallery-tn-container', options, methods.bindThumbnailEditButtons)
				.on('dblclick', '.gallery-tn-container', options, methods.detailsDialog);

				$('.img-upload-btn', $(this)).button();
				$('.sel-galltn-btn', $(this)).button();
				$('.add-img-btn', $(this)).iconButton('addchild');
			});
		},

		/**
		 * Handler for content updates. Re-binds image overlay handlers.
		 * @param {Event} evt
		 * @returns {undefined}
		 */
		updateImageOverlayHandlers: function( evt ) {
			$(this).galleries('bindImageOverlayHandlers', evt.data || {});
		},

		/**
		 * Load button images and bind functionality for edit buttons that sit on top of images
		 */
		bindGalleryEditButtons: function() {

			/* get pointer to overlay image edit buttons */
			var $o = $('#img-overlay-btns');
			if ($o.length < 1) {
				return;
			}

			/* update image container to display edit buttons on top */
			var $e = $(this);
			$e.addClass('active-edit');

			/* edit button handlers */
			$('#edit-overlay-btn')
			.off('click', methods.editImage)
			.on('click', methods.editImage);
			$('#edit-overlay-btn').data('id', $e.data('id'));
			$('#edit-overlay-btn').data('pid', $e.data('pid'));
			$('#edit-overlay-btn').data('tid', $e.data('tid'));

			/* delete button handlers */
			$('#del-overlay-btn').data('id', $e.data('id'));
			$('#del-overlay-btn').data('pid', $e.data('pid'));
			$('#del-overlay-btn').data('tid', $e.data('tid'));
			$('#del-overlay-btn').formDialog({dialogClass: 'ui-dialog-delete'});

			/* position edit buttons on top of image */
			var offset = $e.offset();
			$o.css('top', offset.top+6).css('left', offset.left+6).show();

			/* hide & disable edit buttons if they aren't used right away */
			setTimeout(function() {
				$e.removeClass('active-edit');
				$('#edit-overlay-btn').off('click', methods.editImage);
				$('#del-overlay-btn').formDialog('cancel');
				$o.hide();
			}, 4000); /* 4 seconds */
		},

		/**
		 * Binds edit buttons that sit on top of gallery thumbnail images.
		 * @param {Event} evt
		 */
		bindThumbnailEditButtons: function( evt ) {
			
			evt.preventDefault();
			var options = evt.data || {};
			
			var $o = $('#tn-overlay-btns');
			if ($o.length < 1) { 
				return;
			}

			$(this).addClass('active-edit');

			/* update properties of the edit buttons with those of their parent, the thumbnail image */
			var $edit_btn = $('#edit-galltn-btn');
			var $del_btn = $('#del-galltn-btn');

			$edit_btn.on('click', options, methods.updateThumbnail);
			$del_btn.on('click', options, methods.updateThumbnail);
			
			$edit_btn.data('id', $(this).data('id'));
			$edit_btn.data('pid', $(this).data('pid'));
			$edit_btn.data('tid', $(this).data('tid'));
			$edit_btn.data('op', $(this).data('op'));
			$edit_btn.data('rand', $(this).data('rand'));

			$del_btn.data('id', $(this).data('id'));
			$del_btn.data('pid', $(this).data('pid'));
			$del_btn.data('tid', $(this).data('tid'));
			$del_btn.data('op', 'del-' + $(this).data('op'));

			/* positions edit buttons relative to the thumbnail image, on top of the image */
			var offset = $(this).offset();
			$o.css('top', offset.top+6).css('left', offset.left+6).show();

			/* hides the edit buttons if they remain inactive for a period of time */
			var $e = $(this);
			setTimeout(function() {
				$e.removeClass('active-edit');
				$edit_btn.off('click', methods.updateThumbnail);
				$del_btn.off('click', methods.updateThumbnail);
				$o.hide();
			}, 4000); /* 4 seconds */
		},

		/**
		 * Binds handlers to DOM elements that will validate the current 
		 * slug value in the form. 
		 * - If the slug is found to be invalid, the slug value in the form input
		 * will be updated with whatever is returned by the AJAX scirpt.
		 * @param {object} options (Optional) collection of settings that will
		 * override the library's default settings.
		 * @returns {_L1.methods@call;each}
		 */
		bindSlugWatch: function( options ) {
			var lclSettings = $.extend(true, {}, settings, options || {});
			return this.on('blur', lclSettings, methods.validateSlug);
		},

		/** 
		 * Callback for events that require a slug value to be validated.
		 * @param {eventObject} evt
		 * @returns {undefined}
		 */
		validateSlug: function( evt ) {

			/* local configuration */
			var lclSettings = $.extend(true, {}, settings, evt.data || {});

			$(this).closest('div').children('.alert-error:visible').fadeOut('fast');

			/* collect form data to pass to ajax script */
			var $f = $(this).closest('form');
			var fd = {};
			fd[lclSettings.edit.csrf_key] = $(lclSettings.csrfSelector).html();
			fd[lclSettings.edit.id_param] = $('input[name="'+lclSettings.edit.id_param+'"]', $f).val();
			fd[lclSettings.edit.title_param] = $('input[name="'+lclSettings.edit.title_param+'"]', $f).val();
			fd[lclSettings.edit.slug_param] = $('input[name="'+lclSettings.edit.slug_param+'"]', $f).val();
			fd[lclSettings.edit.class_key] = $('input[name="'+lclSettings.edit.class_param+'"]', $f).val();
			
			/* if this is a new record, and the slug is being manually 
			 * editted, validate the slug value & ignore the title value 
			 * (by default the ajax script will always use the title value & 
			 * ignore the slug value for new records)
			 */
			if (!fd[lclSettings.edit.id_param]) {
				if ($(this).attr('name') === lclSettings.edit.slug_param) {
					fd[lclSettings.edit.title_param] = fd[lclSettings.edit.slug_param];
					fd[lclSettings.edit.slug_param] = '';
				}
			}
			
			$.post(
				lclSettings.edit.validate_slug_uri,
				fd,
				function(data) {
					if (data.content) {
						/* slug input element */
						var $i = $('input[name="'+lclSettings.edit.slug_param+'"]', $f);
						/* update slug value in form */
						$i.val(data.content);
					}
					if (data.error) {
						if (data.content) {
							/* slug input's immediate parent element */
							var $c = $i.closest('div');
							/* error container attached to parent element */
							var $e = $('.alert-error', $c);
							if ($e.length < 1 ) {
								var $e = $('<div class=\"alert alert-error hidden\"></div>');
								$c.prepend($e);
							}
							/* display error message */
							$e.html(data.error);
							$e.fadeIn('slow');
						}
						else {
							$(lclSettings.dom.error_container).littled('displayError', data.error);
						}
						return;
					}
				},
				'json'
			)
			.fail(function(xhr) {
				$(lclSettings.dom.error_container).littled('ajaxError', xhr);
			});
		},

		/**
		 * Click handler that opens modal dialog containing controls to edit an 
		 * image record.
		 * @param {Event} evt Event object.
		 * @returns {undefined}
		 */
		editImage: function( evt ) {
			evt.preventDefault();
			// var lclSettings = $.extend(true, {}, settings, evt.data || {});
			if (evt.hasOwnProperty('data')) {
				delete evt.data;
			}
			/* selector to target only this specific gallery container. */
			var selector = methods.getListingsSelector.apply(this, evt.data);
			evt['data'] = { dom: {listings_container: selector}};
			$(this).formDialog('open', evt);
		},

		getListingsSelector: function( options ) {
			var lclSettings = $.extend(true, {}, settings, options || {});
			return (lclSettings.dom.gallery_container+'[data-'+lclSettings.keys.parent_id+'='+$(this).data(lclSettings.keys.parent_id)+']');
		},

		/**
		 * Wrapper for $.listings('editCell') that can be used when binding handlers 
		 * within this library.
		 * @param {Event} evt
		 * @returns {undefined}
		 */
		editCell: function( evt ) {
			evt.stopPropagation();
			$(this).listings('editCell', evt);
		},
		
		updateThumbnail: function( evt ) {

			evt.preventDefault();
			var options = evt.data || {};
			var lclSettings = $.extend(true, {}, settings, options);

			var op = $(this).data('op');

			var fd = $(this).formDialog('retrieveFormFilters', evt.data || {});
			if ((op!=='upload' && op!=='del-upload' && fd.pid===undefined) || fd.tid===undefined) {
				$(lclSettings.dom.error_container).littled('displayError', 'Gallery properties not set.');
				return;
			}
			fd[lclSettings.edit.csrf_key] = $(lclSettings.csrfSelector).html();

			var url = methods.getThumbnailURI(op, lclSettings);

			/* get dialog content */
			$.post(url, fd, 
				function(data) {

					/* override the default action after the edit form is loaded
					 * to override the default submit handler
					 */
					var dialogOptions = {formLoadCB: methods.displayThumbnailDialog};
					if (op.indexOf('del')===0) {
						dialogOptions.dialogClass = 'ui-dialog-delete';
					}
					data.settings = $.extend(true, {}, options, dialogOptions);

					/* display the thumbnail edit dialog after the load handler has been configured
					 */
                    if ($(lclSettings.dialogContainer).is(':visible')===false) {
                        $(lclSettings.dialogContainer).formDialog('display', data);
                    }
				},
				'json'
			)
			.fail(function(xhr) {
				$(lclSettings.dom.error_container).littled('ajaxError', xhr);
			});
		},

		/** 
		 * clear existing handlers and attach new handler to "commit" button 
		 * in the dialog.
		 * @param {object} options Collection of options that will override the library's 
		 * default settings.
		 */
		displayThumbnailDialog: function( options ) {
			var lclSettings = $.extend(true, {}, settings, options || {});
			$(lclSettings.dialogContainer+' .dlg-commit-btn').off('click');
			$(lclSettings.dialogContainer+' .dlg-commit-btn')
			.on('click', options, methods.commitThumbnailUpdate);
		},

		/**
		 * Handler for the "submit" button in the change thumbnail dialog.
		 * - Sends form data to ajax script that will make the updates.
		 * - Closes the thumbnail dialog.
		 * - Uses markup returned from the ajax script to update page content.
		 * @param {eventObject} evt
		 * @returns {undefined}
		 */
		commitThumbnailUpdate: function(evt) {

			evt.preventDefault();

			var lclSettings = $.extend(true, {}, settings, evt.data || {});
			var $f = $(lclSettings.dialogContainer+' form:first');
			
			/* collect the operation type */
			var op, $i = $('input[name=op]', $f);
			if ($i.length) {
				op = $i.val();
			}
			if (!op) {
				$(lclSettings.dom.error_container).littled('displayError', 'Operation not specified.');
				return;
			}

			/* AJAX URL for selecting thumbnail images */
			var url = methods.getThumbnailURI(op, lclSettings);

			if (op==='select') {
				/* when selecting a new thumbnail from existing images in a 
				 * gallery, the jQuery form plugin for some reason doesn't pass
				 * along the values in a way that the PHP script can collect them.
				 * Use jQuery's $.post() routine in these cases.
				 */
				$.post(
					url, 
					$f.serializeObject(), 
					function(data) {
						data.options = evt.data || {};
						methods.processThumbnailUpdate(data);
					}, 
					'json'
				)
				.fail(function(xhr) { 
					$(lclSettings.dom.error_container).formDialog('ajaxError', xhr); 
				});
			}
			else {
				/* when uploading a thumbnail image file, jQuery's $.post()
				 * routine can't pass along the binary data. Use jQuery form
				 * plugin in these cases.
				 */
				$f.ajaxSubmit({
					type: 'post',
					url: url,
					dataType: 'json',
					success:  function(data) {
						data.options = evt.data || {};
						methods.processThumbnailUpdate(data);
					},
					error: function(xhr) {
						$(lclSettings.dom.error_container).littled('ajaxError', xhr);
					}
				});
			}
		},

		processThumbnailUpdate: function(data) {

			var lclSettings = $.extend(true, {}, settings, data.options || {});

			if (data.error) {
				$(lclSettings.dom.error_container).littled('displayError', data.error);
				return;
			}

			$(lclSettings.dialogContainer).formDialog('close');

			if (data.container_id) {

				/*
				 * update the parent content container
				 * with content to reflect the edit of the 
				 * thumbnail element. 
				 */
				var $e = $(data.container_id);
				if ($e.length) {
					$e.html($.littled.htmldecode(data.content));
					$(data.container_id).galleries('bindImageOverlayHandlers');
				}
			}
		},

		getThumbnailURI: function(op, options) {
			var lclSettings = $.extend(true, {}, settings, options || {});
			switch(op) {
				case 'upload':
				case 'edit':
					return($.littled.getRelativePath() + lclSettings.editThumbnailURL);
				case 'link':
				case 'select':
					return($.littled.getRelativePath() + lclSettings.linkThumbnailURL);
				case 'unlink':
				case 'del-link':
					return($.littled.getRelativePath() + lclSettings.unlinkThumbnailURL);
				case 'delete':
				case 'del-upload':
					return($.littled.getRelativePath() + lclSettings.deleteThumbnailURL);
				default:
					$(lclSettings.dom.error_container).littled('displayError', 'Unhandled operation.');
					return('');
			}
		},

		/**
		 * @deprecated Use $.dialogEdit() instead.
		 * Deletes image records from album. Displays confirmation dialog.
		 * Intended to be used as inline AJAX routine as a part of image listings.
		 * @param {object} options (Optional) Settings used to override the 
		 * library's default settings.
		 */
		deleteRecord: function(options) { },

        collectPageNavigationVariables: function( options ) {
            var lclSettings = $.extend(true, {}, settings, options || {});
            var extras= {};
            extras[lclSettings.keys.album_page] = $(this).data(lclSettings.keys.album_page);
            extras[lclSettings.keys.content_type_id] = $(lclSettings.dom.album_container+' table:first').data(lclSettings.keys.content_type_id);
            if (extras[lclSettings.keys.content_type_id]===null) {
                extras[lclSettings.keys.content_type_id] = $(lclSettings.dom.album_container).data(lclSettings.keys.content_type_id);
            }
            extras[lclSettings.keys.parent_id] = $(lclSettings.dom.album_container+' table:first').data(lclSettings.keys.parent_id);
            return(extras);
        },

        validatePageNavigationVariables: function( data, options ) {
            var lclSettings = $.extend(true, {}, settings, options || {});
            if (data[lclSettings.keys.content_type_id]===null) {
                $(lclSettings.dom.error_container).littled('displayError', "Content type could not be determined through the attributes of the listings container, or the first table nested in the container.");
                return (false);
            }
            return(true);
        },

		/**
		 * Page navigation handler for albums.
		 * @param {Event} evt Pass any settings with the evt.data property.
		 */
		gotoAlbumPage: function(evt) {

			evt.preventDefault();
			var options = evt.data || {};
			var lclSettings = $.extend(true, {}, settings, options);

            var extras = $(this).galleries('collectPageNavigationVariables', options);
            if ($(this).galleries('validatePageNavigationVariables', extras, options)===false) {
                return;
            }

			$('.sm-process-wheel').addClass('sm-active-wheel');
			$('#status-container').fadeOut('fast');

			/* retrieve active listings filters */
			var fd = $(lclSettings.dom.album_filters).littled(
				'collectFormDataWithCSRF', 
				evt.data || {}, 
				extras);

			$.littled.retrieveContentOperations(fd[lclSettings.keys.content_type_id], function(data) {

				if (data.error) {
					$(lclSettings.dom.error_container).littled('displayError', data.error);
					return;
				}

				var listings_url = $.littled.getRelativePath() + lclSettings.albumListingsURL;
				if (data.listings_uri) {
					listings_url = data.listings_uri;
				}

				$.ajax({
					type: 'post',
					url: listings_url, 
					data: fd,
					dataType: 'json'
				})
				.success(function(data) {
					data.settings = options;
					data.page = extras[lclSettings.keys.album_page];
					$(lclSettings.dom.album_container).galleries('updateAlbumContent', data);
				})
				.fail(function(xhr) {
					$(lclSettings.dom.error_container).littled('ajaxError', xhr);
				});
			});
		},

		/**
		 * Refreshes album content on the page using the JSON response from an
		 * AJAX script.
		 * @param {Object} data JSON returned from AJAX script.
		 * @returns {undefined}
		 */
		updateAlbumContent: function(data) {

			var lclSettings = $.extend(true, {}, settings, data.settings || {});

			if (data.error) {
				$(lclSettings.dom.error_container).littled('displayError', data.error);
				return;
			}

			/* current page within the listings */
			var p = ((data.hasOwnProperty('page'))?(data.page):(1));
			if (!p) {
				p = 1;
			}

			$('.sm-process-wheel').removeClass('sm-active-wheel');
			var evt = $.Event('contentUpdate');
			evt.data = data.settings || {};
			$(lclSettings.dom.album_container).html($.littled.htmldecode(data.content));
			$(lclSettings.dom.album_container)
			.data('p', p)
			.trigger(evt);
		},

		/**
		 * pagination handlers for gallery pages 
		 * @param {Event} evt
		 */
		gotoGalleryPage: function( evt ) {

			evt.preventDefault();
			var options = evt.data || {};
			var lclSettings = $.extend(true, {}, settings, options);

			var $gallery = $(this).parents(lclSettings.dom.gallery_container+':first');
			var $f = $(lclSettings.dom.gallery_filters, $gallery);
			var extras = {};
			extras[lclSettings.keys.parent_id] = $gallery.data(lclSettings.keys.parent_id);
			extras[lclSettings.keys.album_page] = $(this).data(lclSettings.keys.album_page);

			/* update value in form for future operations */
			$('input[name='+lclSettings.keys.gallery_page+']', $f).val(extras[lclSettings.keys.album_page]);
			var filters = $.extend({}, extras, $('input, select', $f).serializeObject());
			$gallery.galleries('retrieveGallery', $.extend(true, {}, options, {
				galleryFilters: filters, 
				animate: false
			}));
		},

		configureSorting: function(options) {
			var lclSettings = $.extend(true, {}, settings, options || {});
			return this.each(function() {
				/* get gallery listings table */
				var $t = $('table:first', $(this));
				if ($t.length > 0) {
					$t.sortable({
						items: 'tr.page-row',
						update: function(event, ui) { $(this).resort('resort', event, ui); }
					});
				}
				else if ($(this).is('div')){
					$(this).sortable({
						items: '.page-cell',
						update: function(event, ui) { $(this).resort('resort', event, ui); }
					});
				}
			});
		},

		bindFilterElementsHandlers: function( options ) {

			var lclSettings = $.extend(true, {}, settings, options || {});

			return this.each(function() {
				var $c = $('.gallery-page-nav:first', $(this));
				$('input[name='+lclSettings.keys.keyword_filter+']', $c).keypress(function(evt) {
					var kp = ((window.event) ? (window.event.keyCode) : (evt.which));
					if (kp===13) { /* enter key */
						/* trigger event that will in turn submit form data */
						$('.gallery-filter-btn:first', $c).trigger('click');
					}
				});
				$('.gallery-filter-btn:first', $c)
				.button()
				.off('click', methods.submitGalleryFilters)
				.on('click', options, methods.submitGalleryFilters);
			});
		},

		/**
		 * Binds jQuery UI $.sortable() behavior on the listings.
		 * @param {Object} options
		 * @returns {_L1.methods@call;each}
		 */
		bindAlbumResorting: function ( options ) {

			var lclSettings = $.extend(true, {}, settings, options || {});
			return this.each(function() {
				
				/* check the filters form element for current listings filters */
				var has_filtered_listings = false;
				var $f = $(lclSettings.dom.filters_form);
				if ($f.length > 0) {
					var fd = $f.serializeObject();
					has_filtered_listings = methods.hasFilteredListings(fd);
				}
				
				/* only allow re-sorting if no filters are applied to the listings */
				if (has_filtered_listings===false) {
					/* drag-n-drop resorting of gallery listings */
					$('table:first', $(this)).resort('bindResort', options);
				}
			});
		},

		submitGalleryFilters: function( evt ) {

			evt.preventDefault();
			var options = evt.data || {};
			var lclSettings = $.extend(true, {}, settings, evt.data || {});

			var $f = $(this).closest('form');

			var fd = $('input, select', $f).serializeObject(); 

			/* always start at the first page after applying new filters */
			if (fd.hasOwnProperty(lclSettings.keys.gallery_page)) {
				delete fd[lclSettings.keys.gallery_page];
			}
			$f.closest(lclSettings.dom.gallery_container)
			.galleries('retrieveGallery', $.extend(true, {}, options, {
				galleryFilters: fd, 
				animate: false
			}));
		},

		/**
		 * Attempts to determine if the album listings are currently being 
		 * filtered by their content in some way. Returns true if they are, 
		 * false otherwise.
		 * @param {Object} fd Current form filters as collection of keys and values.
		 * @returns {Boolean} TRUE if a value for a content filter is found in 
		 * the collection
		 */
		hasFilteredListings: function(fd) {
			var prop, exclude = $.extend({}, {p:'', pl: '', filt:'', ifilt:'', next: '', inext: '', ip: '', ipl: ''});
			for (prop in fd) {
				if (fd.hasOwnProperty(prop)) {
					if ((exclude.hasOwnProperty(prop)===false) && Object.prototype.hasOwnProperty.call(fd, prop)) {
						return ((fd[prop])?(true):(false));
					}
				}
			}
			return false;
		},

		toggleListHighlight: function() {
			$(this).toggleClass('md');
		},

		toggleListHighlightAlt: function() {
			$(this).toggleClass('md-alt');
		},

		/**
		 * @deprecated Use $.formDialog('bindDialogHandlers') instead
		 */
		bindDialogHandlers: function( ) {

			return this.each(function() {
				$('.dlg-commit-btn', $(this)).button().on('click', cb);
				$('.dlg-cancel-btn', $(this)).button().on('click', LITTLED.cancel);

				$('.datepicker', $(this)).datepicker();
			});
		}
	};
	
	$.fn.galleries = function( method ) {
	
		/* method calling logic */
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		}
		else if ( typeof method === 'object' || !method ) {
			return methods.init.apply(this, arguments);
		}
		else {
			$.error('Method ' + method + ' does not exist on jQuery.galleries.');
		}
		return false;
	};
	
}) ( jQuery );
