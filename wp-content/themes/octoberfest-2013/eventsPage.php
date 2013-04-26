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
<div id="contentFooter"> 
	<a href="<?php echo home_url(); ?>/about/home/">Home</a>
	<a href="<?php echo home_url(); ?>/events/">Events</a> 
	<a href="<?php echo home_url(); ?>/go-local-ga/music-line-up/">Music</a>
	<a href="<?php echo home_url(); ?>/go-local-ga/breweries/">Breweries</a> 
	<a href="<?php echo home_url(); ?>/causes/">Causes</a> 
	<a href="<?php echo home_url(); ?>/causes/donate/">Donate</a>
	<a href="<?php echo home_url(); ?>/about/">About</a> 
	<a href="<?php echo home_url(); ?>/about/contact/">Contact</a>
  </div>
</div>
<?php get_footer(); ?>