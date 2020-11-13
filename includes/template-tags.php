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
				echo '<svg class="twc-logo" aria-hidden="true" focusable="false" viewBox="0 0 783 104" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2">
				<path class="twc-logo-icon" fill="currentColor" fill-rule="evenodd" stroke-width="2" paint-order="normal" d="M114.079 76.178c-9.005 15.391-26.34 25.818-46.227 25.818-19.665 0-36.834-10.2-45.924-25.312l7.348-22.613h23.347a3.059 3.059 0 003.056-3.058c0-1.68-1.37-3.05-3.056-3.05H31.261l7.844-24.146a3.059 3.059 0 00-1.962-3.85 3.058 3.058 0 00-3.85 1.965l-15.21 46.811a47.462 47.462 0 01-3.04-16.747c0-27.593 23.663-50.001 52.81-50.001 29.146 0 52.81 22.408 52.81 50 0 5.625-.982 11.029-2.793 16.08l-14.992-46.143a3.03 3.03 0 00-.742-1.217h-.007c-.33-.33-.74-.594-1.213-.748a3.25 3.25 0 00-.944-.147c-.313 0-.63.052-.944.147a3.041 3.041 0 00-1.213.748h-.007c-.33.33-.588.74-.741 1.217L78.78 78.21a3.055 3.055 0 001.961 3.85 3.047 3.047 0 003.85-1.958l15.38-47.339 14.108 43.416zM74.564 19.82c.313 0 .631.052.945.147.473.154.883.418 1.213.748h.006c.33.33.588.74.742 1.217l8.22 25.297a3.055 3.055 0 01-5.811 1.892l-5.315-16.359-15.38 47.34a3.047 3.047 0 01-3.85 1.957 3.056 3.056 0 01-1.962-3.85l18.286-56.277a3.04 3.04 0 01.743-1.217h.006c.33-.33.74-.594 1.214-.748.313-.095.631-.147.943-.147z"/>
				<path d="M158.666 80.46c-.595.595-1.361.935-2.297.935-.936 0-1.702-.34-2.298-.936-.68-.596-1.02-1.361-1.02-2.297v-57.35c0-.936.34-1.702 1.02-2.298.596-.595 1.362-.936 2.298-.936.936 0 1.702.34 2.297.936.596.596.936 1.362.936 2.298v22.974c0 .085 0 .085.085.085s.085 0 .17-.085c4.085-5.02 8.85-7.488 14.21-7.488 5.19 0 8.935 1.446 11.232 4.254 2.298 2.893 3.489 7.743 3.489 14.465v23.145c0 .936-.255 1.701-.851 2.297-.596.596-1.361.936-2.297.936-.936 0-1.702-.34-2.298-.936-.595-.596-.936-1.361-.936-2.297V56.124c0-5.701-.766-9.53-2.212-11.573-1.532-1.957-4.17-2.893-7.828-2.893-2.978 0-5.786 1.362-8.594 4.17s-4.17 5.7-4.17 8.679v23.655c0 .936-.34 1.701-.936 2.297zM214.995 82.246c-4.935 0-8.509-1.362-10.806-4.084-2.297-2.723-3.489-7.233-3.489-13.53v-24.25c0-.936.34-1.702.936-2.297.51-.596 1.277-.936 2.213-.936.936 0 1.701.34 2.297.936.596.595.936 1.361.936 2.297v23.4c0 5.105.766 8.594 2.297 10.466 1.532 1.957 4 2.893 7.318 2.893 3.063 0 5.956-1.362 8.68-4 2.722-2.637 4.084-5.445 4.084-8.594V40.382c0-.936.34-1.702 1.02-2.297.596-.596 1.362-.936 2.298-.936.936 0 1.702.34 2.297.936.596.595.936 1.361.936 2.297v37.95c0 .85-.255 1.531-.936 2.127-.51.596-1.276.936-2.127.936-.85 0-1.616-.34-2.212-.936-.596-.596-.936-1.276-.936-2.127v-3.489c0-.085 0-.085-.085-.085s-.085 0-.17.085c-3.914 4.935-8.765 7.403-14.55 7.403zm9.36-60.413c0-.851.34-1.617.936-2.213.596-.595 1.362-.936 2.213-.936.85 0 1.616.34 2.212.936.595.596.936 1.362.936 2.213v4.51c0 .85-.34 1.616-.936 2.212-.596.595-1.362.936-2.212.936-.851 0-1.617-.34-2.213-.936-.595-.596-.936-1.362-.936-2.213v-4.51zm-17.018 0c0-.851.34-1.617.936-2.213.596-.595 1.362-.936 2.213-.936.85 0 1.616.34 2.212.936.596.596.936 1.362.936 2.213v4.51c0 .85-.34 1.616-.936 2.212-.596.595-1.361.936-2.212.936-.851 0-1.617-.34-2.213-.936-.595-.596-.936-1.362-.936-2.213v-4.51zM249.201 43.956c-.766 0-1.361-.255-1.787-.766-.51-.425-.766-1.021-.766-1.787s.256-1.361.766-1.872c.426-.425 1.021-.68 1.787-.68h6.212c.51 0 .765-.256.765-.766V25.917c0-.936.34-1.702.936-2.297.596-.596 1.362-.936 2.298-.936.936 0 1.702.34 2.297.936.596.595.936 1.361.936 2.297v12.168c0 .51.255.765.766.765h12.763c.766 0 1.362.256 1.872.681.426.51.681 1.106.681 1.872s-.255 1.362-.68 1.787c-.511.51-1.107.766-1.873.766h-12.763c-.51 0-.766.255-.766.766v22.463c0 4.084.51 6.807 1.617 7.999 1.106 1.276 3.148 1.957 6.211 1.957 1.787 0 3.319-.17 4.51-.426.596-.085 1.191 0 1.787.34.51.426.766.936.766 1.532 0 .766-.255 1.532-.68 2.127-.511.596-1.107 1.021-1.873 1.106-1.446.256-3.318.426-5.616.426-5.02 0-8.424-1.021-10.296-3.148-1.957-2.043-2.893-5.786-2.893-11.317V44.72c0-.51-.255-.765-.765-.765H249.2zM290.214 43.956c-.766 0-1.362-.255-1.787-.766-.51-.425-.766-1.021-.766-1.787s.255-1.361.766-1.872c.425-.425 1.02-.68 1.787-.68h6.211c.51 0 .766-.256.766-.766V25.917c0-.936.34-1.702.936-2.297.596-.596 1.361-.936 2.297-.936.936 0 1.702.34 2.298.936.595.595.936 1.361.936 2.297v12.168c0 .51.255.765.766.765h12.763c.766 0 1.361.256 1.872.681.425.51.68 1.106.68 1.872s-.255 1.362-.68 1.787c-.51.51-1.106.766-1.872.766h-12.763c-.51 0-.766.255-.766.766v22.463c0 4.084.51 6.807 1.617 7.999 1.106 1.276 3.148 1.957 6.211 1.957 1.787 0 3.319-.17 4.51-.426.595-.085 1.191 0 1.787.34.51.426.765.936.765 1.532 0 .766-.255 1.532-.68 2.127-.51.596-1.106 1.021-1.872 1.106-1.447.256-3.319.426-5.616.426-5.02 0-8.424-1.021-10.296-3.148-1.957-2.043-2.893-5.786-2.893-11.317V44.72c0-.51-.255-.765-.766-.765h-6.211zM348.245 41.233c-7.913 0-12.167 4.68-12.933 14.04 0 .425.255.68.766.68h22.889c.425 0 .68-.255.68-.68-.34-9.36-4.169-14.04-11.402-14.04zm1.787 41.013c-6.977 0-12.253-1.957-15.911-5.786-3.66-3.744-5.446-9.53-5.446-17.188 0-7.913 1.787-13.7 5.275-17.443 3.404-3.66 8.254-5.531 14.295-5.531 11.147 0 17.103 6.637 17.784 19.826.085 1.361-.425 2.467-1.361 3.403-1.021.936-2.213 1.447-3.574 1.447h-25.187c-.425 0-.68.255-.68.765.51 10.381 5.616 15.572 15.4 15.572 3.234 0 6.553-.596 9.956-1.787.596-.255 1.192-.17 1.787.17.596.426.851.936.851 1.617 0 1.957-.85 3.148-2.638 3.573-3.658.936-7.147 1.362-10.55 1.362zM382.027 80.374c-.596.68-1.447 1.021-2.383 1.021-.936 0-1.787-.34-2.467-1.021-.596-.68-.936-1.447-.936-2.383V40.382c0-.936.34-1.702.936-2.297.595-.596 1.361-.936 2.297-.936.936 0 1.702.34 2.298.936.595.595.936 1.361.936 2.297v6.552c0 .085 0 .085.085.085s.17-.085.17-.17c1.702-2.808 4.084-5.105 7.147-6.892 3.149-1.702 6.637-2.638 10.466-2.808.766-.085 1.447.17 2.042.766.596.595.851 1.276.851 2.042 0 .765-.255 1.361-.765 1.957-.596.595-1.277.85-2.043.936-5.616.255-9.955 1.872-13.018 4.85-3.064 3.063-4.595 7.317-4.595 12.763v17.528c0 .936-.34 1.702-1.021 2.383z" fill="#de9026" fill-rule="nonzero"/>
				<path class="twc-logo-icon" fill="currentColor" fill-rule="evenodd" stroke-width="1" paint-order="normal" d="M418.274 80.8c-.425.425-.85.595-1.446.595s-1.021-.17-1.447-.596c-.425-.425-.595-.85-.595-1.446V39.106c0-.596.17-1.021.595-1.447.34-.34.766-.51 1.362-.51.595 0 1.02.17 1.446.51.426.426.596.851.596 1.447v4.85c0 .085 0 .085.085.085s.17 0 .17-.085c3.148-5.106 7.148-7.658 11.998-7.658 5.87 0 9.615 2.978 11.402 8.85 0 .084 0 .084.085.084s.17 0 .17-.085c2.978-5.871 7.318-8.85 12.848-8.85 4.425 0 7.658 1.362 9.786 4 2.042 2.723 3.063 7.062 3.063 13.019v26.037c0 .596-.17 1.021-.596 1.446-.425.426-.85.596-1.446.596s-1.021-.17-1.447-.596c-.425-.425-.595-.85-.595-1.446V54.167c0-5.361-.766-9.105-2.213-11.232-1.446-2.127-3.914-3.234-7.403-3.234-2.467 0-4.935 1.362-7.402 4.085-2.468 2.808-3.66 5.7-3.66 8.849v26.718c0 .596-.17 1.021-.595 1.446-.425.426-.85.596-1.446.596s-1.021-.17-1.447-.596c-.425-.425-.595-.85-.595-1.446V54.167c0-5.361-.766-9.19-2.213-11.317-1.531-2.042-3.999-3.149-7.403-3.149-2.467 0-4.935 1.362-7.402 4.085-2.468 2.808-3.66 5.7-3.66 8.849v26.718c0 .596-.17 1.021-.595 1.446zM499.791 56.89c-13.359 0-19.996 3.999-19.996 11.997 0 2.978.85 5.36 2.638 7.147 1.702 1.787 3.999 2.638 6.892 2.638 4.425 0 8.169-1.531 11.317-4.595 3.148-3.148 4.68-7.147 4.68-11.997v-4.425c0-.51-.255-.766-.766-.766h-4.765zm-11.232 25.356c-3.914 0-7.062-1.191-9.36-3.574-2.297-2.297-3.488-5.446-3.488-9.445 0-2.127.425-3.999 1.191-5.786.766-1.702 1.957-3.318 3.744-4.85 1.702-1.531 4.17-2.723 7.488-3.574 3.233-.85 7.147-1.276 11.657-1.276h4.765c.51 0 .766-.255.766-.766v-2.467c0-3.914-.851-6.637-2.553-8.254-1.787-1.617-4.85-2.382-9.19-2.382-4.68 0-9.274.765-13.784 2.382-.425.17-.85.085-1.276-.17-.426-.255-.596-.596-.596-1.021 0-1.277.596-2.127 1.872-2.553 4.51-1.446 9.105-2.212 13.785-2.212 5.87 0 9.955 1.106 12.252 3.318 2.383 2.298 3.574 6.212 3.574 11.743v28.079c0 .596-.17 1.021-.51 1.361-.426.426-.851.596-1.447.596-.596 0-1.021-.17-1.446-.596-.34-.34-.511-.765-.511-1.361v-6.637c0-.085 0-.085-.085-.085s-.17 0-.17.085c-1.617 2.978-3.83 5.276-6.722 6.892-2.893 1.702-6.212 2.553-9.956 2.553zM522.51 80.8c-.426.425-.937.595-1.532.595-.596 0-1.106-.17-1.532-.596-.425-.425-.595-.936-.595-1.531V39.19c0-.596.17-1.021.595-1.447.426-.425.851-.595 1.447-.595.595 0 1.106.17 1.446.595.51.426.681.851.681 1.447v9.02c0 .084 0 .084.085.084s.085 0 .085-.085c1.617-3.403 4.17-6.126 7.658-8.083 3.404-1.872 7.318-2.893 11.657-2.978.511 0 .936.17 1.362.51.34.426.51.851.51 1.362 0 .51-.17.936-.51 1.276-.34.34-.766.51-1.276.51-5.701.086-10.381 1.872-14.04 5.446-3.659 3.574-5.446 8.254-5.446 14.125v18.89c0 .595-.17 1.106-.596 1.531zM556.545 80.8c-.51.425-.936.595-1.531.595-.596 0-1.021-.17-1.447-.596-.425-.425-.595-.85-.595-1.446V19.62c0-.595.17-1.02.595-1.446.426-.426.851-.596 1.447-.596.595 0 1.02.17 1.531.596.426.425.596.85.596 1.446v37.354c0 .086 0 .086.085.086h.085l22.123-18.21c1.447-1.106 3.064-1.701 4.85-1.701.426 0 .766.17.936.595.086.51 0 .851-.34 1.106l-23.4 19.146c-.255.255-.255.595 0 .936l23.826 20.676c.255.255.34.68.17 1.106-.17.426-.426.681-.851.681-1.787 0-3.319-.596-4.68-1.787L557.31 59.782h-.085c-.085 0-.085 0-.085.085v19.486c0 .596-.17 1.021-.596 1.446zM605.64 39.616c-9.19 0-14.125 5.446-14.89 16.337 0 .596.255.851.765.851h26.718c.51 0 .766-.255.766-.85-.425-10.892-4.85-16.338-13.36-16.338zm1.617 42.63c-6.808 0-11.998-1.957-15.487-5.786-3.488-3.83-5.275-9.615-5.275-17.188 0-8.254 1.787-14.125 5.36-17.699 3.574-3.488 8.169-5.275 13.785-5.275 10.806 0 16.592 6.381 17.358 19.06.085 1.276-.34 2.382-1.276 3.318a4.82 4.82 0 01-3.489 1.447h-26.888c-.426 0-.68.255-.68.766.34 11.997 5.955 18.038 16.847 18.038 3.829 0 7.573-.68 11.232-2.042.425-.17.765-.17 1.19.17.341.17.511.596.511 1.022 0 1.19-.595 1.957-1.786 2.382-3.915 1.191-7.744 1.787-11.402 1.787zM629.041 42.254c-.425 0-.85-.17-1.191-.51a1.68 1.68 0 01-.51-1.192c0-.425.17-.85.51-1.191.34-.34.766-.51 1.191-.51h7.658c.426 0 .681-.256.681-.681V24.726c0-.596.17-1.021.68-1.447.426-.425.852-.595 1.447-.595.596 0 1.021.17 1.447.595.425.426.595.851.595 1.447V38.17c0 .425.256.68.681.68h14.295c.426 0 .851.17 1.191.511.34.34.51.766.51 1.191 0 .426-.17.851-.51 1.192-.34.34-.765.51-1.19.51H642.23c-.425 0-.68.255-.68.766v25.356c0 4.34.595 7.148 1.871 8.424 1.192 1.362 3.489 2.042 6.893 2.042 1.786 0 3.573-.255 5.275-.765.426-.086.766-.086 1.191.17.34.255.51.68.51 1.106 0 1.191-.595 1.957-1.786 2.212-1.957.426-3.829.68-5.786.68-4.68 0-7.913-1.02-9.7-2.977-1.787-1.957-2.638-5.446-2.638-10.636V43.02c0-.51-.255-.766-.68-.766h-7.659zM675.585 80.8c-.425.425-.936.595-1.531.595-.596 0-1.021-.17-1.447-.596-.425-.425-.68-.936-.68-1.531V39.276c0-.596.255-1.106.68-1.532.426-.425.851-.595 1.447-.595.595 0 1.106.17 1.531.595.426.426.596.936.596 1.532v39.992c0 .595-.17 1.106-.596 1.531zm0-55.138c-.425.425-.936.68-1.531.68-.596 0-1.021-.255-1.447-.68-.425-.426-.68-.851-.68-1.447v-4.51c0-.595.255-1.106.68-1.531.426-.426.851-.596 1.447-.596.595 0 1.106.17 1.531.596.426.425.596.936.596 1.531v4.51c0 .596-.17 1.021-.596 1.447zM693.368 80.8c-.425.425-.85.595-1.446.595s-1.021-.17-1.447-.596c-.425-.425-.596-.85-.596-1.446V39.106c0-.596.17-1.021.596-1.447.34-.34.766-.51 1.361-.51.596 0 1.022.17 1.362.51.425.426.596.851.596 1.447l.085 5.786c0 .085 0 .085.085.085l.17-.17c4-5.701 9.104-8.51 15.146-8.51 5.19 0 8.85 1.447 11.232 4.255 2.297 2.808 3.403 7.658 3.403 14.465v24.336c0 .596-.17 1.021-.595 1.446-.426.426-.851.596-1.447.596-.596 0-1.021-.17-1.361-.596-.426-.425-.596-.85-.596-1.446v-23.91c0-6.127-.936-10.296-2.723-12.508-1.787-2.127-4.765-3.234-8.764-3.234-3.233 0-6.467 1.532-9.615 4.68-3.234 3.149-4.85 6.212-4.85 9.36v25.612c0 .596-.17 1.021-.596 1.446zM748.931 39.701c-9.36 0-14.04 6.467-14.04 19.316 0 6.04 1.277 10.72 3.744 14.04 2.468 3.318 5.957 4.934 10.296 4.934 3.744 0 7.063-1.361 9.87-4.169 2.723-2.808 4.17-6.126 4.17-10.04v-9.615c0-3.915-1.447-7.318-4.255-10.211-2.808-2.808-6.126-4.255-9.785-4.255zm-.85 41.694c-5.276 0-9.446-1.957-12.594-5.956-3.148-3.914-4.68-9.36-4.68-16.422 0-8.084 1.617-13.87 4.935-17.444 3.234-3.488 7.318-5.275 12.338-5.275 6.552 0 11.487 2.552 14.806 7.743 0 .085.085.17.17.17s.085 0 .085-.085v-5.02c0-.596.17-1.021.596-1.447.425-.34.85-.51 1.446-.51s1.021.17 1.447.51c.34.426.51.851.51 1.447v40.162c0 7.573-1.531 13.018-4.68 16.507-3.148 3.489-8.168 5.19-14.89 5.19-3.83 0-7.233-.425-10.381-1.36-1.277-.426-1.872-1.192-1.872-2.468 0-.426.17-.851.595-1.107.426-.255.851-.255 1.277-.085 3.148 1.106 6.637 1.617 10.38 1.617 5.446 0 9.36-1.447 11.828-4.254 2.383-2.808 3.574-7.403 3.574-13.87v-5.53c0-.086 0-.086-.085-.086s-.17 0-.17.085c-3.319 5.02-8.169 7.488-14.636 7.488z"/>
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
