(function ($) {
    'use strict';

    $(function () {

        $('#azure_search_init_index').click(function () {
            $('#azure_search_init_index').prop('disabled', true);
            initIndexes();
        });

        var initIndexes = function () {
            $.post(admin_ajax_obj.ajax_url, {
                _ajax_nonce: admin_ajax_obj.nonce,
                action: "init_indexes"
            }, initIndexesSuccess);
        };

        var nextPage = 0,
            progressPostCount = 0,
            totalPostCount = 0;

        var initIndexesSuccess = function (data) {
            var response = $.parseJSON(data);

            // showInitProgress(data);

            nextPage = 1;
            progressPostCount = 0;
            totalPostCount = response.count;

            if (response.response.is_error) {
                showInitProgress(response.response.error_message);
            } else {
                showInitProgress();
                initIndexesContinue();
            }

        };

        var initIndexesContinue = function() {
            $.post(admin_ajax_obj.ajax_url, {
                _ajax_nonce: admin_ajax_obj.nonce,
                action: "update_indexes",
                nextPage: nextPage
            }, initIndexesContinueSuccess);
        };

        var initIndexesContinueSuccess = function(data) {
            var response = $.parseJSON(data);

            // showInitProgress(data);

            nextPage = 1 + response.paged;
            progressPostCount += response.count;

            if (response.response.is_error) {
                showInitProgress(response.response.error_message);
            } else if (response.count == 0) {
                showInitProgress(admin_ajax_obj.finished);
            } else {
                showInitProgress();
                initIndexesContinue();
            }

        };

        var showInitProgress = function(text) {
            var status = $('#azure_search_init_index_status');
            if (text) {
                status.text(text); // TODO show error highlighting
            } else {
                status.text(admin_ajax_obj.progress_prefix + progressPostCount + admin_ajax_obj.progress_infix + totalPostCount);
            }
        }

    });

})(jQuery);
