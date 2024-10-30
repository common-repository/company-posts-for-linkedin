<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Company-Posts-for-LinkedIn
 * @subpackage Company-Posts-for-LinkedIn/includes
 * @author     Brain Station 23 Ltd. <elearning@brainstation-23.com>
 */
class Linkedin_Company_Posts_Admin
{
    protected $meta_boxes = array();
    private $plugin_name;
    private $version;
    protected $client_id = '';
    protected $redirect_url = '';
    protected $admin_url = '';
    protected $auth_options = array();
    protected $options = array();
    private $options_metaboxes;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;

        $this->version = $version;

        // define admin url
        $this->admin_url = admin_url('options-general.php?page=' . $this->plugin_name);

        // add plugin page
        add_action('admin_menu', array(&$this, 'admin_menu'));

        // add plugin sections
        add_action('admin_init', array(&$this, 'settings_sections'));

        $AppLink = '<a class="AppLink" href="' . esc_url('https://www.linkedin.com/developers/apps') . '" target="_blank"> LinkedIn Apps</a>';
        $help_image_1 = '<a href="' . esc_url(plugins_url('images/help_1.jpg', __FILE__)) . '" target="_blank"> 🛈 </a>';
        $help_image_2 = '<a href="' . esc_url(plugins_url('images/help_2.jpg', __FILE__)) . '" target="_blank" class="help"> 🛈 </a>';
        $help_image_3 = '<a href="' . esc_url(plugins_url('images/help_3.jpg', __FILE__)) . '" target="_blank" class="help"> 🛈 </a>';

        // meta boxes configuration
        $this->meta_boxes = array(

            'config' => array(
                'title' => esc_html(__('Configuration', 'company-posts-for-linkedin')) . $help_image_1 . $AppLink,
                'settings' => array(

                    // Linkedin setup
                    'client-id' => array(
                        'type' => 'text',
                        'title' => esc_html(__('Client ID', 'company-posts-for-linkedin')),
                        'description' => esc_html(__('Your LinkedIn App Client ID.', 'company-posts-for-linkedin')),
                        'placeholder' => esc_html(__('Client ID', 'company-posts-for-linkedin')),

                    ),
                    'client-secret' => array(
                        'type' => 'text',
                        'title' => esc_html(__('Client Secret', 'company-posts-for-linkedin')),
                        'description' => esc_html(__('Your LinkedIn App Client Secret.', 'company-posts-for-linkedin')),
                        'placeholder' => esc_html(__('Client Secret', 'company-posts-for-linkedin'))
                    ),
                    'redirect-url' => array(
                        'type' => 'redirect',
                        'title' => esc_html(__('Authorized redirect URL', 'company-posts-for-linkedin')) . $help_image_2,
                        'description' => esc_html(__('Fully qualified URLs to define valid OAuth 2.0 callback paths, as defined in your LinkedIn App.', 'company-posts-for-linkedin')),
                        'placeholder' => esc_html(__('Redirect URL', 'company-posts-for-linkedin'))
                    ),
                    'access-token' => array(
                        'type' => 'authorize',
                        'title' => esc_html(__('Access Token', 'company-posts-for-linkedin')),
                    ),
                    'reset-post' => array(
                        'type' => 'reset',
                        'title' => esc_html(__('Reset LinkedIn Post', 'company-posts-for-linkedin')),
                    ),

                ),
            ),

            'feed' => array(
                'title' => __('Feed Settings', 'company-posts-for-linkedin'),
                'settings' => array(

                    // plugin options
                    'company-id' => array(
                        'type' => 'text',
                        'title' => esc_html(__('Company ID', 'company-posts-for-linkedin')) . $help_image_3,
                        'description' => esc_html(__('Your LinkedIn Company ID.', 'company-posts-for-linkedin')),
                        'placeholder' => 'Your LinkedIn Company ID',
                    ),
                    'limit' => array(
                        'type' => 'number',
                        'title' => esc_html(__('Limit', 'company-posts-for-linkedin')),
                        'description' => esc_html(__('Visible Posts Count.', 'company-posts-for-linkedin')),
                        'validator' => 'numeric',
                        'placeholder' => 3,
                    ),
                    'auto-linkedin_company-refresh-interval' => array(
                        'type' => 'number',
                        'title' => esc_html(__('Auto Refresh', 'company-posts-for-linkedin')),
                        'description' => esc_html(__('Auto Refresh Interval in seconds.', 'company-posts-for-linkedin')),
                        'validator' => 'numeric',
                        'default' => 1800,
                        'placeholder' => 1800,
                    ),
                    'character-length' => array(
                        'type' => 'number',
                        'title' => esc_html(__('Post Content Character Length', 'company-posts-for-linkedin')),
                        'description' => esc_html(__('Post Content Character Length that will be shown in each post card.', 'company-posts-for-linkedin')),
                        'validator' => 'numeric',
                        'default' => 30,
                        'placeholder' => 30,
                    ),
                    'update-items-container-class' => array(
                        'type' => 'text',
                        'title' => esc_html(__('Post Items Container Class', 'company-posts-for-linkedin')),
                        'description' => esc_html(__('This class will be added to the container of the post items. Leave a space between each class.', 'company-posts-for-linkedin')),
                        'placeholder' => 'company-posts-for-linkedin-container',
                    ),
                    'update-item-class' => array(
                        'type' => 'text',
                        'title' => esc_html(__('Post Item Class', 'company-posts-for-linkedin')),
                        'description' => esc_html(__('This class will be added to each post item. Leave a space between each class.', 'company-posts-for-linkedin')),
                        'placeholder' => 'company-posts-for-linkedin-card',
                    ),

                ),

            ),

            'publish' => array(

                'title' => __('Save Settings', 'company-posts-for-linkedin'),

                'settings' => [
                    'remove-config-when-uninstall' => array(
                        'type' => 'checkbox',
                        'title' => esc_html(__('Remove Configuration Upon Uninstallation.', 'company-posts-for-linkedin')),
                    ),
                ],

            ),

        );

        add_filter('admin_post_reset_linkedin_company_post', array(&$this, 'linkedin_company_post_reset_handler'));

        // if we're on the plugin's settings page
        if (isset($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], 'page=' . $this->plugin_name) !== false) {

            $db_version = get_option($this->plugin_name . '_version');

            if (!$db_version) {

                $old_options = get_option($this->plugin_name . '_options');
                $new_options = array();
                if (is_array($old_options)) {
                    foreach ($old_options as $key => $value) {
                        $new_key = str_replace(' ', '-', strtolower($key));
                        $new_options[$new_key] = $value;
                    }
                }
                $this->options = $new_options;
                update_option($this->plugin_name, $new_options);
                update_option($this->plugin_name . '_version', $this->version);
            }

            $this->options_metaboxes = new Linkedin_Company_Posts_Metaboxes();

            // setup variables
            $this->redirect_url = $this->admin_url;

            // add links to plugin page
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'add_plugin_links'));
        }

        $this->auth_options = Linkedin_Company_Posts::$helper->get_auth_option();
        $this->options = Linkedin_Company_Posts::$helper->get_option();
    }

    /**
     * Register the stylesheets for the admin area.
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        if (false !== strpos($_SERVER['QUERY_STRING'], 'page=' . $this->plugin_name)) {
            wp_enqueue_style($this->plugin_name . '-admin', plugin_dir_url(__FILE__) . 'css/linkedin-company-posts-admin.css', array(), $this->version, 'all');
        }
    }


    /**
     * Register the JavaScript for the admin area.
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name . '-admin', plugin_dir_url(__FILE__) . 'js/linkedin-company-posts-admin.js', array('jquery'), $this->version, false);
    }


    /**
     * Add the handy settings link to the plugin page
     * @param array $links Links for the plugin page
     */

    public function add_plugin_links($links)
    {
        $links[] = '<a href="' . esc_url($this->admin_url) . '">' . __('Settings', 'company-posts-for-linkedin') . '</a>';

        return $links;
    }

    /**
     * Set up the settings page for the plugin
     */

    public function settings_sections()
    {

        foreach ($this->meta_boxes as $section => $meta_box) {

            $section_id = $this->plugin_name . '-' . $section;

            // add the plugin settings section
            add_settings_section(
                $section_id,
                null,
                null,
                $section
            );

            // build the settings fields
            foreach ($meta_box['settings'] as $id => $options) {
                $options['id'] = $id;
                $stc = "";
                if ($options['id'] == 'client-id' || $options['id'] == 'client-secret' || $options['id'] == 'company-id') {
                    $stc = "<span class='required'>*</span>";
                    $isReq = "required";
                }

                add_settings_field(
                    $this->plugin_name . '_' . $id . '_settings',
                    $options['title'] . $stc,
                    array(&$this, 'settings_field'),
                    $section,
                    $section_id,
                    $options
                );
            }

            // register validation
            register_setting(
                $section,
                $this->plugin_name,
                array(&$this, 'settings_validate')
            );
        }
    }

    /**
     * Validate settings
     * @param array $input Form fields to be evaluated
     * @return array   Returns the input iff it is valid
     */

    public function settings_validate($input)
    {
        return $input;
    }

    /**
     * function for Menu Button on admin Dashboard
     * @return void
     */

    public function admin_menu()
    {
        $title = __('Company Posts for Linkedin', 'company-posts-for-linkedin');
        add_options_page($title, $title, 'manage_options', $this->plugin_name, array($this, 'settings_page'));
    }

    /**
     * Add input fields to the settings section
     *
     * @param array $options Options regarding how to build the HTML
     *
     * @return void          HTML for each setting field
     * @throws Exception
     */

    public function settings_field(array $options = array())
    {

        $type = $options['type'];

        switch ($type) {

            // authorize button
            case 'authorize':
                $this->echo_auth_html();
                break;

            // Reset button
            case 'reset':
                $this->echo_reset_html();
                break;

            // redirect url
            case 'redirect':
                $string = __('Add this URL to your LinkedIn Apps Authorized Redirect URL', 'company-posts-for-linkedin');
                echo '<p>' .
                    '<input class="lcu-fullwidth" type="text" readonly onclick="this.select()" value="' . esc_url($this->redirect_url) . '" />' .
                    '</p>' .
                    esc_html($string);

                break;

            // text / number / checkbox
            default:
                $type = $options['type'];
                $id = $options['id'];
                $id_string = $this->plugin_name . '_' . str_replace(' ', '-', $id);
                $name = $this->plugin_name . '[' . $id . ']';
                $placeholder = isset($options['placeholder']) ? $options['placeholder'] : '';
                $description = isset($options['description']) ? $options['description'] : '';
                $isReq = "";
                if ($options['id'] == 'client-id' || $options['id'] == 'client-secret' || $options['id'] == 'company-id') {
                    $isReq = "required";
                }

                // set the value
                if (isset($this->options[$id]) && $this->options[$id]) {

                    $value = 'checkbox' === $type ? '1" checked="checked' : $this->options[$id];
                } else {

                    $value = 'checkbox' === $type ? 1 : '';
                }

                echo '<label>' .
                    '<input type="' . esc_attr($type) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" id="' . esc_attr($id_string) . '"placeholder="' . esc_attr($placeholder) . '" ' . esc_attr($isReq) . '/>' .
                    esc_html($description) .
                    '</label>';

                break;
        }
    }

    /**
     * echos out the reauthorized button for the access token
     */

    public function echo_reset_html()
    {
        // Build parameters for the authorize link
        $authorize_string = "";
        $authorization_message = "";
        // if re-authorizing

        if (empty($this->client_id)) {
            $authorization_message = '<p>' . esc_html(__('You must save the Client ID & Client Secret to get the Authorize Button.', 'company-posts-for-linkedin')) . '</p>';
        } else {
            $authorize_string = 'Reset LinkedIn Post';
        }

        // Output all the things
        if (!empty($this->client_id)) {
            $url = admin_url('admin-post.php?action=reset_linkedin_company_post');
            echo '<a href="' . esc_url($url) . '" id="reset-linkedin" class="button-secondary">' . esc_html($authorize_string) . '</a>';

        }
        echo wp_kses_post($authorization_message);
    }

    /**
     * echos out the reauthorized button for the access token with different access capabilities
     * @throws Exception
     */

    public function echo_auth_html()
    {
        // Build parameters for the authorize link
        $_SESSION['state'] = $state = substr(md5(wp_rand()), 0, 7);

        $params = [
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'state' => $state,
            'scope' => implode(' ', [
                'r_organization_social'
            ]),
            'redirect_uri' => $this->redirect_url,
        ];
        $authorize_string = "";
        // if re-authorizing
        if ($this->auth_options) {

            $authorize_string = 'Regenerate Access Token';
            $authorization_message = '<p>' . $this->get_auth_expiration_string($this->auth_options['expires_in']) . '</p>';

            // if authorizing for the first time
        } else {
            if (!empty($this->client_id)) {
                $authorize_string = 'Authorize Me';
                $authorization_message = '<p>' . esc_html(__('You must authorize first to get the shortcode.', 'company-posts-for-linkedin')) . '</p>';
            } else {
                $authorization_message = '<p>' . esc_html(__('You must save the Client ID & Client Secret to get the Authorize Button.', 'company-posts-for-linkedin')) . '</p>';
            }
        }

        // Output all the things
        if (!empty($this->client_id)) {
            $url = Linkedin_Company_Posts::BASE_URL . 'oauth/v2/authorization?' . http_build_query($params);
            echo '<a href="' . esc_url($url) . '" id="authorize-linkedin" class="button-secondary">' . esc_html($authorize_string) . '</a>';
        }
        echo wp_kses_post($authorization_message);
    }

    /**
     * Generates a string reflecting when a token will expire
     * @param number $time Unix Timestamp
     * @return string
     * @throws Exception
     */

    private function get_auth_expiration_string($time)
    {

        $token_life = Linkedin_Company_Posts::$helper->get_token_life();
        if ($token_life) {

            $datetime = new DateTime('@' . $token_life, new DateTimeZone('UTC'));
            $date = new DateTime();
            $times = array(
                'days' => $datetime->format('z'),
                'hours' => $datetime->format('G'),
            );
            $date->modify('+' . $times['days'] . ' days');

            return sprintf(
                __('Expires in %s days, %s hours ( <i>%s</i> ) ' . "<br><span class='autoregen-text'> *Automatically Regenerate after %s days</span>", 'company-posts-for-linkedin'),

                $times['days'],
                $times['hours'],
                $date->format('m / d / Y'),
                $times['days'] + 5,
            );
        } else {

            return __('Authorization token has expired, please regenerate.', 'company-posts-for-linkedin');
        }
    }


    /**
     * Function for loading Client id and authentication code
     * @return void
     */

    public function settings_page()
    {

        // load these options
        if ($this->options) {
            $this->client_id = $this->options['client-id'] ?? '';
        }
        // handle if the request has an authentication code
        $this->handle_params();

        // setup meta boxes
        foreach ($this->meta_boxes as $slug => $meta_box) {
            if ('publish' === $slug) {
                continue;
            }
            $this->options_metaboxes->add_settings_metabox($slug, $meta_box['title'], false);
        }
        $this->options_metaboxes->add_publish_metabox(esc_html(__('Save Settings', 'company-posts-for-linkedin')), $this->options_metaboxes->get_settings_html('publish'));
        $this->options_metaboxes->add_metabox('shortcode-info', esc_html(__('Shortcode Info', 'company-posts-for-linkedin')), true, $this->helper_info());

        ?>

        <div class='wrap'>
            <div class="lcp-head">
                <div class='lcp-title'>
                    <div class="">
                        <img src='<?php echo esc_url(plugins_url('images/logo.png', __FILE__)); ?>'>
                    </div>

                    <div class="lcp-api-link">
                        <a href="<?php echo esc_url('https://learn.microsoft.com/en-us/linkedin/') ?>">Learn Linkedin
                            Developer
                            API 🛈</a>
                    </div>
                </div>
                <div class="lcp-info"
                     style="background-image: url('<?php echo esc_url(plugins_url('images/fTop4.png', __FILE__)) ?>');background-repeat:no-repeat; background-position: top right;">
                    <img class="info-bg" src="" alt="">
                    <p>
                        The "Company Posts for LinkedIn" plugin is a powerful tool designed to seamlessly integrate your
                        LinkedIn company's posts into your WordPress site. With this plugin, you can effortlessly
                        showcase your company's latest updates, articles, and announcements, boosting your online
                        presence and engagement.</p>
                </div>
            </div>

            <form method='post' id="poststuff" action='options.php'>
                <?php echo $this->options_metaboxes->output() ?>
            </form>
        </div>

        <?php
    }


    /**
     * helper function for displaying shortcode info and the shortcode
     * @return bool|string
     */

    private function helper_info()
    {

        ob_start();

        // only attempt to display shortcode info if we have valid credentials for the LinkedIn app
        if ($this->auth_options && Linkedin_Company_Posts::$helper->get_token_life() && !empty($this->options['company-id'])) {

            $posts = Linkedin_Company_Posts::$helper->get_company_posts($this->options['company-id'], $this->options['limit']);
            if (!count($posts) > 0) {
                echo '<b>' . esc_html(__('No Posts Found! Make sure you\'re the owner of the company via LinkedIn & put the company ID in Company ID', 'company-posts-for-linkedin')) . '</b>';
            } else {
                echo '<p><span><b>' . esc_html(__('Use this shortcode: ', 'company-posts-for-linkedin')) . '</b></span><input onClick="this.select();" type="text" id="' . 'company-posts-for-linkedin' . '_shortcode" value="[company-posts-for-linkedin company=\'' . esc_html($this->options['company-id']) . '\']"></p>';
                echo wp_kses_post(sprintf(
                    "<p><span>" . __('Use the above shortcode to put the feed into content. For further documentation of shortcodes, go', 'company-posts-for-linkedin') . '<a target="_blank" href="%s"> Here.</a></span></p>',
                    esc_url("https://wordpress.org/plugins/company-posts-for-linkedin/"),
                    esc_url("https://wordpress.org/plugins/company-posts-for-linkedin/")
                ));
            }

            // notify unauthorized users
        } else {
            echo '<p>' . esc_html(__('Need to authorize first.', 'company-posts-for-linkedin')) . '</p>';
        }

        return ob_get_clean();
    }


    /**
     * Handles get parameters in the URL
     */
    private function handle_params()
    {
        // if we have a `code` GET param, we got some work to do
        if (isset($_GET['code'])) {

            // get the access token
            $token = Linkedin_Company_Posts::$helper->get_access_token(sanitize_text_field($_GET['code']));
            if (!$token) {
                return;
            }

            // calculate when the token expires
            $end_date = time() + $token['expires_in'];

            // set session variables
            $_SESSION['access_token'] = $token['access_token'];
            $_SESSION['expires_in'] = $token['expires_in'];
            $_SESSION['expires_at'] = $end_date;

            // update the `auth key` option
            $auth_options = array(
                'access_token' => $token["access_token"],
                'refresh_token' => $token["refresh_token"],
                'refresh_token_expires_in' => $token["refresh_token_expires_in"],
                'expires_in' => $end_date
            );

            $this->auth_options = $auth_options;
            update_option($this->plugin_name . '_authkey', $auth_options);

            echo '<script>window.location = "' . esc_url($this->admin_url) . '&new_token=true' . '";</script>';

        } else if (isset($_GET['new_token'])) {

            if (empty($this->options['company-id'])) {
                Linkedin_Company_Posts::$helper->notification(__('Please add your Company ID.', 'company-posts-for-linkedin'), 0);
            } else {
                Linkedin_Company_Posts::$helper->notification(__('Your LinkedIn authorization token has been successfully updated!', 'company-posts-for-linkedin'), 1);
            }
        } else {
            if (empty($this->options['client-id'])) {
                Linkedin_Company_Posts::$helper->notification(__('Please add Client ID & Client Secret.', 'company-posts-for-linkedin'), 0);
            } elseif (empty($this->options['company-id'])) {
                Linkedin_Company_Posts::$helper->notification(__('Please add your Company ID.', 'company-posts-for-linkedin'), 0);
            } elseif (isset($_GET['settings-updated'])) {
                if ($this->auth_options && Linkedin_Company_Posts::$helper->get_token_life() && !empty($this->options['company-id'])) {
                    delete_option('linkedin_company_posts_data');
                    delete_option('linkedin_company_last_refreshed_time');
                    delete_option('linkedin_company_posts_data');
                    Linkedin_Company_Posts::$helper->get_company_posts($this->options['company-id'], $this->options['limit']);
                }
            }
        }
    }

    /**
     * Handles resetting the LinkedIn Post data request
     * @return void
     */
    public function linkedin_company_post_reset_handler()
    {
        delete_option('linkedin_company_posts_data');
        Linkedin_Company_Posts::$helper->notification(__('Your LinkedIn Post has been reset!', 'company-posts-for-linkedin'), 1);
        echo '<script>window.location = "' . esc_url($this->admin_url) . '";</script>';
    }
}