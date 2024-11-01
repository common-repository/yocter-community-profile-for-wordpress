<?php

/*
Plugin Name: Yocter Community Profile for WordPress Widget
Version: 1.1
Plugin URI: http://yocter.com/
Description: Display the latest update from any public Profile hosted at any Yocter Community.
Author: Yocter.com
Author URI: http://yocter.com/
*/

/*  
		Check yocter.com, your Yocter Community or WordPress.org for new updates.

		Copyright 2009  Yocter.com | Godfried van Loo

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


define('MAGPIE_CACHE_AGE', 120);

$profile_options['widget_fields']['profileid'] = array('label'=>'<b>Profile ID</b> (number):', 'type'=>'text', 'default'=>'');

$profile_options['prefix'] = 'profile';

// Display Profile Widget
function profile_show($profileid,$stagetype) {
	global $profile_options;

print '
<script type="text/javascript"><!-- //Yocter Community Profile Gadget by yocter.com
var mnblch_profileid = "'.$profileid.'";
//--></script>
<script type="text/javascript" src="http://static.yocter.net/gadgets/bloggadgets/bloggadget1_profile.js"></script>';

}

// Profile widget stuff
function widget_profile_init() {

	if ( !function_exists('register_sidebar_widget') )
		return;
	
	$check_options = get_option('widget_profile');
  if ($check_options['number']=='') {
    $check_options['number'] = 1;
    update_option('widget_profile', $check_options);
  }
  
	function widget_profile($args, $number = 1) {

		global $profile_options;
		
		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);

		// Each widget can store its own options. We keep strings here.
		include_once(ABSPATH . WPINC . '/rss.php');
		$options = get_option('widget_profile');
		
		// fill options with default values if value is not set
		$item = $options[$number];
		foreach($profile_options['widget_fields'] as $key => $field) {
			if (! isset($item[$key])) {
				$item[$key] = $field['default'];
			}
		}
		

		// These lines generate our output.
		echo $before_widget;
  	profile_show($item['profileid'], $item['stagetype']);
  	echo $after_widget;
	}

	// This is the function that outputs the form to let the users edit
	// the widget's title. It's an optional feature that users cry for.
	function widget_profile_control($number) {
	
		global $profile_options;

		// Get our options and see if we're handling a form submission.
		$options = get_option('widget_profile');
		if ( isset($_POST['profile-submit']) ) {

			foreach($profile_options['widget_fields'] as $key => $field) {
				$options[$number][$key] = $field['default'];
				$field_name = sprintf('%s_%s_%s', $profile_options['prefix'], $key, $number);

				if ($field['type'] == 'text') {
					$options[$number][$key] = strip_tags(stripslashes($_POST[$field_name]));
				} elseif ($field['type'] == 'checkbox') {
					$options[$number][$key] = isset($_POST[$field_name]);
				}
			}

			update_option('widget_profile', $options);
		}

		foreach($profile_options['widget_fields'] as $key => $field) {
			
			$field_name = sprintf('%s_%s_%s', $profile_options['prefix'], $key, $number);
			$field_checked = '';
			if ($field['type'] == 'text') {
				$field_value = htmlspecialchars($options[$number][$key], ENT_QUOTES);
			} elseif ($field['type'] == 'checkbox') {
				$field_value = 1;
				if (! empty($options[$number][$key])) {
					$field_checked = 'checked="checked"';
				}
			}
			
			printf('<p style="text-align:right;" class="profile_field"><label for="%s">%s <input id="%s" name="%s" type="%s" value="%s" class="%s" %s /></label></p>',
				$field_name, __($field['label']), $field_name, $field_name, $field['type'], $field_value, $field['type'], $field_checked);
		}

		echo '<input type="hidden" id="profile-submit" name="profile-submit" value="1" />';
	}
	
	function widget_profile_setup() {
		$options = $newoptions = get_option('widget_profile');
		
		if ( isset($_POST['profile-number-submit']) ) {
			$number = (int) $_POST['profile-number'];
			$newoptions['number'] = $number;
		}
		
		if ( $options != $newoptions ) {
			update_option('widget_profile', $newoptions);
			widget_profile_register();
		}
	}
		
	function widget_profile_register() {
		
		$options = get_option('widget_profile');
		$dims = array('width' => 300, 'height' => 300);
		$class = array('classname' => 'widget_profile');

		for ($i = 1; $i <= 9; $i++) {
			$name = sprintf(__('Yocter Community Profile'), $i);
			$id = "profile-$i"; // Never never never translate an id
			wp_register_sidebar_widget($id, $name, $i <= $options['number'] ? 'widget_profile' : /* unregister */ '', $class, $i);
			wp_register_widget_control($id, $name, $i <= $options['number'] ? 'widget_profile_control' : /* unregister */ '', $dims, $i);
		}
		
		add_action('sidebar_admin_setup', 'widget_profile_setup');
	}

	widget_profile_register();
}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'widget_profile_init');
?>