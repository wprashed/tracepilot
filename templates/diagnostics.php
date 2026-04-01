<?php
/**
 * Diagnostics template.
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$wp_version = get_bloginfo('version');
$php_version = phpversion();
$mysql_version = $wpdb->db_version();
$server_software = isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : '';
$memory_limit = ini_get('memory_limit');
$max_execution_time = ini_get('max_execution_time');
$post_max_size = ini_get('post_max_size');
$upload_max_filesize = ini_get('upload_max_filesize');
$max_input_vars = ini_get('max_input_vars');

$plugin_data = get_plugin_data(WPAL_PLUGIN_FILE);
WPAL_Helpers::init();
$table_name = WPAL_Helpers::$db_table;
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
$table_count = $table_exists ? (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name") : 0;
$table_size = $table_exists ? $wpdb->get_var("SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = DATABASE() AND table_name = '$table_name'") : 0;

$issues = array();
if (version_compare($php_version, '7.4', '<')) {
    $issues[] = __('PHP should be upgraded for better compatibility and performance.', 'wp-activity-logger-pro');
}
if (!$table_exists) {
    $issues[] = __('The main logs table is missing. Reactivating the plugin should recreate it.', 'wp-activity-logger-pro');
}
if (wp_convert_hr_to_bytes($memory_limit) < 64 * 1024 * 1024) {
    $issues[] = __('Memory limit is low for analytics-heavy admin pages.', 'wp-activity-logger-pro');
}

if (isset($_POST['wpal_run_diagnostics'])) {
    check_admin_referer('wpal_diagnostics_nonce');
    WPAL_Helpers::log_activity('diagnostics_test', __('Diagnostics test log entry', 'wp-activity-logger-pro'), 'info');
}
?>

<div class="wrap wpal-wrap">
    <section class="wpal-hero wpal-hero-compact">
        <div>
            <p class="wpal-eyebrow"><?php esc_html_e('System checks', 'wp-activity-logger-pro'); ?></p>
            <h1 class="wpal-page-title"><?php esc_html_e('Diagnostics', 'wp-activity-logger-pro'); ?></h1>
            <p class="wpal-hero-copy"><?php esc_html_e('Review the current WordPress environment, validate the plugin’s storage layer, and run a quick write test.', 'wp-activity-logger-pro'); ?></p>
        </div>
        <div class="wpal-hero-actions">
            <form method="post">
                <?php wp_nonce_field('wpal_diagnostics_nonce'); ?>
                <button type="submit" name="wpal_run_diagnostics" class="wpal-btn wpal-btn-primary"><?php esc_html_e('Run Diagnostics', 'wp-activity-logger-pro'); ?></button>
            </form>
        </div>
    </section>

    <?php if (!empty($issues)) : ?>
        <section class="wpal-panel">
            <div class="wpal-panel-head">
                <div>
                    <h2><?php esc_html_e('Attention Needed', 'wp-activity-logger-pro'); ?></h2>
                    <p><?php esc_html_e('A few environment checks deserve a second look.', 'wp-activity-logger-pro'); ?></p>
                </div>
            </div>
            <div class="wpal-list">
                <?php foreach ($issues as $issue) : ?>
                    <div class="wpal-list-row"><div><?php echo esc_html($issue); ?></div></div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="wpal-grid wpal-grid-2">
        <article class="wpal-panel">
            <div class="wpal-panel-head">
                <div>
                    <h2><?php esc_html_e('System Information', 'wp-activity-logger-pro'); ?></h2>
                    <p><?php esc_html_e('Core environment values currently detected by WordPress and PHP.', 'wp-activity-logger-pro'); ?></p>
                </div>
            </div>
            <div class="wpal-table-wrap">
                <table class="wpal-table wpal-kv-table">
                    <tbody>
                        <tr><th><?php esc_html_e('WordPress version', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($wp_version); ?></td></tr>
                        <tr><th><?php esc_html_e('PHP version', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($php_version); ?></td></tr>
                        <tr><th><?php esc_html_e('MySQL version', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($mysql_version); ?></td></tr>
                        <tr><th><?php esc_html_e('Server software', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($server_software); ?></td></tr>
                        <tr><th><?php esc_html_e('Memory limit', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($memory_limit); ?></td></tr>
                        <tr><th><?php esc_html_e('Max execution time', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($max_execution_time); ?>s</td></tr>
                        <tr><th><?php esc_html_e('Post max size', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($post_max_size); ?></td></tr>
                        <tr><th><?php esc_html_e('Upload max filesize', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($upload_max_filesize); ?></td></tr>
                        <tr><th><?php esc_html_e('Max input vars', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($max_input_vars); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </article>

        <article class="wpal-panel">
            <div class="wpal-panel-head">
                <div>
                    <h2><?php esc_html_e('Plugin Information', 'wp-activity-logger-pro'); ?></h2>
                    <p><?php esc_html_e('Useful metadata for support and debugging.', 'wp-activity-logger-pro'); ?></p>
                </div>
            </div>
            <div class="wpal-table-wrap">
                <table class="wpal-table wpal-kv-table">
                    <tbody>
                        <tr><th><?php esc_html_e('Plugin version', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($plugin_data['Version']); ?></td></tr>
                        <tr><th><?php esc_html_e('Author', 'wp-activity-logger-pro'); ?></th><td><?php echo wp_kses_post($plugin_data['Author']); ?></td></tr>
                        <tr><th><?php esc_html_e('Plugin URI', 'wp-activity-logger-pro'); ?></th><td><a href="<?php echo esc_url($plugin_data['PluginURI']); ?>" target="_blank" rel="noreferrer"><?php echo esc_html($plugin_data['PluginURI']); ?></a></td></tr>
                        <tr><th><?php esc_html_e('Database table', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($table_name); ?> <?php echo $table_exists ? esc_html__('(exists)', 'wp-activity-logger-pro') : esc_html__('(missing)', 'wp-activity-logger-pro'); ?></td></tr>
                        <tr><th><?php esc_html_e('Log count', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html(number_format_i18n($table_count)); ?></td></tr>
                        <tr><th><?php esc_html_e('Database size', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($table_size ? size_format($table_size) : 'N/A'); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </article>
    </section>
</div>
