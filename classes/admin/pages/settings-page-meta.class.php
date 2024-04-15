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

		// Basic settings (handled with wp_options).
		self::output_theme_template_title_tag_status();
		settings_fields( self::GROUP );
		do_settings_sections( self::PAGE );
		submit_button( 'Save' );

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

			$fields_markup = '';

			switch ( $type ) {

				case 'front_page':
				case 'blog_index':
					$fields_markup .= $this->get_field_page_title_tag(
						array(
							'field_id' => $type,
							'label'    => $data['label'],
						)
					);
					break;

				case 'page':
				case 'post':
				case 'post_archive':
				case 'author':
					foreach ( $data['pages'] as $key => $page ) {
						$fields_markup .= $this->get_field_page_title_tag(
							array(
								'field_id' => $key,
								'label'    => $page['name'],
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
					<tbody>
						{$fields_markup}
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

		$name        = esc_attr( $page['field_id'] );
		$id          = esc_attr( $page['field_id'] );
		$value       = esc_attr( $page['label'] );
		$placeholder = __( 'Enter a title', 'bigup-seo' );

		$field = <<<HTML
			<tr>
				<th scope="row">{$page['label']}</th>
				<td>
					<input
						type="text"
						class="regular-text"
						name="{$name}"
						id="{$id}"
						value="{$value}"
						placeholder="{$placeholder}"
					>
				</td>
			</tr>




			<tr id="editRow-my-post" class="editActive inline-edit-row inline-edit-row-page quick-edit-row quick-edit-row-page inline-edit-page inline-editor">
				<td colspan="4">
					<form method="post" action="options.php" class="inline-edit-wrapper" data-type-form="edit">
						<fieldset class="inline-edit-fieldset">
							<legend class="inline-edit-legend">
								Edit Custom Post Type
							</legend>
							<template id="deleteFlag">
								<input type="hidden" name="" id="delete" value="1" checked="">	
							</template>
							<h3>Main Settings</h3>
							<input type="hidden" name="" id="post_type" value="my-post" required="">
							<label class="field"><span class="field_title">Singular Name</span>
								<input type="text" name="" id="name_singular" value="" placeholder="My Post" pattern="[- \p{L}\p{N}]*" maxlength="30" required="">
							</label>
							<label class="field"><span class="field_title">Plural Name</span>
								<input type="text" name="" id="name_plural" value="" placeholder="My Posts" pattern="[- \p{L}\p{N}]*" maxlength="30" required="">
							</label>
							<h3>Advanced Settings</h3>
							<label class="field"><span class="field_title">Public</span>
								<input type="checkbox" name="" id="public" value="1" checked="">
							</label>
							<label class="field"><span class="field_title">Show in Menu</span>
								<input type="checkbox" name="" id="show_in_menu" value="1" checked="">
							</label>
							<label class="field"><span class="field_title">Menu Position</span>
								<input type="number" name="" id="menu_position" min="0" max="100" step="1" value="5" required="">
							</label>
						</fieldset>
						<div class="submit inline-edit-save">
							<button type="button" title="Submit and save form" id="submitButton" class="button button-primary save">Save</button>
							<button type="button" id="cancelButton" class="button">
							Cancel
							</button>
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
