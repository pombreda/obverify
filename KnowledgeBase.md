Recommendations and solutions to known issues will be posted here.

## Windows Hosts ##
  * The getID3 library requires some extra helper apps on a windows based hosting environment
  * Please read getid3-1.7/helperapps/readme.txt for more information
  * The required files are included in getid3-windowssupport-2003.12.29.zip

## Change getID3 Location ##
  * You may already have getID3 installed and not want to have another copy of it hanging around
  * For security reasons you may want to have the getID3 files located in a folder inaccessible from the web

Line 30 of obVerify.php contains the relative path to the getID3 library:

```
require_once("getid3-1.7/getid3/getid3.php");
```