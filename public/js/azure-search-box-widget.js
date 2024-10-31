(function ($, Bloodhound, Handlebars) {
    'use strict';

    $(function () {

        // submit form on enter
        $('.azure-search-field').keydown(function (event) {
            var keypressed = event.keyCode || event.which;
            if (keypressed == 13) {
                $(this).closest('form').submit();
            }
        });

        var source = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace,
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: azure_search_box_widget_ajax_obj.ajax_url +
                    '?action=suggestions&_ajax_nonce=' + azure_search_box_widget_ajax_obj.nonce +
                    '&search=%QUERY',
                wildcard: '%QUERY' // the part of the url above to replace with the value
            }
        });

        source.initialize();

        $('.azure-search-field').typeahead({
            minLength: 3 // to match what Azure search suggestions does
        }, {
            name: 'azure-search-result',
            displayKey: 'post_title',
            source: source.ttAdapter(),
            limit: 10,
            templates: {
                notFound: '<div class="tt-not-found">' + azure_search_box_widget_ajax_obj.no_match_text + '</div>'
            }
        }).on('typeahead:select', function(obj, datum) {
            window.location = azure_search_box_widget_ajax_obj.home_url + '?p=' + datum.post_id;
        });

    });

})(jQuery, Bloodhound, Handlebars);
