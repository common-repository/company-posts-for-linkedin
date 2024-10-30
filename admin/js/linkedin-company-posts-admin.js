(function ($) {
    'use strict';

    /**
     * All the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     *
     * When the window is loaded:
     *
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered the best practice to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    let scString, conClassVal, itemClassVal, limitVal;
    const $conClass = $('#linkedin_company_updates_options_Update-Items-Container-Class');
    const $itemClass = $('#linkedin_company_updates_options_Update-Item-Class');
    const $limit = $('#linkedin_company_updates_options_Limit');
    const $shortcode = $('#linkedin_company_updates_shortcode');
    const $companyID = $('#linkedin_company_updates_options_Company-ID');

    $('#linkedin_company_updates_options_Company-ID, #linkedin_company_updates_options_Update-Items-Container-Class, #linkedin_company_updates_options_Update-Item-Class, #linkedin_company_updates_options_Limit').on('input', function () {
        updateShortcode($companyID.val());
    });

    $('#select-company').on('input', function () {
        updateShortcode($(this).find('option:selected').val());
    });

    function updateShortcode(companyId) {
        conClassVal = $conClass.val();
        itemClassVal = $itemClass.val();
        limitVal = $limit.val();
        scString = '[li-company-updates company="' + companyId + '"';
        scString += conClassVal ? ' con_class="' + conClassVal + '"' : '';
        scString += itemClassVal ? ' item_class="' + itemClassVal + '"' : '';
        scString += limitVal ? ' limit="' + limitVal + '"' : '';
        scString += ']';
        $shortcode.val(scString);

    }

})(jQuery);
