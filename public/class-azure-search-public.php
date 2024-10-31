<?php

/**
 * The public-facing functionality of the plugin.
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */
class Azure_Search_Public {

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
	 *
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/azure-search-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

        wp_enqueue_script( 'purl', plugin_dir_url( dirname(__FILE__) ) . 'lib/purl.js', array( 'jquery' ) );
        wp_enqueue_script( 'twbs', plugin_dir_url( dirname(__FILE__) ) . 'lib/jquery.twbsPagination.js', array( 'jquery' ) );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/azure-search-public.js',
            array( 'jquery', 'backbone', 'underscore', 'purl', 'twbs' ) );

        $nonce = wp_create_nonce($this->plugin_name);
        $serviceName = Azure_Search_Settings_Page::getServiceName();
        $indexName = Azure_Search_Settings_Page::getIndexName();
        $searchKey = Azure_Search_Settings_Page::getQueryKey();
        wp_localize_script($this->plugin_name, 'azure_search_results_ajax_obj', array(
            'search_base_url' => "https://{$serviceName}.search.windows.net",
            'search_api_version' => Azure_Search_SDK::API_VERSION,
            'index_name' => $indexName,
            'search_key' => $searchKey,
            'nonce' => $nonce
        ));

	}

    /**
     * Register the shortcodes for the public-facing side of the site.
     */
    public function register_shortcodes()
    {
        add_shortcode('search_with_azure', array($this, 'shortcode_azure_search'));
    }


    function shortcode_azure_search()
    {
        $homeUrl = home_url();
        $loading = __('Loading...', $this->plugin_name);
        $count = __('matching results', $this->plugin_name);
        return <<<HTML
<script type="text/template" id="azure-search-loading-template"><div class="loading">$loading</div></script>
<script type="text/template" id="azure-search-result-template">
<div class="count" data-count="{{count}}">{{count}} $count</div>
<ul class="results">
    {{#each models}}
        <li data-id="{{id}}" data-name="{{name}}" data-date="{{date}}">
            <div class='truncate'><a href="$homeUrl?p={{id}}">{{title}}</a></div>
            <div class='truncate'>{{content}}</div>
        </li>
    {{/each}}
</ul>
</script>
<div class="azure-search-result"></div>
<ul class="azure-search-navigation"></ul>
HTML;
    }

    /**
     * Update indexes.
     * This is the function that's called from JavaScript.
     */
    function suggestions()
    {
        check_ajax_referer($this->plugin_name);
        $search = $_GET["search"];
        $result = $this->suggestions_internal($search);
        echo json_encode($result);
        die();
    }

    function suggestions_internal($search)
    {
        $serviceName = Azure_Search_Settings_Page::getServiceName();
        $indexName = Azure_Search_Settings_Page::getIndexName();
        $key = Azure_Search_Settings_Page::getQueryKey();
        $sdk = new Azure_Search_SDK($serviceName, $indexName, null, $key);
        $response = $sdk->suggestions($search);

        $map = array_map(array('Azure_Search_Public', 'mapSuggestionToResult'), $response);

        return $map;
    }

    private function mapSuggestionToResult($suggestion)
    {
        return array(
            "post_id" => $suggestion["ID"],
            "post_type" => $suggestion["post_type"],
            "post_name" => $suggestion["post_name"],
            "post_title" => $suggestion["post_title"]
        );
    }

}
