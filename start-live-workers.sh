#!/bin/bash

LOG_FILE="/var/log/aggregated.log"

while true; do
    echo "Starting live log processing..."
    php bin/console app:process-live-logs "$LOG_FILE"
    sleep 2
done
