#!/usr/bin/env bash
# Usage: bin/composer.sh <composer-args...>
#
# Runs Composer with the given arguments.
set -euo pipefail

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
# shellcheck source=_env.sh
source "$SCRIPT_DIR/_env.sh"

run_composer "$@"
