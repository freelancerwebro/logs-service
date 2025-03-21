# 📄 Logs Service
The **Logs Service** is a Symfony-based API designed to efficiently process, store, and query large-scale application logs. 
It supports background processing, Redis-based caching, chunked ingestion, and pagination — built with performance and scalability in mind.

## 🚀 Features

- Bulk log ingestion with chunk-based processing
- Fast `/logs/count` endpoint using Redis cache
- Paginated `/logs` listing
- `TRUNCATE` support for clearing logs quickly
- Dockerized setup with a single deploy script

## 🛠 Tech Stack

- PHP 8.4
- Symfony 7.3
- MySQL 8
- Redis
- Docker & Docker Compose

## Requirements
- [git](https://github.com/git-guides/install-git)
- [docker-compose](https://docs.docker.com/compose/install/)
- [docker](https://www.docker.com/get-started/)

## Setup Instructions

### 1. Clone the Repository
```
git clone git@github.com:freelancerwebro/logs-service.git
cd logs-service
```

### 2. Deploy the Project
```
./deploy.sh
```


API usage:
```
- GET `/logs?page={page}&limit={limit}`: Returns a paginated list of logs.
- GET `/logs/count`: Returns the total number of logs
- DELETE `/logs`: Clears all logs from the database
```

🧪 Log Processing & Generation

▶️ Generate Fake Logs (for testing)
You can generate synthetic logs using the built-in command:
```
./start-fake-logs-generation.sh
```
This command will create a .log file (e.g. /var/log/aggregated.log) with sample entries for testing purposes.

-------------------

To process logs from a file in chunks:
```
./start-batch-workers.sh
```
-------------------
To handle new logs as they arrive (tail -F).
```
./start-live-workers.sh
```

🔁 Refresh Cached Count

You can manually refresh the /logs/count Redis cache:
```
php bin/console app:refresh-logs-count
```
or add this command to a Cron Job (Every 10 Min)
```
*/10 * * * * php /var/www/html/bin/console app:refresh-logs-count
```
