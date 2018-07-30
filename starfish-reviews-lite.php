<?php
/*
Plugin Name: Starfish Reviews Lite
Plugin URI: http://starfishwp.com/reviews/
Description: Encourage your customers to leave 5-star reviews on Google, Facebook, Yellow Pages, and more. See responses, monitor your reputation rating, and create multiple funnels with Starfish, the #1 reputation management plugin for WordPress!
Author: Starfish
Version: 1.1
Author URI: https://starfishwp.com
Copyright: © 2018 Starfish.
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: starfish
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Main StarfishRM clas set up for us
 */
class StarfishRMLite{

	/**
	 * Constructor
	 */
	public function __construct() {
		define( 'SRM_LITE_VERSION', '1.1' );
		define( 'SRM_LITE_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'SRM_LITE_MAIN_FILE', __FILE__ );
		define( 'SRM_LITE_BASE_FOLDER', dirname( __FILE__ ) );

		// Actions
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
    add_action( 'plugins_loaded', array( $this, 'init' ), 0 );
    add_action( 'admin_menu', array( $this, 'srm_plugin_admin_menu' ) );
		add_action( 'single_template', array( $this, 'srm_process_review_page_template' ) );
	}

	/**
	 * Init localisations and hook
	 */
	public function init() {
		//Starfish Settngs Saved
		require_once('inc/starfish-settings-process.php');
		//Register StarFish Review features
		require_once('inc/starfish-reviews.php');
		//Register StarFish Review process
		require_once('inc/starfish-reviews-process.php');
		//Register funnel features
		require_once('inc/starfish-funnels.php');
		// Localisation
		load_plugin_textdomain( 'starfish', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	}

	/**
	 * Add relevant links to plugins page
	 * @param  array $links
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=starfish-reviews' ) . '">' . __( 'Settings', 'starfish' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

	/**
	 * Install default data
	 */
	static function srm_plugin_install(){
		$starfish_lite = plugin_basename( __FILE__ ); // 'starfish_pro'
		if ( is_plugin_active( 'starfish-reviews/starfish-reviews.php' ) ) {
				// Plugin was active, do hook for 'myplugin'
				wp_die('Sorry, but deactive starfish reviews pro version and then activate lite plugin. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
		}
		require_once('inc/default-settings.php');
	}

	/**
	 * Uninstall default data
	 */
	static function srm_plugin_uninstall(){
		require_once('inc/uninstall-default-settings.php');
	}


	/**
	 * Add admin settings menu
	 */
	public function srm_process_review_page_template($single_template) {
			global $post;
			if ($post->post_type == 'funnel' ) {
				$single_template = SRM_LITE_BASE_FOLDER . '/template/srm-page-template.php';
			}
			return $single_template;
	}

  /**
	 * Add admin settings menu
	 */
	public function srm_plugin_admin_menu() {
		add_menu_page( __( 'Starfish Settings', 'starfish' ), __( 'Starfish Reviews', 'starfish' ), 'manage_options', 'starfish-reviews',  array(	$this,	'starfish_settings_plugin_page'), 'dashicons-star-filled', 10 );

		//call register settings function
		add_action( 'admin_init', array(	$this,	'register_srm_plugin_settings') );
	}

	public function register_srm_plugin_settings(){
			register_setting( 'srm-plugin-settings-group', 'srm_destination_name' );
	}

	public function starfish_settings_plugin_page(){
		//Plugin settings
		require_once('inc/starfish-settings.php');
	}


}

new StarfishRMLite();
register_activation_hook( __FILE__, array( 'StarfishRMLite', 'srm_plugin_install' ) );
register_deactivation_hook( __FILE__, array( 'StarfishRMLite', 'srm_plugin_uninstall' ) );
