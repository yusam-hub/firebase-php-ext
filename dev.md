#### testing php74

    docker exec -it dev-php74 sh -c "cd /var/www/php74/yusam-hub/helpers && exec bash"

    docker exec -it dev-php74 sh -c "cd /var/www/php74/yusam-hub/helpers && composer update"
    docker exec -it dev-php74 sh -c "cd /var/www/php74/yusam-hub/helpers && composer install"
    docker exec -it dev-php74 sh -c "cd /var/www/php74/yusam-hub/helpers && sh phpunit"
    docker exec -it dev-php74 sh -c "cd /var/www/php74/yusam-hub/helpers && git status"
    docker exec -it dev-php74 sh -c "cd /var/www/php74/yusam-hub/helpers && git pull"



    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/firebase-php-ext/bin && php reactphp.php"
    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/firebase-php-ext/bin && php test-write.php"
    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/firebase-php-ext/bin && php test-read.php"