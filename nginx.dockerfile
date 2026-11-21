# The web server of AzeriCard.
#
# The base image is pinned by digest.

FROM nginx:stable-alpine@sha256:caf4e1e032a20f9c3c5e28fc7f25ffd768ad8677b4975c54193075997b3a97e3

ADD ./server/nginx/default.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /var/www/html/be /var/www/html/fe
