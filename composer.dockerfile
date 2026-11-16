# The image of the composer of AzeriCard.
#
# The service of the compose stack runs the composer, so it builds from this
# file instead of pointing at a registry image (R91).

FROM composer:2@sha256:0c8e663f14c94d07b3c45b175c51f5a14718508f21fe4dd7e54f09488dd0ed71

WORKDIR /var/www/html/be
