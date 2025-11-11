<?php
/**
 * Template for displaying single posts
 * ClassicMicroBlog theme
 */
get_header();
?>

<div class="layout">
  <!-- Left Sidebar -->
  <aside class="sidebar">
    <nav class="nav">
      <?php
      // List all WordPress pages
      $pages = get_pages();
      foreach ($pages as $page) {
          echo '<a class="nav-item" href="' . esc_url(get_permalink($page->ID)) . '"><span>' . esc_html($page->post_title) . '</span></a>';
      }
      ?>
    </nav>
    <button class="btn post-button" onclick="window.ClassicMicroBlogOpenComposer && window.ClassicMicroBlogOpenComposer()">Post</button>
    
    <?php
    $current_user = wp_get_current_user();
    $display_name = $current_user && $current_user->ID ? ($current_user->display_name ?: $current_user->user_login) : get_bloginfo('name');
    $username     = $current_user && $current_user->ID ? $current_user->user_login : 'guest';
    $initial      = strtoupper(mb_substr($display_name, 0, 1));
    ?>
    <div class="mini-profile">
      <div class="mini-avatar"><?php echo esc_html($initial); ?></div>
      <div>
        <div style="font-weight:700;"><?php echo esc_html($display_name); ?></div>
        <div class="muted">@<?php echo esc_html($username); ?></div>
      </div>
    </div>
  </aside>

  <!-- Main content: render the single post -->
  <main class="main">
    <!-- top put a title called POST and a row to return to Timeline -->
    <section class="card" style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
      <a class="nav-link" href="<?php echo esc_url(home_url('/')); ?>" style="display:flex; align-items:center; gap:8px;">
        <span style="font-size:18px;">←</span>
        <span style="font-weight:700;">Post</span>
      </a>
      <a class="btn secondary" href="#comments" style="margin-left:auto;">Reply</a>
    </section>
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <section class="post-header">
        <div class="card">
          <h1 class="post-title"><?php the_title(); ?></h1>
          <div class="post-meta">
            <span>By <?php the_author(); ?></span>
            <span>•</span>
            <span><?php echo get_the_date(); ?></span>
            <?php if (get_comments_number() > 0) : ?>
              <span>•</span>
              <span><?php comments_number('No comments', '1 comment', '% comments'); ?></span>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <section class="post-content">
        <div class="card">
          <div class="post-content">
            <?php the_content(); ?>
          </div>

          <?php if (has_tag()) : ?>
            <div class="post-tags" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border);">
              <div class="muted" style="margin-bottom: 8px;">Tags:</div>
              <?php the_tags('<span class="tag">', '</span> <span class="tag">', '</span>'); ?>
            </div>
          <?php endif; ?>

          <?php if (get_edit_post_link()) : ?>
            <div class="actions" style="margin-top: 16px;">
              <a class="btn secondary" href="<?php echo esc_url(get_edit_post_link()); ?>">Edit Post</a>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- Comments Section -->
      <?php if (comments_open() || get_comments_number()) : ?>
        <section class="post-comments">
          <?php comments_template(); ?>
        </section>
      <?php else : ?>
        <section class="post-comments">
          <div class="card">
            <div class="muted">Comments are closed for this post.</div>
          </div>
        </section>
      <?php endif; ?>

      <!-- Post Navigation -->
      <section class="post-navigation">
        <div class="card">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
              <?php
              $prev_post = get_previous_post();
              if ($prev_post) :
              ?>
                <a href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>" class="nav-link">
                  <div class="muted">← Previous</div>
                  <div style="font-weight: 600;"><?php echo esc_html(get_the_title($prev_post->ID)); ?></div>
                </a>
              <?php endif; ?>
            </div>
            <div style="text-align: right;">
              <?php
              $next_post = get_next_post();
              if ($next_post) :
              ?>
                <a href="<?php echo esc_url(get_permalink($next_post->ID)); ?>" class="nav-link">
                  <div class="muted">Next →</div>
                  <div style="font-weight: 600;"><?php echo esc_html(get_the_title($next_post->ID)); ?></div>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </section>
    <?php endwhile; endif; ?>
  </main>

  <!-- Right Sidebar -->
  <aside class="rightbar">
    <div class="widget">
      <div class="widget-title">Post Info</div>
      <div class="widget-item">
        <div>
          <div style="font-weight:600;">Published</div>
          <div class="muted"><?php echo get_the_date('F j, Y'); ?></div>
        </div>
      </div>
      <div class="widget-item">
        <div>
          <div style="font-weight:600;">Author</div>
          <div class="muted"><?php the_author(); ?></div>
        </div>
      </div>
      <?php if (has_category()) : ?>
        <div class="widget-item">
          <div>
            <div style="font-weight:600;">Categories</div>
            <div class="muted"><?php the_category(', '); ?></div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="widget">
      <div class="widget-title">Quick Actions</div>
      <div class="widget-item">
        <a href="<?php echo esc_url(home_url('/')); ?>" style="text-decoration: none; color: inherit;">
          <div style="font-weight:600;">← Back to Timeline</div>
          <div class="muted">Return to main feed</div>
        </a>
      </div>
      <?php if (get_edit_post_link()) : ?>
        <div class="widget-item">
          <a href="<?php echo esc_url(get_edit_post_link()); ?>" style="text-decoration: none; color: inherit;">
            <div style="font-weight:600;">✏️ Edit Post</div>
            <div class="muted">Make changes to this post</div>
          </a>
        </div>
      <?php endif; ?>
    </div>

    <!-- Related Posts Widget -->
    <?php
    $related_posts = get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => 3,
        'post__not_in' => array(get_the_ID()),
        'orderby' => 'rand'
    ));
    if ($related_posts) :
    ?>
      <div class="widget">
        <div class="widget-title">More Posts</div>
        <?php foreach ($related_posts as $related_post) : ?>
          <div class="widget-item">
            <a href="<?php echo esc_url(get_permalink($related_post->ID)); ?>" style="text-decoration: none; color: inherit;">
              <div style="font-weight:600;"><?php echo esc_html(get_the_title($related_post->ID)); ?></div>
              <div class="muted"><?php echo esc_html(get_the_date('M j', $related_post->ID)); ?></div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </aside>
</div>

<?php get_footer(); ?>