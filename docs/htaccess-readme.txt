This is for Apache users to utilize the given .htaccess file in RivetTracker.

[1] Make sure you have the mod_rewrite module enabled.
[2] For /local/path/to/rivettracker, change this to your absolute local path
    to your RivetTracker install.
    [a] Unix servers: /local/path/to/rivettracker
    [b] Windows servers: <drive letter>:\local\path\to\rivettracker


For Apache 2.0.x and 2.2.x installs, put this in your httpd.conf file:

<Directory "/local/path/to/rivettracker">
Options -Indexes +FollowSymLinks
RewriteEngine On
AllowOverride All
Order allow,deny
Allow from all
</Directory>


For Apache 2.4.x installs, put this in your httpd.conf file:

<Directory "/local/path/to/rivettracker">
Options -Indexes +FollowSymLinks
RewriteEngine On
AllowOverride All
Require all granted
</Directory>


You could also change AllowOverride None to AllowOverride All in the main
httpd.conf and uncomment the first two lines in the htaccess file, but that
also searches for .htaccess in all other subdirectories as well.


For nginx users, it turns out to be a bit different. Read this:
http://wiki.nginx.org/HttpRewriteModule