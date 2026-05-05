#!/usr/bin/env bash
set -uo pipefail

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
COMPOSE_FILE="$SCRIPT_DIR/run/compose.yml"

EXIT_CODE=0
declare -a RESULTS=()

for service in $(docker compose --ansi never -f "$COMPOSE_FILE" config --services | sort); do
    printf '\n===== %s =====\n' "$service"
    rc=0
    docker compose --ansi never -f "$COMPOSE_FILE" run --rm -T "$service" || rc=$?
    if [[ $rc -eq 0 ]]; then
        printf '===== %s OK =====\n' "$service"
        RESULTS+=("$service OK")
    else
        printf '===== %s FAIL (exit %d) =====\n' "$service" "$rc"
        RESULTS+=("$service FAIL($rc)")
        EXIT_CODE=$rc
    fi
done

printf '\n===== SUMMARY =====\n'
for line in "${RESULTS[@]}"; do
    printf '%s\n' "$line"
done

exit "$EXIT_CODE"
