# Logs Service
Implementation of a Logs Service with PHP 8.2, Symfony 7 and MySQL 8. 
The purpose of the service is to save a remote log file into the local database.

## Requirements
- git
- docker-compose

## Installation
Clone git repository:
```
git clone git@github.com:freelancerwebro/logs-service.git
```

In order to build the service, run the following command:
```
./deploy.sh
```

Run code analysis:
```
composer cs
```

Run tests:
```
composer test
```

API usage:
```
- GET `/log/count`: Fetch information about the log counter
- DELETE `/log`: Truncates the log database
```

Save logs into the database:
For importing logs the following command has to be run in the PHP container:
```
php bin/console app:save-logs
```