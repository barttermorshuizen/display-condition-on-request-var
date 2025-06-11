<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Elementor_Conditional_Widget_Element extends \Elementor\Widget_Base {

    public function get_name() {
        return 'conditional_widget';
    }

    public function get_title() {
        return esc_html__('Conditional Widget', 'elementor-conditional-widget');
    }

    public function get_icon() {
        return 'eicon-code';
    }

    public function get_categories() {
        return ['general'];
    }

    public function get_keywords() {
        return ['conditional', 'request', 'variable', 'dynamic'];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'elementor-conditional-widget'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'content',
            [
                'label' => esc_html__('Content', 'elementor-conditional-widget'),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => esc_html__('Widget Content', 'elementor-conditional-widget'),
            ]
        );

        $this->end_controls_section();

        // Condition Section
        $this->start_controls_section(
            'condition_section',
            [
                'label' => esc_html__('Condition', 'elementor-conditional-widget'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'request_var',
            [
                'label' => esc_html__('Request Variable', 'elementor-conditional-widget'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => 'variable_name',
                'description' => esc_html__('Enter the name of the request variable to check', 'elementor-conditional-widget'),
            ]
        );

        $this->add_control(
            'request_value',
            [
                'label' => esc_html__('Expected Value', 'elementor-conditional-widget'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => 'expected_value',
                'description' => esc_html__('Enter the value that the request variable should match', 'elementor-conditional-widget'),
            ]
        );

        $this->add_control(
            'condition_type',
            [
                'label' => esc_html__('Condition Type', 'elementor-conditional-widget'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'equals',
                'options' => [
                    'equals' => esc_html__('Equals', 'elementor-conditional-widget'),
                    'not_equals' => esc_html__('Not Equals', 'elementor-conditional-widget'),
                    'contains' => esc_html__('Contains', 'elementor-conditional-widget'),
                    'not_contains' => esc_html__('Not Contains', 'elementor-conditional-widget'),
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Get request variable value
        $request_var = sanitize_text_field($settings['request_var']);
        $request_value = sanitize_text_field($settings['request_value']);
        $condition_type = $settings['condition_type'];
        
        // Get actual value from $_REQUEST
        $actual_value = isset($_REQUEST[$request_var]) ? sanitize_text_field($_REQUEST[$request_var]) : '';
        
        // Check condition
        $show_content = false;
        switch ($condition_type) {
            case 'equals':
                $show_content = ($actual_value === $request_value);
                break;
            case 'not_equals':
                $show_content = ($actual_value !== $request_value);
                break;
            case 'contains':
                $show_content = (strpos($actual_value, $request_value) !== false);
                break;
            case 'not_contains':
                $show_content = (strpos($actual_value, $request_value) === false);
                break;
        }
        
        // Render content if condition is met
        if ($show_content) {
            echo wp_kses_post($settings['content']);
        }
    }

    protected function content_template() {
        ?>
        <# if ( settings.content ) { #>
            {{{ settings.content }}}
        <# } #>
        <?php
    }
}