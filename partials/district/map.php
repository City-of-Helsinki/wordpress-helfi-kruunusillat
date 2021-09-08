<?php
$map_url = kruunusillat_category_map_url(get_queried_object_id());
if ( ! $map_url ) {
	return;
}
?>
<section class="hds-container">
	<h2 class="container__title">
		<?php
			printf(
				'%s - %s',
				single_cat_title('', false),
				esc_html__('Traffic arrangement map', 'helsinki-universal')
			);
		?>
	</h2>
	<div class="map">
		<iframe src="<?php echo esc_url($map_url); ?>" allowfullscreen="false"></iframe>
	</div>
</section>
