<?php
$district_page_id = kruunusillat_category_page_id(get_queried_object_id());
if ( ! $district_page_id ) {
	return;
}
?>
<a class="hds-button button button--primary" href="<?php echo esc_url( get_permalink( $district_page_id ) ); ?>">
	<?php esc_html_e('Schedules and results', 'helsinki-universal'); ?>
</a>
