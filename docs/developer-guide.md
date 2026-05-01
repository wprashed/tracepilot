# TracePilot - Developer Guide

[Back to README](../readme.md)

## Introduction

This guide is intended for developers who want to integrate with TracePilot, either to log custom events or to extend the plugin's functionality.

## Logging Custom Events

### Basic Usage

To log a custom event, use the `TracePilot_Helpers::log_activity()` method:

```php
// Make sure the helper class is initialized
TracePilot_Helpers::init();

// Log a simple activity
TracePilot_Helpers::log_activity(
    'custom_action_name',    // Action identifier
    'Description of action', // Human-readable description
    'info'                   // Severity: 'info', 'warning', or 'error'
);
```

### Advanced Usage

For more detailed logging, you can include additional context:

```php
// Log activity with context
TracePilot_Helpers::log_activity(
    'product_purchased',                  // Action identifier
    'User purchased Product X',           // Human-readable description
    'info',                               // Severity
    array(
        'object_type' => 'product',
        'object_id'   => 123,
        'object_name' => 'Product X',
        'context'     => array(
            'price'   => 49.99,
            'quantity' => 2,
            'total'   => 99.98
        )
    )
);
```

### Triggering a custom event via action (no extra context)

TracePilot also listens for a lightweight action hook you can call:

```php
do_action('tracepilot_track_custom_event', 'my_action', 'Something happened', 'warning');
```

Note: this hook is intended for a simple “action + description + severity” signal. For structured context data, use `TracePilot_Helpers::log_activity()` directly.

### Available Severity Levels

* `info`: Normal activities, informational only
* `warning`: Activities that might require attention
* `error`: Critical activities that indicate problems
* `critical`: Highest urgency (reserved for severe signals)

### Logging User Actions

When logging actions performed by users other than the current user:

```php
// Log activity for a specific user
TracePilot_Helpers::log_activity(
    'custom_user_action',
    'User performed a custom action',
    'info',
    array(
        'user_id' => 42,
        'username' => 'someuser',
        'user_role' => 'editor',
    )
);
```

## Hooks and Filters

### Actions

#### `tracepilot_after_log_activity`

Fired after an activity has been logged.

```php
add_action('tracepilot_after_log_activity', function($log_id, $action, $description, $severity, $args) {
    // Do something after logging
}, 10, 5);
```

TracePilot also fires a legacy-compatible action: `wpal_after_log_activity`.

## Database Schema

The plugin stores logs in a custom table (default: `{$wpdb->prefix}wpal_logs`) with a schema similar to:

```sql
CREATE TABLE {$wpdb->prefix}wpal_logs (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    time datetime NOT NULL,
    site_id bigint(20) unsigned DEFAULT NULL,
    user_id bigint(20) unsigned DEFAULT NULL,
    username varchar(60) DEFAULT NULL,
    user_role varchar(60) DEFAULT NULL,
    action varchar(255) NOT NULL,
    description text NOT NULL,
    severity varchar(20) NOT NULL DEFAULT 'info',
    ip varchar(45) DEFAULT NULL,
    browser varchar(255) DEFAULT NULL,
    location varchar(255) DEFAULT NULL,
    country varchar(100) DEFAULT NULL,
    country_code varchar(10) DEFAULT NULL,
    object_type varchar(50) DEFAULT NULL,
    object_id bigint(20) unsigned DEFAULT NULL,
    object_name varchar(255) DEFAULT NULL,
    context longtext DEFAULT NULL,
    PRIMARY KEY (id),
    KEY time (time),
    KEY site_id (site_id),
    KEY user_id (user_id),
    KEY action (action),
    KEY severity (severity)
);
```

## Best Practices

### Performance Considerations

* Log only significant events
* Use appropriate severity levels
* Consider indexes for custom queries
* Use `context` for structured data (avoid huge payloads)

### Security Considerations

* Never log sensitive data (passwords, API keys)
* Prefer redaction and avoid storing personal data you don’t need
* Sanitize data before logging
* Use proper capability checks

### Compatibility

* Prefix actions with your plugin/theme slug
* Check for `TracePilot_Helpers` class existence
* Prefer calling `TracePilot_Helpers::log_activity()` directly for best compatibility

## Example Implementations

### WooCommerce Integration

```php
add_action('woocommerce_order_status_completed', 'log_woocommerce_purchase');

function log_woocommerce_purchase($order_id) {
    if (!class_exists('TracePilot_Helpers')) {
        return;
    }

    TracePilot_Helpers::init();

    $order = wc_get_order($order_id);
    $items = $order->get_items();
    $products = array();

    foreach ($items as $item) {
        $products[] = $item->get_name() . ' (x' . $item->get_quantity() . ')';
    }

    TracePilot_Helpers::log_activity(
        'woocommerce_purchase',
        sprintf('Order #%s completed for %s', $order->get_order_number(), $order->get_formatted_billing_full_name()),
        'info',
        array(
            'object_type' => 'order',
            'object_id' => $order_id,
            'object_name' => 'Order #' . $order->get_order_number(),
            'context' => array(
                'total' => $order->get_total(),
                'products' => $products,
                'payment_method' => $order->get_payment_method_title()
            )
        )
    );
}
```

### Custom Post Type Integration

```php
add_action('save_post_my_custom_post', 'log_custom_post_save', 10, 3);

function log_custom_post_save($post_id, $post, $update) {
    if (!class_exists('TracePilot_Helpers') || wp_is_post_revision($post_id)) {
        return;
    }

    TracePilot_Helpers::init();

    $action = $update ? 'updated' : 'created';

    TracePilot_Helpers::log_activity(
        'custom_post_' . $action,
        sprintf('Custom post "%s" was %s', get_the_title($post_id), $action),
        'info',
        array(
            'object_type' => 'post',
            'object_id' => $post_id,
            'object_name' => get_the_title($post_id)
        )
    );
}
```

## Conclusion

TracePilot provides a robust framework for tracking activities in WordPress. Use the provided API and hooks to integrate your plugins and themes for comprehensive logging.
