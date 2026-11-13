# The database image of the compose stack of AzeriCard.
#
# The service builds from this file instead of pointing at a registry image
# (R91). The base image is pinned by digest.

FROM postgres:18-alpine@sha256:1018733457011bf88d81db98cf43aac6d5150afc18787dbe204771fedf80ff95

# The data directory is a bind mount from var/postgres, so the permissions of
# the mount point are set on the host side.
WORKDIR /var/lib/postgresql/data
