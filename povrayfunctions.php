<?php
function makeChunkInPovray($scenerydata,$filename,$chunkx,$chunky,$chunkz)
{
	$deltax=16*$chunkx;
	$deltay=16*$chunky;
	$deltaz=16*$chunkz;
	//$tresc='';
	if(true)//$scenerydata["bits"][8]!=0)
	{
		for($z=0;$z<16;$z++)
		{
			for($y=0;$y<16;$y++)
			{
				for($x=0;$x<16;$x++)
				{
					if($scenerydata[$x][$y][$z]["param0"]!='air')
					if($scenerydata[$x][$y][$z]["param0"]!='ignore')
					{
						$tresc="object { mycube translate <";
						file_put_contents($filename,$tresc,FILE_APPEND|LOCK_EX);
						$tresc=(string)($x+$deltax);
						file_put_contents($filename,$tresc,FILE_APPEND|LOCK_EX);
						$tresc=",";
						file_put_contents($filename,$tresc,FILE_APPEND|LOCK_EX);
						$tresc=(string)($z+$deltaz);
						file_put_contents($filename,$tresc,FILE_APPEND|LOCK_EX);
						$tresc=",";
						file_put_contents($filename,$tresc,FILE_APPEND|LOCK_EX);
						$tresc=(string)($y+$deltay);
						file_put_contents($filename,$tresc,FILE_APPEND|LOCK_EX);
						$tresc="> texture {";
						file_put_contents($filename,$tresc,FILE_APPEND|LOCK_EX);
						if($scenerydata[$x][$y][$z]["param0"]=="default:stone")
						{
							$tresc="T_Stone24";
						}
						elseif($scenerydata[$x][$y][$z]["param0"]=="default:sand")
						{
							$tresc="default_sand";
						}
						elseif($scenerydata[$x][$y][$z]["param0"]=="wool:blue")
						{
							$tresc="wool_blue";
						}
						elseif($scenerydata[$x][$y][$z]["param0"]=="wool:green")
						{
							$tresc="wool_green";
						}
						elseif($scenerydata[$x][$y][$z]["param0"]=="wool:yellow")
						{
							$tresc="wool_yellow";
						}
						elseif($scenerydata[$x][$y][$z]["param0"]=="wool:red")
						{
							$tresc="wool_red";
						}
						elseif($scenerydata[$x][$y][$z]["param0"]=="wool:cyan")
						{
							$tresc="wool_cyan";
						}
						elseif($scenerydata[$x][$y][$z]["param0"]=="wool:pink")
						{
							$tresc="wool_pink";
						}
						elseif($scenerydata[$x][$y][$z]["param0"]=="wool:brown")
						{
							$tresc="wool_brown";
						}
						elseif($scenerydata[$x][$y][$z]["param0"]=="wool:orange")
						{
							$tresc="wool_orange";
						}
						elseif($scenerydata[$x][$y][$z]["param0"]=="wool:magenta")
						{
							$tresc="wool_magenta";
						}
						elseif($scenerydata[$x][$y][$z]["param0"]=="technic:concrete")
						{
							$tresc="technic_concrete";
						}
						elseif($scenerydata[$x][$y][$z]["param0"]=="technic:stainless_steel_block")
						{
							$tresc="T_Chrome_3C normal{granite scale<100, 0.1, 0.1>}";
						}
						elseif($scenerydata[$x][$y][$z]["param0"]=="default:water")
						{
							$tresc="default_water";
						}
						elseif($scenerydata[$x][$y][$z]["param0"]=="default:water_source")
						{
							$tresc="default_water";
						}
						elseif($scenerydata[$x][$y][$z]["param0"]=="default:dirt_with_grass")
						{
							$tresc="default_dirt_with_grass";
						}
						elseif($scenerydata[$x][$y][$z]["param0"]=="default:dirt")
						{
							$tresc="default_dirt";
						}
						else
						{
							$tresc="pigment{ color rgbf<1,1,1,0.5>}";
						}
						file_put_contents($filename,$tresc,FILE_APPEND|LOCK_EX);
						$tresc="} }\n\n";
						file_put_contents($filename,$tresc,FILE_APPEND|LOCK_EX);
					}
				}
			}
		}
	}
}

?>
