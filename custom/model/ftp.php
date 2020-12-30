<?php
/*********************************************************************
 *
 *    PHP FTP Client Class By TOMO ( groove@spencernetwork.org )
 *
 *  - Version 0.13 (2002/06/19)
 *  - This script is free but without any warranty.
 *  - You can freely copy, use, modify or redistribute this script
 *    for any purpose.
 *  - But please do not erase this information!!.
 *
 ********************************************************************/


/*********************************************************************
Example

$ftp_host = "ftp.example.com";
$ftp_user = "username";
$ftp_pass = "password";

$ftp = new ftp();

$ftp->debug = TRUE;

if (!$ftp->ftp_connect($ftp_host)) {
	die("Cannot connect\n");
}

if (!$ftp->ftp_login($ftp_user, $ftp_pass)) {
	$ftp->ftp_quit();
	die("Login failed\n");
}

if ($pwd = $ftp->ftp_pwd()) {
	echo "Current directory is ".$pwd."\n";
} else {
	$ftp->ftp_quit();
	die("Error!!\n");
}

if ($sys = $ftp->ftp_systype()) {
	echo "Remote system is ".$sys."\n";
} else {
	$ftp->ftp_quit();
	die("Error!!\n");
}


$local_filename  = "local.file";
$remote_filename = "remote.file";

if ($ftp->ftp_file_exists($remote_filename) == 1) {
	$ftp->ftp_quit();
	die($remote_filename." already exists\n");
}

if ($ftp->ftp_put($remote_filename, $local_filename)) {
	echo $local_filename." has been uploaded as ".$remote_filename."\n";
} else {
	$ftp->ftp_quit();
	die("Error!!\n");
}


$ftp->ftp_quit();
*********************************************************************/



/*********************************************************************
List of available functions

ftp_connect($server, $port = 21)
ftp_login($user, $pass)
ftp_pwd()
ftp_size($pathname)
ftp_mdtm($pathname)
ftp_systype()
ftp_cdup()
ftp_chdir($pathname)
ftp_delete($pathname)
ftp_rmdir($pathname)
ftp_mkdir($pathname)
ftp_file_exists($pathname)
ftp_rename($from, $to)
ftp_nlist($arg = "", $pathname = "")
ftp_rawlist($pathname = "")
ftp_get($localfile, $remotefile, $mode = 1)
ftp_put($remotefile, $localfile, $mode = 1)
ftp_site($command)
ftp_quit()

*********************************************************************/



class ftp
{
	/* Public variables */
	var $debug;		// print debug messages
	var $timeout;	// fsockopen() time-out
	var $umask;		// local umask

	/* Private variables */
	var $_sock;
	var $_resp;
	var $_buf;

	/* Constractor */
	function __construct($debug = FALSE, $timeout = 30, $umask = 0022)
	{
		$this->debug   = $debug;
		$this->timeout = $timeout;
		$this->umask   = $umask;

		if (!defined("FTP_BINARY")) {
			define("FTP_BINARY", 1);
		}
		if (!defined("FTP_ASCII")) {
			define("FTP_ASCII", 0);
		}

		$this->_sock = FALSE;
		$this->_resp = "";
		$this->_buf  = 4096;
	}

	/* Public functions */
	function ftp_connect($server, $port = 21)
	{
		$this->_debug_print("Trying to ".$server.":".$port." ...\n");
		$this->_sock = @fsockopen($server, $port, $errno, $errstr, $this->timeout);

		if (!$this->_sock || !$this->_ok()) {
			$this->_debug_print("Error : Cannot connect to remote host \"".$server.":".$port."\"\n");
			$this->_debug_print("Error : fsockopen() ".$errstr." (".$errno.")\n");
			return FALSE;
		}
		$this->_debug_print("Connected to remote host \"".$server.":".$port."\"\n");

		return TRUE;
	}

	function ftp_login($user, $pass)
	{
		$this->_putcmd("USER", $user);
		if (!$this->_ok()) {
			$this->_debug_print("Error : USER command failed\n");
			return FALSE;
		}

		$this->_putcmd("PASS", $pass);
		if (!$this->_ok()) {
			$this->_debug_print("Error : PASS command failed\n");
			return FALSE;
		}
		$this->_debug_print("Authentication succeeded\n");

		return TRUE;
	}

	function ftp_pwd()
	{
		$this->_putcmd("PWD");
		if (!$this->_ok()) {
			$this->_debug_print("Error : PWD command failed\n");
			return FALSE;
		}

		return preg_replace("/^[0-9]{3} \"(.+)\" .+\r\n/", "$1", $this->_resp);
	}

	function ftp_size($pathname)
	{
		$this->_putcmd("SIZE", $pathname);
		if (!$this->_ok()) {
			$this->_debug_print("Error : SIZE command failed\n");
			return -1;
		}

		return preg_replace("/^[0-9]{3} ([0-9]+)\r\n/", "$1", $this->_resp);
	}

	function ftp_mdtm($pathname)
	{
		$this->_putcmd("MDTM", $pathname);
		if (!$this->_ok()) {
			$this->_debug_print("Error : MDTM command failed\n");
			return -1;
		}
		$mdtm = preg_replace("/^[0-9]{3} ([0-9]+)\r\n/", "$1", $this->_resp);
		$date = sscanf($mdtm, "%4d%2d%2d%2d%2d%2d");
		$timestamp = mktime($date[3], $date[4], $date[5], $date[1], $date[2], $date[0]);

		return $timestamp;
	}

	function ftp_systype()
	{
		$this->_putcmd("SYST");
		if (!$this->_ok()) {
			$this->_debug_print("Error : SYST command failed\n");
			return FALSE;
		}
		$DATA = explode(" ", $this->_resp);

		return $DATA[1];
	}

	function ftp_cdup()
	{
		$this->_putcmd("CDUP");
		$response = $this->_ok();
		if (!$response) {
			$this->_debug_print("Error : CDUP command failed\n");
		}
		return $response;
	}

	function ftp_chdir($pathname)
	{
		$this->_putcmd("CWD", $pathname);
		$response = $this->_ok();
		if (!$response) {
			$this->_debug_print("Error : CWD command failed\n");
		}
		return $response;
	}

	function ftp_delete($pathname)
	{
		$this->_putcmd("DELE", $pathname);
		$response = $this->_ok();
		if (!$response) {
			$this->_debug_print("Error : DELE command failed\n");
		}
		return $response;
	}

	function ftp_rmdir($pathname)
	{
		$this->_putcmd("RMD", $pathname);
		$response = $this->_ok();
		if (!$response) {
			$this->_debug_print("Error : RMD command failed\n");
		}
		return $response;
	}

	function ftp_mkdir($pathname)
	{
		$this->_putcmd("MKD", $pathname);
		$response = $this->_ok();
		if (!$response) {
			$this->_debug_print("Error : MKD command failed\n");
		}
		return $response;
	}

	function ftp_file_exists($pathname)
	{
		if (!($remote_list = $this->ftp_nlist("-a"))) {
			$this->_debug_print("Error : Cannot get remote file list\n");
			return -1;
		}

		foreach($remote_list as $index => $value){
			if ($value == $pathname) {
				$this->_debug_print("Remote file ".$pathname." exists\n");
				return 1;
			}
		}
		$this->_debug_print("Remote file ".$pathname." does not exist\n");

		return 0;
	}


	function ftp_rename($from, $to)
	{
		$this->_putcmd("RNFR", $from);
		if (!$this->_ok()) {
			$this->_debug_print("Error : RNFR command failed\n");
			return FALSE;
		}
		$this->_putcmd("RNTO", $to);

		$response = $this->_ok();
		if (!$response) {
			$this->_debug_print("Error : RNTO command failed\n");
		}
		return $response;
	}

	function ftp_nlist($arg = "", $pathname = "")
	{
		if (!($string = $this->_pasv())) {
			return FALSE;
		}

		if ($arg == "") {
			$nlst = "NLST";
		} else {
			$nlst = "NLST ".$arg;
		}
		$this->_putcmd($nlst, $pathname);

		$sock_data = $this->_open_data_connection($string);
		if (!$sock_data || !$this->_ok()) {
			$this->_debug_print("Error : Cannot connect to remote host\n");
			$this->_debug_print("Error : NLST command failed\n");
			return FALSE;
		}
		$this->_debug_print("Connected to remote host\n");

		while (!feof($sock_data)) {
			$list[] = preg_replace("/[\r\n]/", "", fgets($sock_data, 512));
		}
		$this->_close_data_connection($sock_data);
		$this->_debug_print(implode("\n", $list));

		if (!$this->_ok()) {
			$this->_debug_print("Error : NLST command failed\n");
			return FALSE;
		}

		return $list;
	}

	function ftp_rawlist($pathname = "")
	{
		if (!($string = $this->_pasv())) {
			return FALSE;
		}

		$this->_putcmd("LIST", $pathname);

		$sock_data = $this->_open_data_connection($string);
		if (!$sock_data || !$this->_ok()) {
			$this->_debug_print("Error : Cannot connect to remote host\n");
			$this->_debug_print("Error : LIST command failed\n");
			return FALSE;
		}

		$this->_debug_print("Connected to remote host\n");

		while (!feof($sock_data)) {
			$list[] = preg_replace("/[\r\n]/", "", fgets($sock_data, 512));
		}
		$this->_debug_print(implode("\n", $list));
		$this->_close_data_connection($sock_data);

		if (!$this->_ok()) {
			$this->_debug_print("Error : LIST command failed\n");
			return FALSE;
		}

		return $list;
	}

	function ftp_get($localfile, $remotefile, $mode = 1)
	{
		umask($this->umask);

		if (@file_exists($localfile)) {
			$this->_debug_print("Warning : local file will be overwritten\n");
		}

		$fp = @fopen($localfile, "wb");
		if (!$fp) {
			$this->_debug_print("Error : Cannot create \"".$localfile."\"");
			$this->_debug_print("Error : GET command failed\n");
			return FALSE;
		}

		if (!$this->_type($mode)) {
			$this->_debug_print("Error : GET command failed\n");
			return FALSE;
		}

		if (!($string = $this->_pasv())) {
			$this->_debug_print("Error : GET command failed\n");
			return FALSE;
		}

		$this->_putcmd("RETR", $remotefile);

		$sock_data = $this->_open_data_connection($string);
		if (!$sock_data || !$this->_ok()) {
			$this->_debug_print("Error : Cannot connect to remote host\n");
			$this->_debug_print("Error : GET command failed\n");
			return FALSE;
		}
		$this->_debug_print("Connected to remote host\n");
		$this->_debug_print("Retrieving remote file \"".$remotefile."\" to local file \"".$localfile."\"\n");
		while (!feof($sock_data)) {
			fwrite($fp, fread($sock_data, $this->_buf));
		}
		fclose($fp);

		$this->_close_data_connection($sock_data);

		$response = $this->_ok();
		if (!$response) {
			$this->_debug_print("Error : GET command failed\n");
		}
		return $response;
	}

	function ftp_put($remotefile, $localfile, $mode = 1)
	{
		
		if (!@file_exists($localfile)) {
			$this->_debug_print("Error : No such file or directory \"".$localfile."\"\n");
			$this->_debug_print("Error : PUT command failed\n");
			return FALSE;
		}

		$fp = @fopen($localfile, "rb");
		if (!$fp) {
			$this->_debug_print("Error : Cannot read file \"".$localfile."\"\n");
			$this->_debug_print("Error : PUT command failed\n");
			return FALSE;
		}

		if (!$this->_type($mode)) {
			$this->_debug_print("Error : PUT command failed\n");
			return FALSE;
		}

		if (!($string = $this->_pasv())) {
			$this->_debug_print("Error : PUT command failed\n");
			return FALSE;
		}

		$this->_putcmd("STOR", $remotefile);

		$sock_data = $this->_open_data_connection($string);
		if (!$sock_data || !$this->_ok()) {
			$this->_debug_print("Error : Cannot connect to remote host\n");
			$this->_debug_print("Error : PUT command failed\n");
			return FALSE;
		}
		$this->_debug_print("Connected to remote host\n");
		$this->_debug_print("Storing local file \"".$localfile."\" to remote file \"".$remotefile."\"\n");
		while (!feof($fp)) {
			fwrite($sock_data, fread($fp, $this->_buf));
		}
		fclose($fp);

		$this->_close_data_connection($sock_data);

		$response = $this->_ok();
		if (!$response) {
			$this->_debug_print("Error : PUT command failed\n");
		}
		return $response;
	}

	function ftp_site($command)
	{
		$this->_putcmd("SITE", $command);
		$response = $this->_ok();
		if (!$response) {
			$this->_debug_print("Error : SITE command failed\n");
		}
		return $response;
	}

	function ftp_quit()
	{
		$this->_putcmd("QUIT");
		if (!$this->_ok() || !fclose($this->_sock)) {
			$this->_debug_print("Error : QUIT command failed\n");
			return FALSE;
		}
		$this->_debug_print("Disconnected from remote host\n");
		return TRUE;
	}

	/* Private Functions */

	function _type($mode)
	{
		if ($mode) {
			$type = "I"; //Binary mode
		} else {
			$type = "A"; //ASCII mode
		}
		$this->_putcmd("TYPE", $type);
		$response = $this->_ok();
		if (!$response) {
			$this->_debug_print("Error : TYPE command failed\n");
		}
		return $response;
	}

	function _port($ip_port)
	{
		$this->_putcmd("PORT", $ip_port);
		$response = $this->_ok();
		if (!$response) {
			$this->_debug_print("Error : PORT command failed\n");
		}
		return $response;
	}

	function _pasv()
	{
		$this->_putcmd("PASV");
		if (!$this->_ok()) {
			$this->_debug_print("Error : PASV command failed\n");
			return FALSE;
		}

		$ip_port = preg_replace("/^.+ \\(?([0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+)\\)?.*\r\n$/", "$1", $this->_resp);
		return $ip_port;
	}

	function _putcmd($cmd, $arg = "")
	{
		if ($arg != "") {
			$cmd = $cmd." ".$arg;
		}

		if( !fwrite($this->_sock, $cmd."\r\n") )
			{ return FALSE; }

		$this->_debug_print("> ".$cmd."\n");

		return TRUE;
	}

	function _ok()
	{
		$this->_resp = "";
		do {
			$res = fgets($this->_sock, 512);

			if( FALSE === $res )
				{ return FALSE; }

			$this->_resp .= $res;
		} while (substr($res, 3, 1) != " ");

		$this->_debug_print(str_replace("\r\n", "\n", $this->_resp));

		if (!preg_match("/^[123]/", $this->_resp)) {
			return FALSE;
		}

		return TRUE;
	}

	function _close_data_connection($sock)
	{
		$this->_debug_print("Disconnected from remote host\n");
		return fclose($sock);
	}

	function _open_data_connection($ip_port)
	{
		if (!preg_match("/[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+/", $ip_port)) {
			$this->_debug_print("Error : Illegal ip-port format(".$ip_port.")\n");
			return FALSE;
		}

		$DATA = explode(",", $ip_port);
		$ipaddr = $DATA[0].".".$DATA[1].".".$DATA[2].".".$DATA[3];
		$port   = $DATA[4]*256 + $DATA[5];
		$this->_debug_print("Trying to ".$ipaddr.":".$port." ...\n");
		$data_connection = @fsockopen($ipaddr, $port, $errno, $errstr);
		if (!$data_connection) {
			$this->_debug_print("Error : Cannot open data connection to ".$ipaddr.":".$port."\n");
			$this->_debug_print("Error : ".$errstr." (".$errno.")\n");
			return FALSE;
		}

		return $data_connection;
	}

	function _debug_print($message = "")
	{
		if ($this->debug) {
			echo $message;
		}

		return TRUE;
	}
}
?>
