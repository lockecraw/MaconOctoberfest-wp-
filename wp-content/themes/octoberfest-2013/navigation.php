<li>
	<a class="arrow" href="<?php echo get_permalink( 33 ); ?>">Events</a>
  	<ul>
  		<li><a href="<?php echo get_permalink( 43 ); ?>">Schedule</a></li>
  		<li><a href="<?php echo site_url(); ?>/event-registration/?ee=3">Founders Feast</a></li>
  		<li><a href="<?php echo site_url(); ?>/event-registration/?ee=11">Octoberfest Beer Garden</a></li>
  		<li><a href="<?php echo site_url(); ?>/event-registration/?ee=15">Brewers Brunch</a></li>
  		<li><a href="<?php echo site_url(); ?>/event-registration/?ee=8">HomeBrew Contest</a></li>
  		<li><a href="<?php echo site_url(); ?>/event-registration/?ee=9">Cyclo-Cross Bike Race</a></li>
  		<li><a href="<?php echo site_url(); ?>/event-registration/?ee=10">Singer-songwriter Competition</a></li>
  	</ul>
</li>
<li>
	<a class="arrow" href="<?php echo get_permalink( 45 ); ?>">Go Local GA</a>
  	<ul>
  		<?php $pages_array = wp_list_pages(array(
                'sort_column'=>'menu_order',
                'title_li'=>'',
                'echo' => '0',
                'child_of' => 45,
                'depth' => 2,
                )
               );
               echo $pages_array;
       ?>
		</ul>
</li>
<li>
	<a class="arrow" href="<?php echo get_permalink( 50 ); ?>">Causes</a>
		<ul>
       <?php $pages_array = wp_list_pages(array(
                'sort_column'=>'menu_order',
                'title_li'=>'',
                'echo' => '0',
                'child_of' => 50,
                'depth' => 2,
                )
               );
               echo $pages_array;
       ?>
		</ul>
</li>
<li class="aboutHorizontal">
	<a class="arrow" href="<?php echo get_permalink( 55 ); ?>">About</a>
		<ul>
       <?php $pages_array = wp_list_pages(array(
                'sort_column'=>'menu_order',
                'title_li'=>'',
                'echo' => '0',
                'depth' => 2,
                'child_of' => 55,
                )
               );
               echo $pages_array;
        ?>
		</ul>
</li>