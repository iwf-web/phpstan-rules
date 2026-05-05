#!/usr/bin/env bash
# Usage: bin/build.sh [VERSION]
#
# Rebuilds Docker images for test services.
# VERSION  Optional PHP version (e.g. "8.3" or "83"). Omit to rebuild all.
set -euo pipefail

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
# shellcheck source=_env.sh
source "$SCRIPT_DIR/_env.sh"

VERSION="${1:-}"

if [[ -n "$VERSION" ]]; then
    svc="php${VERSION//./}"
    printf '===== build %s =====\n' "$svc"
    docker compose --ansi never -f "$COMPOSE_FILE" build "$svc"
else
    printf '===== build all =====\n'
    docker compose --ansi never -f "$COMPOSE_FILE" build
fi
