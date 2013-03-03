<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 * Template Name: Events Page
 */
 get_header();
 ?>
<div id="eventContent">
  <?php if (have_posts()) : while (have_posts()) : the_post();?>
  <div id="post-entries">
  <div <?php post_class() ?> id="post-<?php the_ID(); ?>">
		<?php the_content() ?>
  </div>
  </div>
  <?php endwhile; else: ?>
  <p>
    <?php _e('Sorry, no posts matched your criteria.'); ?>
  </p>
  <?php endif; ?>
</div>
<?php get_footer(); ?>