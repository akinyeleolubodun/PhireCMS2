/**
 * Phire CMS 2.0 Scripts
 */

var clr;
var curForm;
var phireTimeout;
var submitted = false;
var resourceCount = 1;
var batchCount = 1;
var contentForm;
var categoryForm;
var contentParentId = 0;
var contentParentUri = '';
var categoryParentId = 0;
var categoryParentUri = '';
var curErrors = 0;
var valCount = 1;
var modelCount = 1;
var curValue = null;
var selIds = [];
var validators = [
    '----', 'AlphaNumeric', 'Alpha', 'BetweenInclude', 'Between', 'CreditCard', 'Email',
    'Equal', 'Excluded', 'GreaterThanEqual', 'GreaterThan', 'Included', 'Ipv4', 'Ipv6',
    'IsSubnetOf', 'LengthBetweenInclude', 'LengthBetween', 'LengthGte', 'LengthGt',
    'LengthLte', 'LengthLt', 'Length', 'LessThanEqual', 'LessThan', 'NotEmpty',
    'NotEqual', 'Numeric', 'RegEx', 'Subnet'
];

var sysBasePath = window.location.href;
if (sysBasePath.indexOf('/add') != -1) {
    sysBasePath = sysBasePath.substring(0, sysBasePath.indexOf('/add'));
} else if (sysBasePath.indexOf('/edit') != -1) {
    sysBasePath = sysBasePath.substring(0, sysBasePath.indexOf('/edit'));
}
sysBasePath = sysBasePath.substring(0, sysBasePath.lastIndexOf('/'));

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

    // Add type select field
    $('#type_new_1').clone({
        "name" : 'type_new_' + resourceCount,
        "id"   : 'type_new_' + resourceCount
    }).appendTo($('#type_new_1').parent());

    // Add allow select field
    $('#allow_new_1').clone({
        "name" : 'allow_new_' + resourceCount,
        "id"   : 'allow_new_' + resourceCount
    }).appendTo($('#allow_new_1').parent());

    $('#resource_new_' + resourceCount).val($('#resource_new_' + (resourceCount - 1) + ' > option:selected').val());
    $('#permission_new_' + resourceCount).val($('#permission_new_' + (resourceCount - 1) + ' > option:selected').val());
    $('#type_new_' + resourceCount).val($('#type_new_' + (resourceCount - 1) + ' > option:selected').val());
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

    opts = $('#type_' + cur + '_' + id + ' > option').toArray();
    start = opts.length - 1;

    for (var i = start; i >= 0; i--) {
        $(opts[i]).remove();
    }

    $('#type_' + cur + '_' + id).append('option', {"value" : 0}, '(All)');
    $('#permission_' + cur + '_' + id).append('option', {"value" : 0}, '(All)');

    if (marked != 0) {
        var jsonLoc = (window.location.href.indexOf('edit') != -1) ? '../json/' : './json/';
        var j = $().json.parse(jsonLoc + encodeURIComponent(marked.replace(/\\/g, '_')));
        for (type in j.types) {
            if (type != 0) {
                $('#type_' + cur + '_' + id).append('option', {"value" : type}, j.types[type]);
            }
        }
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
    var j = $().json.parse('./config/json/' + encodeURIComponent(val.replace(/\//g, '_')));
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
 * Function to show upload div
 */
var showLoading = function() {
    document.getElementById('loading').style.display = 'block';
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
 * Function to add validators to the fields form
 *
 * @return void
 */
var addValidator = function() {
    valCount++;

    // Add validator select field
    $('#validator_new_1').clone({
        "name" : 'validator_new_' + valCount,
        "id"   : 'validator_new_' + valCount
    }).appendTo($('#validator_new_1').parent());

    // Add validator value text field
    $('#validator_value_new_1').clone({
        "name" : 'validator_value_new_' + valCount,
        "id"   : 'validator_value_new_' + valCount
    }).appendTo($('#validator_value_new_1').parent());

    // Add validator message text field
    $('#validator_message_new_1').clone({
        "name" : 'validator_message_new_' + valCount,
        "id"   : 'validator_message_new_' + valCount
    }).appendTo($('#validator_message_new_1').parent());
};

/**
 * Function to add model/type to the fields form
 *
 * @return void
 */
var addModel = function() {
    var parentModelId = modelCount;
    modelCount++;

    // Add model select field
    $('#model_new_' + parentModelId).clone({
        "name" : 'model_new_' + modelCount,
        "id"   : 'model_new_' + modelCount
    }).appendTo($('#model_new_' + parentModelId).parent());

    // Add type_id text field
    $('#type_id_new_' + parentModelId).clone({
        "name"  : 'type_id_new_' + modelCount,
        "id"    : 'type_id_new_' + modelCount,
        "value" : 0
    }).appendTo($('#type_id_new_' + parentModelId).parent());

    // Select marked clean up
    var sel1 = $('#model_new_' + parentModelId)[0];
    var sel2 = $('#model_new_' + modelCount)[0];
    var marked1 = null;
    var marked2 = null;

    for (var i = 0; i < sel1.options.length; i++) {
        if (sel1.options[i].selected) {
            marked1 = i;
        }
        if (sel2.options[i].selected) {
            marked2 = i;
            sel2.options[i].selected = false;
        }
    }

    if (marked1 != marked2) {
        sel2.options[marked1].selected = true;
    }
};

/**
 * Function to add fields to the form
 *
 * @param  array flds
 * @return void
 */
var addFields = function(flds) {
    var fieldCount = 1;

    // Get the next field number
    while ($('#field_' + flds[0] + '_new_' + fieldCount)[0] != undefined) {
        fieldCount++;
    }

    // Clone the fields
    for (var i = 0; i < flds.length; i++) {
        var oldName = 'field_' + flds[i] + '_new_1';
        var newName = 'field_' + flds[i] + '_new_' + fieldCount;
        var oldObj = $('#' + oldName)[0];

        // If the object is a checkbox or radio set, clone the fieldset
        if ((oldObj.type == 'checkbox') || (oldObj.type == 'radio')) {
            var fldSet = $(oldObj).parent();
            var fldSetInputs = fldSet.getElementsByTagName('input');
            var vals = [];
            var mrk = [];
            for (var j = 0; j < fldSetInputs.length; j++) {
                vals.push(fldSetInputs[j].value);
                if (fldSetInputs[j].checked) {
                    mrk.push(fldSetInputs[j].value);
                }
            }
            var fldSetParent = $(fldSet).parent();
            if (oldObj.type == 'checkbox') {
                var attribs = {"name" : newName + '[]', "id" : newName};
                $(fldSetParent).appendCheckbox(vals, attribs, mrk);
            } else {
                var attribs = {"name" : newName, "id" : newName};
                $(fldSetParent).appendRadio(vals, attribs, mrk);
            }
        // Else, clone the input or select
        } else {
            var realNewName = ((oldObj.nodeName == 'SELECT') && (oldObj.getAttribute('multiple') != undefined)) ?
                newName + '[]' :
                newName;
            $('#' + oldName).clone({
                "name" : realNewName,
                "id"   : newName
            }).appendTo($('#' + oldName).parent());
        }
    }
};

/**
 * Function to change model types
 *
 * @param  mixed sel
 * @return void
 */
var changeModelTypes = function(sel) {
    var id = sel.id.substring(sel.id.lastIndexOf('_') + 1);
    var cur = (sel.id.indexOf('new_') != -1) ? 'new_' : 'cur_';
    var marked = $('#' + sel.id + ' > option:selected').val();
    var opts = $('#type_id_' + cur + id + ' > option').toArray();
    var start = opts.length - 1;

    for (var i = start; i >= 0; i--) {
        $(opts[i]).remove();
    }

    // Get new model types and create new select drop down
    var jsonLoc = (window.location.href.indexOf('edit') != -1) ? '../json/' : './json/';
    var j = $().json.parse(jsonLoc + marked.replace(/\\/g, '_'));
    if (j.types[0] != undefined) {
        var types = [];
        for (key in j.types) {
            types.push([key, j.types[key]]);
        }
    }
    for (var i = 0; i < types.length; i++) {
        $('#type_id_' + cur + id).append('option', {"value" : types[i][0]}, types[i][1]);
    }
};

/**
 * Function to change field history
 *
 * @param  mixed  sel
 * @param  string basePath
 * @return void
 */
var changeHistory = function(sel, basePath) {
    var ids = sel.id.substring(sel.id.indexOf('_') + 1).split('_');
    var modelId = ids[0];
    var fieldId = ids[1];
    var marked = $('#' + sel.id + ' > option:selected').val();

    if ((curValue == null) && ($('#field_' + fieldId)[0] != undefined)) {
        curValue = $('#field_' + fieldId).val();
    }

    if (marked != 0) {
        var j = $().json.parse(basePath + '/structure/fields/json/history/' + modelId + '/' + fieldId + '/' + marked);
        if ($('#field_' + j.fieldId)[0] != undefined) {
            if (typeof CKEDITOR !== 'undefined') {
                if (CKEDITOR.instances['field_' + j.fieldId] != undefined) {
                    CKEDITOR.instances['field_' + j.fieldId].setData(j.value);
                }
            } else if (typeof tinymce !== 'undefined') {
                tinymce.activeEditor.setContent(j.value);
            }
            $('#field_' + j.fieldId).val(j.value);
        }
    } else {
        if ($('#field_' + fieldId)[0] != undefined) {
            if (typeof CKEDITOR !== 'undefined') {
                if (CKEDITOR.instances['field_' + fieldId] != undefined) {
                    CKEDITOR.instances['field_' + fieldId].setData(curValue);
                }
            } else if (typeof tinymce !== 'undefined') {
                tinymce.activeEditor.setContent(curValue);
            }
            $('#field_' + fieldId).val(curValue);
        }
    }
};

/**
 * Function to change editor
 *
 * @param  mixed  sel
 * @return void
 */
var changeEditor = function(sel) {
    var content = '';
    var val = $(sel).val();
    var id = sel.id.substring(sel.id.indexOf('_') + 1);
    var w = Math.round($('#field_' + id).width());
    var h = Math.round($('#field_' + id).height());

    if (val == 'source') {
        if (typeof CKEDITOR !== 'undefined') {
            content = CKEDITOR.instances['field_' + id].getData();
            CKEDITOR.instances['field_' + id].destroy();
        } else if (typeof tinymce !== 'undefined') {
            content = tinymce.activeEditor.getContent();
            var par = $('#field_' + id).parent();
            var children = par.childNodes;
            for (var i = 0; i < children.length; i++) {
                if (children[i].nodeName == 'DIV') {
                    par.removeChild(children[i]);
                }
            }
        }
        $('#field_' + id).val(content);
        $('#field_' + id).show();
    } else if (val == 'ckeditor') {
        loadEditor('ckeditor', id);
    } else if (val == 'tinymce') {
        loadEditor('tinymce', id);
    }
};

/**
 * Function to toggle editor select
 *
 * @param  mixed  sel
 * @return void
 */
var toggleEditor = function(sel) {
    if ($(sel).val().indexOf('textarea') != -1) {
        $('#editor').show();
    } else {
        $('#editor').hide();
    }
};

/**
 * Function to loader editor(s)
 *
 * @param  string editor
 * @return void
 */
var loadEditor = function(editor, id) {
    if (null != id) {
        var w = Math.round($('#field_' + id).width());
        var h = Math.round($('#field_' + id).height());
        selIds = [{ "id" : id, "width" : w, "height" : h }];
    }
    if (selIds.length > 0) {
        for (var i = 0; i < selIds.length; i++) {
            if (editor == 'ckeditor') {
                if (CKEDITOR.instances['field_' + selIds[i].id] == undefined) {
                    CKEDITOR.replace(
                        'field_' + selIds[i].id,
                        {
                            width  : selIds[i].width,
                            height : selIds[i].height,
                            filebrowserBrowseUrl      : sysBasePath + '/structure/fields/browser/file?editor=ckeditor',
                            filebrowserImageBrowseUrl : sysBasePath + '/structure/fields/browser/image?editor=ckeditor',
                            filebrowserWindowWidth    : '900',
                            filebrowserWindowHeight   : '700'
                        }
                    );
                }
            } else if (editor == 'tinymce') {
                if (tinymce.editors['field_' + selIds[i].id] == undefined) {
                    tinymce.init(
                        {
                            selector              : "textarea#field_" + selIds[i].id,
                            theme                 : "modern",
                            plugins: [
                                "advlist autolink lists link image hr", "searchreplace wordcount code fullscreen",
                                "table", "template paste textcolor"
                            ],
                            image_advtab          : true,
                            toolbar1              : "insertfile undo redo | styleselect | forecolor backcolor | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table | link image",
                            width                 : selIds[i].width,
                            height                : selIds[i].height,
                            relative_urls         : false,
                            file_browser_callback : function(field_name, url, type, win) {
                                tinymce.activeEditor.windowManager.open({
                                    title  : "Asset Browser",
                                    url    : sysBasePath + '/structure/fields/browser/' + type + '?editor=tinymce',
                                    width  : 900,
                                    height : 700
                                }, {
                                    oninsert : function(url) {
                                        win.document.getElementById(field_name).value = url;
                                    }
                                });
                            }
                        }
                    );
                }
            }
        }
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

    if ($('#model_1')[0] != undefined) {
        while ($('#model_' + modelCount)[0] != undefined) {
            modelCount++;
        }
        modelCount--;
    }
    if ($('#field-remove-form')[0] != undefined) {
        $('#checkall').click(function(){
            if (this.checked) {
                $('#field-remove-form').checkAll(this.value);
            } else {
                $('#field-remove-form').uncheckAll(this.value);
            }
        });
    }

    if ($('#field-group-remove-form')[0] != undefined) {
        $('#checkall').click(function(){
            if (this.checked) {
                $('#field-group-remove-form').checkAll(this.value);
            } else {
                $('#field-group-remove-form').uncheckAll(this.value);
            }
        });
    }

    var sels = $('select').toArray();
    var selVal = null;
    if ((sels != '') && (sels.length > 0)) {
        for (var i = 0; i < sels.length; i++) {
            if (sels[i].id.indexOf('editor_') != -1) {
                var id = sels[i].id.substring(sels[i].id.indexOf('_') + 1);
                var w = Math.round($('#field_' + id).width());
                var h = Math.round($('#field_' + id).height());
                selIds.push({ "id" : id, "width" : w, "height" : h });
                selVal = $(sels[i]).val();
            }
        }

        if (null != selVal) {
            var head = document.getElementsByTagName('head')[0];
            var script = document.createElement("script");
            switch (selVal) {
                case 'ckeditor':
                    script.src = _jaxRoot + 'ckeditor/ckeditor.js';
                    script.onload = script.onreadystatechange = function() {
                        if (typeof CKEDITOR != 'undefined') {
                            loadEditor('ckeditor');
                        }
                    }
                    head.appendChild(script);
                    break;

                case 'tinymce':
                    script.src = _jaxRoot + 'tinymce/tinymce.min.js';
                    script.onload = script.onreadystatechange = function() {
                        if (typeof tinymce != 'undefined') {
                            loadEditor('tinymce');
                        }
                    }
                    head.appendChild(script);
                    break;
            }
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
