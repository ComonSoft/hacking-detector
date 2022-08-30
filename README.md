# hacking-detector
Fast and powerful PHP script, used in preference by a cron job, to detect files or directories which have been added or modified since the previous execution of the script. Sends an email report with all changes. Useful PHP script for detecting hacking, unauthorized files modification, web access violation. No database and no file storage, standalone PHP script.

## Requirements
PHP >= 5.3 

## Supported report languages
FRench
ENglish

## Installation
Change constructor parameters according to your configuration and just upload the file check4change.php in the directory of your choice in your web server.

### Security recommandation
We recommend you to create a folder outside the root of your website. This will make access to the script to potential hackers more difficult.

## Usage with cron
Simply run the script directly without parameters.
Note that some environments require to specify PHP on the command line.
```
php -f /my/path/check4change.php
php-cli -f /my/path/check4change.php
/usr/bin/php5 -f /my/path/check4change.php
```

## Configure
To configure the script according to your host, email, interval and report language, just change parameters to the following constructor and method.
```
$scan = new scanDirectory( dirname(__DIR__), 'FR');
$scan->MailReport( 'emailsender@mydomain.com', 'receiver@domain.com', 'Alert modified: www.yoursite.com');
```

###Samples
Scan from parent directory every 10 minutes
```
$scan = new scanDirectory( dirname(__DIR__), 'EN', 600);
```

Scan specific path every 5 minutes
```
$scan = new scanDirectory( '/home/www/mydir', 'FR', 300);
```

Scan specific path every 5 minutes and exclude directories
```
$scan = new scanDirectory( '/home/www/mydir', 'EN', 300, array('cache','temp'));
```

Scan specific path every 10 minutes and exclude files
```
$scan = new scanDirectory( '/home/www/mydir', 'EN', 600, null, array('log.txt','sitemap.xml'));
```
