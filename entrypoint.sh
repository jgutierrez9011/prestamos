#!/usr/bin/env bash
set -euo pipefail

# Opcional: verificación (no toca nada)
echo "--- Apache MPM loaded ---"
apachectl -M 2>/dev/null | grep -E 'mpm_' || true

exec apache2-foreground
