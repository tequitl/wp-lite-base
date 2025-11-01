<?php
/**
 * Template Name: Default Page
 * Description: Default page template for ClassicMicroBlog using the X-like layout.
 */
get_header();
?>

<div class="layout">
  <!-- Left Sidebar -->
  <aside class="sidebar">
    <nav class="nav">
      <?php
      //list all wordpress pages
      $pages = get_pages();
      foreach ($pages as $page) {
          echo '<a class="nav-item" href="' . esc_url(get_permalink($page->ID)) . '"><span>' . esc_html($page->post_title) . '</span></a>';
      }
      ?>
    </nav>
    <button class="btn post-button">Post</button>
  </aside>

  <!-- Main content: render the page -->
  <main class="main">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <section class="page-header">
        <div class="card">
          <h1 class="page-title"><?php the_title(); ?></h1>
          <?php if (has_excerpt()) : ?>
            <div class="page-excerpt muted"><?php the_excerpt(); ?></div>
          <?php endif; ?>
        </div>
      </section>

      <section class="page-content">
        <div class="card">
          <div class="post-content">
            <?php the_content(); ?>
          </div>

          <?php if (get_edit_post_link()) : ?>
            <div class="actions">
              <a class="btn secondary" href="<?php echo esc_url(get_edit_post_link()); ?>">Edit Page</a>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <?php if (comments_open() || get_comments_number()) : ?>
        <section class="page-comments">
          <div class="card">
            <?php comments_template(); ?>
          </div>
        </section>
      <?php endif; ?>
    <?php endwhile; endif; ?>
  </main>

  <!-- Right Sidebar -->
  <aside class="rightbar">
    <div class="widget">
      <div class="widget-title">Page Navigation</div>
      <?php
      $pages = get_pages();
      if ($pages) :
        foreach ($pages as $page) :
          $is_current = (get_the_ID() === $page->ID) ? 'current-page' : '';
          ?>
          <div class="widget-item <?php echo esc_attr($is_current); ?>">
            <a href="<?php echo esc_url(get_permalink($page->ID)); ?>" style="text-decoration: none; color: inherit;">
              <div style="font-weight:600;"><?php echo esc_html($page->post_title); ?></div>
              <?php if ($page->post_excerpt) : ?>
                <div class="muted"><?php echo esc_html(wp_trim_words($page->post_excerpt, 10)); ?></div>
              <?php endif; ?>
            </a>
          </div>
          <?php
        endforeach;
      endif;
      ?>
    </div>

    <div class="widget">
      <div class="widget-title">Quick Actions</div>
      <div class="widget-item">
        <a href="<?php echo esc_url(home_url('/')); ?>" style="text-decoration: none; color: inherit;">
          <div style="font-weight:600;">← Back to Timeline</div>
          <div class="muted">Return to main feed</div>
        </a>
      </div>
      <?php if (current_user_can('edit_pages')) : ?>
        <div class="widget-item">
          <a href="<?php echo esc_url(admin_url('post-new.php?post_type=page')); ?>" style="text-decoration: none; color: inherit;">
            <div style="font-weight:600;">+ New Page</div>
            <div class="muted">Create a new page</div>
          </a>
        </div>
      <?php endif; ?>
    </div>
  </aside>
</div>

<?php get_footer(); ?>