FROM dimaip/docker-neos-alpine:latest
ENV PHP_TIMEZONE=Europe/Moscow
ENV REPOSITORY_URL=https://github.com/psmb/PsmbDistr
RUN /provision-neos.sh
