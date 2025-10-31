<?php /*
	Template Name: Posts
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
	<h1><?php bloginfo('description'); ?></h1>
	<!-- end Title -->
	 
	<!-- add a new post functionality  only avalibre for owner of the account-->
	<?php if ( is_user_logged_in() && current_user_can('publish_posts') ) : ?>
	<div class="csm-new-post-card" role="region" aria-label="Create Post">
		<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" class="csm-new-post-form">
			<?php wp_nonce_field('csm_new_post_action', 'csm_new_post_nonce'); ?>
			<input type="hidden" name="action" value="csm_new_post">

			<div class="csm-np-body">
				<input type="text" id="csm_post_title" name="csm_post_title" class="csm-np-input-title" placeholder="Add a title" required>
				<textarea id="csm_post_content" name="csm_post_content" class="csm-np-input-content" rows="6" placeholder="What's on your mind, <?php echo esc_html( $current_user->display_name ); ?>?" required></textarea>
			</div>

			<div class="csm-np-footer">
				<button type="submit" class="csm-np-submit">Post</button>
			</div>
		</form>
	</div>
	<?php endif; ?>

	<?php if (have_posts()) : ?>
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
	<div class="post">
      <div class="post-meta">
        <!-- Replace the old <li class="comment"><a href="...">Comments</a></li> with this -->
        <section class="csm-comments-block">
          <a href="#" class="csm-comments-toggle" data-post-id="<?php the_ID(); ?>" role="button">Comments</a>
          <div class="csm-comments-container" id="csm-comments-<?php the_ID(); ?>" style="display:none;"></div>
        </section>
      </div>
    </div>
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
