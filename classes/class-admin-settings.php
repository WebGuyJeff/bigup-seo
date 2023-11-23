<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Admin Settings.
 *
 * Hook into the WP admin area and add menu options and settings pages.
 *
 * @package bigup-seo
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2023, Jefferson Real
 * @license GPL3+
 * @link https://jeffersonreal.uk
 */

// WordPress dependencies.
use function menu_page_url;
use function add_submenu_page;
use function get_option;
use function add_action;
use function settings_fields;
use function do_settings_sections;
use function submit_button;
use function sanitize_text_field;
use function add_settings_section;
use function add_settings_field;
use function register_setting;
use function do_shortcode;

class Admin_Settings {


	/**
	 * Settings page menu title to add with add_submenu_page().
	 */
	public $admin_label = 'Bigup SEO';


	/**
	 * Settings page slug to add with add_submenu_page().
	 */
	public $page_slug = 'bigup-seo';


	/**
	 * Settings group name called by settings_fields().
	 *
	 * To add multiple sections to the same settings page, all settings
	 * registered for that page MUST BE IN THE SAME 'OPTION GROUP'.
	 */
	public $group_name = 'group_bigup-seo_settings';


	/**
	 * Add admin menu option to sidebar
	 */
	public function register_admin_menu() {
		add_submenu_page(
			Admin_Settings_Parent::$page_slug,       // parent_slug.
			$this->admin_label . ' Settings',        // page_title.
			$this->admin_label,                      // menu_title.
			'manage_options',                        // capability.
			$this->page_slug,                        // menu_slug.
			array( &$this, 'create_settings_page' ), // function.
			null,                                    // position.
		);
	}


	/**
	 * Echo a link to this plugin's settings page.
	 */
	public function echo_plugin_settings_link() {
		?>
		<a href="/wp-admin/admin.php?page=<?php echo $this->page_slug; ?>">
			<?php echo $this->admin_label; ?> settings
		</a>
		<?php
	}


	/**
	 * Create Plugin Settings Page
	 */
	public function create_settings_page() {
		?>

		<div class="wrap">

			<h1>
				<span class="dashicons-bigup-logo" style="font-size: 2em; margin-right: 0.2em;"></span>
				Bigup SEO Settings
			</h1>

			<?php
			$files = Util::theme_files_contain( '<title' );
			if ( $files ) {
				echo '<p>Warning! Your current theme may have the &lt;title&gt; meta tag hard-coded in the following template files:</p>';
				echo '<ul style="list-style: disc; margin-left: 2em;">';
				foreach ( $files as $file ) {
					echo '<li>' . esc_url( $file ) . '</li>';
				}
				echo '</ul>';
				echo '<p>You should disable this setting to prevent duplicate tags.</p>';
			} else {
				echo "<p>Great! Your current theme doesn't have the &lt;title&gt; meta tag hard-coded in it's template markup. We can manage that for you!</p>";
			}
			echo "<p>We've disabled &lt;title&gt; tag theme support to prevent WordPress from generating it on your pages.</p>";
			$theme_support = get_theme_support( 'title-tag' );
			echo '<p>Title tag support status: ' . ( $theme_support ? 'enabled' : 'disabled' ) . '</p>';
			?>

			<form method="post" action="options.php">
				<?php

					// Settings to control SEO meta to be implemented here.

					// settings_fields( $this->group_name );
					// do_settings_sections( $this->page_slug );
					// submit_button( 'Save' );

				?>

			</form>

		</div>

		<?php
	}
}
