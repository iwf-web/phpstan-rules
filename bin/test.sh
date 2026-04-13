#!/usr/bin/env bash
# Usage: bin/test.sh [VERSION]
#
# Runs PHPStan and PHPUnit via "composer test".
#
# VERSION  Optional PHP version to use (e.g. "8.3" or "83").
#          If a matching local PHP binary is found it is used directly.
#          If not, the matching Docker service is used.
#          If omitted, uses the local "php" binary if available,
#          otherwise runs all Docker services sequentially.
set -euo pipefail

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
# shellcheck source=_env.sh
source "$SCRIPT_DIR/_env.sh"

VERSION="${1:-}"

run_local() {
    local bin="$1"
    echo "==> PHP $($bin -r 'echo PHP_VERSION;') (local)"
    cd "$PROJECT_DIR"
    [[ -d vendor ]] || "$bin" "$(command -v composer)" install
    "$bin" "$(command -v composer)" test
}

run_docker() {
    local service="${1:-}"
    if [[ -n "$service" ]]; then
        echo "==> PHP ${service} (Docker)"
        docker compose -f "$COMPOSE_FILE" up "php${service//./}" --remove-orphans
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
