#!/usr/bin/env bash
# Shared environment — source this file after SCRIPT_DIR is set.

PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
COMPOSE_FILE="$PROJECT_DIR/docker/run/compose.yml"

# Prints the path of the PHP binary matching the given version string (e.g. "8.3", "83").
# With no argument, returns the path of the default "php" binary.
find_php_bin() {
    local v="${1:-}"
    if [[ -z "$v" ]]; then
        command -v php 2>/dev/null || return 1
    else
        command -v "php${v}" 2>/dev/null \
            || command -v "php${v//./}" 2>/dev/null \
            || return 1
    fi
}

# Prints the name of the oldest (lowest-sorted) service defined in compose.yml.
default_service() {
    docker compose -f "$COMPOSE_FILE" config --services | sort | head -1
}

# Runs composer with the given arguments using local PHP if available,
# otherwise falls back to the default Docker service.
run_composer() {
    if bin=$(find_php_bin); then
        echo "==> composer $* (PHP $($bin -r 'echo PHP_VERSION;'), local)"
        cd "$PROJECT_DIR"
        "$bin" "$(command -v composer)" "$@"
    else
        local service
        service=$(default_service)
        echo "==> composer $* (Docker ${service})"
        docker compose -f "$COMPOSE_FILE" run --rm "$service" composer "$@"
    fi
}
