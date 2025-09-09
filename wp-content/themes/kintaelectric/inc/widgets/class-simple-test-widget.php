<?php
/**
 * Simple Test Widget para verificar que los widgets funcionan
 */

if (!defined('ABSPATH')) {
    exit;
}

class Simple_Test_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'simple_test_widget',
            __('Simple Test Widget', 'kintaelectric'),
            array(
                'description' => __('A simple test widget to verify widget functionality.', 'kintaelectric'),
            )
        );
    }

    public function widget($args, $instance) {
        echo '<aside class="widget simple-test-widget">';
        echo '<h3 class="widget-title">Test Widget</h3>';
        echo '<p>This is a simple test widget. If you can see this, widgets are working correctly.</p>';
        echo '</aside>';
    }

    public function form($instance) {
        echo '<p>This is a simple test widget with no configuration options.</p>';
    }

    public function update($new_instance, $old_instance) {
        return array();
    }
}
