#!/bin/bash
set -e

# Start Apache2 in the background
service apache2 start

# Run the main command
exec "$@" 