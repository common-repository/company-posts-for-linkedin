<?php

/**
 * The file that defines the core plugin class
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 * @link       https://brainstation-23.com
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 * @since      1.0.0
 * @package    Company-Posts-for-LinkedIn
 * @subpackage Company-Posts-for-LinkedIn/includes
 * @author     Brain Station 23 Ltd. <elearning@brainstation-23.com>
 */
class Linkedin_Company_Posts
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     * @since    1.0.0
     * @access   protected
     * @var      Linkedin_Company_Posts_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;
    public static $helper;
    /**
     * The unique identifier of this plugin.
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    //---- Set up variables
    protected $client_id = '';
    protected $admin_url = '';
    protected $options = array();

    /**
     * Base URL for LinkedIn. This constant represents the root URL for LinkedIn,
     * used as a foundation for constructing various LinkedIn API endpoints.
     *
     * Please refer to LinkedIn's service terms and policies for proper usage of the API.
     *
     * @var string
     */
    const BASE_URL = 'https://www.linkedin.com/';

    /**
     * API URL for LinkedIn. This constant represents the base URL for LinkedIn API requests,
     * specifically the REST API. It is used as a prefix for constructing specific API endpoints.
     *
     * Please ensure compliance with LinkedIn's service terms and policies when making API requests.
     *
     * @var string
     */
    const API_URL = 'https://api.linkedin.com/rest/';

    const LINKEDIN_VERSION = '202406';
    const PROTOCOL_VERSION = '2.0.0';
    const REFRESH_TOKEN_INTERVAL = 55;

    /**
     * Define the core functionality of the plugin.
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     * @since    1.0.0
     */

    public function __construct()
    {
        if (defined('LINKEDIN_COMPANY_POSTS_VERSION')) {
            $this->version = LINKEDIN_COMPANY_POSTS_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'company-posts-for-linkedin';

        $this->options = get_option($this->plugin_name);
        $this->auth_options = get_option($this->plugin_name . '_authkey');

        add_action('refresh_linkedin_company_access_token', array($this, 'refresh_linkedin_company_access_token'));
        add_filter('cron_schedules', array(&$this, 'add_lcp_refresh_token_interval_schedule'));
        if (!wp_next_scheduled('refresh_linkedin_company_access_token') && !wp_installing()) {
            wp_schedule_event(
                time(),
                'linkedin_company_refresh_token_interval',
                'refresh_linkedin_company_access_token'
            );
        }

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

    }

    /**
     * Load the required dependencies for this plugin.
     * Include the following files that make up the plugin:
     * - Linkedin_Company_Posts_Loader. Orchestrates the hooks of the plugin.
     * - Linkedin_Company_Posts_i18n. Defines internationalization functionality.
     * - Linkedin_Company_Posts_Admin. Define all hooks for the admin area.
     * - Linkedin_Company_Posts_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     * @since    1.0.0
     * @access   private
     */

    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-linkedin-company-post-metaboxes.php';

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-linkedin-company-posts-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-linkedin-company-posts-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-linkedin-company-posts-admin.php';

        /**
         * The class responsible for defining common actions that occur in the admin & public area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-linkedin-company-posts-helper.php';

        self::$helper = new Linkedin_Company_Posts_Helper($this->get_plugin_name(), $this->get_version());

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-linkedin-company-posts-public.php';

        $this->loader = new Linkedin_Company_Posts_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     * Uses the Linkedin_Company_Posts_i18n class in order to set the domain and to register the hook
     * with WordPress.
     * @since    1.0.0
     * @access   private
     */

    private function set_locale()
    {

        $plugin_i18n = new Linkedin_Company_Posts_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all the hooks related to the admin area functionality
     * of the plugin.
     * @since    1.0.0
     * @access   private
     */

    private function define_admin_hooks()
    {

        $plugin_admin = new Linkedin_Company_Posts_Admin($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

    }

    /**
     * Register all the hooks related to the public-facing functionality
     * of the plugin.
     * @since    1.0.0
     * @access   private
     */

    private function define_public_hooks()
    {

        $plugin_public = new Linkedin_Company_Posts_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_shortcode('company-posts-for-linkedin', $plugin_public, 'get_updates');
    }


    /**
     * Run the loader to execute all the hooks with WordPress.
     * @since    1.0.0
     */

    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     * @return    string    The name of the plugin.
     * @since     1.0.0
     */

    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     * @return    Linkedin_Company_Posts_Loader    Orchestrates the hooks of the plugin.
     * @since     1.0.0
     */

    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     * @return    string    The version number of the plugin.
     * @since     1.0.0
     */

    public function get_version()
    {
        return $this->version;
    }

    /**
     * Summary of refresh_lcp_access_token
     * function to refresh request for the access token
     * @return bool
     */

    public function refresh_lcp_access_token()
    {
        return Linkedin_Company_Posts::$helper->refresh_lcp_access_token();
    }

    /**
     * Adds a notification to the dashboard
     */

    public static function notification($text, $update = 0)
    {

        $class = esc_attr($update) ? 'updated' : 'error';
        $message = '<div class="notice is-dismissible ' . esc_attr($class) . '">';
        $message .= '<p>' . esc_html($text) . '</p>';
        $message .= '</div>';

        echo wp_kses_post($message);
    }

    /**
     * Summary of add_lcp_refresh_token_interval_schedule
     * lcp = LinkedIn Company posts
     * function to calculate access token refresh interval schedule
     * @return array<array>
     */

    function add_lcp_refresh_token_interval_schedule()
    {
        $schedules['linkedin_company_refresh_token_interval'] = array(
            'interval' => self::REFRESH_TOKEN_INTERVAL * 86400,
            'display' => __('LCP Refresh Token Interval', 'textdomain')
        );

        return $schedules;
    }
}