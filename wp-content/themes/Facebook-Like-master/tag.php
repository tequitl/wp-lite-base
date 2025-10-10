<?php /*
	Template Name: Tags
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
<div  id="posts" >
<!-- Title -->
<h1>Tag Archive</h1>
<!-- end Title -->

<?php if (have_posts()) : ?>
	<?php while (have_posts()) : the_post(); ?>

			<div class="post <?php the_ID(); ?>">
				<ul>
					<?php edit_post_link('Edit this','<!-- Post Edit--><li class="edit">','</li>'); ?> 
					<!-- Post Comments-->
					<li class="comment"><?php comments_popup_link('0', '1', '%', ''); ?></li>
				</ul>
				
				<!-- Post Title -->
				<h2 ><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
					
				<!-- Post Thumb image-->
				<div class="thumb"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_post_thumbnail('thumbnail');?></a></div>
					
				<div class="author"><!-- Author  name --><?php the_author_posts_link(); ?>	
				<span class="date"><!-- Post Date--><?php  echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?></span> 
				</div>
				<!-- Post Data--><?php the_excerpt(); ?>
			</div>

	<?php endwhile; ?>

	<!-- Navigation starts -->
	<div id="navigation">
	<?php next_posts_link('<div class="alignleft button">&laquo; Old Entries </div>') ?>
	<?php previous_posts_link('<div class="alignright button">New Entries &raquo;</div>') ?>
	<div class="clearboth"></div>
	</div>
	<!-- Navigation ends -->

<?php endif; ?>
</div>
<!-- Posts Ends -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>