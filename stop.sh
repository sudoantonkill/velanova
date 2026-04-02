#!/bin/bash

# Navigate to the project directory
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$DIR"

echo "Stopping VelaNova services..."

if [ -f .server.pid ]; then
    PID=$(cat .server.pid)
    if ps -p $PID > /dev/null; then
        echo "Found running Node.js server with PID $PID. Stopping it..."
        kill -9 $PID
        echo "Server successfully stopped."
    else
        echo "Server process (PID $PID) is not running."
    fi
    rm .server.pid
else
    # Fallback to general process kill if the PID file doesn't exist
    echo "No .server.pid file found. Looking for matching 'node server.js' processes..."
    # Find process and kill
    PIDS=$(pgrep -f "node server.js")
    
    if [ -n "$PIDS" ]; then
        echo "Found running server processes. Stopping them..."
        for p in $PIDS; do
            kill -9 $p 2>/dev/null
            echo "Killed process $p"
        done
    else
        echo "No 'node server.js' processes found."
    fi
fi

echo "✅ All services have been stopped."
