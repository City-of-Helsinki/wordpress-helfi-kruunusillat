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

	<?php
		if ( $args['image'] ) {
			printf(
				'<figure class="map-figure">%s%s</figure>',
				$args['image'],
				$args['caption'] ? sprintf( '<figcaption>%s</figcaption>', esc_html( $args['caption'] ) ) : ''
			);
		} else {
			echo $args['map'];
		}
	?>
</section>
