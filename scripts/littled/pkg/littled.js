if (typeof LITTLED === "undefined") {

	LITTLED = {

		script_root: '/vendor/littled_cms/ajax/scripts/',
		edit_url: '',
		delete_url: '',
		view_url: '',
		cache_url: '',
		id_param: 'id',
		progress_html: '<div class="dialog-in-process"></div>',
		debug: false,

		/**
		 * Initialize any of the object properties through associative array passed as argument.
		 */
		init: function(a) {
			if (a !== undefined) {
				for (var i in a) {
					this[i] = a[i];
				}
			}
		},


		/**
		 * @deprecated Use $.littled('ajaxError') instead.
		 */
		ajaxError: function(xhr) {
			var $d = $('#globalDialog');
			if ($d.length > 0 && $d.is(':visible')) {
				$d.littled('ajaxError', xhr);
			}
		},


		/**
		 * @deprecated Use $.littled('displayStatus') instead.
		 */
		displayStatus: function(statusMsg) {
			if (statusMsg && $('#status-container').length) {
				$('#status-container').html(LITTLED.htmldecode(statusMsg).replace(/\n/g, '<br />\n'));
				$('#status-container:hidden').fadeIn('slow');
			}
		},


		doLookup: function() {

			var f = document.getElementById('dialog-edit-form');
			if (!f.url.value) {
				alert('url not set!');
				return (false);
			}

			LITTLED.displayProcessWheel();

			$('#dialog-edit-form').ajaxSubmit({
				url: f.url.value,
				type: 'post',
				dataType: 'json',
				success: LITTLED.onLoadFormSuccess,
				error: LITTLED.ajaxError
			});
			return(false);
		},

		/**
		 * @deprecated Use $.littled.updateCache() in its place.
		 * @returns {mixed}
		 */
		updateCache: function() {
			$.littled.updateCache();
		},

		getParentObject: function(pid) {
			var $p;
			if (pid === undefined) {
				$p = $('body');
			} 
			else if (typeof (pid) == 'object') {
				$p = pid;
			} 
			else {
				$p = $(pid);
				if ($p.length < 1) {
					$p = $('body');
				}
			}
			return ($p);
		},

		oc: function(a) {
			var o = {};
			for (var i = 0; i < a.length; i++) {
				o[a[i]] = '';
			}
			return (o);
		},


		ptq: function(q, arExclude) {
			/* parse the query */
			var x = q.replace(/;/g, '&').split('&'), i, name, t;
			/* q changes from string version of query to object */
			for (q = {}, i = 0; i < x.length; i++) {
				t = x[i].split('=', 2);
				name = unescape(t[0]);
				if (arExclude !== undefined && arExclude.length >= 0 && (name in LITTLED.oc(arExclude))) {
					name = '';
				}
				if (name !== '') {
					if (!q[name]) {
						if (t.length > 1) {
							q[name] = unescape(t[1]);
						}
					}
				}
			}
			return q;
		},

		/**
		 * @deprecated Use $.littled.getQueryArray() instead.
		 */
		getQueryArray: function(arAppend, arExclude) {
			var e = ['id', 'msg'];
			if (arExclude !== undefined && arExclude.length >= 0) {
				e = e.concat(arExclude);
			}
			var q = LITTLED.ptq(document.location.search.substring(1).replace(/\+/g, ' '), e);
			for (var i in q) {
				if (q[i] === '') {
					delete q[i];
				}
			}
			if (arAppend !== undefined && typeof (arAppend) == 'object') {
				for (i in arAppend) {
					q[i] = arAppend[i];
				}
			}
			return q;
		},


		getParameterByName: function(key) {
			key = key.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
			var regexS = "[\\?&]" + key + "=([^&#]*)";
			var regex = new RegExp(regexS);
			var results = regex.exec(window.location.href);
			if (results == null) {
				return ("");
			} else {
				return (decodeURIComponent(results[1].replace(/\+/g, " ")));
			}
		},


		isObjectEmpty: function(obj) {
			for (var prop in obj) {
				if (Object.prototype.hasOwnProperty.call(obj, prop)) {
					return (false);
				}
			}
			return (true);
		},
		
		/**
		 * @deprecated Use $.littled.getDomain() instead.
		 */
        getDomain: function() {
            return ((window.document.domain === undefined) ? (window.document.hostname) : (window.document.domain));
        },

        /**
		 * @deprecated Use $.littled('bindImageRollovers') instead.
		 * Image rollovers assigned to any image with "ro" class. 
		 * Rollover image naming convention: filename.ext > filename-over.ext
		 */
        bindImageRollovers: function() {
            $('img.ro').hover(
                function() {
                    $(this).attr('src', $(this).attr('src').replace(/\.([^.]+)$/, '-over.$1'));
                },
                function() {
                    $(this).attr('src', $(this).attr('src').replace(/-over\.([^.]+)$/, '.$1'));
                }
            );
        },

		/**
		 * @deprecated Use the postContentUpdateCB property of $.formDialog() instead.
		 */
        edit_cb: function(data) {
            LITTLED.onEditSuccess(data);
        },

        /**
         * @deprecated Use $.formDialog('onOperationSuccess') instead.
         * @param {object} data
         * @returns {*}
         */
        onEditSuccess: function(data) {

            LITTLED.clearDialogMsg();

            if (data.error) {
                return(LITTLED.displayError(data.error.replace('/\n/mg', '<br />\n'), 'dialog-error-container'));
            }

            if (data.container_id) {
                var $e = $('#' + data.container_id);
                if ($e.length < 1) {
                    $e = $('#listings-container');
                }
            } else {
                $e = $('#listings-container');
            }

            if ($e.length) {
                $e.html(LITTLED.htmldecode(data.content));
                LITTLED.setSortables();
            }
            LITTLED.displayStatus(data.status);
            LITTLED.dismissDialog();
			return (false);
        },

		/**
		 * @deprecated Use jQuery $.trigger('contentUpdate') instead and target the element to be updated.
		 */ 
        setSortables: function() {
            /* hook for pages to define routine for when a sortable list's contents are refreshed */
        },

		/**
		 * @deprecated Use the 'postContentUpdateCB' proprety of $.formDialog() instead.
         * @param {object} data
         * @returns {*}
         */
        onContentUpdate: function(data) {
            if (data.error) {
                return(LITTLED.displayError(LITTLED.htmldecode(data.error)));
            }
            $('#' + data.container_id).html(LITTLED.htmldecode(data.content));
			return (false);
        },


		/**
		 * @deprecated Use $.littled('displayAjaxResult') instead.
         * @param {object} data
         * @returns {boolean}
         */
        displayAjaxResult: function(data) {
            if (data.error) {
				var $e = null;
                if (data.container_id !== undefined && data.container_id != '') {
                    $e = $('#' + data.container_id);
                    $e.html('<div class="error">' + data.error.replace(/\n/mg, '<br />') + '</div>');
                } else if ($('#error-container').length) {
                    $e = $('#error-container');
                    $e.html(data.error.replace(/\n/mg, '<br />'));
                } else {
                    alert(data.error);
                    return (false);
                }
                $e.show();
                return (false);
            }

            $('#' + data.container_id).html(LITTLED.htmldecode(data.content));
            $('#' + data.container_id).show();
			return(false);
        },

        /**
		 * @deprecated Use $.formDialog('getContentOperations') instead.
		 */ 
        onLoadFormSuccess: function(data) {

            LITTLED.clearDialogMsg();

            if (data.error) {
                return(LITTLED.displayError(data.error));
            }

            /* load dialog with form as content */
            $('#global-modal-content').html(LITTLED.htmldecode(data.content));
            $('#globalDialog').dialog({
                title: data.label,
                minWidth: 360,
                width: 'auto',
                height: 'auto',
                modal: true
            });
			$('#globalDialog .datepicker').datepicker();
            $('#globalDialog .dlg-commit-btn').button().on('click', LITTLED.commitOp);
            $('#globalDialog .dlg-cancel-btn').button().on('click', LITTLED.cancel);
			return (false);
        },


        /**
		 * @deprecated Use $.formDialog('commitOperation') instead.
		 */
        commitOp: function() {

            var tid = $(this).data('tid');
            var op = $(this).data('op');

            if (!tid) { 
				return(LITTLED.displayError('Content type not specified.'));
			}
            if (!op) { 
				return(LITTLED.displayError('Operation not specified.'));
			}

            LITTLED.execSectionOp(tid, function(data1) {

                if (data1.error) {
                    return(LITTLED.displayError(data1.error, 'dialog-error-container'));
                }

                var uri = '';
                switch (op) {
                    case 'edit':
                        uri = data1.edit_uri;
                        break;
                    case 'delete':
                        uri = data1.delete_uri;
                        break;
                    default:
                        LITTLED.displayError('Unhandled operation.');
                        return (false);
                }

                if (uri === '') {
                    alert('Handler not set.');
                    return (false);
                }
				uri = $.littled.addProtocolAndDomain(uri);

                /* LITTLED.displayProcessWheel(); */

                $('#dialog-edit-form').ajaxSubmit({
                    url: uri,
                    type: 'post',
                    dataType: 'json',
                    success: LITTLED.edit_cb,
                    error: LITTLED.ajaxError
                });
				return (false);
            });
			return(false);
        },


        /**
		 * @deprecated Use $.formDialog() instead.
		 * 
		 * usage:	
		 * - dialogEdit() without any arguments will pick up record id, 
		 *		content type id, and operation values from element attributes, 
		 *		and it will use the default callback of LITTLED.onLoadFormSuccess()
		 * - dialogEdit(callback) record id, content type id, and operation values
		 *		will be picked up from element attributes. 'callback' argument
		 *		value will be used in place of the default callback that handles 
		 *		loading the form content
		 * - dialogEdit(id, tid, op) pass in explicit record id, content type,
		 *		and operation values. Used the default callback to load form content.
		 */	 
		dialogEdit: function(a1, a2, op) {

			var cb = LITTLED.onLoadFormSuccess;
			var id, tid, $e;
			if (typeof(a1) === 'function') {
				cb = a1;
				$e = a2;
				tid = $e.data('tid');
			} else if (typeof (a1) === "object" || typeof (a1) === "undefined") {
				$e = $(this);
				id = $e.data('id');
				tid = ((a2===undefined)?($e.data('tid')):(a2));
			}
			if (op === undefined) {op = $e.data('op');}

			if (tid===undefined) { 
				return(LITTLED.displayError('Content type not set.')); 
			}
			if (op===undefined) { 
				return(LITTLED.displayError('Operation not set.')); 
			}

            LITTLED.execSectionOp(tid, function(data1) {

                if (data1.error) {
                    return(LITTLED.displayError(data1.error));
                }
                var url = LITTLED.getOperationURI(op, data1);
				if (!url) {
					return (LITTLED.displayError('Unsupported operation.'));
				}

                LITTLED.initDialog();

                var arr = {tid: tid};
                arr[data1.id_param] = id;
                arr = LITTLED.getQueryArray(arr);

                $.ajax({
                    method: 'get',
                    url: url,
                    data: arr,
                    dataType: 'json',
                    success: cb,
                    error: LITTLED.ajaxError
                });
				return (false);
            });
			return (false);
        },

		/**
		 * @deprecated Use $.formDialog('dismiss') instead.
		 * @param {int} id
		 */
        cancel: function(id) {
            if (typeof LITTLED.Video !== "undefined") {
                LITTLED.Video.unhideFLV(id);
            }
            LITTLED.dismissDialog();
        },

		/**
		 * @deprecated Use $.formDialog('display') instead.
		 */
        initDialog: function() {

            if (typeof LITTLED.Video !== "undefined") {
                LITTLED.Video.hideFLV();
            }

            $('#dialog-bg').height($(document).height());
            $('#dialog-bg').width($(document).width());
            var y = $(document).scrollTop();
            $('#dialog-bg').offset({top: y, left: 0});
            $('#dialog-bg').show();

            $('#dialog-error-container').hide();
            $('#dialog-status-container').hide();
            $('#dialog-form-container').html(LITTLED.progress_html);
            $('#dialog-container').show();
            LITTLED.center($('#dialog-container'));
        },

		/**
		 * @deprecated Use $.formDialog('close') instead.
		 */
        dismissDialog: function() {

            if (typeof LITTLED.Video !== "undefined") {
                LITTLED.Video.unhideFLV();
            }

            $('#globalDialog').dialog('close');
        },

		/**
		 * @deprecated Use $.formDialog('clearMessages') instead.
		 */
        clearDialogMsg: function() {
            $('#dialog-status-container').hide();
            $('#dialog-error-container').hide();
        },

        /**
		 * @deprecated Use $.littled('retrieveContentOperations') instead.
		 * 
        * First makes an AJAX call to get section operations based on the value of tid. 
        * Then executes callback function cb.
        * @param {int} tid section id used to retrieve section settings.
        * @param {function} cb callback used to execute as "success" handler after the section's properties have been successfully retrieved.
        */
        execSectionOp: function(tid, cb) {

            /* ajax call to get script properties */
            $.ajax({
                type: 'get',
                url: LITTLED.script_root + 'utils/script_properties.php',
                dataType: 'json',
                data: {tid: tid},
                success: cb,
                error: LITTLED.ajaxError
            });
        },

		/**
		 * @deprecated Use $.formDialog('getOperationURI') instead.
		 * @param {string} op
		 * @param {object} data
		 */
		getOperationURI: function(op, data) {
			switch (op) {
				case 'edit':
					return(data.edit_uri);
				case 'delete':
					return(data.delete_uri);
				case 'view':
					return(data.details_uri);
				default:
					return('');
			}
		},

		/**
		 * @deprecated Use $.littled('displayProcessWheel') instead.
		 * @param {string} _form_id
		 */
		displayProcessWheel: function(_form_id) {
            if (_form_id === undefined) {_form_id = 'dialog';}
            $('#' + _form_id + '-error-container').html('');
            $('#' + _form_id + '-error-container').hide();
            $('#' + _form_id + '-status-container').html(LITTLED.progress_html);
            $('#' + _form_id + '-status-container').show();
        },

		/**
		 * @deprecated Use jQuery instead.
		 * @param {domElement} $e
		 */
        center: function($e) {
            var yOffset = $(document).scrollTop() + ($(window).height() / 2) - ($e.height() / 2);
            var xOffset = ($(window).width() / 2) - ($e.width() / 2);
            $e.offset({top: yOffset, left: xOffset});
        },

		/**
		 * @deprecated Use $.littled('formatQueryString') instead.
		 */
        formatQueryString: function() {
            var q = document.location.search.toString();
            q = q.replace(/^[\?]/, '&');
            q = q.replace(new RegExp('&(id|msg|' + LITTLED.id_param + ')=.*?&'), '&');
            q = q.replace(new RegExp('&(id|msg|' + LITTLED.id_param + ')=.*$'), '');
            q = q.replace(/^[\?&]/, '');
            return (q);
        },

		/**
		 * @deprecated Use $.littled.htmldecode() instead.
		 * @param {string} html
		 */
		htmldecode: function(html) {
            if (html === undefined) {
                return ("");
            }
            return (html.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"'));
        },

		/**
		 * @deprecated Use $.littled('displayError') instead.
		 * @param {string} err
		 * @param {string} eid
		 */
        displayError: function(err, eid) {

            var $e = ((eid !== undefined && eid) ? ($('#' + eid)) : ($('#error-container')));
            if ($e.length) {
				$e.littled('displayError', err);
            }
			return (false);
        },

		/**
		 * @deprecated Use $.littled('dismissError') instead.
		 * @param {string} eid
		 */
        dismissError: function(eid) {
            var $e = ((eid !== undefined && eid) ? ($('#' + eid)) : ($('#error-container')));
            if ($e.length && $e.is(':visible')) {
                $e.slideToggle('slow');
            }
        },

		/**
		 * @deprecated Use $.littled('displayError') instead.
		 * @param {string} err
		 * @param {domElement} p
		 */
		dialogError: function(err, p) {
			var $e = $('#' + ((p !== undefined && p) ? (p) : ('dialog')) + '-status-container');
			if ($e.length < 1) {$e = $('#status-container');}
			if ($e.attr('id') === 'dialog-status-container' && $('#dialog-container').is(':hidden')) {
				$e = $('#status-container');
			}
			if ($e.length) {$e.hide();}

			$e = $('#' + ((p !== undefined && p) ? (p) : ('dialog')) + '-error-container');
			if ($e.length < 1) {$e = $('#error-container');}
			if ($e.attr('id') === 'dialog-error-container' && $('#dialog-container').is(':hidden')) {
				$e = $('#error-container');
			}

			if ($e.length) {
				$e.html(err.replace(/\n/mg, '<br />'));
				if (!p) {
					$e.html($e.html() + ' <input class="smtext" type="button" value="close" onclick="LITTLED.cancel()" />');
				}
				$e.show();
				var z = parseInt($e.parent().css('z-index'));
				if (z > 0) {
					LITTLED.center($e.parent());
				}
			}
		},

        /*
		 * @deprecated Use $.textboxMessage() instead.
         * Set up textboxes to contain label inside textbox, with the text being cleared on focus 
         * and restored on blur if nothing has been entered in its place.
         */
        bindInlineTB: function() {
            $('input.tbi').each(function() {
                if ($(this).val() === '') {
                    $(this).val($(this).data('init'));
                }
            });
			$('input.tbi')
			.on('focus', function() {
                if ($(this).val() === $(this).data('init')) {
                    $(this).val('');
                }
            })
            .on('blur', function() {
                if ($(this).val() === '') {
                    $(this).val($(this).data('init'));
                }
            });
        },

        /*
		 * @deprecated Use $.textboxMessage() instead.
		 * @param {domElement} $e
		 * @param {string} label
		 */
        setInputLabel: function($e, label) {

            if ($e.length < 1) {return;}

            $e
            .focus(function() {
                if ($(this).val() === label) {
                    $(this).val('');
                    $(this).removeClass('dimtext');
                }
            })
            .blur(function() {
                if ($(this).val() === '') {
                    $(this).val(label);
                    $(this).addClass('dimtext');
                }
            });
            if (!$e.val()) {
                $e.val(label);
                $e.addClass('dimtext');
            }
        },

		/**
		 * @deprecated Use LITTLED.ajaxError() instead.
		 * @param {json} data
		 */
        onHTTPFailure: function(data) {
            /*** DEPRECATED ***/
            LITTLED.ajaxError(data);
        }
    };
}

/*
 * jQuery added functionality
 */
(function($) {

	var settings = {
		errorContainer: '.alert-error',
		statusContainer: '.alert-info',
		csrfSelector: '#csrf-token',
		progress_markup: '<div class="dialog-in-process"></div>',
		ajax: {
			content_operations_uri: '_ajax/utils/script_properties.php'
		},
		dom: {
			page_error_container: '.alert-error:first'
		}
	};

    /**
	 * $.serializeObject()
	 * Returns associative array of all values in the form. (As opposed to built-in $.serializeArray() which returns an object.)
	 * usage:   'src' Optional associative array containing properties that will be added to those found in the form.
	 *			'exclude' one-dimensional array of of form elements to skip over and ignore
	 */
	$.fn.serializeObject = function(src, exclude) {

		var fd = {};

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
		var intID = jQuery.data(this.get(0));
		return($.cache[intID]);	
	};

	$.littled = {

		getDomain: function() {
			return ((window.document.domain === undefined) ? (window.document.hostname) : (window.document.domain));
		},

		getRelativePath: function() {
			var path = '';
			var url = window.location.href;
			url = url.replace(/^.*:\/\/.*?\/(.*\/).*$/, '$1');
			if (url) {
				var depth = url.split('/').length - 1;
				for (var i=0; i < depth; i++) {
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
			var e = ['id', 'msg'];
			if (arExclude !== undefined && arExclude.length >= 0) {
				e = e.concat(arExclude);
			}
			var q = LITTLED.ptq(document.location.search.substring(1).replace(/\+/g, ' '), e);
			for (var i in q) {
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
			var lclSettings = options || {};
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
        * @param {int} tid Section id used to retrieve section settings.
        * @param {function} cb Callback used to execute as "success" handler 
		* after the section's properties have been successfully retrieved.
		* @param {object} options (Optional) collection of settings that will
		* override the library's default settings.
        */
        retrieveContentOperations: function(tid, cb, options) {

			let lclSettings = $.extend(true, {}, settings, options || {});
			let pd = {};
			pd['tid'] = tid;
			pd['csrf'] = $(lclSettings.csrfSelector).html();

			/* ajax call to get script properties */
            $.ajax({
                type: 'post',
                url: $.littled.getRelativePath()+lclSettings.ajax.content_operations_uri,
                dataType: 'json',
                data: pd,
                success: cb
            })
			.fail(function(xhr) {
				$(lclSettings.dom.page_error_container).littled('ajaxError', xhr);
			});
        },

		/**
		 * Calls ajax to retrieve URL of caching script, then makes ajax
		 * call to that scirpt, thereby updating the content cache. 
		 * - TODO: Add property to library 'settings' property that will 
		 * store the caching script url. Then apply that setting when binding 
		 * handlers after a page is loaded. (i.e. remove $.retrieveContentOperations()
		 * call & thereby reduce ajax calls.)
		 * @param {eventObject} evt
		 * @returns {undefined}
		 */
		updateCache: function( evt ) {

			evt.preventDefault();
			var lclSettings = $.extend(true, {}, settings, evt.data || {});

			$(lclSettings.errorContainer+':visible').fadeOut('fast');
			$(lclSettings.statusContainer+':visible').fadeOut('fast');

			var tid = $(this).data('tid');
			var id = $(this).data('id');
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

	var methods = {

		displayAjaxResult: function(data) {

			return this.each(function() {
				if (data.error) {
					var $err = $('.error', $(this));
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
				var $e = $(this);
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
			var lclExtras = $.extend({}, {csrf: $(lclSettings.csrfSelector).html()}, extras || {});
			return($.extend(lclExtras, $('input, select, textarea', this).serializeObject()));
		},

		keyNavigation: function(options) {
			return this.on('keydown', function(e) {

				var keyCode = e.keyCode || e.which;
				var arrow = {left: 37, up: 38, right: 39, down: 40};
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
				var $f = $(this).closest('form');
				$('.error', $f).html('').hide('fast');
				$('.status', $f).html(settings.progress_markup).show();
			});
		},

		formatQueryString: function() {
			var q = document.location.search.toString();
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
				var $p = null;
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
				var $e = $p.first(selector);
				if ($e.length<1) {
					
					/* create target element if it doesn't exist already */
					var $new = '<div></div>';
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
						for(var class_name in class_array) {
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

/*
 * jQuery added functionality
 */
(function($) {

	var settings = {
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

	var methods = {

		/**
		 * Binds event handler to element that will open a dialog on that event.
		 * By default the event is a 'click' event, but it can be overridden with
		 * options.event. 
		 * @param {Object} options
		 * @returns {_L4.methods@call;on}
		 */
		init: function(options) {
			var lclSettings = $.extend(true, {}, settings, options || {});
			return this
				.off(lclSettings.event, methods.open)
				.on(lclSettings.event, options, methods.open);
		},
		
		/**
		 * Disables the dialog event handler.
		 * @param {Object} options
		 * @returns {_L4.methods@call;off}
		 */
		cancel: function(options) {
			var lclSettings = $.extend(true, {}, settings, options || {});
			/* un-bind event handler */
			return this.off(lclSettings.event, methods.open);
		},

		open: function(evt) {

			evt.preventDefault();
			var options = evt.data || {};
			var lclSettings = $.extend(true, {}, settings, options);

			/* hide existing error messages */
			methods.dismissErrorMessages(options);
			
			/* retrieve operation properties */
			var nav = {};
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
			var $e = $(this);

			$.littled.retrieveContentOperations(nav[lclSettings.keys.content_type_id], function(data1) {

                if (data1.error) {
					$(lclSettings.dom.page_error_container).littled('displayError', data1.error);
					return;
                }
                var dialog_url = methods.getOperationURI(nav[lclSettings.keys.operation], data1);
				if (!dialog_url) {
					$(lclSettings.dom.page_error_container).littled('displayError', 'Operation handler not specified.');
					return;
				}

                nav[data1.id_param] = nav[lclSettings.keys.record_id];
                $.extend(nav, lclSettings.scriptData || {});
				var fd = $(lclSettings.dom.listings_filters).formDialog('retrieveFormFilters', options);
				if (_.size(fd) > 0) {
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

			var lclSettings = $.extend(true, {}, settings, data.settings || {});
			if (data.error) {
				$(lclSettings.dom.page_error_container).littled('displayError', data.error);
				return (false);
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
			var lclSettings = $.extend(true, {}, settings, options || {});
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
		
			var value = $(this).data(key);
			if (!value) {
				if (typeof $f !== 'object') {
					$f = $(this).closest('form');
				}
				if ($f.length) {
					var $i = $('input[name="'+key+'"]', $f);
					if ($i.length) {
						value = $i.val();
					}
				}
			}
			return (value);
		},

		commitOperation: function(evt) {
			
			evt.preventDefault();
			var options = evt.data || {};
			var lclSettings = $.extend(true, {}, settings, options);
			
			methods.dismissErrorMessages(options);

			var $f = $(this).closest('form');
			var tid = $(this).formDialog('collectValue', lclSettings.keys.content_type_id, $f);
			var op = $(this).formDialog('collectValue', lclSettings.keys.operation, $f);

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

				var url = methods.getOperationURI(op, data1);
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

			var options = data.settings || {};
			var lclSettings = $.extend(true, {}, settings, options);

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
			var lclSettings = $.extend(true, {}, settings, options || {});
			/* removing this element is removed while the dialog
			 * is open causes the page to shift up. too distracting. */
			// $(lclSettings.dom.status_container+':visible').hide('fast');
			$(lclSettings.dom.error_container+':visible').hide('fast');
			$(lclSettings.dom.page_error_container+':visible').hide('fast');
			$(lclSettings.dom.dialog_error_container+':visible').hide('fast');
		},

		clearPageContent: function( data ) {
			var lclSettings = $.extend(true, {}, settings, data.settings || {});
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
			var lclSettings = $.extend(true, {}, settings, options || {});
			var filters = {};
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
        retrieveContentOperations: function(tid, cb, options) {
			$.littled.retrieveContentOperations(tid, cb, options);
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
			var err = '[' + xhr.status + ' ' + xhr.statusText + '] ' + xhr.responseText;
			methods.displayError(err);
		},
		
		displayError: function( error, options ) {
			var lclSettings = $.extend(true, {}, settings, options || {});			
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
(function ($) {
	
	/**
	 * static properties 
	 */
	var settings = {
		uris: {
			keyword_autocomplete: '_ajax/utils/keyword_autocomplete.php',
			record_details: 'details.php'
		},
		keys: {
			record_id: 'id',
			parent_id: 'pid',
			content_type: 'tid',
			page: 'p',
			operation: 'op',
			csrf: 'csrf',
		},
		dom: {
			/* start using these over the top-level settings */
			listings_container: '.listings',
			page_error_container: '.alert-error:first',
			error_container: '.alert-error',
			filters_form: '.listings-filters:first form',
			csrf_selelctor: '#csrf-token'
		},
		selectCallback: null
	};

	var methods = {

		/**
		 * sets up autocomplete for the keyword filter textbox 
		 * @param {object} options (Optional) collection of settings that will
		 * override the library's default settings.
		 */
		bindAutocomplete: function(options) {

			var lclSettings = $.extend(true, {}, settings, options || {});

			if (typeof(lclSettings.selectCallback) !== 'function') {
				lclSettings.selectCallback = methods.selectCallback;
			}

			return this.each(function() {

				var name = $(this).prop('name');
				var fd = {};
				fd[lclSettings.keys.content_type] = $(this).data(lclSettings.keys.content_type);
				fd[lclSettings.keys.csrf] = $(lclSettings.dom.csrf_selector).html();

				$(this).autocomplete({
					minLength: 3,
					source: function(request, response) {
						fd[name] = request.term;
						$.ajax({
							type: 'post',
							url: $.littled.getRelativePath() + lclSettings.uris.keyword_autocomplete,
							data: fd,
							dataType: 'json'
						})
						.success(function(data) {
							if (data.error) {
								$(lclSettings.dom.page_error_container).littled('displayError', data.error);
								return;
							}
							response($.map(data, function(item) {
								return ({
									item: item.id,
									label: item.title,
									value: item.id,
									term: request.term
								});
							}));
						})
						.fail(function(xhr) {
							$(lclSettings.dom.page_error_container).littled('ajaxError', xhr);
						});
					},
					select: lclSettings.selectCallback
				});
			});
		},
		
		selectCallback: function(event, ui) {

			var lclSettings = $.extend(true, {}, settings, event.data || {});

			/* update the textbox value with the name of the matching item, not its numeric id */ 
			event.preventDefault();
			$(this).val(ui.item.term+'*');

			/* redirect to details page for the selected item, preserving the current listings filters */
			var id = ui.item.value;
			var fa = $(lclSettings.dom.filters_form).serializeArray();
			var qa = $.map(fa, function(item) { 
				return(item.name+'='+encodeURIComponent(item.value));
			});
			var url = lclSettings.uris.record_details+'?'+qa.join('&')+'&id='+id;
			window.location = url;
		}
	};
	
	$.fn.keywordFilter= function( method ) {
	
		/* method calling logic */
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		}
		else if ( typeof method === 'object' || !method ) {
			return methods.init.apply(this, arguments);
		}
		else {
			$.error('Method ' + method + ' does not exist on jQuery.keywordFilter.');
		}
		return (false);
	};
	
}) ( jQuery );


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
