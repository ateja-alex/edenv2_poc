FROM nginx:alpine

ARG UID=1000
ARG GID=1000

# On installe shadow pour avoir les commandes usermod/groupmod
# Et on change l'ID de l'utilisateur 'nginx' pour qu'il corresponde au vôtre
RUN apk add --no-cache shadow \
    && groupmod -g ${GID} nginx \
    && usermod -u ${UID} -g nginx nginx

# Optionnel : Si Nginx n'arrive pas à écrire ses logs/cache, on lui redonne les droits
# RUN chown -R nginx:nginx /var/cache/nginx /var/log/nginx /etc/nginx/conf.d
