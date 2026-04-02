#!/bin/bash

# Navigate to the project directory
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$DIR"

echo "Starting VelaNova services..."

# Check if it's already running
if [ -f .server.pid ]; then
    PID=$(cat .server.pid)
    if ps -p $PID > /dev/null; then
        echo "Server is already running with PID $PID."
        echo "To stop it, run: ./stop.sh"
        exit 0
    else
        echo "Found stale PID file. Cleaning up..."
        rm .server.pid
    fi
fi

# Ensure dependencies are installed
if [ ! -d "node_modules" ]; then
    echo "Installing dependencies..."
    npm install
fi

# Start the server in the background
echo "Starting Node.js server on port 4000..."
nohup node server.js > server.log 2>&1 &
PID=$!

# Save the Process ID
echo $PID > .server.pid

echo "✅ VelaNova server has been successfully started in the background!"
echo "PID: $PID"
echo "You can access the website at: http://localhost:4000"
echo "View logs using: tail -f server.log"
echo "To stop the server, run: ./stop.sh"
