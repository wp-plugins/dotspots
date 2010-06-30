<?php 
/*
 * Plugin Name: DotSpots
 * Version: 1.1
 * Plugin URI: http://dotspots.com/tools/wordpress
 * Description: Enables <a href="http://dotspots.com/">DotSpots</a> on your site. After enabling this plugin visit <a href="options-general.php?page=dotspots.php">the settings page</a> and enter your DotSpots publisher ID. You can <a href="http://dotspots.com/goto/settings/publisher">get a publisher key here</a>.
 * Author: DotSpots, inc.
 * Author URI: http://dotspots.com/
 * Text Domain: dotspots
 */

// Defaults, etc.
define("key_dotspots_id", "dotspots_id", true);
define("dotspots_id_default", "DSxxxxxx", true);

// Create the default key and status
add_option(key_dotspots_id, dotspots_id_default, 'Your DotSpots publisher ID.');

// Initialize the plugin

add_action('admin_init', 'dotspots_admin_init');
function dotspots_admin_init() {
	# Load the localization information
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain('dotspots', 'wp-content/plugins/' . $plugin_dir . '/localizations', $plugin_dir . '/localizations');
	
	// Register our settings
	if ( function_exists('register_setting') ) {
		register_setting('dotspots', key_dotspots_id, '');
	}
}

// Add "DotSpots Configuration" under "Settings"

add_action('admin_menu', 'add_dotspots_option_page');
function add_dotspots_option_page() {
	$plugin_page = add_options_page(__('DotSpots Configuration', 'dotspots'), 'DotSpots', 8, basename(__FILE__), 'dotspots_options_page');
}

// Add "Settings" under the plugin actions

add_action('plugin_action_links_' . plugin_basename(__FILE__), 'dotspots_filter_plugin_actions');
function dotspots_filter_plugin_actions($links) {
	$new_links = array();
	$new_links[] = '<a href="options-general.php?page=dotspots.php">' . __('Settings', 'dotspots') . '</a>';
	
	return array_merge($new_links, $links);
}

// Add "FAQ" under the plugin links

add_filter('plugin_row_meta', 'dotspots_filter_plugin_links', 10, 2);
function dotspots_filter_plugin_links($links, $file)
{
	if ( $file == plugin_basename(__FILE__) )
	{
		$links[] = '<a href="http://dotspots.com/about/faq/">' . __('FAQ', 'dotspots') . '</a>';
	}
	
	return $links;
}


// Options page

function dotspots_options_page() {
	// Postback
	if (isset($_POST['info_update'])) {		
		// Update the publisher ID
		$dotspots_id = $_POST[key_dotspots_id];
		if ($dotspots_id == '')
			$dotspots_id = dotspots_id_default;
		update_option(key_dotspots_id, $dotspots_id);

		// Give an updated message
		echo "<div class='updated fade'><p><strong>" . __('DotSpots settings saved.', 'dotspots') . "</strong></p></div>";
	}

	// Output the options page
	?>
		<div class="wrap">
			
		<h2><?php _e('DotSpots Configuration', 'dotspots'); ?></h2>
		
		<form method="post" action="options-general.php?page=dotspots.php">
			
			<h3><?php _e('Basic Settings', 'dotspots'); ?></h3>
			<table class="form-table" cellspacing="2" cellpadding="5" width="100%">
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_dotspots_id; ?>"><?php _e('DotSpots Publisher ID', 'dotspots'); ?>:</label>
					</th>
					<td>
						<?php
						echo "<input type='text' size='50' ";
						echo "name='".key_dotspots_id."' ";
						echo "id='".key_dotspots_id."' ";
						echo "value='".get_option(key_dotspots_id)."' />\n";
						?>
						<p style="margin: 5px 10px;" class="setting-description"><?php _e('Enter your DotSpots publisher ID.', 'dotspots'); ?></p>
						<p>You'll need a DotSpots publisher ID to use this plugin. You can get one or retrieve your existing one <a href="http://dotspots.com/goto/settings/publisher">from here</a>.</p>
					</td>
				</tr>
			</table>
			<p class="submit">
				<?php if ( function_exists('settings_fields') ) settings_fields('dotspots'); ?>
				<input type="submit" name="info_update" value="<?php _e('Save Changes', 'dotspots'); ?>" />
			</p>
		</div>
		</form>
	<?php
}                  

// Reach widget actions and filters
            
function dotspots_filter_reach_content($arg) {
  return $arg . '<a class="dotspots-reach" href="' . get_permalink() . '"></a>';
}
add_filter('the_content', 'dotspots_filter_reach_content');

function dotspots_action_reach_footer($arg) {
  echo '<script type="text/javascript" src="http://scripts.staging.dotspots.com/reachwidget-wordpress.js"> </script>';
}
add_action('wp_footer', 'dotspots_action_reach_footer');

// Add the the DotSpots scriptlet in the footer
// TODO: Add options for head/body begin?

add_action('wp_footer', 'add_dotspots_script');
function add_dotspots_script() {
	$id = stripslashes(get_option(key_dotspots_id));
	
	if ($id != dotspots_id_default) {
		echo "<!--DotSpots-->\n";
		echo "<script type=\"text/javascript\">\n";
		echo "var __dotSpotsOptions = { publisherId: \"".$id."\" };\n";
		echo "document.write(unescape(\"%3Cscript src='http://scripts.dotspots.com/publisher.js' type='text/javascript'%3E%3C/script%3E\"));\n";
		echo "</script>\n\n";
	} else {
		echo "<!-- DotSpots not configured yet. Visit the settings page and enter your publisher ID. -->\n";
		echo "<script type=\"text/javascript\">\n";
		echo "document.write(unescape(\"%3Cscript src='http://scripts.dotspots.com/publisher.js' type='text/javascript'%3E%3C/script%3E\"));\n";
		echo "</script>\n\n";
	}
}

?>
