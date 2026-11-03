# ═══════════════════════════════════════════════════════════════
# Stage 1 — Build the React app
# ═══════════════════════════════════════════════════════════════
FROM node:20-alpine AS builder

WORKDIR /app

# Copy package files first for better layer caching
COPY package.json package-lock.json* ./

# Install dependencies with lockfile (falls back to install if no lock)
RUN npm ci

# Copy source
COPY . .

# Build for production (outputs to /app/dist)
RUN npm run build

# ═══════════════════════════════════════════════════════════════
# Stage 2 — Serve with Nginx
# ═══════════════════════════════════════════════════════════════
FROM nginx:1.27-alpine

# Copy built static assets
COPY --from=builder /app/dist /usr/share/nginx/html

# Copy our custom nginx config
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]