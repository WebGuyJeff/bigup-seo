<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Admin Settings Parent.
 *
 * Check to see if the Bigup Web parent admin settings page already exisits and
 * if not, create it. A hook is created for child pages to add to this parent.
 * This class should be used accross all Bigup plugins and themes.
 *
 * @package bigup-seo
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2023, Jefferson Real
 * @license GPL3+
 * @link https://jeffersonreal.uk
 */
class Admin_Settings_Parent {

	/**
	 * Settings page slug to add with add_submenu_page().
	 */
	public $admin_label = 'Bigup Web';

	/**
	 * Settings page slug to add with add_submenu_page().
	 */
	public static $page_slug = 'bigup-web';

	/**
	 * Settings group name called by settings_fields().
	 *
	 * To add multiple sections to the same settings page, all settings
	 * registered for that page MUST BE IN THE SAME 'OPTION GROUP'.
	 */
	public $group_name = 'group_bigup_web_settings';


	/**
	 * Add admin menu option to sidebar
	 */
	public function register_admin_menu() {

		// Add Bigup Web parent menu, if it doesn't exist.
		$parent_menu = menu_page_url( self::$page_slug, false );
		if ( false === ! ! $parent_menu ) {
			add_menu_page(
				$this->admin_label . ' Settings',      // page_title.
				$this->admin_label,                    // menu_title.
				'manage_options',                      // capability.
				self::$page_slug,                      // menu_slug.
				array( &$this, 'create_parent_page' ), // function.
				'dashicons-bigup-fist',                // icon_url.
				4                                      // position.
			);
		}
	}


	/**
	 * Do Action Hook
	 */
	public function bigup_plugin_settings_dashboard_entry() {
		do_action( 'bigup_plugin_settings_dashboard_entry' );
	}


	/**
	 * Create Bigup Web Settings Page
	 */
	public function create_parent_page() {
		?>

		<div class="wrap">
			<h1>
				<span class="dashicons-bigup-logo" style="font-size: 2em; margin-right: 0.2em;"></span>
				Bigup Web Settings
			</h1>

			<section>
				<h2>
					Management
				</h2>
				<div class="dashTiles">
					<?php $this->bigup_plugin_settings_dashboard_entry(); ?>
				</div>
			</section>

		</div>

		<?php
	}
}
