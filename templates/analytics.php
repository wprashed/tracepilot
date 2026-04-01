<?php
/**
 * Analytics template.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wpal-wrap">
    <section class="wpal-hero wpal-hero-compact">
        <div>
            <p class="wpal-eyebrow"><?php esc_html_e('Insights', 'wp-activity-logger-pro'); ?></p>
            <h1 class="wpal-page-title"><?php esc_html_e('Analytics', 'wp-activity-logger-pro'); ?></h1>
            <p class="wpal-hero-copy"><?php esc_html_e('Compare activity volume, user behavior, action mix, and severity distribution over time.', 'wp-activity-logger-pro'); ?></p>
        </div>
    </section>

    <section class="wpal-panel">
        <div class="wpal-panel-head">
            <div>
                <h2><?php esc_html_e('Build a chart', 'wp-activity-logger-pro'); ?></h2>
                <p><?php esc_html_e('Choose a view and generate a fresh dataset from the current logs table.', 'wp-activity-logger-pro'); ?></p>
            </div>
        </div>
        <form id="wpal-analytics-form" class="wpal-filter-grid">
            <label>
                <span><?php esc_html_e('Chart type', 'wp-activity-logger-pro'); ?></span>
                <select id="wpal-chart-type" class="wpal-input">
                    <option value="activity_over_time"><?php esc_html_e('Activity over time', 'wp-activity-logger-pro'); ?></option>
                    <option value="activity_by_user"><?php esc_html_e('Activity by user', 'wp-activity-logger-pro'); ?></option>
                    <option value="activity_by_type"><?php esc_html_e('Activity by type', 'wp-activity-logger-pro'); ?></option>
                    <option value="severity_distribution"><?php esc_html_e('Severity distribution', 'wp-activity-logger-pro'); ?></option>
                </select>
            </label>
            <label>
                <span><?php esc_html_e('Date range', 'wp-activity-logger-pro'); ?></span>
                <select id="wpal-date-range" class="wpal-input">
                    <option value="7d"><?php esc_html_e('Last 7 days', 'wp-activity-logger-pro'); ?></option>
                    <option value="30d" selected><?php esc_html_e('Last 30 days', 'wp-activity-logger-pro'); ?></option>
                    <option value="90d"><?php esc_html_e('Last 90 days', 'wp-activity-logger-pro'); ?></option>
                    <option value="1y"><?php esc_html_e('Last year', 'wp-activity-logger-pro'); ?></option>
                </select>
            </label>
            <label id="wpal-group-by-wrap">
                <span><?php esc_html_e('Group by', 'wp-activity-logger-pro'); ?></span>
                <select id="wpal-group-by" class="wpal-input">
                    <option value="day" selected><?php esc_html_e('Day', 'wp-activity-logger-pro'); ?></option>
                    <option value="week"><?php esc_html_e('Week', 'wp-activity-logger-pro'); ?></option>
                    <option value="month"><?php esc_html_e('Month', 'wp-activity-logger-pro'); ?></option>
                </select>
            </label>
            <div class="wpal-filter-actions">
                <button type="submit" class="wpal-btn wpal-btn-primary"><?php esc_html_e('Generate', 'wp-activity-logger-pro'); ?></button>
            </div>
        </form>
    </section>

    <section class="wpal-panel">
        <div class="wpal-panel-head">
            <div>
                <h2 id="wpal-analytics-title"><?php esc_html_e('Activity over time', 'wp-activity-logger-pro'); ?></h2>
                <p><?php esc_html_e('Charts update live from current plugin data.', 'wp-activity-logger-pro'); ?></p>
            </div>
        </div>
        <div class="wpal-chart-shell">
            <canvas id="wpal-analytics-chart"></canvas>
        </div>
        <div id="wpal-analytics-insights" class="wpal-list" style="margin-top:16px;"></div>
    </section>
</div>

<script>
jQuery(function($) {
    let chart;

    function renderAnalytics(config, title, insights) {
        const canvas = document.getElementById('wpal-analytics-chart');
        if (!canvas || typeof Chart === 'undefined') {
            return;
        }

        if (chart) {
            chart.destroy();
        }

        chart = new Chart(canvas, config);
        $('#wpal-analytics-title').text(title);

        const box = $('#wpal-analytics-insights').empty();
        if (Array.isArray(insights) && insights.length) {
            insights.forEach(function(line) {
                box.append('<div class="wpal-list-row"><div>' + $('<div>').text(line).html() + '</div></div>');
            });
        }
    }

    function loadAnalytics() {
        const chartType = $('#wpal-chart-type').val();
        const dateRange = $('#wpal-date-range').val();
        const groupBy = $('#wpal-group-by').val();

        $.post(ajaxurl, {
            action: 'wpal_get_analytics_data',
            nonce: '<?php echo esc_js(wp_create_nonce('wpal_nonce')); ?>',
            chart_type: chartType,
            date_range: dateRange,
            group_by: groupBy
        }).done(function(response) {
            if (!response.success) {
                return;
            }

            let type = 'bar';
            let options = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: chartType === 'severity_distribution',
                        position: 'bottom'
                    }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            };

            if (chartType === 'activity_over_time') {
                type = 'line';
                options.plugins.legend.display = false;
            } else if (chartType === 'severity_distribution') {
                type = 'doughnut';
                delete options.scales;
            }

            renderAnalytics(
                {
                    type: type,
                    data: response.data,
                    options: options
                },
                $('#wpal-chart-type option:selected').text(),
                response.data.insights || []
            );
        });
    }

    $('#wpal-chart-type').on('change', function() {
        $('#wpal-group-by-wrap').toggle($(this).val() === 'activity_over_time');
    });

    $('#wpal-analytics-form').on('submit', function(event) {
        event.preventDefault();
        loadAnalytics();
    });

    loadAnalytics();
});
</script>
