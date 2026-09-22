<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="primary" class="site-main pss-project-single-main">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php \PSS\Render::render_single(); ?>
	<?php endwhile; ?>
</main>
<?php
get_footer();
