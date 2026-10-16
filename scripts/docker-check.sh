#!/bin/sh
set -e
find . -name '*.php' -not -path './release/*' | while read -r file; do
  php -l "$file"
done
php tests/run.php
php scripts/make-pot.php
