# The image of the composer of AzeriCard.
#
# The service of the compose stack runs the composer, so it builds from this
# file instead of pointing at a registry image (R91).

FROM composer:2@sha256:5b2137f112f939a92788ceb5e55f54859d9dbf467a44037cf7760bdda949f365

WORKDIR /var/www/html/be
