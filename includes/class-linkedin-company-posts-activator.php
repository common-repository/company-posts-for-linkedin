<?php

/**
 * Fired during plugin activation.
 * This class defines all code necessary to run during the plugin's activation.
 * @since      1.0.0
 * @package    Company-Posts-for-LinkedIn
 * @subpackage Company-Posts-for-LinkedIn/includes
 * @author     Brain Station 23 Ltd. <elearning@brainstation-23.com>
 */

class Linkedin_Company_Posts_Activator
{
    public static function activate()
    {
        add_option('linkedin_company_plugin_do_activation_redirect', true);
    }

}
