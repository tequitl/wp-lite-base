<?php /*
	Template Name: Sidebar
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
<!-- Sidebar -->

<div id="sidebar">
  <div class="widget">
    <?php _e('<h3>Get connected</h3>'); ?>
    <?php if(get_option('linesh_twitter')) { ?>
    <a href="http://twitter.com/<?php echo get_option('linesh_twitter');?>"><img src="<?php echo  get_stylesheet_directory_uri();?>/images/twitter.png" alt="<?php echo get_option('linesh_twitter');?>" title="Twitter - <?php echo get_option('linesh_twitter');?>" class="img"></a>
    <?php }?>
    <?php if(get_option('linesh_facebook')) { ?>
    <a href="http://facebook.com/<?php echo get_option('linesh_facebook');?>"><img src="<?php echo  get_stylesheet_directory_uri();?>/images/facebook.png" alt="<?php echo get_option('linesh_facebook');?>" title="Facebook - <?php echo get_option('linesh_facebook');?>" class="img"></a>
    <?php }?>
     <a href="<?php bloginfo_rss( 'rss2_url' ); ?>"><img src="<?php echo  get_stylesheet_directory_uri();?>/images/rss.png" alt="Subsrcibe Feed" title="Subsrcibe Feed"></a> </div>
  <?php 	/* Widgetized sidebar, if you have the plugin installed. */
	if (!dynamic_sidebar("Right Sidebar") ) : ?>
  <div class="widget">
    <?php _e('<h3>Archives</h3>'); ?>
    <ul>
      <?php wp_get_archives('type=monthly'); ?>
    </ul>
  </div>
  <?php endif; ?>
  <div class="widget">
    <h3>Feeds</h3>
    <ul >
      <li><a href="<?php bloginfo_rss( 'rss2_url' ); ?>" title="Syndicate this site using RSS 2.0">Entries <abbr title="Really Simple Syndication">RSS 2.0</abbr></a></li>
      <li><a href="<?php bloginfo_rss( 'atom_url' ); ?>" title="Syndicate this site using atom">Entries <abbr title="Really Simple Syndication">Atom</abbr></a></li>
      <li><a href="<?php bloginfo_rss( 'comments_rss2_url' ); ?>" title="The latest comments to all posts in RSS">Comments <abbr title="Really Simple Syndication">RSS 2.0</abbr></a></li>
    </ul>
  </div>
</div>
