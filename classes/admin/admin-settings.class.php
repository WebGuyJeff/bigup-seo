<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Admin Settings.
 *
 * Hook into the WP admin area and add menu options and settings pages.
 *
 * @package bigup-seo
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2024, Jefferson Real
 * @license GPL3+
 * @link https://jeffersonreal.uk
 */
class Admin_Settings {


	/**
	 * Settings page menu title to add with add_submenu_page().
	 */
	private const ADMINLABEL = 'Bigup SEO';


	/**
	 * Settings page slug to add with add_submenu_page().
	 */
	private const SETTINGSLUG = 'bigup-seo-settings';


	/**
	 * Settings group name called by settings_fields().
	 *
	 * Option group ID which is set when registering settings for this page.
	 */
	public $group_name = 'group_bigup-seo_settings';


	/**
	 * Add admin menu option to sidebar
	 */
	public function register_admin_menu() {
		add_submenu_page(
			Admin_Settings_Parent::$page_slug,       // parent_slug.
			self::ADMINLABEL . ' Settings',          // page_title.
			self::ADMINLABEL,                        // menu_title.
			'manage_options',                        // capability.
			self::SETTINGSLUG,                       // menu_slug.
			array( &$this, 'create_settings_page' ), // function.
			null,                                    // position.
		);
	}


	/**
	 * Echo a link to this plugin's settings page.
	 */
	public function echo_plugin_settings_link() {
		?>
		<a href="/wp-admin/admin.php?page=<?php echo self::SETTINGSLUG; ?>">
			<?php echo self::ADMINLABEL; ?> settings
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
				<?php echo esc_html( get_admin_page_title() ); ?>
			</h1>

			<p>
				These settings control Bigup SEO features.
			</p>

			<?php settings_errors(); // Display the form save notices here. ?>

			<?php
			$files = Util::theme_files_contain( '<title' );
			if ( $files ) {
				echo '<p>Warning! Your current theme may have the &lt;title&gt; meta tag hard-coded in the following template files:</p>';
				echo '<ul style="list-style: disc; margin-left: 2em;">';
				foreach ( $files as $file ) {
					echo '<li>' . esc_url( $file ) . '</li>';
				}
				echo '</ul>';
				echo '<p>This may cause duplicated tags in your markup, harming your SEO. Please review your theme templates to ensure these tags are not present allowing WordPress to handle their generation instead.</p>';
			} else {
				echo "<p>Great! Your current theme doesn't have the &lt;title&gt; meta tag hard-coded in it's template markup. This plugin can safely manage that for you.</p>";
			}
			$theme_support = get_theme_support( 'title-tag' );
			echo '<p>Title tag theme support status: ' . ( $theme_support ? 'enabled' : 'disabled' ) . '</p>';

			// Get the active tab from the $_GET URL param.
			$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : null;
			?>

			<nav class="nav-tab-wrapper">
				<a
					href="?page=<?php echo esc_attr( self::SETTINGSLUG ); ?>"
					class="nav-tab<?php echo ( null === $tab ) ? esc_attr( ' nav-tab-active' ) : ''; ?>"
				><?php echo esc_html( __( 'General', 'bigup-seo' ) ); ?></a>
				<a
					href="?page=<?php echo esc_attr( self::SETTINGSLUG ); ?>&tab=tab-2"
					class="nav-tab<?php echo ( 'tab-2' === $tab ) ? esc_attr( ' nav-tab-active' ) : ''; ?>"
				><?php echo esc_html( __( 'Sitemap', 'bigup-seo' ) ); ?></a>
				<a
					href="?page=<?php echo esc_attr( self::SETTINGSLUG ); ?>&tab=tab-3"
					class="nav-tab<?php echo ( 'tab-3' === $tab ) ? esc_attr( ' nav-tab-active' ) : ''; ?>"
				><?php echo esc_html( __( 'Robots', 'bigup-seo' ) ); ?></a>
				<a
					href="?page=<?php echo esc_attr( self::SETTINGSLUG ); ?>&tab=tab-4"
					class="nav-tab<?php echo ( 'tab-4' === $tab ) ? esc_attr( ' nav-tab-active' ) : ''; ?>"
				><?php echo esc_html( __( 'Developer', 'bigup-seo' ) ); ?></a>
			</nav>

			<div class="tab-content">
				<form method="post" action="options.php">
					<?php
					switch ( $tab ) :
						default:
							Settings_Tab_1::output_tab();
							break;
						case 'tab-2':
							Settings_Tab_2::output_tab();
							break;
						case 'tab-3':
							Settings_Tab_3::output_tab();
							break;
						case 'tab-4':
							Settings_Tab_4::output_tab();
							break;
					endswitch;
					?>
				</form>
			</div>

		</div>

		<?php
	}
}
