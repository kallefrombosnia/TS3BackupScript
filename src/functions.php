<?php
include_once('../htdocs/config.php');
// Framework check
$filename = 'src/TeamSpeak3/TeamSpeak3.php';

if (file_exists($filename)) {
    require_once($filename);
} else {
    die ("The file $filename does not exist");
}

//
function makeSnapshot($mode,$port){
	global $ts3;
	global $snapdir;
	global $snapname;
	global $ftp_ip;
	global $ftp_user;
	global $ftp_password;
	global $ts3dir;
	global $snapdirreal;
	global $ignoredfiles;
	global $zip;
	global $method;


	switch ($mode) {
		case 'full':
				// Creating snapshot
				if($method=='ftp'){
					$snapshotdata = $ts3->snapshotCreate();
					$snapshotclean = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $snapshotdata);
					$dir = ($snapdir.'/'.$snapname."-".date('m-d-Y_H-m-s')."-Server-".$port.".txt");

					// But this one takes full ts3 directory + snapshot			
					$localdir = ($snapdirreal.date('m-d-Y_H-i-s'));

					// call ftp copy function
					ftp_copy($ftp_ip,$ftp_user,$ftp_password,$snapdirreal,$ts3dir,$ignoredfiles,$localdir);
					
					// Checking if zipping is enabled, if yes then our dir will be zipped and deleted
					if($zip == 'true'){

						// Get real path for our folder
						$rootPath = realpath($localdir);
						chdir($rootPath);

						// Initialize archive object
						$zip = new ZipArchive();
						$zip->open($localdir.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

						// Create recursive directory iterator
						$files = new RecursiveIteratorIterator(
						    new RecursiveDirectoryIterator($rootPath),
						    RecursiveIteratorIterator::LEAVES_ONLY
						);
						
						foreach ($files as $name => $file)
						{
						    // Skip directories (they would be added automatically)
						    if (!$file->isDir())
						    {
						        // Get real and relative path for current file
						        $filePath = $file->getRealPath();
						        $relativePath = substr($filePath, strlen($rootPath) + 1);

						        // Add current file to archive
						        $zip->addFile($filePath, $relativePath);
						    }
						}

						// adds snapshot string to our zip
						$zip->addFromString($dir, $snapshotclean);
	   
						// Zip archive will be created only after closing object
						$zip->close();

						// Delete directory because its zipped (folder not needed)
						delDirectory($localdir);
					}
				}else{

					$localdir = ($snapdirreal.date('m-d-Y_H-i-s').".zip");
					zipData($localdir,$ts3dir,$ignoredfiles);
				}

			break;

		case 'files':
				// But this one takes full ts3 directory	
				$localdir = ($snapdirreal.date('m-d-Y_H-i-s'));
				// call ftp copy function
				ftp_copy($ftp_ip,$ftp_user,$ftp_password,$snapdirreal,$ts3dir,$ignoredfiles,$localdir);
				// Checking if zipping is enabled, if yes then our dir will be zipped and deleted
				if($zip == 'true'){
					// Get real path for our folder
					$rootPath = realpath($localdir);
					chdir($rootPath);
					// Initialize archive object
					$zip = new ZipArchive();
					$zip->open($localdir.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

					// Create recursive directory iterator
					$files = new RecursiveIteratorIterator(
					    new RecursiveDirectoryIterator($rootPath),
					    RecursiveIteratorIterator::LEAVES_ONLY
					);
					//loop trough files and add them
					foreach ($files as $name => $file)
					{
					    // Skip directories (they would be added automatically)
					    if (!$file->isDir())
					    {
					        // Get real and relative path for current file
					        $filePath = $file->getRealPath();
					        $relativePath = substr($filePath, strlen($rootPath) + 1);

					        // Add current file to archive
					        $zip->addFile($filePath, $relativePath);
					    }
					}

					// Zip archive will be created only after closing object
					$zip->close();

					// Delete directory because its zipped (folder not needed)
					delDirectory($localdir);
				}


			break;
		
		case 'snapshot':
				// Creating snapshot
				$snapshotdata = $ts3->snapshotCreate();
				$snapshotclean = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $snapshotdata);
				$dir = ($snapdir.'/'.$snapname."-".date('m-d-Y_H-m-s')."-Server-".$port.".txt");

				// It just snaps server and puts it into backups file.txt
				file_put_contents($dir, $snapshotclean);
				file_put_contents($dir, implode(PHP_EOL, file($dir, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)));
			break;

		default:
			die('No default mode here');
			break;
	}
	//var_dump($strsnapshot);
}

function ftp_copy($ip,$user,$pw, $src_dir, $dst_dir,$ignoredfiles,$localdir){
	//$dst_dir = dirname($dst_dir);
	// set up a connection or die
	$conn_id = ftp_connect($ip) or die("Couldn't connect to $ip"); 

	// try to login
	if (@ftp_login($conn_id, $user, $pw)) {
	} else {
	    die("Couldn't connect as $user\n");
	}
	// set connection to passive (bugs with firewall)
	ftp_pasv($conn_id, true);
	// name new folder in local directory
	//$localdir = ($src_dir.date('m-d-Y_H-i-s'));
	// make new directory where is backup gonna be saved
	mkdir($localdir);
	// Change directory where we gonna save downloaded ftp files
	chdir($localdir);
	// call on ftp download function
	ftp_sync($dst_dir,$conn_id,$ignoredfiles); 
}

function ftp_sync($dir,$conn_id,$ignoredfiles){ 


if ($dir != ".") { 
    if (ftp_chdir($conn_id, $dir) == false) { 
        echo ("Change Dir Failed: $dir<BR>\r\n"); 
        return; 
    } 
    if (!(is_dir($dir))) 
        mkdir($dir); 
    chdir ($dir); 
} 

$contents = ftp_nlist($conn_id, "."); 
foreach ($contents as $file) { 
	// check for ignored files/ dirs
    if ($file == '.' || $file == '..' || in_array($file, $ignoredfiles)) continue; 
     
    // parse trough all dirs and subdirs
    if (@ftp_chdir($conn_id, $file)) { 
        ftp_chdir ($conn_id, ".."); 
        ftp_sync ($file,$conn_id,$ignoredfiles); 
    } 
    else{
        @ftp_get($conn_id, $file, $file, FTP_BINARY); 
    }
} 
    
ftp_chdir ($conn_id, ".."); 
chdir (".."); 
} 


function delDirectory($dir) { 
	$files = array_diff(scandir($dir), array('.','..')); 
	foreach ($files as $file) { 
	  (is_dir("$dir/$file")) ? delDirectory("$dir/$file") : unlink("$dir/$file"); 
	} 
	return rmdir($dir); 
} 

?>

