<?php
//mb_internal_encoding("iso-8859-1");
include('mapreader.php');
include('povrayfunctions.php');

include('blockdictionary.php');

/*
kolejnosc:
u8 version
u8 flags
u8 content width
u8 param width

zlib:
u16[4096] param0
u8[4096] param1
u8[4096] param2

---
*/

include('config.php');

$sciezka=dirname(__FILE__).'/sceneria.inc';
prepareFile($sciezka);

$conn=mysql_connect($mysqlConn['host'],$mysqlConn['user'],$mysqlConn['pass']);
mysql_select_db($mysqlConn['base']);

for($x=-2;$x<6;$x++)
for($y=0;$y<16;$y++)
for($z=-14;$z<-2;$z++)
{
	print("szukam dla [x,y,z]= ".$x." , ".$y." , ".$z." (".combineCoordinates($x,$y,$z).")\n");
	$sql="select pos,data from ".$mysqlConn['table']." where pos=".combineCoordinates($x,$y,$z);
	$result=mysql_query($sql);
	while($row=mysql_fetch_assoc($result))
	{
		print("Znalazlem chunka na pozycji ".$row["pos"]."\n");
		$tab=parseCoordinates($row["pos"]);
		$zdekodowanychunk=new Mapreader($row["data"]);
		$sceneria=$zdekodowanychunk->exportScenery();
		unset($zdekodowanychunk);
		//print_r($sceneria);
		makeChunkInPovray($sceneria,$sciezka,$tab["x"],$tab["y"],$tab["z"]);
	}
}
mysql_close($conn);



/*
//$data=$row['data'];
$klasa=new Mapreader($data);


unset($klasa);
*/



function prepareFile($file)
{
	$plik=fopen($file,"wb");
	fclose($plik);
}

function combineCoordinates($x,$y,$z)
{
	return((int)int64(bcadd(bcmul($z,16777216),bcadd(bcmul($y,4096),$x))));
}

function int64($i)
{
	if(bccomp($i,bcpow(2,63))>=0)
	{
		$i=bcsub($i,bcpow(2,64));
	}
	if(bccomp($i,bcpow(-2,63))<=0)
	{
		$i=bcadd($i,bcpow(2,64));
	}
	return($i);
}

function parseCoordinates($i)
{
  $result=system(dirname(__FILE__).'/unpack_coords.py'.' '.$i);
  $ret=explode(' ',$result);
  $ret['x']=$ret[0];
  $ret['y']=$ret[1];
  $ret['z']=$ret[2];
  return($ret);
}

/*
function parseCoordinates($i)
{
  $x=unsignedToSigned($i%4096,2048);
  $i=(int)(($i-$x)/4096);
  $y=unsignedToSigned($i%4096,2048);
  $i=(int)(($i-$y)/4096);
  $z=unsignedToSigned($i%4096,2048);
  $ret=array();
  $ret['x']=$x;
  $ret['y']=$y;
  $ret['z']=$z;
  return($ret);
}
*/
function unsignedToSigned($i,$max_positive)
{
  if(bccomp($i,$max_positive)<0)
  {
    return($i);
  }
  else
  {
    return(bcsub($i,bcmul(2,$max_positive)));
  }
}
/*
 def getIntegerAsBlock(i):
x = unsignedToSigned(i % 4096, 2048)
i = int((i - x) / 4096)
y = unsignedToSigned(i % 4096, 2048)
i = int((i - y) / 4096)
z = unsignedToSigned(i % 4096, 2048)
return x,y,z
* 
def unsignedToSigned(i, max_positive):
if i < max_positive:
return i
else:
return i - 2*max_positive
*/
/*
$inty=array();
$conn=mysql_connect("redacted","redacted","redacted");
mysql_select_db("redacted");
$sql="select id,pos from blocks";
$result=mysql_query($sql);
while($row=mysql_fetch_assoc($result))
{
  $tab=getIntegerAsBlock($row["pos"]);
  $sql="update blocks set x=".$tab['x'].",y=".$tab['y'].",z=".$tab['z']." where id=".$row["id"];
  mysql_query($sql);
}
mysql_close($conn);
*/

?>
