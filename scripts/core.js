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
				for (let i in a) {
					this[i] = a[i];
				}
			}
		},


		/**
		 * @deprecated Use $.littled('ajaxError') instead.
		 */
		ajaxError: function(xhr) {
			let $d = $('#globalDialog');
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

			let f = document.getElementById('dialog-edit-form');
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
			let $p;
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
			let o = {};
			for (let i = 0; i < a.length; i++) {
				o[a[i]] = '';
			}
			return (o);
		},


		ptq: function(q, arExclude) {
			/* parse the query */
			let x = q.replace(/;/g, '&').split('&'), i, name, t;
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
			if (arAppend !== undefined && typeof (arAppend) == 'object') {
				for (i in arAppend) {
					q[i] = arAppend[i];
				}
			}
			return q;
		},


		getParameterByName: function(key) {
			key = key.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
			let regexS = "[\\?&]" + key + "=([^&#]*)";
			let regex = new RegExp(regexS);
			let results = regex.exec(window.location.href);
			if (results == null) {
				return ("");
			} else {
				return (decodeURIComponent(results[1].replace(/\+/g, " ")));
			}
		},


		isObjectEmpty: function(obj) {
			for (let prop in obj) {
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
                let $e = $('#' + data.container_id);
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
				let $e = null;
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

            let tid = $(this).data('tid');
            let op = $(this).data('op');

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

                let uri = '';
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

			let cb = LITTLED.onLoadFormSuccess;
			let id, tid, $e;
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
                let url = LITTLED.getOperationURI(op, data1);
				if (!url) {
					return (LITTLED.displayError('Unsupported operation.'));
				}

                LITTLED.initDialog();

                let arr = {tid: tid};
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
            let y = $(document).scrollTop();
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
            let yOffset = $(document).scrollTop() + ($(window).height() / 2) - ($e.height() / 2);
            let xOffset = ($(window).width() / 2) - ($e.width() / 2);
            $e.offset({top: yOffset, left: xOffset});
        },

		/**
		 * @deprecated Use $.littled('formatQueryString') instead.
		 */
        formatQueryString: function() {
            let q = document.location.search.toString();
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

            let $e = ((eid !== undefined && eid) ? ($('#' + eid)) : ($('#error-container')));
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
            let $e = ((eid !== undefined && eid) ? ($('#' + eid)) : ($('#error-container')));
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
			let $e = $('#' + ((p !== undefined && p) ? (p) : ('dialog')) + '-status-container');
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
				let z = parseInt($e.parent().css('z-index'));
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

	let settings = {
		errorContainer: '.alert-error',
		statusContainer: '.alert-info',
		csrfSelector: '#csrf-token',
		progress_markup: '<div class="dialog-in-process"></div>',
		ajax: {
		    script_path: '/vendor/dbarchowsky/littled_cms/ajax/scripts/',
			content_operations_uri: 'utils/script_properties.php'
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
        * @param {int} tid Section id used to retrieve section settings.
        * @param {function} cb Callback used to execute as "success" handler 
		* after the section's properties have been successfully retrieved.
		* @param {object} opts (Optional) collection of settings that will
		* override the library's default settings.
        */
        retrieveContentOperations: function(tid, cb, opts) {

			let lclSettings = $.extend(true, {}, settings, opts || {});
			let pd = {};
			pd['tid'] = tid;
			pd['csrf'] = $(lclSettings.csrfSelector).html();

			/* ajax call to get script properties */
            $.ajax({
                type: 'post',
                url: $.littled.getRelativePath()+lclSettings.ajax.script_path+lclSettings.ajax.content_operations_uri,
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
