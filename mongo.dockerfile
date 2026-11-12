# The database image of the compose stack of AzeriCard.
#
# The service builds from this file instead of pointing at a registry image
# (R91). The base image is pinned by digest.

FROM mongo:9@sha256:1444adda2e4b8d6a0d7f242a625d834a78d6e3aa6e4ed1b0b4c2dab2318c5e3d

# The data directory is a bind mount from var/mongo, so the permissions of the
# mount point are set on the host side.
WORKDIR /data/db
