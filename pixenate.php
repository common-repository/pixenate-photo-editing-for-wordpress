<?php
/*
Plugin Name: Pixenate
Plugin URI: http://walterhiggins.net/projects/wpplugin.html
Description: This is a photo editing plugin for WordPress
Author: Walter Higgins
Version: 0.2
Author URI: http://walterhiggins.net/
*/

/**
 * @author Walter Higgins
 * @link http://walterhiggins.net/projects/wpplugin.html
 * @license LGPL License http://www.opensource.org/licenses/lgpl-license.html
 */
function pixenate_media_meta($tag,$value)
{

    if (strpos($value->post_mime_type,'image') !== false)
    {
        $url = "../" . PLUGINDIR . "/pixenate/editor.php?id=" . $value->ID . "&image=" . $value->guid ;

        $pixenate_edit_button = "../" . PLUGINDIR . "/pixenate/edit_photo.gif";

        $onclick = "onclick='window.open(\"" . $url . '");return false;\'';

        return "<a href='" . $url . "' " . $onclick . "'><img src='" . $pixenate_edit_button . "'/></a>";

      //return print_r($value);      
    }
}
add_filter('media_meta','pixenate_media_meta',10,2);

?>
