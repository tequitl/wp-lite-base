<?php /*
	Template Name: Archives
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
<div id="posts">
<?php if (have_posts()):  ?>
<?php $post = $posts[0]; ?>
<!-- Title -->
<h1>
<?php if (is_day()): ?>Archive for <?php the_time('F jS, Y'); ?>
<?php elseif (is_month()): ?>Archive for <?php the_time('F, Y'); ?>
<?php elseif (is_year()): ?>Archive for <?php the_time('Y'); ?>
<?php elseif (isset($_GET['paged']) && !empty($_GET['paged'])): ?>Blog Archives
<?php endif; ?>
</h1>
<!-- end Title -->

	<?php while (have_posts()) : the_post(); ?>

<table cellpadding="0" cellspacing="0" class="post <?php the_ID(); ?>">
		<tr><!-- Author Avatar -->
			<td class="avatar">
			<a href="<?php echo get_author_posts_url(get_the_author_id());?>" title="<?php the_author();?>" alt="<?php the_author();?>"><?php echo get_avatar( get_the_author_email(), '50' );?></a>
			</td> 
			
			<td class="posted">
				<div class="author"><!-- Author  name --><?php the_author_posts_link(); ?>	</div>
				
				<table cellpadding="0" cellspacing="0" class="celarboth">
						<tr>
						<!-- Post Thumb image-->
							<td class="thumb">
								<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_post_thumbnail('thumbnail');?></a>
							</td>
							
							<td class="text">
								<!-- post title -->
								<h2 ><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
								<!-- Post Data-->
								<?php the_excerpt(); ?>
							</td>
						</tr>
				</table>
				
				<ul>
					<li class="date"><!-- Post Date--><?php  echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?></li> 
						<?php edit_post_link('Edit this','<!-- Post Edit--><li class="edit">','</li>'); ?> 
						
					<li class="comment"><!-- Post Comments--><?php comments_popup_link('Comments', 'Comments', 'Comments', ''); ?></li>
				</ul>
			
			</td>
		</tr>
	</table>
	<?php endwhile; ?>

	<!-- Navigation starts -->
	<ul id="navigation">
	<?php  next_posts_link('<li class="alignleft">&laquo; Older posts </li>') ?>
	<?php previous_posts_link('<li class="alignright">New posts  &raquo;</li>') ?>
	<div class="clearboth"></div>
	</ul>
	<!-- Navigation ends -->
	<?php endif; ?>
</div>
<!-- Posts Ends -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>