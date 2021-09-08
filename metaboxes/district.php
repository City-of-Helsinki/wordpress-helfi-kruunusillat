<?php
/**
 * Register meta box(es).
 */
function kruunusillat_district_metabox( $post ) {
	if ( 'templates/district.php' === get_page_template_slug( $post->ID ) ) {
		add_meta_box(
			'district-details',
			__( 'Category' ),
			'kruunusillat_district_metabox_callback',
			'page',
			'side'
		);
	}
}
add_action( 'add_meta_boxes_page', 'kruunusillat_district_metabox' );

/**
 * Meta box display callback.
 *
 * @param WP_Post $post Current post object.
 */
function kruunusillat_district_metabox_callback( $post ) {
    wp_nonce_field( 'kruunusillat_district_metabox_' . $post->ID, 'kruunusillat_district_metabox_' . $post->ID );

	wp_dropdown_categories(array(
		'show_option_none' => '--',
		'option_none_value' => '',
		'name' => 'district_category',
		'selected' => get_post_meta( $post->ID, 'district_category', true ),
		'hide_empty' => false,
	));
}

/**
 * Save meta box content.
 *
 * @param int $post_id Post ID
 */
function kruunusillat_district_metabox_save( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( 'templates/district.php' !== get_page_template_slug( $post_id ) ) {
		return;
	}
	if (
		empty($_POST['kruunusillat_district_metabox_' . $post_id]) ||
		! wp_verify_nonce( $_POST['kruunusillat_district_metabox_' . $post_id], 'kruunusillat_district_metabox_' . $post_id )
	) {
		return;
	}

	$previous_category = get_post_meta( $post_id, 'district_category', true );
	$district_category = isset($_POST['district_category']) ? absint($_POST['district_category']) : '';
	update_post_meta( $post_id, 'district_category', $district_category );

	if ( $district_category ) {
		update_term_meta( $district_category, 'category_page_id', $post_id );
	}

	if ( $previous_category && $previous_category != $district_category ) {
		update_term_meta( $previous_category, 'category_page_id', '' );
	}
}
add_action( 'save_post_page', 'kruunusillat_district_metabox_save' );
