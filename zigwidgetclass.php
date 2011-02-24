<?php
/*
Plugin Name: ZigWidgetClass
Plugin URI: http://www.zigpress.com/wordpress/plugins/zigwidgetclass/
Description: Lets you add a custom class to each widget instance.
Version: 0.1
Author: ZigPress
Requires at least: 3.0.5
Tested up to: 3.1
Author URI: http://www.zigpress.com/
License: GPLv2
*/


/*
Copyright (c) 2011 ZigPress

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation Inc, 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
*/


/*
ZigPress PHP code uses Whitesmiths indent style: http://en.wikipedia.org/wiki/Indent_style#Whitesmiths_style
*/


# DEFINE PLUGIN


if (!class_exists('ZigWidgetClass'))
	{
	class ZigWidgetClass
		{
		public function __construct()
			{
			global $wp_version;
			if (version_compare(phpversion(), '5.0.0', '<')) { wp_die(__('ZigWidgetClass requires PHP 5.0.0 or newer. Please update your server.', 'zigwidgetclass')); }
			if (version_compare($wp_version, '3.0.5', '<')) { wp_die(__('ZigWidgetClass requires WordPress 3.0.5 or newer. Please update your installation.', 'zigwidgetclass')); }
			add_filter('widget_form_callback', array($this, 'Form'), 10, 2);
			add_filter('widget_update_callback', array($this, 'Update'), 10, 2);
			add_filter('dynamic_sidebar_params', array($this, 'Apply'));
			}


		function Form($instance, $widget) 
			{
			if (!isset($instance['zigclass'])) $instance['zigclass'] = null;
			?>
			<p>
			<label for='widget-<?php echo $widget->id_base?>-<?php echo $widget->number?>-zigclass'>ZigClass:</label>
			<input type='text' name='widget-<?php echo $widget->id_base?>[<?php echo $widget->number?>][zigclass]' id='widget-<?php echo $widget->id_base?>-<?php echo $widget->number?>-zigclass' class='widefat' value='<?php echo $instance['zigclass']?>'/>
			</p>
			<?php
			return $instance;
			}


		function Update($instance, $new_instance) 
			{
			$instance['zigclass'] = $new_instance['zigclass'];
			return $instance;
			}


		function Apply($params) 
			{
			global $wp_registered_widgets;
			$widget_id = $params[0]['widget_id'];
			$widget = $wp_registered_widgets[$widget_id];
			$option_name = get_option($widget['callback'][0]->option_name);
			$number = $widget['params'][0]['number'];
			if (isset($option_name[$number]['zigclass']) && !empty($option_name[$number]['zigclass'])) 
				{
				$params[0]['before_widget'] = preg_replace('/class="/', "class=\"{$option_name[$number]['zigclass']} ", $params[0]['before_widget'], 1);
				}
			return $params;
			}


		} # end of class


	}
else
	{
	exit('Class ZigWidgetClass already declared!');
	}


# INSTANTIATE PLUGIN


$objZigWidgetClass = new ZigWidgetClass();


# EOF
