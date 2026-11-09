# The web server of AzeriCard.
#
# The base image is pinned by digest.

FROM nginx:stable-alpine@sha256:583b7f2fce1cdb8da5ad5c39ebd3c33b98b87d493190e548bdee1fa8c619c311

ADD ./server/nginx/default.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /var/www/html/be /var/www/html/fe
