<?php

add_action( "category_add_form_fields", 'kruunusillat_category_fields_add', 10, 2 );
add_action( "created_category", 'kruunusillat_category_fields_save', 10, 2 );
add_action( "category_edit_form_fields", 'kruunusillat_category_fields_edit', 10, 2 );
add_action( "edited_category", 'kruunusillat_category_fields_update', 10, 2 );

function kruunusillat_category_fields_add($taxonomy) {
	wp_nonce_field( 'kruunusillat_category_meta', 'kruunusillat_category_meta_nonce', true, true );
  ?>
  <div class="form-field term-group">
	  <label for="category_page_id"><?php esc_html_e('Page'); ?></label>
	  <?php
	  wp_dropdown_pages(array(
		  'name' => 'category_page_id',
		  'id' => 'category_page_id',
		  'show_option_none' => '--',
		  'option_none_value' => '',
	  ));
	  ?>
  </div>
  <div class="form-field term-group">
	  <label for="category_map_url"><?php esc_html_e('Map url', 'helsinki-universal'); ?></label>
	  <input type="url" name="category_map_url" value="">
  </div>
  <?php
}

function kruunusillat_category_fields_save( $term_id, $tt_id ){
	if (
		empty( $_POST['kruunusillat_category_meta_nonce'] ) ||
		! wp_verify_nonce( $_POST['kruunusillat_category_meta_nonce'], 'kruunusillat_category_meta' )
	) {
		return;
	}

	$page_id = $_POST['category_page_id'] ?? '';
	if ( $page_id ) {
		add_term_meta( $term_id, 'category_page_id', absint( $page_id ), true );
	}

	$map_url = $_POST['category_map_url'] ?? '';
	if ( $map_url ) {
		add_term_meta( $term_id, 'category_map_url', esc_url_raw( $map_url ), true );
	}
}

function kruunusillat_category_fields_edit( $term, $taxonomy ){
	wp_nonce_field( 'kruunusillat_category_meta', 'kruunusillat_category_meta_nonce', true, true );
    ?>
    <tr class="form-field term-group-wrap">
      <th scope="row"><label for="category_page_id"><?php esc_html_e('Page'); ?></label></th>
      <td>
		  <?php
		  wp_dropdown_pages(array(
		  	'name' => 'category_page_id',
		  	'id' => 'category_page_id',
		  	'show_option_none' => '--',
		  	'option_none_value' => '',
			'selected' => esc_attr(kruunusillat_category_page_id($term->term_id)),
		  ));
		  ?>
      </td>
    </tr>
    <tr class="form-field term-group-wrap">
      <th scope="row"><label for="category_map_url"><?php esc_html_e('Map url', 'helsinki-universal'); ?></label></th>
      <td>
		  <input type="url" name="category_map_url" value="<?php echo esc_url(kruunusillat_category_map_url($term->term_id)); ?>">
      </td>
    </tr>
    <?php
}

function kruunusillat_category_fields_update( $term_id, $tt_id ){
	if (
		empty( $_POST['kruunusillat_category_meta_nonce'] ) ||
		! wp_verify_nonce( $_POST['kruunusillat_category_meta_nonce'], 'kruunusillat_category_meta' )
	) {
		return;
	}

	$previous_page = kruunusillat_category_page_id($term_id);
	$category_page = isset($_POST['category_page_id']) ? absint($_POST['category_page_id']) : '';
	update_term_meta( $term_id, 'category_page_id', $category_page );

	if ( $category_page ) {
		update_post_meta( $category_page, 'district_category', $term_id );
	}

	if ( $previous_page && $previous_page != $category_page ) {
		update_post_meta( $previous_page, 'district_category', '' );
	}

	if ( isset( $_POST['category_map_url'] ) ) {
		update_term_meta( $term_id, 'category_map_url', esc_url_raw( $_POST['category_map_url'] ) );
	}
}

function kruunusillat_category_page_id( int $term_id ) {
	return get_term_meta( $term_id, 'category_page_id', true );
}

function kruunusillat_category_map_url( int $term_id ) {
	return get_term_meta( $term_id, 'category_map_url', true );
}
