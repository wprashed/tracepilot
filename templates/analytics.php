<?php
/**
 * Template for the analytics page
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wpal-wrap">
    <div class="wpal-dashboard-header">
        <h1 class="wpal-dashboard-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bar-chart-2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
            <?php _e('Visual Analytics', 'wp-activity-logger-pro'); ?>
        </h1>
    </div>
    
    <div class="wpal-widget">
        <div class="wpal-widget-header">
            <h3 class="wpal-widget-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-filter"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                <?php _e('Analytics Controls', 'wp-activity-logger-pro'); ?>
            </h3>
        </div>
        <div class="wpal-widget-body">
            <form id="wpal-analytics-form" class="wpal-d-flex wpal-flex-wrap">
                <div class="wpal-form-group" style="margin-right: 20px;">
                    <label class="wpal-form-label" for="wpal-chart-type"><?php _e('Chart Type', 'wp-activity-logger-pro'); ?></label>
                    <select id="wpal-chart-type" name="chart_type" class="wpal-form-control">
                        <option value="activity_over_time"><?php _e('Activity Over Time', 'wp-activity-logger-pro'); ?></option>
                        <option value="activity_by_user"><?php _e('Activity by User', 'wp-activity-logger-pro'); ?></option>
                        <option value="activity_by_type"><?php _e('Activity by Type', 'wp-activity-logger-pro'); ?></option>
                        <option value="activity_heatmap"><?php _e('Activity Heatmap', 'wp-activity-logger-pro'); ?></option>
                        <option value="severity_distribution"><?php _e('Severity Distribution', 'wp-activity-logger-pro'); ?></option>
                    </select>
                </div>
                
                <div class="wpal-form-group" style="margin-right: 20px;">
                    <label class="wpal-form-label" for="wpal-date-range"><?php _e('Date Range', 'wp-activity-logger-pro'); ?></label>
                    <select id="wpal-date-range" name="date_range" class="wpal-form-control">
                        <option value="7d"><?php _e('Last 7 Days', 'wp-activity-logger-pro'); ?></option>
                        <option value="30d" selected><?php _e('Last 30 Days', 'wp-activity-logger-pro'); ?></option>
                        <option value="90d"><?php _e('Last 90 Days', 'wp-activity-logger-pro'); ?></option>
                        <option value="1y"><?php _e('Last Year', 'wp-activity-logger-pro'); ?></option>
                        <option value="all"><?php _e('All Time', 'wp-activity-logger-pro'); ?></option>
                    </select>
                </div>
                
                <div class="wpal-form-group" id="wpal-group-by-container" style="margin-right: 20px;">
                    <label class="wpal-form-label" for="wpal-group-by"><?php _e('Group By', 'wp-activity-logger-pro'); ?></label>
                    <select id="wpal-group-by" name="group_by" class="wpal-form-control">
                        <option value="hour"><?php _e('Hour', 'wp-activity-logger-pro'); ?></option>
                        <option value="day" selected><?php _e('Day', 'wp-activity-logger-pro'); ?></option>
                        <option value="week"><?php _e('Week', 'wp-activity-logger-pro'); ?></option>
                        <option value="month"><?php _e('Month', 'wp-activity-logger-pro'); ?></option>
                    </select>
                </div>
                
                <div class="wpal-form-group" style="align-self: flex-end;">
                    <button type="submit" id="wpal-generate-chart" class="wpal-btn wpal-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bar-chart"><line x1="12" y1="20" x2="12" y2="10"></line><line x1="18" y1="20" x2="18" y2="4"></line><line x1="6" y1="20" x2="6" y2="16"></line></svg>
                        <?php _e('Generate Chart', 'wp-activity-logger-pro'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="wpal-chart-loading" style="display: none;" class="wpal-text-center wpal-mt-4">
        <div class="wpal-spinner"></div>
        <p><?php _e('Generating chart...', 'wp-activity-logger-pro'); ?></p>
    </div>
    
    <div id="wpal-chart-container" class="wpal-widget" style="display: none;">
        <div class="wpal-widget-header">
            <h3 class="wpal-widget-title" id="wpal-chart-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bar-chart-2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                <?php _e('Activity Over Time', 'wp-activity-logger-pro'); ?>
            </h3>
        </div>
        <div class="wpal-widget-body">
            <div class="wpal-chart-container">
                <canvas id="wpal-analytics-chart"></canvas>
            </div>
            
            <div id="wpal-chart-legend" class="wpal-mt-4" style="display: none;"></div>
            
            <div id="wpal-chart-insights" class="wpal-alert wpal-alert-info wpal-mt-4" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <div id="wpal-insights-content">
                    <!-- Insights will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let analyticsChart = null;
    
    // Show/hide group by based on chart type
    $('#wpal-chart-type').on('change', function() {
        const chartType = $(this).val();
        
        if (chartType === 'activity_over_time') {
            $('#wpal-group-by-container').show();
        } else {
            $('#wpal-group-by-container').hide();
        }
    });
    
    // Generate chart
    $('#wpal-analytics-form').on('submit', function(e) {
        e.preventDefault();
        
        const chartType = $('#wpal-chart-type').val();
        const dateRange = $('#wpal-date-range').val();
        const groupBy = $('#wpal-group-by').val();
        
        // Update chart title
        let chartTitle = '';
        switch (chartType) {
            case 'activity_over_time':
                chartTitle = '<?php _e('Activity Over Time', 'wp-activity-logger-pro'); ?>';
                break;
            case 'activity_by_user':
                chartTitle = '<?php _e('Activity by User', 'wp-activity-logger-pro'); ?>';
                break;
            case 'activity_by_type':
                chartTitle = '<?php _e('Activity by Type', 'wp-activity-logger-pro'); ?>';
                break;
            case 'activity_heatmap':
                chartTitle = '<?php _e('Activity Heatmap', 'wp-activity-logger-pro'); ?>';
                break;
            case 'severity_distribution':
                chartTitle = '<?php _e('Severity Distribution', 'wp-activity-logger-pro'); ?>';
                break;
        }
        
        $('#wpal-chart-title').html(`
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bar-chart-2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
            ${chartTitle}
        `);
        
        // Show loading
        $('#wpal-chart-loading').show();
        $('#wpal-chart-container').hide();
        
        // Fetch data
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpal_get_analytics_data',
                nonce: '<?php echo wp_create_nonce('wpal_nonce'); ?>',
                chart_type: chartType,
                date_range: dateRange,
                group_by: groupBy
            },
            success: function(response) {
                if (response.success) {
                    // Destroy existing chart
                    if (analyticsChart) {
                        analyticsChart.destroy();
                    }
                    
                    // Create new chart
                    const ctx = document.getElementById('wpal-analytics-chart').getContext('2d');
                    
                    // Configure chart based on type
                    let chartConfig = {};
                    
                    switch (chartType) {
                        case 'activity_over_time':
                            chartConfig = {
                                type: 'line',
                                data: response.data,
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            title: {
                                                display: true,
                                                text: '<?php _e('Activity Count', 'wp-activity-logger-pro'); ?>'
                                            }
                                        },
                                        x: {
                                            title: {
                                                display: true,
                                                text: groupBy.charAt(0).toUpperCase() + groupBy.slice(1)
                                            }
                                        }
                                    },
                                    plugins: {
                                        tooltip: {
                                            mode: 'index',
                                            intersect: false
                                        },
                                        legend: {
                                            display: true,
                                            position: 'top'
                                        }
                                    }
                                }
                            };
                            break;
                            
                        case 'activity_by_user':
                        case 'activity_by_type':
                            chartConfig = {
                                type: 'bar',
                                data: response.data,
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            title: {
                                                display: true,
                                                text: '<?php _e('Activity Count', 'wp-activity-logger-pro'); ?>'
                                            }
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            display: false
                                        }
                                    }
                                }
                            };
                            break;
                            
                        case 'severity_distribution':
                            chartConfig = {
                                type: 'pie',
                                data: response.data,
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'right'
                                        }
                                    }
                                }
                            };
                            break;
                            
                        case 'activity_heatmap':
                            // Special handling for heatmap
                            $('#wpal-chart-legend').show();
                            chartConfig = {
                                type: 'heatmap',
                                data: response.data,
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            type: 'category',
                                            position: 'left',
                                            title: {
                                                display: true,
                                                text: '<?php _e('Day of Week', 'wp-activity-logger-pro'); ?>'
                                            }
                                        },
                                        x: {
                                            type: 'category',
                                            position: 'bottom',
                                            title: {
                                                display: true,
                                                text: '<?php _e('Hour of Day', 'wp-activity-logger-pro'); ?>'
                                            }
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            callbacks: {
                                                title: function(context) {
                                                    const day = context[0].label.split('-')[0];
                                                    const hour = context[0].label.split('-')[1];
                                                    return day + ' at ' + hour + ':00';
                                                },
                                                label: function(context) {
                                                    return context.raw + ' activities';
                                                }
                                            }
                                        }
                                    }
                                }
                            };
                            break;
                    }
                    
                    analyticsChart = new Chart(ctx, chartConfig);
                    
                    // Show insights if available
                    if (response.data.insights) {
                        $('#wpal-insights-content').html(response.data.insights);
                        $('#wpal-chart-insights').show();
                    } else {
                        $('#wpal-chart-insights').hide();
                    }
                    
                    // Show chart container
                    $('#wpal-chart-container').show();
                } else {
                    alert(response.data.message);
                }
                
                // Hide loading
                $('#wpal-chart-loading').hide();
            },
            error: function() {
                alert('<?php _e('An error occurred while generating the chart.', 'wp-activity-logger-pro'); ?>');
                $('#wpal-chart-loading').hide();
            }
        });
    });
    
    // Trigger form submission on page load
    $('#wpal-analytics-form').submit();
});
</script>