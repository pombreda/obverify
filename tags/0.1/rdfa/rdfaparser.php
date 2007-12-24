<?php
	
/**
 *	Copyright 2006 Ruben Thys
 *	
 *	RDFa monkey parser is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation
 *	
 *	RDFa monkey parser is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 *	GNU General Public License for more details.
 *	
 *	You should have received a copy of the GNU General Public License
 *	along with this program; if not, write to the Free Software
 *	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA	02110-1301	USA	
 *
 *	Edited 2007 by Tommy Murphy
 *		- put everything into a class
 *
 */

include_once ("htmlparser.php");

class RDFaParser {
	// ABOUT ARRAYS
	var $aboutArray;
	var $aboutTypes;
	
	// ID ARRAYS
	var $idArray;
	var $idTypes;
	var $elementCounter;
	
	// NAMESPACE
	var $namespaces;
	
	// HTML PARSER
	var $isset = false;
	var $parser;

	// Constructor
	function RDFaParser() {
		$this->isset = false;
	}
	
	function setParserURL($url) {
		$this->parser = new HtmlParser("");
		$this->parser = $this->parser->HtmlParser_ForURL($url);
		$this->isset = true;
	}
	
	function setParserData($data) {
		$this->parser = new HtmlParser($data);
		$this->isset = true;
	}
	
	function parse() {
		if (!$this->isset) return false;
		
		// CREATION 
		$outputInfo = array();
		$this->aboutArray = array();
		$this->aboutTypes = array();
		$this->idArray = array();
		$this->idTypes = array();
		$this->elementCounter = array(); 
		$this->namespaces = array();
		
		// START PARSING
		while ($this->parser->parse()) 
		{
	
			if($this->parser->iNodeType == NODE_TYPE_ELEMENT || $this->parser->iNodeType == NODE_TYPE_ELEMENTANDEND) 
			{
			
				$added = false;
				
				// add element to counter
				if(!isset($this->elementCounter[$this->parser->iNodeName]))
					$this->elementCounter[$this->parser->iNodeName] = 1;
				else 
					$this->elementCounter[$this->parser->iNodeName] = $this->elementCounter[$this->parser->iNodeName] + 1;
				
				// SAVE PARENT ABOUT STATES
				if($this->checkIfAttribute(array_keys($this->parser->iNodeAttributes),"about")) {
					
					
					$this->aboutArray[sizeof($this->aboutArray)] = $this->parser->iNodeAttributes["about"];
					$this->idArray[sizeof($this->idArray)] = $this->parser->iNodeAttributes["about"];
					
					
					$this->aboutTypes[sizeof($this->aboutTypes)] = $this->parser->iNodeName;
					$this->idTypes[sizeof($this->idTypes)] = $this->parser->iNodeName;
					$added = true;
					
				}
				else 
				{
					$this->checkAboutStack($this->parser->iNodeName);
				}
				
				// SAVE ID ABOUT STATES
				
				// if already an 'about'-tag found, don't add the id-tag to the stack
				if(!$added) 
				{
					if($this->checkIfAttribute(array_keys($this->parser->iNodeAttributes),"id")) {
						$this->idArray[sizeof($this->idArray)] = "#" .$this->parser->iNodeAttributes["id"];
						$this->idTypes[sizeof($this->idTypes)] = $this->parser->iNodeName;
					}
					else if($this->checkIfAttribute(array_keys($this->parser->iNodeAttributes),"xml:base"))
					{
						$this->idArray[sizeof($this->idArray)] = $this->parser->iNodeAttributes["xml:base"];
						$this->idTypes[sizeof($this->idTypes)] = $this->parser->iNodeName;
					}
					else 
					{
						$this->idArray[sizeof($this->idArray)] = "_:" . $this->parser->iNodeName . $this->elementCounter[$this->parser->iNodeName];
						$this->idTypes[sizeof($this->idTypes)] = $this->parser->iNodeName;
					}
				}
						
			}
	
	
			$keys = (array_keys($this->parser->iNodeAttributes));
			
			// RUN THROUGH ALL ATTRIBUTES
			foreach($keys as $key) {
				
				if($this->parser->iNodeType == NODE_TYPE_ELEMENT || $this->parser->iNodeType == NODE_TYPE_ELEMENTANDEND) 
				{
					$addElement = false;
					
					/////// NAMESPACES //////
					if(strstr ($key, 'xmlns')) 
					{
					
						$prefix = split(":",$key);
						if(isset($prefix[1])) {				
							$add = "@prefix " . $prefix[1] . ":	<" . $this->parser->iNodeAttributes[$key] . ">	.\n";
							$this->namespaces[$prefix[1]] = $this->parser->iNodeAttributes[$key];
						}
						else
							$add = "@prefix " . ":	<" . $this->parser->iNodeAttributes[$key] .">	.\n";
						
							
						if(!$this->alreadyAdded($add,$outputInfo))
								array_push($outputInfo,$add);
					}
					/////// END NAMESPACES //////
					
					
						
					/////// ABOUT OR ID TAG //////
					
					// NO <link> or <meta> tag
					if($this->parser->iNodeName != "link" && $this->parser->iNodeName != "meta") {
						// get highest about in DOM-tree
						$about = $this->getAboutAttribute();
					}
					// <link> or <meta> tag
					else {
					
						$about = "";
						// check if about-tag exists
						if(isset($this->parser->iNodeAttributes['about'])) {
							$about = $this->parser->iNodeAttributes['about'];
							
						}			
						// else get id from parent element 
						else if (trim($about) == "")
						{
							$about = $this->getParentId();
						}
					}
					
					
					// strip front '[' and back ']' if exists
					if(isset($about[0]) && isset($about[strlen($about)-1]) && $about[0] == '[' && $about[strlen($about)-1] == ']')
					{
						$about = substr($about,1,strlen($about)-2);					
					}
					else {
						// check type
						$about = "<".$about.">";
					}
					
					/////// END ABOUT OR ID TAG //////
					
									
					/////// PROPERTY TAG //////
					if(isset($this->parser->iNodeAttributes['property'])) {
					
						$property = $this->parser->iNodeAttributes['property'];
						$property = $this->checkType($property);
						
						
						// check if there is a type-tag available
						if(isset($this->parser->iNodeAttributes['type']))
						{
							$type = $this->parser->iNodeAttributes['type'];
						}
						// check if there is a datatype-tag available
						else if(isset($this->parser->iNodeAttributes['datatype']))
						{
							$type = $this->parser->iNodeAttributes['datatype'];
						}
						// default type
						else {
							$type = "rdf:XMLLiteral";
						}
						
						// check if there is a xml:lang-tag available
						if(isset($this->parser->iNodeAttributes['xml:lang']))
						{
							$lang = "@" . $this->parser->iNodeAttributes['xml:lang'];
						}
						// default type
						else {
							$lang= "";
						}
					
						// Content Tag
						if(isset($this->parser->iNodeAttributes['content'])) {
							$content = $this->parser->iNodeAttributes['content'];
							
							$add = $about . " " . $property . " \"" . $content	. "\"" . $lang . "^^" . $type	. " .\n";
							if(!$this->alreadyAdded($add,$outputInfo))
								array_push($outputInfo,$add);
						}
						// Node value
						else 
						{
							$this->parser->parse();
							
							$add = $about . " " . $property . " \"" . $this->parser->iNodeValue	. "\"" . $lang . "^^" . $type	. " .\n";					
							if(!$this->alreadyAdded($add,$outputInfo))
								array_push($outputInfo,$add);
						}
					}
					//////// END PROPERTY TAG ///////
					
					
					/////// REL TAG //////
					if(isset($this->parser->iNodeAttributes['rel'])) {
						
						$rel = $this->parser->iNodeAttributes['rel'];
						
						if(isset($this->parser->iNodeAttributes['href'])) {
							$href = $this->parser->iNodeAttributes['href'];
													
							$href = $this->checkIfNamespace($href);
							$href = $this->checkType($href);
							$rel = $this->checkType($rel);
							
							
							$add = $about . " " . $rel . " " . $href	. " .\n";
							
							if(!$this->alreadyAdded($add,$outputInfo))
								array_push($outputInfo,$add);
						}
						
					}
					//////// END REL TAG ///////
					
					/////// REV TAG //////
					if(isset($this->parser->iNodeAttributes['rev'])) {
	
						$rev = $this->parser->iNodeAttributes['rev'];
						
						if(isset($this->parser->iNodeAttributes['href'])) {
							$href = $this->parser->iNodeAttributes['href'];
							
							$href = $this->checkIfNamespace($href);
							$href = $this->checkType($href);
							$rev = $this->checkType($rev);
							
							$add = $href . " " . $rev . " " . $about	. " .\n";
							
							if(!$this->alreadyAdded($add,$outputInfo))
								array_push($outputInfo,$add);
						}
						
					}
					//////// END REV TAG ///////
					
				}
			}
			
			if($this->parser->iNodeType == NODE_TYPE_ENDELEMENT || $this->parser->iNodeType == NODE_TYPE_ELEMENTANDEND) 
			{
				$this->deleteStackAttribute($this->parser->iNodeName);
			}	
		}
		
		$output = $this->parseToString($outputInfo);
		return explode(" .\n",$output);
	}

	//////////// PRIVATE FUNCTIONS ////////////////////////////////////////////
	function alreadyAdded($str,$outputInfo) 
	{
		for($i = 0; $i < sizeof($outputInfo); $i++) 
		{
			if($str == $outputInfo[$i]) 
				return true;
		}
		
		return false;
	}
	
	function checkIfNamespace($element) 
	{
		if(isset($element[0]) && isset($element[strlen($element)-1]) && $element[0] == '[' && $element[strlen($element)-1] == ']')
		{
			$element = substr($element,1,strlen($element)-2);
		}
						
		/*$namesp = split(":",$element);
		
		if(isset($this->namespaces[$namesp[0]]))
			return	$this->namespaces[$namesp[0]] . $namesp[1];*/
		
		
		return $element; 
	
	}
	
	function parseToString($outputInfo)
	{
		$returnStr = "";
		for($i = 0; $i < sizeof($outputInfo); $i++) 
		{
			$returnStr .= $outputInfo[$i];
		}
		
		return $returnStr;
	}
	
	function checkAboutStack($type)
	{
		if (in_array($type, $this->aboutTypes)) 
		{
			$this->aboutTypes[sizeof($this->aboutTypes)] = $type;
			$this->aboutArray[sizeof($this->aboutArray)] = "######";
		}
				
	}
	
	function checkIdStack($type)
	{
		if (in_array($type, $this->idTypes)) 
		{
			$this->idTypes[sizeof($this->idTypes)] = $type;
			$this->idArray[sizeof($this->idArray)] = "######";
		}
				
	}
	
	
	function checkIfAttribute($attributes,$attribute) 
	{
		foreach($attributes as $key) 
		{
			if($key == $attribute) 
				return true;
		}
		
		return false;
	}
	
	function printStack() 
	{
		echo "<BR>";
		echo "[";
		for($i = 0; $i < sizeof($this->aboutTypes); $i++)
			echo $this->aboutTypes[$i] . ",";
		echo "]";
		echo "<BR>";
	}
	
	function getAboutAttribute() 
	{
		
		for($j = (sizeof($this->aboutTypes)-1); $j >= 0; $j--) 
		{
			if($this->aboutArray[$j] != "######")
				return $this->aboutArray[$j];
		}
		
		return null;
	}
	
	function getParentId() 
	{
		return $this->idArray[sizeof($this->idArray)-2];
	}	
	
	
	function deleteStackAttribute($attribute) 
	{
		$stop = false;
	
		for($j = (sizeof($this->aboutTypes)-1); $j >= 0 && !$stop; $j--) 
		{
			if($this->aboutTypes[$j] == $attribute)
			{
				unset($this->aboutArray[$j]);
				unset($this->aboutTypes[$j]);
				$stop = true;
			}
		}	
		
		$stop = false;
		
		for($j = (sizeof($this->idTypes)-1); $j >= 0 && !$stop; $j--) 
		{
			if($this->idTypes[$j] == $attribute)
			{
				unset($this->idArray[$j]);
				unset($this->idTypes[$j]);
				$stop = true;
			}
		}
		
	}
	
	function checkType($element) 
	{
		// if URL or empty
		if(substr($element, 0,7) == "http://" || $element == "")
		{
			$element = "<" . $element . ">";
		}
		if(isset($element[0]) && $element[0] == "#")
		{
			$element = "<" . $element . ">";
		}
	
		
		return $element;
	
	} 
	
	//////////// END PRIVATE FUNCTIONS ////////////////////////////////////////////

}


?>
