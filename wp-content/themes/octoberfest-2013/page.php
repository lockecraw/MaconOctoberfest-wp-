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
	<div id="contentFooter"> 
	<a href="<?php echo site_url(); ?>/about/home/">Home</a>
	<a href="<?php echo site_url(); ?>/events/">Events</a> 
	<a href="<?php echo site_url(); ?>/go-local-ga/music-line-up/">Music</a>
	<a href="<?php echo site_url(); ?>/go-local-ga/breweries/">Breweries</a> 
	<a href="<?php echo site_url(); ?>/causes/">Causes</a> 
	<a href="<?php echo site_url(); ?>/causes/donate/">Donate</a>
	<a href="<?php echo site_url(); ?>/about/">About</a> 
	<a href="<?php echo site_url(); ?>/about/contact/">Contact</a>
  </div>
</div>
<?php get_footer(); ?>