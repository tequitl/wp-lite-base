(function () {
  const config = window.FreeRadioFrontend || {};

  function qs(root, selector) {
    return root.querySelector(selector);
  }

  function safeJsonParse(text) {
    try {
      return JSON.parse(text);
    } catch (e) {
      return null;
    }
  }

  function ready(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
    } else {
      fn();
    }
  }

  function initPlayer(playerEl) {
    const statusEl = qs(playerEl, "[data-freeradio-status]");
    const audioEl = qs(playerEl, "[data-freeradio-audio]");
    const wsUrl = (playerEl.getAttribute("data-ws-url") || config.wsUrl || "").trim();

    if (!statusEl || !audioEl || !wsUrl) {
      return;
    }

    let ws = null;
    let reconnectTimer = null;
    let online = false;
    let mimeType = "audio/webm;codecs=opus";

    let mediaSource = null;
    let sourceBuffer = null;
    let queue = [];
    let objectUrl = "";

    function setStatus(isOnline) {
      online = !!isOnline;
      statusEl.textContent = online ? "Online" : "Offline";
      statusEl.classList.toggle("is-online", online);
      statusEl.classList.toggle("is-offline", !online);
    }

    function resetStream() {
      queue = [];
      sourceBuffer = null;
      mediaSource = null;
      if (objectUrl) {
        try {
          URL.revokeObjectURL(objectUrl);
        } catch (e) {}
        objectUrl = "";
      }
      try {
        audioEl.pause();
      } catch (e) {}
      audioEl.removeAttribute("src");
      audioEl.load();
    }

    function appendFromQueue() {
      if (!sourceBuffer || sourceBuffer.updating) {
        return;
      }
      if (queue.length === 0) {
        return;
      }
      const chunk = queue.shift();
      try {
        sourceBuffer.appendBuffer(chunk);
      } catch (e) {
        resetStream();
      }
    }

    function ensureMediaSource() {
      if (!window.MediaSource) {
        return false;
      }
      if (mediaSource) {
        return true;
      }
      if (!MediaSource.isTypeSupported(mimeType)) {
        return false;
      }

      mediaSource = new MediaSource();
      objectUrl = URL.createObjectURL(mediaSource);
      audioEl.src = objectUrl;

      mediaSource.addEventListener("sourceopen", function () {
        if (!mediaSource) {
          return;
        }
        try {
          sourceBuffer = mediaSource.addSourceBuffer(mimeType);
        } catch (e) {
          resetStream();
          return;
        }
        sourceBuffer.mode = "sequence";
        sourceBuffer.addEventListener("updateend", appendFromQueue);
        appendFromQueue();
      });

      return true;
    }

    function scheduleReconnect() {
      if (reconnectTimer) {
        return;
      }
      reconnectTimer = window.setTimeout(function () {
        reconnectTimer = null;
        connect();
      }, 2000);
    }

    function connect() {
      if (ws) {
        try {
          ws.close();
        } catch (e) {}
        ws = null;
      }

      try {
        ws = new WebSocket(wsUrl);
      } catch (e) {
        scheduleReconnect();
        return;
      }

      ws.binaryType = "arraybuffer";

      ws.addEventListener("open", function () {
        try {
          ws.send(JSON.stringify({ type: "hello", role: "listener" }));
        } catch (e) {}
      });

      ws.addEventListener("message", function (event) {
        if (typeof event.data === "string") {
          const msg = safeJsonParse(event.data);
          if (!msg || !msg.type) {
            return;
          }
          if (msg.type === "status") {
            setStatus(!!msg.online);
            if (!msg.online) {
              resetStream();
            }
            if (msg.mime && typeof msg.mime === "string") {
              mimeType = msg.mime;
            }
            return;
          }
          if (msg.type === "mime" && typeof msg.mime === "string") {
            mimeType = msg.mime;
            resetStream();
            return;
          }
          return;
        }

        const buf = event.data;
        if (!(buf instanceof ArrayBuffer)) {
          return;
        }
        if (!online) {
          setStatus(true);
        }
        if (!ensureMediaSource()) {
          return;
        }
        queue.push(new Uint8Array(buf));
        appendFromQueue();
      });

      ws.addEventListener("close", function () {
        setStatus(false);
        resetStream();
        scheduleReconnect();
      });

      ws.addEventListener("error", function () {
        scheduleReconnect();
      });
    }

    setStatus(false);
    connect();
  }

  ready(function () {
    const players = document.querySelectorAll("[data-freeradio-player]");
    for (const el of players) {
      initPlayer(el);
    }
  });
})();

