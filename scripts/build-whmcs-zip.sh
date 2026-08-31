#!/usr/bin/env bash
# Build WHMCS-compatible release ZIP for RC & LB Tools (resellerclubmods_tools).
# Extract the ZIP to your WHMCS root (ROOTDIR) — paths inside mirror WHMCS layout.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

VERSION="$(grep -m1 '^\$softversion' modules/addons/resellerclubmods_tools/incs/functions.php | sed 's/.*"\([^"]*\)".*/\1/')"
RELEASEDATE="$(grep -m1 '^\$releasedate' modules/addons/resellerclubmods_tools/incs/functions.php | sed 's/.*"\([^"]*\)".*/\1/')"

if [[ -z "$VERSION" ]]; then
  echo "error: could not read \$softversion from incs/functions.php" >&2
  exit 1
fi

ZIP_NAME="resellerclubmods_tools-v${VERSION}-whmcs.zip"
DIST_DIR="$ROOT/dist"
STAGING="$DIST_DIR/.staging"
OUT="$DIST_DIR/$ZIP_NAME"

rm -rf "$STAGING"
mkdir -p "$STAGING/modules/addons" "$STAGING/includes/hooks" "$STAGING/widgets" "$DIST_DIR"

# WHMCS install tree
cp -R modules/addons/resellerclubmods_tools "$STAGING/modules/addons/"
cp includes/hooks/resellerclubmods_*.php "$STAGING/includes/hooks/"
cp widgets/domainpricelist.php "$STAGING/widgets/"

# Root-level release docs (MIT OSS; no vendor EULA in distribution zip)
cp LICENSE "$STAGING/LICENSE"
cp README.txt "$STAGING/README.txt"

cat > "$STAGING/INSTALL.txt" <<EOF
RC & LB Tools v${VERSION} — WHMCS Install (MIT open source)
Release date: ${RELEASEDATE}

Extract this ZIP to your WHMCS root directory (ROOTDIR). The archive paths
mirror WHMCS layout:

  ROOTDIR/modules/addons/resellerclubmods_tools/
  ROOTDIR/includes/hooks/resellerclubmods_*.php   (10 files)
  ROOTDIR/widgets/domainpricelist.php

Fresh install:
  1. Back up your WHMCS installation.
  2. Upload/extract so the folders above land under ROOTDIR.
  3. In WHMCS Admin: Setup > Addon Modules > RC & LB Tools v2 > Activate.
  4. Configure at least one reseller account (API URL, User ID, API key, name).
  5. Enable desired hooks and the funds widget in addon settings.

Upgrade from ionCube or prior OSS build (IMPORTANT):
  1. Back up WHMCS files and database.
  2. Do NOT deactivate the addon — deactivation can drop module tables/settings.
  3. Overwrite the paths above in place (same module ID: resellerclubmods_tools).
  4. Confirm version ${VERSION} in Setup > Addon Modules.
  5. Smoke test: Admin Home API status, funds widget (if enabled), one hook path.
  6. ionCube Loader is NOT required for this OSS build.

Requirements: PHP 7.4–8.4, WHMCS 8.x / 9.x (see docs/README.md).

Requirements and changelog (external docs):
  https://www.resellerclub-mods.com/whmcs/resellerclub-tools-docs.php

License: MIT (see LICENSE). No runtime license key or vendor phone-home.
EOF

# Strip dev artifacts if present
find "$STAGING" -name '.DS_Store' -delete
find "$STAGING" -name '._*' -delete

rm -f "$OUT"
(
  cd "$STAGING"
  zip -rq "$OUT" .
)

rm -rf "$STAGING"

BYTES="$(wc -c < "$OUT" | tr -d ' ')"
echo "Built: $OUT ($BYTES bytes)"
echo "Version: $VERSION ($RELEASEDATE)"
