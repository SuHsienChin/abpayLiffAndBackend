#<VirtualHost *:80>
#  ServerName localhost
#  ServerAlias localhost
#  DocumentRoot "${INSTALL_DIR}/www/abpay-system/public"
#  <Directory "${INSTALL_DIR}/www/abpay-system/public">
#    Options +Indexes +Includes +FollowSymLinks +MultiViews
#    Require all granted
#  </Directory>
#</VirtualHost>



<VirtualHost *:80>
  ServerName localhost
  ServerAlias localhost
  DocumentRoot "${INSTALL_DIR}/www"
  <Directory "${INSTALL_DIR}/www">
    Options +Indexes +Includes +FollowSymLinks +MultiViews
    Header set Access-Control-Allow-Origin "*" 
    Require all granted
  </Directory>
</VirtualHost>


