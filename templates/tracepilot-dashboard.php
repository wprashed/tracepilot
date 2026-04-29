<?php
/**
 * Dashboard template.
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
TracePilot_Helpers::init();
$table_name = TracePilot_Helpers::$db_table;

$metrics = TracePilot_Helpers::get_dashboard_metrics();
$series = TracePilot_Helpers::get_activity_series(14);

$top_actions = $wpdb->get_results(
    "SELECT action, COUNT(*) AS total
    FROM $table_name
    GROUP BY action
    ORDER BY total DESC
    LIMIT 6"
);

$recent_logs = TracePilot_Helpers::get_logs(array(), 8);
?>

<div class="wrap tracepilot-wrap">
    <section class="tracepilot-hero">
        <div>
            <p class="tracepilot-eyebrow"><?php esc_html_e('Security, audit, and reporting', 'tracepilot'); ?></p>
            <h1 class="tracepilot-page-title"><?php esc_html_e('Activity Logger Dashboard', 'tracepilot'); ?></h1>
            <p class="tracepilot-hero-copy"><?php esc_html_e('Monitor user activity, review suspicious behavior, and export the events that matter from one clean workspace.', 'tracepilot'); ?></p>
        </div>
        <div class="tracepilot-hero-actions">
            <a class="tracepilot-btn tracepilot-btn-primary" href="<?php echo esc_url(admin_url('admin.php?page=tracepilot-logs')); ?>"><?php esc_html_e('Open Logs', 'tracepilot'); ?></a>
            <a class="tracepilot-btn tracepilot-btn-secondary" href="<?php echo esc_url(admin_url('admin.php?page=tracepilot-analytics')); ?>"><?php esc_html_e('View Analytics', 'tracepilot'); ?></a>
            <a class="tracepilot-btn tracepilot-btn-secondary" href="<?php echo esc_url(admin_url('admin.php?page=tracepilot-export')); ?>"><?php esc_html_e('Export Data', 'tracepilot'); ?></a>
        </div>
    </section>

    <section class="tracepilot-stats-grid">
        <article class="tracepilot-stat-card">
            <span class="tracepilot-stat-label"><?php esc_html_e('Total Logs', 'tracepilot'); ?></span>
            <strong class="tracepilot-stat-value"><?php echo esc_html(number_format_i18n($metrics['total_logs'])); ?></strong>
            <span class="tracepilot-stat-meta"><?php esc_html_e('All recorded events', 'tracepilot'); ?></span>
        </article>
        <article class="tracepilot-stat-card">
            <span class="tracepilot-stat-label"><?php esc_html_e('Today', 'tracepilot'); ?></span>
            <strong class="tracepilot-stat-value"><?php echo esc_html(number_format_i18n($metrics['today_logs'])); ?></strong>
            <span class="tracepilot-stat-meta"><?php esc_html_e('Events in the last 24 hours', 'tracepilot'); ?></span>
        </article>
        <article class="tracepilot-stat-card">
            <span class="tracepilot-stat-label"><?php esc_html_e('Active Users', 'tracepilot'); ?></span>
            <strong class="tracepilot-stat-value"><?php echo esc_html(number_format_i18n($metrics['unique_users'])); ?></strong>
            <span class="tracepilot-stat-meta"><?php esc_html_e('Unique users recorded', 'tracepilot'); ?></span>
        </article>
        <article class="tracepilot-stat-card">
            <span class="tracepilot-stat-label"><?php esc_html_e('Warnings', 'tracepilot'); ?></span>
            <strong class="tracepilot-stat-value"><?php echo esc_html(number_format_i18n($metrics['warnings'])); ?></strong>
            <span class="tracepilot-stat-meta"><?php esc_html_e('Warning and error-level logs', 'tracepilot'); ?></span>
        </article>
        <article class="tracepilot-stat-card">
            <span class="tracepilot-stat-label"><?php esc_html_e('Open Threats', 'tracepilot'); ?></span>
            <strong class="tracepilot-stat-value"><?php echo esc_html(number_format_i18n($metrics['open_threats'])); ?></strong>
            <span class="tracepilot-stat-meta"><?php esc_html_e('Threats still marked new', 'tracepilot'); ?></span>
        </article>
    </section>

    <section class="tracepilot-grid tracepilot-grid-2">
        <article class="tracepilot-panel">
            <div class="tracepilot-panel-head">
                <div>
                    <h2><?php esc_html_e('Activity Trend', 'tracepilot'); ?></h2>
                    <p><?php esc_html_e('Last 14 days of recorded activity.', 'tracepilot'); ?></p>
                </div>
            </div>
            <div class="tracepilot-chart-shell">
                <canvas id="tracepilot-dashboard-trend"></canvas>
            </div>
        </article>

        <article class="tracepilot-panel">
            <div class="tracepilot-panel-head">
                <div>
                    <h2><?php esc_html_e('Quick Links', 'tracepilot'); ?></h2>
                    <p><?php esc_html_e('Jump straight into the workflow you need.', 'tracepilot'); ?></p>
                </div>
            </div>
            <div class="tracepilot-quick-links">
                <a class="tracepilot-quick-link" href="<?php echo esc_url(admin_url('admin.php?page=tracepilot-threat-detection')); ?>">
                    <strong><?php esc_html_e('Threat Detection', 'tracepilot'); ?></strong>
                    <span><?php esc_html_e('Review brute-force, unusual location, and privilege alerts.', 'tracepilot'); ?></span>
                </a>
                <a class="tracepilot-quick-link" href="<?php echo esc_url(admin_url('admin.php?page=tracepilot-settings')); ?>">
                    <strong><?php esc_html_e('Notification Rules', 'tracepilot'); ?></strong>
                    <span><?php esc_html_e('Tune email, webhook, Slack, and Discord routing.', 'tracepilot'); ?></span>
                </a>
                <a class="tracepilot-quick-link" href="<?php echo esc_url(admin_url('admin.php?page=tracepilot-export')); ?>">
                    <strong><?php esc_html_e('Compliance Export', 'tracepilot'); ?></strong>
                    <span><?php esc_html_e('Download filtered logs for audits or troubleshooting.', 'tracepilot'); ?></span>
                </a>
            </div>
        </article>
    </section>

    <section class="tracepilot-grid tracepilot-grid-2">
        <article class="tracepilot-panel">
            <div class="tracepilot-panel-head">
                <div>
                    <h2><?php esc_html_e('Top Actions', 'tracepilot'); ?></h2>
                    <p><?php esc_html_e('Most common activity types right now.', 'tracepilot'); ?></p>
                </div>
            </div>
            <div class="tracepilot-list">
                <?php if (empty($top_actions)) : ?>
                    <p><?php esc_html_e('No actions recorded yet.', 'tracepilot'); ?></p>
                <?php else : ?>
                    <?php foreach ($top_actions as $action) : ?>
                        <div class="tracepilot-list-row">
                            <div>
                                <strong><?php echo esc_html($action->action); ?></strong>
                            </div>
                            <div class="tracepilot-list-value"><?php echo esc_html(number_format_i18n($action->total)); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>

        <article class="tracepilot-panel">
            <div class="tracepilot-panel-head">
                <div>
                    <h2><?php esc_html_e('Recent Activity', 'tracepilot'); ?></h2>
                    <p><?php esc_html_e('Latest events across the site.', 'tracepilot'); ?></p>
                </div>
            </div>
            <div class="tracepilot-list">
                <?php if (empty($recent_logs)) : ?>
                    <p><?php esc_html_e('No logs found yet.', 'tracepilot'); ?></p>
                <?php else : ?>
                    <?php foreach ($recent_logs as $log) : ?>
                        <button type="button" class="tracepilot-list-row tracepilot-list-row-button tracepilot-view-log" data-log-id="<?php echo esc_attr($log->id); ?>">
                            <div>
                                <strong><?php echo esc_html($log->action); ?></strong>
                                <div class="tracepilot-list-subtext"><?php echo esc_html($log->username); ?> • <?php echo esc_html(TracePilot_Helpers::format_datetime($log->time)); ?><?php echo !empty($log->site_label) ? ' • ' . esc_html($log->site_label) : ''; ?></div>
                            </div>
                            <div><?php echo TracePilot_Helpers::get_severity_badge($log->severity); ?></div>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>
    </section>
</div>

<div id="tracepilot-log-details-modal" class="tracepilot-modal">
    <div class="tracepilot-modal-dialog">
        <button type="button" class="tracepilot-modal-close" aria-label="<?php esc_attr_e('Close', 'tracepilot'); ?>">×</button>
        <div class="tracepilot-modal-body"></div>
    </div>
</div>
<div
    id="tracepilot-dashboard-trend-data"
    data-labels="<?php echo esc_attr(wp_json_encode($series['labels'])); ?>"
    data-values="<?php echo esc_attr(wp_json_encode($series['values'])); ?>"
    hidden
></div>
