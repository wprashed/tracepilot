<?php
/**
 * Archive template.
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
TracePilot_Archive::init();
$archive_table = TracePilot_Archive::$archive_table;
$exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $archive_table));
$logs = array();

if ($exists) {
    $logs = $wpdb->get_results("SELECT * FROM $archive_table ORDER BY archived_at DESC LIMIT 200");
}
?>

<div class="wrap tracepilot-wrap">
    <section class="tracepilot-hero tracepilot-hero-compact">
        <div>
            <p class="tracepilot-eyebrow"><?php esc_html_e('Storage', 'tracepilot'); ?></p>
            <h1 class="tracepilot-page-title"><?php esc_html_e('Archive', 'tracepilot'); ?></h1>
            <p class="tracepilot-hero-copy"><?php esc_html_e('Review archived logs, restore important entries, or permanently remove stale records.', 'tracepilot'); ?></p>
        </div>
    </section>

    <section class="tracepilot-panel">
        <div class="tracepilot-panel-head">
            <div>
                <h2><?php esc_html_e('Archived Logs', 'tracepilot'); ?></h2>
                <p><?php echo esc_html(sprintf(_n('%d archived log', '%d archived logs', count($logs), 'tracepilot'), count($logs))); ?></p>
            </div>
        </div>

        <?php if (!$exists || empty($logs)) : ?>
            <p><?php esc_html_e('No archived logs found.', 'tracepilot'); ?></p>
        <?php else : ?>
            <div class="tracepilot-table-wrap">
                <table class="tracepilot-table tracepilot-responsive-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Archived', 'tracepilot'); ?></th>
                            <th><?php esc_html_e('User', 'tracepilot'); ?></th>
                            <th><?php esc_html_e('Action', 'tracepilot'); ?></th>
                            <th><?php esc_html_e('Severity', 'tracepilot'); ?></th>
                            <th><?php esc_html_e('Actions', 'tracepilot'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log) : ?>
                            <tr>
                                <td data-label="<?php esc_attr_e('Archived', 'tracepilot'); ?>"><?php echo esc_html(TracePilot_Helpers::format_datetime($log->archived_at)); ?></td>
                                <td data-label="<?php esc_attr_e('User', 'tracepilot'); ?>"><?php echo esc_html($log->username); ?></td>
                                <td data-label="<?php esc_attr_e('Action', 'tracepilot'); ?>"><?php echo esc_html($log->action); ?></td>
                                <td data-label="<?php esc_attr_e('Severity', 'tracepilot'); ?>"><?php echo TracePilot_Helpers::get_severity_badge($log->severity); ?></td>
                                <td data-label="<?php esc_attr_e('Actions', 'tracepilot'); ?>" class="tracepilot-table-actions">
                                    <button type="button" class="tracepilot-btn tracepilot-btn-secondary tracepilot-restore-log" data-log-id="<?php echo esc_attr($log->id); ?>"><?php esc_html_e('Restore', 'tracepilot'); ?></button>
                                    <button type="button" class="tracepilot-btn tracepilot-btn-danger tracepilot-delete-archived-log" data-log-id="<?php echo esc_attr($log->id); ?>"><?php esc_html_e('Delete', 'tracepilot'); ?></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
