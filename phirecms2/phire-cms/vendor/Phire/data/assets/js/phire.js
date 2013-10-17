/**
 * Phire CMS 2.0 Scripts
 */

var resourceCount = 1;
var batchCount = 1;
var contentForm;
var categoryForm;
var contentParentId = 0;
var contentParentUri = '';
var categoryParentId = 0;
var categoryParentUri = '';
var curErrors = 0;
var submitted = false;
var clr;
var curForm;
var phireTimeout;

/**
 * Function to add resource/permission
 *
 * @return void
 */
var addResource = function() {
    resourceCount++;

    // Add resource select field
    $('#resource_new_1').clone({
        "name" : 'resource_new_' + resourceCount,
        "id"   : 'resource_new_' + resourceCount
    }).appendTo($('#resource_new_1').parent());

    // Add permission select field
    $('#permission_new_1').clone({
        "name" : 'permission_new_' + resourceCount,
        "id"   : 'permission_new_' + resourceCount
    }).appendTo($('#permission_new_1').parent());

    // Add allow select field
    $('#allow_new_1').clone({
        "name" : 'allow_new_' + resourceCount,
        "id"   : 'allow_new_' + resourceCount
    }).appendTo($('#allow_new_1').parent());

    $('#resource_new_' + resourceCount).val($('#resource_new_' + (resourceCount - 1) + ' > option:selected').val());
    $('#permission_new_' + resourceCount).val($('#permission_new_' + (resourceCount - 1) + ' > option:selected').val());
    $('#allow_new_' + resourceCount).val($('#allow_new_' + (resourceCount - 1) + ' > option:selected').val());
};

/**
 * Function to change permissions
 *
 * @param  mixed sel
 * @return void
 */
var changePermissions = function(sel) {
    var cur = (sel.id.indexOf('cur_') != -1) ? 'cur' : 'new';
    var id = sel.id.substring(sel.id.lastIndexOf('_') + 1);
    var marked = $('#' + sel.id + ' > option:selected').val();

    var opts = $('#permission_' + cur + '_' + id + ' > option').toArray();
    var start = opts.length - 1;

    for (var i = start; i >= 0; i--) {
        $(opts[i]).remove();
    }

    $('#permission_' + cur + '_' + id).append('option', {"value" : 0}, '(All)');

    if (marked != 0) {
        var jsonLoc = (window.location.href.indexOf('edit') != -1) ? '../json/' : './json/';
        var j = $().json.parse(jsonLoc + encodeURIComponent(marked.replace(/\\/g, '_')));
        for (var i = 0; i < j.actions.length; i++) {
            $('#permission_' + cur + '_' + id).append('option', {"value" : j.actions[i]}, j.actions[i]);
        }
    }
};

/**
 * Function to create a content slug and display it
 *
 * @param  string src
 * @param  string tar
 * @return void
 */
var slug = function(src, tar) {
    if ((src != null) && (tar != null)) {
        $('#' + tar).val($('#' + src).val().slug());
    }

    if ($('#uri-span')[0] != undefined) {
        if ($('#parent_id')[0] != undefined) {
            var parent = $('#parent_id').val();
            if (parent != contentParentId) {
                contentParentId = parent;
                var j = $().json.parse('../json/' + parent);
                contentParentUri = j.uri;
            }
        }
        var val = $('#' + tar).val();
        val = contentParentUri + val;
        if ((val != '') && (val.substring(0, 1) != '/')) {
            val = '/' + val;
        } else if (val == '') {
            val = '/';
        }
        $('#uri-span').val(((val.substring(0, 2) == '//') ? val.substring(1) : val));
    }
};

/**
 * Function to create a category slug and display it
 *
 * @param  string src
 * @param  string tar
 * @return void
 */
var catSlug = function(src, tar) {
    if ((src != null) && (tar != null)) {
        $('#' + tar).val($('#' + src).val().slug());
    }

    if ($('#slug-span')[0] != undefined) {
        if ($('#parent_id')[0] != undefined) {
            var parent = $('#parent_id').val();
            if (parent != categoryParentId) {
                categoryParentId = parent;
                var jsonLoc = (window.location.href.indexOf('edit') != -1) ? '../json/' : './json/';
                var j = $().json.parse(jsonLoc + parent);
                categoryParentUri = j.uri;
            }
        }
        var val = $('#' + tar).val();
        val = categoryParentUri + val;
        if ((val != '') && (val.substring(0, 1) != '/')) {
            val = '/' + val;
        } else if (val == '') {
            val = '/';
        }
        $('#slug-span').val(((val.substring(0, 2) == '//') ? val.substring(1) : val));
    }
};

/**
 * Function to output custom datetime example
 *
 * @param  string val
 * @return void
 */
var customDatetime = function(val) {
    var j = $().json.parse('./config/json/' + val.replace(/\//g, '\\'));
    if ($('#custom-datetime')[0] != undefined) {
        var v = (j.format != '') ? '(' + j.format + ')' : '';
        $('#custom-datetime').val(v);
    }
};

/**
 * Function to process form
 *
 * @param  object response
 * @return void
 */
var processForm = function(response) {
    var j = $().json.parse(response.text);
    if (j.updated != undefined) {
        if (j.redirect != undefined) {
            window.location.href = j.redirect;
        } else {
            if ($('#result')[0] != undefined) {
                $('#result').css({
                    "background-color" : '#dbf2bf',
                    "color"            : '#315900',
                    "opacity"          : 0
                });
                $('#result').val('Saved!');
                for (var i = 1; i <= curErrors; i++) {
                    if ($('#error-' + i)[0] != undefined) {
                        $('#error-' + i).remove();
                    }
                }
                if ($('#updated')[0] != undefined) {
                    $('#updated').val(j.updated);
                }
                if ((j.form != undefined) && ($('#' + j.form)[0] != undefined)) {
                    var f = $('#' + j.form)[0];
                    for (var i = 0; i < f.elements.length; i++) {
                        if ((f.elements[i].type == 'text') || (f.elements[i].type == 'textarea')) {
                            f.elements[i].defaultValue = f.elements[i].value;
                        }
                    }
                    if (typeof CKEDITOR !== 'undefined') {
                        for (ed in CKEDITOR.instances) {
                            CKEDITOR.instances[ed].setData(f.elements[ed].value);
                        }
                    } else if (typeof tinymce !== 'undefined') {
                        for (ed in tinymce.editors) {
                            if (ed.indexOf('field_') != -1) {
                                tinymce.editors[ed].setContent(f.elements[ed].value);
                            }
                        }
                    }
                }
                $('#result').fade(100, {tween : 10, speed: 200});
                clr = setTimeout(clearStatus, 3000);
            }
        }
    } else {
        if ($('#result')[0] != undefined) {
            $('#result').css({
                "background-color" : '#e8d0d0',
                "color"            : '#8e0202',
                "opacity"          : 0
            });
            $('#result').val('Please correct the errors below.');
            for (var i = 1; i <= curErrors; i++) {
                if ($('#error-' + i)[0] != undefined) {
                    $('#error-' + i).remove();
                }
            }
            $('#result').fade(100, {tween : 10, speed: 200});
            clr = setTimeout(clearStatus, 3000);
        }
        for (name in j) {
            // Check if the error already exists via a PHP POST
            var curErrorDivs = $('#' + name).parent().getElementsByTagName('div');
            var curErrorDivsHtml = [];
            for (var i = 0; i < curErrorDivs.length; i++) {
                curErrorDivsHtml.push(curErrorDivs[i].innerHTML);
            }
            // If error doesn't exists yet, append it
            if (curErrorDivsHtml.indexOf(j[name].toString()) == -1) {
                curErrors++;
                $($('#' + name).parent()).append('div', {"id" : 'error-' + curErrors, "class" : 'error'}, j[name]);
            }

        }
    }
};

/**
 * Function to update form
 *
 * @param  string  form
 * @param  boolean ret
 * @param  boolean prev
 * @return boolean
 */
var updateForm = function(form, ret, prev) {
    submitted = true;
    if (ret) {
        if (prev != null) {
            if ($('#status')[0] != undefined) {
                $('#status').val(1);
            }
            if ($('#update_value')[0] != undefined) {
                $('#update_value').val(2);
            }
        } else {
            if ($('#update_value')[0] != undefined) {
                $('#update_value').val(1);
            }
        }
        return true;
    } else {
        var f = $(form)[0];
        if (typeof CKEDITOR !== 'undefined') {
            for (ed in CKEDITOR.instances) {
                f.elements[ed].value = CKEDITOR.instances[ed].getData();
            }
        } else if (typeof tinymce !== 'undefined') {
            for (ed in tinymce.editors) {
                if (ed.indexOf('field_') != -1) {
                    f.elements[ed].value = tinymce.editors[ed].getContent();
                }
            }
        }
        var act = $(form).attrib('action');
        var url = act + ((act.indexOf('?') != -1) ? '&update=1' : '?update=1');
        $().ajax(url, {status : {200 : processForm}, method : 'post', data : f});
        return false;
    }
};

/**
 * Function to clear the status
 *
 * @return void
 */
var clearStatus = function() {
    $('#result').fade(0, {tween : 10, speed: 200});
    clearTimeout(clr);
};

/**
 * Function to clear the status
 *
 * @param  object a
 * @param  int    hgt
 * @return void
 */
var wipeErrors = function(a, hgt) {
    if ($('#errors').height() > 50) {
        $(a).val('Show');
        $('#errors').wipeUp(17, {tween : 10, speed: 200});
    } else {
        $(a).val('Hide');
        $('#errors').wipeUp(hgt, {tween : 10, speed: 200});
    }
};

/**
 * Function to add batch fields
 *
 * @return void
 */
var addBatchFields = function() {
    batchCount++;

    // Add file name field
    $('#file_name_1').clone({
        "name" : 'file_name_' + batchCount,
        "id"   : 'file_name_' + batchCount
    }).appendTo($('#file_name_1').parent());

    // Add file title field
    $('#file_title_1').clone({
        "name" : 'file_title_' + batchCount,
        "id"   : 'file_title_' + batchCount
    }).appendTo($('#file_title_1').parent());
};

/**
 * Function to check if form values have changed before leaving the page
 *
 * @return void
 */
var checkFormChange = function() {
    if (!submitted) {
        var change = false;
        var f = $(curForm)[0];
        for (var i = 0; i < f.elements.length; i++) {
            if ((f.elements[i].type == 'text') || (f.elements[i].type == 'textarea')) {
                if (f.elements[i].value != f.elements[i].defaultValue) {
                    change = true;
                }
            }
        }
        if (typeof CKEDITOR !== 'undefined') {
            for (ed in CKEDITOR.instances) {
                if (CKEDITOR.instances[ed].getData() != f.elements[ed].defaultValue) {
                    change = true;
                }
            }
        } else if (typeof tinymce !== 'undefined') {
            for (ed in tinymce.editors) {
                if (ed.indexOf('field_') != -1) {
                    if (tinymce.editors[ed].getContent() != f.elements[ed].defaultValue) {
                        change = true;
                    }
                }
            }
        }
        if (change) {
            return 'You are about to leave this page and have unsaved changes. Are you sure?';
        } else {
            return;
        }
    } else {
        return;
    }
};

/**
 * Document ready function to load the correct URI string into the URI span
 */
$(document).ready(function(){
    if (typeof _exp != 'undefined') {
        phireTimeout = setInterval(function() {
            var url = decodeURIComponent(_base);
            if (confirm('Your session is about to end. Do you wish to logout?')) {
                window.location = url + '/logout';
            } else {
                $().ajax(url + '/users/sessions/json');
            }

        }, _exp * 1000);
    }

    // Check saved timestamp to determine if the saved div should display
    if ($().get('saved') != undefined) {
        var ts = Math.round(new Date().getTime() / 1000);
        var diff = Math.abs($().get('saved') - ts);
        if (diff < 40) {
            if ($('#result')[0] != undefined) {
                $('#result').css({
                    "background-color" : '#dbf2bf',
                    "color"            : '#315900',
                    "opacity"          : 0
                });
                $('#result').val('Saved!');
                $('#result').fade(100, {tween : 10, speed: 200});
                clr = setTimeout(clearStatus, 3000);
            }
        }
    }

    if ($('#errors')[0] != undefined) {
        $('#errors').css('opacity', 100);
    }

    // For content form
    if ($('#content-form')[0] != undefined) {
        contentForm = $('#content-form');
        contentForm.submit(function(){
            submitted = true;
        });

        if ($('#uri')[0] != undefined) {
            var val = '';
            if ($('#parent_id')[0] != undefined) {
                var parent = $('#parent_id').val();
                if (parent != contentParentId) {
                    contentParentId = parent;
                    var j = $().json.parse('../json/' + parent);
                    contentParentUri = j.uri;
                    val = contentParentUri + $('#uri').val();
                } else {
                    val = $('#uri').val();
                }
            }
            if ($('#uri')[0].type != 'file') {
                if ((val != '') && (val.substring(0, 1) != '/')) {
                    val = '/' + val;
                } else if (val == '') {
                    val = '/';
                }
                $($('#uri').parent()).append('span', {"id" : 'uri-span'}, ((val.substring(0, 2) == '//') ? val.substring(1) : val));
            }

            // Check preview timestamp to determine if a preview window should be opened
            if ($().get('preview') != undefined) {
                var ts = Math.round(new Date().getTime() / 1000);
                var diff = Math.abs($().get('preview') - ts);
                if (diff < 40) {
                    if ($('#uri-span')[0] != undefined) {
                        window.open(decodeURIComponent($().get('base_path')) + $('#uri-span').val());
                    }
                }
            }
        }

        curForm = '#content-form';
        $().beforeunload(checkFormChange);
    }

    // For category form
    if ($('#category-form')[0] != undefined) {
        categoryForm = $('#category-form');
        if ($('#slug')[0] != undefined) {
            var val = '';
            if ($('#parent_id')[0] != undefined) {
                var parent = $('#parent_id').val();
                if (parent != categoryParentId) {
                    categoryParentId = parent;
                    var jsonLoc = (window.location.href.indexOf('edit') != -1) ? '../json/' : './json/';
                    var j = $().json.parse(jsonLoc + parent);
                    categoryParentUri = j.uri;
                    val = categoryParentUri + $('#slug').val();
                } else {
                    val = $('#slug').val();
                }
            }
            if ((val != '') && (val.substring(0, 1) != '/')) {
                val = '/' + val;
            } else if (val == '') {
                val = '/';
            }
            $($('#slug').parent()).append('span', {"id" : 'slug-span'}, ((val.substring(0, 2) == '//') ? val.substring(1) : val));
        }
    }

    if ($('#content-remove-form')[0] != undefined) {
        $('#checkall').click(function(){
            if (this.checked) {
                $('#content-remove-form').checkAll(this.value);
            } else {
                $('#content-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#navigation-remove-form')[0] != undefined) {
        $('#checkall').click(function(){
            if (this.checked) {
                $('#navigation-remove-form').checkAll(this.value);
            } else {
                $('#navigation-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#category-remove-form')[0] != undefined) {
        $('#checkall').click(function(){
            if (this.checked) {
                $('#category-remove-form').checkAll(this.value);
            } else {
                $('#category-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#content-type-remove-form')[0] != undefined) {
        $('#checkall').click(function(){
            if (this.checked) {
                $('#content-type-remove-form').checkAll(this.value);
            } else {
                $('#content-type-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#template-remove-form')[0] != undefined) {
        $('#checkall').click(function(){
            if (this.checked) {
                $('#template-remove-form').checkAll(this.value);
            } else {
                $('#template-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#themes-remove-form')[0] != undefined) {
        $('#checkall').click(function(){
            if (this.checked) {
                $('#themes-remove-form').checkAll(this.value);
            } else {
                $('#themes-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#modules-remove-form')[0] != undefined) {
        $('#checkall').click(function(){
            if (this.checked) {
                $('#modules-remove-form').checkAll(this.value);
            } else {
                $('#modules-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#user-remove-form')[0] != undefined) {
        $('#checkall').click(function(){
            if (this.checked) {
                $('#user-remove-form').checkAll(this.value);
            } else {
                $('#user-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#role-remove-form')[0] != undefined) {
        $('#checkall').click(function(){
            if (this.checked) {
                $('#role-remove-form').checkAll(this.value);
            } else {
                $('#role-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#session-remove-form')[0] != undefined) {
        $('#checkall').click(function(){
            if (this.checked) {
                $('#session-remove-form').checkAll(this.value);
            } else {
                $('#session-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#type-remove-form')[0] != undefined) {
        $('#checkall').click(function(){
            if (this.checked) {
                $('#type-remove-form').checkAll(this.value);
            } else {
                $('#type-remove-form').uncheckAll(this.value);
            }
        });
    }
});
