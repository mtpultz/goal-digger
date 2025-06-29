#!/bin/bash

TEMPLATE_FILE=".cursor/mcp.template.json"
TARGET_FILE=".cursor/mcp.json"

if [ -z "$GITHUB_MCP_ACCESS_TOKEN" ]; then
  echo "❌ GITHUB_MCP_ACCESS_TOKEN is not set."
  echo "Set it using: export GITHUB_MCP_ACCESS_TOKEN=github_pat_..."
  exit 1
fi

if [ ! -f "$TEMPLATE_FILE" ]; then
  echo "❌ Missing template file: $TEMPLATE_FILE"
  exit 1
fi

# Make the .cursor directory if it doesn't exist
mkdir -p .cursor

# Replace the token placeholder with the actual token
sed "s|__TOKEN__|${GITHUB_MCP_ACCESS_TOKEN}|" "$TEMPLATE_FILE" > "$TARGET_FILE"

echo "✅ Generated: $TARGET_FILE"
