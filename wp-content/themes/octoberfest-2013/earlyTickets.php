<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 * Template Name: Early Tickets
 */
 get_header();
 ?>
<style type="text/css">
#earlyTickets {
	float: right;
	font-family: "museo",serif;
	font-style: normal;
	font-weight: 700;
	font-size: 16px;
	color: #86311E;
	line-height: 25px;
}
</style> 
<div id="content">
  <?php if (have_posts()) : while (have_posts()) : the_post();?>
  <div id="post-entries">
  <div <?php post_class() ?> id="post-<?php the_ID(); ?>">
  	<div id="earlyTickets">
  		Last day to purchase advance tickets is Thursday October 23rd.
  	</div>
    <h2 class="eventTitle"><?php the_title(); ?></h2>
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