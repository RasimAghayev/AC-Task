# The image of the composer of AzeriCard.
#
# The service of the compose stack runs the composer, so it builds from this
# file instead of pointing at a registry image (R91).

FROM composer:2@sha256:af98f42dfff7c68ba8d53c2164fd9fde1087b7d449514baa38c418b1f6bc4bac

WORKDIR /var/www/html/be
