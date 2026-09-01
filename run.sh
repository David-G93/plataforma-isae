#!/usr/bin/env bash

set -euo pipefail

php_binary="${ISAE_PHP_BINARY:-${LOCALAPPDATA//\\//}/Programs/PHP/php.exe}"
composer_phar="${ISAE_COMPOSER_PHAR:-$(dirname "$php_binary")/composer.phar}"

if [[ ! -f "$php_binary" ]]; then
    echo "No se encontro PHP en: $php_binary" >&2
    echo "Defini ISAE_PHP_BINARY con la ruta a php.exe e intenta de nuevo." >&2
    exit 1
fi

if [[ ! -f "$composer_phar" ]]; then
    echo "No se encontro Composer en: $composer_phar" >&2
    echo "Defini ISAE_COMPOSER_PHAR con la ruta a composer.phar e intenta de nuevo." >&2
    exit 1
fi

exec "$php_binary" "$composer_phar" "$@"
