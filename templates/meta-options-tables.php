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
		'title'         => $page_type_data['label'],
		'type'          => $page_type,
		'th_page_title' => __( 'Page Title', 'bigup-seo' ),
		'th_page_type'  => __( 'Page Type', 'bigup-seo' ),
		'th_key'        => __( 'Key', 'bigup-seo' ),
		'th_crawlable'  => __( 'Crawling Allowed', 'bigup-seo' ),
	);

	/**
	 * Generate a table for each page type.
	 */
	?>
		<h2><?php echo esc_attr( $strings['title'] ); ?></h2>
		<table id="metaOptions_<?php echo esc_attr( $strings['type'] ); ?>" role="presentation" class="metaOptions wp-list-table widefat fixed striped table-view-list">
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
				</tr>
			</thead>
			<tbody>
				<?php

				/**
				 * Generate table rows for each page.
				 */
				foreach ( $page_type_data['pages'] as $key => $seo_page ) {

					// To do: Add a source for this status.
					$seo_page['crawlable'] = true;

					$page_type = $sub_type ? $sub_type : $page_type;

					$strings = array(

						// Visible row.
						'title'                   => $seo_page['name'],
						'type'                    => $page_type,
						'key'                     => $key,
						'crawlable'               => $seo_page['crawlable'] ? '✔' : '',
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
						'description_label'       => __( 'Meta Description', 'bigup-seo' ),
						'description_placeholder' => __( 'Enter a description', 'bigup-seo' ),
						'canonical_label'         => __( 'Canonical URL', 'bigup-seo' ),
						'canonical_placeholder'   => __( 'Enter a URL', 'bigup-seo' ),
					);

					?>
						<tr data-edit-id="<?php echo esc_attr( $strings['edit-id'] ); ?>">
							<td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
								<strong><?php echo esc_attr( $strings['title'] ); ?></strong>
								<div class="row-actions">
									<span class="inline hide-if-no-js">
										<button
											data-page="<?php echo esc_attr( $strings['type'] . '-' . $strings['key'] ); ?>"
											type="button"
											class="inlineEditButton button-link editinline"
											aria-label="<?php echo esc_attr( $strings['button_edit'] ); ?>"
											aria-expanded="false"
											aria-controls="<?php echo esc_attr( $strings['edit-id'] ); ?>"
										><?php echo esc_attr( $strings['button_edit'] ); ?></button>
									</span>
									<span class="inline hide-if-no-js">|</span>
									<span class="inline hide-if-no-js">
										<a
											href="<?php echo esc_url( $strings['button_view_url'] ); ?>"
											target="_blank"
											type="button"
											class="inlineViewButton button-link"
											aria-label="<?php echo esc_attr( $strings['button_view'] ); ?>"
										><?php echo esc_attr( $strings['button_view'] ); ?></a>
									</span>
									<span class="inline hide-if-no-js">|</span>
									<span class="inline hide-if-no-js">
										<button
											type="button"
											class="inlineResetButton button-link"
											aria-label="<?php echo esc_attr( $strings['button_reset'] ); ?>"
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
						</tr>

						<tr style="display:none" id="hiddenRow" class="editActive hidden"></tr>
						<tr style="display:none" id="<?php echo esc_attr( $strings['edit-id'] ); ?>" class="editActive inline-edit-row inline-edit-row-page quick-edit-row quick-edit-row-page inline-edit-page inline-editor">
							<td colspan="4">
								<form method="post" class="inline-edit-wrapper" data-type-form="edit">
									<h3><?php echo esc_attr( $strings['title'] ); ?></h3>
									<fieldset class="inline-edit-fieldset">
										<legend><?php echo esc_attr( $strings['legend'] ); ?></legend>
										<label class="field"><span class="field_label"><?php echo esc_attr( $strings['title_label'] ); ?></span>
											<input
												type="text"
												class="regular-text"
												name=""
												id=""
												value=""
												placeholder="<?php echo esc_attr( $strings['title_placeholder'] ); ?>"
											>
										</label>
										<label class="field"><span class="field_label"><?php echo esc_attr( $strings['description_label'] ); ?></span>
											<textarea
												rows="3"
												name=""
												id=""
												value=""
												placeholder="<?php echo esc_attr( $strings['description_placeholder'] ); ?>"
											></textarea>
										</label>
										<label class="field"><span class="field_label"><?php echo esc_attr( $strings['canonical_label'] ); ?></span>
											<input
												type="url"
												class="regular-text"
												name=""
												id=""
												value=""
												placeholder="<?php echo esc_attr( $strings['canonical_placeholder'] ); ?>"
											>
										</label>
									</fieldset>
									<div class="submit inline-edit-save">
										<button type="button" title="Submit and save" id="submitButton" class="button button-primary save"><?php echo esc_attr( $strings['button_save'] ); ?></button>
										<button type="button" title="Cancel action" id="cancelButton" class="button"><?php echo esc_attr( $strings['button_cancel'] ); ?></button>
									</div>
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
