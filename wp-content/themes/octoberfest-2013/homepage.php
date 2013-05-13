<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 * Template Name: Home Page
 */
 ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head profile="http://gmpg.org/xfn/11">
<title>
<?php if (is_home()) { echo bloginfo('name');
			} elseif (is_404()) {
			echo '404 Not Found';
			} elseif (is_category()) {
			echo 'Category:'; wp_title('');
			} elseif (is_search()) {
			echo 'Search Results';
			} elseif ( is_day() || is_month() || is_year() ) {
			echo 'Archives:'; wp_title('');
			} else {
			echo wp_title('');
			}
			?>
</title>
<meta http-equiv="content-type" content="<?php bloginfo('html_type') ?>; charset=<?php bloginfo('charset') ?>" />
<meta name="description" content="<?php bloginfo('description') ?>" />
<?php if(is_search()) { ?>
<meta name="robots" content="noindex, nofollow" />
<?php }?>
<link rel="stylesheet" type="text/css" href="<?php bloginfo('stylesheet_url'); ?>" media="screen" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<script type="text/javascript" src="//use.typekit.net/aqp8yiy.js"></script>
<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
<?php wp_head(); ?>
</head>
<body>
<!-- header START -->
<div id="contentWrapper">
		<div id="topCartButtons">
		<a id="topCartArrows" href="<?php echo home_url(); ?>/about/home/">About Octoberfest</a>
	</div>
		<a id="homeLogo" href="<?php echo get_permalink( 56 ); ?>" title="Come Celebrate!"></a>
	<div id="hiddenHomeLogo">
		<img src="<?php bloginfo('template_url'); ?>/images/MaconOctoberfest-Logo2013.png" alt="<?php bloginfo('name'); ?>" />
	</div>
	<?php if (have_posts()) : while (have_posts()) : the_post();?>
		<div id="homeText">
			<?php the_content() ?>
		</div>
	<?php endwhile; else: ?>
    <?php _e('Sorry, no posts matched your criteria.'); ?>
  <?php endif; ?>
  <div id="homeButtonsWrapper">
  	<div id="homeButtons">
  		<a href="<?php echo home_url(); ?>/buy-tickets/" id="buyTicketsHome" title="Buy Tickets">
  		</a>
  		<a href="<?php echo home_url(); ?>/register/" id="registerHome" title="Register for Competitions">
  		</a>
  		<a href="<?php echo home_url(); ?>/events/schedule/" id="scheduleHome" title="Look at Schedule">
  		</a>
  	</div>
  </div>
  <div id="navigationRibbonHome">
  	<div class="navColumn">
  		<ul id="navList">
  			<?php include 'navigation.php'; ?>
  		</ul>
  	</div>
  </div>
  <?php include 'sponsors.php'; ?>
<?php get_footer(); ?>