# The database image of the compose stack of AzeriCard.
#
# The service builds from this file instead of pointing at a registry image
# (R91). The base image is pinned by digest.

FROM postgres:19-alpine@sha256:8b0ff4cdf221f0c53d7fcda681d3b461be725c492cd881c5ec9c0ad25023f188

# The data directory is a bind mount from var/postgres, so the permissions of
# the mount point are set on the host side.
WORKDIR /var/lib/postgresql/data
