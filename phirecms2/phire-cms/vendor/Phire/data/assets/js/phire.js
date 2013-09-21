/**
 * Phire CMS 2.0 Scripts
 */

var resourceCount = 1;
var batchCount = 1;
var actions = [];
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
 * Function to get resource permission actions
 *
 * @return void
 */
var getPermissions = function() {
    if (_request.readyState == 4) {
        if (_request.status == 200) {
            var j = $().json.parse(_request.responseText);
            actions = j.actions;
        }
    }
};

/**
 * Function to add resource/permission
 *
 * @return void
 */
var addResource = function() {
    resourceCount++;

    // Add resource select field
    $($('#resource_new_1').parent()).clone(
        '#resource_new_1',
        [['name', 'resource_new_' + resourceCount], ['id', 'resource_new_' + resourceCount]]
    );

    // Add permission select field
    $($('#permission_new_1').parent()).clone(
        '#permission_new_1',
        [['name', 'permission_new_' + resourceCount], ['id', 'permission_new_' + resourceCount]]
    );

    // Add allow select field
    $($('#allow_new_1').parent()).clone(
        '#allow_new_1',
        [['name', 'allow_new_' + resourceCount], ['id', 'allow_new_' + resourceCount]]
    );

    $('#resource_new_' + resourceCount).val($('#resource_new_' + (resourceCount - 1) + ' > option:selected').val());
    $('#permission_new_' + resourceCount).val($('#permission_new_' + (resourceCount - 1) + ' > option:selected').val());
    $('#allow_new_' + resourceCount).val($('#allow_new_' + (resourceCount - 1) + ' > option:selected').val());
};

/**
 * Function to change permissions
 *
 * @param  Mixed sel
 * @return void
 */
var changePermissions = function(sel) {
    var cur = (sel.id.indexOf('cur_') != -1) ? 'cur' : 'new';
    var id = sel.id.substring(sel.id.lastIndexOf('_') + 1);
    var marked = $('#' + sel.id + ' > option:selected').val();

    var opts = $('#permission_' + cur + '_' + id + ' > option').objs;
    var start = opts.length - 1;

    for (var i = start; i >= 0; i--) {
        $(opts[i]).remove();
    }

    $('#permission_' + cur + '_' + id).append('option', ['value', 0], '(All)');

    if (marked != 0) {
        var jsonLoc = (window.location.href.indexOf('edit') != -1) ? '../json/' : './json/';
        $xmlHttp().get(jsonLoc + encodeURIComponent(marked), getPermissions);
        for (var i = 0; i < actions.length; i++) {
            $('#permission_' + cur + '_' + id).append('option', ['value', actions[i]], actions[i]);
        }
    }
};

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
 * Function to process form
 *
 * @return void
 */
var processForm = function() {
    if (_request.readyState == 4) {
        if (_request.status == 200) {
            var j = $().json.parse(_request.responseText);
            if (j.updated != undefined) {
                if (j.redirect != undefined) {
                    window.location.href = j.redirect;
                } else {
                    if ($('#result').obj != null) {
                        $('#result').css('background-color', '#dbf2bf')
                                    .css('color', '#315900')
                                    .css('opacity', 0);
                        $('#result').val('Saved!');
                        for (var i = 1; i <= curErrors; i++) {
                            if ($('#error-' + i).obj != null) {
                                $('#error-' + i).remove();
                            }
                        }
                        if ($('#updated').obj != null) {
                            $('#updated').val(j.updated);
                        }
                        if ((j.form != undefined) && ($('#' + j.form).obj != null)) {
                            var f = $('#' + j.form).obj;
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
                        $fx('#result').fade(100, 10, 20);
                        clr = setTimeout(clearStatus, 3000);
                    }
                }
            } else {
                if ($('#result').obj != null) {
                    $('#result').css('background-color', '#e8d0d0')
                        .css('color', '#8e0202')
                        .css('opacity', 0);
                    $('#result').val('Please correct the errors below.');
                    for (var i = 1; i <= curErrors; i++) {
                        if ($('#error-' + i).obj != null) {
                            $('#error-' + i).remove();
                        }
                    }
                    $fx('#result').fade(100, 10, 20);
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
                        $($('#' + name).parent()).append('div', [['id', 'error-' + curErrors], ['class', 'error']], j[name]);
                    }

                }
            }
        }
    }
};

/**
 * Function to update form
 *
 * @param  String  form
 * @param  Boolean ret
 * @param  Boolean prev
 * @return Boolean
 */
var updateForm = function(form, ret, prev) {
    submitted = true;
    if (ret) {
        if (prev != null) {
            if ($('#status').obj != null) {
                $('#status').val(1);
            }
            if ($('#update_value').obj != null) {
                $('#update_value').val(2);
            }
        } else {
            if ($('#update_value').obj != null) {
                $('#update_value').val(1);
            }
        }
        return true;
    } else {
        var f = $(form).obj;
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
        $xmlHttp().post(f, processForm, url);
        return false;
    }
};

/**
 * Function to clear the status
 *
 * @return void
 */
var clearStatus = function() {
    $fx('#result').fade(0, 10, 20);
    clearTimeout(clr);
};

/**
 * Function to clear the status
 *
 * @param  Object a
 * @param  int    hgt
 * @return void
 */
var wipeErrors = function(a, hgt) {
    if ($('#errors').height() > 50) {
        $(a).val('Show');
        $fx('#errors').wipeUp(17, 10, 20);
    } else {
        $(a).val('Hide');
        $fx('#errors').wipeUp(hgt, 10, 20);
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
    $($('#file_name_1').parent()).clone(
        '#file_name_1',
        [['name', 'file_name_' + batchCount], ['id', 'file_name_' + batchCount]]
    );

    // Add file title field
    $($('#file_title_1').parent()).clone(
        '#file_title_1',
        [['name', 'file_title_' + batchCount], ['id', 'file_title_' + batchCount]]
    );
};

/**
 * Function to check if form values have changed before leaving the page
 *
 * @return void
 */
var checkFormChange = function() {
    if (!submitted) {
        var change = false;
        var f = $(curForm).obj;
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
        var phireTimeout = setInterval(function() {
            var url = decodeURIComponent(_base);
            if (confirm('Your session is about to end. Do you wish to logout?')) {
                window.location = url + '/logout';
            } else {
                $xmlHttp().get(url + '/users/sessions/json');
            }

        }, _exp * 1000);
    }

    // Check saved timestamp to determine if the saved div should display
    if ($().get('saved') != undefined) {
        var ts = Math.round(new Date().getTime() / 1000);
        var diff = Math.abs($().get('saved') - ts);
        if (diff < 40) {
            if ($('#result').obj != null) {
                $('#result').css('background-color', '#dbf2bf')
                    .css('color', '#315900')
                    .css('opacity', 0);
                $('#result').val('Saved!');
                $fx('#result').fade(100, 10, 20);
                clr = setTimeout(clearStatus, 3000);
            }
        }
    }

    if ($('#errors').obj != null) {
        $('#errors').css('opacity', 100);
    }

    // For content form
    if ($('#content-form').obj != null) {
        contentForm = $form('#content-form');
        contentForm.submit(function(){
            submitted = true;
        });

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

            // Check preview timestamp to determine if a preview window should be opened
            if ($().get('preview') != undefined) {
                var ts = Math.round(new Date().getTime() / 1000);
                var diff = Math.abs($().get('preview') - ts);
                if (diff < 40) {
                    if ($('#uri-span').obj != null) {
                        window.open(decodeURIComponent($().get('base_path')) + $('#uri-span').val());
                    }
                }
            }
        }

        curForm = '#content-form';
        $().beforeunload(checkFormChange);
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
    if ($('#themes-remove-form').obj != null) {
        $('#checkall').click(function(){
            if (this.checked) {
                $form('#themes-remove-form').checkAll(this.value);
            } else {
                $form('#themes-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#modules-remove-form').obj != null) {
        $('#checkall').click(function(){
            if (this.checked) {
                $form('#modules-remove-form').checkAll(this.value);
            } else {
                $form('#modules-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#user-remove-form').obj != null) {
        $('#checkall').click(function(){
            if (this.checked) {
                $form('#user-remove-form').checkAll(this.value);
            } else {
                $form('#user-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#role-remove-form').obj != null) {
        $('#checkall').click(function(){
            if (this.checked) {
                $form('3role-remove-form').checkAll(this.value);
            } else {
                $form('#role-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#session-remove-form').obj != null) {
        $('#checkall').click(function(){
            if (this.checked) {
                $form('#session-remove-form').checkAll(this.value);
            } else {
                $form('#session-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#type-remove-form').obj != null) {
        $('#checkall').click(function(){
            if (this.checked) {
                $form('#type-remove-form').checkAll(this.value);
            } else {
                $form('#type-remove-form').uncheckAll(this.value);
            }
        });
    }
});
