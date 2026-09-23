<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="primary" class="site-main pss-project-archive-main">
	<?php \PSS\Render::render_archive(); ?>
</main>
<?php
get_footer();
