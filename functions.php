<?php

/**
  * Child Theme Setup
  */
add_action('after_setup_theme', 'helsinki_child_theme_setup');
function helsinki_child_theme_setup() {
	/**
	  * Child Theme Parts
	  */
	require_once get_stylesheet_directory() . '/metaboxes/category.php';
	require_once get_stylesheet_directory() . '/metaboxes/district.php';

	/**
	  * Child Theme textdomain
	  */
	load_child_theme_textdomain( 'helsinki-universal', get_stylesheet_directory() . '/languages' );
}

add_action('wp_enqueue_scripts', 'helsinki_child_theme_assets');
function helsinki_child_theme_assets() {
	/**
	  * Theme version
	  */
    $version = wp_get_theme()->get('Version');

	/**
	  * Styles
	  */
	wp_enqueue_style(
		'kruunusillat',
		get_stylesheet_uri(),
		array('theme'),
		$version,
		'all'
	);
}

/**
  * Child Theme Scheme
  */
add_filter('helsinki_default_scheme', function($name){
	return 'kruunusillat';
}, 11);

function kruunusillat_colors() {
	return array(
		'primary' => array(
			'color' => '#70AFD7',
			'light' => '#acd0e8',
			'medium' => '#84badd',
			'dark' => '#378dc4',
			'content' => '#ffffff',
			'content-secondary' => '#1a1a1a',
		),
		'secondary' => '#84badd',
		'accent' => '#378dc4',
	);
}

add_filter('helsinki_colors', function($colors){
	$colors['kruunusillat'] = kruunusillat_colors();
	return $colors;
}, 11);

/**
  * Template functions
  */
function kruunusillat_district_category_link() {
	$district_category = get_post_meta(get_the_ID(), 'district_category', true);
	if ( ! $district_category ) {
		return;
	}

	get_template_part(
		'partials/district/category-link',
		null,
		array(
			'url' => get_category_link( $district_category )
		)
	);
}

function kruunusillat_district_category_title() {
	return '<span class="prefix">' . esc_html__('News', 'helsinki-universal') . '</span>';
}

function kruunusillat_district_link() {
	$district_page_id = kruunusillat_category_page_id(get_queried_object_id());
	if ( ! $district_page_id ) {
		return;
	}

	get_template_part(
		'partials/district/district-link',
		null,
		array(
			'url' => get_permalink( $district_page_id )
		)
	);
}

function kruunusillat_district_traffic_arrangement_map(): void {
	$url = kruunusillat_category_map_url( get_queried_object_id() );
	if ( ! $url ) {
		return;
	}

	get_template_part(
		'partials/district/map',
		null,
		array_merge(
			array(
				'title' => sprintf(
					'%s - %s',
					single_cat_title('', false),
					esc_html__('Traffic arrangement map', 'helsinki-universal')
				),
			),
			kruunusillat_url_to_map(
				$url,
				__( 'Traffic arrangement map', 'helsinki-universal' )
			)
		)
	);
}

function kruunusillat_district_additional_map(): void {
	$url = kruunusillat_category_map_2_url( get_queried_object_id() );
	if ( ! $url ) {
		return;
	}

	$title = kruunusillat_category_map_2_title( get_queried_object_id() );

	get_template_part(
		'partials/district/map',
		null,
		array_merge(
			array(
				'title' => $title,
			),
			kruunusillat_url_to_map( $url, $title )
		)
	);
}

function kruunusillat_render_category_map_title(array $args): void {
	if ( ! empty( $args['title'] ) ) {
		printf(
			'<h2 class="container__title">%s</h2>',
			esc_html( $args['title'] )
		);
	}
}

function kruunusillat_render_category_map(array $args): void {
	if ( ! empty( $args['image'] ) ) {
		printf(
			'<figure class="map-figure">%s%s</figure>',
			$args['image'],
			$args['caption'] ? sprintf( '<figcaption>%s</figcaption>', esc_html( $args['caption'] ) ) : ''
		);
	} else if ( ! empty( $args['map'] ) ) {
		echo $args['map'];
	}
}

function kruunusillat_url_to_map(string $url, string $alt = ''): array {
	$args = array(
		'map' => '',
		'image' => '',
		'caption' => '',
	);

	if ( kruunusillat_is_district_map_image( $url ) ) {
		$image_id = kruunusillat_district_map_to_image_id( $url );

		if ( $image_id ) {
			$args['image'] = sprintf(
				'<div class="image-wrap">%s</div>',
				wp_get_attachment_image( $image_id, 'full' )
			);
			$args['caption'] = wp_get_attachment_caption( $image_id );
		} else {
			$args['image'] = sprintf(
				'<div class="image-wrap">
					<img src="%s" alt="%s" loading="lazy">
				</div>',
				esc_url( $url ),
				esc_attr( $alt )
			);
		}
	} else {
		$args['map'] = sprintf(
			'<div class="map">
				<iframe src="%s" allowfullscreen="false"></iframe>
			</div>',
			esc_url( $url )
		);
	}

	return $args;
}

function kruunusillat_is_district_map_image( string $url ) {
	$mimes = array(
		'image/jpg',
		'image/jpeg',
		'image/png',
		'image/gif',
		'image/bmp',
		'image/tiff',
		'image/webp',
	);

	$filetype = wp_check_filetype( basename( $url ) );

	return ! empty( $filetype['type'] ) && in_array( $filetype['type'], $mimes );
}

function kruunusillat_district_map_to_image_id( string $url ) {
	return attachment_url_to_postid( $url );
}

function kruunusillat_front_page_recent_posts_query_args($args, $default) {
	$args['posts_per_page'] += 1;
	$sticky = get_option( 'sticky_posts', array() );
	if ( $sticky ) {
		$sticky_count = count($sticky);
		if ( $sticky_count < $args['posts_per_page'] ) {
			$args['ignore_sticky_posts'] = true;
			$args['post'] = $sticky;
			add_filter( 'kruunusillat_front_page_recent_posts_append_sticky', '__return_true' );
		} else {
			$args['post__in'] = $sticky;
		}
	}
	return $args;
}

function kruunusillat_front_page_recent_posts_with_highlight($args) {
	if ( apply_filters( 'kruunusillat_front_page_recent_posts_append_sticky', false ) ) {
		$sticky_ids = get_option( 'sticky_posts' );
		$sticky = get_posts(array(
			'post__in' => $sticky_ids,
		));
		// Sort out potential duplicates
		$sticky_ids = array_flip($sticky_ids);
		foreach ($args['query']->posts as $index => $post) {
			if ( isset( $sticky_ids[$post->ID] ) ) {
				unset($args['query']->posts[$index]);
			}
		}
		// prepend sticky posts
		rsort( $sticky );
		$args['query']->posts = array_merge(
			$sticky,
			$args['query']->posts
		);
		// limit posts to desired count
		array_splice(
			$args['query']->posts,
			$args['query']->query_vars['posts_per_page']
		);
	}

	$post = array_shift($args['query']->posts);
	$args['query']->post_count = count($args['query']->posts);
	$args['query']->found_posts = $args['query']->post_count;
	if ( $post ) {
		get_template_part( 'partials/entry/highlight', null, array('post' => $post) );
	}

	if ( $args['query']->posts ) {
		helsinki_front_page_recent_posts_grid($args);
	}
}

if ( function_exists('pll_register_string') ) {
	pll_register_string( 'custom_logo', get_theme_mod('custom_logo', 0), 'WordPress', false );

	add_filter('theme_mod_custom_logo', function($default){
		return pll__($default);
	});
}

/**
  * Template actions
  */
add_action('template_redirect', 'helsinki_child_template_setup', 11);
function helsinki_child_template_setup() {

	/**
		* Front page
		*/
	add_filter('helsinki_front_page_recent_posts_query_args', 'kruunusillat_front_page_recent_posts_query_args', 10, 2);
	add_action('helsinki_front_page_recent_posts', 'kruunusillat_front_page_recent_posts_with_highlight', 15);
	remove_action('helsinki_front_page_recent_posts', 'helsinki_front_page_recent_posts_grid', 20);

	/**
	  * Page templates
	  */
	if ( 'templates/district.php' === get_page_template_slug( get_the_ID() ) ) {
		add_action('helsinki_hero', 'kruunusillat_district_category_link', 20);
	}

	/**
	  * Categories
	  */
	if ( did_action( 'wpseo_loaded' ) ) {
		add_filter('kruunusillat_use_yoast_primary_category', '__return_true');
	}

	if ( is_category() ) {
		add_action('helsinki_view_header', 'kruunusillat_district_link', 20);

		if ( kruunusillat_category_page_id(get_queried_object_id()) ) {
			add_filter( 'get_the_archive_title_prefix', 'kruunusillat_district_category_title', 11 );
		}

		if ( kruunusillat_category_map_url(get_queried_object_id()) ) {
			add_action('helsinki_loop_after', 'kruunusillat_district_additional_map', 10);
			add_action('helsinki_loop_after', 'kruunusillat_district_traffic_arrangement_map', 20);

			add_action('kruunusillat_district_map', 'kruunusillat_render_category_map_title', 10);
			add_action('kruunusillat_district_map', 'kruunusillat_render_category_map', 20);
		}

		add_filter('wpseo_breadcrumb_links', 'kruunusillat_district_news_breadcrumbs');

		add_action('helsinki_main_top', 'helsinki_content_breadcrumbs', 10);
	}
}

add_filter('helsinki_entry_classes', 'kruunusillat_entry_classes');
function kruunusillat_entry_classes( $classes ) {
	if ( ! in_array('has-thumbnail', $classes) ) {
		$classes[] = 'has-thumbnail';
	}
	return $classes;
}

function kruunusillat_highlight_entry_thumbnail( $post = null ) {
	echo kruunusillat_get_highlight_entry_thumbnail( $post );
}

function kruunusillat_get_highlight_entry_thumbnail( $post = null ) {
	if ( ! $post ) {
		$post = get_post();
	}

	$image = helsinki_get_entry_image_html(	$post, 'large', array() );
	if ( ! $image ) {
		return helsinki_get_entry_image_with_wrap(
			helsinki_entry_image_icon(),
			helsinki_entry_image_classes(true)
		);
	} else {
		return helsinki_get_entry_image_with_wrap(
			$image,
			helsinki_entry_image_classes()
		);
	}
}

function kruunusillat_district_news_breadcrumbs( $crumbs ) {
	$category_page_id = kruunusillat_category_page_id( get_queried_object_id() );
	if ( ! $category_page_id ) {
		return $crumbs;
	}

	$filtered = array(
		array_shift($crumbs),
	);

	$page_ancestors = get_ancestors( $category_page_id, 'page', 'post_type' );
	if ( $page_ancestors ) {
		$page_ancestors = array_reverse($page_ancestors);
		foreach ($page_ancestors as $page_ancestor_id) {
			$filtered[] = array(
				'url' => get_permalink( $page_ancestor_id ),
				'text' => get_the_title($page_ancestor_id),
				'id' => $page_ancestor_id,
			);
		}
	}

	$filtered[] = array(
		'url' => get_permalink( $category_page_id ),
		'text' => get_the_title($category_page_id),
		'id' => $category_page_id,
	);

	return array_merge($filtered, $crumbs);
}

function kruunusillat_post_category_thumbnail_id( int $post_id ) {
	$default_cat_id = (int) get_option('default_category', 0);
	$primary_category = kruunusillat_yoast_primary_category($post_id);
	if ( $primary_category && $primary_category !== $default_cat_id ) {
		$cat_thumb_id = helsinki_category_featured_image($primary_category);
		if ( $cat_thumb_id ) {
			return $cat_thumb_id;
		}
	}

	$cat_thumb_id = 0;
	foreach (get_the_category( $post_id ) as $category) {
		if ( $category->term_id === $default_cat_id ) {
			continue;
		}
		$cat_thumb_id = helsinki_category_featured_image($category->term_id);
		if ( $cat_thumb_id ) {
			break;
		}
	}

	return $cat_thumb_id;
}

/**
  * Replaces default function of the same name
	* Diff: checks for yoast primary category first and uses default category as last option
	*/
function helsinki_get_entry_default_image( string $size = 'post-thumnbnail', array $attr = array(), int $post_id = 0 ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$cat_thumb_id = kruunusillat_post_category_thumbnail_id($post_id);
	if ( ! $cat_thumb_id ) {
		$cat_thumb_id = helsinki_category_featured_image(
			(int) get_option('default_category', 0)
		);
	}

	return $cat_thumb_id ? wp_get_attachment_image( $cat_thumb_id, $size, false, $attr ): '';
}

function kruunusillat_yoast_primary_category( int $post_id ) {
	return apply_filters( 'kruunusillat_use_yoast_primary_category', false ) ?
		(int) get_post_meta( $post_id, '_yoast_wpseo_primary_category', true ) : '';
}

add_filter( 'get_post_metadata', 'kruunusillat_get_featured_image_id_default', 10, 4 );
function kruunusillat_get_featured_image_id_default( $null, $object_id, $meta_key, $single ) {
	if ( '_thumbnail_id' !== $meta_key || is_admin() || ! is_singular( 'post' ) ) {
		return $null;
	}

	remove_filter( 'get_post_metadata', __FUNCTION__, 10, 4 );

	$featured_image_id = get_post_thumbnail_id( $object_id );

	add_filter( 'get_post_metadata', __FUNCTION__, 10, 4 );

	return $featured_image_id ?: kruunusillat_post_category_thumbnail_id( $object_id );
}

function kruunusillat_default_og_image() {
	$cat_thumb_id = kruunusillat_post_category_thumbnail_id(get_the_ID());
	return $cat_thumb_id ? wp_get_attachment_image_url($cat_thumb_id, 'large') : '';
}

add_filter( 'wpseo_twitter_image', 'kruunusillat_post_default_twitter_image', 99, 2);
function kruunusillat_post_default_twitter_image($image, $presentation) {
	if ( ! $image && is_single() && ! has_post_thumbnail() ) {
		return kruunusillat_default_og_image();
	}
	return $image;
}

add_filter( 'wpseo_add_opengraph_additional_images', 'kruunusillat_opengaph_images', 99 );
function kruunusillat_opengaph_images( $object ) {
	if ( is_single() && ! $object->has_images() ) {
		$object->add_image_by_id(
			kruunusillat_post_category_thumbnail_id( get_the_ID() )
		);
	}
}
