<?php /*
	Template Name: Default Page
	URI: http://linesh.com/
	Description:  Feature-packed theme with a solid design, built-in widgets and a intuitive theme settings interface... Designed by <a href="http://linesh.com/">Linesh Jose</a>.
	Version: 15.5
	Author: Linesh Jose 
	Author URI: http://linesh.com/
	roTags: light, white,two-columns, Flexible-width, right-sidebar, left-sidebar, theme-options, threaded-comments, translation-ready, custom-header	
	http://linesh.com/
	Both the design and code are released under GPL.
	http://www.opensource.org/licenses/gpl-license.php
*/?>
<?php get_header(); ?>
<!-- Posts starts -->
<div  id="posts">
<!-- Title -->
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<h1> <?php the_title(); ?></h1>
<!-- end Title -->

<!-- Post starts -->
<div  id="posts" class="<?php the_ID(); ?>">
<div class="post_data">
<?php the_content(); ?>
</div>
</div>
<!-- Post Ends -->
<?php endwhile; endif; ?>
</div>
<!-- end post -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>