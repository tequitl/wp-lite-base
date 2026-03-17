(function () {
  const config = window.FreeRadioAdmin || {};

  function pickMimeType() {
    const candidates = [
      "audio/webm;codecs=opus",
      "audio/webm; codecs=opus",
      "audio/webm",
    ];
    if (!window.MediaRecorder || !MediaRecorder.isTypeSupported) {
      return "";
    }
    for (const mime of candidates) {
      if (MediaRecorder.isTypeSupported(mime)) {
        return mime;
      }
    }
    return "";
  }

  function ready(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
    } else {
      fn();
    }
  }

  ready(function () {
    const startBtn = document.querySelector('[data-freeradio-transmit="start"]');
    const stopBtn = document.querySelector('[data-freeradio-transmit="stop"]');
    const statusEl = document.querySelector("[data-freeradio-transmit-status]");
    const wsUrlInput = document.getElementById("freeradio_ws_url");

    if (!startBtn || !stopBtn || !statusEl) {
      return;
    }

    let ws = null;
    let stream = null;
    let recorder = null;

    function setStatus(text) {
      statusEl.textContent = text;
    }

    function setButtons(transmitting) {
      startBtn.disabled = transmitting;
      stopBtn.disabled = !transmitting;
    }

    async function stopTransmit() {
      setButtons(false);
      try {
        if (recorder && recorder.state !== "inactive") {
          recorder.stop();
        }
      } catch (e) {}
      recorder = null;

      try {
        if (stream) {
          for (const track of stream.getTracks()) {
            track.stop();
          }
        }
      } catch (e) {}
      stream = null;

      try {
        if (ws) {
          ws.close();
        }
      } catch (e) {}
      ws = null;

      setStatus("Idle");
    }

    async function startTransmit() {
      const wsUrl = (wsUrlInput && wsUrlInput.value ? wsUrlInput.value : config.wsUrl || "").trim();
      if (!wsUrl) {
        setStatus("Missing WebSocket URL");
        return;
      }

      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        setStatus("Microphone not available");
        return;
      }

      setStatus("Requesting microphone…");
      setButtons(true);

      const mimeType = pickMimeType();
      if (!mimeType) {
        setStatus("Unsupported audio format");
        setButtons(false);
        return;
      }

      try {
        stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      } catch (e) {
        setStatus("Microphone permission denied");
        setButtons(false);
        return;
      }

      setStatus("Connecting…");
      try {
        ws = new WebSocket(wsUrl);
      } catch (e) {
        setStatus("Invalid WebSocket URL");
        setButtons(false);
        return;
      }
      ws.binaryType = "arraybuffer";

      ws.addEventListener("open", function () {
        try {
          ws.send(JSON.stringify({ type: "hello", role: "transmitter", mime: mimeType }));
        } catch (e) {}
        setStatus("Transmitting");
      });

      ws.addEventListener("close", function () {
        stopTransmit();
      });

      ws.addEventListener("error", function () {
        setStatus("WebSocket error");
      });

      try {
        recorder = new MediaRecorder(stream, { mimeType });
      } catch (e) {
        setStatus("Failed to start recorder");
        stopTransmit();
        return;
      }

      recorder.addEventListener("dataavailable", async function (event) {
        if (!event.data || event.data.size === 0) {
          return;
        }
        if (!ws || ws.readyState !== WebSocket.OPEN) {
          return;
        }
        try {
          const buf = await event.data.arrayBuffer();
          ws.send(buf);
        } catch (e) {}
      });

      recorder.addEventListener("stop", function () {
        if (ws && ws.readyState === WebSocket.OPEN) {
          try {
            ws.send(JSON.stringify({ type: "transmitter-stopped" }));
          } catch (e) {}
        }
      });

      try {
        recorder.start(1000);
      } catch (e) {
        setStatus("Failed to start transmit");
        stopTransmit();
      }
    }

    startBtn.addEventListener("click", function () {
      startTransmit();
    });

    stopBtn.addEventListener("click", function () {
      stopTransmit();
    });
  });
})();

