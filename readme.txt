obVerify -  A php class to verify the license information embedded in
			audio files.

Version 0.1
==========================================================================


Requirements
------------
	- PHP 4.2.0
	- 4MB memory for PHP, 8MB is highly recommended
	- WINDOWS HOSTS: please read getid3-1.7/helperapps/readme.txt
	- cURL library is recommended


Dependencies
------------
	getID3			- GPL V2.0	(Commercial License Available)
	RDFa Monkey		- GPL V2.0
	SHA1			- GPL V2.0


Changelog
---------

0.1 - 21 Dec 2007
	* Initial Release

	
Usage
-----
Object
	require_once("/path/to/obVerify.php");
	
	$verify = new obVerify();
	
	// if you don't have curl installed then you must add this line:
	// $verify->use_curl = false;
	
	$verify->setFile('local file path');
	
	// returns true if the file validates
	// false if it does not
	$verify->analyze();
	
	// the results of getid3() are stored here:
	$verify->id3_info
	
	// copyright information is stored in the associative array:
	$verify->copy_info
	
	// with the following keys
	['verify_url']	- Validation URL for the file (WOAF)
	['copyright'] - The Creative Commons License URL (WCOP)
	['notification'] - The Copyright Text (TCOP)
	['sha1'] - SHA1 Hash in base 32
	['verified'] - true if the id3 information validated with a web page
	['rdfa'] - the RDFa triple generated from the id3 information
	
Settings

	// Is cURL installed?  If set this variable to true so that the
	// script will know to use cURL instead of fopen() when making HTTP
	// requests.
	use_curl = true
	
	
Contact
-------

	Questions/Comments?	tommy@ourbunny.com
	Found a bug?		http://code.google.com/p/jshout/
	Keep in touch		http://www.ourbunny.com