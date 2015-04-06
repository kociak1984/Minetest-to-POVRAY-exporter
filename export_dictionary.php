<?php
/*
CAUTION!
This file is to be run once, only when you've got
a complete map as it overwrites blocks*.inc files!
It's purpose is to make initial files for you to modify.

Make sure to back up those after you've done some changes to them!
*/
include('mapreader.php'); // include map-parsing functions

// BEGIN CONFIG HERE
include('config.php');

$divider=100; // How many chunks to fetch at a time? Tinker with this to improve performance.
// END CONFIG HERE

$dictionary=array(); // we will store blocks' names here

$file1=dirname(__FILE__).'/blocks.inc';
$file2=dirname(__FILE__).'/blocks_textures.inc';
prepareFile($file1); // prepare those two suckers!
prepareFile($file2);

// Open the database connection
// Note: Can be sqlite3 functions here, but I'll stick with mysql. For reasons.
$conn=mysql_connect($mysqlConn['host'],$mysqlConn['user'],$mysqlConn['pass']);
mysql_select_db($mysqlConn['base']);

// How many chunks are there in the set?
$sql="select count(pos) as count_pos from ".$mysqlConn['table'];
$result=mysql_fetch_assoc(mysql_query($sql));
$count=$result["count_pos"];

$countDiv=(int)($count/$divider);

// Main loop
for($i=0;$i<=$countDiv;$i++)
{
	// Indices for the loop to use
	$start=$i*100;
	$end=$start+100;
	// Eyecandy line!
	print("Reading chunks ".$start." to ".$end." of ".$count." total\n".count($dictionary)." entries so far...\n");
	// Neat way of doing things. Read $divider number of chunks.
	$sql="select pos,data from ".$mysqlConn['table']." order by pos asc limit ".$divider." offset ".$start;
	$result=mysql_query($sql);
	while($row=mysql_fetch_assoc($result))
	{
		$decodedchunk=new Mapreader($row["data"]); // Pass the data to the parser
		$decodedchunk->exportScenery(); // Needed for the parser to run
		$mappings=$decodedchunk->exportMappings(); // Export mappings - nodes' names
		foreach($mappings as $row)
		{
			// Filter out some of them - they won't appear on the image either.
			if($row!="ignore")
			if($row!="air")
			{
				addEntry($row); // Add to the dictionary
			}
		}
		unset($decodedchunk); // Free some memory
	}
}

// Now we just write the files
$fhandler1=fopen($file1,"wb");
$fhandler2=fopen($file2,"wb");
foreach($dictionary as $row)
{
	// Povray-compatible syntax. Defaluts to a box with empty texture.
	fwrite($fhandler1,"#declare ".$row." = box { <0,0,0> <1,1,1> }\n");
	fwrite($fhandler2,"#declare texture_".$row." = \ntexture {\n\n}\n");
}
fclose($fhandler1);
fclose($fhandler2);

// Close the database
mysql_close($conn);

function addEntry($entry)
{
	global $dictionary;
	$data=str_replace(":","_",$entry); // Remove colon, as PovRAY could have problems with it
	$dictionary[$data]=$data; // Simplest possible way of writing the data without checking if it exists already
}

function prepareFile($file)
{
	unlink($file); // Here we jsut delete files and make new ones.
	$fhandler=fopen($file,"wb");
	fclose($fhandler);
}

?>
