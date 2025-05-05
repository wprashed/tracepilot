<?php
class WPAL_Tracker {
    public static function init() {
        // WordPress core actions
        add_action('wp_login', [__CLASS__, 'track_login'], 10, 2);
        add_action('wp_login_failed', [__CLASS__, 'track_login_failed']);
        add_action('wp_logout', [__CLASS__, 'track_logout']);
        add_action('delete_post', [__CLASS__, 'track_post_delete']);
        add_action('save_post', [__CLASS__, 'track_post_save'], 10, 3);
        add_action('user_register', [__CLASS__, 'track_user_register']);
        add_action('profile_update', [__CLASS__, 'track_profile_update']);
        add_action('deleted_user', [__CLASS__, 'track_user_delete']);
        add_action('activated_plugin', [__CLASS__, 'track_plugin_activated']);
        add_action('deactivated_plugin', [__CLASS__, 'track_plugin_deactivated']);
        add_action('switch_theme', [__CLASS__, 'track_theme_switched']);
        add_action('upgrader_process_complete', [__CLASS__, 'track_update'], 10, 2);
        
        // Comment actions
        add_action('wp_insert_comment', [__CLASS__, 'track_comment_insert'], 10, 2);
        add_action('trash_comment', [__CLASS__, 'track_comment_trash']);
        add_action('spam_comment', [__CLASS__, 'track_comment_spam']);
        
        // WooCommerce actions (if available)
        if (class_exists('WooCommerce')) {
            add_action('woocommerce_order_status_changed', [__CLASS__, 'track_order_status'], 10, 4);
            add_action('woocommerce_new_product', [__CLASS__, 'track_product_created']);
            add_action('woocommerce_update_product', [__CLASS__, 'track_product_updated']);
            add_action('woocommerce_delete_product', [__CLASS__, 'track_product_deleted']);
            add_action('woocommerce_new_order', [__CLASS__, 'track_order_created']);
            add_action('woocommerce_payment_complete', [__CLASS__, 'track_payment_complete']);
            add_action('woocommerce_checkout_order_processed', [__CLASS__, 'track_checkout_processed']);
            add_action('woocommerce_coupon_options_save', [__CLASS__, 'track_coupon_saved']);
        }
        
        // Track API requests
        add_action('rest_api_init', function() {
            add_filter('rest_pre_dispatch', [__CLASS__, 'track_api_request'], 10, 3);
        });
        
        // Track 404 errors
        add_action('template_redirect', function() {
            if (is_404()) {
                self::track_404_error();
            }
        });
        
        // Track session duration
        if (!session_id()) {
            session_start();
        }
    }

    public static function track_login($user_login, $user) {
        $_SESSION['wpal_login_time'] = current_time('timestamp');
        
        $user_roles = $user->roles;
        $user_role = !empty($user_roles) ? implode(', ', $user_roles) : 'none';
        
        self::log("Logged in", $user->ID, 'info', [
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        ]);
    }
    
    public static function track_login_failed($username) {
        self::log("Login failed", 0, 'warning', [
            'attempted_username' => $username,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        ]);
    }

    public static function track_logout() {
        $user_id = get_current_user_id();
        
        if ($user_id && isset($_SESSION['wpal_login_time'])) {
            $duration = time() - $_SESSION['wpal_login_time'];
            $hours = floor($duration / 3600);
            $minutes = floor(($duration % 3600) / 60);
            $seconds = $duration % 60;
            
            $duration_formatted = '';
            if ($hours > 0) {
                $duration_formatted .= "$hours hours ";
            }
            if ($minutes > 0) {
                $duration_formatted .= "$minutes minutes ";
            }
            $duration_formatted .= "$seconds seconds";
            
            self::log("Logged out after $duration_formatted", $user_id);
        } else {
            self::log("Logged out", $user_id);
        }
    }

    public static function track_post_save($post_ID, $post, $update) {
        // Skip revisions and auto-drafts
        if (wp_is_post_revision($post_ID) || $post->post_status === 'auto-draft') {
            return;
        }
        
        $action = $update ? 'Updated' : 'Created';
        $post_type = get_post_type_object($post->post_type);
        $post_type_label = $post_type ? $post_type->labels->singular_name : $post->post_type;
        
        self::log("$action $post_type_label: {$post->post_title}", get_current_user_id(), 'info', [
            'post_id' => $post_ID,
            'post_type' => $post->post_type,
            'post_status' => $post->post_status,
        ]);
    }

    public static function track_post_delete($post_ID) {
        $post = get_post($post_ID);
        if (!$post) {
            return;
        }
        
        $post_type = get_post_type_object($post->post_type);
        $post_type_label = $post_type ? $post_type->labels->singular_name : $post->post_type;
        
        self::log("Deleted $post_type_label: {$post->post_title}", get_current_user_id(), 'warning', [
            'post_id' => $post_ID,
            'post_type' => $post->post_type,
        ]);
    }
    
    public static function track_user_register($user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            return;
        }
        
        self::log("New user registered: {$user->user_login}", get_current_user_id(), 'info', [
            'user_id' => $user_id,
            'user_email' => $user->user_email,
        ]);
    }
    
    public static function track_profile_update($user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            return;
        }
        
        self::log("User profile updated: {$user->user_login}", get_current_user_id(), 'info', [
            'user_id' => $user_id,
        ]);
    }
    
    public static function track_user_delete($user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            return;
        }
        
        self::log("User deleted: {$user->user_login}", get_current_user_id(), 'warning', [
            'user_id' => $user_id,
            'user_email' => $user->user_email,
        ]);
    }
    
    public static function track_plugin_activated($plugin) {
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
        $plugin_name = $plugin_data['Name'] ?? basename($plugin, '.php');
        
        self::log("Plugin activated: $plugin_name", get_current_user_id(), 'info', [
            'plugin' => $plugin,
            'version' => $plugin_data['Version'] ?? 'unknown',
        ]);
    }
    
    public static function track_plugin_deactivated($plugin) {
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
        $plugin_name = $plugin_data['Name'] ?? basename($plugin, '.php');
        
        self::log("Plugin deactivated: $plugin_name", get_current_user_id(), 'info', [
            'plugin' => $plugin,
            'version' => $plugin_data['Version'] ?? 'unknown',
        ]);
    }
    
    public static function track_theme_switched($new_theme) {
        $theme = wp_get_theme($new_theme);
        
        self::log("Theme switched to: {$theme->get('Name')}", get_current_user_id(), 'info', [
            'theme' => $new_theme,
            'version' => $theme->get('Version'),
        ]);
    }
    
    public static function track_update($upgrader, $options) {
        if (!isset($options['action']) || $options['action'] !== 'update') {
            return;
        }
        
        $type = $options['type'] ?? '';
        $current_user_id = get_current_user_id();
        
        switch ($type) {
            case 'plugin':
                if (isset($options['plugins']) && is_array($options['plugins'])) {
                    foreach ($options['plugins'] as $plugin) {
                        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
                        $plugin_name = $plugin_data['Name'] ?? basename($plugin, '.php');
                        
                        self::log("Plugin updated: $plugin_name", $current_user_id, 'info', [
                            'plugin' => $plugin,
                            'version' => $plugin_data['Version'] ?? 'unknown',
                        ]);
                    }
                }
                break;
                
            case 'theme':
                if (isset($options['themes']) && is_array($options['themes'])) {
                    foreach ($options['themes'] as $theme) {
                        $theme_data = wp_get_theme($theme);
                        
                        self::log("Theme updated: {$theme_data->get('Name')}", $current_user_id, 'info', [
                            'theme' => $theme,
                            'version' => $theme_data->get('Version'),
                        ]);
                    }
                }
                break;
                
            case 'core':
                global $wp_version;
                self::log("WordPress core updated to version $wp_version", $current_user_id, 'info');
                break;
        }
    }
    
    public static function track_comment_insert($comment_id, $comment) {
        $post = get_post($comment->comment_post_ID);
        if (!$post) {
            return;
        }
        
        $action = $comment->comment_approved === '1' ? 'Comment added' : 'Comment pending approval';
        
        self::log("$action on: {$post->post_title}", $comment->user_id, 'info', [
            'comment_id' => $comment_id,
            'post_id' => $comment->comment_post_ID,
            'comment_author' => $comment->comment_author,
        ]);
    }
    
    public static function track_comment_trash($comment_id) {
        $comment = get_comment($comment_id);
        if (!$comment) {
            return;
        }
        
        $post = get_post($comment->comment_post_ID);
        if (!$post) {
            return;
        }
        
        self::log("Comment trashed on: {$post->post_title}", get_current_user_id(), 'info', [
            'comment_id' => $comment_id,
            'post_id' => $comment->comment_post_ID,
            'comment_author' => $comment->comment_author,
        ]);
    }
    
    public static function track_comment_spam($comment_id) {
        $comment = get_comment($comment_id);
        if (!$comment) {
            return;
        }
        
        $post = get_post($comment->comment_post_ID);
        if (!$post) {
            return;
        }
        
        self::log("Comment marked as spam on: {$post->post_title}", get_current_user_id(), 'warning', [
            'comment_id' => $comment_id,
            'post_id' => $comment->comment_post_ID,
            'comment_author' => $comment->comment_author,
        ]);
    }
    
    public static function track_order_status($order_id, $from_status, $to_status, $order) {
        self::log("Order #$order_id status changed from $from_status to $to_status", get_current_user_id(), 'info', [
            'order_id' => $order_id,
            'from_status' => $from_status,
            'to_status' => $to_status,
            'order_total' => $order->get_total(),
        ]);
    }
    
    public static function track_product_created($product_id) {
        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }
        
        self::log("Product created: {$product->get_name()}", get_current_user_id(), 'info', [
            'product_id' => $product_id,
            'product_type' => $product->get_type(),
            'product_price' => $product->get_price(),
        ]);
    }
    
    public static function track_product_updated($product_id) {
        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }
        
        self::log("Product updated: {$product->get_name()}", get_current_user_id(), 'info', [
            'product_id' => $product_id,
            'product_type' => $product->get_type(),
            'product_price' => $product->get_price(),
        ]);
    }
    
    public static function track_product_deleted($product_id) {
        self::log("Product deleted (ID: $product_id)", get_current_user_id(), 'warning', [
            'product_id' => $product_id,
        ]);
    }
    
    public static function track_order_created($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        self::log("Order created #$order_id", $order->get_customer_id(), 'info', [
            'order_id' => $order_id,
            'order_total' => $order->get_total(),
            'payment_method' => $order->get_payment_method_title(),
        ]);
    }
    
    public static function track_payment_complete($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        self::log("Payment completed for order #$order_id", $order->get_customer_id(), 'info', [
            'order_id' => $order_id,
            'order_total' => $order->get_total(),
            'payment_method' => $order->get_payment_method_title(),
        ]);
    }
    
    public static function track_checkout_processed($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        self::log("Checkout processed for order #$order_id", $order->get_customer_id(), 'info', [
            'order_id' => $order_id,
            'order_total' => $order->get_total(),
            'items_count' => count($order->get_items()),
        ]);
    }
    
    public static function track_coupon_saved($coupon_id) {
        $coupon = new WC_Coupon($coupon_id);
        
        self::log("Coupon saved: {$coupon->get_code()}", get_current_user_id(), 'info', [
            'coupon_id' => $coupon_id,
            'discount_type' => $coupon->get_discount_type(),
            'amount' => $coupon->get_amount(),
        ]);
    }
    
    public static function track_api_request($result, $server, $request) {
        $route = $request->get_route();
        $method = $request->get_method();
        $user_id = get_current_user_id();
        
        self::log("API Request: $method $route", $user_id, 'info', [
            'route' => $route,
            'method' => $method,
            'params' => $request->get_params(),
        ]);
        
        return $result;
    }
    
    public static function track_404_error() {
        $current_url = home_url(add_query_arg(null, null));
        $referrer = wp_get_referer() ?: 'direct access';
        
        self::log("404 Error: Page not found", get_current_user_id(), 'warning', [
            'url' => $current_url,
            'referrer' => $referrer,
        ]);
    }

    public static function log($message, $user_id = null, $severity = 'info', $context = []) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        $user = $user_id ? get_userdata($user_id) : null;
        $username = $user ? $user->user_login : 'Guest';
        $user_role = $user ? implode(', ', $user->roles) : 'none';
        
        $entry = [
            'time' => current_time('mysql'),
            'user_id' => $user_id,
            'username' => $username,
            'user_role' => $user_role,
            'action' => $message,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'browser' => WPAL_Helpers::get_browser_name(),
            'severity' => $severity,
            'context' => $context,
        ];
        
        WPAL_Helpers::write_log($entry);
        
        // Check if this event requires notification
        $notification_events = get_option('wpal_notification_events', []);
        
        // Extract event type from message (first word)
        $event_type = strtolower(explode(' ', $message)[0]);
        
        if (in_array($event_type, $notification_events) || $severity === 'error') {
            WPAL_Notifications::send_notification($entry);
        }
    }
}