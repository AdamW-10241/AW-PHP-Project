#!/bin/bash
set -e

# Start Apache in the background
apache2-foreground &

# Keep the container running
tail -f /var/log/apache2/error.log 