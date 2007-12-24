==========================================================================
||																		||
||							RDFa Monkey	parser							||
||																		||
==========================================================================

This RDF/a parser is written in PHP and uses an adapted version of the 
open-source HTML Parser for PHP-4. It is based on W3C's RDF/a syntax,
W3C's RDFa Primer and the excellent RDFa test files of Elias Torres.

==========================================================================
COPYRIGHT NOTICE 
==========================================================================
Copyright 2006 Ruben Thys

RDFa monkey parser is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation

RDFa monkey parser is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA  

==========================================================================
RDFa Monkey - Ourbunny Edition
==========================================================================

This is an adaptation of Ruben Thys's RDFa Monkey parser.  The
original can be found at:

http://www.avthasselt.sohosted.com/rdfamonkey/ 

Changelog
---------

19 Dec 2007
	- Modified original code into the RDFaParser Object
	- Removed some debugging code
	- Triad output


Requirements
------------
	PHP 4.* ?
	
	
Usage
-----
	// Upload rdfaparser.ini and htmlparser.ini to the same folder on our
	// webserver
	
	include ("/path/to/rdfaparser.php");
	
	// Create a new object
	$parser = new RDFaParser();
	
	// Give it the content to parse.  This content may either be the raw
	// data or the URL of the HTML file to be parsed.
	
	// If you want to parse the raw data use this command
	$parser->setParserData($data);
	
	// Or if you want to parse a URL use this one
	$parser->setParserURL($url);
	
	// Parse the input and store the result!
	$result = $parser->parse();
	
	// The $result will be an array of "triples".  This is basically the
	// same output to the original script except that the parse()
	// function returns explode(' .\n', $output); instead of $output
	
Contact
-------

	Questions/Comments?	tommy@ourbunny.com
	Keep in touch		http://www.ourbunny.com