# The web server of AzeriCard.
#
# The base image is pinned by digest.

FROM nginx:stable-alpine@sha256:970364228b1e4f932abc22d321932816782cca8a20b85a77552a7cfe9acfb3b5

ADD ./server/nginx/default.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /var/www/html/be /var/www/html/fe
