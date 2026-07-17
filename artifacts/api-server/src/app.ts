import express, { type Express } from "express";
import cors from "cors";
import pinoHttp from "pino-http";
import http from "http";
import router from "./routes";
import { logger } from "./lib/logger";

const app: Express = express();

app.use(
  pinoHttp({
    logger,
    serializers: {
      req(req) {
        return {
          id: req.id,
          method: req.method,
          url: req.url?.split("?")[0],
        };
      },
      res(res) {
        return {
          statusCode: res.statusCode,
        };
      },
    },
  }),
);
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

app.use("/api", router);

// Proxy all other requests to the Nepal News Portal PHP server on port 3000
const PHP_PORT = 3000;
app.use((req, res, next) => {
  const options: http.RequestOptions = {
    hostname: "127.0.0.1",
    port: PHP_PORT,
    path: req.url,
    method: req.method,
    headers: { ...req.headers, host: `localhost:${PHP_PORT}` },
  };

  const proxyReq = http.request(options, (proxyRes) => {
    // Pass through status and headers from PHP
    const headers: Record<string, string | string[]> = {};
    for (const [k, v] of Object.entries(proxyRes.headers)) {
      if (v !== undefined) headers[k] = v as string | string[];
    }
    res.writeHead(proxyRes.statusCode ?? 502, headers);
    proxyRes.pipe(res, { end: true });
  });

  proxyReq.on("error", (err) => {
    logger.warn({ err }, "PHP proxy error");
    next(err);
  });

  req.pipe(proxyReq, { end: true });
});

export default app;
