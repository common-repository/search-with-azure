<?php
/**
 * Add function to widgets_init that'll load our widget.
 */
add_action('widgets_init', 'azure_search_box_widget_init');

/**
 * Register our widget.
 * 'Azure_Search_Box_Widget' is the widget class used below.
 */
function azure_search_box_widget_init()
{
    register_widget('Azure_Search_Box_Widget');
}

/**
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.
 */
class Azure_Search_Box_Widget extends WP_Widget
{
    private $plugin_name;
    private $version;

    /**
     * Widget setup.
     */
    function __construct()
    {
        $this->plugin_name = 'search-with-azure'; // TODO get this from the plugin
        $this->version = '1.1.1'; // TODO get this from the plugin

        /* Widget settings. */
        $widget_ops = array(
            'classname' => 'azure-search-box',
            'description' => __('Search form for Azure Search.', $this->plugin_name)
        );

        /* Widget control settings. */
        $control_ops = array('width' => 300, 'height' => 350, 'id_base' => 'azure_search_box_widget');

        /* Create the widget. */
        parent::__construct('azure_search_box_widget', __('Search with Azure', $this->plugin_name), $widget_ops, $control_ops);

    }

    /**
     * Register the stylesheets for the widget.
     */
    function enqueue_style() {

        wp_enqueue_style( 'azure-search-box-widget', plugin_dir_url( dirname(__FILE__) ) . 'public/css/azure-search-box-widget.css', array(), $this->version, 'all' );

    }

    /**
     * Register the scripts for the widget.
     */
    function enqueue_scripts() {

        wp_enqueue_script( 'typeahead-bundle', plugin_dir_url( dirname(__FILE__) ) . 'lib/typeahead.bundle.0.11.1.js' );
        wp_enqueue_script( 'handlebars',       plugin_dir_url( dirname(__FILE__) ) . 'lib/handlebars-v3.0.3.js' );

        wp_enqueue_script( 'azure-search-box-widget', plugin_dir_url( dirname(__FILE__) ) . 'public/js/azure-search-box-widget.js',
            array( 'jquery', 'backbone', 'handlebars', 'typeahead-bundle') );

        $nonce = wp_create_nonce( $this->plugin_name );
        wp_localize_script( 'azure-search-box-widget', 'azure_search_box_widget_ajax_obj', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => $nonce,
            'home_url' => home_url(),
            'no_match_text' => __('No matching results', $this->plugin_name),
            'limit' => 5 // TODO use the same variable in sdk
        ));
    }

    /**
     * How to display the widget on the screen.
     */
    function widget($args, $instance)
    {
        extract($args);

        /* Our variables from the widget settings. */
        $title = apply_filters('widget_title', $instance['title']);

        $this->enqueue_style();
        $this->enqueue_scripts();

        /* Before widget (defined by themes). */
        echo $before_widget;

        /* Display the widget title if one was input (before and after defined by themes). */
        if ($title)
            echo $before_title . $title . $after_title;

        $pageId = Azure_Search_Settings_Page::getSearchPageId();
        $searchPage = get_permalink($pageId);
        // TODO don't need page_id parameter if it's a proper permalink

        ?>
        <div class="cls_search cls_azure_search">
            <form method="get" action="<?php echo $searchPage; ?>">
                <input type="hidden" name="page_id" value="<?php echo $pageId; ?>" />
                <input type="text" placeholder="<?php _e('Search ...', $this->plugin_name); ?>" value="" name="as" class="azure-search-field typeahead" autocomplete="off" />
            </form>
        </div>
        <?php


        /* After widget (defined by themes). */
        echo $after_widget;
    }

    /**
     * Update the widget settings.
     */
    function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }

    /**
     * Displays the widget settings controls on the widget panel.
     * Make use of the get_field_id() and get_field_name() function
     * when creating your form elements. This handles the confusing stuff.
     */
    function form($instance)
    {
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text"
                   value="<?php echo esc_attr($instance['title']); ?>">
        </p>
    <?php
    }

}

?>