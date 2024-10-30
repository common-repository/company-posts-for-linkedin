<?php

/**
 * All helper functions for the plugin
 * @link       https://brainstation-23.com
 * @since      1.0.0
 */

/**
 *
 * @package    Company-Posts-for-LinkedIn
 * @subpackage Company-Posts-for-LinkedIn/includes
 * @author     Brain Station 23 Ltd. <elearning@brainstation-23.com>
 */
class Linkedin_Company_Posts_Helper
{
    protected $client_id = '';
    protected $token_life = 0;
    protected $options = array();
    protected $redirect_url = '';
    protected $auth_options = array();
    protected $admin_url;
    public $images = [];

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
        $this->options = get_option($this->plugin_name);
        $this->auth_options = get_option($this->plugin_name . '_authkey');
        $this->admin_url = admin_url('options-general.php?page=' . $this->plugin_name);
        $this->redirect_url = $this->admin_url;
        $this->client_id = isset($this->options['client-id']) ? $this->options['client-id'] : '';
        $this->token_life();
    }

    /**
     * Constructs a string detailing how long the access token remains valid for
     * @return void Difference between access token & the current time in milliseconds
     */

    private function token_life()
    {

        $life = 0;

        if (!empty($this->auth_options['expires_in'])) {
            $life = intval($this->auth_options['expires_in']) - strtotime(gmdate('Y-m-d H:m:s'));
        }

        $this->token_life = $life;

    }


    /**
     * function to get access token uses the code from the previous step
     * @return int
     */

    public function get_token_life()
    {
        return $this->token_life;
    }

    /**
     * function to authentication options
     * @return array|mixed
     */

    public function get_auth_option()
    {
        return $this->auth_options;
    }

    /**
     * function to get option field values
     * @return array|mixed
     */

    public function get_option()
    {
        return $this->options;
    }

    /**
     * Fetches the API access token
     * @param string $code Linkedin API authentication code
     * @return array | false
     */

    public function get_access_token($code)
    {
        // build the url
        $params = array(
            'grant_type' => 'authorization_code',
            'client_id' => $this->client_id,
            'client_secret' => isset($this->options['client-secret']) ? $this->options['client-secret'] : '',
            'code' => $code,
            'redirect_uri' => $this->redirect_url,
        );
        $url = Linkedin_Company_Posts::BASE_URL . 'oauth/v2/accessToken?' . http_build_query($params);

        // get the json
        $json = $this->get_remote_json($url, 'Failed to make request for access token.');

        // if there's something wrong with the access token, say so
        if (!isset($json['access_token']) || 5 >= strlen($json['access_token'])) {

            $this->notification(__('Did not receive an access token.', 'linkedin-company-posts'));

            return false;

        }

        return $json;

    }

    /**
     * GETs remote json data
     * @param string $url url from which to fetch the data
     * @param string $error_message Text error message
     * @return array | false
     */

    private function get_remote_json($url, $error_message)
    {

        $access_token = '';
        if (isset($this->auth_options['access_token']) && !empty($this->auth_options['access_token'])) {
            $access_token = $this->auth_options['access_token'];
        }
        // make the GET request
        $response = wp_remote_get($url, [
            "headers" =>
                [
                    "Content-Type" => "application/json",
                    "Accept" => "application/json",
                    "Linkedin-Version" => Linkedin_Company_Posts::LINKEDIN_VERSION,
                    "X-Restli-Protocol-Version" => Linkedin_Company_Posts::PROTOCOL_VERSION,
                    "Authorization" => "Bearer " . $access_token
                ]
        ]);

        // try to parse it
        try {

            $json = json_decode($response['body'], 1);

            // check for errors
            if (isset($json['error'])) {
                throw new Error($json['error_description']);
            }

            return $json;

            // handle errors
        } catch (Exception $ex) {

            $this->notification($ex->getMessage());

            return false;

        }

    }

    /**
     * function to fetch the company posts from LinkedIn
     * @param int $company_id
     * @param int $limit
     * @return mixed
     */

    public function get_company_posts($company_id = null, $limit = 3)
    {
        // Get the last refreshed time from the WordPress options
        $current_time = time();
        $last_refreshed_time = get_option('linkedin_company_last_refreshed_time');
        $refresh_interval = is_numeric($this->options['auto-linkedin_company-refresh-interval']) ? $this->options['auto-linkedin_company-refresh-interval'] : 1800;
        $next_refresh_time = $last_refreshed_time + $refresh_interval;

        if ($current_time >= $next_refresh_time) {
            return $this->fetch_and_save_linkedin_posts($company_id, $limit);
        }

        $linkedin_posts = get_option('linkedin_company_posts_data');

        if (!$linkedin_posts || !count($linkedin_posts) > 0) {
            return $this->fetch_and_save_linkedin_posts($company_id, $limit);
        } else {
            return $linkedin_posts;
        }

    }

    /**
     * Handle fetch and save company posts data request
     * @param mixed $company_id
     * @param mixed $limit
     * @return array<array>|bool
     */

    private function fetch_and_save_linkedin_posts($company_id = null, $limit = 3)
    {
        // Your LinkedIn API code to fetch the latest company posts goes here
        // Store the posts in an array variable called $linkedin_posts
        try {
            $limit = $limit == "" ? 3 : $limit;
            if ($company_id == null) {
                $url_encoded_company_id = urlencode("urn:li:organization:" . $this->options['company-id']);
            } else {
                $url_encoded_company_id = urlencode("urn:li:organization:" . $company_id);
            }

            $url = Linkedin_Company_Posts::API_URL . "posts?author=$url_encoded_company_id&q=author&count=$limit";
            $response = wp_remote_get(
                $url,
                [
                    "headers" =>
                        [
                            "Content-Type" => "application/json",
                            "Accept" => "application/json",
                            "Linkedin-Version" => Linkedin_Company_Posts::LINKEDIN_VERSION,
                            "X-Restli-Protocol-Version" => Linkedin_Company_Posts::PROTOCOL_VERSION,
                            "Authorization" => "Bearer " . $this->auth_options['access_token']
                        ]
                ]
            );

            $data = json_decode($response['body'], true);

            $linkedin_posts = [];
            $characterLength = isset($this->options['character-length']) && is_numeric($this->options['character-length']) ? (int)$this->options['character-length'] : 30;

            if (is_array($data) && isset($data['elements'])) {

                foreach ($data['elements'] as $post) {

                    if (isset($post['reshareContext'])) {
                        $parentUrn = $post['reshareContext']['root'];
                        $parent_post = $this->fetchParentPost($parentUrn);
                        if ($parent_post) {
                            $linkedin_posts[] = $this->setPostData($parent_post, $characterLength);
                        }
                    } else {
                        $linkedin_posts[] = $this->setPostData($post, $characterLength);
                    }

                }

            }

        } catch (Exception $exception) {
            $this->notification($exception->getMessage());

            return false;
        }
        // Save the posts in the WordPress options to prevent LinkedIn API rate limit
        $current_time = time();
        delete_option('linkedin_company_posts_data');
        update_option('linkedin_company_last_refreshed_time', $current_time);
        update_option('linkedin_company_posts_data', $linkedin_posts);

        return $linkedin_posts;
    }

    /**
     * @param $urn
     * @return false|mixed
     */

    private function fetchParentPost($urn = null)
    {

        try {

            if ($urn == null) {
                return false;
            }

            $urn = urlencode($urn);

            $url = Linkedin_Company_Posts::API_URL . "posts/$urn";

            $response = wp_remote_get(
                $url,
                [
                    "headers" =>
                        [
                            "Content-Type" => "application/json",
                            "Accept" => "application/json",
                            "Linkedin-Version" => Linkedin_Company_Posts::LINKEDIN_VERSION,
                            "X-Restli-Protocol-Version" => Linkedin_Company_Posts::PROTOCOL_VERSION,
                            "Authorization" => "Bearer " . $this->auth_options['access_token']
                        ]
                ]
            );

            $data = json_decode($response['body'], true);

            if (isset($data['code']) && $data['code'] == "NOT_FOUND") {
                return false;
            }

            return $data;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param $post
     * @param $characterLength
     * @return array
     */

    private function setPostData($post, $characterLength)
    {

        $regex = "/media\.licdn\.com\/dms\/image/";

        $id = $post['id'];

        $published_at = $post['publishedAt'];
        $url = Linkedin_Company_Posts::BASE_URL . "feed/update/" . $id;
        $content = $post['commentary'];
        $content = (strlen($content) > $characterLength) ? substr($content, 0, $characterLength) . "..." : $content;
        $single_media = plugins_url('../public/images/linkedin-bg.jpg',__FILE__);
		
        $urn = null;
        if (isset($post['content']['multiImage'])) {
            $urn = $post['content']['multiImage']['images'][0]['id'];
        } elseif (isset($post['content']['media']['id'])) {
            $urn = $post['content']['media']['id'];
        }

        $media = $this->getSingleImage($urn);

        if ($media !== false) {
            $single_media = preg_match($regex, $media) ? $media : plugins_url('../public/images/linkedin-bg.jpg',__FILE__);
        }

        return [
            'id' => $id,
            'published_at' => $published_at,
            'url' => $url,
            'content' => $content,
            'media' => $single_media
        ];
    }

    /**
     *  getSingleImage function to get a single image from LinkedIn posts
     * @param mixed $urn
     * @return mixed
     */

    private function getSingleImage($urn = null)
    {

        if ($urn == null) {
            return false;
        }

        $urn = urlencode($urn);

        $url = Linkedin_Company_Posts::API_URL . "images/$urn";

        $response = wp_remote_get(
            $url,
            [
                "headers" =>
                    [
                        "Content-Type" => "application/json",
                        "Accept" => "application/json",
                        "Linkedin-Version" => Linkedin_Company_Posts::LINKEDIN_VERSION,
                        "X-Restli-Protocol-Version" => Linkedin_Company_Posts::PROTOCOL_VERSION,
                        "Authorization" => "Bearer " . $this->auth_options['access_token']
                    ]
            ]
        );

        $data = json_decode($response['body'], true);

        return $data['downloadUrl'] ?? false;
    }


    /**
     * Summary of refresh_lcp_access_token
     * Refresh the access token if it's expired
     * @return bool
     */

    public function refresh_lcp_access_token()
    {

        $token = $this->get_refresh_access_token();

        if (false === $token) {
            return;
        }


        // calculate when the token will expire
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

        return true;
    }


    /**
     * Summary of get_refresh_access_token
     * get the access token that was refreshed recently after request
     * @return array|bool
     */

    private function get_refresh_access_token()
    {

        $token_options = get_option($this->plugin_name . '_authkey');
        $key_options = get_option($this->plugin_name);

        $params = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $token_options['refresh_token'],
            'client_id' => $key_options['client-id'],
            'client_secret' => $key_options['client-secret'],
        );

        $url = Linkedin_Company_Posts::BASE_URL . 'oauth/v2/accessToken?' . http_build_query($params);

        // get the json
        $json = $this->get_remote_json($url, 'Failed to make request for access token.');

        // if there's something wrong with the access token, say so
        if (!isset($json['access_token']) || 5 >= strlen($json['access_token'])) {

            $this->notification(__('Did not receive an access token.', 'linkedin-company-posts'));

            return false;

        }

        return $json;

    }


    /**
     * Adds a notification to the dashboard
     */
    public function notification($text, $update = 0)
    {

        $class = $update ? 'updated' : 'error';

        $message = '<div class="notice is-dismissible ' . esc_attr($class) . '">';
        $message .= '<p>' . esc_html($text) . '</p>';
        $message .= '</div>';
        
        echo wp_kses_post($message);
    }

}