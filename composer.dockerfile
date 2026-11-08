# The image of the composer of AzeriCard.
#
# The service of the compose stack runs the composer, so it builds from this
# file instead of pointing at a registry image (R91).

FROM composer:2@sha256:8de2184c6de224f4689e8f80ed5f5edaf0030b6b8b7839a22a2bcb6722ac87d0

WORKDIR /var/www/html/be
