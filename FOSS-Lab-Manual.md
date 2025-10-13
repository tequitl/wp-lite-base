# FOSS Lab Manual: WordPress + SQLite + ClassicSocialMedia + Friends

A replicable blueprint to run a small, portable Free and Open Source Software (FOSS) lab using WordPress with SQLite, the ClassicSocialMedia theme, and the Friends plugin.

ClassicSocialMedia is a fork of the Facebook-Like theme by Linesh Jose <mcreference link="https://github.com/lineshjose/Facebook-Like" index="0">0</mcreference>  
Friends plugin by Alex Kirk adds a social reader and interaction layer on your site <mcreference link="https://wordpress.org/plugins/friends/" index="1">1</mcreference>

---

## 1) Vision and Goals
- Purpose: Create a local-first, community-friendly environment for publishing, learning, and collaboration with open tools.
- Principles:
  - Own your data (SQLite single-file DB)
  - Minimal stack (fewer moving parts)
  - Open standards (RSS, ActivityPub via additional plugin)
  - Replicable (package and copy to new labs easily)

## 2) Architecture Overview
- CMS: WordPress
- DB: SQLite (via the SQLite Database Integration plugin)
- Theme: ClassicSocialMedia (fork of Facebook-Like) <mcreference link="https://github.com/lineshjose/Facebook-Like" index="0">0</mcreference>
- Social Reader: Friends plugin (feeds, friend requests, reading flows) <mcreference link="https://wordpress.org/plugins/friends/" index="1">1</mcreference>
- Hosting modes:
  - Local development (laptops for workshops)
  - Raspberry Pi or small VPS for persistent labs
  - “Replication Pack” for cloning labs quickly

## 3) Prerequisites
- macOS or Linux (Windows works but optimize for Unix-like workflows)
- PHP 7.4+ recommended
- WordPress files in your project directory
- SQLite Database Integration plugin available in wp-content/plugins
- Optional: WP-CLI for admin tasks

## 4) Setup (Local-first)
- Step 1: Place WordPress files in your target folder and serve locally (Apache/Nginx or PHP built-in server).
- Step 2: Enable the SQLite Database Integration plugin to use SQLite instead of MySQL.
- Step 3: Run the WordPress installer via your browser; create an admin account.
- Step 4: Activate the ClassicSocialMedia theme via Appearance → Themes. <mcreference link="https://github.com/lineshjose/Facebook-Like" index="0">0</mcreference>
- Step 5: Install and activate the Friends plugin (Plugins → Add New → “Friends”). <mcreference link="https://wordpress.org/plugins/friends/" index="1">1</mcreference>
- Step 6: Configure permalinks (Settings → Permalinks → Post name).
- Step 7: Create core pages: Home, About, People, Projects, Feed.

## 5) Configure Content and Navigation
- Navigation: Home, Projects, People, Feed, About.
- Home: Mission statement and latest posts.
- People: Lab members, roles, links to their sites.
- Projects: Use categories/tags; each project gets an overview and weekly updates.
- Feed: Display aggregated posts via Friends. <mcreference link="https://wordpress.org/plugins/friends/" index="1">1</mcreference>
- Comments: Keep enabled with moderation; ensure theme uses modern functions (e.g., is_user_logged_in()).

## 6) Friends Plugin Workflows
- Subscribe to peers via RSS to aggregate posts into your lab feed. <mcreference link="https://wordpress.org/plugins/friends/" index="1">1</mcreference>
- Optional: Add ActivityPub for federated follows (Mastodon and compatible). <mcreference link="https://wordpress.org/plugins/friends/" index="1">1</mcreference>
- Use post formats and categories to browse types (notes, links, articles). <mcreference link="https://wordpress.org/plugins/friends/" index="1">1</mcreference>
- Use friend requests to enable private sharing among trusted peers. <mcreference link="https://wordpress.org/plugins/friends/" index="1">1</mcreference>

## 7) Roles and Governance
- Lab Lead: Deployment, updates, security, backups.
- Maintainers: Plugin/theme updates, content moderation.
- Editors: Curate weekly digest and featured content.
- Participants: Publish posts, share tutorials, contribute links.
- Charter: Short code of conduct, contribution guidelines, licensing, privacy.

## 8) Operating Procedures
- Weekly cadence:
  - Publish: Each participant posts at least one note/article.
  - Curate: Editors compile a digest highlighting top posts and projects.
  - Connect: Add 1–2 new feeds to broaden perspectives.
- Moderation:
  - Approve comments regularly
  - Unfollow noisy/off-topic sources
- Documentation:
  - Each project has a README and progress log
  - Use tags per project for consistency

## 9) Backup and Replication
- SQLite backups: Copy the SQLite file regularly (e.g., nightly). Store snapshots safely.
- WordPress content: Tools → Export for posts/pages; mirror wp-content (themes/plugins/uploads) weekly.
- Replication Pack (“FOSS Lab in a Box”):
  - Include: wp-content, wp-config.php (remove secrets), SQLite database file, this manual, and Lab Charter.
  - Zip and publish for new cohorts to spin up identical labs.
- Restore:
  - Place files in clean WordPress
  - Ensure SQLite drop-in is active
  - Update Site URL in Settings if domain changes

## 10) Security and Privacy
- HTTPS for public sites; strong passwords and role separation.
- Apply updates monthly (core, themes, plugins).
- Privacy defaults: Clarify visibility for posts; use Friends for private sharing. <mcreference link="https://wordpress.org/plugins/friends/" index="1">1</mcreference>

## 11) Scaling Options
- Raspberry Pi or small VPS: SQLite is suitable for small to medium traffic.
- Caching/CDN: Optional for read-heavy sites.
- Multi-lab ecosystem: Interlink labs via Friends feeds for cross-site awareness. <mcreference link="https://wordpress.org/plugins/friends/" index="1">1</mcreference>

## 12) Education Modules
- WordPress basics and content publishing
- Web standards, accessibility, performance
- RSS and federation
- Licensing and attribution (GPL, CC)
- SQLite basics and backup strategy

## 13) Templates (Copy-paste)
- Lab Charter:
  - Mission: Learn and build with FOSS; share openly; respect contributors.
  - Roles: Lead, Maintainers, Editors, Participants.
  - Guidelines: Be constructive, attribute sources, respect privacy, license contributions.
- Project README:
  - Title, Description, Goals
  - Setup steps and dependencies
  - Milestones and weekly updates
  - License and acknowledgments
- Weekly Digest:
  - Highlights from lab posts
  - Top external posts from Friends feeds <mcreference link="https://wordpress.org/plugins/friends/" index="1">1</mcreference>
  - Upcoming events and calls for contribution

## 14) Troubleshooting
- Comments not appearing: Check post discussion settings; ensure have_comments() is true; verify theme uses is_user_logged_in() for login checks.
- Friends feed empty: Verify subscriptions are added and fetch is working; check cron or manual refresh. <mcreference link="https://wordpress.org/plugins/friends/" index="1">1</mcreference>
- Performance issues: Reduce plugins, enable caching, review image sizes.

## 15) Credits
- ClassicSocialMedia theme is a fork of: https://github.com/lineshjose/Facebook-Like <mcreference link="https://github.com/lineshjose/Facebook-Like" index="0">0</mcreference>
- Friends plugin by Alex Kirk: https://wordpress.org/plugins/friends/ <mcreference link="https://wordpress.org/plugins/friends/" index="1">1</mcreference>

---