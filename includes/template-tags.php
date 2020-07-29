<?php
/**
 * Custom template tags for this theme.
 *
 * This file is for custom template tags only and it should not contain
 * functions that will be used for filtering or adding an action.
 *
 * @package Go\Template_Tags
 */

namespace Go;

use function Go\Core\get_available_color_schemes;
use function Go\AMP\is_amp;
use function Go\Core\is_marketing_design_style;

/**
 * Return the Post Meta.
 *
 * @param int    $post_id The ID of the post for which the post meta should be output.
 * @param string $location Which post meta location to output.
 */
function post_meta( $post_id = null, $location = 'top' ) {

	echo get_post_meta( $post_id, $location ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_post_meta().

}

/**
 * Get the post meta.
 *
 * @param int    $post_id The iD of the post.
 * @param string $location The location where the meta is shown.
 */
function get_post_meta( $post_id = null, $location = 'top' ) {

	// Require post ID.
	if ( ! $post_id ) {
		return;
	}

	$page_template = get_page_template_slug( $post_id );

	// Check whether the post type is allowed to output post meta.
	$disallowed_post_types = apply_filters( 'go_disallowed_post_types_for_meta_output', array( 'page' ) );

	if ( in_array( get_post_type( $post_id ), $disallowed_post_types, true ) ) {
		return;
	}

	$post_meta                 = false;
	$post_meta_wrapper_classes = '';
	$post_meta_classes         = '';

	// Get the post meta settings for the location specified.
	if ( 'top' === $location ) {

		$post_meta                 = apply_filters(
			'go_post_meta_location_single_top',
			array(
				'author',
				'post-date',
				'comments',
				'sticky',
			)
		);
		$post_meta_wrapper_classes = ' post__meta--single post__meta--top';

	} elseif ( 'single-bottom' === $location ) {

		$post_meta                 = apply_filters(
			'go_post_meta_location_single_bottom',
			array(
				'tags',
			)
		);
		$post_meta_wrapper_classes = ' post__meta--single post__meta--single-bottom';

	}

	// If the post meta setting has the value 'empty', it's explicitly empty and the default post meta shouldn't be output.
	if ( ! $post_meta || in_array( 'empty', $post_meta, true ) ) {

		return;

	}

	// Make sure we don't output an empty container.
	$has_meta = false;

	global $post;
	$the_post = get_post( $post_id );
	setup_postdata( $the_post );

	ob_start();

	?>

	<div class="post__meta--wrapper<?php echo esc_attr( $post_meta_wrapper_classes ); ?>">

		<ul class="post__meta list-reset<?php echo esc_attr( $post_meta_classes ); ?>">

			<?php

			// Allow output of additional meta items to be added by child themes and plugins.
			do_action( 'go_start_of_post_meta_list', $post_meta, $post_id );

			// Author.
			if ( in_array( 'author', $post_meta, true ) ) {

				$has_meta = true;
				?>
				<li class="post-author meta-wrapper">
					<span class="meta-icon">
						<span class="screen-reader-text"><?php esc_html_e( 'Post author', 'go' ); ?></span>
						<?php load_inline_svg( 'author.svg' ); ?>
					</span>
					<span class="meta-text">
						<?php
						// Translators: %s = the author name.
						printf( esc_html_x( 'By %s', '%s = author name', 'go' ), '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author_meta( 'display_name' ) ) . '</a>' );
						?>
					</span>
				</li>
				<?php

			}

			// Post date.
			if ( in_array( 'post-date', $post_meta, true ) ) {
				$has_meta = true;

				?>
				<li class="post-date">
					<a class="meta-wrapper" href="<?php the_permalink(); ?>">
						<span class="meta-icon">
							<span class="screen-reader-text"><?php esc_html_e( 'Post date', 'go' ); ?></span>
							<?php load_inline_svg( 'calendar.svg' ); ?>
						</span>
						<span class="meta-text">
							<?php
							echo wp_kses(
								sprintf(
									'<time datetime="%1$s">%2$s</time>',
									esc_attr( get_the_date( DATE_W3C ) ),
									esc_html( get_the_date() )
								),
								array_merge(
									wp_kses_allowed_html( 'post' ),
									array(
										'time' => array(
											'datetime' => true,
										),
									)
								)
							);
							?>
						</span>
					</a>
				</li>
				<?php

			}

			// Categories.
			if ( in_array( 'categories', $post_meta, true ) && has_category() ) {

				$has_meta = true;
				?>
				<li class="post-categories meta-wrapper">
					<span class="meta-icon">
						<span class="screen-reader-text"><?php esc_html_e( 'Categories', 'go' ); ?></span>
						<?php load_inline_svg( 'categories.svg' ); ?>
					</span>
					<span class="meta-text">
						<?php esc_html_e( 'In', 'go' ); ?> <?php the_category( ', ' ); ?>
					</span>
				</li>
				<?php

			}

			// Tags.
			if ( in_array( 'tags', $post_meta, true ) && has_tag() ) {

				$has_meta = true;
				?>
				<li class="post-tags meta-wrapper">
					<span class="meta-icon">
						<span class="screen-reader-text"><?php esc_html_e( 'Tags', 'go' ); ?></span>
						<?php load_inline_svg( 'tags.svg' ); ?>
					</span>
					<span class="meta-text">
						<?php the_tags( '', ', ', '' ); ?>
					</span>
				</li>
				<?php

			}

			// Comments link.
			if ( in_array( 'comments', $post_meta, true ) && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {

				$has_meta = true;
				?>
				<li class="post-comment-link meta-wrapper">
					<span class="meta-icon">
						<?php load_inline_svg( 'comments.svg' ); ?>
					</span>
					<span class="meta-text">
						<?php comments_popup_link(); ?>
					</span>
				</li>
				<?php

			}

			// Sticky.
			if ( in_array( 'sticky', $post_meta, true ) && is_sticky() ) {

				$has_meta = true;
				?>
				<li class="post-sticky meta-wrapper">
					<span class="meta-icon">
						<?php load_inline_svg( 'bookmark.svg' ); ?>
					</span>
					<span class="meta-text">
						<?php esc_html_e( 'Featured', 'go' ); ?>
					</span>
				</li>
				<?php

			}

			// Allow output of additional post meta types to be added by child themes and plugins.
			do_action( 'go_end_of_post_meta_list', $post_meta, $post_id );
			?>

		</ul>

	</div>

	<?php

	wp_reset_postdata();

	$meta_output = ob_get_clean();

	if ( ! $has_meta || empty( $meta_output ) ) {

		return;

	}

	return $meta_output;

}

/**
 * Allwed HTML in the copyright site settings
 *
 * @return array Allowed HTML markup.
 */
function get_copyright_kses_html() {

	return (array) apply_filters(
		'go_copyright_kses_html',
		array(
			'div'  => array(
				'class' => array(),
			),
			'span' => array(
				'class' => array(),
			),
			'a'    => array(
				'href'  => array(),
				'class' => array(),
			),
		)
	);

}

/**
 * Returns the color selected by the user.
 *
 * @param string $color  Which color to return.
 * @param string $format The format to return the color. RGB (default) or HSL (returns an array).
 *
 * @return string|array|bool A string with the RGB value or an array containing the HSL values.
 */
function get_palette_color( $color, $format = 'RGB' ) {
	$default         = \Go\Core\get_default_color_scheme();
	$color_scheme    = get_theme_mod( 'color_scheme', $default );
	$override_colors = array(
		'primary'           => 'primary_color',
		'secondary'         => 'secondary_color',
		'tertiary'          => 'tertiary_color',
		'background'        => 'background_color',
		'header_background' => 'header_background_color',
		'footer_background' => 'footer_background_color',
	);

	$color_override = get_theme_mod( $override_colors[ $color ] );

	$avaliable_color_schemes = get_available_color_schemes();

	$the_color = '';

	if ( $color_scheme && isset( $avaliable_color_schemes[ $color_scheme ] ) && isset( $avaliable_color_schemes[ $color_scheme ][ $color ] ) ) {
		$the_color = $avaliable_color_schemes[ $color_scheme ][ $color ];
	}

	if ( $color_override ) {
		$the_color = $color_override;
	}

	if ( ! empty( $the_color ) ) {
			// Ensure we have a hash mark at the beginning of the hex value.
		$the_color = '#' . ltrim( $the_color, '#' );

		if ( 'HSL' === $format ) {
			return hex_to_hsl( $the_color );
		}

		if ( 'RGB' === $format ) {
			return hex_to_rgb( $the_color );
		}
	}

	return $the_color;
}

/**
 * Returns the default color for the active color scheme.
 *
 * @param string $color  Which color to return.
 * @param string $format The format to return the color. RGB (default) or HSL (returns an array).
 *
 * @return string|array|bool A string with the RGB value or an array containing the HSL values.
 */
function get_default_palette_color( $color, $format = 'RGB' ) {
	$default                 = \Go\Core\get_default_color_scheme();
	$color_scheme            = get_theme_mod( 'color_scheme', $default );
	$avaliable_color_schemes = get_available_color_schemes();

	$the_color = '';

	if ( $color_scheme && empty( $avaliable_color_schemes[ $color_scheme ] ) ) {
		$color_scheme_keys = array_keys( $avaliable_color_schemes );
		$color_scheme      = array_shift( $color_scheme_keys );
	}

	if ( $color_scheme && isset( $avaliable_color_schemes[ $color_scheme ] ) && isset( $avaliable_color_schemes[ $color_scheme ][ $color ] ) ) {
		$the_color = $avaliable_color_schemes[ $color_scheme ][ $color ];
	}

	if ( ! empty( $the_color ) ) {
		if ( 'HSL' === $format ) {
			return hex_to_hsl( $the_color );
		}
	}

	return $the_color;
}

/**
 * Convert a 3- or 6-digit hexadecimal color to an associative RGB array.
 *
 * @param string $color The color in hex format.
 * @param bool   $opacity Whether to return the RGB color is opaque.
 *
 * @return string
 */
function hex_to_rgb( $color, $opacity = false ) {

	if ( empty( $color ) ) {
		return false;
	}

	if ( '#' === $color[0] ) {
		$color = substr( $color, 1 );
	}

	if ( 6 === strlen( $color ) ) {
		$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
	} elseif ( 3 === strlen( $color ) ) {
		$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
	} else {
		$default                 = \Go\Core\get_default_color_scheme();
		$avaliable_color_schemes = get_available_color_schemes();
		if ( isset( $avaliable_color_schemes[ $default ] ) && isset( $avaliable_color_schemes[ $default ]['primary'] ) ) {
			$default = $avaliable_color_schemes[ $default ]['primary'];
		}
		return $default;
	}

	$rgb = array_map( 'hexdec', $hex );

	if ( $opacity ) {
		if ( abs( $opacity ) > 1 ) {
			$opacity = 1.0;
		}

		$output = 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';

	} else {

		$output = 'rgb(' . implode( ',', $rgb ) . ')';

	}

	return esc_attr( $output );

}

/**
 * Converts a hex RGB color to HSL
 *
 * @param string $hex The HSL color in hex format.
 * @param bool   $string_output Whether to return the HSL color in CSS format.
 *
 * @return array
 */
function hex_to_hsl( $hex, $string_output = false ) {
	if ( empty( $hex ) ) {
		return $string_output ? '' : array( '', '', '' );
	}

	$hex = array( $hex[1] . $hex[2], $hex[3] . $hex[4], $hex[5] . $hex[6] );
	$rgb = array_map(
		function( $part ) {
			return intval( hexdec( $part ) ) / 255.0;
		},
		$hex
	);

	$max   = max( $rgb );
	$min   = min( $rgb );
	$delta = $max - $min;
	$h     = 0;
	$s     = 0;
	$l     = 0;

	if ( 0.0 === $delta ) {
		$h = 0;
	} elseif ( $max === $rgb[0] ) {
		$h = fmod( ( $rgb[1] - $rgb[2] ) / $delta, 6 );
	} elseif ( $max === $rgb[1] ) {
		$h = ( $rgb[2] - $rgb[0] ) / $delta + 2;
	} else {
		$h = ( $rgb[0] - $rgb[1] ) / $delta + 4;
	}

	$h = round( $h * 60 );

	if ( 0 > $h ) {
		$h += 360;
	}

	$l = ( $max + $min ) / 2;
	$s = 0.0 === $delta ? 0.0 : $delta / ( 1 - abs( 2 * $l - 1 ) );
	$s = abs( round( $s * 100 ) );
	$l = abs( round( $l * 100 ) );

	if ( $string_output ) {
		return "{$h}, {$s}%, ${l}%";
	}

	return array( $h, $s, $l );
}

/**
 * Returns whether there is a footer background or not.
 *
 * @return boolean
 */
function has_header_background() {

	$background_color = get_palette_color( 'header_background' );

	if ( $background_color ) {
		return 'has-background';
	}
}

/**
 * Includes the selected footer varation
 *
 * @return void
 */
function footer_variation() {
	$variations         = \Go\Core\get_available_footer_variations();
	$selected_variation = \Go\Core\get_footer_variation();

	if ( is_customize_preview() ) {
		foreach ( $variations as $variation ) {
			call_user_func( $variation['partial'] );
		}
	} elseif ( $selected_variation ) {
		call_user_func( $selected_variation['partial'] );
	}
}

/**
 * Returns whether there is a footer background or not.
 *
 * @return boolean
 */
function has_footer_background() {

	$background_color = get_palette_color( 'footer_background' );

	if ( $background_color ) {
		return 'has-background';
	}
}

/**
 * Displays the footer copyright text.
 *
 * @param array $args {
 *   Optional. An array of arguments.
 *
 *   @type string $class The div class. Default is .site-info
 * }
 *
 * @return void
 */
function copyright( $args = array() ) {

	$args = wp_parse_args(
		$args,
		array(
			'class' => 'site-info',
		)
	);

	/**
	 * Filter the footer copyright year text.
	 *
	 * @since 1.2.5
	 *
	 * @var string
	 */
	$year      = (string) apply_filters( 'go_footer_copyrght_year_text', sprintf( '&copy; %s&nbsp;', esc_html( gmdate( 'Y' ) ) ) );
	$copyright = get_theme_mod( 'copyright', \Go\Core\get_default_copyright() );
	$isMarketing = is_marketing_design_style();

	?>

	<div class="<?php echo esc_attr( $args['class'] ); ?>">

		<?php
		if ( ! empty( $year ) ) {
			echo wp_kses( $year, get_copyright_kses_html() );
		}

		if ( $copyright || is_customize_preview() ) {
			?>
			<span class="copyright">
				<?php echo wp_kses( $copyright, get_copyright_kses_html() ); ?>
			</span>
		<?php } ?>

		<?php
		if ( function_exists( 'the_privacy_policy_link' ) && $isMarketing === false ) {
			the_privacy_policy_link( '' );
		}
		?>

	</div>

	<?php
}

/**
 * Display the page title markup
 *
 * @since 0.1.0
 *
 * @return mixed Markup for the page title
 */
function page_title() {

	if ( ! is_customize_preview() && is_front_page() ) {

		return;

	}

	$show_titles = (bool) get_theme_mod( 'page_titles', true );
	$non_archive = ! is_404() && ! is_search() && ! is_archive();
	$is_shop     = function_exists( 'is_shop' ) && is_shop(); // WooCommerce shop.

	if ( ! is_customize_preview() && ! $show_titles && ( $non_archive || $is_shop ) ) {

		return;

	}

	/**
	 * Filter the page title display args.
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	$args = (array) apply_filters(
		'go_page_title_args',
		array(
			'title'   => get_the_title(),
			'wrapper' => 'h1',
			'atts'    => array(
				'class' => 'post__title m-0 text-center',
			),
			'custom'  => false,
		)
	);

	/**
	 * When $args['custom'] is true, this function will short circuit and output
	 * the value of $args['title']
	 */
	if ( $args['custom'] ) {

		echo wp_kses_post( $args['title'] );

		return;

	}

	if ( empty( $args['title'] ) ) {

		return;

	}

	$args['atts'] = empty( $args['atts'] ) ? array() : (array) $args['atts'];

	foreach ( $args['atts'] as $key => $value ) {

		$args['classes'][] = sprintf( '%s="%s"', sanitize_key( $key ), esc_attr( $value ) );

	}

	$html = esc_html( $args['title'] );

	if ( ! empty( $args['wrapper'] ) ) {

		$html = sprintf(
			'<%1$s %2$s>%3$s</%1$s>',
			sanitize_key( $args['wrapper'] ),
			implode( ' ', $args['classes'] ),
			$html
		);

	}

	foreach ( array_keys( $args['atts'] ) as $index => $attribute ) {

		$args['atts'][ $attribute ] = array();

	}

	printf(
		'<header class="page-header entry-header m-auto px fit-this-text %1$s">%2$s</header>',
		is_customize_preview() ? ( get_theme_mod( 'page_titles', true ) ? '' : 'display-none' ) : '',
		wp_kses(
			$html,
			array(
				$args['wrapper'] => $args['atts'],
			)
		)
	);

}

/**
 * Output the content wrapper class
 *
 * @param string $class Class names.
 */
function content_wrapper_class( $class = '' ) {

	if (
		( function_exists( 'is_cart' ) && is_cart() ) ||
		( function_exists( 'is_checkout' ) && is_checkout() )
	) {

		$class .= ' max-w-wide w-full m-auto px';

	}

	echo esc_attr( $class );

}

/**
 * Returns whether there are social icons set or not.
 *
 * @param array $social_icons the array of social icons.
 *
 * @return boolean
 */
function has_social_icons( $social_icons = null ) {
	if ( is_null( $social_icons ) ) {
		$social_icons = \Go\Core\get_social_icons();
	}

	return array_reduce(
		$social_icons,
		function( $previous, $social_icon ) {
			return $previous || ! empty( $social_icon['url'] );
		},
		false
	);
}

/**
 * Displays the social icons
 *
 * @param array $args {
 *   Optional. An array of arguments.
 *
 *   @type string $class The ul class. Default is .social-icons
 *   @type string $li_class The li class. Default is .social-icon-%s, where %s is the social icon slug.
 * }
 *
 * @return void
 */
function social_icons( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'class'    => 'social-icons',
			'li_class' => 'display-inline-block social-icon-%s',
		)
	);

	$social_icons     = \Go\Core\get_social_icons();
	$has_social_cions = has_social_icons( $social_icons );

	if ( ! $has_social_cions && ! is_customize_preview() ) {
		return;
	}

	?>
	<ul class="<?php echo esc_attr( $args['class'] ); ?>">
		<?php foreach ( $social_icons as $key => $social_icon ) : ?>

			<?php $visibility = empty( $social_icon['url'] ) ? ' display-none' : null; ?>

			<?php if ( ! empty( $social_icon['url'] ) || is_customize_preview() ) : ?>
				<li class="<?php echo esc_attr( sprintf( $args['li_class'], $key ) ) . esc_attr( $visibility ); ?>">
					<a class="social-icons__icon" href="<?php echo esc_url( $social_icon['url'] ); ?>" aria-label="<?php echo esc_attr( $social_icon['label'] ); ?>" rel="noopener noreferrer">
						<?php
						// Including SVGs, not template files.
						// phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound
						include $social_icon['icon'];
						?>
					</a>
				</li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * Display the site branding section, which includes a logo
 * from Customizer (if set) or site title and description.
 *
 * @param array $args {
 *   Optional. An array of arguments.
 *
 *   @type boolean $description Whether to show the Site Description. Default is true.
 * }
 * @return void
 */
function display_site_branding( $args = array() ) {
	echo '<div class="header__titles lg:flex items-center" itemscope itemtype="http://schema.org/Organization">';
		site_branding( $args );
	echo '</div>';
}

/**
 * Render the site branding or the logo.
 *
 * @param array $args Optional arguments.
 *
 * @return void
 */
function site_branding( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'description' => true,
		)
	);

	if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
		the_custom_logo();
	} else {
		$blog_name        = get_bloginfo( 'name' );
		$blog_description = get_bloginfo( 'description' );

		if ( ! empty( $blog_name ) ) {
			if (is_marketing_design_style()) {
				echo '<a class="header--logo no-underline" href="' . esc_url( home_url( '/' ) ) . '" itemprop="url" rel="home" aria-label="Zurück zur Startseite">';
				echo '<svg class="twc-logo-first tw-h-8 lg:tw-h-12" aria-hidden="true" focusable="false" viewBox="0 0 200 50" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2">
				<path class="fa-secondary" fill="currentColor" d="M0 0h200v50H0z"/>
				<path class="fa-primary" fill="currentColor" fill-rule="nonzero" d="M36.41 40.81h5.84V9.91h-5.84v12.66H23.53V9.91h-5.85v30.9h5.85V27.75H36.4v13.06zM56.46 6.77c2.3 0 3.15-1.1 3.15-2.57v-.93c0-1.46-.85-2.61-3.15-2.61s-3.14 1.15-3.14 2.61v.93c0 1.46.84 2.57 3.14 2.57zm9.3 0c2.3 0 3.14-1.1 3.14-2.57v-.93c0-1.46-.84-2.61-3.14-2.61-2.3 0-3.14 1.15-3.14 2.61v.93c0 1.46.84 2.57 3.14 2.57zM49.29 9.9v18.24c0 9.07 3.14 13.2 11.82 13.2s11.9-4.13 11.9-13.2V9.9h-5.75v19c0 4.82-1.68 7.25-6.1 7.25-4.43 0-6.11-2.43-6.11-7.26V9.91h-5.76zM100.9 15.09V9.91H77.54v5.18h8.77v25.72h5.84V15.09h8.77zM127.33 15.09V9.91h-23.37v5.18h8.77v25.72h5.84V15.09h8.76zM152.48 40.81v-5.18h-14.52v-7.88h12.84v-5.18h-12.84V15.1h14.52V9.91h-20.36v30.9h20.36zM164.52 28.9h5.53l5.76 11.91h6.5l-6.33-12.57c3.81-1.29 5.76-4.52 5.76-8.77 0-5.84-3.5-9.56-9.12-9.56h-13.94v30.9h5.84v-11.9zm0-4.91V15h7.52c2.26 0 3.63 1.2 3.63 3.45v2.04c0 2.26-1.37 3.5-3.63 3.5h-7.52z"/>
			  </svg>
			  <svg class="twc-logo-second tw-h-8 lg:tw-h-12" aria-hidden="true" focusable="false" viewBox="0 0 200 50" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2">
				<path class="fa-secondary" fill="currentColor" d="M0 0h200v50H0z"/>
				<path class="fa-primary" fill="currentColor" d="M17.68 35.32h2.79V18.64h.14l1.69 3.4 4.73 8.67 4.74-8.67 1.68-3.4h.15v16.68h2.78V14.68H32.9l-5.74 10.85h-.12L21.3 14.68h-3.6v20.64zM57.6 35.32l-7.14-20.64h-3.6l-7.17 20.64h2.9l1.99-5.82h8.04l1.95 5.82h3.02zm-5.69-8.37h-6.65l3.25-9.64h.15l3.25 9.64zM63.78 26.75h4.52l4.3 8.57h3.22l-4.59-8.87c2.66-.68 4.05-2.72 4.05-5.71 0-3.79-2.22-6.06-5.85-6.06H60.9v20.64h2.87v-8.57zm0-2.49v-7.04h5.53c1.83 0 2.93 1 2.93 2.78v1.48c0 1.77-1.1 2.78-2.93 2.78h-5.53zM92.38 35.32h3.52l-8.1-11.86 7.54-8.78h-3.43l-5.95 7.1-2.93 3.66h-.11V14.68h-2.87v20.64h2.87v-6.68l2.87-3.26 6.6 9.94zM112.03 35.32v-2.54h-10v-6.63h9.26v-2.54h-9.26v-6.39h10v-2.54H99.16v20.64h12.87zM120.96 35.32h2.87v-18.1h6.39v-2.54h-15.65v2.54h6.39v18.1zM141.05 35.32V32.9h-2.87V17.1h2.87v-2.42h-8.61v2.42h2.87v15.8h-2.87v2.42h8.6zM157.9 35.32h3.2V14.68h-2.78v16.47h-.09l-2.84-5.17-6.77-11.3h-3.2v20.64h2.79V18.85h.08l2.84 5.17 6.78 11.3zM179.74 35.32h2.57v-10.8h-7.18v2.5h4.4v1.86c0 2.84-2.27 4.23-5.03 4.23-3.7 0-6-2.49-6-6.45V23.3c0-3.96 2.34-6.42 5.83-6.42a5.42 5.42 0 015.23 3.4l2.37-1.38a8.04 8.04 0 00-7.6-4.59c-5.36 0-8.9 3.82-8.9 10.71 0 6.92 3.54 10.65 8.54 10.65 3.14 0 5.2-1.48 5.65-3.43h.12v3.07z"/>
			  </svg>';
				echo '</a>';
			} else {
				echo '<a class="display-inline-block no-underline" href="' . esc_url( home_url( '/' ) ) . '" itemprop="url" rel="home" aria-label="Zurück zur Startseite">';
				printf(
					'<%1$s class="site-title">' . esc_html( $blog_name ) . '</%1$s>',
					( is_front_page() && ! did_action( 'get_footer' ) ) ? 'h1' : 'span'
				);
				echo '</a>';
			}

		}

		if ( true === $args['description'] && ! empty( $blog_description ) && !is_marketing_design_style()) :
			echo '<span class="site-description display-none sm:display-block relative text-sm">' . esc_html( $blog_description ) . '</span>';
		endif;
	}
}

/**
 * Display the navigation toggle button.
 *
 * @return void
 */
function navigation_toggle() {

	if ( is_amp() ) {
		?>
		<amp-state id="mainNavMenuExpanded">
			<script type="application/json">false</script>
		</amp-state>
		<?php
	}
	?>

	<div class="header__nav-toggle">
		<button
			id="nav-toggle"
			class="nav-toggle"
			type="button"
			aria-controls="header__navigation"
			<?php
			if ( is_amp() ) {
				?>
				on="tap:AMP.setState( { mainNavMenuExpanded: ! mainNavMenuExpanded } )"
				aria-expanded="false"
				[aria-expanded]="mainNavMenuExpanded ? 'true' : 'false'"
				<?php
			}
			?>
		>
			<div class="nav-toggle-icon">
				<?php load_inline_svg( 'menu.svg' ); ?>
			</div>
			<div class="nav-toggle-icon nav-toggle-icon--close">
				<?php load_inline_svg( 'close.svg' ); ?>
			</div>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'go' ); ?></span>
		</button>
	</div>

	<?php
}

/**
 * Display the header search toggle button.
 *
 * @return void
 */
function search_toggle() {
	?>

	<button
		id="header__search-toggle"
		class="header__search-toggle"
		data-toggle-target=".search-modal"
		data-set-focus=".search-modal .search-form__input"
		type="button"
		aria-controls="js-site-search"
		<?php
		if ( is_amp() ) {
			?>
			on="tap:AMP.setState( { searchModalActive: ! searchModalActive } )"
			<?php
		}
		?>
	>
		<div class="search-toggle-icon">
			<?php load_inline_svg( 'search.svg' ); ?>
		</div>
		<span class="screen-reader-text"><?php esc_html_e( 'Search Toggle', 'go' ); ?></span>
	</button>

	<?php
}

/**
 * Output an inline SVG.
 *
 * @param string $filename The filename of the SVG you want to load.
 *
 * @return void
 */
function load_inline_svg( $filename ) {

	$design_style = Core\get_design_style();

	ob_start();

	locate_template(
		array(
			sprintf(
				'dist/images/design-styles/%1$s/%2$s',
				sanitize_title( $design_style['label'] ),
				$filename
			),
			"dist/images/{$filename}",
		),
		true,
		false
	);

	echo wp_kses(
		ob_get_clean(),
		array_merge(
			wp_kses_allowed_html( 'post' ),
			array(
				'svg'  => array(
					'role'        => true,
					'width'       => true,
					'height'      => true,
					'fill'        => true,
					'xmlns'       => true,
					'viewbox'     => true,
					'aria-hidden' => true,
				),
				'path' => array(
					'd'              => true,
					'fill'           => true,
					'fill-rule'      => true,
					'stroke'         => true,
					'stroke-width'   => true,
					'stroke-linecap' => true,
				),
				'g'    => array(
					'd'    => true,
					'fill' => true,
				),
			)
		)
	);

}
