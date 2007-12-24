<?php

/**
 * 
 *	=======================================================================
 * 	obVerify - A php class to verify the license information embedded in 
 *	audio files.
 *
 *	Copyright (C) 2007  Tommy Murphy
 *
 *	This program is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 2 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License along
 *	with this program; if not, write to the Free Software Foundation, Inc.,
 *	51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *	=======================================================================
 *
 */

require_once("SHA1.php");
require_once("rdfa/rdfaparser.php");
require_once("getid3-1.7/getid3/getid3.php");

class obVerify {
	// private variables
	var $ready;
	var $file;
	var $getid3;

	// public variables
	var $use_curl = true;

	var $id3_info;
	var $copy_info;

	// constructor
	function obVerify() {
		$this->ready = false;
		
		// set up getid3
		$this->getid3 = new getID3();
		$this->getid3->encoding = 'UTF-8';
	}

	// path of audio file to be analyzed
	function setFile($path) {
		//unset($this->id3_info);
		//unset($this->copy_info);

		$this->file = $path;
		$this->ready = true;
	}

	// actually analyze the file
	function analyze() {
		if (!$this->ready) return false;

		$this->copy_info = array();

		$this->id3_info = $this->getid3->analyze($this->file);
		getid3_lib::CopyTagsToComments($this->id3_info);

		$this->copy_info['verify_url'] = $this->id3_info['comments']['url_file'][0];
		$this->copy_info['copyright'] = $this->id3_info['comments']['copyright'][0];
		$this->copy_info['notification'] = $this->id3_info['comments']['copyright_message'][0];
		$this->copy_info['sha1'] = fileSHA1($this->file);

		if (empty($this->copy_info['verify_url']) || empty($this->copy_info['copyright'])) {
			// no verify url or no copyright...
			// so no point in trying to verify!
			$this->copy_info['verified'] = false;
			return false;
		}

		$parser = new RDFaParser();

		if ($this->use_curl) {
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $this->copy_info['verify_url']);
			curl_setopt($ch, CURLOPT_HEADER, false);		// don't care about the header 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);	// follow all redirects
			curl_setopt($ch, CURLOPT_ENCODING, "");			// handle all encodings
			curl_setopt($ch, CURLOPT_MAXREDIRS, 10);		// set a reasonable # of redirects
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	// direct output of curl_exec

			$body = curl_exec($ch);

			curl_close($ch);

			$parser->setParserData($body);
		} else {
			$parser->setParserURL($this->copy_info['verify_url']);
		}

		$results = $parser->parse();

		$this->copy_info['rdfa'] = '<urn:sha1:' . $this->copy_info['sha1'] . '> license <' . $this->copy_info['copyright'] . '>';

		if (array_search($this->copy_info['rdfa'], $results) === false) {
			$this->copy_info['verified'] = false;
		} else {
			$this->copy_info['verified'] = true;
		}

		return $this->copy_info['verified'];
	}
}

?>
