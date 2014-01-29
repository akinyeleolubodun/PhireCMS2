/**
 * Phire CMS 2.0 Scripts
 */

var phire = {
    clear             : null,
    curForm           : null,
    timeout           : null,
    submitted         : false,
    resourceCount     : 1,
    batchCount        : 1,
    modelCount        : 1,
    valCount          : 1,
    contentForm       : null,
    categoryForm      : null,
    contentParentId   : 0,
    contentParentUri  : '',
    categoryParentId  : 0,
    categoryParentUri : '',
    curErrors         : 0,
    curValue          : null,
    selIds            : [],
    basePath          : null,
    sysBasePath       : null,
    errorDisplay      : {
        "color"    : '#f00',
        "bgColor"  : '#ffe5e5',
        "orgColor" : '#fff',
        "speed"    : 500,
        "tween"    : 25,
        "easing"   : jax.tween.easein.quad
    },
    addResource       : function() {
        phire.resourceCount++;

        // Add resource select field
        jax('#resource_new_1').clone({
            "name" : 'resource_new_' + phire.resourceCount,
            "id"   : 'resource_new_' + phire.resourceCount
        }).appendTo(jax('#resource_new_1').parent());

        // Add permission select field
        jax('#permission_new_1').clone({
            "name" : 'permission_new_' + phire.resourceCount,
            "id"   : 'permission_new_' + phire.resourceCount
        }).appendTo(jax('#permission_new_1').parent());

        // Add type select field
        jax('#type_new_1').clone({
            "name" : 'type_new_' + phire.resourceCount,
            "id"   : 'type_new_' + phire.resourceCount
        }).appendTo(jax('#type_new_1').parent());

        // Add allow select field
        jax('#allow_new_1').clone({
            "name" : 'allow_new_' + phire.resourceCount,
            "id"   : 'allow_new_' + phire.resourceCount
        }).appendTo(jax('#allow_new_1').parent());

        jax('#resource_new_' + phire.resourceCount).val(jax('#resource_new_' + (phire.resourceCount - 1) + ' > option:selected').val());
        jax('#permission_new_' + phire.resourceCount).val(jax('#permission_new_' + (phire.resourceCount - 1) + ' > option:selected').val());
        jax('#type_new_' + phire.resourceCount).val(jax('#type_new_' + (phire.resourceCount - 1) + ' > option:selected').val());
        jax('#allow_new_' + phire.resourceCount).val(jax('#allow_new_' + (phire.resourceCount - 1) + ' > option:selected').val());
    },
    changePermissions : function(sel) {
        var cur = (sel.id.indexOf('cur_') != -1) ? 'cur' : 'new';
        var id = sel.id.substring(sel.id.lastIndexOf('_') + 1);
        var marked = jax('#' + sel.id + ' > option:selected').val();

        var opts = jax('#permission_' + cur + '_' + id + ' > option').toArray();
        var start = opts.length - 1;

        for (var i = start; i >= 0; i--) {
            jax(opts[i]).remove();
        }

        opts = jax('#type_' + cur + '_' + id + ' > option').toArray();
        start = opts.length - 1;

        for (var i = start; i >= 0; i--) {
            jax(opts[i]).remove();
        }

        jax('#type_' + cur + '_' + id).append('option', {"value" : 0}, '(All)');
        jax('#permission_' + cur + '_' + id).append('option', {"value" : 0}, '(All)');

        if (marked != 0) {
            var jsonLoc = (window.location.href.indexOf('edit') != -1) ? '../json/' : './json/';
            var j = jax.json.parse(jsonLoc + encodeURIComponent(marked.replace(/\\/g, '_')));
            for (type in j.types) {
                if (type != 0) {
                    jax('#type_' + cur + '_' + id).append('option', {"value" : type}, j.types[type]);
                }
            }
            for (var i = 0; i < j.actions.length; i++) {
                jax('#permission_' + cur + '_' + id).append('option', {"value" : j.actions[i]}, j.actions[i]);
            }
        }
    },
    slug : function(src, tar) {
        if ((src != null) && (tar != null)) {
            var uri = new jax.String(jax('#' + src).val());
            jax('#' + tar).val(uri.slug());
        }

        if (jax('#uri-span')[0] != undefined) {
            if (jax('#parent_id')[0] != undefined) {
                var parent = jax('#parent_id').val();
                if (parent != phire.contentParentId) {
                    phire.contentParentId = parent;
                    var j = jax.json.parse('../json/' + parent);
                    phire.contentParentUri = j.uri;
                }
            }
            var val = jax('#' + tar).val();
            val = phire.contentParentUri + val;
            if ((val != '') && (val.substring(0, 1) != '/')) {
                val = '/' + val;
            } else if (val == '') {
                val = '/';
            }
            jax('#uri-span').val(((val.substring(0, 2) == '//') ? val.substring(1) : val));
        }
    },
    catSlug : function(src, tar) {
        if ((src != null) && (tar != null)) {
            var uri = new jax.String(jax('#' + src).val());
            jax('#' + tar).val(uri.slug());
        }

        if (jax('#slug-span')[0] != undefined) {
            if (jax('#parent_id')[0] != undefined) {
                var parent = jax('#parent_id').val();
                if (parent != phire.categoryParentId) {
                    phire.categoryParentId = parent;
                    var jsonLoc = (window.location.href.indexOf('edit') != -1) ? '../json/' : './json/';
                    var j = jax.json.parse(jsonLoc + parent);
                    phire.categoryParentUri = j.uri;
                }
            }
            var val = jax('#' + tar).val();
            val = phire.categoryParentUri + val;
            if ((val != '') && (val.substring(0, 1) != '/')) {
                val = '/' + val;
            } else if (val == '') {
                val = '/';
            }
            jax('#slug-span').val(((val.substring(0, 2) == '//') ? val.substring(1) : val));
        }
    },
    customDatetime : function(val) {
        var j = jax.json.parse('./config/json/' + encodeURIComponent(val.replace(/\//g, '_')));
        if ((jax('#custom-datetime')[0] != undefined) && (j != undefined)) {
            var v = (j.format != undefined) ? '(' + j.format + ')' : '';
            jax('#custom-datetime').val(v);
        }
    },
    processForm : function(response) {
        var j = jax.json.parse(response.text);
        if (j.updated != undefined) {
            if (j.redirect != undefined) {
                window.location.href = j.redirect;
            } else {
                if (jax('#result')[0] != undefined) {
                    jax('#result').css({
                        "background-color" : '#dbf2bf',
                        "color"            : '#315900',
                        "opacity"          : 0
                    });
                    jax('#result').val('Saved!');
                    for (var i = 1; i <= phire.curErrors; i++) {
                        if (jax('#error-' + i)[0] != undefined) {
                            jax('#error-' + i).remove();
                        }
                    }
                    if (jax('#updated')[0] != undefined) {
                        jax('#updated').val(j.updated);
                    }
                    if ((j.form != undefined) && (jax('#' + j.form)[0] != undefined)) {
                        var f = jax('#' + j.form)[0];
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
                    jax('#result').fade(100, {tween : 10, speed: 200});
                    phire.clear = setTimeout(phire.clearStatus, 3000);
                }
            }
        } else {
            if (jax('#result')[0] != undefined) {
                jax('#result').css({
                    "background-color" : '#e8d0d0',
                    "color"            : '#8e0202',
                    "opacity"          : 0
                });
                jax('#result').val('Please correct the errors below.');
                for (var i = 1; i <= phire.curErrors; i++) {
                    if (jax('#error-' + i)[0] != undefined) {
                        jax('#error-' + i).remove();
                    }
                }
                jax('#result').fade(100, {tween : 10, speed: 200});
                phire.clear = setTimeout(phire.clearStatus, 3000);
            }
            for (name in j) {
                // Check if the error already exists via a PHP POST
                var curErrorDivs = jax('#' + name).parent().getElementsByTagName('div');
                var curErrorDivsHtml = [];
                for (var i = 0; i < curErrorDivs.length; i++) {
                    curErrorDivsHtml.push(curErrorDivs[i].innerHTML);
                }
                // If error doesn't exists yet, append it
                if (curErrorDivsHtml.indexOf(j[name].toString()) == -1) {
                    phire.curErrors++;
                    jax(jax('#' + name).parent()).append('div', {"id" : 'error-' + phire.curErrors, "class" : 'error'}, j[name]);
                }

            }
        }
    },
    updateForm : function(form, ret, prev) {
        phire.submitted = true;
        if (ret) {
            if (prev != null) {
                if (jax('#status')[0] != undefined) {
                    jax('#status').val(1);
                }
                if (jax('#update_value')[0] != undefined) {
                    jax('#update_value').val(2);
                }
            } else {
                if (jax('#update_value')[0] != undefined) {
                    jax('#update_value').val(1);
                }
            }
            return true;
        } else {
            var f = jax(form)[0];
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
            var act = jax(form).attrib('action');
            var url = act + ((act.indexOf('?') != -1) ? '&update=1' : '?update=1');
            jax.ajax(url, {status : {200 : phire.processForm}, method : 'post', data : f});
            return false;
        }
    },
    clearStatus : function() {
        jax('#result').fade(0, {tween : 10, speed: 200});
        clearTimeout(phire.clear);
    },
    wipeErrors : function(a, hgt) {
        if (jax('#dir-errors').height() > 50) {
            jax(a).val('Show');
            jax('#dir-errors').wipeUp(17, {tween : 10, speed: 200});
        } else {
            jax(a).val('Hide');
            jax('#dir-errors').wipeUp(hgt, {tween : 10, speed: 200});
        }
    },
    addBatchFields : function(max) {
        if (phire.batchCount < max) {
            phire.batchCount++;

            // Add file name field
            jax('#file_name_1').clone({
                "name" : 'file_name_' + phire.batchCount,
                "id"   : 'file_name_' + phire.batchCount
            }).appendTo(jax('#file_name_1').parent());

            // Add file title field
            jax('#file_title_1').clone({
                "name" : 'file_title_' + phire.batchCount,
                "id"   : 'file_title_' + phire.batchCount
            }).appendTo(jax('#file_title_1').parent());
        }
    },
    showLoading : function() {
        document.getElementById('loading').style.display = 'block';
    },
    checkFormChange : function() {
        if (!phire.submitted) {
            var change = false;
            var f = jax(phire.curForm)[0];
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
    },
    addValidator : function() {
        phire.valCount++;

        // Add validator select field
        jax('#validator_new_1').clone({
            "name" : 'validator_new_' + phire.valCount,
            "id"   : 'validator_new_' + phire.valCount
        }).appendTo(jax('#validator_new_1').parent());

        // Add validator value text field
        jax('#validator_value_new_1').clone({
            "name" : 'validator_value_new_' + phire.valCount,
            "id"   : 'validator_value_new_' + phire.valCount
        }).appendTo(jax('#validator_value_new_1').parent());

        // Add validator message text field
        jax('#validator_message_new_1').clone({
            "name" : 'validator_message_new_' + phire.valCount,
            "id"   : 'validator_message_new_' + phire.valCount
        }).appendTo(jax('#validator_message_new_1').parent());
    },
    addModel : function() {
        var parentModelId = phire.modelCount;
        phire.modelCount++;

        // Add model select field
        jax('#model_new_' + parentModelId).clone({
            "name" : 'model_new_' + phire.modelCount,
            "id"   : 'model_new_' + phire.modelCount
        }).appendTo(jax('#model_new_' + parentModelId).parent());

        // Add type_id text field
        jax('#type_id_new_' + parentModelId).clone({
            "name"  : 'type_id_new_' + phire.modelCount,
            "id"    : 'type_id_new_' + phire.modelCount,
            "value" : 0
        }).appendTo(jax('#type_id_new_' + parentModelId).parent());

        // Select marked clean up
        var sel1 = jax('#model_new_' + parentModelId)[0];
        var sel2 = jax('#model_new_' + phire.modelCount)[0];
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
    },
    addFields : function(flds) {
        var fieldCount = 1;

        // Get the next field number
        while (jax('#field_' + flds[0] + '_new_' + fieldCount)[0] != undefined) {
            fieldCount++;
        }

        // Clone the fields
        for (var i = 0; i < flds.length; i++) {
            var oldName = 'field_' + flds[i] + '_new_1';
            var newName = 'field_' + flds[i] + '_new_' + fieldCount;
            var oldObj = jax('#' + oldName)[0];

            // If the object is a checkbox or radio set, clone the fieldset
            if ((oldObj.type == 'checkbox') || (oldObj.type == 'radio')) {
                var fldSet = jax(oldObj).parent();
                var fldSetInputs = fldSet.getElementsByTagName('input');
                var vals = [];
                var mrk = [];
                for (var j = 0; j < fldSetInputs.length; j++) {
                    vals.push(fldSetInputs[j].value);
                    if (fldSetInputs[j].checked) {
                        mrk.push(fldSetInputs[j].value);
                    }
                }
                var fldSetParent = jax(fldSet).parent();
                if (oldObj.type == 'checkbox') {
                    var attribs = {"name" : newName + '[]', "id" : newName};
                    jax(fldSetParent).appendCheckbox(vals, attribs, mrk);
                } else {
                    var attribs = {"name" : newName, "id" : newName};
                    jax(fldSetParent).appendRadio(vals, attribs, mrk);
                }
                // Else, clone the input or select
            } else {
                var realNewName = ((oldObj.nodeName == 'SELECT') && (oldObj.getAttribute('multiple') != undefined)) ?
                    newName + '[]' :
                    newName;
                jax('#' + oldName).clone({
                    "name" : realNewName,
                    "id"   : newName
                }).appendTo(jax('#' + oldName).parent());
            }
        }
    },
    changeModelTypes : function(sel) {
        var id = sel.id.substring(sel.id.lastIndexOf('_') + 1);
        var cur = (sel.id.indexOf('new_') != -1) ? 'new_' : 'cur_';
        var marked = jax('#' + sel.id + ' > option:selected').val();
        var opts = jax('#type_id_' + cur + id + ' > option').toArray();
        var start = opts.length - 1;

        for (var i = start; i >= 0; i--) {
            jax(opts[i]).remove();
        }

        // Get new model types and create new select drop down
        var jsonLoc = (window.location.href.indexOf('edit') != -1) ? '../json/' : './json/';
        var j = jax.json.parse(jsonLoc + marked.replace(/\\/g, '_'));
        if (j.types[0] != undefined) {
            var types = [];
            for (key in j.types) {
                types.push([key, j.types[key]]);
            }
        }
        for (var i = 0; i < types.length; i++) {
            jax('#type_id_' + cur + id).append('option', {"value" : types[i][0]}, types[i][1]);
        }
    },
    changeHistory : function(sel, basePath) {
        var ids = sel.id.substring(sel.id.indexOf('_') + 1).split('_');
        var modelId = ids[0];
        var fieldId = ids[1];
        var marked = jax('#' + sel.id + ' > option:selected').val();

        if ((phire.curValue == null) && (jax('#field_' + fieldId)[0] != undefined)) {
            phire.curValue = jax('#field_' + fieldId).val();
        }

        if (marked != 0) {
            var j = jax.json.parse(basePath + '/structure/fields/json/history/' + modelId + '/' + fieldId + '/' + marked);
            if (jax('#field_' + j.fieldId)[0] != undefined) {
                if (typeof CKEDITOR !== 'undefined') {
                    if (CKEDITOR.instances['field_' + j.fieldId] != undefined) {
                        CKEDITOR.instances['field_' + j.fieldId].setData(j.value);
                    }
                } else if (typeof tinymce !== 'undefined') {
                    tinymce.activeEditor.setContent(j.value);
                }
                jax('#field_' + j.fieldId).val(j.value);
            }
        } else {
            if (jax('#field_' + fieldId)[0] != undefined) {
                if (typeof CKEDITOR !== 'undefined') {
                    if (CKEDITOR.instances['field_' + fieldId] != undefined) {
                        CKEDITOR.instances['field_' + fieldId].setData(phire.curValue);
                    }
                } else if (typeof tinymce !== 'undefined') {
                    tinymce.activeEditor.setContent(phire.curValue);
                }
                jax('#field_' + fieldId).val(phire.curValue);
            }
        }
    },
    changeEditor : function(sel) {
        var content = '';
        var val = jax(sel).val();
        var id = sel.id.substring(sel.id.indexOf('_') + 1);
        var w = Math.round(jax('#field_' + id).width());
        var h = Math.round(jax('#field_' + id).height());

        if (val == 'source') {
            if (typeof CKEDITOR !== 'undefined') {
                content = CKEDITOR.instances['field_' + id].getData();
                CKEDITOR.instances['field_' + id].destroy();
            } else if (typeof tinymce !== 'undefined') {
                content = tinymce.activeEditor.getContent();
                tinymce.get('field_' + id).hide();
                //tinymce.editors['field_' + id] = undefined;
                //var par = jax('#field_' + id).parent();
                //var children = par.childNodes;
                //for (var i = 0; i < children.length; i++) {
                //    if (children[i].nodeName == 'DIV') {
                //        par.removeChild(children[i]);
                //    }
                //}
                //tinymce.remove('#field_' + id);
            }
            jax('#field_' + id).val(content);
            jax('#field_' + id).show();
        } else if (val == 'ckeditor') {
            phire.loadEditor('ckeditor', id);
        } else if (val == 'tinymce') {
            phire.loadEditor('tinymce', id);
        }
    },
    toggleEditor : function(sel) {
        if (jax(sel).val().indexOf('textarea') != -1) {
            jax('#editor').show();
        } else {
            jax('#editor').hide();
        }
    },
    loadEditor : function(editor, id) {
        if (null != id) {
            var w = Math.round(jax('#field_' + id).width());
            var h = Math.round(jax('#field_' + id).height());
            phire.selIds = [{ "id" : id, "width" : w, "height" : h }];
        }

        if (phire.selIds.length > 0) {
            for (var i = 0; i < phire.selIds.length; i++) {
                if (editor == 'ckeditor') {
                    if (CKEDITOR.instances['field_' + phire.selIds[i].id] == undefined) {
                        CKEDITOR.replace(
                            'field_' + phire.selIds[i].id,
                            {
                                width  : phire.selIds[i].width,
                                height : phire.selIds[i].height,
                                filebrowserBrowseUrl      : phire.sysBasePath + '/structure/fields/browser/file?editor=ckeditor',
                                filebrowserImageBrowseUrl : phire.sysBasePath + '/structure/fields/browser/image?editor=ckeditor',
                                filebrowserWindowWidth    : '900',
                                filebrowserWindowHeight   : '700'
                            }
                        );
                    }
                } else if (editor == 'tinymce') {
                    if (tinymce.editors['field_' + phire.selIds[i].id] == undefined) {
                        tinymce.init(
                            {
                                selector              : "textarea#field_" + phire.selIds[i].id,
                                theme                 : "modern",
                                plugins: [
                                    "advlist autolink lists link image hr", "searchreplace wordcount code fullscreen",
                                    "table", "template paste textcolor"
                                ],
                                image_advtab          : true,
                                toolbar1              : "insertfile undo redo | styleselect | forecolor backcolor | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table | link image",
                                width                 : phire.selIds[i].width,
                                height                : phire.selIds[i].height,
                                relative_urls         : false,
                                convert_urls          : 0,
                                remove_script_host    : 0,
                                file_browser_callback : function(field_name, url, type, win) {
                                    tinymce.activeEditor.windowManager.open({
                                        title  : "Asset Browser",
                                        url    : phire.sysBasePath + '/structure/fields/browser/' + type + '?editor=tinymce',
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
                    } else {
                        tinymce.get('field_' + phire.selIds[i].id).show();
                    }
                }
            }
        }
    }
};

/**
 * Document ready function for Phire
 */
jax(document).ready(function(){
    phire.sysBasePath = window.location.href;

    if (phire.sysBasePath.indexOf('/content') != -1) {
        phire.sysBasePath = phire.sysBasePath.substring(0, phire.sysBasePath.indexOf('/content'));
    } else if (phire.sysBasePath.indexOf('/structure') != -1) {
        phire.sysBasePath = phire.sysBasePath.substring(0, phire.sysBasePath.indexOf('/structure'));
    } else if (phire.sysBasePath.indexOf('/extensions') != -1) {
        phire.sysBasePath = phire.sysBasePath.substring(0, phire.sysBasePath.indexOf('/extensions'));
    } else if (phire.sysBasePath.indexOf('/users') != -1) {
        phire.sysBasePath = phire.sysBasePath.substring(0, phire.sysBasePath.indexOf('/users'));
    } else if (phire.sysBasePath.indexOf('/config') != -1) {
        phire.sysBasePath = phire.sysBasePath.substring(0, phire.sysBasePath.indexOf('/config'));
    }

    if (phire.sysBasePath.substring(0, -1) == '/') {
        phire.sysBasePath = phire.sysBasePath.substring(0, (phire.sysBasePath.length - 1));
    }

    phire.basePath = phire.sysBasePath.substring(0, phire.sysBasePath.lastIndexOf('/'));

    if (typeof _exp != 'undefined') {
        phire.timeout = setInterval(function() {
            if (jax('#logout-warning-back')[0] == undefined) {
                var url = decodeURIComponent(_base);
                jax('body').append('div', {id : 'logout-warning-back'});
                jax('body').append('div', {id : 'logout-warning'}, '<h3 style="margin: 30px 0 10px 0; font-size: bold;">Your session is about to end.</h3><h4 id="countdown">30</h4><a href="#" id="continue">Continue</a> <a href="' + url + '/logout" id="logout">Logout</a>');
                jax('#logout-warning-back').css({
                    "opacity" : 80,
                    "width"   : jax().width() + 'px',
                    "height"  : jax().getScrollHeight() + 'px',
                    "display" : 'block'
                });
                jax('#logout-warning').css({
                    "left" : Math.round((jax().width() / 2) - 170) + 'px'
                });

                var resizeLogout = function() {
                    jax('#logout-warning-back').css({
                        "width"   : jax().width() + 'px',
                        "height"  : jax().getScrollHeight() + 'px'
                    });
                    jax('#logout-warning').css({
                        "left" : Math.round((jax().width() / 2) - 170) + 'px'
                    });
                };

                jax().on('resize', resizeLogout);

                var countDown = setInterval(function(){
                    var sec = parseInt(jax('#countdown').val());
                    if (sec > 0) {
                        var newSec = sec - 1;
                        jax('#countdown').val(newSec);
                    } else {
                        window.location = url;
                    }
                }, 1000);

                jax('#continue').click(function(){
                    clearInterval(countDown);
                    jax().off('resize', resizeLogout);
                    jax('#logout-warning-back').remove();
                    jax('#logout-warning').remove();
                    jax.ajax(url + '/session');
                    return false;
                });
            }
        }, _exp * 1000);
    }

    // Check saved timestamp to determine if the saved div should display
    if (jax.query('saved') != undefined) {
        var ts = Math.round(new Date().getTime() / 1000);
        var diff = Math.abs(jax.query('saved') - ts);
        if (diff < 40) {
            if (jax('#result')[0] != undefined) {
                jax('#result').css({
                    "background-color" : '#dbf2bf',
                    "color"            : '#315900',
                    "opacity"          : 0
                });
                jax('#result').val('Saved!');
                jax('#result').fade(100, {tween : 10, speed: 200});
                phire.clear = setTimeout(phire.clearStatus, 3000);
            }
        }
    }

    if (jax('#errors')[0] != undefined) {
        jax('#errors').css('opacity', 100);
    }

    // For login form
    if (jax('#login-form')[0] != undefined) {
        var loginForm = jax('#login-form').form({
            "username" : {
                "required" : true
            },
            "password" : {
                "required" : true
            }
        });

        loginForm.setErrorDisplay(phire.errorDisplay);
        loginForm.submit(function(){
            return loginForm.validate();
        });
    }

    // For content form
    if (jax('#content-form')[0] != undefined) {
        if (jax('#uri').attrib('type') == 'text') {
            phire.contentForm = jax('#content-form').form({
                "content_title" : {
                    "required" : true
                }
            });
        } else if (jax('#current-file')[0] == undefined) {
            phire.contentForm = jax('#content-form').form({
                "uri" : {
                    "required" : 'The file field is required.'
                }
            });
        }

        phire.contentForm.setErrorDisplay(phire.errorDisplay);
        phire.contentForm.submit(function(){
            phire.submitted = true;
            return phire.contentForm.validate();
        });

        if (jax('#uri')[0] != undefined) {
            var val = '';
            if (jax('#parent_id')[0] != undefined) {
                var parent = jax('#parent_id').val();
                if (parent != phire.contentParentId) {
                    phire.contentParentId = parent;
                    var j = jax.json.parse('../json/' + parent);
                    phire.contentParentUri = j.uri;
                    val = phire.contentParentUri + jax('#uri').val();
                } else {
                    val = jax('#uri').val();
                }
            }
            if (jax('#uri')[0].type != 'file') {
                if ((val != '') && (val.substring(0, 1) != '/')) {
                    val = '/' + val;
                } else if (val == '') {
                    val = '/';
                }
                jax(jax('#uri').parent()).append('span', {"id" : 'uri-span'}, ((val.substring(0, 2) == '//') ? val.substring(1) : val));
            }

            // Check preview timestamp to determine if a preview window should be opened
            if (jax.query('preview') != undefined) {
                var ts = Math.round(new Date().getTime() / 1000);
                var diff = Math.abs(jax.query('preview') - ts);
                if (diff < 40) {
                    if (jax('#uri-span')[0] != undefined) {
                        window.open(decodeURIComponent(jax.query('base_path')) + jax('#uri-span').val());
                    }
                }
            }
        }

        phire.curForm = '#content-form';
        jax.beforeunload(phire.checkFormChange);
    }

    // For content type form
    if (jax('#content-type-form')[0] != undefined) {
        var contentTypeForm = jax('#content-type-form').form({
            "name" : {
                "required" : true
            }
        });

        contentTypeForm.setErrorDisplay(phire.errorDisplay);
        contentTypeForm.submit(function(){
            return contentTypeForm.validate();
        });
    }

    // For field form
    if (jax('#field-form')[0] != undefined) {
        var fieldForm = jax('#field-form').form({
            "name" : {
                "required" : true
            }
        });

        fieldForm.setErrorDisplay(phire.errorDisplay);
        fieldForm.submit(function(){
            return fieldForm.validate();
        });
    }

    // For field group form
    if (jax('#field-group-form')[0] != undefined) {
        var fieldGroupForm = jax('#field-group-form').form({
            "name" : {
                "required" : true
            }
        });

        fieldGroupForm.setErrorDisplay(phire.errorDisplay);
        fieldGroupForm.submit(function(){
            return fieldGroupForm.validate();
        });
    }

    // For navigation form
    if (jax('#navigation-form')[0] != undefined) {
        var navigationForm = jax('#navigation-form').form({
            "navigation" : {
                "required" : true
            }
        });

        navigationForm.setErrorDisplay(phire.errorDisplay);
        navigationForm.submit(function(){
            return navigationForm.validate();
        });
    }

    // For template form
    if (jax('#template-form')[0] != undefined) {
        var templateForm = jax('#template-form').form({
            "name" : {
                "required" : true
            },
            "template" : {
                "required" : true
            }
        });

        templateForm.setErrorDisplay(phire.errorDisplay);
        templateForm.submit(function(){
            return templateForm.validate();
        });
    }

    // For category form
    if (jax('#category-form')[0] != undefined) {
        phire.categoryForm = jax('#category-form').form({
            "category_title" : {
                "required" : 'The title field is required.'
            }
        });

        phire.categoryForm.setErrorDisplay(phire.errorDisplay);
        phire.categoryForm.submit(function(){
            return phire.categoryForm.validate();
        });

        if (jax('#slug')[0] != undefined) {
            var val = '';
            if (jax('#parent_id')[0] != undefined) {
                var parent = jax('#parent_id').val();
                if (parent != phire.categoryParentId) {
                    phire.categoryParentId = parent;
                    var jsonLoc = (window.location.href.indexOf('edit') != -1) ? '../json/' : './json/';
                    var j = jax.json.parse(jsonLoc + parent);
                    phire.categoryParentUri = j.uri;
                    val = phire.categoryParentUri + jax('#slug').val();
                } else {
                    val = jax('#slug').val();
                }
            }
            if ((val != '') && (val.substring(0, 1) != '/')) {
                val = '/' + val;
            } else if (val == '') {
                val = '/';
            }
            jax(jax('#slug').parent()).append('span', {"id" : 'slug-span'}, ((val.substring(0, 2) == '//') ? val.substring(1) : val));
        }
    }

    // For user role form
    if (jax('#user-role-form')[0] != undefined) {
        var userRoleForm = jax('#user-role-form').form({
            "name" : {
                "required" : true
            }
        });

        userRoleForm.setErrorDisplay(phire.errorDisplay);
        userRoleForm.submit(function(){
            return userRoleForm.validate();
        });
    }

    // For user type form
    if (jax('#user-type-form')[0] != undefined) {
        var userTypeForm = jax('#user-type-form').form({
            "type" : {
                "required" : true
            }
        });

        userTypeForm.setErrorDisplay(phire.errorDisplay);
        userTypeForm.submit(function(){
            return userTypeForm.validate();
        });
    }

    if (jax('#model_1')[0] != undefined) {
        while (jax('#model_' + phire.modelCount)[0] != undefined) {
            phire.modelCount++;
        }
        phire.modelCount--;
    }
    if (jax('#field-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#field-remove-form').checkAll(this.value);
            } else {
                jax('#field-remove-form').uncheckAll(this.value);
            }
        });
    }

    if (jax('#field-group-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#field-group-remove-form').checkAll(this.value);
            } else {
                jax('#field-group-remove-form').uncheckAll(this.value);
            }
        });
    }

    var sels = jax('select').toArray();
    var selVal = null;
    if ((sels != '') && (sels.length > 0)) {
        for (var i = 0; i < sels.length; i++) {
            if (sels[i].id.indexOf('editor_') != -1) {
                var id = sels[i].id.substring(sels[i].id.indexOf('_') + 1);
                var w = Math.round(jax('#field_' + id).width());
                var h = Math.round(jax('#field_' + id).height());
                phire.selIds.push({ "id" : id, "width" : w, "height" : h });
                selVal = jax(sels[i]).val();
            }
        }

        if (null != selVal) {
            var head = document.getElementsByTagName('head')[0];
            var script = document.createElement("script");
            switch (selVal) {
                case 'ckeditor':
                    script.src = jax.root + 'ckeditor/ckeditor.js';
                    script.onload = script.onreadystatechange = function() {
                        if (typeof CKEDITOR != 'undefined') {
                            phire.loadEditor('ckeditor');
                        }
                    }
                    head.appendChild(script);
                    break;

                case 'tinymce':
                    script.src = jax.root + 'tinymce/tinymce.min.js';
                    script.onload = script.onreadystatechange = function() {
                        if (typeof tinymce != 'undefined') {
                            phire.loadEditor('tinymce');
                        }
                    }
                    head.appendChild(script);
                    break;
            }
        }
    }

    if (jax('#content-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#content-remove-form').checkAll(this.value);
            } else {
                jax('#content-remove-form').uncheckAll(this.value);
            }
        });
    }
    if (jax('#navigation-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#navigation-remove-form').checkAll(this.value);
            } else {
                jax('#navigation-remove-form').uncheckAll(this.value);
            }
        });
    }
    if (jax('#category-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#category-remove-form').checkAll(this.value);
            } else {
                jax('#category-remove-form').uncheckAll(this.value);
            }
        });
    }
    if (jax('#content-type-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#content-type-remove-form').checkAll(this.value);
            } else {
                jax('#content-type-remove-form').uncheckAll(this.value);
            }
        });
    }
    if (jax('#template-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#template-remove-form').checkAll(this.value);
            } else {
                jax('#template-remove-form').uncheckAll(this.value);
            }
        });
    }
    if (jax('#themes-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#themes-remove-form').checkAll(this.value);
            } else {
                jax('#themes-remove-form').uncheckAll(this.value);
            }
        });
    }
    if (jax('#modules-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#modules-remove-form').checkAll(this.value);
            } else {
                jax('#modules-remove-form').uncheckAll(this.value);
            }
        });
    }
    if (jax('#user-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#user-remove-form').checkAll(this.value);
            } else {
                jax('#user-remove-form').uncheckAll(this.value);
            }
        });
    }
    if (jax('#role-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#role-remove-form').checkAll(this.value);
            } else {
                jax('#role-remove-form').uncheckAll(this.value);
            }
        });
    }
    if (jax('#session-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#session-remove-form').checkAll(this.value);
            } else {
                jax('#session-remove-form').uncheckAll(this.value);
            }
        });
    }
    if (jax('#type-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#type-remove-form').checkAll(this.value);
            } else {
                jax('#type-remove-form').uncheckAll(this.value);
            }
        });
    }
    if (jax('#sites-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#sites-remove-form').checkAll(this.value);
            } else {
                jax('#sites-remove-form').uncheckAll(this.value);
            }
        });
    }
    if (jax('#site-migration-form')[0] != undefined) {
        jax(jax('#site_from').parent()).attrib('class', 'blue-arrow');
    }
});
