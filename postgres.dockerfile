# The database image of the compose stack of AzeriCard.
#
# The service builds from this file instead of pointing at a registry image
# (R91). The base image is pinned by digest.

FROM postgres:16-alpine@sha256:721873c34ceb9f8d8fc265984940dc982404c105f19ad51be9fdc5970a6080ea

# The data directory is a bind mount from var/postgres, so the permissions of
# the mount point are set on the host side.
WORKDIR /var/lib/postgresql/data
