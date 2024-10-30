<?php

/**
 * @link       https://brainstation-23.com
 * @since      1.0.0
 * @package    Company-Posts-for-LinkedIn
 * @subpackage Company-Posts-for-LinkedIn/includes
 * @author     Brain Station 23 Ltd. <elearning@brainstation-23.com>
 */
class Linkedin_Company_Posts_i18n
{
    /**
     * Load the plugin text domain for translation.
     * @since    1.0.0
     */
    public function load_plugin_textdomain()
    {

        load_plugin_textdomain(
            'linkedin-company-posts',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
