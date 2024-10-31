<?php

/**
 * The admin settings page.
 * Based on http://codex.wordpress.org/Creating_Options_Pages#Example_.232
 */
class Azure_Search_Settings_Page
{

    /**
     * The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     * @var      string $plugin_name The name of this plugin.
     * @var      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public static function getServiceName()
    {
        $options = get_option('azure_search');
        if (isset($options['service_name'])) {
            return $options['service_name'];
        }
        return FALSE;
    }

    public static function getIndexName()
    {
        $options = get_option('azure_search');
        if (isset($options['index_name'])) {
            return $options['index_name'];
        }
        return FALSE;
    }

    public static function getAdminKey()
    {
        $options = get_option('azure_search');
        if (isset($options['admin_key'])) {
            return $options['admin_key'];
        }
        return FALSE;
    }

    public static function getQueryKey()
    {
        $options = get_option('azure_search');
        if (isset($options['query_key'])) {
            return $options['query_key'];
        }
        return FALSE;
    }

    public static function getSearchPageId()
    {
        $options = get_option('azure_search');
        if (isset($options['search_page_id'])) {
            return $options['search_page_id'];
        }
        return FALSE;
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            __('Settings Admin', $this->plugin_name),
            __('Search with Azure', $this->plugin_name),
            'manage_options',
            'azure-search-setting-admin',
            array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option('azure_search');
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Search with Azure</h2>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields('azure_search_option_group');
                do_settings_sections('as-setting-admin');
                submit_button();
                do_settings_sections('init-setting-admin');
                ?>
            </form>
        </div>
    <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'azure_search_option_group', // Option group
            'azure_search', // Option name
            array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            'service_setting_section', // ID
            __('Azure Service Settings', $this->plugin_name), // Title
            array($this, 'service_section_info'), // Callback
            'as-setting-admin' // Page
        );

        add_settings_field(
            'service_name', // ID
            __('Service Name', $this->plugin_name), // Title
            array($this, 'service_name_callback'), // Callback
            'as-setting-admin', // Page
            'service_setting_section' // Section
        );

        add_settings_field(
            'index_name', // ID
            __('Index Name', $this->plugin_name), // Title
            array($this, 'index_name_callback'), // Callback
            'as-setting-admin', // Page
            'service_setting_section' // Section
        );

        add_settings_field(
            'admin_key', // ID
            __('Admin Key', $this->plugin_name), // Title
            array($this, 'admin_key_callback'), // Callback
            'as-setting-admin', // Page
            'service_setting_section' // Section
        );

        add_settings_field(
            'query_key', // ID
            __('Query Key', $this->plugin_name), // Title
            array($this, 'query_key_callback'), // Callback
            'as-setting-admin', // Page
            'service_setting_section' // Section
        );

        add_settings_field(
            'search_page_id', // ID
            __('Search Page ID', $this->plugin_name), // Title
            array($this, 'search_page_id_callback'), // Callback
            'as-setting-admin', // Page
            'service_setting_section' // Section
        );

        add_settings_section(
            'init_setting_section', // ID
            __('Initialize Index', $this->plugin_name), // Title
            array($this, 'init_section_info'), // Callback
            'init-setting-admin' // Page
        );

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
        $new_input = array();

        if (isset($input['service_name']))
            $new_input['service_name'] = sanitize_text_field($input['service_name']);

        if (isset($input['index_name']))
            $new_input['index_name'] = sanitize_text_field($input['index_name']);

        if (isset($input['admin_key']))
            $new_input['admin_key'] = sanitize_text_field($input['admin_key']);

        if (isset($input['query_key']))
            $new_input['query_key'] = sanitize_text_field($input['query_key']);

        if (isset($input['search_page_id']))
            $new_input['search_page_id'] = sanitize_text_field($input['search_page_id']);

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function service_section_info()
    {
        // print 'Enter your settings below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function service_name_callback()
    {
        printf(
            '<input type="text" id="service_name" name="azure_search[service_name]" value="%s" size="40" />',
            isset($this->options['service_name']) ? esc_attr($this->options['service_name']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function index_name_callback()
    {
        printf(
            '<input type="text" id="index_name" name="azure_search[index_name]" value="%s" size="40" />',
            isset($this->options['index_name']) ? esc_attr($this->options['index_name']) : 'posts'
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function admin_key_callback()
    {
        printf(
            '<input type="text" id="admin_key" name="azure_search[admin_key]" value="%s" size="40" autocomplete="off" />',
            isset($this->options['admin_key']) ? esc_attr($this->options['admin_key']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function query_key_callback()
    {
        printf(
            '<input type="text" id="query_key" name="azure_search[query_key]" value="%s" size="40" autocomplete="off" />',
            isset($this->options['query_key']) ? esc_attr($this->options['query_key']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function search_page_id_callback()
    {
        printf(
            '<input type="text" id="search_page_id" name="azure_search[search_page_id]" value="%s" size="40" />',
            isset($this->options['search_page_id']) ? esc_attr($this->options['search_page_id']) : ''
        );
    }

    /**
     * Print the Section text
     */
    public function init_section_info()
    {
        // TODO some helpful, descriptive text
        // _e('Create or update index', $this->plugin_name);

        printf(
            '<p class="submit"><input type="button" id="azure_search_init_index" class="button button-primary" value="%s"></p>',
            __('Initialize Now', $this->plugin_name));
        printf(
            '<div id="azure_search_init_index_status"></div>');
    }

}
