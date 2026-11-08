# The database image of the compose stack of AzeriCard.
#
# The service builds from this file instead of pointing at a registry image
# (R91). The base image is pinned by digest.

FROM mongo:7@sha256:8bb4442761a63a8dda452744082cdac9644b429807eb977b42324a63cef59ca4

# The data directory is a bind mount from var/mongo, so the permissions of the
# mount point are set on the host side.
WORKDIR /data/db
