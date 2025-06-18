#!/bin/bash

# Get the absolute path of the cron.php file
SCRIPT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/cron.php"

# Create the CRON job command
CRON_CMD="0 * * * * php $SCRIPT_PATH"

# Check if the CRON job already exists
EXISTING_CRON=$(crontab -l 2>/dev/null | grep -F "$SCRIPT_PATH")

if [ -z "$EXISTING_CRON" ]; then
    # Add the new CRON job
    (crontab -l 2>/dev/null; echo "$CRON_CMD") | crontab -
    echo "CRON job has been set up successfully!"
else
    echo "CRON job already exists."
fi

# Make the script executable
chmod +x "$SCRIPT_PATH"

echo "Setup complete. The task reminder system will run every hour." 