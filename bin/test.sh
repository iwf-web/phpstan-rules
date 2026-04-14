#!/usr/bin/env bash
# Usage: bin/test.sh [--coverage] [VERSION]
#
# Runs PHPStan and PHPUnit via "composer test".
#
# --coverage  Generate Clover coverage report (coverage.xml). Uses php83.
# VERSION     Optional PHP version (e.g. "8.3" or "83").
#             Omit to run all Docker services sequentially.
set -euo pipefail

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
# shellcheck source=_env.sh
source "$SCRIPT_DIR/_env.sh"

COVERAGE=false
VERSION=""

for arg in "$@"; do
    case "$arg" in
        --coverage) COVERAGE=true ;;
        *) VERSION="$arg" ;;
    esac
done

run_docker() {
    local service="${1:-}"
    if [[ -n "$service" ]]; then
        echo "==> PHP ${service} (Docker)"
        docker compose -f "$COMPOSE_FILE" up "php${service//./}" --remove-orphans
    else
        exec "$PROJECT_DIR/docker/test.sh"
    fi
}

if [[ "$COVERAGE" == "true" ]]; then
    service="php${VERSION//./}"
    [[ -z "$VERSION" ]] && service=$(default_service)
    echo "==> Install (${service})"
    docker compose -f "$COMPOSE_FILE" run --rm \
        -e COMPOSER_MEMORY_LIMIT=-1 \
        "$service" composer update --no-interaction --prefer-dist
    echo "==> Coverage (${service})"
    docker compose -f "$COMPOSE_FILE" run --rm \
        -e XDEBUG_MODE=off \
        "$service" composer phpunit:coverage
else
    run_docker "$VERSION"
fi
