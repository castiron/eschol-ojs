#!/bin/bash

set -e

# source /scripts/helpers.sh
# source /scripts/composer.sh
# source /scripts/database.sh
# source /scripts/plugins.sh

# This should only run the first time a project is set up and dependencies aren't in place yet. In all other cases
# the developer will be managing dependencies.
# [ ! -d "/var/www/html/vendor" ] && install_composer_dependencies

# setup_database &
# SETUP_PID=$!
# DEPENDENDIES_PID=$!
#
# wait $SETUP_PID
# wait $DEPENDENCIES_PID
# activate_plugins
#
# wait
#
h2 "✅  Visit http://localhost:8080 to start using OJS."

# Make sure PHP FPM has a log dir
[ ! -d "/var/www/html/log" ] && mkdir -p /var/www/html/log

# Start php-fpm
exec "$@"
