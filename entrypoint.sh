#!/usr/bin/env bash
set -euo pipefail

echo "--- Fixing Apache MPM ---"
a2dismod mpm_event 2>/dev/null || true
a2dismod mpm_worker 2>/dev/null || true
a2dismod mpm_prefork 2>/dev/null || true

rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf

a2enmod mpm_prefork

echo "--- MPM symlinks ---"
ls -la /etc/apache2/mods-enabled | grep -E 'mpm_' || true

echo "--- apachectl -M (mpm) ---"
apachectl -M | grep -E 'mpm_' || true

exec apache2-foreground