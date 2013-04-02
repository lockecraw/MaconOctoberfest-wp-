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
<link rel="Stylesheet" type="text/css" href="<?php bloginfo('template_url'); ?>/css/smoothDivScroll.css" />
<script type="text/javascript" src="//use.typekit.net/aqp8yiy.js"></script>
<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
<link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/js/jquery.fancybox.css?v=2.1.4" type="text/css" media="screen" />
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/jquery.fancybox.pack.js?v=2.1.4"></script>
<link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/js/helpers/jquery.fancybox-buttons.css?v=1.0.5" type="text/css" media="screen" />
<script type="text/javascript">
	$(document).ready(function() {
		$(".fancybox").fancybox();
	$(".open_fancybox").click(function() {
    
    $.fancybox.open([
        {
            href : 'http://maconoctoberfest.com/wp/wp-content/uploads/2013/04/upgrade.png'
        },
        {
            href : 'http://maconoctoberfest.com/wp/wp-content/uploads/2013/04/12oz-pink.jpg',                
            title : '12oz Beer/Wine Glass in Pink'
        },
        {
            href : 'http://maconoctoberfest.com/wp/wp-content/uploads/2013/04/16oz-pink.jpg',                
            title : '16oz Beer Stein in Pink '
        },
        {
            href : 'http://maconoctoberfest.com/wp/wp-content/uploads/2013/04/16oz-blue.jpg',                
            title : '16oz Beer Stein in Blue '
        },
        {
            href : 'http://maconoctoberfest.com/wp/wp-content/uploads/2013/04/25oz-pink.jpg',                
            title : '25oz Beer Stein in Pink'
        },
				{
            href : 'http://maconoctoberfest.com/wp/wp-content/uploads/2013/04/25oz-blue.jpg',                
            title : '25oz Beer Stein in Blue '
        }
    ], {
        padding : 0
    });
    
    return false;
    
});

	});
</script>
<?php wp_head(); ?>
</head>
<body>
<!-- header START -->
<div id="contentWrapper">
	<div id="leftContentWrapper">
		<div id="logo">
			<a id="logo" href="<?php echo get_permalink( 56 ); ?>"></a>
		</div>
		<div id="verticalRibbon">
			<div class="verticalNavColumn">
  			<ul id="verticalnavList">
  				<?php include 'navigation.php'; ?>
  			</ul>
  	</div>
		</div>
	</div>
	<div id="topCartButtons">
		<a id="topCartArrows" href="<?php echo site_url(); ?>/about/home/">Home Page</a>
		<a id="topCartArrows" href="<?php echo site_url(); ?>/event-registration/?regevent_action=show_shopping_cart">View Cart</a>
	</div>
	<div id="buttonHeader">
		<div id="buttonWrapper">
		<div id="buyTicketsWrapper">
			<a href="<?php echo site_url(); ?>/buy-tickets/" id="buyTickets" title="Buy Tickets"></a>
			<div id="buyTicktesList">
				<ul>
					<li><a href="<?php echo site_url(); ?>/event-registration/?ee=11">Beer Garden</a></li>
					<li><a href="<?php echo site_url(); ?>/event-registration/?ee=3">Founders Feast</a></li>
					<li><a href="<?php echo site_url(); ?>/event-registration/?ee=15">Brewers Brunch</a></li>
				</ul>
			</div>
		</div>
		<div id="registerWrapper">
			<a href="<?php echo site_url(); ?>/register/" id="register" title="Register for Competitions"></a>
			<div id="registerList">
				<ul>
					<li><a href="<?php echo site_url(); ?>/event-registration/?ee=8">Homebrew Contest</a></li>
					<li><a href="<?php echo site_url(); ?>/event-registration/?ee=9">Cyclo-Cross Bike Race</a></li>
					<li><a href="<?php echo site_url(); ?>/event-registration/?ee=10">Singer-songwriter Competition</a></li>
				</ul>
			</div>
		</div>
		</div>
	</div>