<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Head Meta
 *
 * @package bigup-seo
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2024, Jefferson Real
 * @license GPL3+
 * @link https://jeffersonreal.uk
 */
class Head {

	/**
	 * Meta variables scraped from the website.
	 */
	private array $meta = array();

	/**
	 * Head meta tag markup.
	 */
	private string $markup = '';

	/**
	 * Title tag text.
	 */
	private string $title = '';

	/**
	 * Constants (need a source like wp options perhaps).
	 */
	private const SETTINGS = array(
		'localealt'  => 'en_US',
		'objecttype' => 'website',
	);


	/**
	 * Populate class properties.
	 *
	 * Must be instantiated between the wp query and 'wp_head' hooks so conditionals work.
	 * Hook 'template_redirect' seems to work nicely.
	 */
	public function __construct() {
		$this->meta   = $this->get_meta();
		$this->markup = $this->get_markup( $this->meta );
		$this->title  = $this->meta['title'];
	}


	/**
	 * Return the current page title to filter WP document_title with.
	 */
	public function get_title_tag_text() {
		return $this->title;
	}


	/**
	 * Print the head meta.
	 */
	public function print_markup() {
		$output  = "<!-- Bigup SEO: START -->\n";
		$output .= $this->markup;
		$output .= "<!-- Bigup SEO: END -->\n";

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
	private function get_meta() {

		/* Sitewide */
		$sitetitle  = wp_strip_all_tags( get_bloginfo( 'name', 'display' ) );
		$blogtitle  = wp_strip_all_tags( get_the_title( get_option( 'page_for_posts', true ) ) );
		$siteauthor = wp_strip_all_tags( get_bloginfo( 'name', 'display' ) ) . ' Staff';
		$sitedesc   = wp_strip_all_tags( get_bloginfo( 'description', 'display' ) );
		$url        = esc_url( home_url( '/', 'https' ) );
		$themeuri   = trailingslashit( get_template_directory_uri() );
		$sitelogo   = esc_url( wp_get_attachment_url( get_theme_mod( 'custom_logo' ) ) );
		$locale     = wp_strip_all_tags( get_bloginfo( 'language' ) );
		$colour     = get_background_color() ? '#' . get_background_color() : '#ebe8e6';
		$icon512    = $this->get_favicon_url( 512 );
		$icon270    = $this->get_favicon_url( 270 );
		$icon192    = $this->get_favicon_url( 192 );
		$icon180    = $this->get_favicon_url( 180 );
		$icon150    = $this->get_favicon_url( 150 );
		$icon96     = $this->get_favicon_url( 96 );
		$icon32     = $this->get_favicon_url( 32 );

		/* Page-Specific */
		$post = get_post(); // Setup the post manually.
		setup_postdata( $post );
		$postid      = get_the_ID();
		$postcontent = get_post_field( 'post_content', $postid, '' );
		$postimage   = esc_url( $this->extract_image_from_content( $postcontent ) );
		$posttitle   = wp_strip_all_tags( get_the_title() );
		$permalink   = esc_url( get_permalink() );

		$catexcerpt   = '';
		$archivetitle = '';
		$postexcerpt  = '';
		$postauthor   = '';
		$thumbnail    = '';
		if ( is_category() || is_archive() ) {
			$catexcerpt   = preg_split( '/[.?!]/', wp_strip_all_tags( category_description(), true ) )[0];
			$archivetitle = wp_strip_all_tags( post_type_archive_title( '', false ) );
		} else {
			$postexcerpt = preg_split( '/[.?!]/', wp_strip_all_tags( $postcontent, true ) )[0];
			$postauthor  = wp_strip_all_tags( get_the_author() );
			$thumbnail   = esc_url( get_the_post_thumbnail_url( $postid ) );
		}

		$warning = false;

		/* choose the most suitable scraped value with preference order by page type */
		if ( is_front_page() ) {
			$title   = ucwords( $sitetitle . ' - ' . $sitedesc );
			$desc    = ucfirst( $this->first_not_empty( array( $sitedesc, $postexcerpt ) ) );
			$author  = ucwords( $this->first_not_empty( array( $siteauthor, $postauthor ) ) );
			$canon   = $url;
			$ogimage = $this->first_not_empty( array( $sitelogo, $thumbnail, $postimage ) );

		} elseif ( is_home() ) {
			$title   = ucwords( $this->first_not_empty( array( $blogtitle, $sitetitle ) ) . ' - ' . $sitedesc );
			$desc    = ucfirst( $this->first_not_empty( array( $postexcerpt, $sitedesc ) ) );
			$author  = ucwords( $this->first_not_empty( array( $siteauthor, $postauthor ) ) );
			$canon   = trailingslashit( $permalink );
			$ogimage = $this->first_not_empty( array( $thumbnail, $sitelogo, $postimage ) );

		} elseif ( is_category() ) {
			$title   = ucwords( $this->first_not_empty( array( $archivetitle, $posttitle ) ) );
			$desc    = ucfirst( $this->first_not_empty( array( $catexcerpt, $postexcerpt, $sitedesc ) ) );
			$author  = ucwords( $this->first_not_empty( array( $postauthor, $siteauthor ) ) );
			$canon   = trailingslashit( $permalink );
			$ogimage = $this->first_not_empty( array( $thumbnail, $postimage, $sitelogo ) );

		} elseif ( is_archive() ) {
			$title   = ucwords( $this->first_not_empty( array( $archivetitle, $posttitle ) ) );
			$desc    = ucfirst( $this->first_not_empty( array( $catexcerpt, $postexcerpt, $sitedesc ) ) );
			$author  = ucwords( $this->first_not_empty( array( $postauthor, $siteauthor ) ) );
			$canon   = trailingslashit( $permalink );
			$ogimage = $this->first_not_empty( array( $thumbnail, $postimage, $sitelogo ) );

		} elseif ( is_singular() ) {
			$title   = ucwords( $posttitle );
			$desc    = ucfirst( $postexcerpt );
			$author  = ucwords( $postauthor );
			$canon   = trailingslashit( $permalink );
			$ogimage = $this->first_not_empty( array( $postimage, $thumbnail, $sitelogo ) );

		} else {
			$warning = "<!-- NOTICE: Fallback meta in use as template not matched for this page -->\n";
			$title   = ucwords( $this->first_not_empty( array( $posttitle, $archivetitle, $sitetitle ) ) );
			$desc    = ucfirst( $this->first_not_empty( array( $postexcerpt, $catexcerpt, $sitedesc ) ) );
			$author  = ucwords( $this->first_not_empty( array( $postauthor, $siteauthor ) ) );
			$canon   = trailingslashit( $permalink );
			$ogimage = $this->first_not_empty( array( $thumbnail, $postimage, $sitelogo ) );
		}

		$meta = array(
			'warning'     => $warning,
			'title'       => $title,
			'desc'        => $desc,
			'author'      => $author,
			'canon'       => $canon,
			'ogimage'     => $ogimage,
			'ogtitle'     => $title,
			'ogtype'      => self::SETTINGS['objecttype'],
			'ogurl'       => $canon,
			'oglocale'    => $locale,
			'oglocalealt' => self::SETTINGS['localealt'],
			'ogdesc'      => $desc,
			'ogsitename'  => $sitetitle,
			'url'         => $url,
			'themeuri'    => $themeuri,
			'colour'      => $colour,
			'icon512'     => $icon512,
			'icon270'     => $icon270,
			'icon192'     => $icon192,
			'icon180'     => $icon180,
			'icon150'     => $icon150,
			'icon96'      => $icon96,
			'icon32'      => $icon32,
		);
		return $meta;
	}

	/**
	 * Generate the SEO meta HTML.
	 *
	 * @return string HTML to be inserted into head.
	 * @param array $meta The array of SEO meta data variables.
	 */
	private function get_markup( $meta ) {

		$markup = '';

		if ( $meta['warning'] ) {
			$markup .= $meta['warning'];
		}

		$markup .=
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
		return $markup;
	}
}
