#!/bin/bash

LOG_FILE="/var/log/aggregated.log"

echo "Starting live log processing..."
php bin/console app:process-live-logs "$LOG_FILE"


