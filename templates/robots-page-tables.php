<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Robots Page Tables Template.
 *
 * @package bigup-seo
 */

// Variables passed by the calling function.
[ 'seo_pages' => $seo_pages ] = $passed_variables;

$site_url = get_site_url();

// $seo_pages is passed to this template by the calling function.
foreach ( $seo_pages as $page_type => $page_type_data ) {

	// Decode prefixes for post and tax types.
	$sub_type = '';
	if ( preg_match( '/post__.*/', $page_type ) ) {
		$sub_type  = str_replace( 'post__', '', $page_type );
		$page_type = 'post';
	} elseif ( preg_match( '/tax__.*/', $page_type ) ) {
		$sub_type  = str_replace( 'tax__', '', $page_type );
		$page_type = 'tax';
	}

	$strings = array(
		'title'           => $page_type_data['label'],
		'type'            => $page_type,
		'th_page_title'   => __( 'Page Title', 'bigup-seo' ),
		'th_page_url'     => __( 'URL', 'bigup-seo' ),
		'th_googlebot'    => __( 'Googlebot Allowed', 'bigup-seo' ),
		'th_bingbot'      => __( 'Bingbot Allowed', 'bigup-seo' ),
		'th_robots_rules' => __( 'Bot Rules', 'bigup-seo' ),
	);

	/**
	 * Generate a table for each page type.
	 */
	?>
		<h2><?php echo esc_attr( $strings['title'] ); ?></h2>
		<table id="metaOptions_<?php echo esc_attr( $strings['type'] ); ?>" role="presentation" class="wp-list-table widefat fixed striped table-view-list">
			<thead>
				<tr>
					<th scope="col" class="column-primary">
						<span><?php echo esc_attr( $strings['th_page_title'] ); ?></span>
					</th>
					<th scope="col">
						<span><?php echo esc_attr( $strings['th_page_url'] ); ?></span>
					</th>
					<th scope="col">
						<span><?php echo esc_attr( $strings['th_googlebot'] ); ?></span>
					</th>
					<th scope="col">
						<span><?php echo esc_attr( $strings['th_bingbot'] ); ?></span>
					</th>
					<th scope="col">
						<span><?php echo esc_attr( $strings['th_robots_rules'] ); ?></span>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php

				/**
				 * Generate table rows for each page.
				 */
				foreach ( $page_type_data['pages'] as $key => $seo_page ) {

					$page_type    = $sub_type ? $sub_type : $page_type;
					$relative_url = str_replace( $site_url, '', $seo_page['url'] );


					$strings = array(
						'title'        => $seo_page['name'],
						'relative_url' => empty( $relative_url ) ? '/' : $relative_url,
						'googlebot'    => $seo_page['robots']['googlebot_allowed'] ? '✅' : '❌',
						'bingbot'      => $seo_page['robots']['bingbot_allowed'] ? '✅' : '❌',
						'robots_rules' => $seo_page['robots']['status'],
					);

					?>
						<tr class="infoRow">
							<td class="column-primary">
								<strong><?php echo esc_attr( $strings['title'] ); ?></strong>
							</td>
							<td>
								<span><?php echo esc_attr( $strings['relative_url'] ); ?></span>
							</td>
							<td>
								<span><?php echo esc_attr( $strings['googlebot'] ); ?></span>
							</td>
							<td>
								<span><?php echo esc_attr( $strings['bingbot'] ); ?></span>
							</td>
							<td>
								<span class="multiline"><?php echo esc_attr( $strings['robots_rules'] ); ?></span>
							</td>
						</tr>
					<?php

				}

				?>
			</tbody>
		</table>
	<?php
}
