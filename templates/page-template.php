<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title><?php echo esc_html( get_the_title() ); ?></title>
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
	<?php
	/**
	 * Template Name: MSF Dashboard Template
	 */
	while ( have_posts() ) :
		the_post();

		the_content();
		endwhile; // End of the loop.

		// The page footer.
		wp_footer();

		/**
		 * Fires after msf dashboard finished loading.
		 *
		 * @hooked msf_dashboard_after_footer
		 */
		do_action( 'msf_dashboard_after_footer' );
	?>
	</body>
</html>