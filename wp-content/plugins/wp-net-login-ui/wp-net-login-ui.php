<?php
/**
 * Plugin Name: WP‑NET Login UI (Monochrome Terminal)
 * Description: Applies a black-and-white, Linux-terminal style and a p5.js network background to the WordPress login page.
 * Version: 0.1.0
 * Author: WP‑NET
 */

if (!defined('ABSPATH')) {
    exit;
}

// Add the canvas container to the login page
add_action('login_footer', function () {
    echo '<div id="canvas-container" aria-hidden="true"></div>';
});

// Enqueue fonts, p5.js, CSS, and attach the animation script
add_action('login_enqueue_scripts', function () {
    // Optional: load monochrome-friendly monospace font
    wp_enqueue_style(
        'wpnet-login-font',
        'https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap',
        [],
        null
    );

    // Register a style handle to attach our inline CSS
    wp_register_style('wpnet-login-style', false);
    wp_enqueue_style('wpnet-login-style');

    $css = <<<CSS
    body.login {
      margin: 0;
      background: #000000;
      color: #e6e6e6;
      font-family: 'Share Tech Mono', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      overflow: hidden;
    }
    body.login:before {
      content: "";
      position: fixed;
      inset: 0;
      background: repeating-linear-gradient(transparent 0px, rgba(255,255,255,0.02) 2px, transparent 4px);
      mix-blend-mode: soft-light;
      pointer-events: none;
      z-index: 1;
    }
    #canvas-container {
      position: fixed;
      inset: 0;
      z-index: 0;
    }

    /* Place login UI above the canvas */
    #login { position: relative; z-index: 2; }

    /* Hide WP logo for clean terminal look (keep accessible link off-screen) */
    #login h1 a {
      background-image: none !important;
      text-indent: -9999px;
      overflow: hidden;
      height: 0;
      display: block;
    }

    /* Panel styling similar to index-bw */
    .login form {
      background: #000;
      border: 1px solid #e6e6e6;
      border-radius: 6px;
      box-shadow: 0 0 0 2px rgba(255,255,255,0.05), 0 0 18px rgba(255,255,255,0.08);
      padding: 1.6rem;
    }
    .login label {
      font-size: 0.85rem;
      color: #bdbdbd;
      letter-spacing: 0.04em;
    }
    .login form .input, .login input[type="text"], .login input[type="password"] {
      background: #000;
      color: #e6e6e6;
      border: 1px solid #e6e6e6;
      border-radius: 4px;
      padding: 0.7rem 0.9rem;
      outline: none;
      transition: box-shadow 140ms ease, border-color 140ms ease, transform 140ms ease;
    }
    .login input[type="text"]::placeholder, .login input[type="password"]::placeholder {
      color: #9a9a9a;
    }
    .login input[type="text"]:focus, .login input[type="password"]:focus {
      border-color: #ffffff;
      box-shadow: 0 0 0 2px rgba(255,255,255,0.12);
      transform: translateZ(0) scale(1.01);
    }

    /* Primary button: invert on hover */
    .wp-core-ui .button-primary {
      appearance: none;
      border: 1px solid #e6e6e6;
      background: #000;
      color: #e6e6e6;
      font-weight: 700;
      letter-spacing: 0.06em;
      padding: 0.6rem 1rem;
      border-radius: 4px;
      cursor: pointer;
      transition: transform 140ms ease, box-shadow 140ms ease, color 140ms ease, background 140ms ease;
    }
    .wp-core-ui .button-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 0 0 2px rgba(255,255,255,0.08);
      background: #fff;
      color: #000;
    }

    /* Links */
    .login #nav a, .login #backtoblog a {
      color: #e6e6e6;
      text-decoration: none;
      font-size: 0.85rem;
    }
    .login #nav a:hover, .login #backtoblog a:hover { text-decoration: underline; }
    CSS;

    wp_add_inline_style('wpnet-login-style', $css);

    // p5.js from CDN
    wp_enqueue_script('p5', 'https://cdn.jsdelivr.net/npm/p5@1.9.0/lib/p5.min.js', [], null, true);

    // Attach our monochrome network + grid animation (instance mode)
    $js = <<<JS
    (function () {
      new p5(function (p) {
        let nodes = [];
        let linkThreshold;
        let baseCount;

        // Grid parameters (monochrome)
        const gridSpacing = 40;
        const gridTilt = Math.PI / 12;
        const gridAlpha = 22; // light lines

        const mouseInfluence = 140;

        function computeDensity() {
          const area = p.windowWidth * p.windowHeight;
          baseCount = p.constrain(Math.floor(area * 0.00008), 140, 520);
          linkThreshold = p.constrain(Math.min(p.windowWidth, p.windowHeight) * 0.18, 100, 240);
        }

        function initNodes() {
          nodes = [];
          for (let i = 0; i < baseCount; i++) {
            nodes.push({
              x: p.random(p.width),
              y: p.random(p.height),
              vx: p.random(-0.35, 0.35),
              vy: p.random(-0.35, 0.35),
              r: p.random(1.4, 2.6),
              intensity: p.random(210, 255),
              pulsePhase: p.random(p.TWO_PI)
            });
          }
        }

        p.setup = function () {
          const cnv = p.createCanvas(p.windowWidth, p.windowHeight);
          cnv.parent("canvas-container");
          computeDensity();
          initNodes();
          p.strokeCap(p.ROUND);
        };

        p.windowResized = function () {
          p.resizeCanvas(p.windowWidth, p.windowHeight);
          computeDensity();
          initNodes();
        };

        function drawGrid() {
          p.push();
          p.translate(p.width / 2, p.height / 2);
          p.rotate(gridTilt);
          p.translate(-p.width / 2, -p.height / 2);

          p.stroke(255, gridAlpha);
          p.strokeWeight(1);

          for (let x = 0; x < p.width + gridSpacing; x += gridSpacing) {
            p.line(x, 0, x, p.height);
          }
          for (let y = 0; y < p.height + gridSpacing; y += gridSpacing) {
            p.line(0, y, p.width, y);
          }

          p.pop();
        }

        p.draw = function () {
          p.background(0);
          drawGrid();

          // Soft monochrome vignette
          p.noStroke();
          p.fill(255, 12);
          p.rect(0, 0, p.width, p.height);

          // Update nodes with wrap
          for (let n of nodes) {
            n.x += n.vx;
            n.y += n.vy;
            if (n.x < -20) n.x = p.width + 20;
            if (n.x > p.width + 20) n.x = -20;
            if (n.y < -20) n.y = p.height + 20;
            if (n.y > p.height + 20) n.y = -20;
          }

          // Links (white lines, variable alpha/width)
          for (let i = 0; i < nodes.length; i++) {
            const a = nodes[i];
            for (let j = i + 1; j < nodes.length; j++) {
              const b = nodes[j];
              const dx = a.x - b.x;
              const dy = a.y - b.y;
              const d = Math.sqrt(dx * dx + dy * dy);
              if (d < linkThreshold) {
                const nearMouseA = p.dist(a.x, a.y, p.mouseX, p.mouseY) < mouseInfluence;
                const nearMouseB = p.dist(b.x, b.y, p.mouseX, p.mouseY) < mouseInfluence;
                const boost = (nearMouseA || nearMouseB) ? 1.25 : 1.0;

                const alpha = p.map(d, 0, linkThreshold, 180, 10) * boost;
                const w = p.map(d, 0, linkThreshold, 2.2, 0.5) * boost;

                p.stroke(255, alpha);
                p.strokeWeight(w);
                p.line(a.x, a.y, b.x, b.y);
              }
            }
          }

          // Nodes (white glow circles)
          for (let n of nodes) {
            const pulse = (p.sin(p.frameCount * 0.03 + n.pulsePhase) + 1) * 0.5;
            const glow = 140 + 60 * pulse;
            p.noStroke();
            p.fill(n.intensity, glow * 0.3);
            p.circle(n.x, n.y, n.r * 5.2);
            p.fill(n.intensity, 220);
            p.circle(n.x, n.y, n.r * (1.5 + 0.6 * pulse));
          }
        };

        p.mouseMoved = function () {
          // Nudge nodes away from mouse for interactivity
          for (let n of nodes) {
            const d = p.dist(n.x, n.y, p.mouseX, p.mouseY);
            if (d < mouseInfluence) {
              const angle = Math.atan2(n.y - p.mouseY, n.x - p.mouseX);
              n.vx += 0.02 * Math.cos(angle);
              n.vy += 0.02 * Math.sin(angle);
              n.vx = Math.max(-0.6, Math.min(0.6, n.vx));
              n.vy = Math.max(-0.6, Math.min(0.6, n.vy));
            }
          }
        };

        p.touchMoved = function () {
          p.mouseMoved();
          return false;
        };
      });
    })();
    JS;

    // Attach our script right after p5
    wp_add_inline_script('p5', $js, 'after');
});