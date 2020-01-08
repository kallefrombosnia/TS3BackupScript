<?php

$login_name = 'serveradmin';  				// query login info
$login_password = '5KMTIAXd'; 				// =||=
$ip = 'localhost';            				// ex. 127.0.0.1/ 254.13.121.12 
$query_port = '10011';		  			// default 10011
$virtualserver_port= array('9987'); 			// All virtual server ports to do snapshot in
$bot_name = 'Snapshot MaStEr';      			// bot name
$snapdir = 'backup';					// Where is snapshot stored (include directorys only)
$snapname = 'TS3Snapshot';				// Name of the file
$mode = 'full';						// snapshot -> basic snapshot without icons/ avatars, files-> all files 
							// from ts3 dir without snap, full-> files + snap

/* For this methods you can use file_get_contents() [local] or ftp [remote] NOTE: You need ftp account */
$method = 'local';					// local-> uses php file_get_contents() to save ts3 dir, ftp-> connects  
							// via ftp and downloads ts3 dir
							// Note: ftp wont downloads files in write mode (eg sqlitedb, log.txt)

$ts3dir = 'teamspeak3-server_win32'; 			// ftp path of ts3 folder (configure ftp server to use this- no support)
$snapdirreal = ('C:/xampp/htdocs/backup/');
$ignoredfiles = array('doc','serverquerydocs','sql','redist','tsdns','.ts3server_license_accepted','changelog.txt','license.txt','query_ip_blacklist.txt','query_ip_whitelist.txt','ts3_ssh.dll','ts3db_mariadb.dll','ts3db_sqlite3.dll','ts3server.exe');

# If ftp is choosen set credentials					
$ftp_ip = '127.0.0.1';
$ftp_port = '21';
$ftp_user = 'kalle';
$ftp_password = '';	

# Additional configuration
$zip = 'true';						 // If true snapshot will be deployed in .zip compression
$debug = 'true';					 // If enabled it will show warnings/ errors
				

?>
