<?php
$district_category = get_post_meta(get_the_ID(), 'district_category', true);
if ( ! $district_category ) {
	return;
}
?>
<a class="hds-button button" href="<?php echo esc_url( get_category_link( $district_category ) ); ?>">
	<?php esc_html_e('Site news and traffic arrangement map', 'helsinki-universal'); ?>
</a>
