# Introduction #

```
	require_once("/path/to/obVerify.php");
	
	$verify = new obVerify();
	
	// if you don't have curl installed then you must add this line:
	// $verify->use_curl = false;
	
	$verify->setFile('local file path');
	
	// returns true if the file validates
	// false if it does not
	$verify->analyze();
```

# Results #

## $verify->id3\_info ##
  * The array of results from getID3 are stored here

## $verify->copy\_info ##
  * ['verify\_url']	- Validation URL for the file (WOAF)
  * ['copyright'] - The Creative Commons License URL (WCOP)
  * ['notification'] - The Copyright Text (TCOP)
  * ['sha1'] - SHA1 Hash in base 32
  * ['verified'] - true if the id3 information validated with a web page
  * ['rdfa'] - the RDFa triple generated from the id3 information