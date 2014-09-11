<?php
/*
Plugin Name: ZigWidgetClass
Plugin URI: http://www.zigpress.com/plugins/zigwidgetclass/
Description: Lets you add a custom class to each widget instance.
Version: 0.7
Author: ZigPress
Requires at least: 3.6
Tested up to: 4.0
Author URI: http://www.zigpress.com/
License: GPLv2
*/


/*
Copyright (c) 2011-2014 ZigPress

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


# DEFINE PLUGIN


if (!class_exists('zigwidgetclass')) {


	class zigwidgetclass
	{


		public $plugin_folder;
		public $plugin_directory;


		public function __construct() {
			$this->plugin_folder = get_bloginfo('wpurl') . '/' . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)) . '/';
			$this->plugin_directory = WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)) . '/';
			global $wp_version;
			if (version_compare(phpversion(), '5.2.4', '<')) wp_die('ZigWidgetClass requires PHP 5.2.4 or newer. Please update your server.');
			if (version_compare($wp_version, '3.6', '<')) wp_die('ZigWidgetClass requires WordPress 3.6 or newer. Please update your installation.');
			add_action('admin_enqueue_scripts', array($this, 'action_admin_enqueue_scripts'));
			add_action('admin_menu', array($this, 'action_admin_menu'));
			add_filter('widget_form_callback', array($this, 'filter_widget_form_callback'), 10, 2);
			add_filter('widget_update_callback', array($this, 'filter_widget_update_callback'), 10, 2);
			add_filter('dynamic_sidebar_params', array($this, 'filter_dynamic_sidebar_params'));
			add_filter('plugin_row_meta', array($this, 'filter_plugin_row_meta'), 10, 2 );
		}


		/* ACTIONS */


		public function action_admin_enqueue_scripts() {
			wp_enqueue_style('zigwidgetclassadmin', $this->plugin_folder . 'css/admin.css', false, date('Ymd'));
		}
	
	
		public function action_admin_menu() {
			add_options_page('ZigWidgetClass', 'ZigWidgetClass', 'manage_options', 'zigwidgetclass-options', array($this, 'admin_page_options'));
		}
	
	
		/* FILTERS */


		function filter_widget_form_callback($instance, $widget) {
			if (!isset($instance['zigclass'])) $instance['zigclass'] = null;
			?>
			<p>
			<label for='widget-<?php echo $widget->id_base?>-<?php echo $widget->number?>-zigclass'>CSS Class:</label>
			<input class='widefat' type='text' name='widget-<?php echo $widget->id_base?>[<?php echo $widget->number?>][zigclass]' id='widget-<?php echo $widget->id_base?>-<?php echo $widget->number?>-zigclass' value='<?php echo $instance['zigclass']?>'/>
			</p>
			<?php
			return $instance;
		}


		function filter_widget_update_callback($instance, $new_instance) {
			$instance['zigclass'] = $new_instance['zigclass'];
			return $instance;
		}


		function filter_dynamic_sidebar_params($params) {
			global $wp_registered_widgets;
			$widget_id = $params[0]['widget_id'];
			$widget = $wp_registered_widgets[$widget_id];

			# We're looking for the option_name (in wp_options) of where this widget's data is stored
			# Default location
			if (!($ouroptionname = $widget['callback'][0]->option_name)) {
				# Alternate location of option name if widget logic installed
				if (!($ouroptionname = $widget['callback_wl_redirect'][0]->option_name)) {
					# Alternate location of option name if widget context installed
					$ouroptionname = $widget['callback_original_wc'][0]->option_name; 
				}
			}
			$option_name = get_option($ouroptionname);

			# within the option, we're looking for the data for the right widget number
			# that's where we'll find the zigclass value if it exists
			$number = $widget['params'][0]['number'];
			if (isset($option_name[$number]['zigclass']) && !empty($option_name[$number]['zigclass'])) {
				# add our class to the start of the existing class declaration
				$params[0]['before_widget'] = preg_replace('/class="/', "class=\"{$option_name[$number]['zigclass']} ", $params[0]['before_widget'], 1);
			} else {
				# No zigclass found - but if we're using wp page widget, there could be one elsewhere
				
				# WP Page Widget plugin fix - the function exists test works because my plugin's name starts with Z so will always be loaded after WP Page Widget.
				# If another plugin also uses this function name then you've got bigger problems than adding a class to a widget...
				if (function_exists('pw_filter_widget_display_instance')) {
					global $post;
					$ouroptionname = 'widget_' . $post->ID . '_' . $widget['callback'][0]->id_base;
					# did we find a wp page widget option for this post					
					if ($option_name = get_option($ouroptionname)) {
						$number = $widget['params'][0]['number'];
						if (isset($option_name[$number]['zigclass']) && !empty($option_name[$number]['zigclass'])) {
							$params[0]['before_widget'] = preg_replace('/class="/', "class=\"{$option_name[$number]['zigclass']} ", $params[0]['before_widget'], 1);
						}
					}
				}
			}
			return $params;
		}


		public function filter_plugin_row_meta($links, $file) {
			$plugin = plugin_basename(__FILE__);
			$newlinks = array(
				'<a target="_blank" href="http://www.zigpress.com/donations/">Donate</a>',
				'<a href="' . get_admin_url() . 'options-general.php?page=zigwidgetclass-options">Information</a>',
			);
			if ($file == $plugin) return array_merge($links, $newlinks);
			return $links;
		}


		# ADMIN CONTENT
	
	
		public function admin_page_options() {
			if (!current_user_can('manage_options')) { wp_die('You are not allowed to do this.'); }
			?>
			<div class="wrap zigwidgetclass-admin">
			<h2>ZigWidgetClass - Information</h2>
			<div class="wrap-left">
			<div class="col-pad">
			<p>ZigWidgetClass adds a free text field labelled 'CSS Class' to each widget control form on your widget admin page. Enter a CSS class name in the box and it will be added to the classes that WordPress applies to that widget instance. </p>
			<p>It has been tested and verified to work with the Widget Logic plugin, the Widget Context plugin and the WP Page Widgets plugin. If you have problems getting it to work with one of those plugins, make sure you are using the latest version(s).</p>
			<p>It only works with widgets that were created by extending the built-in multi-widget class. If it appears not to work on a certain widget, that widget is probably not a multi-widget. </p>
			<p>Also, if you have trouble getting it to work with the WP Page Widgets plugin, you should create and save each page widget first, before adding the CSS class, then save again.</p>
			</div><!--col-pad-->
			</div><!--wrap-left-->
			<div class="wrap-right">
			<table class="widefat donate" cellspacing="0">
			<thead>
			<tr><th>Support this plugin!</th></tr>
			</thead>
			<tr><td>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="GT252NPAFY8NN">
			<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
			<p>If you find ZigWidgetClass useful, please keep it free and actively developed by making a donation.</p>
			<p>Suggested donation: &euro;10 or an amount of your choice. Thanks!</p>
			</td></tr>
			</table>
			<table class="widefat donate" cellspacing="0">
			<thead>
			<tr><th><img class="icon floatRight" src="<?php echo $this->plugin_folder?>images/icon-16x16-zp.png" alt="Yes" title="Yes" />Brought to you by ZigPress</th></tr>
			</thead>
			<tr><td>
			<p><a href="http://www.zigpress.com/">ZigPress</a> is engaged in WordPress consultancy, solutions and research. We have also released a number of free plugins to support the WordPress community.</p>
			<p><a target="_blank" href="http://www.zigpress.com/plugins/zigwidgetclass/"><img class="icon" src="<?php echo $this->plugin_folder?>images/cog.png" alt="ZigWidgetClass WordPress plugin by ZigPress" title="ZigWidgetClass WordPress plugin by ZigPress" /> ZigWidgetClass page</a></p>
			<p><a target="_blank" href="http://www.zigpress.com/plugins/"><img class="icon" src="<?php echo $this->plugin_folder?>images/plugin.png" alt="WordPress plugins by ZigPress" title="WordPress plugins by ZigPress" /> Other ZigPress plugins</a></p>
			<p><a target="_blank" href="http://www.facebook.com/zigpress"><img class="icon" src="<?php echo $this->plugin_folder?>images/facebook.png" alt="ZigPress on Facebook" title="ZigPress on Facebook" /> ZigPress on Facebook</a></p>
			<p><a target="_blank" href="http://twitter.com/ZigPress"><img class="icon" src="<?php echo $this->plugin_folder?>images/twitter.png" alt="ZigPress on Twitter" title="ZigPress on Twitter" /> ZigPress on Twitter</a></p>
			</td></tr>
			</table>
			</div><!--wrap-right-->
			<div class="clearer">&nbsp;</div>
			</div><!--/wrap-->
			<?php
		}
		
		
	} # END OF CLASS


} else {
	wp_die('Namespace clash! Class zigwidgetclass already declared.');
}


# INSTANTIATE PLUGIN


$zigwidgetclass = new zigwidgetclass();


# EOF
