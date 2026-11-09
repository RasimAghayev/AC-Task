# The image of the frontend of AzeriCard.
#
# The base image is pinned by digest.

FROM node:26-alpine@sha256:8996bb5821d62a8fd9bb27c1cb07462f3fdce6e47d640c6a881ead0b064ce216 AS base

WORKDIR /var/www/html/fe

# The dependency layer. The lock file alone decides the tree, so the layer is
# reused whenever only the code changes.
COPY src/fe/package.json src/fe/package-lock.json ./
RUN npm ci --no-audit --no-fund

# The image of the build. The documents are written into the directory of the
# machine, so the web server can serve them.
FROM base AS build

COPY src/fe/ ./
RUN npm run build

# The image of the development. The source is mounted over it.
FROM base AS dev

COPY src/fe/ ./

EXPOSE 5173

CMD ["npm", "run", "dev"]
