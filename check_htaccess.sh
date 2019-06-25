#!/bin/bash 

MAGE_DIR="$1"
HTACCESS_FILE="$MAGE_DIR/.htaccess"

# Find .htaccess
if [ -f "$HTACCESS_FILE" ]; then

	# Test .htaccess contains "setenvif HTTPS On HTTPS=on"
	# Note SetEnvIf may be camelcase but the rest must be that exact case
	# Also note must let first grep output matched line(s) but prevent second
	# grep from outputting, just look at EXIT_CODE (hence -q)
	if grep -iF "setenvif" "$HTACCESS_FILE" | grep -qF "HTTPS On HTTPS=on"; then
	
		echo ".htaccess checked"

	else
	
		echo """

WARNING:

The .htaccess file does not contain the following Apache directive required for
Magento to work behind a proxy which sets the HTTPS Apache server variable to
'On' rather than 'on'. Magento does a case sensitive comparison to determine
whether HTTPS was used for the request from the usewr to the proxy:

SetEnvIf HTTPS On HTTPS=on
"""
	fi
	
else

	echo "Cannot find $MAGEDIR/.htaccess to verify"

fi
