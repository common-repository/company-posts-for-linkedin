<?php

/**
 * The public-facing functionality of the plugin.
 * @link       https://brainstation-23.com
 * @since      1.0.0
 * @package    Company-Posts-for-LinkedIn
 * @subpackage Company-Posts-for-LinkedIn/public
 * @author     Brain Station 23 Ltd. <elearning@brainstation-23.com>
 */
class Linkedin_Company_Posts_Public
{

    private $plugin_name;
    private $version;

    protected $token_life = 0;
    protected $options = array();
    protected $auth_options = array();

    /**
     * Initialize the class and set its properties.
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */

    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->options = get_option($this->plugin_name);
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     * @since    1.0.0
     */

    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/linkedin-company-posts-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/linkedin-company-posts-public.js', array('jquery'), $this->version, false);
    }

    /**
     * Summary of get_updates
     * @param mixed $atts
     * @param mixed $tag
     * @param mixed $content
     * @return string
     */
    public function get_updates($atts, $tag, $content = null)
    {

        // Set up shortcode attributes
        $args = shortcode_atts(
            array(
                'card_class' => $this->options['update-items-container-class'] ?? '',
                'item_class' => $this->options['update-item-class'] ?? '',
                'company' => $this->options['company-id'] ?? '',
                'limit' => $this->options['limit'] ?? 3,
            ),
            $atts
        );

        // Set up options
        $company_id = $args['company'];

        $token_life = Linkedin_Company_Posts::$helper->get_token_life();

        // Make sure auth token isn't expired
        if ($token_life) {

            $array_posts = Linkedin_Company_Posts::$helper->get_company_posts($company_id, $args['limit']);

            $company_posts = '<div id="linkedin-company-posts" class="linkedin-posts' . $args['card_class'] . '">';

            if ($array_posts) {

                foreach ($array_posts as $post) {

                    // Set up the time ago strings
                    // $time_ago = $this->time_ago($post['published_at']);
                    // Add picture if there is one
                    $post_image_link = $post['media'];
                    $post_link = $post['url'];
                    $img = '<a target="_blank" class="image-container" href="' . $post_link . '"><img alt="" class="linkedin-update-image" src="' . $post_image_link . '" /></a>';

                    // Filter the content for links
                    $post_content = preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a target="_blank" href="$1">$1</a>', $post['content']);

                    // Create the link to the post
                    // Add this item to the update string
                    $company_posts .= '<div class="single-post-div ' . $args['item_class'] . '">';
                    $company_posts .= '<div class="single_post" style="position:relative;">';
                    $company_posts .= $img;
                    $company_posts .= '<span class="linkedin-icon"><img src="' .esc_url(plugins_url('images/linkedin-logo.png', __FILE__) ) . '" /></span>';
                    $company_posts .= '<span class="linkedin-title">' .esc_html($post_content) . '</span>';
                    $company_posts .= '</div>';
                    $company_posts .= '</div>';
                }
            } else {
                $company_posts .= '<div>' . __('Sorry, no posts were received from LinkedIn!', 'company-posts-for-linkedin') . '</div>';
            }

            $company_posts .= '</div>';

            return $company_posts;
        }

    }

}