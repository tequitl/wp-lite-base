;(function () {
  const { createApp, reactive, onMounted } = Vue

  function fetchJson(url) {
    return fetch(url, { credentials: 'same-origin' }).then(async (res) => {
      if (!res.ok) throw new Error(`Request failed: ${res.status}`)
      const data = await res.json()
      return { data, res }
    })
  }

  const app = createApp({
    setup() {
      const timeline = reactive({
        items: [],
        loading: true,
        error: '',
      })

      function loadPosts() {
        timeline.loading = true
        timeline.error = ''
        const url = CMBTimelineConfig?.postsQuery || CMBTimelineConfig?.postsUrl
        fetchJson(url)
          .then(({ data }) => {
            timeline.items = Array.isArray(data) ? data : []
          })
          .catch((err) => {
            timeline.error = err?.message || 'Failed to fetch posts'
          })
          .finally(() => {
            timeline.loading = false
          })
      }

      onMounted(() => {
        loadPosts()
      })

      return {
        timeline,
        loadPosts,
      }
    },
    template: `
      <div>
        <h2 class="card-title" style="margin-bottom:12px;">Timeline</h2>

        <div v-if="timeline.loading" class="muted">Loading...</div>
        <div v-else-if="timeline.error" class="muted" style="color:#ff4d4f;">{{ timeline.error }}</div>

        <div v-else>
          <div v-if="timeline.items.length === 0" class="muted">No posts found.</div>
          <div v-for="post in timeline.items" :key="post.id" class="card" style="margin-top:12px;">
            <a :href="post.link" style="text-decoration:none; color:inherit;">
              <h3 style="margin:0 0 6px 0;">{{ post.title?.rendered || 'Untitled' }}</h3>
            </a>
            <div class="muted" style="font-size:12px; margin-bottom:8px;">
              {{ new Date(post.date).toLocaleString() }}
            </div>
            <div class="post-content" v-html="post.excerpt?.rendered || post.content?.rendered"></div>
          </div>
        </div>
      </div>
    `,
  })

  const el = document.querySelector('#timeline-app')
  if (el) app.mount(el)
})()