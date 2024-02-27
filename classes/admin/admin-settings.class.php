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
	 * Bigup Web parent menu item and dashboard.
	 */
	private $admin_settings_parent;


	/**
	 * Settings page: General.
	 */
	private $settings_page_general;

	/**
	 * Settings page: Sitemap.
	 */
	private $settings_page_sitemap;


	/**
	 * Settings page: Robots.
	 */
	private $settings_page_robots;


	/**
	 * Settings page: Developer.
	 */
	private $settings_page_developer;


	/**
	 * Setup the class.
	 */
	public function __construct() {
		$this->admin_settings_parent   = new Admin_Settings_Parent();
		$this->settings_page_general   = new Settings_Page_General();
		$this->settings_page_sitemap   = new Settings_Page_Sitemap();
		$this->settings_page_robots    = new Settings_Page_Robots();
		$this->settings_page_developer = new Settings_Page_Developer();
	}


	/**
	 * Register the admin menu and settings pages.
	 */
	public function register() {
		add_action( 'admin_menu', array( &$this->admin_settings_parent, 'register_admin_menu' ), 1, 0 );
		add_action( 'bigup_settings_dashboard_entry', array( &$this, 'echo_plugin_settings_link' ), 10, 0 );
		add_action( 'admin_menu', array( &$this, 'register_admin_menu' ), 99 );
	}


	/**
	 * Add admin menu option to sidebar
	 */
	public function register_admin_menu() {
		add_submenu_page(
			$this->admin_settings_parent::$page_slug, // parent_slug.
			self::ADMINLABEL,                         // page_title.
			self::ADMINLABEL,                         // menu_title.
			'manage_options',                         // capability.
			self::SETTINGSLUG,                        // menu_slug.
			array( &$this, 'create_settings_page' ),  // function.
			null,                                     // position.
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
				Apply SEO enhancements to this website.
			</p>

			<?php settings_errors(); // Display the form save notices here. ?>

			<?php
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
							$this->settings_page_general->output();
							break;
						case 'tab-2':
							$this->settings_page_sitemap->output();
							break;
						case 'tab-3':
							$this->settings_page_robots->output();
							break;
						case 'tab-4':
							$this->settings_page_developer->output();
							break;
					endswitch;
					?>
				</form>
			</div>

		</div>

		<?php
	}
}
