# The database image of the compose stack of AzeriCard.
#
# The service builds from this file instead of pointing at a registry image
# (R91). The base image is pinned by digest.

FROM mongo:9@sha256:452bdaf2127b8ff851e31410a267809b9829eded219979f2210e7c7efb85a058

# The data directory is a bind mount from var/mongo, so the permissions of the
# mount point are set on the host side.
WORKDIR /data/db
