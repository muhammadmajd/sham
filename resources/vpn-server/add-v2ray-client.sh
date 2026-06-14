#add-v2ray-client.sh (server script)

#Place this on your VPS at /usr/local/bin/add-v2ray-client.sh. Make it executable (chmod +x).

#!/usr/bin/env bash
# add-v2ray-client.sh
# Usage: add-v2ray-client.sh UUID "optional comment"
# Appends a client object to the first inbound's clients array and restarts xray.
# Creates a timestamped backup before changes.

set -euo pipefail

UUID="${1:-}"
COMMENT="${2:-added-by-api}"

if [[ -z "$UUID" ]]; then
  echo "Usage: $0 <UUID> [comment]"
  exit 2
fi

CFG="/etc/xray/config.json"
BACKUP_DIR="/var/backups/xray"
TMP="/tmp/xray_config.$$"

mkdir -p "$BACKUP_DIR"

# backup
# Security notes:
# The script creates backups in /var/backups/xray.
# It uses jq for safe JSON editing. Install via apt install jq (Debian/Ubuntu).
# Test script manually before automating.
ts=$(date +%Y%m%d%H%M%S)
cp "$CFG" "${BACKUP_DIR}/config.json.bak.${ts}"

# Add client safely using jq
# Ensures .inbounds[0].settings.clients exists and is an array
jq --arg id "$UUID" --arg comment "$COMMENT" '
  .inbounds[0].settings.clients |= (. // []) + [ { "id": $id, "flow": "", "comment": $comment } ]
' "$CFG" > "$TMP" && mv "$TMP" "$CFG"

# Validate JSON
if ! jq empty "$CFG"; then
  echo "Resulting JSON invalid! Restoring backup."
  cp "${BACKUP_DIR}/config.json.bak.${ts}" "$CFG"
  exit 3
fi

# Restart Xray (adjust service name if different)
systemctl restart xray

echo "Added $UUID to $CFG and restarted xray. Backup at ${BACKUP_DIR}/config.json.bak.${ts}"
exit 0