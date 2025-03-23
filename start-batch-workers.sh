#!/bin/bash

LOG_FILE="/var/log/aggregated.log"
TOTAL_LINES=$(wc -l < "$LOG_FILE")
CHUNK_SIZE=100000
START=1
echo 'Total lines: ' $TOTAL_LINES ' Chunk size: ' $CHUNK_SIZE ' Start: ' $START

for (( START=1; START<=TOTAL_LINES; START+=CHUNK_SIZE ))
do
    END=$((START + CHUNK_SIZE - 1))
    echo "Processing lines from $START to $END..."
    echo "--> php bin/console app:process-logs $LOG_FILE $START $END &" .
    echo ''
    php bin/console app:process-logs $LOG_FILE $START $END &
done
