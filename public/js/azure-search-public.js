(function ($) {
    'use strict';

    $(function () {

        var pageSize = 20; // number of items per page

        var SearchResult = Backbone.Model.extend({
            id: function() { return this.get('ID')},
            name: function() { return this.get('post_name')},
            date: function() { return this.get('post_date_gmt')},
            title: function() { return this.get('post_title')},
            content: function() {
                var content = this.get('post_content_text');
                return content.substring(0, 100);
            }
        });

        var SearchResults = Backbone.Collection.extend({
            model: SearchResult
        });

        /**
         * Show that we're waiting for search results
         */
        var loading = function() {
            var html = $('#azure-search-loading-template').html();
            $('.azure-search-result').html(html);
        };

        /**
         * Send an ajax search request
         */
        var doSearch = function (page) {

            loading();

            $.ajax({
                url: azure_search_results_ajax_obj.search_base_url +
                    '/indexes/' + azure_search_results_ajax_obj.index_name + '/docs',
                accepts: 'application/json',
                data: {
                    "api-version": azure_search_results_ajax_obj.search_api_version,
                    "search": searchText,
                    "$count": "true",
                    "$top": pageSize,
                    "$skip": pageSize * (page - 1)
                },
                headers: {
                    "api-key": azure_search_results_ajax_obj.search_key
                },
                success: searchSuccess,
                error: searchError
            });
        };

        /**
         * Show the search results
         */
        var searchSuccess = function (results) {
            var searchResults = new SearchResults(results.value);

            var data = {
                count: results["@odata.count"],
                models: searchResults.models
            };

            var SearchResultView = Backbone.View.extend({
                el: '.azure-search-result',
                initialize:function(){
                    this.render();
                },
                render: function () {
                    var source = $('#azure-search-result-template').html();
                    var template = Handlebars.compile(source);
                    var html = template(data);
                    this.$el.html(html);
                    showPagination(data.count);
                }
            });

            var searchResultView = new SearchResultView();
        };

        var currentPage = function () {
            // in no hash return 1
            if (!window.location.hash) {
                return 1;
            }
            var hash = parseInt(window.location.hash.substring(1), 10);
            // if it's a positive integer then return it
            if (hash > 0) {
                return hash;
            }
            // else return 1
            return 1;
        };

        var showPagination = function(count) {
            var totalPages = 1 + (count - 1) / pageSize;
            $('.azure-search-navigation').twbsPagination({
                totalPages: totalPages,
                href: '#{{number}}',
                paginationClass: 'navigation-sm', // need this to avoid conflict with 2015 theme
                onPageClick: function (event, page) {
                    window.scrollTo(0,0);
                    doSearch(page);
                }
            });
        };

        var searchError = function (jqXHR, textStatus, errorThrown) {
            // TODO handle the error
        };

        // do search when page is loaded using search term from query string
        var searchText = $.url().param('as');
        var azure_search_result = $('azure-search-result');
        if (searchText && azure_search_result) {
            doSearch(currentPage());
        }

    });

})(jQuery);
