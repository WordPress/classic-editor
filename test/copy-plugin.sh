#!/bin/bash

# exit on error
set -e

cd "$(dirname "$0")"
cd ..

rm -rf test/site/wp-content/plugins/classic-editor/
mkdir -p test/site/wp-content/plugins/classic-editor/
cp -var *.php js/ css/ \
    test/site/wp-content/plugins/classic-editor/
