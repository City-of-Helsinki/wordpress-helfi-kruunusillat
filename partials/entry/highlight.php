<?php
global $post;
$post = $args['post'];
setup_postdata($post);
?>
<article id="post-<?php the_id(); ?>" class="<?php helsinki_entry_classes( 'highlight entry--post' ); ?>">
	<div class="grid">
		<div class="grid__column l-8">
			<?php
				if ( has_post_thumbnail() ) {
					the_post_thumbnail('large', array('class' => 'entry__thumbnail ' . 'large'));
				} else {
					helsinki_entry_default_image('large', array('class' => 'entry__thumbnail ' . 'large'));
				}
			?>
		</div>
		<div class="grid__column l-4">
			<header class="entry__header">
				<h2 class="entry__title"><?php the_title(); ?></h2>
			</header>

			<div class="entry__meta meta">
				<time class="date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
					<?php echo get_the_date(); ?>
				</time>
			</div>

			<div class="entry__excerpt"><?php the_excerpt(); ?></div>

			<a class="entry__more" href="<?php the_permalink(); ?>">
				<?php esc_html_e('Read more', 'helsinki-universal'); ?>
			</a>

		</div>
	</div>
</article>
<?php wp_reset_postdata(); ?>
