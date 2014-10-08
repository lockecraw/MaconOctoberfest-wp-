<li>
	<a class="arrow" href="<?php echo get_permalink( 33 ); ?>">Events</a>
  	<ul>
  		<li><a href="<?php echo get_permalink( 43 ); ?>">Schedule</a></li>
  		<li><a href="<?php echo get_permalink( 709 ); ?>">Octoberfest Beer Garden</a></li>
  		<li><a href="<?php echo home_url(); ?>/event-registration/?ee=24">Brewers Brunch</a></li>
  		<li><a href="<?php echo home_url(); ?>/event-registration/?ee=9">Cyclocross Bike Race</a></li>
  		<li><a href="<?php echo get_permalink( 779 ); ?>">Wiener Dog Race</a></li>
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
       <li><a href="http://hiltongardeninn.hilton.com/en/gi/groups/personalized/M/MCNGAGI-OFS-20141024/index.jhtml" target="_blank">Place to Stay</a></li>
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