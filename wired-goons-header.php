<?php
/*
Plugin Name: Wired Goons Header
Plugin URI: http://wiredgoons.com/wordpress-plugins/wired-goons-header/
Description: Creates options to clean up any installed actions to WP Head and to remove various items for SEO, security, theme additions etc... sets some defaults on install to make things easier.
Author: Pete Dainty @ Wired Goons
Version: 1.0
Author URI: http://wiredgoons.com/
*/

add_action('admin_menu', 'wg_wphead_add_page');

if(!get_option('wg_wphead_settings')) {
	 $cleanDefault = array('feed_links-2' => 'on',
	 	 'feed_links_extra-3' => 'on',
	 	 'index_rel_link-10' => 'on',
	 	 'locale_stylesheet-10' => 'on',
	 	 'parent_post_rel_link-10' => 'on',
	 	 'rsd_link-10' => 'on',
	 	 'start_post_rel_link-10' => 'on',
	 	 'wlwmanifest_link-10' => 'on',
	 	 'wp_generator-10' => 'on',
	 	 'wp_shortlink_wp_head-10' => 'on');
	 add_option('wg_wphead_settings', $cleanDefault);
}

function wg_wphead_add_page() {
    add_options_page('WG Header Settings', 'Wired Goons Header Settings', 'administrator', 'wg_wphead', 'wg_wphead_settings_page');
	add_action( 'admin_init', 'register_wg_wphead_settings' );
}

function register_wg_wphead_settings() {
	 register_setting( 'wg_wphead_settings', 'wg_wphead_settings');

	 if(get_option('wg_wphead_settings') && get_option('wg_wphead_settings') !=""){
	 	add_settings_section('wg_wphead_set_options', 'Currently removed from WP Head', 'wg_wphead_set_text', __FILE__);
	 	foreach(get_option(wg_wphead_settings) as $name => $value){
			add_settings_field('wg_wphead_'.$name, substr($name, 0, strpos($name,'-')) , 'wg_wphead_field', __FILE__, 'wg_wphead_set_options', $args=array($name));
	 	}
	 }

	 add_settings_section('wg_wphead_options', '<br/>Remove more from WP Head', 'wg_wphead_text', __FILE__);
	 global $wp_filter;
	 $whatsInHead[]=$wp_filter['wp_head'];
	 foreach($whatsInHead as $tag => $priority){
	  foreach($priority as $priority => $function){
	  	foreach($function as $name => $properties) {
	 		add_settings_field('wg_wphead_'.$name, $name, 'wg_wphead_field', __FILE__, 'wg_wphead_options', $args=array($name.'-'.$priority));
		}
	  }
	 }
}

function wg_wphead_set_text() {
	echo '<p>The following items are currently removed from the header.</p>';
}

function wg_wphead_text() {
	echo '<p>You can remove the following options from wp_head() - remember to clear cache from any caching plugins you may have installed before checking the result, Typically anything that is unknown will be from a theme or plugin.</p>';
}

function wg_wphead_field($args) {
	$name = $args[0];
	$options = get_option('wg_wphead_settings');
	if($options[$name]) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='$name' name='wg_wphead_settings[$name]' type='checkbox' />\n";
	wg_wphead_whatswhat(substr($name, 0, strpos($name,'-')));
}

function wg_wphead_settings_page() {
	?>
<style type="text.css">
th { width: 300px; font-weight:bold; }
</style>
	<div class="wrap">
	<h2>Wired Goons Header Settings</h2>
	<p>This allows you to check and remove anything that has been loaded into the wp_head() function either by default and from any theme or a plugin as long they have been coded the correct way.</p>
	<form method="post" action="options.php">
	<?php settings_fields( 'wg_wphead_settings' ); ?>
    <?php do_settings_sections( __FILE__ );?>
	<p class="submit">
	<input type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
	</p>
	</form>
	</div>
<?php
}

function wg_wphead_whatswhat($name) {
	$defaultFilters = array(
		'adjacent_posts_rel_link_wp_head' => '&lt;link rel=\'prev\' title=\'Your Site Name\' href=\'http://yoursite.com\' /&gt;<br/>&lt;link rel=\'next\' title=\'Your Site Name\' href=\'http://yoursite.com\' /&gt;<br/>Creates links in the header for the previous and next pages if part of a series',
		'feed_links' => 'Creates links to RSS feeds for posts and comments - generally you just need a site RSS feed',
		'feed_links_extra' => 'Creates links to RSS feeds for categories. tags etc...',
		'index_rel_link' => '&lt;link rel=\'index\' title=\'Your Site Name\' href=\'http://yoursite.com\' /&gt;<br/>This informs a search engine of the page that this is linked from',
		'locale_stylesheet' => '<a href="http://codex.wordpress.org/Function_Reference/locale_stylesheet">Used to create screen, print etc... versions of CSS files</a>',
		'noindex' => '&lt;meta name=\'robots\' content=\'noindex,nofollow\' /&gt;<br/>This tells search engines not to index your pages - normally added to admin and log in pages so don\'t disable it unless you have good reason',
		'parent_post_rel_link' => '&lt;link rel=\'up\' title=\'Hello world!\' href=\'http://yoursite.com/uncategorized/hello-world\' /&gt;<br/>Defines a parent page if part of a heirarchy',
		'rsd_link' => '&lt;link rel=\"EditURI\" type=\"application/rsd+xml\" title=\"RSD\" href=\"http://yoursite.com/xmlrpc.php?rsd\" /&gt;<br/>Allows remote posting, disable for better security if you\'re not using this feature',
		'rel_canonical' => '&lt;link rel=\'canonical\' href=\'http://yoursite.com/uncategorized/hello-world\' /&gt;<br/>Creates a link to the permanent page so if you use dynamic URLs e.g. for tracking the search engines won\'t see this as duplicate content',
		'start_post_rel_link' => '&lt;link rel=\'start\' title=\'Hello world!\' href=\'http://yoursite.com/uncategorized/hello-world\' /&gt;<br/>Refers to the first page of a series of pages',
		'wlwmanifest_link' => '&lt;link rel=\"wlwmanifest\" type=\"application/wlwmanifest+xml\" href=\"http://yoursite.com/wp-includes/wlwmanifest.xml\" /&gt;<br/>This displays various information about your blog such as URLs that you may not want to be visable, disable for better security',
		'wp_enqueue_scripts' => '<a href="http://codex.wordpress.org/Function_Reference/wp_enqueue_script">Used to call in additional Javascript</a>',
		'wp_generator' => '&lt;meta name=\"generator\" content=\"WordPress X.X.X\" /&gt;<br/>Tells the world your wordpress version - remove for better security',
		'wp_print_styles' => '<a href="http://codex.wordpress.org/Function_Reference/wp_enqueue_style">Creates a hook to add CSS style sheets</a>',
		'wp_print_head_scripts' => 'Generates CSS and Javascript files, do not disable unless you want to remove all scripts',
		'wp_shortlink_wp_head' => '&lt;link rel=\'shortlink\' href=\'http://yoursite.com/?p=1\' /&gt;<br/>Points to a shortened version of your URL'
	);

	if(array_key_exists($name, $defaultFilters)) {
		echo "</td><td>".$defaultFilters[$name];
	} else {
		echo '</td><td><i>Unknown</i> <a href="http://blekko.com/ws/'.$name.'">Search Blekko for <i>'.$name.'</i>.</a>';
	}
}

if (get_option('wg_wphead_settings') && get_option('wg_wphead_settings') != "") {
	foreach(get_option('wg_wphead_settings') as $action => $value){
		remove_action('wp_head',substr($action, 0, strpos($action,'-')),substr(stristr($action,'-'),1));
	}
}

?>