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




# Virtual Hosts
#
#<VirtualHost _default_:80>
#  ServerName localhost
#  ServerAlias localhost
#  DocumentRoot "${INSTALL_DIR}/www"
#  <Directory "${INSTALL_DIR}/www/">
#    Options +Indexes +Includes +FollowSymLinks +MultiViews
#    AllowOverride All
#    Require local
#  </Directory>
#</VirtualHost>


<VirtualHost _default_:80>
  ServerName localhost
  ServerAlias localhost
  DocumentRoot "D:/www/abpay"
  <Directory "D:/www/abpay">
    Options +Indexes +Includes +FollowSymLinks +MultiViews
    AllowOverride All
    Require local
  </Directory>
</VirtualHost>

