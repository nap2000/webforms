#!/bin/sh

rm -rf webforms/src/lib/enketo-core

# The following lines add the code from the git subproject enketo-core referenced by webforms
#cp -r public/lib/enketo-core webforms/src/lib

# The following line adds the code directly from the local enketo-core project
cp -r ../enketo-core webforms/src/lib

# cleanup
rm -rf webforms/src/lib/enketo-core/node_modules
rm -rf webforms/src/lib/enketo-core/.git
