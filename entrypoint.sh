#!/usr/bin/env bash
set -euo pipefail

# Asegurar que solo quede un MPM activo para evitar:
# AH00534: Configuration error: More than one MPM loaded.
a2dismod mpm_event mpm_worker mpm_prefork >/dev/null 2>&1 || true
a2enmod mpm_prefork >/dev/null 2>&1 || true

# Diagnóstico breve
echo "--- Apache MPM loaded ---"
apachectl -M 2>/dev/null | grep -E 'mpm_' || true

exec apache2-foreground
