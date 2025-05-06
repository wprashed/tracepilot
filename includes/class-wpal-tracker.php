<?php
/**
 * Tracker class for WP Activity Logger Pro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WPAL_Tracker {
    /**
     * Initialize the tracker
     */
    public static function init() {
        // Track user login
        add_action('wp_login', [__CLASS__, 'track_login'], 10, 2);
        
        // Track user logout
        add_action('wp_logout', [__CLASS__, 'track_logout']);
        
        // Track failed login
        add_action('wp_login_failed', [__CLASS__, 'track_login_failed']);
        
        // Track password reset
        add_action('after_password_reset', [__CLASS__, 'track_password_reset'], 10, 2);
        
        // Track user registration
        add_action('user_register', [__CLASS__, 'track_user_registration']);
        
        // Track user profile update
        add_action('profile_update', [__CLASS__, 'track_profile_update'], 10, 2);
        
        // Track post creation
        add_action('wp_insert_post', [__CLASS__, 'track_post_creation'], 10, 3);
        
        // Track post update
        add_action('post_updated', [__CLASS__, 'track_post_update'], 10, 3);
        
        // Track post deletion
        add_action('before_delete_post', [__CLASS__, 'track_post_deletion']);
        
        // Track plugin activation
        add_action('activated_plugin', [__CLASS__, 'track_plugin_activation'], 10, 2);
        
        // Track plugin deactivation
        add_action('deactivated_plugin', [__CLASS__, 'track_plugin_deactivation'], 10, 2);
        
        // Track theme switch
        add_action('switch_theme', [__CLASS__, 'track_theme_switch'], 10, 3);
        
        // Track 404 errors
        add_action('template_redirect', [__CLASS__, 'track_404_errors']);
        
        // Track API requests
        add_action('rest_api_init', [__CLASS__, 'track_api_requests']);
    }
    
    /**
     * Track user login
     *
     * @param string $user_login
     * @param WP_User $user
     */
    public static function track_login($user_login, $user) {
        WPAL_Helpers::log_activity(
            'User login',
            'info',
            [
                'user_id' => $user->ID,
                'user_login' => $user_login,
                'user_email' => $user->user_email,
                'ip' => WPAL_Helpers::get_client_ip(),
                'browser' => WPAL_Helpers::get_browser(),
                'os' => WPAL_Helpers::get_os()
            ]
        );
    }
    
    /**
     * Track user logout
     */
    public static function track_logout() {
        $current_user = wp_get_current_user();
        
        if (!$current_user || !$current_user->exists()) {
            return;
        }
        
        WPAL_Helpers::log_activity(
            'User logout',
            'info',
            [
                'user_id' => $current_user->ID,
                'user_login' => $current_user->user_login,
                'user_email' => $current_user->user_email,
                'ip' => WPAL_Helpers::get_client_ip(),
                'browser' => WPAL_Helpers::get_browser(),
                'os' => WPAL_Helpers::get_os()
            ]
        );
    }
    
    /**
     * Track failed login
     *
     * @param string $username
     */
    public static function track_login_failed($username) {
        WPAL_Helpers::log_activity(
            'Failed login attempt',
            'warning',
            [
                'username' => $username,
                'ip' => WPAL_Helpers::get_client_ip(),
                'browser' => WPAL_Helpers::get_browser(),
                'os' => WPAL_Helpers::get_os()
            ]
        );
    }
    
    /**
     * Track password reset
     *
     * @param WP_User $user
     * @param string $new_password
     */
    public static function track_password_reset($user, $new_password) {
        WPAL_Helpers::log_activity(
            'Password reset',
            'info',
            [
                'user_id' => $user->ID,
                'user_login' => $user->user_login,
                'user_email' => $user->user_email,
                'ip' => WPAL_Helpers::get_client_ip(),
                'browser' => WPAL_Helpers::get_browser(),
                'os' => WPAL_Helpers::get_os()
            ]
        );
    }
    
    /**
     * Track user registration
     *
     * @param int $user_id
     */
    public static function track_user_registration($user_id) {
        $user = get_user_by('id', $user_id);
        
        if (!$user) {
            return;
        }
        
        WPAL_Helpers::log_activity(
            'User registered',
            'info',
            [
                'user_id' => $user->ID,
                'user_login' => $user->user_login,
                'user_email' => $user->user_email,
                'ip' => WPAL_Helpers::get_client_ip(),
                'browser' => WPAL_Helpers::get_browser(),
                'os' => WPAL_Helpers::get_os()
            ]
        );
    }
    
    /**
     * Track user profile update
     *
     * @param int $user_id
     * @param WP_User $old_user_data
     */
    public static function track_profile_update($user_id, $old_user_data) {
        $user = get_user_by('id', $user_id);
        
        if (!$user) {
            return;
        }
        
        WPAL_Helpers::log_activity(
            'User profile updated',
            'info',
            [
                'user_id' => $user->ID,
                'user_login' => $user->user_login,
                'user_email' => $user->user_email,
                'old_email' => $old_user_data->user_email,
                'ip' => WPAL_Helpers::get_client_ip(),
                'browser' => WPAL_Helpers::get_browser(),
                'os' => WPAL_Helpers::get_os()
            ]
        );
    }
    
    /**
     * Track post creation
     *
     * @param int $post_id
     * @param WP_Post $post
     * @param bool $update
     */
    public static function track_post_creation($post_id, $post, $update) {
        if ($update || wp_is_post_revision($post_id) || wp_is_post_autosave($post_id) || $post->post_status === 'auto-draft') {
            return;
        }
        
        WPAL_Helpers::log_activity(
            'Post created: ' . $post->post_title,
            'info',
            [
                'post_id' => $post_id,
                'post_title' => $post->post_title,
                'post_type' => $post->post_type,
                'post_status' => $post->post_status,
                'ip' => WPAL_Helpers::get_client_ip(),
                'browser' => WPAL_Helpers::get_browser(),
                'os' => WPAL_Helpers::get_os()
            ]
        );
    }
    
    /**
     * Track post update
     *
     * @param int $post_id
     * @param WP_Post $post_after
     * @param WP_Post $post_before
     */
    public static function track_post_update($post_id, $post_after, $post_before) {
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id) || $post_after->post_status === 'auto-draft') {
            return;
        }
        
        // Check if this is a new post
        if ($post_before->post_status === 'auto-draft' || $post_before->post_status === 'new') {
            return;
        }
        
        WPAL_Helpers::log_activity(
            'Post updated: ' . $post_after->post_title,
            'info',
            [
                'post_id' => $post_id,
                'post_title' => $post_after->post_title,
                'post_type' => $post_after->post_type,
                'post_status' => $post_after->post_status,
                'previous_status' => $post_before->post_status,
                'ip' => WPAL_Helpers::get_client_ip(),
                'browser' => WPAL_Helpers::get_browser(),
                'os' => WPAL_Helpers::get_os()
            ]
        );
    }
    
    /**
     * Track post deletion
     *
     * @param int $post_id
     */
    public static function track_post_deletion($post_id) {
        $post = get_post($post_id);
        
        if (!$post || wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        
        WPAL_Helpers::log_activity(
            'Post deleted: ' . $post->post_title,
            'warning',
            [
                'post_id' => $post_id,
                'post_title' => $post->post_title,
                'post_type' => $post->post_type,
                'post_status' => $post->post_status,
                'ip' => WPAL_Helpers::get_client_ip(),
                'browser' => WPAL_Helpers::get_browser(),
                'os' => WPAL_Helpers::get_os()
            ]
        );
    }
    
    /**
     * Track plugin activation
     *
     * @param string $plugin
     * @param bool $network_wide
     */
    public static function track_plugin_activation($plugin, $network_wide) {
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
        
        WPAL_Helpers::log_activity(
            'Plugin activated: ' . $plugin_data['Name'],
            'info',
            [
                'plugin' => $plugin,
                'plugin_name' => $plugin_data['Name'],
                'plugin_version' => $plugin_data['Version'],
                'network_wide' => $network_wide,
                'ip' => WPAL_Helpers::get_client_ip(),
                'browser' => WPAL_Helpers::get_browser(),
                'os' => WPAL_Helpers::get_os()
            ]
        );
    }
    
    /**
     * Track plugin deactivation
     *
     * @param string $plugin
     * @param bool $network_wide
     */
    public static function track_plugin_deactivation($plugin, $network_wide) {
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
        
        WPAL_Helpers::log_activity(
            'Plugin deactivated: ' . $plugin_data['Name'],
            'info',
            [
                'plugin' => $plugin,
                'plugin_name' => $plugin_data['Name'],
                'plugin_version' => $plugin_data['Version'],
                'network_wide' => $network_wide,
                'ip' => WPAL_Helpers::get_client_ip(),
                'browser' => WPAL_Helpers::get_browser(),
                'os' => WPAL_Helpers::get_os()
            ]
        );
    }
    
    /**
     * Track theme switch
     *
     * @param string $new_name
     * @param WP_Theme $new_theme
     * @param WP_Theme $old_theme
     */
    public static function track_theme_switch($new_name, $new_theme, $old_theme) {
        WPAL_Helpers::log_activity(
            'Theme switched',
            'info',
            [
                'new_theme' => $new_name,
                'new_theme_version' => $new_theme->get('Version'),
                'old_theme' => $old_theme->get('Name'),
                'old_theme_version' => $old_theme->get('Version'),
                'ip' => WPAL_Helpers::get_client_ip(),
                'browser' => WPAL_Helpers::get_browser(),
                'os' => WPAL_Helpers::get_os()
            ]
        );
    }
    
    /**
     * Track 404 errors
     */
    public static function track_404_errors() {
        if (!is_404()) {
            return;
        }
        
        // Check if 404 tracking is enabled
        if (!get_option('wpal_track_404_errors', true)) {
            return;
        }
        
        $current_url = home_url($_SERVER['REQUEST_URI']);
        
        WPAL_Helpers::log_activity(
            '404 Error: Page not found',
            'warning',
            [
                'url' => $current_url,
                'referrer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
                'ip' => WPAL_Helpers::get_client_ip(),
                'browser' => WPAL_Helpers::get_browser(),
                'os' => WPAL_Helpers::get_os()
            ]
        );
    }
    
    /**
     * Track API requests
     */
    public static function track_api_requests() {
        // Check if API tracking is enabled
        if (!get_option('wpal_track_api_requests', false)) {
            return;
        }
        
        // Only track REST API requests
        if (!defined('REST_REQUEST') || !REST_REQUEST) {
            return;
        }
        
        $route = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        
        WPAL_Helpers::log_activity(
            'API Request',
            'info',
            [
                'route' => $route,
                'method' => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '',
                'ip' => WPAL_Helpers::get_client_ip(),
                'browser' => WPAL_Helpers::get_browser(),
                'os' => WPAL_Helpers::get_os()
            ]
        );
    }
}