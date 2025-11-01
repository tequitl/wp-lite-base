(function () {
  const { createApp, reactive, onMounted, computed } = Vue

  function fetchJson(url) {
    return fetch(url, { credentials: 'same-origin' }).then(async (res) => {
      if (!res.ok) throw new Error(`Request failed: ${res.status}`)
      const data = await res.json()
      return { data, res }
    })
  }

  function postForm(url, formData) {
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        // Let WordPress parse it as regular comment form post
        // Do not set Content-Type so browser sets multipart/form-data for FormData
      },
      body: formData,
    }).then(async (res) => {
      // WordPress usually redirects to the post page after comment submission
      const text = await res.text()
      return { ok: res.ok, status: res.status, text, res }
    })
  }

  const app = createApp({
    setup() {
      const state = reactive({
        comments: [],
        loading: true,
        error: '',
        form: {
          author: '',
          email: '',
          url: '',
          comment: '',
          parent: 0,
        },
        submitting: false,
      })

      const requireNameEmail = !!(CMBCommentsConfig && CMBCommentsConfig.requireNameEmail)
      const postId = CMBCommentsConfig?.postId

      const canSubmit = computed(() => {
        if (!state.form.comment.trim()) return false
        if (requireNameEmail) {
          if (!state.form.author.trim()) return false
          if (!state.form.email.trim()) return false
        }
        return true
      })

      function loadComments() {
        state.loading = true
        state.error = ''
        fetchJson(CMBCommentsConfig.commentsQuery)
          .then(({ data }) => {
            // Normalize a bit for display
            state.comments = Array.isArray(data) ? data : []
          })
          .catch((err) => {
            state.error = err.message || 'Failed to load comments'
          })
          .finally(() => {
            state.loading = false
          })
      }

      function submitComment() {
        if (!canSubmit.value || state.submitting) return
        state.submitting = true
        state.error = ''

        const fd = new FormData()
        fd.append('comment_post_ID', String(postId))
        fd.append('comment', state.form.comment)
        fd.append('comment_parent', String(state.form.parent || 0))
        // Optional identity fields
        if (state.form.author) fd.append('author', state.form.author)
        if (state.form.email) fd.append('email', state.form.email)
        if (state.form.url) fd.append('url', state.form.url)

        postForm(CMBCommentsConfig.submitUrl, fd)
          .then(({ ok, status }) => {
            if (!ok) {
              throw new Error(`Submit failed (${status})`)
            }
            // Clear the comment box; keep author info for convenience
            state.form.comment = ''
            // Refresh list
            loadComments()
          })
          .catch((err) => {
            state.error = err.message || 'Failed to submit comment'
          })
          .finally(() => {
            state.submitting = false
          })
      }

      onMounted(() => {
        loadComments()
      })

      return {
        state,
        canSubmit,
        requireNameEmail,
        loadComments,
        submitComment,
      }
    },
    template: `
      <div>
        <h2 class="card-title" style="margin-bottom: 12px;">
          {{ state.comments.length === 1 ? 'Comment (1)' : 'Comments (' + state.comments.length + ')' }}
        </h2>

        <div v-if="state.loading" class="muted">Loading comments…</div>
        <div v-else-if="state.error" class="muted" style="color:#ff4d4f;">{{ state.error }}</div>

        <ol v-else class="comment-list" style="list-style:none; padding:0; margin:0;">
          <li v-for="c in state.comments" :key="c.id" class="comment-item" style="padding:12px 0; border-top:1px solid rgba(255,255,255,0.08);">
            <div class="comment-meta" style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
              <img v-if="c.author_avatar_urls?.['48']" :src="c.author_avatar_urls['48']" alt="" width="32" height="32" style="border-radius:50%;" />
              <div class="comment-author" style="font-weight:600;">
                {{ c.author_name || 'Anonymous' }}
              </div>
              <div class="muted" style="font-size:12px;">
                {{ new Date(c.date).toLocaleString() }}
              </div>
            </div>
            <div class="comment-content" v-html="c.content?.rendered"></div>
          </li>
        </ol>

        <h3 class="card-title" style="margin-top:16px; margin-bottom:12px;">Write a comment</h3>
        <div class="comment-form" style="display:flex; flex-direction:column; gap:10px;">
          <div v-if="requireNameEmail" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div style="display:flex; flex-direction:column; gap:6px;">
              <label style="font-weight:600;">Name</label>
              <input type="text" class="input" v-model.trim="state.form.author" />
            </div>
            <div style="display:flex; flex-direction:column; gap:6px;">
              <label style="font-weight:600;">Email</label>
              <input type="email" class="input" v-model.trim="state.form.email" />
            </div>
          </div>
          <div style="display:flex; flex-direction:column; gap:6px;">
            <label style="font-weight:600;">Website (optional)</label>
            <input type="url" class="input" v-model.trim="state.form.url" />
          </div>
          <div style="display:flex; flex-direction:column; gap:6px;">
            <label style="font-weight:600;">Comment</label>
            <textarea class="input" rows="6" v-model.trim="state.form.comment"></textarea>
          </div>
          <div style="display:flex; gap:12px; align-items:center;">
            <button class="btn primary" :disabled="!canSubmit || state.submitting" @click="submitComment">
              {{ state.submitting ? 'Submitting…' : 'Post Comment' }}
            </button>
            <button class="btn secondary" :disabled="state.submitting" @click="loadComments">
              Refresh
            </button>
          </div>
        </div>
      </div>
    `,
  })

  app.mount('#comments-app')
})()