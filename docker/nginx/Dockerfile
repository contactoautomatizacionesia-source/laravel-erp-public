# =============================================================================
# Nginx image — amazingsite-erp
# Contiene archivos estáticos (CSS, JS, imágenes del tema) y config de nginx.
# Los archivos dinámicos (uploads, carrier, invoice) se montan via STORAGE_BASE.
# =============================================================================
FROM nginx:1.27-alpine
LABEL org.opencontainers.image.source=https://github.com/DaruinHerreraIgniweb/amazingsite-erp

# Config de nginx (baked en la imagen; en dev se sobreescribe via bind mount)
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Archivos estáticos del frontend/backend
# .dockerignore excluye uploads/*, carrier/*, invoice/*, database-backup/*
COPY --chown=nginx:nginx public/ /var/www/public/

# index.php placeholder: necesario para que la directiva `index` de nginx
# genere el redirect interno a la location PHP-FPM. Nginx nunca lo ejecuta.
COPY index.php /var/www/index.php

# Service Worker (debe servirse desde la raíz)
COPY serviceworker.js /var/www/serviceworker.js

# Páginas de error estáticas
COPY 404.html 502.html /var/www/

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
    CMD wget -qO- http://localhost/health || exit 1
