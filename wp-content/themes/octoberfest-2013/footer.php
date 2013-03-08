<div style="clear: both"></div>
<!-- start footer -->
<div id="footer">
    <p>Copyright &copy; <?php the_date('Y'); ?> <a href="<?php echo get_settings('home'); ?>/">
      <?php bloginfo('name'); ?>
      </a> &middot; <?php wp_loginout(); ?>
    </p>
    <?php wp_list_pages('title_li='); ?>
</div>
<!-- end footer -->
<?php wp_footer(); ?>
<?php print_a($_SESSION); ?>
</div>
</body>
</html>