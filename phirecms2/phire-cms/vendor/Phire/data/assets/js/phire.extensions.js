/**
 * Phire CMS 2.0 Extensions Scripts
 */

/**
 * Document ready function to check user forms
 */
$(document).ready(function(){
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
});