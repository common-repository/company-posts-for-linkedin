<?php

/**
 * The plugin bootstrap file
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://brainstation-23.com
 * @since             1.0.0
 * @package          Company-Posts-for-Linkedin
 *
 * @wordpress-plugin
 * Plugin Name:       Company Posts for LinkedIn
 * Plugin URI:        https://wordpress.org/plugins/company-posts-for-linkedin
 * Description:       Elevate Your LinkedIn Company's Engagement with Seamless WordPress Integration.
 * Version:           1.0.0
 * Author:            Brain Station 23 Ltd.
 * Author URI:        https://brainstation-23.com
 * Author Email:      elearning@brainstation-23.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       company-posts-for-linkedin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('LINKEDIN_COMPANY_POSTS_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-linkedin-company-posts-activator.php
 */
function linkedin_company_posts_activate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-linkedin-company-posts-activator.php';
    Linkedin_Company_Posts_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-linkedin-company-posts-deactivator.php
 */
function linkedin_company_posts_deactivate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-linkedin-company-posts-deactivator.php';
    Linkedin_Company_Posts_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'linkedin_company_posts_activate');
register_deactivation_hook(__FILE__, 'linkedin_company_posts_deactivate');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-linkedin-company-posts.php';

/**
 * Begins execution of the plugin.
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 * @since    1.0.0
 */

function linkedin_company_posts_run()
{

    $plugin = new Linkedin_Company_Posts();
    $plugin->run();

}

linkedin_company_posts_run();
