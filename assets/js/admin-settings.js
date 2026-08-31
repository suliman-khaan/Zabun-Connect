(function($) {
    'use strict';

    $(document).ready(function() {
        var $testBtn = $('#zabun-test-connection');
        var $syncBtn = $('#zabun-sync-now');
        var $testFeedback = $('#zabun-connection-status');
        var $syncFeedback = $('#zabun-sync-status');

        // Test Connection Button Handler
        $testBtn.on('click', function(e) {
            e.preventDefault();

            var apiKey    = $('#zabun_connect_api_key').val();
            var clientId  = $('#zabun_connect_client_id').val();
            var serverId  = $('#zabun_connect_server_id').val();
            var xClientId = $('#zabun_connect_x_client_id').val();
            var baseUrl   = $('#zabun_connect_base_url').val();

            $testBtn.prop('disabled', true);
            $testFeedback
                .removeClass('success error')
                .addClass('loading')
                .html('<span class="zabun-spinner"></span> ' + zabunConnectAdmin.i18n.testing)
                .show();

            $.ajax({
                url: zabunConnectAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'zabun_test_connection',
                    nonce: zabunConnectAdmin.nonce,
                    api_key: apiKey,
                    client_id: clientId,
                    server_id: serverId,
                    x_client_id: xClientId,
                    base_url: baseUrl
                },
                success: function(response) {
                    $testBtn.prop('disabled', false);
                    if (response.success) {
                        $testFeedback
                            .removeClass('loading error')
                            .addClass('success')
                            .html(response.data.message || zabunConnectAdmin.i18n.testSuccess);
                    } else {
                        $testFeedback
                            .removeClass('loading success')
                            .addClass('error')
                            .html(response.data.message || zabunConnectAdmin.i18n.testError);
                    }
                },
                error: function(xhr) {
                    $testBtn.prop('disabled', false);
                    var msg = zabunConnectAdmin.i18n.networkError;
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        msg = xhr.responseJSON.data.message;
                    }
                    $testFeedback
                        .removeClass('loading success')
                        .addClass('error')
                        .html(msg);
                }
            });
        });

        // Manual Sync Now Button Handler
        $syncBtn.on('click', function(e) {
            e.preventDefault();

            $syncBtn.prop('disabled', true);
            $syncFeedback
                .removeClass('success error')
                .addClass('loading')
                .html('<span class="zabun-spinner"></span> ' + zabunConnectAdmin.i18n.syncing)
                .show();

            $.ajax({
                url: zabunConnectAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'zabun_manual_sync',
                    nonce: zabunConnectAdmin.nonce
                },
                success: function(response) {
                    $syncBtn.prop('disabled', false);
                    if (response.success) {
                        $syncFeedback
                            .removeClass('loading error')
                            .addClass('success')
                            .html(response.data.message || zabunConnectAdmin.i18n.syncSuccess);
                        
                        // Update status card counters dynamically if available
                        if (response.data.stats) {
                            var cachedCount = response.data.stats.total_cached !== undefined ? response.data.stats.total_cached : response.data.stats.total_fetched;
                            $('#zabun-cached-count').text(cachedCount);
                        }
                        $('#zabun-last-sync').text(zabunConnectAdmin.i18n.justNow);
                    } else {
                        $syncFeedback
                            .removeClass('loading success')
                            .addClass('error')
                            .html(response.data.message || zabunConnectAdmin.i18n.syncError);
                    }
                },
                error: function(xhr) {
                    $syncBtn.prop('disabled', false);
                    var msg = zabunConnectAdmin.i18n.networkError;
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        msg = xhr.responseJSON.data.message;
                    }
                    $syncFeedback
                        .removeClass('loading success')
                        .addClass('error')
                        .html(msg);
                }
            });
        });
    });
})(jQuery);
