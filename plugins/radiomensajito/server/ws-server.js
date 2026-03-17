const http = require("http");
const WebSocket = require("ws");

const port = parseInt(process.env.PORT || "8080", 10);

const server = http.createServer((req, res) => {
  res.writeHead(200, { "Content-Type": "text/plain; charset=utf-8" });
  res.end("freeradio websocket server\n");
});

const wss = new WebSocket.Server({ server });

let transmitter = null;
let transmitterMime = "audio/webm;codecs=opus";

function sendJson(ws, obj) {
  try {
    ws.send(JSON.stringify(obj));
  } catch (e) {}
}

function broadcastJson(obj) {
  const msg = JSON.stringify(obj);
  for (const client of wss.clients) {
    if (client.readyState === WebSocket.OPEN) {
      try {
        client.send(msg);
      } catch (e) {}
    }
  }
}

function broadcastBinary(buffer, exceptWs) {
  for (const client of wss.clients) {
    if (client === exceptWs) {
      continue;
    }
    if (client.readyState === WebSocket.OPEN && client.role === "listener") {
      try {
        client.send(buffer, { binary: true });
      } catch (e) {}
    }
  }
}

function updateStatus() {
  broadcastJson({ type: "status", online: !!transmitter, mime: transmitterMime });
}

wss.on("connection", (ws) => {
  ws.role = null;

  sendJson(ws, { type: "status", online: !!transmitter, mime: transmitterMime });

  ws.on("message", (data, isBinary) => {
    if (!ws.role) {
      if (isBinary) {
        return;
      }
      let msg = null;
      try {
        msg = JSON.parse(data.toString("utf8"));
      } catch (e) {
        return;
      }
      if (!msg || msg.type !== "hello") {
        return;
      }

      const role = msg.role === "transmitter" ? "transmitter" : "listener";
      ws.role = role;

      if (role === "transmitter") {
        if (transmitter && transmitter !== ws) {
          try {
            transmitter.close(4000, "replaced");
          } catch (e) {}
        }
        transmitter = ws;
        if (typeof msg.mime === "string" && msg.mime.trim()) {
          transmitterMime = msg.mime.trim();
        }
        updateStatus();
        broadcastJson({ type: "mime", mime: transmitterMime });
      } else {
        sendJson(ws, { type: "mime", mime: transmitterMime });
      }

      return;
    }

    if (ws.role === "transmitter") {
      if (isBinary) {
        broadcastBinary(data, ws);
        return;
      }
      let msg = null;
      try {
        msg = JSON.parse(data.toString("utf8"));
      } catch (e) {
        return;
      }
      if (msg && msg.type === "mime" && typeof msg.mime === "string" && msg.mime.trim()) {
        transmitterMime = msg.mime.trim();
        broadcastJson({ type: "mime", mime: transmitterMime });
        updateStatus();
      }
    }
  });

  ws.on("close", () => {
    if (ws === transmitter) {
      transmitter = null;
      updateStatus();
    }
  });
});

server.listen(port, () => {
  process.stdout.write(`freeradio ws server listening on http://localhost:${port}\n`);
});

