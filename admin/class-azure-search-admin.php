<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks to
 * enqueue the admin-specific stylesheet and JavaScript.
 */
class Azure_Search_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in Azure_Search_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Azure_Search_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/azure-search-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 */
	public function enqueue_scripts() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in Azure_Search_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Azure_Search_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/azure-search-admin.js', array( 'jquery' ) );

        $nonce = wp_create_nonce($this->plugin_name);
        wp_localize_script($this->plugin_name, 'admin_ajax_obj', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => $nonce,
            'progress_prefix' => __('Progress: ', $this->plugin_name),
            'progress_infix' => __(' of ', $this->plugin_name),
            'finished' => __('Finished', $this->plugin_name)
        ));

	}


    /**
     * Initialize indexes.
     * This is the function that's called from JavaScript.
     */
    function init_indexes()
    {
        check_ajax_referer($this->plugin_name);
        $result = $this->init_indexes_internal();
        echo json_encode($result);
        die();
    }

    function init_indexes_internal()
    {
        $result = array();

        $serviceName = Azure_Search_Settings_Page::getServiceName();
        $indexName = Azure_Search_Settings_Page::getIndexName();
        $adminKey = Azure_Search_Settings_Page::getAdminKey();
        $sdk = new Azure_Search_SDK($serviceName, $indexName, $adminKey, null);
        $response = $sdk->createPostsIndex();
        $result['response'] = $response;

        $count = $this->countPosts();
        $result['count'] = $count;

        return $result;
    }

    function countPosts()
    {
        $args = array(
            'post_type' => array('post', 'page'),
            'post_status' => 'publish',
        );
        $the_query = new WP_Query($args);
        return $the_query->found_posts;
    }

    /**
     * Update indexes.
     * This is the function that's called from JavaScript.
     */
    function update_indexes()
    {
        check_ajax_referer($this->plugin_name);
        $nextPage = $_POST["nextPage"];
        $result = $this->update_indexes_internal($nextPage);
        echo json_encode($result);
        die();
    }

    function update_indexes_internal($paged)
    {
        $args = array(
            'post_type' => array('post', 'page'),
            'post_status' => 'publish',
            'paged' => $paged
        );
        $the_query = new WP_Query($args);
        $posts = $the_query->posts;

        $count = $the_query->post_count;
        $result = array('paged' => intval($paged), 'count' => $count);

        $response = array('is_error' => FALSE);

        if ($count > 0) {
            $serviceName = Azure_Search_Settings_Page::getServiceName();
            $indexName = Azure_Search_Settings_Page::getIndexName();
            $adminKey = Azure_Search_Settings_Page::getAdminKey();
            $sdk = new Azure_Search_SDK($serviceName, $indexName, $adminKey, null);
            $response = $sdk->addPosts($posts);
        }

        $result['response'] = $response;

        return $result;
    }

}
