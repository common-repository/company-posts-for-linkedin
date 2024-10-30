<?php

/**
 * The file that defines the metabox component of the plugin settings.
 * A class definition that includes attributes and functions used in the admin area settings.
 * @link       https://brainstation-23.com
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 * @since      1.0.0
 * @package    Company-Posts-for-LinkedIn
 * @subpackage Company-Posts-for-LinkedIn/includes
 * @author     Brain Station 23 Ltd. <elearning@brainstation-23.com>
 */

class Linkedin_Company_Posts_Metaboxes
{

    protected $body = '';
    protected $sidebar = '';
    protected $version = 0.1;

    /**
     * Queues a metabox for the output
     * @param string $section Slug for the section
     * @param string $title Title for the metabox title bar
     * @param boolean $sidebar Boolean to determine whether to put it in the body or sidebar
     * @param string $inside HTML string that goes inside the metabox
     */

    public function add_metabox($section, $title, $sidebar, $inside)
    {
        // Localize the toggle text
        $toggle = __('Toggle panel:');

        $allowed_html = array(
            'div' => array(
                'id' => array(),
                'class' => array(),
            ),
            'button' => array(
                'type' => array(),
                'class' => array(),
                'aria-expanded' => array(),
            ),
            'span' => array(
                'class' => array(),
            ),
            'a' => array(
                'href' => array(),
                'target' => array(),
                'class' => array(),
            ),
            'h2' => array(
                'class' => array(),
            ),
            'input' => array(
                'type' => array(),
                'name' => array(),
                'value' => array(),
                'id' => array(),
                'placeholder' => array(),
                'required' => array(),
                'readonly' => array(),
                'onclick' => array(),
            ),
            'table' => array(
                'class' => array(),
                'role' => array(),
            ),
            'tbody' => array(),
            'tr' => array(),
            'th' => array(
                'scope' => array(),
            ),
            'td' => array(),
            'p' => array(),
            'label' => array(),
            // More tags and attributes as needed...
        );

        // Start building the metabox HTML
        $metabox = '<div id="' . esc_html($section) . '" class="postbox">';
        $metabox .= '<button type="button" class="handlediv button-link" aria-expanded="true">';
        $metabox .= '<span class="screen-reader-text">' . esc_html($toggle) . ' ' . esc_html($title) . '</span>';
        $metabox .= '<span class="toggle-indicator" aria-hidden="true"></span>';
        $metabox .= '</button>';
        $metabox .= '<h2 class="hndle ui-sortable-handle">';
        $metabox .= '<span>' . wp_kses($title, $allowed_html) . '</span>';
        $metabox .= '</h2>';
        $metabox .= '<div class="inside">';
        $metabox .= wp_kses($inside, $allowed_html);
        $metabox .= '</div>';
        $metabox .= '</div>';

        // If $sidebar is true, add this metabox there
        if ($sidebar) {
            $this->sidebar .= $metabox;
        } else {
            // Otherwise, add it to the body by default
            $this->body .= $metabox;
        }
    }


    /**
     * Adds a metabox with fields from the Settings API
     * @param string $section Slug for the section
     * @param string $title Title for the metabox title bar
     * @param boolean $sidebar Boolean to determine whether to put it in the body or sidebar
     */

    public function add_settings_metabox($section, $title, $sidebar = false)
    {

        // get the settings fields
        $inside = $this->get_settings_html($section);

        // add the metabox
        $this->add_metabox($section, $title, $sidebar, $inside);

    }

    /**
     * function to get the settings fields for a section
     * @param mixed $section Slug for the section
     * @return bool|string
     */

    public function get_settings_html($section)
    {

        // get the settings fields
        ob_start();
        settings_fields(esc_html($section));
        do_settings_sections(esc_html($section));

        return ob_get_clean();

    }

    /**
     * Adds a "publish" style metabox to the sidebar
     * @param string $title Title for the metabox title bar
     */

    public function add_publish_metabox($title, $inside)
    {
        // Localize the "Save" string
        $save = __('Save');
        $insideData = $inside;

        // Build the metabox inside HTML
        $inside = '<div class="submitbox" id="submitpost">';
        $inside .= '<div id="minor-publishing">';
        $inside .= '<div id="misc-publishing-actions">';
        $inside .= '<div class="misc-pub-section">';
        $inside .= $insideData; // You can't replace $inside here; it's the content you pass to the function.
        $inside .= '</div>';
        $inside .= '<div class="clear"></div>';
        $inside .= '</div>';
        $inside .= '<div id="major-publishing-actions">';
        $inside .= '<div id="publishing-action">';
        $inside .= '<span class="spinner"></span>';
        $inside .= '<input name="original_publish" type="hidden" id="original_publish" value="Publish">';
        $inside .= '<input type="submit" name="submit" id="submit" class="button button-primary button-large" value="' . esc_attr($save) . '">';
        $inside .= '</div>';
        $inside .= '<div class="clear"></div>';
        $inside .= '</div>';
        $inside .= '</div>';
        $inside .= '</div>';

        // Add the metabox
        $this->add_metabox('submitdiv', $title, 1, $inside);
    }

    /**
     * Outputs the metaboxes
     * @return string
     */

    public function output()
    {
        $output = '<div id="post-body" class="metabox-holder columns-2">';
        $output .= '<div id="post-body-content">';
        $output .= $this->body;
        $output .= '</div>';
        $output .= '<div id="postbox-container-1" class="postbox-container">';
        $output .= '<div id="side-sortables" class="meta-box-sortables ui-sortable" style="">';
        $output .= $this->sidebar;
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div>';

        return $output;
    }

}