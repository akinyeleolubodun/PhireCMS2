/**
 * Phire CMS 2.0 User Scripts
 */

var resourceCount = 1;
var actions = [];

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

    $('#resource_new_' + resourceCount).val($('#resource_new_' + (resourceCount - 1) + ' > option:selected').val());
    $('#permission_new_' + resourceCount).val($('#permission_new_' + (resourceCount - 1) + ' > option:selected').val());
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
 * Document ready function to check user forms
 */
$(document).ready(function(){
    if ($('#user-remove-form').obj != null) {
        $('#checkall').click(function(){
            if (this.checked) {
                $form('user-remove-form').checkAll(this.value);
            } else {
                $form('user-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#role-remove-form').obj != null) {
        $('#checkall').click(function(){
            if (this.checked) {
                $form('role-remove-form').checkAll(this.value);
            } else {
                $form('role-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#session-remove-form').obj != null) {
        $('#checkall').click(function(){
            if (this.checked) {
                $form('session-remove-form').checkAll(this.value);
            } else {
                $form('session-remove-form').uncheckAll(this.value);
            }
        });
    }
    if ($('#type-remove-form').obj != null) {
        $('#checkall').click(function(){
            if (this.checked) {
                $form('type-remove-form').checkAll(this.value);
            } else {
                $form('type-remove-form').uncheckAll(this.value);
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