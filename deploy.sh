echo 'Prepare config files...'
cp phpunit.xml.dist phpunit.xml
cp phpstan.dist.neon phpstan.neon
cp behat.yml.dist behat.yml
cp .env.example .env
cp .env.test.example .env.test

echo 'Building containers...'
docker-compose build --no-cache
docker-compose up -d

echo 'Installing composer...'
docker exec -it logs-service-app composer install --prefer-dist --no-progress --no-interaction

echo 'Creating database...'
docker exec -it logs-service-app php bin/console doctrine:database:create --if-not-exists --no-interaction

echo 'Running migrations...'
docker exec -it logs-service-app php bin/console doctrine:migrations:migrate --no-interaction
docker exec -it logs-service-app php bin/console cache:clear --no-interaction

echo 'Generate swagger documentation...'
docker exec -it logs-service-app php bin/console nelmio:apidoc:dump --format=json > swagger.json