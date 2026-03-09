#!/usr/bin/env bash
# Shared environment — source this file after SCRIPT_DIR is set.

PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
COMPOSE_FILE="$PROJECT_DIR/docker/run/compose.yml"

# Prints the name of the oldest (lowest-sorted) service defined in compose.yml.
default_service() {
    docker compose -f "$COMPOSE_FILE" config --services | sort | head -1
}

# Runs composer with the given arguments
run_composer() {
    local service
    service=$(default_service)
    echo "==> composer $* (Docker ${service})"
    docker compose -f "$COMPOSE_FILE" run --rm "$service" composer "$@"
}
