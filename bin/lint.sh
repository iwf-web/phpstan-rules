#!/usr/bin/env bash
# Usage: bin/lint.sh
#
# Runs linting scripts via "composer lint"
set -euo pipefail

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
# shellcheck source=_env.sh
source "$SCRIPT_DIR/_env.sh"

run_composer lint
