<?php

/**
 * Fired during plugin deactivation.
 * This class defines all code necessary to run during the plugin's deactivation.
 * @since      1.0.0
 * @package    Company-Posts-for-LinkedIn
 * @subpackage Company-Posts-for-LinkedIn/includes
 * @author     Brain Station 23 Ltd. <elearning@brainstation-23.com>
 */

class Linkedin_Company_Posts_Deactivator
{

    /**
     * @return void
     * Trigger hook when uninstalling the plugin
     */
    public static function deactivate()
    {
        $options = get_option(Linkedin_Company_Posts::$helper->plugin_name);
        if (isset($options['remove-config-when-uninstall']) && $options['remove-config-when-uninstall']) {
            delete_option(Linkedin_Company_Posts::$helper->plugin_name . '_authkey');
            delete_option(Linkedin_Company_Posts::$helper->plugin_name . '_version');
            delete_option(Linkedin_Company_Posts::$helper->plugin_name);
        }

    }

}
