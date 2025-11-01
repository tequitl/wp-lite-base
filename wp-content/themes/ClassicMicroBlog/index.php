<?php get_header(); ?>

<?php
  $current_user = wp_get_current_user();
  $display_name = $current_user && $current_user->ID ? ($current_user->display_name ?: $current_user->user_login) : get_bloginfo('name');
  $username     = $current_user && $current_user->ID ? $current_user->user_login : 'guest';
  $joined_str   = $current_user && $current_user->ID ? date_i18n('F Y', strtotime($current_user->user_registered)) : date_i18n('F Y');
  $initial      = strtoupper(mb_substr($display_name, 0, 1));
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
    <button class="btn post-button" onclick="window.ClassicMicroBlogOpenComposer && window.ClassicMicroBlogOpenComposer()">Post</button>
    <div class="mini-profile">
      <div class="mini-avatar"><?php echo esc_html($initial); ?></div>
      <div>
        <div style="font-weight:700;"><?php echo esc_html($display_name); ?></div>
        <div class="muted">@<?php echo esc_html($username); ?></div>
      </div>
    </div>
  </aside>

  <!-- Main content -->
  <main class="main">
    <!-- Profile Header -->
    <section class="profile-header">
      <div class="cover"></div>
      <div class="profile-row">
        <div class="profile-avatar"><?php echo esc_html($initial); ?></div>
        <button class="edit-profile">Edit profile</button>
      </div>
      <div class="profile-info">
        <h1><?php echo esc_html($display_name); ?> <span class="badge">Get verified</span></h1>
        <div class="muted">@<?php echo esc_html($username); ?></div>
        <div class="muted">Joined <?php echo esc_html($joined_str); ?></div>
        <div class="profile-stats muted"><span>3 Following</span><span>0 Followers</span></div>
      </div>
      <div class="tabs">
        <a class="tab active" href="#">Posts</a>
        <a class="tab" href="#">Replies</a>
        <a class="tab" href="#">Highlights</a>
        <a class="tab" href="#">Articles</a>
        <a class="tab" href="#">Media</a>
        <a class="tab" href="#">Likes</a>
      </div>
    </section>

    <!-- Vue App: Timeline and owner-only panel -->
    <div id="app">
        <!-- Modal Composer -->
        <div v-if="composerOpen" class="modal-backdrop" style="position:fixed; inset:0; background:rgba(0,0,0,.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
            <div class="card" style="width:600px; max-width:90%; border-radius:12px; background:#fff; padding:16px; position:relative;">
                <button class="btn secondary" @click="closeComposer" style="position:absolute; top:8px; left:8px;">✕</button>
                <p></p>
                <input class="input" v-model="owner.newTitle" placeholder="Title (optional)">
                <textarea class="input" rows="4" v-model="owner.newContent" placeholder="What’s happening?"></textarea>
    
                <div class="actions" style="margin-top:8px;">
                    <button class="btn" :disabled="owner.loading || !owner.newContent" @click="createOwnerPost">Post</button>
                </div>
                <div v-if="owner.loading" class="muted" style="margin-top:8px;">Posting...</div>
                <div v-if="owner.error" class="error" style="margin-top:8px;">{{ owner.error }}</div>
            </div>
        </div>
    
        <!-- Modal overlay: only close when clicking the overlay itself -->
        <div v-if="composerOpen" class="cmb-modal-overlay" @click.self="closeComposer">
            <div class="cmb-modal" @click.stop>
                <button class="btn secondary" @click="closeComposer" style="position:absolute; top:8px; left:8px;">✕</button>
            <input class="input" v-model="owner.newTitle" placeholder="Title (optional)">
                <textarea class="input" rows="4" v-model="owner.newContent" placeholder="What’s happening?"></textarea>
    
                <div class="actions" style="margin-top:8px;">
                    <button class="btn" :disabled="owner.loading || !owner.newContent" @click="createOwnerPost">Post</button>
                </div>
                <div v-if="owner.loading" class="muted" style="margin-top:8px;">Posting...</div>
                <div v-if="owner.error" class="error" style="margin-top:8px;">{{ owner.error }}</div>
            </div>
        </div>

      <!-- Timeline -->
    <!-- add section a new post in case is owner-->
      <section>
        <div class="card">
          <div class="post-title">What’s happening?</div>
          <input class="input" v-model="owner.newTitle" placeholder="Title (optional)">
          <textarea class="input" rows="3" v-model="owner.newContent" placeholder="What’s happening?"></textarea>
          <div class="actions">
            <button class="btn"  @click="createOwnerPost">Post</button>
          </div>
          <div v-if="owner.loading" class="muted">Posting...</div>
          <div v-if="owner.error" class="error">{{ owner.error }}</div>
          <div v-if="owner.created" class="muted">
            Posted!
          </div>
        </div>

        <div class="card">
          <div class="post-title">Timeline</div>
          <div class="muted">Fetching published posts from WP REST API</div>
          <div v-if="timeline.loading" class="muted">Loading...</div>
          <div v-if="timeline.error" class="error">{{ timeline.error }}</div>
        </div>

        <div v-for="p in timeline.items" :key="p.id" class="card">
          <div class="post-title">{{ p.title.rendered }}</div>
          <div class="post-meta">
            <span>By: {{ p._author_name || ('User #' + p.author) }}</span>
            <span> · </span>
            <span>{{ new Date(p.date).toLocaleString() }}</span>
          </div>
          <div v-html="p.excerpt.rendered"></div>
          <div class="actions">
            <a class="btn secondary" :href="p.link" target="_blank" rel="noopener">Open</a>
          </div>
        </div>

        <div class="card actions">
          <button class="btn secondary" :disabled="timeline.page<=1 || timeline.loading" @click="prevPage">Prev</button>
          <button class="btn" :disabled="timeline.loading" @click="nextPage">Next</button>
          <span class="muted">Page {{ timeline.page }}</span>
        </div>
      </section>
    </div>
  </main>

  <!-- Right Sidebar -->
  <aside class="rightbar">
    <div class="widget">
      <div class="widget-title">You might like</div>
      <div class="widget-item">
        <div>
          <div style="font-weight:600;">SSC CDMX</div>
          <div class="muted">@SSC_CDMX</div>
        </div>
        <button class="follow-btn">Follow</button>
      </div>
      <div class="widget-item">
        <div>
          <div style="font-weight:600;">Sismologico Nacional</div>
          <div class="muted">@SismologicoMX</div>
        </div>
        <button class="follow-btn">Follow</button>
      </div>
      <div class="widget-item">
        <div>
          <div style="font-weight:600;">Museo de Arte Carrillo Gil</div>
          <div class="muted">@Carrillo_Gil</div>
        </div>
        <button class="follow-btn">Follow</button>
      </div>
    </div>

    <div class="widget">
      <div class="widget-title">What’s happening</div>
      <div class="widget-item">
        <div>
          <div style="font-weight:600;">#BernardoPicks</div>
          <div class="muted">Trending in Mexico</div>
        </div>
      </div>
      <div class="widget-item">
        <div>
          <div style="font-weight:600;">Cuba</div>
          <div class="muted">Politics · Trending</div>
        </div>
      </div>
      <div class="widget-item">
        <div>
          <div style="font-weight:600;">#viernesdebonosplaydoit</div>
          <div class="muted">Trending in Mexico</div>
        </div>
      </div>
    </div>
  </aside>
</div>

<?php get_footer(); ?>
