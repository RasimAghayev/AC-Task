# The web server of AzeriCard.
#
# The base image is pinned by digest.

FROM nginx:stable-alpine@sha256:901e593c95dc85f5a729f5765516321366ef4ac401821431d408897e95287d0a

ADD ./server/nginx/default.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /var/www/html/be /var/www/html/fe
