LITTLED.Image = {

    dest_dir: '',
    edit_url: '/_inline/images/edit_image.php',
    upload_url: '/_inline/images/upload_image.php',
    delete_url: '/_inline/images/del_image.php',
    thumbnail_url: '/_inline/images/set_thumbnail.php',
    listings_url: '/_inline/images/listings.php',
    preview_url: '/_inline/images/preview.php',
    prefix: '',
    preview_tooltip_properties: {
		track: true, 
		delay: 0, 
		showURL: false, 
		opacity: 1, 
		fixPNG: true, 
		bodyHandler: function() {
		    if ($('.tooltip-content', this).length<1) { return ('Preview unavailable!'); }
            return ($('.tooltip-content', this).html());
		},
		extraClass: "preview", 
		top: -98, 
		left: 5
    },

    init: function(a) {
        if (a !== undefined) {
            for (var i in a) {
                this[i] = a[i];
            }
        }
        LITTLED.windowLoaded = true;
    },

    dialogEdit: function(id, pid, tid, filters) {

        if (!LITTLED.windowLoaded) {
            alert('Please allow the document to finish loading and try again.');
            return (false);
        }

        LITTLED.initDialog();
        LITTLED.edit_url = LITTLED.Image.edit_url;

		/* 
		 * function arguments goes 2nd because we want those values to supercede filters
		 * empty object as first argument to keep filters from being changed 
		 */
		var arr = $.extend({}, filters, {ilid: id, ilpi: pid, ilti: tid}); 

        $.ajax({
            type: 'post',
            url: LITTLED.Image.edit_url,
            data: arr,
            dataType: 'json',
            success: LITTLED.onLoadFormSuccess,
            error: LITTLED.onHTTPFailure
        });
    },

    uploadDialog: function(id, pid, tid) {

        if (!LITTLED.windowLoaded) {
            alert('Please allow the document to finish loading and try again.');
            return (false);
        }

        LITTLED.initDialog();
        LITTLED.edit_url = LITTLED.Image.upload_url;
        $.ajax({
            type: 'get',
            url: LITTLED.Image.upload_url,
            data: {
                ilid: id,
                ilpi: pid,
                ilti: tid
            },
            dataType: 'json',
            success: LITTLED.onLoadFormSuccess,
            error: LITTLED.onHTTPFailure
        });
    },

    upload: function() {

        LITTLED.displayProcessWheel();
        $('#dialog-edit-form').ajaxSubmit({
            url: LITTLED.Image.upload_url,
            type: 'post',
            dataType: 'json',
            success: LITTLED.onEditSuccess,
            error: LITTLED.onHTTPFailure
        });
    },

    selectThumbnail: function(id, pid, sid) {

        LITTLED.initDialog();
        $.ajax({
            type: 'get',
            url: LITTLED.Image.thumbnail_url,
            data: {
                id: id,
                pid: pid,
                sid: sid
            },
            dataType: 'json',
            success: LITTLED.onLoadFormSuccess,
            error: LITTLED.onHTTPFailure
        });
    },

    setThumbnail: function() {

        $('#dialog-edit-form').ajaxSubmit({
            url: LITTLED.Image.thumbnail_url,
            type: 'post',
            dataType: 'json',
            success: LITTLED.onEditSuccess,
            error: LITTLED.onHTTPFailure
        });
    },

    remove: function(id, ut, filters) {

        LITTLED.delete_url = LITTLED.Image.delete_url;

        LITTLED.initDialog();
        $.ajax({
            type: 'get',
            url: LITTLED.Image.delete_url,
            data: $.extend({}, filters, { ilid: id, ut: ut }),
            dataType: 'json',
            success: LITTLED.onLoadFormSuccess,
            error: LITTLED.onHTTPFailure
        });
    },

    gotoPage: function(n, tid, pid, filters) {

        $('.img-process-wheel').show();

		/* 
		 * function args go 2nd because we want those values to supercede filters
		 * empty object as first argument to keep filters from being changed 
		 */
		var arr = $.extend({}, filters, {p: n, tid: tid, pid: pid}); 

		/* save page value for when listings are refreshed */
		filters['ip'] = n; 

		/* gets next page of listings */
        $.ajax({
            type: 'get',
            url: LITTLED.Image.listings_url,
            data: arr,
            dataType: 'json',
            success: LITTLED.Listings.onUpdateCellSuccess,
            error: LITTLED.onHTTPFailure
        });
    }
}