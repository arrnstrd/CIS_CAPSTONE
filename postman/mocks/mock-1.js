const http = require("http");

const PORT = process.env.PORT || 4500;

let logs = [];

const server = http.createServer((req, res) => {
  const { method, url } = req;

  // Health check
  if (method === "GET" && url === "/health") {
    res.writeHead(200, { "Content-Type": "application/json" });
    return res.end(JSON.stringify({ status: "ok" }));
  }

  // Simulated QR scan endpoint
  if (method === "POST" && url === "/scan") {
    let body = "";

    req.on("data", chunk => {
      body += chunk;
    });

    req.on("end", () => {
      const data = JSON.parse(body || "{}");

      const now = new Date().toISOString();

      logs.push({
        code: data.code,
        time: now
      });

      res.writeHead(200, { "Content-Type": "application/json" });

      return res.end(JSON.stringify({
        message: "scan received",
        code: data.code,
        total_scans: logs.length,
        time: now
      }));
    });

    return;
  }

  res.writeHead(404, { "Content-Type": "application/json" });
  res.end(JSON.stringify({ error: "Endpoint not defined" }));
});

server.listen(PORT, () => {
  console.log(`Mock server running on http://localhost:${PORT}`);
});