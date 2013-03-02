<?php get_header(); ?>
<div id="content">
  <?php if (have_posts()) : while (have_posts()) : the_post();?>
  <div id="post-entries">
  <div <?php post_class() ?> id="post-<?php the_ID(); ?>">
    <h2 class="title"><?php the_title(); ?></h2>
		<?php the_content() ?>
  </div>
  </div>
  <?php endwhile; else: ?>
  <p>
    <?php _e('Sorry, no posts matched your criteria.'); ?>
  </p>
  <?php endif; ?>
	<div id="beerSpoonFork">
  </div>
</div>
<?php get_footer(); ?>