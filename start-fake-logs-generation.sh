#!/bin/bash

LOG_FILE="/var/log/aggregated.log"
ROWS_NO=1000000

echo "Starting log generation..."
php bin/console app:generate-logs "$LOG_FILE" "$ROWS_NO"
