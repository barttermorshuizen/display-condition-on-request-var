<?php
/**
 * Plugin Name: Display Condition On Request Var
 * Description: Adds conditional display capabilities to Elementor based on request variables
 * Version:     1.0.0
 * Author:      More Awesome
 * Author URI:  https://moreawesome.co
 * Text Domain: display-condition-on-request-var
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Main Display Condition On Request Var Class
 */
final class Display_Condition_On_Request_Var {

    /**
     * Plugin Version
     */
    const VERSION = '1.0.0';

    /**
     * Minimum Elementor Version
     */
    const MINIMUM_ELEMENTOR_VERSION = '3.0.0';

    /**
     * Minimum PHP Version
     */
    const MINIMUM_PHP_VERSION = '7.0';

    /**
     * Instance
     */
    private static $_instance = null;

    /**
     * Instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        add_action('plugins_loaded', [$this, 'on_plugins_loaded']);
    }

    /**
     * Load plugin after Elementor (and other plugins) are loaded.
     */
    public function on_plugins_loaded() {
        if ($this->is_compatible()) {
            add_action('elementor/init', [$this, 'init']);
        }
    }

    /**
     * Compatibility Checks
     */
    public function is_compatible() {
        // Check if Elementor installed and activated
        if (!class_exists('Elementor\Plugin')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_main_plugin']);
            return false;
        }

        // Check for required Elementor version
        if (!version_compare(ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return false;
        }

        // Check for required PHP version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
            return false;
        }

        return true;
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        add_action(
            'elementor/element/container/section_layout/before_section_end',
            [$this, 'add_match_value_control'],
            10, 2
        );

        add_action('elementor/frontend/before_render', [$this, 'before_render'], 10);
        add_filter('elementor/frontend/the_content', [$this, 'filter_content']);
    }

    /**
     * Get domain terms as options
     */
    private function get_domain_options() {
        $options = ['' => esc_html__('-- Select Domain --', 'display-condition-on-request-var')];
        
        $terms = get_terms([
            'taxonomy' => 'domein',
            'hide_empty' => false,
        ]);

        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $options[$term->slug] = $term->name;
            }
        }

        return $options;
    }

    /**
     * Add Match Value Control
     */
    public function add_match_value_control($element, $args) {
        $element->add_control(
            'enable_domain_condition',
            [
                'label' => esc_html__('Use Match Domain Condition', 'display-condition-on-request-var'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'display-condition-on-request-var'),
                'label_off' => esc_html__('No', 'display-condition-on-request-var'),
                'return_value' => 'yes',
                'default' => '',
                'separator' => 'before',
                'render_type' => 'none',
                'frontend_available' => true,
            ]
        );

        $element->add_control(
            'match_value',
            [
                'label' => 'Match Value',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $this->get_domain_options(),
                'description' => esc_html__('Show this container only when URL parameter "domein" matches selected domain', 'display-condition-on-request-var'),
                'label_block' => true,
                'condition' => [
                    'enable_domain_condition' => 'yes',
                ],
                'render_type' => 'none',
                'frontend_available' => true,
            ]
        );
    }

    /**
     * Before render
     */
    public function before_render($element) {
        if ('container' !== $element->get_name()) {
            return;
        }

        $settings = $element->get_settings_for_display();
        if (!empty($settings['enable_domain_condition'])) {
            // Get current post's domein term first
            $current_post_id = get_the_ID();
            $domein_terms = wp_get_post_terms($current_post_id, 'domein');
            
            // Check if we have a domein term
            if (!empty($domein_terms) && !is_wp_error($domein_terms)) {
                $request_value = $domein_terms[0]->slug;
            } else {
                // Fall back to request parameter
                $request_value = get_query_var('domein');
            }
            $element->add_render_attribute('_wrapper', 'data-domain-condition', $settings['match_value']);
            $element->add_render_attribute('_wrapper', 'data-domain-current', $request_value);
            
            // Check if we should hide the element
            $should_hide = false;
            
            if ($settings['match_value'] === 'algemeen') {
                // For 'algemeen', show when domein is empty or not set
                $should_hide = !empty($request_value);
            } else {
                // For other values, show only when they match exactly
                $should_hide = ($request_value !== $settings['match_value']);
            }
            
            if ($should_hide) {
                $element->add_render_attribute('_wrapper', 'style', 'display: none;');
            }
        }
    }

    /**
     * Filter content
     */
    public function filter_content($content) {
        return preg_replace_callback(
            '/<div[^>]*data-domain-condition="([^"]*)"[^>]*data-domain-current="([^"]*)"[^>]*>/',
            function($matches) {
                $match_value = $matches[1];
                $current_value = $matches[2];
                
                // Check if we should hide the element
                $should_hide = false;
                
                if ($match_value === 'algemeen') {
                    // For 'algemeen', show when domein is empty or not set
                    $should_hide = !empty($current_value);
                } else {
                    // For other values, show only when they match exactly
                    $should_hide = ($current_value !== $match_value);
                }
                
                if ($should_hide) {
                    return str_replace('>', ' style="display: none;">', $matches[0]);
                }
                return $matches[0];
            },
            $content
        );
    }

    /**
     * Admin notice for missing main plugin
     */
    public function admin_notice_missing_main_plugin() {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'display-condition-on-request-var'),
            '<strong>' . esc_html__('Display Condition On Request Var', 'display-condition-on-request-var') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'display-condition-on-request-var') . '</strong>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin notice for minimum Elementor version
     */
    public function admin_notice_minimum_elementor_version() {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'display-condition-on-request-var'),
            '<strong>' . esc_html__('Display Condition On Request Var', 'display-condition-on-request-var') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'display-condition-on-request-var') . '</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin notice for minimum PHP version
     */
    public function admin_notice_minimum_php_version() {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'display-condition-on-request-var'),
            '<strong>' . esc_html__('Display Condition On Request Var', 'display-condition-on-request-var') . '</strong>',
            '<strong>' . esc_html__('PHP', 'display-condition-on-request-var') . '</strong>',
            self::MINIMUM_PHP_VERSION
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }
}

// Initialize the plugin
Display_Condition_On_Request_Var::instance();