# The image of the frontend of AzeriCard.
#
# The base image is pinned by digest. Multi-stage: dependencies are installed
# once in `base`, the production bundle is built in `build`, and `dev` is the
# hot-reload entry used only locally. Nothing runs as root.

FROM node:26-alpine@sha256:8996bb5821d62a8fd9bb27c1cb07462f3fdce6e47d640c6a881ead0b064ce216 AS base

WORKDIR /var/www/html/fe

# The dependency layer. The lock file alone decides the tree, so the layer is
# reused whenever only the code changes. Installed as the non-root `node` user
# so node_modules are not root-owned inside the image.
COPY --chown=node:node src/fe/package.json src/fe/package-lock.json ./
USER node
RUN npm ci --no-audit --no-fund

# The image of the build. The production documents are written into the
# `dist` directory, which the compose stack bind-mounts (`var/fe`) so nginx
# can serve them statically.
FROM base AS build

USER node
COPY --chown=node:node src/fe/ ./
RUN npm run build

# The image of local development. The source is mounted over it; the dev
# server is intentionally not the container's default command in production.
FROM base AS dev

USER node
COPY --chown=node:node src/fe/ ./

EXPOSE 5173

CMD ["npm", "run", "dev"]
