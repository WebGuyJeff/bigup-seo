<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Meta Page Tables Template.
 *
 * @package bigup-seo
 */

// Variables passed by the calling function.
[ 'db_meta' => $db_meta, 'pages_map' => $pages_map ] = $passed_variables;

/**
 * Generate a table for each page type.
 */
foreach ( $pages_map as $pages_type => $pages_data ) {

	$strings = array(
		'title'         => $pages_data['label'],
		'type'          => $pages_type,
		'th_page_title' => __( 'Page Title', 'bigup-seo' ),
		'th_page_type'  => __( 'Type', 'bigup-seo' ),
		'th_key'        => __( 'Key', 'bigup-seo' ),
		'th_meta_title' => __( 'Meta Title', 'bigup-seo' ),
		'th_meta_desc'  => __( 'Meta Description', 'bigup-seo' ),
	);
	?>
		<h2><?php echo esc_attr( $strings['title'] ); ?></h2>
		<table id="metaOptions_<?php echo esc_attr( $strings['type'] ); ?>" role="presentation" class="wp-list-table widefat fixed striped table-view-list">
			<thead>
				<tr>
					<th scope="col" class="column-primary">
						<span><?php echo esc_attr( $strings['th_page_title'] ); ?></span>
					</th>
					<th scope="col">
						<span><?php echo esc_attr( $strings['th_page_type'] ); ?></span>
					</th>
					<th scope="col">
						<span><?php echo esc_attr( $strings['th_key'] ); ?></span>
					</th>
					<th scope="col">
						<span><?php echo esc_attr( $strings['th_meta_title'] ); ?></span>
					</th>
					<th scope="col">
						<span><?php echo esc_attr( $strings['th_meta_desc'] ); ?></span>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php

				/**
				 * Generate table rows for each page.
				 */
				foreach ( $pages_data['pages'] as $key => $page_data ) {
					$page_meta  = $db_meta->$pages_type->$key ?? null;
					$meta_title = $page_meta->meta_title ?? null;
					$meta_desc  = $page_meta->meta_description ?? '';
					$strings    = array(

						// Visible row.
						'title'                   => $page_data['name'],
						'type'                    => $pages_type,
						'key'                     => $key,
						'meta_title'              => $meta_title ?? $page_data['name'],
						'meta_desc'               => $meta_desc,
						'button_edit'             => __( 'Edit', 'bigup-seo' ),
						'button_view'             => __( 'View Page', 'bigup-seo' ),
						'button_view_url'         => $page_data['url'],
						'button_reset'            => __( 'Reset', 'bigup-seo' ),
						'edit-id'                 => 'row-' . $strings['type'] . '-' . $key,

						// Inline edit row.
						'subtitle'                => $pages_type . ' ' . $pages_data['key_type'] . ' ' . $key,
						'button_save'             => __( 'Save', 'bigup-seo' ),
						'button_cancel'           => __( 'Cancel', 'bigup-seo' ),
						'title_label'             => __( 'Meta Title', 'bigup-seo' ),
						'title_value'             => $meta_title ?? '',
						'title_placeholder'       => __( 'Enter a title', 'bigup-seo' ),
						'title_table_col'         => 'meta_title',
						'description_label'       => __( 'Meta Description', 'bigup-seo' ),
						'description_value'       => $meta_desc ?? '',
						'description_placeholder' => __( 'Enter a description', 'bigup-seo' ),
						'description_table_col'   => 'meta_description',
						'canonical_label'         => __( 'Canonical URL', 'bigup-seo' ),
						'canonical_value'         => $page_meta->meta_canonical ?? '',
						'canonical_placeholder'   => __( 'Enter a URL', 'bigup-seo' ),
						'canonical_table_col'     => 'meta_canonical',
						'serp_preview_title'      => __( 'SERP Preview', 'bigup-seo' ),
						'serp_preview_meta_desc'  => ! empty( $meta_desc ) ? $meta_desc : __( 'No description to preview', 'bigup-seo' ),
					);
					?>
						<tr class="infoRow" data-edit-id="<?php echo esc_attr( $strings['edit-id'] ); ?>">
							<td class="has-row-actions column-primary">
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
							<td>
								<span><?php echo esc_attr( $strings['type'] ); ?></span>
							</td>
							<td>
								<span><?php echo esc_attr( $strings['key'] ); ?></span>
							</td>
							<td class="inlineMetaTitle">
								<span><?php echo esc_attr( $strings['meta_title'] ); ?></span>
							</td>
							<td class="inlineMetaDesc">
								<span><?php echo esc_attr( $strings['meta_desc'] ); ?></span>
							</td>
						</tr>

						<tr style="display:none" id="hiddenRow" class="editActive hidden"></tr>
						<tr style="display:none" id="<?php echo esc_attr( $strings['edit-id'] ); ?>" class="editRow">
							<td colspan="5">
								<form method="post">
									<header class="editRow_header">
										<span class="editRow_title"><?php echo esc_attr( $strings['title'] ); ?></span>
										<span><?php echo esc_attr( $strings['subtitle'] ); ?></span>
									</header>
									<div class="editRow_main">
										<fieldset class="editRow_column">
											<input type="hidden" name="seo_reset_flag" class="resetFlag" value="">
											<input type="hidden" name="page_type" value="<?php echo esc_attr( $strings['type'] ); ?>">
											<input type="hidden" name="page_type_key" value="<?php echo esc_attr( $strings['key'] ); ?>">
											<label class="field">
												<span class="field_label"><?php echo esc_attr( $strings['title_label'] ); ?></span>
												<input
													type="text"
													class="regular-text serp_titleIn"
													name="meta_title"
													id=""
													value="<?php echo esc_attr( $strings['title_value'] ); ?>"
													placeholder="<?php echo esc_attr( $strings['title_placeholder'] ); ?>"
												>
											</label>
											<label class="field">
												<span class="field_label"><?php echo esc_attr( $strings['description_label'] ); ?></span>
												<textarea
													rows="3"
													class="serp_descriptionIn"
													name="meta_description"
													id=""
													placeholder="<?php echo esc_attr( $strings['description_placeholder'] ); ?>"
												><?php echo esc_attr( $strings['description_value'] ); ?></textarea>
											</label>
											<label class="field">
												<span class="field_label"><?php echo esc_attr( $strings['canonical_label'] ); ?></span>
												<input
													type="url"
													class="regular-text serp_urlIn"
													name="meta_canonical"
													id=""
													value="<?php echo esc_attr( $strings['canonical_value'] ); ?>"
													placeholder="<?php echo esc_attr( $strings['canonical_placeholder'] ); ?>"
												>
											</label>
										</fieldset>
										<div class="editRow_column editRow_preview">
											<div class="serp">
												<h3><?php echo esc_html( $strings['serp_preview_title'] ); ?></h3>
												<div class="serp_title"><?php echo esc_html( $strings['meta_title'] ); ?></div>
												<div class="serp_description"><?php echo esc_html( $strings['serp_preview_meta_desc'] ); ?></div>
											</div>
										</div>
									</div>
									<footer class="editRow_footer">
										<div class="notices"></div>
										<div class="editRow_controls">
											<button type="button" title="Save" class="submitButton button button-primary save"><?php echo esc_attr( $strings['button_save'] ); ?></button>
											<button type="button" title="Cancel" class="cancelButton button"><?php echo esc_attr( $strings['button_cancel'] ); ?></button>
											<button type="button" title="Reset" class="resetButton button"><?php echo esc_attr( $strings['button_reset'] ); ?></button>
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
