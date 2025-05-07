/**
 * WP Activity Logger Pro Admin JavaScript
 */
(function($) {
    'use strict';

    // Initialize charts
    window.initCharts = function() {
        // Charts are initialized via AJAX callbacks
    };

    $(document).ready(function() {
        // Initialize DataTables
        if ($.fn.dataTable && !$.fn.dataTable.isDataTable('#wpal-logs-table')) {
            $('#wpal-logs-table').DataTable({
                order: [[4, 'desc']], // Sort by time column (index 4) in descending order
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                language: {
                    search: '<span class="screen-reader-text">Search logs:</span> ',
                    searchPlaceholder: 'Search logs...',
                    info: 'Showing _START_ to _END_ of _TOTAL_ logs',
                    lengthMenu: 'Show _MENU_ logs per page'
                }
            });
        }

        // Initialize datepickers
        if ($.fn.datepicker) {
            $('.wpal-datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                maxDate: '0'
            });
        }

        // Load dashboard widgets
        if ($('#wpal-activity-chart-widget').length) {
            $.ajax({
                url: wpal_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpal_get_activity_chart',
                    nonce: wpal_admin_vars.nonce
                },
                success: function(response) {
                    $('#wpal-activity-chart-widget').html(response);
                    initCharts();
                }
            });
        }

        if ($('#wpal-top-users-widget').length) {
            $.ajax({
                url: wpal_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpal_get_top_users',
                    nonce: wpal_admin_vars.nonce
                },
                success: function(response) {
                    $('#wpal-top-users-widget').html(response);
                }
            });
        }

        if ($('#wpal-severity-breakdown-widget').length) {
            $.ajax({
                url: wpal_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpal_get_severity_breakdown',
                    nonce: wpal_admin_vars.nonce
                },
                success: function(response) {
                    $('#wpal-severity-breakdown-widget').html(response);
                    initCharts();
                }
            });
        }

        if ($('#wpal-recent-logs-widget').length) {
            $.ajax({
                url: wpal_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpal_get_recent_logs',
                    nonce: wpal_admin_vars.nonce
                },
                success: function(response) {
                    $('#wpal-recent-logs-widget').html(response);
                }
            });
        }

        // View log details
        $(document).on('click', '.wpal-view-log', function(e) {
            e.preventDefault();
            
            const logId = $(this).data('log-id');
            
            $.ajax({
                url: wpal_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpal_get_log_details',
                    nonce: wpal_admin_vars.nonce,
                    log_id: logId
                },
                success: function(response) {
                    // Set modal content and show
                    $('#wpal-log-details-modal .wpal-modal-body').html(response);
                    $('#wpal-log-details-modal').addClass('wpal-modal-show');
                }
            });
        });
        
        // Close modal on click
        $(document).on('click', '.wpal-modal-close', function() {
            $('#wpal-log-details-modal').removeClass('wpal-modal-show');
        });
        
        // Close modal on outside click
        $(window).on('click', function(e) {
            if ($(e.target).is('#wpal-log-details-modal')) {
                $('#wpal-log-details-modal').removeClass('wpal-modal-show');
            }
        });
        
        // Delete log entry
        $(document).on('click', '.wpal-delete-log', function(e) {
            e.preventDefault();
            
            if (!confirm(wpal_admin_vars.confirm_delete)) {
                return;
            }
            
            const $row = $(this).closest('tr');
            const logId = $(this).data('log-id');
            const nonce = $(this).data('nonce');
            
            $.ajax({
                url: wpal_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpal_delete_log',
                    log_id: logId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(300, function() {
                            // If using DataTables, we need to remove the row properly
                            if ($.fn.dataTable && $.fn.dataTable.isDataTable('#wpal-logs-table')) {
                                const table = $('#wpal-logs-table').DataTable();
                                table.row($row).remove().draw();
                            } else {
                                $row.remove();
                            }
                        });
                    } else {
                        alert(response.data);
                    }
                },
                error: function() {
                    alert('An error occurred while deleting the log.');
                }
            });
        });
        
        // Delete all logs
        $('#wpal-delete-all-logs').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm(wpal_admin_vars.confirm_delete_all)) {
                return;
            }
            
            $.ajax({
                url: wpal_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpal_delete_all_logs',
                    nonce: wpal_admin_vars.delete_nonce
                },
                success: function(response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert(response.data);
                    }
                },
                error: function() {
                    alert('An error occurred while deleting all logs.');
                }
            });
        });

        // Refresh widget
        $('.wpal-refresh-widget').on('click', function() {
            const widget = $(this).data('widget');
            const $widgetBody = $('#wpal-' + widget + '-widget');
            
            $widgetBody.html('<div class="wpal-text-center wpal-mt-4 wpal-mb-4"><div class="spinner is-active"></div><p>Loading...</p></div>');
            
            switch (widget) {
                case 'activity-chart':
                    $.ajax({
                        url: wpal_admin_vars.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'wpal_get_activity_chart',
                            nonce: wpal_admin_vars.nonce
                        },
                        success: function(response) {
                            $widgetBody.html(response);
                            initCharts();
                        }
                    });
                    break;
                case 'top-users':
                    $.ajax({
                        url: wpal_admin_vars.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'wpal_get_top_users',
                            nonce: wpal_admin_vars.nonce
                        },
                        success: function(response) {
                            $widgetBody.html(response);
                        }
                    });
                    break;
                case 'severity-breakdown':
                    $.ajax({
                        url: wpal_admin_vars.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'wpal_get_severity_breakdown',
                            nonce: wpal_admin_vars.nonce
                        },
                        success: function(response) {
                            $widgetBody.html(response);
                            initCharts();
                        }
                    });
                    break;
                case 'recent-logs':
                    $.ajax({
                        url: wpal_admin_vars.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'wpal_get_recent_logs',
                            nonce: wpal_admin_vars.nonce
                        },
                        success: function(response) {
                            $widgetBody.html(response);
                        }
                    });
                    break;
            }
        });

        // Settings tabs
        $('.wpal-settings-tab').on('click', function(e) {
            e.preventDefault();
            
            const target = $(this).data('target');
            
            // Update active tab
            $('.wpal-settings-tab').removeClass('wpal-active');
            $(this).addClass('wpal-active');
            
            // Show target tab content
            $('.wpal-settings-content').removeClass('wpal-active');
            $('#' + target).addClass('wpal-active');
        });
    });
})(jQuery);

// View log details
jQuery(document).on("click", ".wpal-view-log", function (e) {
    e.preventDefault()
  
    const logId = jQuery(this).data("log-id")
  
    jQuery.ajax({
      url: wpal_admin_vars.ajax_url,
      type: "POST",
      data: {
        action: "wpal_get_log_details",
        nonce: wpal_admin_vars.nonce,
        log_id: logId,
      },
      success: (response) => {
        // Set modal content and show
        jQuery("#wpal-log-details-modal .wpal-modal-body").html(response)
        jQuery("#wpal-log-details-modal").addClass("wpal-modal-show")
      },
      error: (xhr, status, error) => {
        console.error("Error loading log details:", error)
        jQuery("#wpal-log-details-modal .wpal-modal-body").html(
          '<p class="wpal-text-center">Error loading log details. Please try again.</p>',
        )
        jQuery("#wpal-log-details-modal").addClass("wpal-modal-show")
      },
    })
  })  