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
		'kruunusillat' => '#70AFD7',
		'kruunusillat-light' => '#acd0e8',
		'kruunusillat-medium-light' => '#84badd',
		'kruunusillat-dark' => '#378dc4',
	);
}

add_filter('helsinki_colors', function($colors){
	return array_merge(
		$colors,
		kruunusillat_colors()
	);
}, 11);

add_filter('helsinki_scheme_root_styles_colors', function($colors, $scheme){
	if ( 'kruunusillat' !== $scheme ) {
		return $colors;
	}

	add_filter('helsinki_scheme_root_styles_use_hex', '__return_true');
	$custom = kruunusillat_colors();
	return array(
		'--primary-color' => $custom['kruunusillat'],
		'--primary-color-light' => $custom['kruunusillat-light'],
		'--primary-color-medium' => $custom['kruunusillat-medium-light'],
		'--primary-color-dark' => $custom['kruunusillat-dark'],
	);
}, 11, 2);

/**
  * Template functions
  */
function kruunusillat_district_category_link() {
	get_template_part( 'partials/district/category-link' );
}

function kruunusillat_district_category_title() {
	return '<span class="prefix">' . esc_html__('News', 'helsinki-universal') . '</span>';
}

function kruunusillat_district_link() {
	get_template_part( 'partials/district/district-link' );
}

function kruunusillat_district_map() {
	get_template_part( 'partials/district/map' );
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
		add_action('helsinki_content_header', 'kruunusillat_district_category_link', 20);
	}

	/**
	  * Categories
	  */
	if ( is_category() ) {
		add_action('helsinki_view_header', 'kruunusillat_district_link', 20);

		if ( kruunusillat_category_page_id(get_queried_object_id()) ) {
			add_filter( 'get_the_archive_title_prefix', 'kruunusillat_district_category_title', 11 );
		}

		if ( kruunusillat_category_map_url(get_queried_object_id()) ) {
			add_action('helsinki_loop_after', 'kruunusillat_district_map', 10);
		}

		add_filter('wpseo_breadcrumb_links', 'kruunusillat_district_news_breadcrumbs');

		add_action('helsinki_main_top', 'helsinki_content_breadcrumbs', 10);
	}

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
