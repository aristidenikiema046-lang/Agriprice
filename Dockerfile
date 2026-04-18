# On utilise une image PHP officielle avec Apache
FROM php:8.2-apache

# Installation des extensions PHP nécessaires pour MySQL
RUN docker-php-ext-install pdo pdo_mysql

# On active le module de réécriture d'Apache (utile pour les URL propres)
RUN a2enmod rewrite

# On copie tout ton code dans le dossier du serveur web
COPY . /var/www/html/

# On donne les droits d'accès au serveur
RUN chown -R www-data:www-data /var/www/html/

# On expose le port 80 (standard pour le web)
EXPOSE 80