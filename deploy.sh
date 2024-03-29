echo 'Prepare config files...'
cp phpunit.xml.dist phpunit.xml
cp phpstan.dist.neon phpstan.neon
cp behat.yml.dist behat.yml

echo 'Building containers...'
docker-compose up -d

echo 'Installing composer...'
docker exec -it php composer install --prefer-dist --no-progress --no-interaction

echo 'Creating database...'
docker exec -it php php bin/console doctrine:database:create --if-not-exists --no-interaction

echo 'Running migrations...'
docker exec -it php php bin/console doctrine:migrations:migrate --no-interaction
docker exec -it php php bin/console cache:clear --no-interaction

