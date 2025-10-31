<?php /*
	Template Name: Post Content
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
	<div class="metadata">
	<!-- Author Avatar -->
	<div class="avatar"><a href="<?php echo get_author_posts_url(get_the_author_id());?>" title="<?php the_author();?>" alt="<?php the_author();?>"><?php echo get_avatar( get_the_author_email(), '40' );?></a></div> 
		<div class="posted">
		<!-- Author  name -->
		<div class="author"><?php the_author_posts_link() ?></div>
		<ul>
		<!-- Post Date-->
		<li class="date"><?php the_date()?> in : 		<!-- Post Categories --><?php the_category(' , ') ?></li>
		<?php edit_post_link('Edit','<!-- Post Edit--><li class="edit">','</li>'); ?> 
		<li class="comment"><!-- Post Comments--><?php comments_popup_link('0 Comment', '1 Comment', '% Comments', ''); ?></li>
		</ul>
		</div>


	<div class="clearboth"></div>
	</div>
<div class="post_data">
<?php the_content('<p class="serif">Read the rest of this entry &raquo;</p>'); ?>
<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
</div>	
<div class="clearboth"></div>
<!--  Tags-->
<?php the_tags('<div class="tags"><b>Tags:</b> ', '&nbsp;&nbsp;|&nbsp;&nbsp;','</div><br />'); ?>


<?php comments_template(); ?>
<?php endwhile; else: ?>
<?php endif; ?>
</div>
<!-- end post -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>