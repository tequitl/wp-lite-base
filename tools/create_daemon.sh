#!/bin/bash

# Script to create a systemd daemon for run.sh with custom name
# Usage: ./create_daemon.sh <daemon_name> [script_path] [user]

set -e  # Exit on any error

# Function to display usage
usage() {
    echo "Usage: $0 <daemon_name> [script_path] [user]"
    echo ""
    echo "Arguments:"
    echo "  daemon_name   : Name for the systemd service (required)"
    echo "  script_path   : Path to the script to run (default: ./run.sh)"
    echo "  user         : User to run the service as (default: current user)"
    echo ""
    echo "Examples:"
    echo "  $0 myapp"
    echo "  $0 webserver /home/user/scripts/run.sh"
    echo "  $0 worker /opt/myapp/run.sh ubuntu"
    exit 1
}

# Check if daemon name is provided
if [ $# -lt 1 ]; then
    echo "Error: Daemon name is required"
    usage
fi

DAEMON_NAME="$1"
SCRIPT_PATH="${2:-./run.sh}"
SERVICE_USER="${3:-$USER}"

# Convert relative path to absolute path
SCRIPT_PATH=$(realpath "$SCRIPT_PATH")

# Validate inputs
if [[ ! "$DAEMON_NAME" =~ ^[a-zA-Z0-9_-]+$ ]]; then
    echo "Error: Daemon name can only contain letters, numbers, hyphens, and underscores"
    exit 1
fi

if [ ! -f "$SCRIPT_PATH" ]; then
    echo "Error: Script file '$SCRIPT_PATH' does not exist"
    exit 1
fi

# Service file path
SERVICE_FILE="/etc/systemd/system/${DAEMON_NAME}.service"
LOG_FILE="/var/log/${DAEMON_NAME}.log"

echo "Creating systemd daemon: $DAEMON_NAME"
echo "Script path: $SCRIPT_PATH"
echo "Service user: $SERVICE_USER"
echo "Log file: $LOG_FILE"
echo ""

# Check if running as root for system service creation
if [ "$EUID" -ne 0 ]; then
    echo "This script needs to be run with sudo to create system services"
    echo "Please run: sudo $0 $*"
    exit 1
fi

# Create the systemd service file
echo "Creating service file: $SERVICE_FILE"
cat > "$SERVICE_FILE" << EOF
[Unit]
Description=$DAEMON_NAME Daemon
After=network.target
StartLimitIntervalSec=0

[Service]
Type=simple
Restart=always
RestartSec=5
User=$SERVICE_USER
ExecStart=/bin/bash $SCRIPT_PATH
StandardOutput=journal
StandardError=journal
SyslogIdentifier=$DAEMON_NAME
WorkingDirectory=$(dirname "$SCRIPT_PATH")

# Resource limits (optional - uncomment and adjust as needed)
# MemoryLimit=512M
# CPUQuota=50%

# Environment variables (optional - add as needed)
# Environment=NODE_ENV=production
# Environment=PATH=/usr/local/bin:/usr/bin:/bin

[Install]
WantedBy=multi-user.target
EOF

# Make sure the script is executable
echo "Making script executable..."
chmod +x "$SCRIPT_PATH"

# Create log file with proper permissions
echo "Creating log file: $LOG_FILE"
touch "$LOG_FILE"
chown "$SERVICE_USER:$SERVICE_USER" "$LOG_FILE"
chmod 644 "$LOG_FILE"

# Reload systemd daemon
echo "Reloading systemd daemon..."
systemctl daemon-reload

# Enable the service
echo "Enabling service to start on boot..."
systemctl enable "$DAEMON_NAME.service"

echo ""
echo "✅ Daemon '$DAEMON_NAME' created successfully!"
echo ""
echo "Management commands:"
echo "  Start service:    sudo systemctl start $DAEMON_NAME"
echo "  Stop service:     sudo systemctl stop $DAEMON_NAME"
echo "  Restart service:  sudo systemctl restart $DAEMON_NAME"
echo "  Check status:     sudo systemctl status $DAEMON_NAME"
echo "  View logs:        sudo journalctl -u $DAEMON_NAME -f"
echo "  View app logs:    sudo tail -f $LOG_FILE"
echo "  Disable service:  sudo systemctl disable $DAEMON_NAME"
echo ""

# Ask if user wants to start the service now
read -p "Do you want to start the service now? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Starting service..."
    systemctl start "$DAEMON_NAME.service"
    echo "Service started! Checking status..."
    systemctl status "$DAEMON_NAME.service" --no-pager
fi

echo ""
echo "Service file location: $SERVICE_FILE"
echo "To remove this daemon later, run:"
echo "  sudo systemctl stop $DAEMON_NAME"
echo "  sudo systemctl disable $DAEMON_NAME"
echo "  sudo rm $SERVICE_FILE"
echo "  sudo systemctl daemon-reload"
