#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

VERSION="${1:-}"

# Returns the path of the PHP binary matching the given version string (e.g. "8.3",
# "83"). With no argument, returns the path of the default "php" binary.
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

run_local() {
    local bin="$1"
    echo "==> PHP $($bin -r 'echo PHP_VERSION;') (local)"
    cd "$PROJECT_DIR"
    [[ -d vendor ]] || "$bin" "$(which composer)" install
    "$bin" vendor/bin/phpstan --memory-limit=256M
    "$bin" vendor/bin/phpunit
}

run_docker() {
    local service="${1:-}"
    if [[ -n "$service" ]]; then
        echo "==> PHP ${service} (Docker)"
        docker compose -f "$PROJECT_DIR/docker/run/compose.yml" up "php${service//./}" --remove-orphans
    else
        exec "$PROJECT_DIR/docker/test.sh"
    fi
}

if bin=$(find_php_bin "$VERSION"); then
    run_local "$bin"
elif [[ -n "$VERSION" ]]; then
    echo "==> PHP ${VERSION} not available locally, falling back to Docker"
    run_docker "$VERSION"
else
    echo "==> No local PHP found, running all versions via Docker"
    run_docker
fi
