#!/bin/bash

# This is a workaround for the following issue:
# https://github.com/lando/lando/issues/1197#issuecomment-429733044

mkdir -p /var/www/.composer/vendor/bin

# TODO: Is $PHP_VERSION always e.g. 5.3.29 at this point, or can it also be
# just 5.3?
PHP_VERSION="$PHP_VERSION.0"

if [[ "$PHP_VERSION" = 5.[23].* ]]; then
	# WP-CLI 2.x not supported
	wget \
		https://github.com/wp-cli/wp-cli/releases/download/v1.5.0/wp-cli-1.5.0.phar \
		-O /var/www/.composer/vendor/bin/wp
else
	wget \
		https://github.com/wp-cli/wp-cli/releases/download/v2.0.1/wp-cli-2.0.1.phar \
		-O /var/www/.composer/vendor/bin/wp
fi
chmod +x /var/www/.composer/vendor/bin/wp
wp --version
exit $?
