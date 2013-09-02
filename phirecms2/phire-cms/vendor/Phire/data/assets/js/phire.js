/**
 * Phire CMS 2.0 Scripts
 */

var resourceCount = 1;
var actions = [];
var contentForm;
var categoryForm;
var contentParentId = 0;
var contentParentUri = '';
var categoryParentId = 0;
var categoryParentUri = '';
var curErrors = 0;
var clr;

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
                        $fx('#result').fade(100, 10, 20);
                        clr = setTimeout(clearStatus, 3000);
                    }
                }
            } else {
                if ($('#result').obj != null) {
                    $('#result').css('background-color', '#e8d0d0')
                        .css('color', '#f00')
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
                    curErrors++;
                    $($('#' + name).parent()).append('div', [['id', 'error-' + curErrors], ['class', 'error']], j[name]);

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
 * @return Boolean
 */
var updateForm = function(form, ret) {
    if (ret) {
        if ($('#update_value').obj != null) {
            $('#update_value').val(1);
        }
        return true;
    } else {
        var url = $(form).attrib('action') + '?update=1';
        $xmlHttp().post($(form).obj, processForm, url);
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
}

/**
 * Document ready function to load the correct URI string into the URI span
 */
$(document).ready(function(){
    // Check saved timestamp to determine if the saved div should display
    if ($().get('saved') != undefined) {
        var ts = Math.round(new Date().getTime() / 1000);
        var diff = Math.abs($().get('saved') - ts);
        if (diff < 10) {
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

    var left = 0;
    var nav = $('#main-nav-1').children();
    for (var i = 0; i < nav.length; i++) {
        if ((nav[i].nodeType == 1) && (nav[i].nodeName == 'LI')) {
            var navChildren = nav[i].childNodes;
            for (var j = 0; j < navChildren.length; j++) {
                if ((navChildren[j].nodeType == 1) && (navChildren[j].nodeName == 'UL')) {
                    $(navChildren[j]).css('left', left + 'px');
                }
            }
            left += 150;
        }
    }
});
