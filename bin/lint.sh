#!/usr/bin/env bash
# Usage: bin/lint.sh
#
# Runs PHP CS Fixer via "composer lint" using the local PHP binary if available,
# otherwise falls back to the default (lowest) Docker service.
set -euo pipefail

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
# shellcheck source=_env.sh
source "$SCRIPT_DIR/_env.sh"

run_composer lint
