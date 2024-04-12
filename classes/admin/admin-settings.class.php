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
	public const SETTINGSLUG = 'bigup-seo-settings';


	/**
	 * Bigup Web parent menu item and dashboard.
	 */
	private $settings_parent;


	/**
	 * Settings tab: Meta.
	 */
	private $meta_tab;

	/**
	 * Settings tab: Sitemap.
	 */
	private $sitemap_tab;


	/**
	 * Settings tab: Robots.
	 */
	private $robots_tab;


	/**
	 * Settings tab: Developer.
	 */
	private $developer_tab;


	/**
	 * Setup the class.
	 */
	public function __construct() {
		$this->settings_parent = new Admin_Settings_Parent();
		$this->meta_tab        = new Settings_Page_Meta();
		$this->sitemap_tab     = new Settings_Page_Sitemap();
		$this->robots_tab      = new Settings_Page_Robots();
		$this->developer_tab   = new Settings_Page_Developer();
	}


	/**
	 * Register the admin menu and settings pages.
	 */
	public function register() {
		add_action( 'admin_menu', array( &$this->settings_parent, 'register_admin_menu' ), 1, 0 );
		add_action( 'bigup_settings_dashboard_entry', array( &$this, 'echo_plugin_settings_link' ), 10, 0 );
		add_action( 'admin_menu', array( &$this, 'register_admin_menu' ), 99 );
	}


	/**
	 * Add admin menu option to sidebar
	 */
	public function register_admin_menu() {
		add_submenu_page(
			$this->settings_parent::$page_slug, // parent_slug.
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
				><?php echo esc_html( __( 'Meta', 'bigup-seo' ) ); ?></a>
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
							$this->meta_tab->output();
							break;
						case 'tab-2':
							$this->sitemap_tab->output();
							break;
						case 'tab-3':
							$this->robots_tab->output();
							break;
						case 'tab-4':
							$this->developer_tab->output();
							break;
					endswitch;
					?>
				</form>
			</div>

		</div>

		<?php
	}
}
