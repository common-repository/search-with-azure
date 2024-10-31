<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 */
class Azure_Search {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 */
	public function __construct() {

		$this->plugin_name = 'search-with-azure';
		$this->version = '1.1.1';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Azure_Search_Loader. Orchestrates the hooks of the plugin.
	 * - Azure_Search_i18n. Defines internationalization functionality.
	 * - Azure_Search_Admin. Defines all hooks for the admin area.
	 * - Azure_Search_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-azure-search-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-azure-search-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-azure-search-admin.php';

        /**
         * The class responsible for defining the admin settings page.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-azure-search-admin-settings-page.php';

        /**
         * The class responsible for defining the Azure Search indexing operations.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-azure-search-indexing.php';

        /**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-azure-search-public.php';

        /**
         * The class responsible for interacting with the Azure Search API.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-azure-search-sdk.php';

        /**
         * Search box widget
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'widgets/azure-search-box-widget.php';


        $this->loader = new Azure_Search_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Azure_Search_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 */
	private function set_locale() {

		$plugin_i18n = new Azure_Search_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Azure_Search_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        // edit post hooks
        $indexing = new Azure_Search_Indexing( $this->get_plugin_name(), $this->get_version() );

        // these two are the real hooks
        $this->loader->add_action( 'wp_insert_post', $indexing, 'wp_insert_post', 99, 3);
        $this->loader->add_action( 'delete_post', $indexing, 'delete_post');

        // these are just for debugging
        $this->loader->add_action( 'add_attachment', $indexing, 'add_attachment');
        $this->loader->add_action( 'comment_post', $indexing, 'comment_post');
        $this->loader->add_action( 'delete_attachment', $indexing, 'delete_attachment');
        $this->loader->add_action( 'delete_comment', $indexing, 'delete_comment');
        $this->loader->add_action( 'edit_attachment', $indexing, 'edit_attachment');
        $this->loader->add_action( 'edit_comment', $indexing, 'edit_comment');
        $this->loader->add_action( 'transition_post_status', $indexing, 'transition_post_status', 99, 3);

        $settings_page = new Azure_Search_Settings_Page( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_menu', $settings_page, 'add_plugin_page' );
        $this->loader->add_action( 'admin_init', $settings_page, 'page_init' );

        // define ajax callback methods
        $this->loader->add_action('wp_ajax_init_indexes', $plugin_admin, 'init_indexes');
        $this->loader->add_action('wp_ajax_update_indexes', $plugin_admin, 'update_indexes');

    }

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 */
	private function define_public_hooks() {

		$plugin_public = new Azure_Search_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );

		// define ajax callback methods
		$this->loader->add_action('wp_ajax_suggestions', $plugin_public, 'suggestions');
		$this->loader->add_action('wp_ajax_nopriv_suggestions', $plugin_public, 'suggestions');

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
