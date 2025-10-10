<?php /*
	Template Name: Comments
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
<!-- #comments -->
<div class="comments_top"></div>

<?php if ( post_password_required() ) : ?>
<fieldset id="comments">
<legend class="nocomments"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'twentyten' ); ?></legend>
</fieldset>
<!-- #comments -->
<?php
		/* Stop the rest of comments.php from being processed,
		 * but don't kill the script entirely -- we still have
		 * to fully load the template.
		 */
		return;
	endif;
?>
<?php 	// You can start editing here -- including this comment! ?>
<?php if ( have_comments() ) : ?>
<fieldset id="comments">
<legend><?php printf( _n( 'One Comment', '%1$s Comments', get_comments_number(), 'twentyten' ), number_format_i18n( get_comments_number() ), '<em>' . get_the_title() . '</em>' );?></legend>
<ol class="commentlist">
<?php wp_list_comments( array( 'callback' => 'twentyten_comment' ) ); ?>
</ol>
</fieldset>
<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
<ul id="navigation">
<?php previous_comments_link( __( '<li class="alignleft">&larr; Older Comments</li>', 'twentyten' ) ); ?>
<div><?php next_comments_link( __( '<li  class="alignright">Newer Comments &rarr;</li>', 'twentyten' ) ); ?></div>
<div class="clearboth"></div>
</ul>
<!-- .navigation -->
<?php endif; // check for comment navigation ?>
<?php else : // this is displayed if there are no comments so far ?>
<?php if ('open' == $post->comment_status) : ?>
<!-- If comments are open, but there are no comments. -->
<?php else : // comments are closed ?>	<!-- If comments are closed. -->
<fieldset id="comments">
<legend class="nocomments">Comments are closed.</legend>
</fieldset>
<?php endif; ?>
<?php endif; ?>

<fieldset id="comments">
<?php if ('open' == $post->comment_status) : ?>
<legend>Leave a comment</legend>
<?php if ( get_option('comment_registration') && !get_current_user_id() ) : ?>
<p>You must be <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php echo urlencode(get_permalink()); ?>">logged in</a> to post a comment.</p>
<?php else : ?>
<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
<?php if ( get_current_user_id() ) : ?>
<table cellpadding="0" cellspacing="8" >
<?php else : ?>
<table cellpadding="0" cellspacing="8" >
<tr><td>
<table cellpadding="0" cellspacing="0" >
<tr>
<td align="left"><span><?php if ($req) echo "*"; ?></span><label for="author"> Name</label><br /><input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="22" tabindex="1"  class="textbox" /></td>
<td><span><?php if ($req) echo "**"; ?></span><label for="email"> Mail</label><br /><input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="22" tabindex="2" class="textbox" /></td>
<td><label for="url">Website</label><br /><input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="22" tabindex="3" class="textbox"  /></td>
</tr>
</table>
</td></tr>
<?php endif; ?>
<tr>
<td ><span><?php if ($req) echo "*"; ?></span><label for="comment">Your Comment</label><br />
<textarea name="comment" id="comment" cols="25" rows="5" tabindex="4" class="textarea" ></textarea><br/>
<small><strong>XHTML:</strong> You can use these tags: <code><?php echo allowed_tags(); ?></code></small>
<br /><br />
<input name="submit" type="submit" id="submit" tabindex="5" value="Submit"  >
<input name="Reset" type="Reset" id="submit" tabindex="5" value="Clear" />
<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
<span>*</span> Required , <span>**</span> will not be published.
</td>
</tr>
</table>
</p>
<?php do_action('comment_form', $post->ID); ?>
</form>
<?php endif; // If registration required and not logged in ?>
<?php endif; // if you delete this the sky will fall on your head ?>
</fieldset>