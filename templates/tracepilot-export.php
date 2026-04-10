<?php
/**
 * Export template.
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
TracePilot_Helpers::init();
$table_name = TracePilot_Helpers::$db_table;
$settings = TracePilot_Helpers::get_settings();
$users = $wpdb->get_col("SELECT DISTINCT username FROM $table_name WHERE username <> '' ORDER BY username ASC");
$actions = $wpdb->get_col("SELECT DISTINCT action FROM $table_name ORDER BY action ASC");
$export_columns = array(
    __('Time', 'tracepilot'),
    __('User', 'tracepilot'),
    __('Role', 'tracepilot'),
    __('Action', 'tracepilot'),
    __('Severity', 'tracepilot'),
    __('IP', 'tracepilot'),
    __('Browser', 'tracepilot'),
    __('Object', 'tracepilot'),
    __('Description', 'tracepilot'),
);
?>

<div class="wrap tracepilot-wrap">
    <section class="tracepilot-hero tracepilot-hero-compact">
        <div>
            <p class="tracepilot-eyebrow"><?php esc_html_e('Compliance & reporting', 'tracepilot'); ?></p>
            <h1 class="tracepilot-page-title"><?php esc_html_e('Export Logs', 'tracepilot'); ?></h1>
            <p class="tracepilot-hero-copy"><?php esc_html_e('Download filtered activity data for audits, troubleshooting, or external analysis.', 'tracepilot'); ?></p>
        </div>
    </section>

    <section class="tracepilot-grid tracepilot-grid-2">
        <article class="tracepilot-panel">
            <div class="tracepilot-panel-head">
                <div>
                    <h2><?php esc_html_e('Export Filters', 'tracepilot'); ?></h2>
                    <p><?php esc_html_e('Choose the slice of activity you want to download.', 'tracepilot'); ?></p>
                </div>
            </div>
            <form method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" class="tracepilot-form-stack tracepilot-export-form">
                <input type="hidden" name="action" value="tracepilot_export_logs">
                <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('tracepilot_nonce')); ?>">
                <label>
                    <span><?php esc_html_e('Format', 'tracepilot'); ?></span>
                    <select class="tracepilot-input" name="format">
                        <?php foreach (array('csv', 'json', 'xml', 'pdf') as $format) : ?>
                            <option value="<?php echo esc_attr($format); ?>" <?php selected($settings['default_export_format'], $format); ?>><?php echo esc_html(strtoupper($format)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span><?php esc_html_e('User', 'tracepilot'); ?></span>
                    <select class="tracepilot-input" name="user">
                        <option value=""><?php esc_html_e('All users', 'tracepilot'); ?></option>
                        <?php foreach ($users as $user) : ?>
                            <option value="<?php echo esc_attr($user); ?>"><?php echo esc_html($user); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span><?php esc_html_e('Action', 'tracepilot'); ?></span>
                    <select class="tracepilot-input" name="action_filter">
                        <option value=""><?php esc_html_e('All actions', 'tracepilot'); ?></option>
                        <?php foreach ($actions as $action) : ?>
                            <option value="<?php echo esc_attr($action); ?>"><?php echo esc_html($action); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span><?php esc_html_e('Severity', 'tracepilot'); ?></span>
                    <select class="tracepilot-input" name="severity">
                        <option value=""><?php esc_html_e('All severities', 'tracepilot'); ?></option>
                        <option value="info"><?php esc_html_e('Info', 'tracepilot'); ?></option>
                        <option value="warning"><?php esc_html_e('Warning', 'tracepilot'); ?></option>
                        <option value="error"><?php esc_html_e('Error', 'tracepilot'); ?></option>
                    </select>
                </label>
                <div class="tracepilot-date-grid">
                    <label>
                        <span><?php esc_html_e('Date from', 'tracepilot'); ?></span>
                        <input class="tracepilot-input tracepilot-datepicker" type="text" name="date_from" placeholder="<?php esc_attr_e('YYYY-MM-DD', 'tracepilot'); ?>">
                    </label>
                    <label>
                        <span><?php esc_html_e('Date to', 'tracepilot'); ?></span>
                        <input class="tracepilot-input tracepilot-datepicker" type="text" name="date_to" placeholder="<?php esc_attr_e('YYYY-MM-DD', 'tracepilot'); ?>">
                    </label>
                </div>
                <button type="submit" class="tracepilot-btn tracepilot-btn-primary"><?php esc_html_e('Download Export', 'tracepilot'); ?></button>
            </form>
        </article>

        <article class="tracepilot-panel">
            <div class="tracepilot-panel-head">
                <div>
                    <h2><?php esc_html_e('Included Columns', 'tracepilot'); ?></h2>
                    <p><?php esc_html_e('Exports include the fields most useful for audit and incident review.', 'tracepilot'); ?></p>
                </div>
            </div>
            <div class="tracepilot-list">
                <?php foreach ($export_columns as $column) : ?>
                    <div class="tracepilot-list-row">
                        <div><strong><?php echo esc_html($column); ?></strong></div>
                        <div class="tracepilot-list-value">✓</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>
</div>
