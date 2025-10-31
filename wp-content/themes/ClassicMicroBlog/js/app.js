const { createApp } = Vue;

createApp({
  data() {
    return {
      timeline: {
        items: [],
        page: 1,
        perPage: (ClassicMicroBlog && ClassicMicroBlog.defaultPerPage) || 10,
        loading: false,
        error: null,
      },
      owner: {
        username: '',
        password: '',
        postId: null,
        loading: false,
        error: null,
        post: null,
      }
    };
  },

  mounted() {
    this.loadTimeline();
  },

  methods: {
    async loadTimeline() {
      this.timeline.loading = true;
      this.timeline.error = null;
      try {
        const url = new URL(ClassicMicroBlog.restPostsUrl);
        url.searchParams.set('per_page', this.timeline.perPage);
        url.searchParams.set('page', this.timeline.page);

        const res = await fetch(url.toString(), {
          headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) {
          throw new Error(`REST error ${res.status}`);
        }
        const data = await res.json();

        // Optional: augment with author names via embedded data if available
        // If you want author names, you can add `_embed=author` query or do a second fetch.
        this.timeline.items = Array.isArray(data) ? data : [];

      } catch (e) {
        this.timeline.error = e.message || String(e);
      } finally {
        this.timeline.loading = false;
      }
    },

    nextPage() {
      this.timeline.page += 1;
      this.loadTimeline();
    },

    prevPage() {
      if (this.timeline.page > 1) {
        this.timeline.page -= 1;
        this.loadTimeline();
      }
    },

    async fetchOwnerPost() {
      this.owner.loading = true;
      this.owner.error = null;
      this.owner.post = null;

      if (!this.owner.username || !this.owner.password || !this.owner.postId) {
        this.owner.error = 'Username, password, and post ID are required';
        this.owner.loading = false;
        return;
      }

      try {
        // Build body for admin-ajax csm_posts_crud (op=get, post_id)
        const body = new URLSearchParams({
          action: 'csm_posts_crud',
          op: 'get',
          post_id: String(this.owner.postId),
        });

        const auth = 'Basic ' + btoa(`${this.owner.username}:${this.owner.password}`);

        const res = await fetch(ClassicMicroBlog.adminAjaxUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest', // required by guard
            'Authorization': auth,                 // Basic Auth required
            'Accept': 'application/json',
          },
          body: body.toString(),
          credentials: 'same-origin',
        });

        const json = await res.json();

        if (!json || json.success !== true) {
          const msg = (json && json.data && json.data.message) ? json.data.message : `AJAX error ${res.status}`;
          throw new Error(msg);
        }

        this.owner.post = json.data.post;

      } catch (e) {
        this.owner.error = e.message || String(e);
      } finally {
        this.owner.loading = false;
      }
    },
  }
}).mount('#app');