/**
 * Phire CMS 2.0 Content Scripts
 */

var contentForm;
var categoryForm;
var contentParentId = 0;
var contentParentUri = '';
var categoryParentId = 0;
var categoryParentUri = '';

/**
 * Function to get content parent URI
 *
 * @return void
 */
var getParentUri = function() {
    if (_request.readyState == 4) {
        if (_request.status == 200) {
            var j = $().json.parse(_request.responseText);
            contentParentUri = j.uri;
        }
    }
};

/**
 * Function to get category parent URI
 *
 * @return void
 */
var getCatParentUri = function() {
    if (_request.readyState == 4) {
        if (_request.status == 200) {
            var j = $().json.parse(_request.responseText);
            categoryParentUri = j.uri;
        }
    }
};

/**
 * Function to get custom datetime format
 *
 * @return void
 */
var getDatetimeFormat = function() {
    if (_request.readyState == 4) {
        if (_request.status == 200) {
            var j = $().json.parse(_request.responseText);
            if ($('#custom-datetime').obj != null) {
                var v = (j.format != '') ? '(' + j.format + ')' : '';
                $('#custom-datetime').val(v);
            }
        }
    }
};

/**
 * Function to create a content slug and display it
 *
 * @param  String src
 * @param  String tar
 * @return void
 */
var slug = function(src, tar) {
    if ((src != null) && (tar != null)) {
        $('#' + tar).val($('#' + src).val().slug());
    }

    if ($('#uri-span').obj != null) {
        if ($('#parent_id').obj != null) {
            var parent = $('#parent_id').obj.value;
            if (parent != contentParentId) {
                contentParentId = parent;
                $xmlHttp().get('../json/' + parent, getParentUri);
            }
        }
        var val = $('#' + tar).obj.value;
        val = contentParentUri + val;
        if ((val != '') && (val.substring(0, 1) != '/')) {
            val = '/' + val;
        } else if (val == '') {
            val = '/';
        }
        $('#uri-span').obj.innerHTML = (val.substring(0, 2) == '//') ? val.substring(1) : val;
    }
};

/**
 * Function to create a category slug and display it
 *
 * @param  String src
 * @param  String tar
 * @return void
 */
var catSlug = function(src, tar) {
    if ((src != null) && (tar != null)) {
        $('#' + tar).val($('#' + src).val().slug());
    }

    if ($('#slug-span').obj != null) {
        if ($('#parent_id').obj != null) {
            var parent = $('#parent_id').obj.value;
            if (parent != categoryParentId) {
                categoryParentId = parent;
                var jsonLoc = (window.location.href.indexOf('edit') != -1) ? '../json/' : './json/';
                $xmlHttp().get(jsonLoc + parent, getCatParentUri);
            }
        }
        var val = $('#' + tar).obj.value;
        val = categoryParentUri + val;
        if ((val != '') && (val.substring(0, 1) != '/')) {
            val = '/' + val;
        } else if (val == '') {
            val = '/';
        }
        $('#slug-span').obj.innerHTML = (val.substring(0, 2) == '//') ? val.substring(1) : val;
    }
};

/**
 * Function to output custom datetime example
 *
 * @param  String val
 * @return void
 */
var customDatetime = function(val) {
    $xmlHttp().get('./config/json/' + val.replace(/\//g, '\\'), getDatetimeFormat);
};

/**
 * Document ready function to load the correct URI string into the URI span
 */
$(document).ready(function(){
    // For content form
    if ($('#content-form').obj != null) {
        contentForm = $form('#content-form');
        if ($('#uri').obj != null) {
            var val = '';
            if ($('#parent_id').obj != null) {
                var parent = $('#parent_id').obj.value;
                if (parent != contentParentId) {
                    contentParentId = parent;
                    $xmlHttp().get('../json/' + parent, getParentUri);
                    val = contentParentUri + $('#uri').obj.value;
                } else {
                    val = $('#uri').obj.value;
                }
            }
            if ($('#uri').obj.type != 'file') {
                if ((val != '') && (val.substring(0, 1) != '/')) {
                    val = '/' + val;
                } else if (val == '') {
                    val = '/';
                }
                $($('#uri').parent()).append('span', ['id', 'uri-span'], (val.substring(0, 2) == '//') ? val.substring(1) : val);
            }
        }
    }

    // For category form
    if ($('#category-form').obj != null) {
        categoryForm = $form('#category-form');
        if ($('#slug').obj != null) {
            var val = '';
            if ($('#parent_id').obj != null) {
                var parent = $('#parent_id').obj.value;
                if (parent != categoryParentId) {
                    categoryParentId = parent;
                    var jsonLoc = (window.location.href.indexOf('edit') != -1) ? '../json/' : './json/';
                    $xmlHttp().get(jsonLoc + parent, getCatParentUri);
                    val = categoryParentUri + $('#slug').obj.value;
                } else {
                    val = $('#slug').obj.value;
                }
            }
            if ((val != '') && (val.substring(0, 1) != '/')) {
                val = '/' + val;
            } else if (val == '') {
                val = '/';
            }
            $($('#slug').parent()).append('span', ['id', 'slug-span'], (val.substring(0, 2) == '//') ? val.substring(1) : val);
        }
    }

    if ($('#content-remove-form').obj != null) {
        $('#checkall').click(function(){
            if (this.checked) {
                $form('#content-remove-form').checkAll(this.value);
            } else {
                $form('#content-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#category-remove-form').obj != null) {
        $('#checkall').click(function(){
            if (this.checked) {
                $form('#category-remove-form').checkAll(this.value);
            } else {
                $form('#category-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#content-type-remove-form').obj != null) {
        $('#checkall').click(function(){
            if (this.checked) {
                $form('#content-type-remove-form').checkAll(this.value);
            } else {
                $form('#content-type-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#template-remove-form').obj != null) {
        $('#checkall').click(function(){
            if (this.checked) {
                $form('#template-remove-form').checkAll(this.value);
            } else {
                $form('#template-remove-form').uncheckAll(this.value);
            }
        });
    }
});
