# 🌐 Rebuilding the Independent Web: WordPress + SQLite + ClassicSocialMedia + Friends

## 🏠 A Return to Self-Hosted Freedom
There was a time when the web felt more personal—more human. Before algorithmic feeds and centralized platforms, self-hosted sites and forums fostered genuine connection and creativity.

This project brings that spirit back with a lightweight, portable stack:
- WordPress (the familiar publishing engine)
- SQLite (a single-file database, no MySQL server required)
- ClassicSocialMedia theme (forked from the Facebook-Like theme by Linesh Jose)
- Friends plugin by Alex Kirk (follow, read, and interact with your social web—on your own site)

## 🚀 Why WordPress + SQLite?
- Lightweight & portable: Perfect for local experimentation, small VPS, or Raspberry Pi
- Own your data: Everything lives in a single SQLite file you control
- Minimal setup: No external database server to manage
- Fast to iterate: Spin up, customize, and share quickly

## 🎨 ClassicSocialMedia Theme
ClassicSocialMedia is a fork inspired by the Facebook-Like theme’s clean, social-style layout.
- Origin: https://github.com/lineshjose/Facebook-Like
- Goals: Simplicity, readability, customizable and social-friendly presentation
- Notes: Updated for modern WordPress functions and compatibility

## 🤝 Friends Plugin
Friends adds a social reader to WordPress—subscribe to friends’ sites and interact from your own blog:
- Plugin page: https://wordpress.org/plugins/friends/
- Highlights:
  - Follow blogs via RSS; integrate with ActivityPub to follow Mastodon and other federated platforms
  - Read posts in one place, categorize by post format
  - Interact while preserving your autonomy—no third-party platform lock-in
  - Uses standard WordPress capabilities and data structures

## 🔧 Quick Start
1. Install WordPress (this repository uses the SQLite integration, so no MySQL needed).
2. Activate the SQLite Database Integration plugin (bundled in wp-content/plugins).
3. Activate the ClassicSocialMedia theme in Appearance → Themes.
4. Install and activate the Friends plugin (Plugins → Add New → “Friends”).
5. Start publishing and optionally add friends to follow their content.

## 🧭 Tips
- Keep it minimal: fewer plugins means faster, simpler, more reliable.
- Use HTTPS for public sites (via a reverse proxy or tunneling if needed).
- To share locally, try Cloudflare Tunnel, ngrok, LocalTunnel, or Pagekite.
- Regularly back up your SQLite database file (wp-content/database/ or your configured path).

## 🔒 Security Considerations
- Harden WordPress (strong passwords, limited logins, updates)
- Use modern functions and avoid deprecated globals
- Review plugin settings to match your privacy and sharing goals

## 🙌 Credits
- ClassicSocialMedia theme is a fork of: https://github.com/lineshjose/Facebook-Like
- Friends plugin by Alex Kirk: https://wordpress.org/plugins/friends/
- WordPress + SQLite integration: thank you to the maintainers enabling database portability

## 📜 License
This project builds on open-source software. Refer to each component’s respective license for details.
