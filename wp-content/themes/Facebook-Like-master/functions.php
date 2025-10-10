<?php /*
	Function Name: Facebook-like
	URI: http://linesh.com/
	Description:  Feature-packed theme with a solid design, built-in widgets and a intuitive theme settings interface... Designed by <a href="http://linesh.com/">Linesh Jose</a>.
	Version: 15.5
	Author: Linesh Jose 
	Author URI: http://linesh.com/
	roTags: light, white,two-columns, Flexible-width, right-sidebar, left-sidebar, theme-options, threaded-comments, translation-ready, custom-header	
	http://linesh.com/
	Both the design and code are released under GPL.
	http://www.opensource.org/licenses/gpl-license.php
*/
//----------------------------------------------------- Theme admin section -------------------------------------------------------//

	$themename = "Facebook-like";
	$shortname = "linesh";
	$options = array (	

		array(  "name" => "Header Logo",
				"desc" => "Would you like a logo in your header?",
				"id" => $shortname."_logo_header",
				"default" => "no",
				"type" => "logo"),	

		array(  "name" => "Twitter.com",
				"desc" => "<br>http://twitter.com/<b>username</b>",
				"id" => $shortname."_twitter",
				"default" => "lineshjose",
				"type" => "twitter"),
				
		array(  "name" => "Facebook.com",
				"desc" => "<br>http://facebook.com/<b>username</b>",
				"id" => $shortname."_facebook",
				"default" => "lineshjose",
				"type" => "facebook"),
				
	);
	

	add_action('admin_head', 'wp_admin_js');
	function wp_admin_js() {
	echo '<script type="text/javascript" src="'; echo bloginfo('template_url'); echo '/js/header.js"></script>'."\n"; 
	}

	
	function linesh_head() {
				
		if(get_option('linesh_background_image')=='yes'){
		echo '	
		<style>
		body{
		background-image:url('.get_option('linesh_bg_image').');
		background-repeat:';
		if(get_option('linesh_background_repeat')=='vertical'){ echo 'repeat-y';}
		else if(get_option('linesh_background_repeat')=='horizondal'){ echo 'repeat-x';}
		else if(get_option('linesh_background_repeat')=='no'){ echo 'no-repeat';}
		else {echo 'repeat';}
		echo ';
		background-position:'.get_option('linesh_background_position').';
		background-color:#'.get_option('linesh_background_color').';
		}
		</style>
		';
		
		}
		
	}
	add_action('wp_head', 'linesh_head');

	function linesh_add_admin() {
	global $themename, $shortname, $options;

		if ( isset($_REQUEST['action']) && 'save' == $_REQUEST['action'] ) {
					
					foreach ($options as $value) {
						if( !isset( $_REQUEST[ $value['id'] ] ) ) {  } else { update_option( $value['id'], $_REQUEST[ $value['id'] ]  ); }
					}
						
					if(stristr($_SERVER['REQUEST_URI'],'&saved=true')) {
						$location = $_SERVER['REQUEST_URI'];
						} else {
						$location = $_SERVER['REQUEST_URI'] . "&saved=true";		
						}
						
					if ($_FILES["file"]["type"]){
							$directory = dirname(__FILE__) . "/uploads/";				
							move_uploaded_file($_FILES["file"]["tmp_name"],
							$directory . $_FILES["file"]["name"]);
							update_option('linesh_logo', get_settings('siteurl'). "/wp-content/themes/". get_settings('template')."/uploads/". $_FILES["file"]["name"]);
							}
							
					
									
					header("Location: $location");
					die;
			} 
	   
		// Set all default options
		foreach ($options as $default) {
			if(get_option($default['id'])=="") {
				update_option($default['id'],$default['default']);
			}
		}
		add_theme_page('Page title', 'Facebook-like settings', 10, 'fb-like', 'linesh_header');
		
	}

	add_action('admin_menu', 'linesh_add_admin'); 

	function linesh_header()  {
	global $themename, $shortname, $options;
	?>
<?php if ( isset($_REQUEST['saved']) && $_REQUEST['saved'] ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.' settings saved.</strong></p></div>';	?>

<div class="wrap">
<h2><?php echo $themename; ?></h2>
<p>Thanks for downloading <strong><a href="http://linesh.com/projects/facebook-like/">Facebook-like</a></strong> by Linesh Jose. Hope you enjoy using it!</p>
<p>There are tons of layout possibilities available with this theme, as well as a bunch of cool features that will surely help you get your site looking and working it's best.</p>
<p>A lot of hard work went in to programming and designing <strong> Facebook-like </strong>, and if you would like to support Linesh Jose (the guy who designed it) please <a href="http://linesh.com/make-a-donation/">make a  donation</a>.  If you have any questions, comments, or if you encounter a bug, please <a href="http://linesh.com/">contact me</a>.</p>
</form>
<h4 style="margin:0;padding:10px 0 0 0;border-bottom:1px solid #ccc;clear:both;">Theme Settings</h4>
<form method="post" class="jqtransform" id="myForm" enctype="multipart/form-data" action="">
  <table cellpadding="0" cellspacing="10" >
    <tr>
      <td width="80%" valign="top"><div id="poststuff" class="">
          <?php
		foreach ($options as $value) { 
		switch ( $value['type'] ) {
		case "logo":			?>
          <div class="stuffbox">
            <h3><?php echo $value['name']; ?></h3>
            <div class="inside">
              <table>
                <tr>
                  <th><?php echo $value['desc']; ?></th>
                  <td><input  name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="radio" value="yes"<?php if ( get_settings( $value['id'] ) == "yes") { echo " checked"; } ?> onclick="showMe()" />
                    <label>Yes</label>
                    <input  name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="radio" value="no"<?php if ( get_settings( $value['id'] ) == "no") { echo " checked"; } ?> onclick="showMe()" />
                    <label>No</label></td>
                </tr>
              </table>
              <div id="headerLogo"> Choose a file to upload:
                <input type="file" name="file" id="file" />
                <?php if(get_option('linesh_logo')) { echo '<div><img src="'; echo get_option('linesh_logo'); echo '"  style="margin-top:10px;border:1px solid #aaa;padding:10px;" /></div>'; } ?>
              </div>
            </div>
          </div>
          <?php
			break;	
			case "twitter":
		?>
          <div class="stuffbox">
            <h3>Your Social Usernames</h3>
            <div class="inside">
              <table>
                <tr>
                  <td width="250"><table>
                      <tr>
                        <th align="left"><?php echo $value['name']; ?></th>
                      </tr>
                      <tr>
                        <td><input  name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="text" value="<?php echo get_settings( $value['id'] );?>">
                          <small><?php echo $value['desc']; ?></small></td>
                      </tr>
                    </table></td>
                  <?php
			break;	
			case "facebook":
		?>
                  <td width="250"><table>
                      <tr>
                        <th align="left"><?php echo $value['name']; ?></th>
                      </tr>
                      <tr>
                        <td><input  name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="text" value="<?php echo get_settings( $value['id'] );?>">
                          <small><?php echo $value['desc']; ?></small></td>
                      </tr>
                    </table></td>
                  <?php
			break;	
			case "myspace":
		?>
                  <td width="250"><table>
                      <tr>
                        <th align="left"><?php echo $value['name']; ?></th>
                      </tr>
                      <tr>
                        <td><input  name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="text" value="<?php echo get_settings( $value['id'] );?>">
                          <small><?php echo $value['desc']; ?></small></td>
                      </tr>
                    </table></td>
                </tr>
              </table>
            </div>
          </div>
        </div>
        <?php
			break;		
		}
	}
	?>
      </div>
    
      </div>
    
      </td>
    
    
      <td valign="top"><div id="poststuff" class="metabox-holder has-right-sidebar" style="margin-top:-10px;">
          <div id="linksubmitdiv" class="postbox " >
            <h3>Current Saved Settings</h3>
            <div id="minor-publishing">
              <ul style="padding:10px 0 5px 5px;">
                <li>Header Logo: <strong><?php echo ucwords(get_option('linesh_logo_header')); ?></strong></li>
              </ul>
              <span style="border-bottom:1px solid #ccc;">Social usernames</span>
              <ul style="padding:5px 0 0 5px;">
                <li>Twitter : <strong><?php echo ucwords(get_option('linesh_twitter')); ?></strong></li>
                <li>Facebook: <strong><?php echo ucwords(get_option('linesh_facebook')); ?></strong></li>
                <li>Myspace : <strong><?php echo ucwords(get_option('linesh_myspace')); ?></strong></li>
              </ul>
              <div id="major-publishing-actions">
                <input name="save" type="submit" value="Save changes" />
                <input type="hidden" name="action" value="save" />
              </div>
            </div>
          </div>
        </div></td>
    </tr>
  </table>
</form>
</div>
<?php  }?>
<?php
//----------------------------------------------------- Theme section -------------------------------------------------------//

	if (function_exists("register_sidebar")) {
		register_sidebar(array(
		'name' => 'Left Sidebar',
			'before_widget' => '<div class="widget">',
			'after_widget' => '</div>',
			'before_title' => '<h3>',
			'after_title' => '</h3>',
		));
		
		register_sidebar(array(
		'name' => 'Right Sidebar',
			'before_widget' => '<div class="widget">',
			'after_widget' => '</div>',
			'before_title' => '<h3>',
			'after_title' => '</h3>',
		));
	}


	function new_excerpt_length($length) {// This theme uses post excerpt_length
		return 50;
	}
	add_filter('excerpt_length', 'new_excerpt_length');

	
	// This theme uses post thumbnails
	add_theme_support( 'post-thumbnails' );


	
	function getImage($num) {
	global $more;
	$more = 1;
	$link = get_permalink();
	$content = get_the_content();
	$count = substr_count($content, '<img');
	$start = 0;
	for($i=1;$i<=$count;$i++) {
		$imgBeg = strpos($content, '<img', $start);
		$post = substr($content, $imgBeg);
		$imgEnd = strpos($post, '>');
		$postOutput = substr($post, 0, $imgEnd+1);
		$result = preg_match('/width="([0-9]*)" height="([0-9]*)"/', $postOutput, $matches);
		if ($result) {
			$pagestring = $matches[0];
			$image[$i] = str_replace($pagestring, "", $postOutput);
		} else {
			$image[$i] = $postOutput;
		}
		$start=$imgEnd+1;
	}
	if(stristr($image[$num],'<img')) { echo '<a href="'.$link.'">'.$image[$num]."</a>"; }
	$more = 0;
}




	## Comment function
	if ( ! function_exists( 'twentyten_comment' ) ) :
	function twentyten_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) :
			case '' :
		?>
<li>
  <?php if(get_comment_author_url()){?>
  <a href="<?php echo get_comment_author_url();?>"><?php echo get_avatar( $comment, 32 ); ?></a>
  <?php } 
				else { echo get_avatar( $comment, 32 );} ?>
  <div class="comment">
    <div class="info"> <?php printf( __( '%s', 'twentyten' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?> <small><?php echo human_time_diff(get_comment_time('U'), current_time('timestamp')) . ' ago'; ?> </small>
      <?php if ( $comment->comment_approved == '0' ) : ?>
      (<em>
      <?php _e( 'Your comment is awaiting moderation.', 'twentyten' ); ?>
      </em>)
      <?php endif; ?>
      <?php edit_comment_link( __( '(Edit this)', 'twentyten' ), ' ' );?>
    </div>
    <div class="text">
      <?php comment_text(); ?>
    </div>
  </div>
  <!-- #comment-##  -->
  
  <?php
				break;
			case 'pingback'  :
			case 'trackback' :
		?>
  <p>
    <?php _e( 'Pingback:', 'twentyten' ); ?>
    <?php comment_author_link(); ?>
    <?php edit_comment_link( __('(Edit)', 'twentyten'), ' ' ); ?>
  </p>
  <?php
				break;
		endswitch;
	}
	endif;

/**
 * Hide WordPress Core Updates
 * Removes update notifications and hides the update-core.php page
 */

// Hide WordPress core update notifications
add_filter('pre_site_transient_update_core', '__return_null');

// Remove the WordPress update nag
remove_action('admin_notices', 'update_nag', 3);

// Hide the update menu item from admin menu
function hide_update_core_menu() {
    remove_submenu_page('index.php', 'update-core.php');
}
add_action('admin_menu', 'hide_update_core_menu');

// Redirect users away from update-core.php if they try to access it directly
function redirect_from_update_core() {
    global $pagenow;
    if ($pagenow == 'update-core.php') {
        wp_redirect(admin_url());
        exit;
    }
}
add_action('admin_init', 'redirect_from_update_core');

// Remove WordPress version from admin footer
function remove_wp_version_admin_footer() {
    return '';
}
add_filter('update_footer', 'remove_wp_version_admin_footer', 9999);

// Hide WordPress core updates from the admin bar
function hide_admin_bar_updates() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('updates');
}
add_action('wp_before_admin_bar_render', 'hide_admin_bar_updates');

?>
