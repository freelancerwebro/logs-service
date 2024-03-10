docker-compose up -d

echo 'Creating database:'
docker exec -it php php bin/console doctrine:database:create --if-not-exists --no-interaction

echo 'Run migrations:'
docker exec -it php php bin/console doctrine:migrations:migrate --no-interaction
docker exec -it php php bin/console cache:clear --no-interaction

