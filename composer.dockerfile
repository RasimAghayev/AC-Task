# The image of the composer of AzeriCard.
#
# The service of the compose stack runs the composer, so it builds from this
# file instead of pointing at a registry image (R91).

FROM composer:2@sha256:ca11f643d7791ebdd90a34fc20caf11380d434007ae539a9ea75cd9212e13dca

WORKDIR /var/www/html/be
