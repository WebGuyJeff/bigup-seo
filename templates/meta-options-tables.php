<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Meta Options Tables Template.
 *
 * @package bigup-seo
 */

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
		'th_page_type'    => __( 'Page Type', 'bigup-seo' ),
		'th_key'          => __( 'Key', 'bigup-seo' ),
		'th_crawlable'    => __( 'Googlebot Allowed', 'bigup-seo' ),
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
					<th scope="col" id="title" class="manage-column column-primary">
						<span><?php echo esc_attr( $strings['th_page_title'] ); ?></span>
					</th>
					<th scope="col" id="type" class="manage-column column-primary">
						<span><?php echo esc_attr( $strings['th_page_type'] ); ?></span>
					</th>
					<th scope="col" id="key" class="manage-column column-primary">
						<span><?php echo esc_attr( $strings['th_key'] ); ?></span>
					</th>
					<th scope="col" id="crawlable" class="manage-column column-primary">
						<span><?php echo esc_attr( $strings['th_crawlable'] ); ?></span>
					</th>
					<th scope="col" id="crawlable" class="manage-column column-primary">
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

					$page_type = $sub_type ? $sub_type : $page_type;

					$strings = array(

						// Visible row.
						'title'                   => $seo_page['name'],
						'type'                    => $page_type,
						'key'                     => $key,
						'crawlable'               => $seo_page['robots']['google_allowed'] ? '✅' : '❌',
						'robots_rules'            => $seo_page['robots']['status'],
						'button_edit'             => __( 'Edit', 'bigup-seo' ),
						'button_view'             => __( 'View Page', 'bigup-seo' ),
						'button_view_url'         => $seo_page['url'],
						'button_reset'            => __( 'Reset', 'bigup-seo' ),
						'edit-id'                 => 'row-' . $strings['type'] . '-' . $key,

						// Inline edit row.
						'legend'                  => $page_type . ' ' . $page_type_data['key_type'] . ' ' . $key,
						'button_save'             => __( 'Save', 'bigup-seo' ),
						'button_cancel'           => __( 'Cancel', 'bigup-seo' ),
						'title_label'             => __( 'Meta Title', 'bigup-seo' ),
						'title_placeholder'       => __( 'Enter a title', 'bigup-seo' ),
						'title_table_col'         => 'seo_title',
						'description_label'       => __( 'Meta Description', 'bigup-seo' ),
						'description_placeholder' => __( 'Enter a description', 'bigup-seo' ),
						'description_table_col'   => 'seo_description',
						'canonical_label'         => __( 'Canonical URL', 'bigup-seo' ),
						'canonical_placeholder'   => __( 'Enter a URL', 'bigup-seo' ),
						'canonical_table_col'     => 'seo_canonical',
						'google_serp_title'       => __( 'Google SERP Preview', 'bigup-seo' ),
					);

					?>
						<tr class="infoRow" data-edit-id="<?php echo esc_attr( $strings['edit-id'] ); ?>">
							<td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
								<strong><?php echo esc_attr( $strings['title'] ); ?></strong>
								<div class="row-actions hide-if-no-js">
									<span>
										<button
											data-page="<?php echo esc_attr( $strings['type'] . '-' . $strings['key'] ); ?>"
											type="button"
											class="inlineEditButton button-link editinline"
											aria-label="<?php echo esc_attr( $strings['button_edit'] ); ?>"
											aria-expanded="false"
											aria-controls="<?php echo esc_attr( $strings['edit-id'] ); ?>"
										><?php echo esc_attr( $strings['button_edit'] ); ?></button>
									</span>
									<span>|</span>
									<span>
										<a
											href="<?php echo esc_url( $strings['button_view_url'] ); ?>"
											target="_blank"
											type="button"
											class="inlineViewButton button-link"
											aria-label="<?php echo esc_attr( $strings['button_view'] ); ?>"
										><?php echo esc_attr( $strings['button_view'] ); ?></a>
									</span>
									<span>|</span>
									<span>
										<button
											type="button"
											class="inlineResetButton reset button-link"
											aria-label="<?php echo esc_attr( $strings['button_reset'] ); ?>"
											aria-expanded="false"
											aria-controls="<?php echo esc_attr( $strings['edit-id'] ); ?>"
										><?php echo esc_attr( $strings['button_reset'] ); ?></button>
									</span>
								</div>
							</td>
							<td class="has-row-actions column-primary" data-colname="Type">
								<span><?php echo esc_attr( $strings['type'] ); ?></span>
							</td>
							<td class="has-row-actions column-primary" data-colname="Key">
								<span><?php echo esc_attr( $strings['key'] ); ?></span>
							</td>
							<td class="has-row-actions column-primary" data-colname="Crawlable">
								<span><?php echo esc_attr( $strings['crawlable'] ); ?></span>
							</td>
							<td class="has-row-actions column-primary" data-colname="Crawlable">
								<span class="multiline"><?php echo esc_attr( $strings['robots_rules'] ); ?></span>
							</td>
						</tr>

						<tr style="display:none" id="hiddenRow" class="editActive hidden"></tr>
						<tr style="display:none" id="<?php echo esc_attr( $strings['edit-id'] ); ?>" class="editRow">
							<td colspan="5">
								<form method="post">
									<header class="editRow_header">
										<span class="editRow_title"><?php echo esc_attr( $strings['title'] ); ?></span>
										<span><?php echo esc_attr( $strings['legend'] ); ?></span>
									</header>
									<div class="editRow_main">
										<fieldset class="editRow_column">
											<input type="hidden" name="seo_reset_flag" class="resetFlag" value=""></input>
											<input type="hidden" name="page_type" class="resetFlag" value="<?php echo esc_attr( $strings['type'] ); ?>"></input>
											<input type="hidden" name="page_type_key" class="resetFlag" value="<?php echo esc_attr( $strings['key'] ); ?>"></input>
											<label class="field">
												<span class="field_label"><?php echo esc_attr( $strings['title_label'] ); ?></span>
												<input
													type="text"
													class="regular-text serp_titleIn"
													name="seo_title"
													id=""
													value="title"
													placeholder="<?php echo esc_attr( $strings['title_placeholder'] ); ?>"
													data-validation-ref="title"
												>
											</label>
											<label class="field">
												<span class="field_label"><?php echo esc_attr( $strings['description_label'] ); ?></span>
												<textarea
													rows="3"
													class="serp_descriptionIn"
													name="seo_description"
													id=""
													placeholder="<?php echo esc_attr( $strings['description_placeholder'] ); ?>"
													data-validation-ref="description"
												>description</textarea>
											</label>
											<label class="field">
												<span class="field_label"><?php echo esc_attr( $strings['canonical_label'] ); ?></span>
												<input
													type="url"
													class="regular-text serp_urlIn"
													name="seo_canonical"
													id=""
													value="canonical"
													placeholder="<?php echo esc_attr( $strings['canonical_placeholder'] ); ?>"
													data-validation-ref="canonical"
												>
											</label>
										</fieldset>
										<div class="editRow_column editRow_preview">
											<div class="serp">
												<h3><?php echo esc_attr( $strings['google_serp_title'] ); ?></h3>
												<div class="serp_title">No title to preview</div>
												<div class="serp_description">No description to preview</div>
											</div>
										</div>
									</div>
									<footer class="editRow_footer">
										<div class="editRow_controls">
											<button type="button" title="Submit and save" class="submitButton button button-primary save"><?php echo esc_attr( $strings['button_save'] ); ?></button>
											<button type="button" title="Cancel action" class="cancelButton button"><?php echo esc_attr( $strings['button_cancel'] ); ?></button>
										</div>
									</footer>
								</form>
							</td>
						</tr>
					<?php

				}

				?>
			</tbody>
		</table>
	<?php
}
