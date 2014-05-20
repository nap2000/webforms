#!/bin/sh

# webforms
#cp public/build/js/webform-combined.min.js webforms/js/libs


# Create tar file and deploy
tar -czf webforms.tgz webforms fonts lib
tar -xzf webforms.tgz -C  /Library/WebServer/Documents
sudo apachectl restart
cp webforms.tgz ../../deploy/
rm webforms.tgz
