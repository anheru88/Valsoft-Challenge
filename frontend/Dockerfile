# syntax=docker/dockerfile:1

# --- Shared dependency layer -------------------------------------------------
# npm ci runs once here; both build branches reuse the cached node_modules.
FROM node:22-alpine AS deps
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci

# --- Build the Angular SPA (production configuration) ------------------------
FROM deps AS build
COPY . .
# defaultConfiguration is production, which swaps in environment.ts
# (apiUrl '/api/v1') — a relative path the nginx proxy resolves.
RUN npm run build

# --- Build Storybook (static, relative asset paths) --------------------------
FROM deps AS storybook-build
COPY . .
RUN npm run build-storybook

# --- Serve the SPA + Storybook behind nginx ----------------------------------
FROM nginx:alpine AS app
# The application builder emits the browser bundle under dist/<project>/browser.
COPY --from=build /app/dist/librarium/browser /usr/share/nginx/html
# Storybook rides along under /storybook (its assets are relative, so the
# sub-path needs no base-href). The nginx template routes it.
COPY --from=storybook-build /app/storybook-static /usr/share/nginx/html/storybook
# Rendered by nginx's envsubst entrypoint from the container environment.
COPY docker/default.conf.template /etc/nginx/templates/default.conf.template
EXPOSE 80
