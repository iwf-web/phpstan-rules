#!/usr/bin/env bash
set -uo pipefail

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )

EXIT_CODE=0

for build_dir in "$SCRIPT_DIR/build"/*/; do
    service="$(basename "$build_dir")"
    docker compose -f "$SCRIPT_DIR/run/compose.yml" up "$service" --remove-orphans || EXIT_CODE=$?
done

exit "$EXIT_CODE"
