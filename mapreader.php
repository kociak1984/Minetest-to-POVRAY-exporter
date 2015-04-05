<?php
class Mapreader
{
	private $data; // dane do obrobienia
	
	private $index=0; // indeks do walkera - ktora pare czytamy

	public $mapVersion; // wersja mapy
	private $bitmask=array(1=>0,2=>0,4=>0,8=>0); // flagi - is_underground, day_night_differs, lighting_expired, generated
	private $contentWidth; // szerokosc danych param0 wezla
	private $paramsWidth; // szerokosc danych wezlow
	private $databinary; // dane do obrobienia przekodowane na binarke
	private $gzipoffsets=array(); // offsety spakowanych blokow
	private $map=array();
	private $mappings=array();

	
	function __construct($input)
	{
		// konstruktor - bierze na siebie jedna linie (jeden chunk)
		$this->data=$input;
		$this->recodeInput();
		unset($input);
		$this->versionSet();
		$this->bitflagsSet();
		$this->contentwidthSet();
		$this->paramswidthSet();
		$this->gzipoffsets=$this->findGzipOffsets();
		$this->splitCompressedBlocks();
		//$this->exportScenery(); // do wywolania z zewnatrz
		//print_r($this->databinary);

/*
		print("\nwersja mapy: ".$this->mapVersion."\n");
		print("\nflagi: 1:".$this->bitmask[1]." 2:".$this->bitmask[2]." 4:".$this->bitmask[4]." 8:".$this->bitmask[8]."\n");
		print("\nwielkosc param0: ".$this->contentWidth."\n");
		print("\nwielkosc parametrow: ".$this->paramsWidth."\n");
*/
	}
	
	function __destruct()
	{
		unset($this->mapVersion);
		unset($this->index);
		unset($this->data);
	}
	
	private function readlen($input)
	{
		// podaj dlugosc ciagu w bajtach - 2 znaki hex
		return(strlen($input));
	}
	
	private function versionSet()
	{
		// ustawia zmienna wersji mapy
		$this->mapVersion=$this->readByte(0); // 0 to pozycja wersji
	}
	
	private function bitflagsSet()
	{
		$bf=$this->readByte(1); // 1 to pozycja flag
		$this->bitmask[1]=$bf&1;
		$this->bitmask[2]=($bf&2)>>1;
		$this->bitmask[4]=($bf&4)>>2;
		$this->bitmask[8]=($bf&8)>>3;
		unset($bf);
	}

	private function contentwidthSet()
	{
		// ustawia zmienna szerokosci param0
		$this->contentWidth=$this->readByte(2); // 0 to pozycja wersji
	}

	private function paramswidthSet()
	{
		// ustawia zmienna szerokosci parametrow
		$this->paramsWidth=$this->readByte(3); // 0 to pozycja wersji
	}
	
	private function findGzipOffsets()
	{
		$offset=array();
		$j=$this->readlen($this->data)-2;
		for($i=0;$i<$j;$i++)
		{
			if($this->readByte($i)==120) // 0x78
			{
				if($this->readByte($i+1)==156) // 0x9c
				{
					$offset[1]=$i;
					break;
				}
			}
		}
		$i++;
		for($k=$i;$k<$j;$k++)
		{
			if($this->readByte($k)==120)
			{
				if($this->readByte($k+1)==156)
				{
					$offset[2]=$k;
					break;
				}
			}
		}
		return($offset);
	}
	
	private function recodeInput()
	{
		$temp='';
		$j=$this->readlen($this->data);
		for($i=4;$i<$j;$i++)
		{
			$temp.=chr($this->readByte($i));
		}
		$this->databinary=$temp;
	}
	
	private function splitCompressedBlocks()
	{
		$temp='';
		$j=$this->readlen($this->data);
		$g1='/tmp/map';
		$tempfile=fopen($g1, "wb");
		$k=$this->gzipoffsets[2];
		for($i=4;$i<$k;$i++)
		{
			fwrite($tempfile,chr($this->readByte($i)));
			//pack('n',$this->readByte($i)));
		}
		fclose($tempfile);
		$g2='/tmp/dict';
		$tempfile=fopen($g2, "wb");
		$k=$this->gzipoffsets[2];
		for($i=$k;$i<$j;$i++)
		{
			fwrite($tempfile,chr($this->readByte($i)));
			//pack('n',$this->readByte($i)));
		}
		fclose($tempfile);
		
		system(dirname(__FILE__).'/unzlib.py');
		
		// wczytanie mapy
		$this->loadMap($g1.'.uncompressed',$g2.'.uncompressed','/tmp/leftover.uncompressed');
		
		//var_dump(gzinflate(readfile($g))); 
		
		/*$gz = gzopen($g, 'r');
		while (!gzeof($gz)) {
			echo gzgetc($gz).'.';
		}
		gzclose($gz);/**/
		//unlink($g);
	}
	
	private function loadMap($g1,$g2,$g3)
	{
		$mapfile=fopen($g1, "rb");
		// czytamy: 4096*u16, 4096*u8, 4096*u8
		// dane są msb-first
		for($i=0;$i<4096;$i++)
		{
			$this->map[$i]['param0']=ord(fread($mapfile,1))*256+ord(fread($mapfile,1));
		}
		for($i=0;$i<4096;$i++)
		{
			$this->map[$i]['param1']=ord(fread($mapfile,1));
		}
		for($i=0;$i<4096;$i++)
		{
			$this->map[$i]['param2']=ord(fread($mapfile,1));
		}
		fclose($mapfile);
		// wczytane, porzadkujemy

		$leftover=fopen($g3, "rb");
		fread($leftover,1); // wczytaj static_object_version (nie jest potrzebne)
		$socount=(ord(fread($leftover,1))*256)+ord(fread($leftover,1)); // ile jest static objects
		//print("\n\nilosc static objects: ".$socount."\n\n"); //
		while($socount!=0)
		{
			fread($leftover,13); // czytamy rekordy zeby sie przesunac w pliku
			$datasize=(ord(fread($leftover,1))*256)+ord(fread($leftover,1)); // czytamy datasize
			if($datasize!=0)
				fread($leftover,$datasize); // czytamy tyle bajtow ile bylo w datasize
			$socount--;
		}
		fread($leftover,4); // czytamy timestamp (i olewamy go)
		$ver=ord(fread($leftover,1));
		//print('wersja name-id-mapping: '.$ver."\n\n"); // czytamy name-id-mapping-version (z zalozenia zero)
		$numofmappings=(ord(fread($leftover,1))*256)+ord(fread($leftover,1)); // czytamy ile jest mapowan
		//print('znalazlem '.$numofmappings." mapowan\n\n");
		while($numofmappings!=0)
		{
			$id=(ord(fread($leftover,1))*256)+ord(fread($leftover,1)); // wczytujemy id mapowania
			$namelen=(ord(fread($leftover,1))*256)+ord(fread($leftover,1)); // wczytujemy dlugosc nazwy
			$this->mappings[$id]=fread($leftover,$namelen);
			$numofmappings--;
		}
		fclose($leftover);
		// zostalo: dict.uncompressed - napisy itp
		
		$this->matchMappings();
		
		
		//debug
		//print_r($this->mappings);
		//print_r($this->map);
	}
	
	private function matchMappings()
	{
		for($i=0;$i<4096;$i++)
		{
			$index=$this->map[$i]["param0"];
			$id=$this->mappings[$index];
			$this->map[$i]["param0"]=$id;
		}
		$x=0;
		$y=0;
		$z=0;
		for($x=0;$x<16;$x++)
		{
			for($y=0;$y<16;$y++)
			{
				for($z=0;$z<16;$z++)
				{
					$i=$x+($y*16)+($z*16*16);
					$this->map[$x][$y][$z]=$this->map[$i];
					//unset($this->map[$i]);
				}
			}
		}
	}
	
	function exportScenery()
	{
		//print_r($this->map);
		for($i=16;$i<4096;$i++)
		{
			unset($this->map[$i]);
		}
		$this->map["bits"][1]=$this->bitmask[1];
		$this->map["bits"][2]=$this->bitmask[2];
		$this->map["bits"][4]=$this->bitmask[4];
		$this->map["bits"][8]=$this->bitmask[8];
		return($this->map);
	}
	
	function exportMappings()
	{
		return($this->mappings);
	}

	private function readByte($i)
	{
		// zwroc bajt z pozycji danej przez index
		$a=$i;
		return(ord($this->data[$a]));
	}
	
}
?>
