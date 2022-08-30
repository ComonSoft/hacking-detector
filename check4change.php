<?php
/*~ check4change.php
.---------------------------------------------------------------------------.
|  Software: PHPCheck4Change - PHP script that scan directories and files   |
|            for change during interval                                     |
|   Version: 1.1                                                            |
|      Site:                                                                |
| ------------------------------------------------------------------------- |
|   Authors: Com'onSoft http://www.comonsoft.com                            |
|   Founder: Com'onSoft (original founder)                                  |
| ------------------------------------------------------------------------- |
|   License: Distributed under the Lesser General Public License (LGPL)     |
|            http://www.gnu.org/copyleft/lesser.html                        |
| This program is distributed in the hope that it will be useful - WITHOUT  |
| ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or     |
| FITNESS FOR A PARTICULAR PURPOSE.                                         |
'---------------------------------------------------------------------------'
*/

/**
 * check4change - PHP script to detect directories and files modifications 
 * NOTE: Requires PHP version 5.3 or later
 * @package PHPCheck4Change
 * @author Com'onSoft
 * @copyright 2022 Com'onSoft
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

set_time_limit(0);	// No time limit, normaly script running on a cron job as a php command line

// Constants used for mail() see PHP user manual, should not be modified
define("cMAIL_HtmlFormat","0");
define("cMAIL_TextFormat","1");
define("cMAIL_HighPriority","1");
define("cMAIL_NormalPriority","3");
define("cMAIL_LowPriority","5");

// Constants messages with language suffix, feel free to change messages used for report
//		english
define("cMSG-REPORT-TITLE_EN",".:: LIST OF FILES/FOLDERS CREATED OR MODIFIED LESS THAN %s MINUTES ::.\r\n\r\n");
define("cMSG-REPORT-LIST-HEADER_EN","OBJECT\tCREATION\tMODIFICATION\tTYPE\r\n");
define("cMSG-REPORT-CREATION_EN","created");
define("cMSG-REPORT-MODIFICATION_EN","modified");
define("cMSG-REPORT-START_EN","Start:");
define("cMSG-REPORT-END_EN","End:");
define("cMSG-REPORT-OBJECTS_EN","Number of scanned objects:");
//		french
define("cMSG-REPORT-TITLE_FR",".:: LISTE DES FICHIERS/DOSSIERS MODIFIES OU CREES IL Y A MOINS DE %s MINUTES ::.\r\n\r\n");
define("cMSG-REPORT-LIST-HEADER_FR","OBJET\tCRÉATION\tMODIFICATION\tTYPE\r\n");
define("cMSG-REPORT-CREATION_FR","créé");
define("cMSG-REPORT-MODIFICATION_FR","modifié");
define("cMSG-REPORT-START_FR","Début:");
define("cMSG-REPORT-END_FR","Fin:");
define("cMSG-REPORT-OBJECTS_FR","Nombre d'objets scannés:");

/*-------------------------------------------------------------------------
* Class		: DirFilter extend RecursiveFilterIterator
* Purpose	: Filter list of directories to exclude from scan
*-------------------------------------------------------------------------*/
class DirFilter extends RecursiveFilterIterator
{
	private static $mExcludes = array(); // List of directories to exclude
	
	public function setExcludeDirs( $inArrayDir) {
		if( $inArrayDir && count($inArrayDir)>0 )
			DirFilter::$mExcludes = $inArrayDir;
	}
	
    public function accept() {
        return !($this->isDir() && in_array($this->getFilename(), DirFilter::$mExcludes));
    }
}
//-----------End function : DirFilter()------------------------------------

/*
* Class	  : scanDirectory( $inPath=null, $inLang='EN', $inDelta = 3600, $inExclude=null)
* Purpose : Scan directory recursively, scan for date creation/modification according to delta
*	        Generate a report and send report by mail
*/
class scanDirectory {
	private $nbObjects = 0;			// Number of objects scanned
	private $start, $end ;			// Timers
	private $result;				// Array result of scan
	private $report = null;			// Human readable report
	private $delta = 3600 ;			// Default delta interval in seconds
	private $homeDir ;				// Start directory scanning
	private $excludeDir = null;		// Array of exclude directories
	private $lang = 'EN' ;			// Default language for report
	private $excludeFile = null;	// Array of exclude files
	
	/*
	* function: __constructor( $inPath=null, $inDelta = 3600, $inExclude=null)
	* Parameters
	*		string $inPath = start path to scan
	*		chars(2) = language iso code for report
	*		int $inDelta = time delta to check in seconds. Recomanded delta is 3600s interval for a cron running each hour
	*		array string $inExclude = null or array of directories exclusion
	*		array string $inExcludeFile = null or array of directories exclusion
	*/
	function  __construct( $inPath=null, $inLang='EN', $inDelta = 3600, $inExclude=null, $inExcludeFile = null) {
		if( is_null( $inPath) )
			$this->homeDir = dirname(__DIR__);
		else
			$this->homeDir = $inPath;
		if( $inDelta )
			$this->delat = $inDelta;
		if( $inExclude )
			$this->excludeDir = $inExclude;
		if($inExcludeFile)
			$this->excludeFile = $inExcludeFile;	
		// Verify supported languages
		if( $inLang=='EN' || $inLang=='FR')
			$this->lang = $inLang;
	}
	//-----------End default constructor------------------------------------
	

	/*
	* Function	: Run()
	* Purpose	: Scan directory recursively, scan for date creation/modification according to delta
	*	Generate  multidimensional array of files/directories changed or created since last delta
	*	string array[]['obj'] = full path file or directory
	*	string array[]['date'] = date time cretation or modification
	*	int array[]['cre'] = true if object is creation
	*   and process for report
	* Return
	*	int number of objects scanned
	*/
	public function Run() {
		$this->start = date("Y/m/d H:i:s", time());
		clearstatcache( true) ;		// Be sure to clear the cache before
		$res = array();	

		$root = new RecursiveDirectoryIterator($this->homeDir, FilesystemIterator::SKIP_DOTS);
		if( $this->excludeDir ) {
			$root = new DirFilter($root);
			$root->setExcludeDirs(  $this->excludeDir);
		}
		foreach ( new RecursiveIteratorIterator($root, RecursiveIteratorIterator::SELF_FIRST) as $filename => $obj ) {
			if($obj->isFile() && !empty($this->excludeFile)){
				if(in_array($obj->getFilename(), $this->excludeFile)){
					continue;
				}
			}
			$this->nbObjects++;
			$datec = $obj->getCTime();
			$date = $obj->getMTime();
			
			if( $datec==$date ) {	// Object created
				$isCreation = 1 ;
			}
			else {
				$isCreation = 0 ;
			}
			if( (time()-$date) <= $this->delta ) {
				$res[] = array( 'obj' => $filename, 'datec' => date("Y/m/d H:i:s", $datec), 'date' => date("Y/m/d H:i:s", $date), 'cre' => $isCreation) ;
			}
		}
		$this->result = $res;
		$this->Report();

		return $this->nbObjects;
	}
	//-----------End method : Run()------------------------------------
	
	/*
	* Function	: Report()
	* Purpose	: Generate a human readable report from scan
	* Return
	*			void
	*/
	private function Report() {
		if( count($this->result)>0 ) {	// Generate report only if something to notify
			$this->report = sprintf( constant('cMSG-REPORT-TITLE_'.$this->lang), ($this->delta)/60);
			$this->report .= constant('cMSG-REPORT-LIST-HEADER_'.$this->lang);
			
			foreach( $this->result as $obj) {
				if( $obj['cre']==true )
					$msgType = constant('cMSG-REPORT-CREATION_'.$this->lang);
				else
					$msgType = constant('cMSG-REPORT-MODIFICATION_'.$this->lang);
					
				$this->report .= $obj['obj']."\t".$obj['datec']."\t".$obj['date']."\t$msgType\r\n";
			}
			$this->end = date("Y/m/d H:i:s", time());
			$this->report = 'CRON: '.__FILE__."\r\n".constant('cMSG-REPORT-START_'.$this->lang)." $this->start\r\n".constant('cMSG-REPORT-OBJECTS_'.$this->lang)." $this->nbObjects\r\n".constant('cMSG-REPORT-END_'.$this->lang)." $this->end\r\n\r\n$this->report";
		}
	}
	//-----------End method : Report()------------------------------------
	
	/*-------------------------------------------------------------------------
	* Function	: boolean MailReport( $inFrom, $inDest, $inSubject , $inPrio, $inFormat, $inCharset, $inCc)
	* Purpose	: Send report by mail in text or html mode
	* Parameters
	*       string inFrom from recipient
	*       string inDest dest recipient
	*       string inSubject message subject
	*       int inPrio message priority : 1:Highest / 3:Normal / 5: lowest
	*       int inFormat message format : 0:TEXT / 1:HTML
	*		string inCharset : utf-8, ansi ...
	* Return
	*       true if OK
	*       false otherwise 
	* External function(s)
	*       stripslashes()
	*       mail()
	*-------------------------------------------------------------------------*/
	public function MailReport ($inFrom, $inDest, $inSubject, $inPrio=cMAIL_NormalPriority, $inFormat=cMAIL_TextFormat, $inCharset="utf-8", $inCc=null)
	{
		if(  ! is_null( $this->report) ) {
			$headers = "From: ". $inFrom . "\n";  // Initialize mail Header
			if ($inPrio!=cMAIL_NormalPriority) $headers .= "X-Priority: ".$inPrio."\n";	// Set Message priority
			if ($inCc) $headers .= "Cc: ".$inCc."\n"; // Set carbon copy
			// Safe mode
			$headers .= "Errors-To: $inFrom\n";
			$headers .= "Return-Path: ".$inFrom."\n";
			$headers .= "Reply-To: $inFrom\n"; 
			$headers .= "X-Sender: $inFrom\n";
			$headers .= "X-auth-smtp-user: $inFrom\n";
			$headers .= "X-Mailer: Security pack COMONSOFT\n"; 
		
			$format_body = stripslashes( $this->report);
			if ($inFormat==cMAIL_HtmlFormat) {
				$headers  .= "MIME-Version: 1.0\n";
				$headers .= "Content-Type: text/html; charset=".$inCharset."\n"; // Type MIME
			}
			else
				$headers .= "Content-Type: text/plain; charset=".$inCharset."\n"; // Type text
			$format_subject = stripslashes( $inSubject );
			
			return mail($inDest, $format_subject, $format_body , $headers );
		}
	}
	//-----------End method : MailReport()------------------------------------
}
//---------------------End class: scanDirectory-------------------------------

// -- MAIN --------------------------------
$scan = new scanDirectory( dirname(__DIR__), 'FR');
$scan->Run();
$scan->MailReport( 'sender@yourdomain.com', 'receiver@yourdomain.com', 'Alert modifications: www.yourdomain.com');
?>
