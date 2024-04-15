<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Settings Tab 1.
 *
 * @package bigup-seo
 */
class Settings_Page_Meta {

	public const PAGE   = 'bigupseo_page_meta';
	public const GROUP  = 'bigupseo_group_meta';
	public const OPTION = 'bigupseo_settings_meta';

	private $settings;

	/**
	 * The pages.
	 */
	private $pages = array();

	/**
	 * Hook into WP.
	 */
	public function __construct() {

		if ( empty( $this->pages ) ) {
			$this->pages = new Pages();
		}
		add_action( 'admin_init', array( &$this, 'register' ), 11, 0 ); // Must fire after Pages().
	}


	/**
	 * Register the settings.
	 */
	public function register() {

		$this->settings = get_option( self::OPTION );

		// A single serialised array holds all tab settings.
		register_setting(
			self::GROUP,               // option_group.
			self::OPTION,              // option_name.
			array( $this, 'sanitise' ) // sanitise_callback.
		);

		$this->register_section_meta();
	}


	/**
	 * Output the content for this tab.
	 */
	public function output() {

		?>
			<form method="post" action="options.php">
				<?php
					// Basic settings (handled with wp_options).
					self::output_theme_template_title_tag_status();
					settings_fields( self::GROUP );
					do_settings_sections( self::PAGE );
					submit_button( 'Save' );
				?>
			</form>
		<?php

		// SEO meta settings (handled with custom table 'bigup_seo_meta').
		$this->echo_seo_meta_options();

		if ( BIGUPSEO_DEBUG ) {
			$this->debug();
		}
	}


	/**
	 * Page data output for debugging.
	 *
	 * This should only be displayed when debugging is enabled.
	 */
	public function debug() {
		if ( is_admin() ) {
			$debug  = '';
			$debug .= '<pre style="background:#fff;overflow:scroll;padding:0.5rem;border:solid;font-size:0.7rem;">';
			// $debug .= print_r( $this->pages->providers, true );
			$debug .= print_r( $this->pages->map, true );
			$debug .= '</pre>';
			echo $debug;
		}
	}


	/**
	 * Theme template title tag check.
	 *
	 * Output a warning if the title tag is detected in theme file markup.
	 */
	public static function output_theme_template_title_tag_status() {
		echo '<div class="copyWidth">';
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
			echo "<p>Great! Your current theme doesn't have the <code>&lt;title&gt;</code> meta tag hard-coded in it's template markup. This plugin can safely manage that for you.</p>";
		}
		$theme_support = get_theme_support( 'title-tag' );
		echo '<p>Title tag theme support status: <span class="inlineStatusOutput">' . ( $theme_support ? '✅ enabled' : '❌ disabled' ) . '</span></p>';
		echo '</div>';
	}


	/**
	 * Register meta settings section and fields.
	 */
	private function register_section_meta() {
		$section = 'meta_settings';
		add_settings_section( $section, 'Meta', array( $this, 'echo_section_intro_meta' ), self::PAGE );
		add_settings_field( 'generate_title_tags', 'Generate title tags', array( &$this, 'echo_field_enable_plugin_title_tags' ), self::PAGE, $section );
	}


	/**
	 * Output meta section intro.
	 */
	public function echo_section_intro_meta() {
		echo '<p>Meta settings.</p>';
	}


	/**
	 * Output option to enable plugin title tags.
	 */
	public function echo_field_enable_plugin_title_tags() {
		$setting = self::OPTION . '[generate_title_tags]';
		printf(
			'<input type="checkbox" value="1" id="%s" name="%s" %s><label for="%s">%s</label>',
			$setting,
			$setting,
			isset( $this->settings['generate_title_tags'] ) ? checked( '1', $this->settings['generate_title_tags'], false ) : '',
			$setting,
			'Enable generation of page meta title tags.'
		);
	}


	/**
	 * Output seo meta options.
	 *
	 * This markup contains all options which interact with the bigup_seo_meta DB table.
	 */
	private function echo_seo_meta_options() {

		$seo_meta_options = '';

		foreach ( $this->pages->map as $type => $data ) {

			// Decode prefixes for post and tax types.
			$sub_type = '';
			if ( preg_match( '/post__.*/', $type ) ) {
				$sub_type = str_replace( 'post__', '', $type );
				$type     = 'post';
			} elseif ( preg_match( '/tax__.*/', $type ) ) {
				$sub_type = str_replace( 'tax__', '', $type );
				$type     = 'tax';
			}

			$table_rows = '';

			switch ( $type ) {

				case 'front_page':
				case 'blog_index':
					$table_rows .= $this->get_field_page_title_tag(
						array(
							'label' => $data['label'],
							'type'  => $type,
							'key'   => $type,
						)
					);
					break;

				case 'page':
				case 'post':
				case 'post_archive':
				case 'author':
					foreach ( $data['pages'] as $key => $page ) {
						$table_rows .= $this->get_field_page_title_tag(
							array(
								'label' => $page['name'],
								'type'  => $type,
								'key'   => $key,
							)
						);
					}
					break;

				default:
					error_log( "Bigup SEO: Page type {$type} not found." );
					break;
			}

			$seo_meta_options .= <<<HTML
				<h2>{$data['label']}</h2>
				<table class="form-table" role="presentation">

				<table id="metaOptionsTable" class="wp-list-table widefat fixed striped table-view-list">
					<thead>
						<tr>
							<th scope="col" id="title" class="manage-column column-primary">
								<span>Meta Title</span>
							</th>
							<th scope="col" id="type" class="manage-column column-primary">
								<span>Page Type</span>
							</th>
							<th scope="col" id="key" class="manage-column column-primary">
								<span>Key</span>
							</th>
							<th scope="col" id="crawlable" class="manage-column column-primary">
								<span>Crawling Allowed</span>
							</th>
						</tr>
					</thead>
					<tbody>
						{$table_rows}
					</tbody>
				</table>
			HTML;
		}

		echo $seo_meta_options;
	}


	/**
	 * Get page title tag field.
	 */
	public function get_field_page_title_tag( $page ) {

		$title       = esc_attr( $page['label'] );
		$type        = esc_attr( $page['type'] );
		$key         = esc_attr( $page['key'] );
		$placeholder = __( 'Enter a title', 'bigup-seo' );

		$field = <<<HTML
			<tr id="my-post" class="customPostTypeRow iedit">
				<td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
					<strong>{$title}</strong>
					<div class="row-actions">
						<span class="inline hide-if-no-js">
							<button data-post-type="my-post" type="button" class="inlineEditButton button-link editinline" aria-label="edit custom post type" aria-expanded="false">Edit</button>
						</span>
					</div>
				</td>
				<td class="has-row-actions column-primary" data-colname="Type">
					<span>{$type}</span>
				</td>
				<td class="has-row-actions column-primary" data-colname="Key">
					<span>{$key}</span>
				</td>
				<td class="has-row-actions column-primary" data-colname="Crawlable">
					<span>✔</span>
				</td>
			</tr>
			<tr id="editRow-my-post" class="editActive inline-edit-row inline-edit-row-page quick-edit-row quick-edit-row-page inline-edit-page inline-editor">
				<td colspan="4">
					<form method="post" class="inline-edit-wrapper" data-type-form="edit">
						<fieldset class="inline-edit-fieldset">
							<legend class="inline-edit-legend">
								Page Meta
							</legend>
							<h3>Title and Description</h3>
							<label class="field"><span class="field_title">Meta Title</span>
								<input
									type="text"
									class="regular-text"
									name="{$key}"
									id="{$key}"
									value="{$title}"
									placeholder="{$placeholder}"
								>
							</label>
							<label class="field"><span class="field_title">Meta Description</span>
								<textarea
									rows="3"
									name=""
									id=""
									value=""
									placeholder="Enter a description"
								></textarea>
							</label>
							<h3>Indexing</h3>
							<label class="field"><span class="field_title">Canonical URL</span>
								<input
									type="url"
									class="regular-text"
									name=""
									id=""
									value=""
									placeholder="Enter a URL"
								>
							</label>
						</fieldset>
						<div class="submit inline-edit-save">
							<button type="button" title="Submit and save" id="submitButton" class="button button-primary save">Save</button>
							<button type="button" title="Cancel action" id="cancelButton" class="button">Cancel</button>
						</div>
					</form>
				</td>
			</tr>
		HTML;
		return $field;
	}


	/**
	 * Sanitise all settings in the array.
	 */
	public function sanitise( $input ) {

		$sanitised = array();

		if ( isset( $input['generate_title_tags'] ) ) {
			$sanitised['generate_title_tags'] = Sanitise_Setting::checkbox( $input['generate_title_tags'] );
		}

		return $sanitised;
	}
}
