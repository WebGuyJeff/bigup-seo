<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Head Meta
 *
 * @package bigup-seo
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2023, Jefferson Real
 * @license GPL3+
 * @link https://jeffersonreal.uk
 */

class Head_Meta {

	/**
	 * Vars scraped from the website.
	 */
	private array $seo_vars = array();

	/**
	 * Head metadata markup.
	 */
	private string $head_meta = '';

	/**
	 * Constants (need a source like wp options perhaps).
	 */
	private const SETTINGS = array(
		'localealt'  => 'en_US',
		'objecttype' => 'website',
	);


	/**
	 * Generate and print the head meta.
	 */
	public function print_head_meta() {

		// Populate when wp_head is called otherwise conditionals like is_category() won't work.
		$this->seo_vars  = $this->get_seo_vars();
		$this->head_meta = $this->get_seo_meta( $this->seo_vars );

		$output  = "<!-- Bigup SEO: Meta START -->\n";
		$output .= $this->head_meta;
		$output .= "<!-- Bigup SEO: Meta END -->\n";

		Escape::head( $output );
	}


	/**
	 * Return the first non-empty array value as a string.
	 *
	 * @return string The first non-empty value or empty string on failure.
	 * @param array $array The array to check for non-empty values.
	 */
	private function first_not_empty( $array ) {
		$string = '';
		if ( is_array( $array ) ) {
			foreach ( $array as &$value ) {
				$trimmed = trim( $value, ' ' );
				if ( ! empty( $trimmed ) ) {
					$string = $trimmed;
					goto end;
				}
			}
			end:
			unset( $value );
			if ( empty( $string ) ) {
				$string = '';
			}
		}
		return $string;
	}


	/**
	 * Parse string with regular expression to find an image src.
	 *
	 * @return string image src string without quotes.
	 * @param string $content The passed content to search.
	 */
	private function extract_image_from_content( $content ) {
		$url = '';

		if ( isset( $content ) && $content !== '' ) {

			if ( is_array( $content ) ) {
				implode( $content );
			}

			$regex = '/src="([^"]*)"/';
			preg_match_all( $regex, $content, $matches, PREG_PATTERN_ORDER );

			if ( isset( $matches[0][0] ) ) {
				$match     = $matches[0][0];
				$url_parts = explode( '"', $match, 3 );
				$url       = $url_parts[1];

			} else {
				$url = '';
			}
		} else {
			$url = '';
		}
			return $url;
	}


	/**
	 * Get a favicon URL by specified size.
	 *
	 * @return string The URL.
	 */
	private function get_favicon_url( $size ) {
		if ( has_site_icon() ) {
			$url = get_site_icon_url( $size );
		} else {
			// Fallback when no icon is set.
			$url = wp_get_attachment_url( get_theme_mod( 'custom_logo' ) );
		}
		return $url;
	}


	/**
	 * Populate the SEO meta variables.
	 *
	 * @return array Array of meta variables.
	 */
	private function get_seo_vars() {

		/* Sitewide */
		$lw_sitetitle  = wp_strip_all_tags( get_bloginfo( 'name', 'display' ) );
		$lw_blogtitle  = wp_strip_all_tags( get_the_title( get_option( 'page_for_posts', true ) ) );
		$lw_siteauthor = wp_strip_all_tags( get_bloginfo( 'name', 'display' ) ) . ' Staff';
		$lw_sitedesc   = wp_strip_all_tags( get_bloginfo( 'description', 'display' ) );
		$lw_url        = esc_url( home_url( '/', 'https' ) );
		$lw_themeuri   = trailingslashit( get_template_directory_uri() );
		$lw_sitelogo   = esc_url( wp_get_attachment_url( get_theme_mod( 'custom_logo' ) ) );
		$lw_locale     = wp_strip_all_tags( get_bloginfo( 'language' ) );
		$lw_charset    = wp_strip_all_tags( get_bloginfo( 'charset' ) );
		$lw_colour     = get_background_color() ? '#' . get_background_color() : '#ebe8e6';
		$lw_icon512    = $this->get_favicon_url( 512 );
		$lw_icon270    = $this->get_favicon_url( 270 );
		$lw_icon192    = $this->get_favicon_url( 192 );
		$lw_icon180    = $this->get_favicon_url( 180 );
		$lw_icon150    = $this->get_favicon_url( 150 );
		$lw_icon96     = $this->get_favicon_url( 96 );
		$lw_icon32     = $this->get_favicon_url( 32 );

		/* Page-Specific */
		$post = get_post(); // Setup the post manually.
		setup_postdata( $post );
		$lw_postid      = get_the_ID();
		$lw_postcontent = get_post_field( 'post_content', $lw_postid, '' );
		$lw_postimage   = esc_url( $this->extract_image_from_content( $lw_postcontent ) );
		$lw_posttitle   = wp_strip_all_tags( get_the_title() );
		$lw_permalink   = esc_url( get_permalink() );

		/* Set scope */
		$lw_catexcerpt   = '';
		$lw_archivetitle = '';
		$lw_postexcerpt  = '';

		/* scrape conditionally by page type */
		if ( is_category() ) { // User may have set desc.
			$lw_catexcerpt = preg_split( '/[.?!]/', wp_strip_all_tags( category_description(), true ) )[0] . '.';
		}
		if ( is_archive() ) { // Also matches categories (don't set vars twice).
			$lw_archivetitle = wp_strip_all_tags( post_type_archive_title( '', false ) );
			$lw_thumbnail    = esc_url( get_the_post_thumbnail_url( $lw_postid ) );
		} else {
			$lw_postexcerpt = preg_split( '/[.?!]/', wp_strip_all_tags( $lw_postcontent, true ) )[0] . '.';
			$lw_postauthor  = wp_strip_all_tags( get_the_author() );
			$lw_thumbnail   = esc_url( get_the_post_thumbnail_url( $lw_postid ) );
		}

		/* choose the most suitable scraped value with preference order by page type */
		if ( is_front_page() ) {
			$lw_title   = ucwords( $lw_sitetitle . ' - ' . $lw_sitedesc );
			$lw_desc    = ucfirst( $this->first_not_empty( array( $lw_sitedesc, $lw_postexcerpt ) ) );
			$lw_author  = ucwords( $this->first_not_empty( array( $lw_siteauthor, $lw_postauthor ) ) );
			$lw_canon   = $lw_url;
			$lw_ogimage = $this->first_not_empty( array( $lw_sitelogo, $lw_thumbnail, $lw_postimage ) );

		} elseif ( is_home() ) {
			$lw_title   = ucwords( $this->first_not_empty( array( $lw_blogtitle, $lw_sitetitle ) ) . ' - ' . $lw_sitedesc );
			$lw_desc    = ucfirst( $this->first_not_empty( array( $lw_postexcerpt, $lw_sitedesc ) ) );
			$lw_author  = ucwords( $this->first_not_empty( array( $lw_siteauthor, $lw_postauthor ) ) );
			$lw_canon   = trailingslashit( $lw_permalink );
			$lw_ogimage = $this->first_not_empty( array( $lw_thumbnail, $lw_sitelogo, $lw_postimage ) );

		} elseif ( is_category() ) {
			$lw_title   = ucwords( $this->first_not_empty( array( $lw_archivetitle, $lw_posttitle ) ) );
			$lw_desc    = ucfirst( $this->first_not_empty( array( $lw_catexcerpt, $lw_postexcerpt, $lw_sitedesc ) ) );
			$lw_author  = ucwords( $this->first_not_empty( array( $lw_postauthor, $lw_siteauthor ) ) );
			$lw_canon   = trailingslashit( $lw_permalink );
			$lw_ogimage = $this->first_not_empty( array( $lw_thumbnail, $lw_postimage, $lw_sitelogo ) );

		} elseif ( is_archive() ) {
			$lw_title   = ucwords( $this->first_not_empty( array( $lw_archivetitle, $lw_posttitle ) ) );
			$lw_desc    = ucfirst( $this->first_not_empty( array( $lw_catexcerpt, $lw_postexcerpt, $lw_sitedesc ) ) );
			$lw_author  = ucwords( $this->first_not_empty( array( $lw_postauthor, $lw_siteauthor ) ) );
			$lw_canon   = trailingslashit( $lw_permalink );
			$lw_ogimage = $this->first_not_empty( array( $lw_thumbnail, $lw_postimage, $lw_sitelogo ) );

		} elseif ( is_singular() ) {
			$lw_title   = ucwords( $lw_posttitle );
			$lw_desc    = ucfirst( $lw_postexcerpt );
			$lw_author  = ucwords( $lw_postauthor );
			$lw_canon   = trailingslashit( $lw_permalink );
			$lw_ogimage = $this->first_not_empty( array( $lw_postimage, $lw_thumbnail, $lw_sitelogo ) );

		} else {
			echo '<!-- SEO META FALLBACK - WP TEMLPATE NOT MATCHED -->';
			$lw_title   = ucwords( $this->first_not_empty( array( $lw_posttitle, $lw_archivetitle, $lw_sitetitle ) ) );
			$lw_desc    = ucfirst( $this->first_not_empty( array( $lw_postexcerpt, $lw_catexcerpt, $lw_sitedesc ) ) );
			$lw_author  = ucwords( $this->first_not_empty( array( $lw_postauthor, $lw_siteauthor ) ) );
			$lw_canon   = trailingslashit( $lw_permalink );
			$lw_ogimage = $this->first_not_empty( array( $lw_thumbnail, $lw_postimage, $lw_sitelogo ) );
		}

		$meta = array(
			'title'       => $lw_title,
			'desc'        => $lw_desc,
			'author'      => $lw_author,
			'canon'       => $lw_canon,
			'ogimage'     => $lw_ogimage,
			'ogtitle'     => $lw_title,
			'ogtype'      => self::SETTINGS['objecttype'],
			'ogurl'       => $lw_canon,
			'oglocale'    => $lw_locale,
			'oglocalealt' => self::SETTINGS['localealt'],
			'ogdesc'      => $lw_desc,
			'ogsitename'  => $lw_sitetitle,
			'charset'     => $lw_charset,
			'url'         => $lw_url,
			'themeuri'    => $lw_themeuri,
			'colour'      => $lw_colour,
			'icon512'     => $lw_icon512,
			'icon270'     => $lw_icon270,
			'icon192'     => $lw_icon192,
			'icon180'     => $lw_icon180,
			'icon150'     => $lw_icon150,
			'icon96'      => $lw_icon96,
			'icon32'      => $lw_icon32,
		);
		return $meta;
	}

	/**
	 * Generate the SEO meta HTML.
	 *
	 * @return string HTML to be inserted into head.
	 * @param array $meta The array of SEO meta data variables.
	 */
	private function get_seo_meta( $meta ) {
		$head_meta =
			'<meta charset="' . $meta['charset'] . '">' .
			'<meta name="viewport" content="width=device-width, initial-scale=1">' .
			'<title>' . $meta['title'] . '</title>' .
			'<meta name="description" content="' . $meta['desc'] . '">' .
			'<meta name="author" content="' . $meta['author'] . '">' .
			'<link rel="canonical" href="' . $meta['canon'] . '">' .
			'<!-- Open Graph Meta -->' .
			'<meta property="og:title" content="' . $meta['ogtitle'] . '">' .
			'<meta property="og:type" content="' . $meta['ogtype'] . '">' . // HTML tag namespace must match og:type.
			'<meta property="og:image" content="' . $meta['ogimage'] . '">' .
			'<meta property="og:url" content="' . $meta['ogurl'] . '">' .
			'<meta property="og:locale" content="' . $meta['oglocale'] . '">' .
			'<meta property="og:locale:alternate" content="' . $meta['oglocalealt'] . '">' .
			'<meta property="og:description" content="' . $meta['ogdesc'] . '">' .
			'<meta property="og:site_name" content="' . $meta['ogsitename'] . '">' .
			'<!-- Browser Colours -->' .
			'<meta name="theme-color" content="' . $meta['colour'] . '">' .
			'<meta name="apple-mobile-web-app-capable" content="yes">' .
			'<meta name="apple-mobile-web-app-status-bar-style" content="' . $meta['colour'] . '">' .
			'<!-- Favicons -->' .
			'<link rel="icon" type="image/png" href="' . $meta['icon512'] . '" sizes="512x512">' .
			'<link rel="icon" type="image/png" href="' . $meta['icon270'] . '" sizes="270x270">' .
			'<link rel="icon" type="image/png" href="' . $meta['icon192'] . '" sizes="192x192">' .
			'<link rel="icon" type="image/png" href="' . $meta['icon180'] . '" sizes="180x180">' .
			'<link rel="icon" type="image/png" href="' . $meta['icon150'] . '" sizes="150x150">' .
			'<link rel="icon" type="image/png" href="' . $meta['icon96'] . '" sizes="96x96">' .
			'<link rel="icon" type="image/png" href="' . $meta['icon32'] . '" sizes="32x32">' .
			'<link rel="apple-touch-icon" href="' . $meta['icon180'] . '">' .
			'<meta name="msapplication-TileImage" content="' . $meta['icon270'] . '">';
		return $head_meta;
	}


	/**
	 * Get the verification keys from saved settings.
	 *
	 * @return array Array of verification name/content pairs.
	 */
	private function get_verification_keys() {
		$option = get_option( 'lw_settings_verification' );
		$keys   = array(
			'google-site-verification' => $option['google_verification_key'],
			'msvalidate.01'            => $option['microsoft_verification_key'],
		);
		return $keys;
	}


	/**
	 * Generate the verification meta HTML.
	 *
	 * @return string HTML to be inserted into head.
	 * @param array $data The array of verification names and content.
	 */
	private function get_verification_meta( $keys ) {
		$verification_meta = "<!-- Site verification -->\n";
		foreach ( $keys as $key => $value ) {
			$verification_meta .= "<meta name=\"{$key}\" content=\"{$value}\" />\n";
		}
		return $verification_meta;
	}
}
