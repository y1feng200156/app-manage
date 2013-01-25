<?php

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if (!defined('RELATIVE_PATH')) define('RELATIVE_PATH', S_ROOT.'./class/');
if (!defined('FPDF_FONTPATH')) define('FPDF_FONTPATH', S_ROOT.'./class/font/');
if (!defined('RTOOL_PATH')) define('RTOOL_PATH', S_ROOT.'./include/');

include_once(RELATIVE_PATH.'pdf.class.php');
include_once(S_ROOT.'./function/pdftoolkit.func.php');

$Big5_widths=array(' '=>250,'!'=>250,'"'=>408,'#'=>668,'$'=>490,'%'=>875,'&'=>698,'\''=>250,
	'('=>240,')'=>240,'*'=>417,'+'=>667,','=>250,'-'=>313,'.'=>250,'/'=>520,'0'=>500,'1'=>500,
	'2'=>500,'3'=>500,'4'=>500,'5'=>500,'6'=>500,'7'=>500,'8'=>500,'9'=>500,':'=>250,';'=>250,
	'<'=>667,'='=>667,'>'=>667,'?'=>396,'@'=>921,'A'=>677,'B'=>615,'C'=>719,'D'=>760,'E'=>625,
	'F'=>552,'G'=>771,'H'=>802,'I'=>354,'J'=>354,'K'=>781,'L'=>604,'M'=>927,'N'=>750,'O'=>823,
	'P'=>563,'Q'=>823,'R'=>729,'S'=>542,'T'=>698,'U'=>771,'V'=>729,'W'=>948,'X'=>771,'Y'=>677,
	'Z'=>635,'['=>344,'\\'=>520,']'=>344,'^'=>469,'_'=>500,'`'=>250,'a'=>469,'b'=>521,'c'=>427,
	'd'=>521,'e'=>438,'f'=>271,'g'=>469,'h'=>531,'i'=>250,'j'=>250,'k'=>458,'l'=>240,'m'=>802,
	'n'=>531,'o'=>500,'p'=>521,'q'=>521,'r'=>365,'s'=>333,'t'=>292,'u'=>521,'v'=>458,'w'=>677,
	'x'=>479,'y'=>458,'z'=>427,'{'=>480,'|'=>496,'}'=>480,'~'=>667);

$GB_widths=array(' '=>207,'!'=>270,'"'=>342,'#'=>467,'$'=>462,'%'=>797,'&'=>710,'\''=>239,
	'('=>374,')'=>374,'*'=>423,'+'=>605,','=>238,'-'=>375,'.'=>238,'/'=>334,'0'=>462,'1'=>462,
	'2'=>462,'3'=>462,'4'=>462,'5'=>462,'6'=>462,'7'=>462,'8'=>462,'9'=>462,':'=>238,';'=>238,
	'<'=>605,'='=>605,'>'=>605,'?'=>344,'@'=>748,'A'=>684,'B'=>560,'C'=>695,'D'=>739,'E'=>563,
	'F'=>511,'G'=>729,'H'=>793,'I'=>318,'J'=>312,'K'=>666,'L'=>526,'M'=>896,'N'=>758,'O'=>772,
	'P'=>544,'Q'=>772,'R'=>628,'S'=>465,'T'=>607,'U'=>753,'V'=>711,'W'=>972,'X'=>647,'Y'=>620,
	'Z'=>607,'['=>374,'\\'=>333,']'=>374,'^'=>606,'_'=>500,'`'=>239,'a'=>417,'b'=>503,'c'=>427,
	'd'=>529,'e'=>415,'f'=>264,'g'=>444,'h'=>518,'i'=>241,'j'=>230,'k'=>495,'l'=>228,'m'=>793,
	'n'=>527,'o'=>524,'p'=>524,'q'=>504,'r'=>338,'s'=>336,'t'=>277,'u'=>517,'v'=>450,'w'=>652,
	'x'=>466,'y'=>452,'z'=>407,'{'=>370,'|'=>258,'}'=>370,'~'=>605);

class cpdf extends PDF {
	//internal attributes
	var $HREF; //! string
	var $pgwidth; //! float
	var $fontlist; //! array 
	var $issetfont; //! bool
	var $issetcolor; //! bool
	var $titulo; //! string
	var $oldx; //! float
	var $oldy; //! float
	var $B; //! int
	var $U; //! int
	var $I; //! int

	var $tablestart; //! bool
	var $tdbegin; //! bool
	var $table; //! array
	var $cell; //! array 
	var $col; //! int
	var $row; //! int

	var $divbegin; //! bool
	var $divalign; //! char
	var $divwidth; //! float
	var $divheight; //! float
	var $divbgcolor; //! bool
	var $divcolor; //! bool
	var $divborder; //! int
	var $divrevert; //! bool

	var $listlvl; //! int
	var $listnum; //! int
	var $listtype; //! string
	//array(lvl,# of occurrences)
	var $listoccur; //! array
	//array(lvl,occurrence,type,maxnum)
	var $listlist; //! array
	//array(lvl,num,content,type)
	var $listitem; //! array

	var $buffer_on; //! bool
	var $pbegin; //! bool
	var $pjustfinished; //! bool
	var $blockjustfinished; //! bool
	var $SUP; //! bool
	var $SUB; //! bool
	var $toupper; //! bool
	var $tolower; //! bool
	var $dash_on; //! bool
	var $dotted_on; //! bool
	var $strike; //! bool

	var $CSS; //! array
	var $cssbegin; //! bool
	var $backupcss; //! array
	var $textbuffer; //! array
	var	$currentstyle; //! string
	var $currentfont; //! string
	var $colorarray; //! array
	var $bgcolorarray; //! array
	var $internallink; //! array
	var $enabledtags; //! string

	var $lineheight; //! int
	var $basepath; //! string
	// array('COLOR','WIDTH','OLDWIDTH')
	var $outlineparam; //! array
	var $outline_on; //! bool

	var $specialcontent; //! string
	var $selectoption; //! array

	//options attributes
	var $usecss; //! bool
	var $usepre; //! bool
	var $usetableheader; //! bool
	var $shownoimg; //! bool
	var $footerarr;
	var $headerarr;
	var $PDFFontFamily;


	function GetStringWidth($s) {
		if($this->CurrentFont['type']=='Type0')
			return $this->GetMBStringWidth($s);
		else
			return parent::GetStringWidth($s);
	}

	function GetMBStringWidth($s) {
		//Multi-byte version of GetStringWidth()
		$l=0;
		$cw=&$this->CurrentFont['cw'];
		$nb=strlen($s);
		$i=0;
		while($i<$nb) {
			$c=$s[$i];
			if(ord($c)<128) {
				$l+=$cw[$c];
				$i++;
			} else {
				$l+=1000;
				$i+=2;
			}
		}
		return $l*$this->FontSize/1000;
	}

	function MultiCell($w,$h,$txt,$border=0,$align='L',$fill=0) {
		if($this->CurrentFont['type']=='Type0')
			$this->MBMultiCell($w,$h,$txt,$border,$align,$fill);
		else
			parent::MultiCell($w,$h,$txt,$border,$align,$fill);
	}

	function MBMultiCell($w,$h,$txt,$border=0,$align='L',$fill=0) {
		//Multi-byte version of MultiCell()
		$cw=&$this->CurrentFont['cw'];
		if($w==0)
			$w=$this->w-$this->rMargin-$this->x;
		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
		if($nb>0 and $s[$nb-1]=="\n")
			$nb--;
		$b=0;
		if($border) {
			if($border==1) {
				$border='LTRB';
				$b='LRT';
				$b2='LR';
			} else {
				$b2='';
				if(is_int(strpos($border,'L')))
					$b2.='L';
				if(is_int(strpos($border,'R')))
					$b2.='R';
				$b=is_int(strpos($border,'T')) ? $b2.'T' : $b2;
			}
		}
		$sep=-1;
		$i=0;
		$j=0;
		$l=0;
		$ns=0;
		$nl=1;
		while($i<$nb) {
			//Get next character
			$c=$s[$i];
			//Check if ASCII or MB
			$ascii=(ord($c)<128);
			if($c=="\n") {
				//Explicit line break
				if($this->ws>0) {
					$this->ws=0;
					$this->_out('0 Tw');
				}
				$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
				$i++;
				$sep=-1;
				$j=$i;
				$l=0;
				$ns=0;
				$nl++;
				if($border and $nl==2)
					$b=$b2;
				continue;
			}
			if(!$ascii) {
				$sep=$i;
				$ls=$l;
			} elseif($c==' ') {
				$sep=$i;
				$ls=$l;
				$ns++;
			}
			$l+=$ascii ? $cw[$c] : 1000;
			if($l>$wmax) {
				//Automatic line break
				if($sep==-1 or $i==$j) {
					if($i==$j)
						$i+=$ascii ? 1 : 2;
					if($this->ws>0) {
						$this->ws=0;
						$this->_out('0 Tw');
					}
					$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
				} else {
					if($align=='J') {
						if($s[$sep]==' ')
							$ns--;
						if($s[$i-1]==' ') {
							$ns--;
							$ls-=$cw[' '];
						}
						$this->ws=($ns>0) ? ($wmax-$ls)/1000*$this->FontSize/$ns : 0;
						$this->_out(sprintf('%.3f Tw',$this->ws*$this->k));
					}
					$this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
					$i=($s[$sep]==' ') ? $sep+1 : $sep;
				}
				$sep=-1;
				$j=$i;
				$l=0;
				$ns=0;
				$nl++;
				if($border and $nl==2)
					$b=$b2;
			} else $i+=$ascii ? 1 : 2;
		}
		//Last chunk
		if($this->ws>0) {
			$this->ws=0;
			$this->_out('0 Tw');
		}
		if($border and is_int(strpos($border,'B')))
			$b.='B';
		$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
		$this->x=$this->lMargin;
	}

	function Write($h,$txt,$link='') {
		if($this->CurrentFont['type']=='Type0') {
			/*
			$params = '/[\x00-\x7f]+/';
			preg_match_all($params, $txt, $outfix, PREG_PATTERN_ORDER);
			foreach($outfix[0] as $key => $val) {
				$tmp = explode($val, $txt);
				$this->SetFont($this->PDFFontFamily, $this->FontStyle, $this->FontSizePt);
				$this->MBWrite($h,$tmp[0],$link);
				$this->SetFont('Arial', $this->FontStyle, $this->FontSizePt);
				$this->MBWrite($h,$val,$link);
				$position = 0;
				if(! ($position = strpos($txt, $val))) {
					$position = 0;
				}
				$txt = isset($txt) && $txt!='' ? substr($txt, $position + strlen($val)) : '';
				$this->SetFont($this->PDFFontFamily, $this->FontStyle, $this->FontSizePt);
			}
			if($txt != '') {
				$this->SetFont($this->PDFFontFamily, $this->FontStyle, $this->FontSizePt);
				$this->MBWrite($h,$txt,$link);
			}
*/
			$this->MBWrite($h,$txt,$link);
		} else {
			parent::Write($h,$txt,$link);
		}
	}

	function MBWrite($h,$txt,$link) {
		//Multi-byte version of Write()
		$cw=&$this->CurrentFont['cw'];
		$w=$this->w-$this->rMargin-$this->x;
		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
		$sep=-1;
		$i=0;
		$j=0;
		$l=0;
		$nl=1;
		while($i<$nb) {
			//Get next character
			$c=$s[$i];
			//Check if ASCII or MB
			$ascii=(ord($c)<128);
			if($c=="\n") {
				//Explicit line break
				$this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',0,$link);
				$i++;
				$sep=-1;
				$j=$i;
				$l=0;
				if($nl==1) {
					$this->x=$this->lMargin;
					$w=$this->w-$this->rMargin-$this->x;
					$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
				}
				$nl++;
				continue;
			}
			if(!$ascii or $c==' ')
				$sep=$i;
			$l+=$ascii ? $cw[$c] : 1000;
			if($l>$wmax) {
				//Automatic line break
				if($sep==-1 or $i==$j) {
					if($this->x>$this->lMargin) {
						//Move to next line
						$this->x=$this->lMargin;
						$this->y+=$h;
						$w=$this->w-$this->rMargin-$this->x;
						$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
						$i++;
						$nl++;
						continue;
					}
					if($i==$j)
						$i+=$ascii ? 1 : 2;
					$this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',0,$link);
				} else {
					$this->Cell($w,$h,substr($s,$j,$sep-$j),0,2,'',0,$link);
					$i=($s[$sep]==' ') ? $sep+1 : $sep;
				}
				$sep=-1;
				$j=$i;
				$l=0;
				if($nl==1) {
					$this->x=$this->lMargin;
					$w=$this->w-$this->rMargin-$this->x;
					$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
				}
				$nl++;
			} else $i+=$ascii ? 1 : 2;
		}
		//Last chunk
		if($i!=$j)
			$this->Cell($l/1000*$this->FontSize,$h,substr($s,$j,$i-$j),0,0,'',0,$link);
	}

	function AddCIDFont($family,$style,$name,$cw,$CMap,$registry) {
		$i=count($this->fonts)+1;
		$fontkey=strtolower($family).strtoupper($style);
		$this->fonts[$fontkey]=array('i'=>$i,'type'=>'Type0','name'=>$name,'up'=>-120,'ut'=>40,'cw'=>$cw,'CMap'=>$CMap,'registry'=>$registry);
	}

	function AddBig5Font($family='Big5') {
		$cw=$GLOBALS['Big5_widths'];
		$name='MSungStd-Light-Acro';
		$CMap='ETenms-B5-H';
		$registry=array('ordering'=>'CNS1','supplement'=>0);
		$this->AddCIDFont($family,'',$name,$cw,$CMap,$registry);
		$this->AddCIDFont($family,'B',$name.',Bold',$cw,$CMap,$registry);
		$this->AddCIDFont($family,'I',$name.',Italic',$cw,$CMap,$registry);
		$this->AddCIDFont($family,'BI',$name.',BoldItalic',$cw,$CMap,$registry);
	}

	function AddGBFont($family='GB', $name='STSongStd-Light-Acro') {
		$cw=$GLOBALS['GB_widths'];
		//$name='STSongStd-Light-Acro';
		$CMap='GBKp-EUC-H';
		$registry=array('ordering'=>'GB1','supplement'=>2);
		$this->AddCIDFont($family,'',$name,$cw,$CMap,$registry);
		$this->AddCIDFont($family,'B',$name.',Bold',$cw,$CMap,$registry);
		$this->AddCIDFont($family,'I',$name.',Italic',$cw,$CMap,$registry);
		$this->AddCIDFont($family,'BI',$name.',BoldItalic',$cw,$CMap,$registry);
	}

	function _putfonts() {
		$nf=$this->n;
		foreach($this->diffs as $diff) {
			//Encodings
			$this->_newobj();
			$this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$diff.']>>');
			$this->_out('endobj');
		}
		$mqr=get_magic_quotes_runtime();
		set_magic_quotes_runtime(0);
		foreach($this->FontFiles as $file=>$info) {
			//Font file embedding
			$this->_newobj();
			$this->FontFiles[$file]['n']=$this->n;
			if(defined('FPDF_FONTPATH'))
				$file=FPDF_FONTPATH.$file;
			$size=filesize($file);
			if(!$size)
				$this->Error('Font file not found');
			$this->_out('<</Length '.$size);
			if(substr($file,-2)=='.z')
				$this->_out('/Filter /FlateDecode');
			$this->_out('/Length1 '.$info['length1']);
			if(isset($info['length2']))
				$this->_out('/Length2 '.$info['length2'].' /Length3 0');
			$this->_out('>>');
			$f=fopen($file,'rb');
			$this->_putstream(fread($f,$size));
			fclose($f);
			$this->_out('endobj');
		}
		set_magic_quotes_runtime($mqr);
		foreach($this->fonts as $k=>$font) {
			//Font objects
			$this->_newobj();
			$this->fonts[$k]['n']=$this->n;
			$this->_out('<</Type /Font');
			if($font['type']=='Type0') {
				$this->_putType0($font);
			} else {
				$name=$font['name'];
				$this->_out('/BaseFont /'.$name);
				if($font['type']=='core') {
					//Standard font
					$this->_out('/Subtype /Type1');
					if($name!='Symbol' and $name!='ZapfDingbats')
						$this->_out('/Encoding /WinAnsiEncoding');
				} else {
					//Additional font
					$this->_out('/Subtype /'.$font['type']);
					$this->_out('/FirstChar 32');
					$this->_out('/LastChar 255');
					$this->_out('/Widths '.($this->n+1).' 0 R');
					$this->_out('/FontDescriptor '.($this->n+2).' 0 R');
					if($font['enc']) {
						if(isset($font['diff']))
							$this->_out('/Encoding '.($nf+$font['diff']).' 0 R');
						else
							$this->_out('/Encoding /WinAnsiEncoding');
					}
				}
				$this->_out('>>');
				$this->_out('endobj');
				if($font['type']!='core') {
					//Widths
					$this->_newobj();
					$cw=&$font['cw'];
					$s='[';
					for($i=32;$i<=255;$i++)
						$s.=$cw[chr($i)].' ';
					$this->_out($s.']');
					$this->_out('endobj');
					//Descriptor
					$this->_newobj();
					$s='<</Type /FontDescriptor /FontName /'.$name;
					foreach($font['desc'] as $k=>$v)
						$s.=' /'.$k.' '.$v;
					$file=$font['file'];
					if($file)
						$s.=' /FontFile'.($font['type']=='Type1' ? '' : '2').' '.$this->FontFiles[$file]['n'].' 0 R';
					$this->_out($s.'>>');
					$this->_out('endobj');
				}
			}
		}
	}

	function _putType0($font) {
		//Type0
		$this->_out('/Subtype /Type0');
		$this->_out('/BaseFont /'.$font['name'].'-'.$font['CMap']);
		$this->_out('/Encoding /'.$font['CMap']);
		$this->_out('/DescendantFonts ['.($this->n+1).' 0 R]');
		$this->_out('>>');
		$this->_out('endobj');
		//CIDFont
		$this->_newobj();
		$this->_out('<</Type /Font');
		$this->_out('/Subtype /CIDFontType0');
		$this->_out('/BaseFont /'.$font['name']);
		$this->_out('/CIDSystemInfo <</Registry (Adobe) /Ordering ('.$font['registry']['ordering'].') /Supplement '.$font['registry']['supplement'].'>>');
		$this->_out('/FontDescriptor '.($this->n+1).' 0 R');
		$W='/W [1 [';
		foreach($font['cw'] as $w)
			$W.=$w.' ';
		$this->_out($W.']]');
		$this->_out('>>');
		$this->_out('endobj');
		//Font descriptor
		$this->_newobj();
		$this->_out('<</Type /FontDescriptor');
		$this->_out('/FontName /'.$font['name']);
		$this->_out('/Flags 6');
		$this->_out('/FontBBox [0 0 1000 1000]');
		$this->_out('/ItalicAngle 0');
		$this->_out('/Ascent 1000');
		$this->_out('/Descent 0');
		$this->_out('/CapHeight 1000');
		$this->_out('/StemV 10');
		$this->_out('>>');
		$this->_out('endobj');
	}

	function cpdf($orientation='P',$unit='mm',$format='A4') {
		//! @desc Constructor
		//! @return An object (a class instance)
		//Call parent constructor
		$this->PDF($orientation,$unit,$format);
		//To make the function Footer() work properly
		$this->AliasNbPages();
		//Enable all tags as default
		$this->DisableTags();
		//Set default display preferences
		$this->DisplayPreferences('');
		//Initialization of the attributes
		$this->SetFont('Arial','',11); // Changeable?(not yet...)
		$this->lineheight = 5; // Related to FontSizePt == 11
		$this->pgwidth = $this->fw - $this->lMargin - $this->rMargin ;
		$this->SetFillColor(255);
		$this->HREF='';
		$this->titulo='';
		$this->oldx=-1;
		$this->oldy=-1;
		$this->B=0;
		$this->U=0;
		$this->I=0;

		$this->listlvl=0;
		$this->listnum=0; 
		$this->listtype='';
		$this->listoccur=array();
		$this->listlist=array();
		$this->listitem=array();

		$this->tablestart=false;
		$this->tdbegin=false; 
		$this->table=array(); 
		$this->cell=array();	
		$this->col=-1; 
		$this->row=-1; 

		$this->divbegin=false;
		$this->divalign="L";
		$this->divwidth=0; 
		$this->divheight=0; 
		$this->divbgcolor=false;
		$this->divcolor=false;
		$this->divborder=0;
		$this->divrevert=false;

		$this->fontlist=array("arial","times","courier","helvetica","symbol","monospace","serif","sans");
		$this->issetfont=false;
		$this->issetcolor=false;

		$this->pbegin=false;
		$this->pjustfinished=false;
		$this->blockjustfinished = true; //in order to eliminate exceeding left-side spaces
		$this->toupper=false;
		$this->tolower=false;
		$this->dash_on=false;
		$this->dotted_on=false;
		$this->SUP=false;
		$this->SUB=false;
		$this->buffer_on=false;
		$this->strike=false;

		$this->currentfont='';
		$this->currentstyle='';
		$this->colorarray=array();
		$this->bgcolorarray=array();
		$this->cssbegin=false;
		$this->textbuffer=array();
		$this->CSS=array();
		$this->backupcss=array();
		$this->internallink=array();

		$this->basepath = "";
		
		$this->outlineparam = array();
		$this->outline_on = false;

		$this->specialcontent = '';
		$this->selectoption = array();

		$this->shownoimg=false;
		$this->usetableheader=false;
		$this->usecss=true;
		$this->usepre=true;
		$this->footercontent = array();
		$this->PDFFontFamily = '';
	}
	/**
	 * The directory where this script is
	 */
	function setBasePath($str) {
		$this->basepath = dirname($str) . "/";
		$this->basepath = str_replace("\\","/",$this->basepath); //If on Windows
	}

	function ShowNOIMG_GIF($opt=true) {
		//! @desc Enable/Disable Displaying the no_img.gif when an image is not found. No-Parameter: Enable
		//! @return void
		$this->shownoimg=$opt;
	}

	function UseCSS($opt=true) {
		//! @desc Enable/Disable CSS recognition. No-Parameter: Enable
		//! @return void
		$this->usecss=$opt;
	}

	function UseTableHeader($opt=true) {
	//! @desc Enable/Disable Table Header to appear every new page. No-Parameter: Enable
	//! @return void
		$this->usetableheader=$opt;
	}

	function UsePRE($opt=true) {
	//! @desc Enable/Disable pre tag recognition. No-Parameter: Enable
	//! @return void
		$this->usepre=$opt;
	}

	//Page header
	function Header($content='') {
	//! @return void
	//! @desc The header is printed in every page.
		//Arial italic 9
		$this->SetXY(6,2);
		$this->SetFont($this->PDFFontFamily,'B',10);
		$this->Write(8,$this->headerarr['left']);
		$this->SetX(($this->w-$this->rMargin-$this->x)/2);
		$this->SetFont($this->PDFFontFamily,'B',11);
		$this->Write(8,$this->headerarr['center']);
		$this->SetFont($this->PDFFontFamily,'B',10);
		$hw=$this->GetStringWidth($this->headerarr['rigth'])+13;
		$this->SetX(-$hw);
		$this->Write(8,$this->headerarr['rigth']);
		$this->Line(3,10,206,10);
	}

	//Page footer
	function Footer() {
	//! @return void
	//! @desc The footer is printed in every page!
			//Position at 1.0 cm from bottom
			$this->Line(3,287,206,287);
			$this->SetY(-10);
			//Copyright //especial para esta vers鉶
			$this->SetFont('Arial','B',9);
			$this->SetTextColor(0);
			//Arial italic 9
			$this->SetFont($this->PDFFontFamily,'B',9);
			
			$this->Write(8,$this->footerarr['left']);
			$this->SetFont('Arial','I',9);
			//Page number
			$this->Cell(70,10,$this->PageNo().'/{nb}',0,0,'C');
			$this->SetFont($this->PDFFontFamily,'B',9);
			$w=$this->GetStringWidth($this->footerarr['right'])+13;
			$this->SetX(-$w);
			$this->Write(8,$this->footerarr['right']);
	}

	///////////////////
	/// HTML parser ///
	///////////////////
	function WriteHTML($html) {
	//! @desc HTML parser
	//! @return void
	/* $e == content */
	
		$this->ReadMetaTags($html);
		$html = AdjustHTML($html,$this->usepre); //Try to make HTML look more like XHTML
		if ($this->usecss) $html = $this->ReadCSS($html);
		//Add new supported tags in the DisableTags function
		$html=str_replace('<?','< ',$html); //Fix '<?XML' bug from HTML code generated by MS Word
		$html=strip_tags($html,$this->enabledtags); //remove all unsupported tags, but the ones inside the 'enabledtags' string
		$a=preg_split('/<(.*?)>/mis',$html,-1,PREG_SPLIT_DELIM_CAPTURE);

		foreach($a as $i => $e) {
			if($i%2==0) {
				//TEXT
				//Adjust lineheight
				//			$this->lineheight = (5*$this->FontSizePt)/11; //should be inside printbuffer?
				//Adjust text, if needed
				if (strpos($e,"&") !== false) { //HTML-ENTITIES decoding
					if (strpos($e,"#") !== false) $e = value_entity_decode($e); // Decode value entities
					//Avoid crashing the script on PHP 4.0
					$version = phpversion();
					$version = str_replace('.','',$version);
					if ($version >= 430) $e = html_entity_decode($e,ENT_QUOTES,'cp1252'); // changes &nbsp; and the like by their respective char
					else $e = lesser_entity_decode($e);
				}
				$e = str_replace(chr(160),chr(32),$e); //unify ascii code of spaces (in order to recognize all of them correctly)
				if (strlen($e) == 0) continue;
				if ($this->divrevert) $e = strrev($e);
				if ($this->toupper) $e = strtoupper($e);
				if ($this->tolower) $e = strtolower($e);
				//Start of 'if/elseif's
				
				if($this->titulo) $this->SetTitle($e);
				elseif($this->specialcontent) {
					if ($this->specialcontent == "type=select" and $this->selectoption['ACTIVE'] == true) {	//SELECT tag (form element)
						$stringwidth = $this->GetStringWidth($e);
						if (!isset($this->selectoption['MAXWIDTH']) or $stringwidth > $this->selectoption['MAXWIDTH']) $this->selectoption['MAXWIDTH'] = $stringwidth;
						if (!isset($this->selectoption['SELECTED']) or $this->selectoption['SELECTED'] == '') $this->selectoption['SELECTED'] = $e;
					} else $this->textbuffer[] = array("|x%s#pa@c!e|"/*identifier*/.$this->specialcontent."|x%s#pa@c!e|".$e);

				} elseif($this->tablestart) {
					
					if($this->tdbegin) {
						$this->cell[$this->row][$this->col]['textbuffer'][] = array($e,$this->HREF,$this->currentstyle,$this->colorarray,$this->currentfont,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->bgcolorarray);
						$this->cell[$this->row][$this->col]['text'][] = $e;
						if(isset($this->cell[$this->row][$this->col]['s'])) {
							$this->cell[$this->row][$this->col]['s'] += $this->GetStringWidth($e);
						} else {
							$this->cell[$this->row][$this->col]['s'] = $this->GetStringWidth($e);
						}
							
					}
						//Ignore content between <table>,<tr> and a <td> tag (this content is usually only a bunch of spaces)
				} elseif($this->pbegin or $this->HREF or $this->divbegin or $this->SUP or $this->SUB or $this->strike or $this->buffer_on) {
						
					$this->textbuffer[] = array($e,$this->HREF,$this->currentstyle,$this->colorarray,$this->currentfont,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->bgcolorarray); //Accumulate text on buffer

				} else {
					//if ($this->blockjustfinished) $e = ltrim($e);
					if ($e != '') {

						$this->SetFont($this->PDFFontFamily, $this->FontStyle, $this->FontSizePt);
						$this->Write($this->lineheight, $e);
						if ($this->pjustfinished) $this->pjustfinished = false;
					}
				}
			} else {
				//Tag
				if($e{0}=='/') {
					//echo strtoupper(substr($e,1))."<br>";
					$this->CloseTag(strtoupper(substr($e,1)));
				} else {
					$regexp = '|=\'(.*?)\'|s'; // eliminate single quotes, if any
					$e = preg_replace($regexp,"=\"\$1\"",$e);
					$regexp = '| (\\w+?)=([^\\s>"]+)|si'; // changes anykey=anyvalue to anykey="anyvalue" (only do this when this happens inside tags)
					$e = preg_replace($regexp," \$1=\"\$2\"",$e);
					//Fix path values, if needed
					if ((stristr($e,"href=") !== false) or (stristr($e,"src=") !== false) ) {
						$regexp = '/ (href|src)="(.*?)"/i';
						preg_match($regexp,$e,$auxiliararray);
						$path = $auxiliararray[2];
						$path = str_replace("\\","/",$path); //If on Windows
						//Get link info and obtain its absolute path
						$regexp = '|^./|';
						$path = preg_replace($regexp,'',$path);
						if($path{0} != '#') {	//It is not an Internal Link
							if (strpos($path,"../") !== false ) {	//It is a Relative Link
								$backtrackamount = substr_count($path,"../");
								$maxbacktrack = substr_count($this->basepath,"/") - 1;
								$filepath = str_replace("../",'',$path);
								$path = $this->basepath;
								//If it is an invalid relative link, then make it go to directory root
								if ($backtrackamount > $maxbacktrack) $backtrackamount = $maxbacktrack;
								//Backtrack some directories
								for( $i = 0 ; $i < $backtrackamount + 1 ; $i++ ) $path = substr( $path, 0 , strrpos($path,"/") );
								$path = $path . "/" . $filepath; //Make it an absolute path
							} elseif( strpos($path,":/") === false) {	//It is a Local Link
								$path = $this->basepath . $path; 
							}
								//Do nothing if it is an Absolute Link
						}
						$regexp = '/ (href|src)="(.*?)"/i';
						$e = preg_replace($regexp,' \\1="'.$path.'"',$e);
					}//END of Fix path values
					//Extract attributes
					$contents=array();
					preg_match_all('/\\S*=["\'][^"\']*["\']/',$e,$contents);
					preg_match('/\\S+/',$e,$a2);
					$tag=strtoupper($a2[0]);
					$attr=array();
					if (!empty($contents)) {
						foreach($contents[0] as $v) {
							if(ereg('^([^=]*)=["\']?([^"\']*)["\']?$',$v,$a3)) {
								$attr[strtoupper($a3[1])]=$a3[2];
							}
						}
					}
					$this->OpenTag($tag,$attr);
				}
			}
		}//end of	foreach($a as $i=>$e)
		//Create Internal Links, if needed
		if (!empty($this->internallink) )
		{
			foreach($this->internallink as $k=>$v)
			{
				if (strpos($k,"#") !== false ) continue; //ignore
				$ypos = $v['Y'];
				$pagenum = $v['PAGE'];
				$sharp = "#";
				while (array_key_exists($sharp.$k,$this->internallink))
				{
					 $internallink = $this->internallink[$sharp.$k];
					 $this->SetLink($internallink,$ypos,$pagenum);
					 $sharp .= "#";
				}
			}
		}
	}
	
	/**
	 *What this gets: < $tag $attr['WIDTH']="90px" > does not get content here </closeTag here>
	 *
	 */
	function OpenTag($tag,$attr) {

		$align = array('left'=>'L','center'=>'C','right'=>'R','top'=>'T','middle'=>'M','bottom'=>'B','justify'=>'J');

		$this->blockjustfinished=false;
		//Opening tag
		switch($tag) {
			case 'PAGE_BREAK': //custom-tag
			case 'NEWPAGE': //custom-tag
				$this->blockjustfinished = true;
				$this->AddPage();
				break;
			case 'OUTLINE': //custom-tag (CSS2 property - browsers don't support it yet - Jan2005)
				//Usage: (default: width=normal color=white)
				//<outline width="(thin|medium|thick)" color="(usualcolorformat)" >Text</outline>
				//Mix this tag with the <font color="(usualcolorformat)"> tag to get mixed colors on outlined text!
				$this->buffer_on = true;
				if (isset($attr['COLOR'])) $this->outlineparam['COLOR'] = ConvertColor($attr['COLOR']);
				else $this->outlineparam['COLOR'] = array('R'=>255,'G'=>255,'B'=>255); //white
				$this->outlineparam['OLDWIDTH'] = $this->LineWidth;
				if (isset($attr['WIDTH'])) {
					switch(strtoupper($attr['WIDTH'])) {
						case 'THIN': $this->outlineparam['WIDTH'] = 0.75*$this->LineWidth; break;
						case 'MEDIUM': $this->outlineparam['WIDTH'] = $this->LineWidth; break;
						case 'THICK': $this->outlineparam['WIDTH'] = 1.75*$this->LineWidth; break;
					}
				} else $this->outlineparam['WIDTH'] = $this->LineWidth; //width == oldwidth
				break;
			case 'BDO':
				if (isset($attr['DIR']) and (strtoupper($attr['DIR']) == 'RTL' )) $this->divrevert = true;
				break;
			case 'S':
			case 'STRIKE':
			case 'DEL':
				$this->strike=true;
				break;
			case 'SUB':
				$this->SUB=true;
				break;
			case 'SUP':
				$this->SUP=true;
				break;
			case 'CENTER':
				$this->buffer_on = true;
				if ($this->tdbegin) {
					$this->cell[$this->row][$this->col]['a'] = $align['center'];
				} else {
					$this->divalign = $align['center'];
					if ($this->x != $this->lMargin) $this->Ln($this->lineheight);
				}
				break;
			case 'ADDRESS': 
				$this->buffer_on = true;
				$this->SetStyle('I',true);
				if (!$this->tdbegin and $this->x != $this->lMargin) $this->Ln($this->lineheight);
				break;
			case 'TABLE': // TABLE-BEGIN
				if ($this->x != $this->lMargin) $this->Ln($this->lineheight);
				$this->tablestart = true;
				$this->table['nc'] = $this->table['nr'] = 0;
				//print_r($attr);
				if (isset($attr['REPEAT_HEADER']) and $attr['REPEAT_HEADER'] == true) $this->UseTableHeader(true);
				if (isset($attr['WIDTH'])) $this->table['w']	= ConvertSize($attr['WIDTH'],$this->pgwidth);
				if (isset($attr['HEIGHT']))	$this->table['h']	= ConvertSize($attr['HEIGHT'],$this->pgwidth);
				if (isset($attr['ALIGN']))	$this->table['a']	= $align[strtolower($attr['ALIGN'])];
				if (isset($attr['BORDER']))	$this->table['border']	= $attr['BORDER'];
				if (isset($attr['BGCOLOR'])) $this->table['bgcolor'][-1]	= $attr['BGCOLOR'];
				break;
			case 'TR':
				$this->row++;
				$this->table['nr']++;
				$this->col = -1;
				if (isset($attr['BGCOLOR']))$this->table['bgcolor'][$this->row]	= $attr['BGCOLOR'];
				break;
			case 'TH':
				$this->SetStyle('B',true);
				if (!isset($attr['ALIGN'])) $attr['ALIGN'] = "center";
			case 'TD':
				$this->tdbegin = true;
				$this->col++;
				while (isset($this->cell[$this->row][$this->col])) $this->col++;
				//Update number column
				if ($this->table['nc'] < $this->col+1) $this->table['nc'] = $this->col+1;
				$this->cell[$this->row][$this->col] = array();
				$this->cell[$this->row][$this->col]['text'] = array();
				$this->cell[$this->row][$this->col]['s'] = 3;
				if (isset($attr['WIDTH'])) $this->cell[$this->row][$this->col]['w'] = ConvertSize($attr['WIDTH'],$this->pgwidth);
				if (isset($attr['HEIGHT'])) $this->cell[$this->row][$this->col]['h']	= ConvertSize($attr['HEIGHT'],$this->pgwidth);
				if (isset($attr['ALIGN'])) $this->cell[$this->row][$this->col]['a'] = $align[strtolower($attr['ALIGN'])];
				if (isset($attr['VALIGN'])) $this->cell[$this->row][$this->col]['va'] = $align[strtolower($attr['VALIGN'])];
				if (isset($attr['BORDER'])) $this->cell[$this->row][$this->col]['border'] = $attr['BORDER'];
				if (isset($attr['BGCOLOR'])) $this->cell[$this->row][$this->col]['bgcolor'] = $attr['BGCOLOR'];
				$cs = $rs = 1;
				if (isset($attr['COLSPAN']) && $attr['COLSPAN']>1)	$cs = $this->cell[$this->row][$this->col]['colspan']	= $attr['COLSPAN'];
				if (isset($attr['ROWSPAN']) && $attr['ROWSPAN']>1)	$rs = $this->cell[$this->row][$this->col]['rowspan']	= $attr['ROWSPAN'];
				//Chiem dung vi tri de danh cho cell span (縨ais hein?)
				for ($k=$this->row ; $k < $this->row+$rs ;$k++)
					for($l=$this->col; $l < $this->col+$cs ;$l++) {
						if ($k-$this->row || $l-$this->col)	$this->cell[$k][$l] = 0;
					}
				if (isset($attr['NOWRAP'])) $this->cell[$this->row][$this->col]['nowrap']= 1;
				break;
			case 'OL':
				if ( !isset($attr['TYPE']) or $attr['TYPE'] == '' ) $this->listtype = '1'; //OL default == '1'
				else $this->listtype = $attr['TYPE']; // ol and ul types are mixed here
			case 'UL':
				if ( (!isset($attr['TYPE']) or $attr['TYPE'] == '') and $tag=='UL') {
					 //Insert UL defaults
					 if ($this->listlvl == 0) $this->listtype = 'disc';
					 elseif ($this->listlvl == 1) $this->listtype = 'circle';
					 else $this->listtype = 'square';
				} elseif (isset($attr['TYPE']) and $tag=='UL') $this->listtype = $attr['TYPE'];
				$this->buffer_on = false;
				if ($this->listlvl == 0) {
					//First of all, skip a line
					if (!$this->pjustfinished) {
							if ($this->x != $this->lMargin) $this->Ln($this->lineheight);
							$this->Ln($this->lineheight);
					}
					$this->oldx = $this->x;
					$this->listlvl++; // first depth level
					$this->listnum = 0; // reset
					$this->listoccur[$this->listlvl] = 1;
					$this->listlist[$this->listlvl][1] = array('TYPE'=>$this->listtype,'MAXNUM'=>$this->listnum);

				} else {

					if (!empty($this->textbuffer)) {
						$this->listitem[] = array($this->listlvl,$this->listnum,$this->textbuffer,$this->listoccur[$this->listlvl]);
						$this->listnum++;
					}
					$this->textbuffer = array();
					$occur = $this->listoccur[$this->listlvl];
					$this->listlist[$this->listlvl][$occur]['MAXNUM'] = $this->listnum; //save previous lvl's maxnum
					$this->listlvl++;
					$this->listnum = 0; // reset

					if ($this->listoccur[$this->listlvl] == 0) $this->listoccur[$this->listlvl] = 1;
					else $this->listoccur[$this->listlvl]++;
					$occur = $this->listoccur[$this->listlvl];
					$this->listlist[$this->listlvl][$occur] = array('TYPE'=>$this->listtype,'MAXNUM'=>$this->listnum);
				}
				break;
			case 'LI':
				//Observation: </LI> is ignored
				if ($this->listlvl == 0) {	//in case of malformed HTML code. Example:(...)</p><li>Content</li><p>Paragraph1</p>(...)
					//First of all, skip a line
					if (!$this->pjustfinished and $this->x != $this->lMargin) $this->Ln(2*$this->lineheight);
					$this->oldx = $this->x;
					$this->listlvl++; // first depth level
					$this->listnum = 0; // reset
					$this->listoccur[$this->listlvl] = 1;
					$this->listlist[$this->listlvl][1] = array('TYPE'=>'disc','MAXNUM'=>$this->listnum);
				}
				if ($this->listnum == 0) {
					$this->buffer_on = true; //activate list 'bufferization'
					$this->listnum++;
					$this->textbuffer = array();

				} else {

					$this->buffer_on = true; //activate list 'bufferization'
					if (!empty($this->textbuffer)) {
						$this->listitem[] = array($this->listlvl,$this->listnum,$this->textbuffer,$this->listoccur[$this->listlvl]);
						$this->listnum++;
					}
					$this->textbuffer = array();
				}
				break;
			case 'H1': // 2 * fontsize
			case 'H2': // 1.5 * fontsize
			case 'H3': // 1.17 * fontsize
			case 'H4': // 1 * fontsize
			case 'H5': // 0.83 * fontsize
			case 'H6': // 0.67 * fontsize
				//Values obtained from: http://www.w3.org/TR/REC-CSS2/sample.html
				if(isset($attr['ALIGN'])) $this->divalign = $align[strtolower($attr['ALIGN'])];
				$this->buffer_on = true;
				if ($this->x != $this->lMargin) $this->Ln(2*$this->lineheight);
				elseif (!$this->pjustfinished) $this->Ln($this->lineheight);
				$this->SetStyle('B',true);
				switch($tag) {
					case 'H1': 
						$this->SetFontSize(2*$this->FontSizePt); 
						$this->lineheight *= 2;
						break;
					case 'H2': 
						$this->SetFontSize(1.5*$this->FontSizePt); 
						$this->lineheight *= 1.5;
						break;
					case 'H3':
						$this->SetFontSize(1.17*$this->FontSizePt);
						$this->lineheight *= 1.17;
						break;
					case 'H4':
						$this->SetFontSize($this->FontSizePt); 
						break;
					case 'H5': 
						$this->SetFontSize(0.83*$this->FontSizePt); 
						$this->lineheight *= 0.83;
						break;
					case 'H6': 
						$this->SetFontSize(0.67*$this->FontSizePt); 
						$this->lineheight *= 0.67;
						break;
				}
				break;
			case 'HR': //Default values: width=100% align=center color=gray
				//Skip a line, if needed
				if ($this->x != $this->lMargin) $this->Ln($this->lineheight);
				$this->Ln(0.2*$this->lineheight);
				$hrwidth = $this->pgwidth;
				$hralign = 'C';
				$hrcolor = array('R'=>200,'G'=>200,'B'=>200);
				if($attr['WIDTH'] != '') $hrwidth = ConvertSize($attr['WIDTH'],$this->pgwidth);
				if($attr['ALIGN'] != '') $hralign = $align[strtolower($attr['ALIGN'])];
				if($attr['COLOR'] != '') $hrcolor = ConvertColor($attr['COLOR']);
				$this->SetDrawColor($hrcolor['R'],$hrcolor['G'],$hrcolor['B']);
				$x = $this->x;
				$y = $this->y;
				switch($hralign) {
					case 'L':
					case 'J':
						break;
					case 'C':
						$empty = $this->pgwidth - $hrwidth;
						$empty /= 2;
						$x += $empty;
						break;
					case 'R':
						$empty = $this->pgwidth - $hrwidth;
						$x += $empty;
						break;
				}
				$oldlinewidth = $this->LineWidth;
				$this->SetLineWidth(0.3);
				$this->Line($x,$y,$x+$hrwidth,$y);
				$this->SetLineWidth($oldlinewidth);
				$this->Ln(0.2*$this->lineheight);
				$this->SetDrawColor(0);
				$this->blockjustfinished = true; //Eliminate exceeding left-side spaces
				break;
			case 'INS':
				$this->SetStyle('U',true);
				break;
			case 'SMALL':
				$newsize = $this->FontSizePt - 1;
				$this->SetFontSize($newsize);
				break;
			case 'BIG':
				$newsize = $this->FontSizePt + 1;
				$this->SetFontSize($newsize);
			case 'STRONG':
				$this->SetStyle('B',true);
				break;
			case 'CITE':
			case 'EM':
				$this->SetStyle('I',true);
				break;
			case 'TITLE':
				$this->titulo = true;
				break;
			case 'B':
			case 'I':
			case 'U':
				if( isset($attr['CLASS']) or isset($attr['ID']) or isset($attr['STYLE']) ) {
					$this->cssbegin=true;
					if (isset($attr['CLASS'])) $properties = $this->CSS[$attr['CLASS']];
					elseif (isset($attr['ID'])) $properties = $this->CSS[$attr['ID']];
					//Read Inline CSS
					if (isset($attr['STYLE'])) $properties = $this->readInlineCSS($attr['STYLE']);
					//Look for name in the $this->CSS array
					$this->backupcss = $properties;
					if (!empty($properties)) $this->setCSS($properties); //name found in the CSS array!
				}
				$this->SetStyle($tag,true);
				break;
			case 'A':
				if (isset($attr['NAME']) and $attr['NAME'] != '') $this->textbuffer[] = array('','','',array(),'',false,false,$attr['NAME']); //an internal link (adds a space for recognition)
				if (isset($attr['HREF'])) $this->HREF=$attr['HREF'];
				break;
			case 'DIV':
				//in case of malformed HTML code. Example:(...)</div><li>Content</li><div>DIV1</div>(...)
				if ($this->listlvl > 0) {	// We are closing (omitted) OL/UL tag(s)
					$this->buffer_on = false;
					if (!empty($this->textbuffer)) $this->listitem[] = array($this->listlvl,$this->listnum,$this->textbuffer,$this->listoccur[$this->listlvl]);
					$this->textbuffer = array();
					$this->listlvl--;
					$this->printlistbuffer();
					$this->pjustfinished = true; //act as if a paragraph just ended
				}
				$this->divbegin=true;
				if ($this->x != $this->lMargin)	$this->Ln($this->lineheight);
				if( isset($attr['ALIGN']) and	$attr['ALIGN'] != '' ) $this->divalign = $align[strtolower($attr['ALIGN'])];
				if( isset($attr['CLASS']) or isset($attr['ID']) or isset($attr['STYLE']) ) {
					$this->cssbegin=true;
					if (isset($attr['CLASS'])) $properties = $this->CSS[$attr['CLASS']];
					elseif (isset($attr['ID'])) $properties = $this->CSS[$attr['ID']];
					//Read Inline CSS
					if (isset($attr['STYLE'])) $properties = $this->readInlineCSS($attr['STYLE']);
					//Look for name in the $this->CSS array
					if (!empty($properties)) $this->setCSS($properties); //name found in the CSS array!
				}
				break;
			case 'IMG':
				if(!empty($this->textbuffer) and !$this->tablestart) {
					//Output previously buffered content and output image below
					//Set some default values
					$olddivwidth = $this->divwidth;
					$olddivheight = $this->divheight;
					if ( $this->divwidth == 0) $this->divwidth = $this->pgwidth - $x + $this->lMargin;
					if ( $this->divheight == 0) $this->divheight = $this->lineheight;
					//Print content
					$this->printbuffer($this->textbuffer,true/*is out of a block (e.g. DIV,P etc.)*/);
					$this->textbuffer=array(); 
					//Reset values
					$this->divwidth = $olddivwidth;
					$this->divheight = $olddivheight;
					$this->textbuffer=array();
					$this->Ln($this->lineheight);
				}
				if(isset($attr['SRC'])) {
					$srcpath = $attr['SRC'];
					$size = @getimagesize ($srcpath);	//取文件类型
					$f_exists = @fopen($srcpath,"rb");
					if($size[2]!=2 || !$f_exists) {
						$this->WriteHTML($srcpath.' ');
						break;
					}
					if(!isset($attr['WIDTH'])) $attr['WIDTH'] = 0;
					else $attr['WIDTH'] = ConvertSize($attr['WIDTH'],$this->pgwidth);//$attr['WIDTH'] /= 4;
					if(!isset($attr['HEIGHT']))	$attr['HEIGHT'] = 0;
					else $attr['HEIGHT'] = ConvertSize($attr['HEIGHT'],$this->pgwidth);//$attr['HEIGHT'] /= 4;
					if ($this->tdbegin) {
						$bak_x = $this->x;
						$bak_y = $this->y;
						//Check whether image exists locally or on the URL
						$f_exists = @fopen($srcpath,"rb");
						if (!$f_exists) {	//Show 'image not found' icon instead
							if(!$this->shownoimg) break;
							$srcpath = str_replace("\\","/",dirname(__FILE__)) . "/";
							$srcpath .= 'no_img.gif';
						}
						$sizesarray = $this->Image($srcpath, $this->GetX(), $this->GetY(), $attr['WIDTH'], $attr['HEIGHT'],'','',false);
						$this->y = $bak_y;
						$this->x = $bak_x;
					} elseif($this->pbegin or $this->divbegin) {
						//In order to support <div align='center'><img ...></div>
						$ypos = 0;
						$bak_x = $this->x;
						$bak_y = $this->y;
						//Check whether image exists locally or on the URL
						$f_exists = @fopen($srcpath,"rb");
						if (!$f_exists) {	//Show 'image not found' icon instead
							if(!$this->shownoimg) break;
							$srcpath = str_replace("\\","/",dirname(__FILE__)) . "/";
							$srcpath .= 'no_img.gif';
						}
						$sizesarray = $this->Image($srcpath, $this->GetX(), $this->GetY(), $attr['WIDTH'], $attr['HEIGHT'],'','',false);
						$this->y = $bak_y;
						$this->x = $bak_x;
						$xpos = '';
						switch($this->divalign) {
							case "C":
								$spacesize = $this->CurrentFont[ 'cw' ][ ' ' ] * ( $this->FontSizePt / 1000 );
								$empty = ($this->pgwidth - $sizesarray['WIDTH'])/2;
								$xpos = 'xpos='.$empty.',';
								break;
							case "R":
								$spacesize = $this->CurrentFont[ 'cw' ][ ' ' ] * ( $this->FontSizePt / 1000 );
								$empty = ($this->pgwidth - $sizesarray['WIDTH']);
								$xpos = 'xpos='.$empty.',';
								break;
							default: break;
						}
						$numberoflines = (integer)ceil($sizesarray['HEIGHT']/$this->lineheight) ;
						$ypos = $numberoflines * $this->lineheight;
						$this->textbuffer[] = array("|x%s#pa@c!e|"/*identifier*/."type=image,ypos=$ypos,{$xpos}width=".$sizesarray['WIDTH'].",height=".$sizesarray['HEIGHT']."|x%s#pa@c!e|".$sizesarray['OUTPUT']);
						while($numberoflines) {$this->textbuffer[] = array("\n",$this->HREF,$this->currentstyle,$this->colorarray,$this->currentfont,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->bgcolorarray);$numberoflines--;}
					} else {
						$imgborder = 0;
						if (isset($attr['BORDER'])) $imgborder = ConvertSize($attr['BORDER'],$this->pgwidth);
						//Check whether image exists locally or on the URL
						$f_exists = @fopen($srcpath,"rb");
						if (!$f_exists) {	//Show 'image not found' icon instead
							$srcpath = str_replace("\\","/",dirname(__FILE__)) . "/";
							$srcpath .= 'no_img.gif';
						}
						$sizesarray = $this->Image($srcpath, $this->GetX(), $this->GetY(), $attr['WIDTH'], $attr['HEIGHT'],'',$this->HREF); //Output Image
						$ini_x = $sizesarray['X'];
						$ini_y = $sizesarray['Y'];
						if ($imgborder) {
							$oldlinewidth = $this->LineWidth;
							$this->SetLineWidth($imgborder);
							$this->Rect($ini_x,$ini_y,$sizesarray['WIDTH'],$sizesarray['HEIGHT']);
							$this->SetLineWidth($oldlinewidth);
						}
					}
					if ($sizesarray['X'] < $this->x) $this->x = $this->lMargin;
					if ($this->tablestart) {
						$this->cell[$this->row][$this->col]['textbuffer'][] = array("|x%s#pa@c!e|"/*identifier*/."type=image,width=".$sizesarray['WIDTH'].",height=".$sizesarray['HEIGHT']."|x%s#pa@c!e|".$sizesarray['OUTPUT']);
						$this->cell[$this->row][$this->col]['s'] += $sizesarray['WIDTH'] + 1;// +1 == margin
						$this->cell[$this->row][$this->col]['form'] = true; // in order to make some width adjustments later
						if (!isset($this->cell[$this->row][$this->col]['w'])) $this->cell[$this->row][$this->col]['w'] = $sizesarray['WIDTH'] + 3;
						if (!isset($this->cell[$this->row][$this->col]['h'])) $this->cell[$this->row][$this->col]['h'] = $sizesarray['HEIGHT'] + 3;
					}
				}
				break;
			case 'BLOCKQUOTE':
			case 'BR':
				if($this->tablestart) {
					$this->cell[$this->row][$this->col]['textbuffer'][] = array("\n",$this->HREF,$this->currentstyle,$this->colorarray,$this->currentfont,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->bgcolorarray);
					$this->cell[$this->row][$this->col]['text'][] = "\n";
					if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
						$this->cell[$this->row][$this->col]['maxs'] = isset($this->cell[$this->row][$this->col]['s']) ? $this->cell[$this->row][$this->col]['s'] +2:2; 
					} //+2 == margin
					elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) $this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']+2;//+2 == margin
					$this->cell[$this->row][$this->col]['s'] = 0;// reset
				}
				elseif($this->divbegin or $this->pbegin or $this->buffer_on)	$this->textbuffer[] = array("\n",$this->HREF,$this->currentstyle,$this->colorarray,$this->currentfont,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->bgcolorarray);
				else {$this->Ln($this->lineheight);$this->blockjustfinished = true;}
				break;
			case 'P':
				//in case of malformed HTML code. Example:(...)</p><li>Content</li><p>Paragraph1</p>(...)
				if ($this->listlvl > 0) {	// We are closing (omitted) OL/UL tag(s)
					$this->buffer_on = false;
					if (!empty($this->textbuffer)) $this->listitem[] = array($this->listlvl,$this->listnum,$this->textbuffer,$this->listoccur[$this->listlvl]);
					$this->textbuffer = array();
					$this->listlvl--;
					$this->printlistbuffer();
					$this->pjustfinished = true; //act as if a paragraph just ended
				}
				if ($this->tablestart) {
					$this->cell[$this->row][$this->col]['textbuffer'][] = array($e,$this->HREF,$this->currentstyle,$this->colorarray,$this->currentfont,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->bgcolorarray);
					$this->cell[$this->row][$this->col]['text'][] = "\n";
					break;
				}
				$this->pbegin=true;
				if ($this->x != $this->lMargin) $this->Ln(2*$this->lineheight);
				elseif (!$this->pjustfinished) $this->Ln($this->lineheight);
				//Save x,y coords in case we need to print borders...
				$this->oldx = $this->x;
				$this->oldy = $this->y;
				if(isset($attr['ALIGN'])) $this->divalign = $align[strtolower($attr['ALIGN'])];
				if(isset($attr['CLASS']) or isset($attr['ID']) or isset($attr['STYLE']) ) {
					$this->cssbegin=true;
					if (isset($attr['CLASS'])) $properties = $this->CSS[$attr['CLASS']];
					elseif (isset($attr['ID'])) $properties = $this->CSS[$attr['ID']];
					//Read Inline CSS
					if (isset($attr['STYLE'])) $properties = $this->readInlineCSS($attr['STYLE']);
					//Look for name in the $this->CSS array
					$this->backupcss = $properties;
					if (!empty($properties)) $this->setCSS($properties); //name(id/class/style) found in the CSS array!
				}
				break;
			case 'SPAN':
				$this->buffer_on = true;
				//Save x,y coords in case we need to print borders...
				$this->oldx = $this->x;
				$this->oldy = $this->y;
				if( isset($attr['CLASS']) or isset($attr['ID']) or isset($attr['STYLE']) ) {
					$this->cssbegin=true;
					if (isset($attr['CLASS']) && isset($this->CSS[$attr['CLASS']])) {
						$properties = $this->CSS[$attr['CLASS']];
					}elseif(isset($attr['ID']) && isset($this->CSS[$attr['ID']])) {
						$properties = $this->CSS[$attr['ID']];
					}
					//Read Inline CSS
					if (isset($attr['STYLE'])) $properties = $this->readInlineCSS($attr['STYLE']);
					//Look for name in the $this->CSS array
					if(isset($properties)) $this->backupcss = $properties;
					if (!empty($properties)) $this->setCSS($properties); //name found in the CSS array!
				}
				break;
			case 'PRE':
				if($this->tablestart) {
					$this->cell[$this->row][$this->col]['textbuffer'][] = array("\n",$this->HREF,$this->currentstyle,$this->colorarray,$this->currentfont,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->bgcolorarray);
					$this->cell[$this->row][$this->col]['text'][] = "\n";
				} elseif($this->divbegin or $this->pbegin or $this->buffer_on) {
					$this->textbuffer[] = array("\n",$this->HREF,$this->currentstyle,$this->colorarray,$this->currentfont,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->bgcolorarray);
				} else {
					if ($this->x != $this->lMargin) $this->Ln(2*$this->lineheight);
					elseif (!$this->pjustfinished) $this->Ln($this->lineheight);
					$this->buffer_on = true;
					//Save x,y coords in case we need to print borders...
					$this->oldx = $this->x;
					$this->oldy = $this->y;
					if(isset($attr['ALIGN'])) $this->divalign = $align[strtolower($attr['ALIGN'])];
					if(isset($attr['CLASS']) or isset($attr['ID']) or isset($attr['STYLE']) ) {
						$this->cssbegin=true;
						if (isset($attr['CLASS'])) $properties = $this->CSS[$attr['CLASS']];
						elseif (isset($attr['ID'])) $properties = $this->CSS[$attr['ID']];
						//Read Inline CSS
						if (isset($attr['STYLE'])) $properties = $this->readInlineCSS($attr['STYLE']);
						//Look for name in the $this->CSS array
						$this->backupcss = $properties;
						if (!empty($properties)) $this->setCSS($properties); //name(id/class/style) found in the CSS array!
					}
				}
			case 'TT':
			case 'KBD':
			case 'SAMP':
			case 'CODE':
				$this->SetFont('courier');
				$this->currentfont='courier';
				break;
			case 'TEXTAREA':
				$this->buffer_on = true;
				$colsize = 20; //HTML default value 
				$rowsize = 2; //HTML default value
				if (isset($attr['COLS'])) $colsize = $attr['COLS'];
				if (isset($attr['ROWS'])) $rowsize = $attr['ROWS'];
				if (!$this->tablestart) {
					if ($this->x != $this->lMargin) $this->Ln($this->lineheight);
					$this->col = $colsize;
					$this->row = $rowsize;
				} else {	//it is inside a table
					$this->specialcontent = "type=textarea,lines=$rowsize,width=".((2.2*$colsize) + 3); //Activate form info in order to paint FORM elements within table
					$this->cell[$this->row][$this->col]['s'] += (2.2*$colsize) + 6;// +6 == margin
					if (!isset($this->cell[$this->row][$this->col]['h'])) $this->cell[$this->row][$this->col]['h'] = 1.1*$this->lineheight*$rowsize + 2.5;
				}
				break;
			case 'SELECT':
				$this->specialcontent = "type=select"; //Activate form info in order to paint FORM elements within table
				break;
			case 'OPTION':
				$this->selectoption['ACTIVE'] = true;
				if (empty($this->selectoption)) {
					$this->selectoption['MAXWIDTH'] = '';
					$this->selectoption['SELECTED'] = '';
				}
				if (isset($attr['SELECTED'])) $this->selectoption['SELECTED'] = '';
				break;
			case 'FORM':
				if($this->tablestart) {
					$this->cell[$this->row][$this->col]['textbuffer'][] = array($e,$this->HREF,$this->currentstyle,$this->colorarray,$this->currentfont,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->bgcolorarray);
					$this->cell[$this->row][$this->col]['text'][] = "\n";
				}
				elseif ($this->x != $this->lMargin) $this->Ln($this->lineheight); //Skip a line, if needed
				break;
			case 'INPUT':
				if (!isset($attr['TYPE'])) $attr['TYPE'] == ''; //in order to allow default 'TEXT' form (in case of malformed HTML code)
				if (!$this->tablestart) {
					switch(strtoupper($attr['TYPE'])){
						case 'CHECKBOX': //Draw Checkbox
							$checked = false;
							if (isset($attr['CHECKED'])) $checked = true;
							$this->SetFillColor(235,235,235);
							$this->x += 3;
							$this->Rect($this->x,$this->y+1,3,3,'DF');
							if ($checked) {
								$this->Line($this->x,$this->y+1,$this->x+3,$this->y+1+3);
								$this->Line($this->x,$this->y+1+3,$this->x+3,$this->y+1);
							}
							$this->SetFillColor(255);
							$this->x += 3.5;
							break;
						case 'RADIO': //Draw Radio button
							$checked = false;
							if (isset($attr['CHECKED'])) $checked = true;
							$this->x += 4;
							$this->Circle($this->x,$this->y+2.2,1,'D');
							$this->_out('0.000 g');
							if ($checked) $this->Circle($this->x,$this->y+2.2,0.4,'DF');
							$this->Write(5,$texto,$this->x);
							$this->x += 2;
							break;
						case 'BUTTON': // Draw a button
						case 'SUBMIT':
						case 'RESET':
							$texto='';
							if (isset($attr['VALUE'])) $texto = $attr['VALUE'];
							$nihil = 2.5;
							$this->x += 2;
							$this->SetFillColor(190,190,190);
							$this->Rect($this->x,$this->y,$this->GetStringWidth($texto)+2*$nihil,4.5,'DF'); // 4.5 in order to avoid overlapping
							$this->x += $nihil;
							$this->Write(5,$texto,$this->x);
							$this->x += $nihil;
							$this->SetFillColor(255);
							break;
						case 'PASSWORD':
							if(isset($attr['VALUE'])) {
								$num_stars = strlen($attr['VALUE']);
								$attr['VALUE'] = str_repeat('*',$num_stars);
							}
						case 'TEXT': //Draw TextField
						default: //default == TEXT
							$texto='';
							if (isset($attr['VALUE'])) $texto = $attr['VALUE'];
							$tamanho = 20;
							if (isset($attr['SIZE']) and ctype_digit($attr['SIZE']) ) $tamanho = $attr['SIZE'];
							$this->SetFillColor(235,235,235);
							$this->x += 2;
							$this->Rect($this->x,$this->y,2*$tamanho,4.5,'DF');// 4.5 in order to avoid overlapping
							if ($texto != '') {
								$this->x += 1;
								$this->Write(5,$texto,$this->x);
								$this->x -= $this->GetStringWidth($texto);
							}
							$this->SetFillColor(255);
							$this->x += 2*$tamanho;
							break;
					}
				} else {	//we are inside a table
					$this->cell[$this->row][$this->col]['form'] = true; // in order to make some width adjustments later
					$type = '';
					$text = '';
					$height = 0;
					$width = 0;
					switch(strtoupper($attr['TYPE'])) {
						case 'CHECKBOX': //Draw Checkbox
							$checked = false;
							if (isset($attr['CHECKED'])) $checked = true;
							$text = $checked;
							$type = 'CHECKBOX';
							$width = 4;
							$this->cell[$this->row][$this->col]['textbuffer'][] = array("|x%s#pa@c!e|"/*identifier*/."type=input,subtype=$type,width=$width,height=$height"."|x%s#pa@c!e|".$text);
							$this->cell[$this->row][$this->col]['s'] += $width;
							if (!isset($this->cell[$this->row][$this->col]['h'])) $this->cell[$this->row][$this->col]['h'] = $this->lineheight;
							break;
						case 'RADIO': //Draw Radio button
							$checked = false;
							if (isset($attr['CHECKED'])) $checked = true;
							$text = $checked;
							$type = 'RADIO';
							$width = 3;
							$this->cell[$this->row][$this->col]['textbuffer'][] = array("|x%s#pa@c!e|"/*identifier*/."type=input,subtype=$type,width=$width,height=$height"."|x%s#pa@c!e|".$text);
							$this->cell[$this->row][$this->col]['s'] += $width;
							if (!isset($this->cell[$this->row][$this->col]['h'])) $this->cell[$this->row][$this->col]['h'] = $this->lineheight;
							break;
						case 'BUTTON': $type = 'BUTTON'; // Draw a button
						case 'SUBMIT': if ($type == '') $type = 'SUBMIT';
						case 'RESET': if ($type == '') $type = 'RESET';
							$texto='';
							if (isset($attr['VALUE'])) $texto = " " . $attr['VALUE'] . " ";
							$text = $texto;
							$width = $this->GetStringWidth($texto)+3;
							$this->cell[$this->row][$this->col]['textbuffer'][] = array("|x%s#pa@c!e|"/*identifier*/."type=input,subtype=$type,width=$width,height=$height"."|x%s#pa@c!e|".$text);
							$this->cell[$this->row][$this->col]['s'] += $width;
							if (!isset($this->cell[$this->row][$this->col]['h'])) $this->cell[$this->row][$this->col]['h'] = $this->lineheight + 2;
							break;
						case 'PASSWORD':
							if(isset($attr['VALUE'])) {
								$num_stars = strlen($attr['VALUE']);
								$attr['VALUE'] = str_repeat('*',$num_stars);
							}
							$type = 'PASSWORD';
						case 'TEXT': //Draw TextField
						default: //default == TEXT
							$texto='';
							if (isset($attr['VALUE'])) $texto = $attr['VALUE'];
							$tamanho = 20;
							if(isset($attr['SIZE']) and ctype_digit($attr['SIZE']) ) $tamanho = $attr['SIZE'];
							$text = $texto;
							$width = 2*$tamanho;
							if ($type == '') $type = 'TEXT';
							$this->cell[$this->row][$this->col]['textbuffer'][] = array("|x%s#pa@c!e|"/*identifier*/."type=input,subtype=$type,width=$width,height=$height"."|x%s#pa@c!e|".$text);
							$this->cell[$this->row][$this->col]['s'] += $width;
							if (!isset($this->cell[$this->row][$this->col]['h'])) $this->cell[$this->row][$this->col]['h'] = $this->lineheight + 2;
							break;
					}
				}
				break;
			case 'FONT': //Font size is ignored for now
				if(isset($attr['COLOR']) and $attr['COLOR']!='') {
					$cor = ConvertColor($attr['COLOR']);
					//If something goes wrong switch color to black
					$cor['R'] = (isset($cor['R'])?$cor['R']:0);
					$cor['G'] = (isset($cor['G'])?$cor['G']:0);
					$cor['B'] = (isset($cor['B'])?$cor['B']:0);
					$this->colorarray = $cor;
					$this->SetTextColor($cor['R'],$cor['G'],$cor['B']);
					$this->issetcolor = true;
				}
				if(isset($attr['FACE']) and in_array(strtolower($attr['FACE']), $this->fontlist)) {
					$this->SetFont(strtolower($attr['FACE']));
					$this->issetfont=true;
				}
				//'If' disabled in this version due lack of testing (you may enable it if you want)
	//			if (isset($attr['FACE']) and in_array(strtolower($attr['FACE']), $this->fontlist) and isset($attr['SIZE']) and $attr['SIZE']!='') {
	//				$this->SetFont(strtolower($attr['FACE']),'',$attr['SIZE']);
	//				$this->issetfont=true;
	//			}
				break;
		}//end of switch
		$this->pjustfinished=false;
	}

	/**
	 * Closing tag
	 */
	function CloseTag($tag) {

		if($tag=='OPTION') $this->selectoption['ACTIVE'] = false;
		if($tag=='BDO') $this->divrevert = false;
		if($tag=='INS') $tag='U';
		if($tag=='STRONG') $tag='B';
		if($tag=='EM' or $tag=='CITE') $tag='I';
		if($tag=='OUTLINE') {
			if(!$this->pbegin and !$this->divbegin and !$this->tablestart) {
				//Deactivate $this->outlineparam for its info is already stored inside $this->textbuffer
				//if (isset($this->outlineparam['OLDWIDTH'])) $this->SetTextOutline($this->outlineparam['OLDWIDTH']);
				$this->SetTextOutline(false);
				$this->outlineparam=array();
				//Save x,y coords ???
				$x = $this->x;
				$y = $this->y;
				//Set some default values
				$this->divwidth = $this->pgwidth - $x + $this->lMargin;
				//Print content
				$this->printbuffer($this->textbuffer,true/*is out of a block (e.g. DIV,P etc.)*/);
				$this->textbuffer=array(); 
				//Reset values
				$this->Reset();
				$this->buffer_on=false;
			}
			$this->SetTextOutline(false);
			$this->outlineparam=array();
		}
		if($tag=='A') {
			if(!$this->pbegin and !$this->divbegin and !$this->tablestart and !$this->buffer_on) {
				 //Deactivate $this->HREF for its info is already stored inside $this->textbuffer
				 $this->HREF='';
				 //Save x,y coords ???
				 $x = $this->x;
				 $y = $this->y;
				 //Set some default values
				 $this->divwidth = $this->pgwidth - $x + $this->lMargin;
				 //Print content
				 $this->printbuffer($this->textbuffer,true/*is out of a block (e.g. DIV,P etc.)*/);
				 $this->textbuffer=array();
				 //Reset values
				 $this->Reset();
			}
			$this->HREF=''; 
		}
		if($tag=='TH') $this->SetStyle('B',false);
		if($tag=='TH' or $tag=='TD') $this->tdbegin = false;
		if($tag=='SPAN') {
			if(!$this->pbegin and !$this->divbegin and !$this->tablestart) {
				if($this->cssbegin) {
					//Check if we have borders to print
					if ($this->cssbegin and ($this->divborder or $this->dash_on or $this->dotted_on or $this->divbgcolor)) {
						$texto=''; 
						foreach($this->textbuffer as $vetor) $texto.=$vetor[0];
						$tempx = $this->x;
						if($this->divbgcolor) $this->Cell($this->GetStringWidth($texto),$this->lineheight,'',$this->divborder,'','L',$this->divbgcolor);
						if ($this->dash_on) $this->Rect($this->oldx,$this->oldy,$this->GetStringWidth($texto),$this->lineheight);
						if ($this->dotted_on) $this->DottedRect($this->x - $this->GetStringWidth($texto),$this->y,$this->GetStringWidth($texto),$this->lineheight);
						$this->x = $tempx;
						$this->x -= 1; //adjust alignment
					}
					$this->cssbegin=false;
					$this->backupcss=array();
				}
				//Save x,y coords ???
				$x = $this->x;
				$y = $this->y;
				//Set some default values
				$this->divwidth = $this->pgwidth - $x + $this->lMargin;
				//Print content
				$this->printbuffer($this->textbuffer,true/*is out of a block (e.g. DIV,P etc.)*/);
				$this->textbuffer=array(); 
				//Reset values
				$this->Reset();
			}
			$this->buffer_on=false;
		}
		if($tag=='P' or $tag=='DIV') {	//CSS in BLOCK mode
		
			$this->blockjustfinished = true; //Eliminate exceeding left-side spaces
			if(!$this->tablestart) {
				if ($this->divwidth == 0) $this->divwidth = $this->pgwidth;
				if ($tag=='P') {
					$this->pbegin=false;
					$this->pjustfinished=true;
				} else $this->divbegin=false;
				$content='';
				foreach($this->textbuffer as $aux) $content .= $aux[0];
				$numlines = $this->WordWrap($content,$this->divwidth);
				if ($this->divheight == 0) $this->divheight = $numlines * 5;
				//Print content
				$this->printbuffer($this->textbuffer);
				$this->textbuffer=array();
				if ($tag=='P') $this->Ln($this->lineheight);
			}//end of 'if (!this->tablestart)'
			//Reset values
			$this->Reset();
			$this->cssbegin=false;
			$this->backupcss=array();
		}
		if($tag=='TABLE') { // TABLE-END 
			$this->blockjustfinished = true; //Eliminate exceeding left-side spaces
			$this->table['cells'] = $this->cell;
			//print_r($this->table);
			$this->table['wc'] = array_pad(array(),$this->table['nc'],array('miw'=>0,'maw'=>0));
			$this->table['hr'] = array_pad(array(),$this->table['nr'],0);
			$this->_tableColumnWidth($this->table);
			$this->_tableWidth($this->table);
			$this->_tableHeight($this->table);

			//Output table on PDF
			$this->_tableWrite($this->table);
			
			//Reset values
			$this->tablestart=false; //bool
			$this->table=array(); //array
			$this->cell=array(); //array 
			$this->col=-1; //int
			$this->row=-1; //int
			$this->Reset();
			$this->Ln(0.5*$this->lineheight);
		}
		if(($tag=='UL') or ($tag=='OL')) {
			if ($this->buffer_on == false) $this->listnum--;//Adjust minor BUG (this happens when there are two </OL> together)
			if ($this->listlvl == 1) {	// We are closing the last OL/UL tag
				$this->blockjustfinished = true; //Eliminate exceeding left-side spaces
				$this->buffer_on = false;
				if (!empty($this->textbuffer)) $this->listitem[] = array($this->listlvl,$this->listnum,$this->textbuffer,$this->listoccur[$this->listlvl]);
				$this->textbuffer = array();
				$this->listlvl--;
				$this->printlistbuffer();

			} else { // returning one level

				if (!empty($this->textbuffer)) $this->listitem[] = array($this->listlvl,$this->listnum,$this->textbuffer,$this->listoccur[$this->listlvl]);
				 $this->textbuffer = array();
				 $occur = $this->listoccur[$this->listlvl]; 
				 $this->listlist[$this->listlvl][$occur]['MAXNUM'] = $this->listnum; //save previous lvl's maxnum
				 $this->listlvl--;
				 $occur = $this->listoccur[$this->listlvl];
				 $this->listnum = $this->listlist[$this->listlvl][$occur]['MAXNUM']; // recover previous level's number
				 $this->listtype = $this->listlist[$this->listlvl][$occur]['TYPE']; // recover previous level's type
				 $this->buffer_on = false;
			}
		}
		if($tag=='H1' or $tag=='H2' or $tag=='H3' or $tag=='H4' or $tag=='H5' or $tag=='H6') {
			$this->blockjustfinished = true; //Eliminate exceeding left-side spaces
			if(!$this->pbegin and !$this->divbegin and !$this->tablestart) {
				//These 2 codelines are useless?
				$texto=''; 
				foreach($this->textbuffer as $vetor) $texto.=$vetor[0];
				//Save x,y coords ???
				$x = $this->x;
				$y = $this->y;
				//Set some default values
				$this->divwidth = $this->pgwidth;
				//Print content
				$this->printbuffer($this->textbuffer);
				$this->textbuffer=array(); 
				if ($this->x != $this->lMargin) $this->Ln($this->lineheight);
				//Reset values
				$this->Reset();
			}
			$this->buffer_on=false;
			$this->lineheight = 5;
			$this->Ln($this->lineheight);
			$this->SetFontSize(11);
			$this->SetStyle('B',false);
		}
		if($tag=='TITLE') { $this->titulo=false; $this->blockjustfinished = true; }
		if($tag=='FORM') $this->Ln($this->lineheight);
		if($tag=='PRE') {
			if(!$this->pbegin and !$this->divbegin and !$this->tablestart) {
				if ($this->divwidth == 0) $this->divwidth = $this->pgwidth;
				$content='';
				foreach($this->textbuffer as $aux) $content .= $aux[0];
				$numlines = $this->WordWrap($content,$this->divwidth);
				if ($this->divheight == 0) $this->divheight = $numlines * 5;
				//Print content
				$this->textbuffer[0][0] = ltrim($this->textbuffer[0][0]); //Remove exceeding left-side space
				$this->printbuffer($this->textbuffer);
				$this->textbuffer=array();
				if ($this->x != $this->lMargin) $this->Ln($this->lineheight);
				//Reset values
				$this->Reset();
				$this->Ln(1.1*$this->lineheight);
			}
			if($this->tablestart) {
				$this->cell[$this->row][$this->col]['textbuffer'][] = array("\n",$this->HREF,$this->currentstyle,$this->colorarray,$this->currentfont,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->bgcolorarray);
				$this->cell[$this->row][$this->col]['text'][] = "\n";
			}
			if($this->divbegin or $this->pbegin or $this->buffer_on)	$this->textbuffer[] = array("\n",$this->HREF,$this->currentstyle,$this->colorarray,$this->currentfont,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->bgcolorarray);
			$this->cssbegin=false;
			$this->backupcss=array();
			$this->buffer_on = false;
			$this->blockjustfinished = true; //Eliminate exceeding left-side spaces
			$this->pjustfinished = true; //behaves the same way
		}
		if($tag=='CODE' or $tag=='PRE' or $tag=='TT' or $tag=='KBD' or $tag=='SAMP') {
			$this->currentfont='';
			$this->SetFont('arial');
		}
		if($tag=='B' or $tag=='I' or $tag=='U') {
			$this->SetStyle($tag,false);
			if ($this->cssbegin and !$this->divbegin and !$this->pbegin and !$this->buffer_on) {
				//Reset values
				$this->Reset();
				$this->cssbegin=false;
				$this->backupcss=array();
			}
		}
		if($tag=='TEXTAREA') {
			if (!$this->tablestart) {	//not inside a table
				//Draw arrows too?
				$texto = '';
				foreach($this->textbuffer as $v) $texto .= $v[0];
				$this->SetFillColor(235,235,235);
				$this->SetFont('courier');
				$this->x +=3;
				$linesneeded = $this->WordWrap($texto,($this->col*2.2)+3);
				if ( $linesneeded > $this->row ) {	//Too many words inside textarea
					$textoaux = explode("\n",$texto);
					$texto = '';
					for($i=0;$i < $this->row;$i++) {
						if ($i == $this->row-1) $texto .= $textoaux[$i];
						else $texto .= $textoaux[$i] . "\n";
					}
					//Inform the user that some text has been truncated
					$texto{strlen($texto)-1} = ".";
					$texto{strlen($texto)-2} = ".";
					$texto{strlen($texto)-3} = ".";
				}
				$backup_y = $this->y;
				$this->Rect($this->x,$this->y,(2.2*$this->col)+6,5*$this->row,'DF');
				if ($texto != '') $this->MultiCell((2.2*$this->col)+6,$this->lineheight,$texto);
				$this->y = $backup_y + $this->row*$this->lineheight;
				$this->SetFont('arial');
			} else {	//inside a table
			
				$this->cell[$this->row][$this->col]['textbuffer'][] = $this->textbuffer[0];
				$this->cell[$this->row][$this->col]['text'][] = $this->textbuffer[0];
				$this->cell[$this->row][$this->col]['form'] = true; // in order to make some width adjustments later
				$this->specialcontent = '';
			}
			$this->SetFillColor(255);
			$this->textbuffer=array(); 
			$this->buffer_on = false;
		}
		if($tag=='SELECT') {
			$texto = '';
			$tamanho = 0;
			if (isset($this->selectoption['MAXWIDTH'])) $tamanho = $this->selectoption['MAXWIDTH'];
			if ($this->tablestart) {
				$texto = "|x%s#pa@c!e|".$this->specialcontent."|x%s#pa@c!e|".$this->selectoption['SELECTED'];
				$aux = explode("|x%s#pa@c!e|",$texto);
				$texto = $aux[2];
				$texto = "|x%s#pa@c!e|".$aux[1].",width=$tamanho,height=".($this->lineheight + 2)."|x%s#pa@c!e|".$texto;
				$this->cell[$this->row][$this->col]['s'] += $tamanho + 7; // margin + arrow box
				$this->cell[$this->row][$this->col]['form'] = true; // in order to make some width adjustments later
				if (!isset($this->cell[$this->row][$this->col]['h'])) $this->cell[$this->row][$this->col]['h'] = $this->lineheight + 2;
				$this->cell[$this->row][$this->col]['textbuffer'][] = array($texto);
				$this->cell[$this->row][$this->col]['text'][] = '';
			} else { //not inside a table
				$texto = $this->selectoption['SELECTED'];
				$this->SetFillColor(235,235,235);
				$this->x += 2;
				$this->Rect($this->x,$this->y,$tamanho+2,5,'DF');//+2 margin
				$this->x += 1;
				if ($texto != '') $this->Write(5,$texto,$this->x);
				$this->x += $tamanho - $this->GetStringWidth($texto) + 2;
				$this->SetFillColor(190,190,190);
				$this->Rect($this->x-1,$this->y,5,5,'DF'); //Arrow Box
				$this->SetFont('zapfdingbats');
				$this->Write(5,chr(116),$this->x); //Down arrow
				$this->SetFont('arial');
				$this->SetFillColor(255);
				$this->x += 1;
			}
			$this->selectoption = array();
			$this->specialcontent = '';
			$this->textbuffer = array(); 
		}
		if($tag=='SUB' or $tag=='SUP') {	//subscript or superscript
		
			if(!$this->pbegin and !$this->divbegin and !$this->tablestart and !$this->buffer_on and !$this->strike) {
				//Deactivate $this->SUB/SUP for its info is already stored inside $this->textbuffer
				$this->SUB=false;
				$this->SUP=false;
				//Save x,y coords ???
				$x = $this->x;
				$y = $this->y;
				//Set some default values
				$this->divwidth = $this->pgwidth - $x + $this->lMargin;
				//Print content
				$this->printbuffer($this->textbuffer,true/*is out of a block (e.g. DIV,P etc.)*/);
				$this->textbuffer=array();
				//Reset values
				$this->Reset();
			}
			$this->SUB=false;
			$this->SUP=false;
		}
		if($tag=='S' or $tag=='STRIKE' or $tag=='DEL') {
			if(!$this->pbegin and !$this->divbegin and !$this->tablestart) {
				//Deactivate $this->strike for its info is already stored inside $this->textbuffer
				$this->strike=false;
				//Save x,y coords ???
				$x = $this->x;
				$y = $this->y;
				//Set some default values
				$this->divwidth = $this->pgwidth - $x + $this->lMargin;
				//Print content
				$this->printbuffer($this->textbuffer,true/*is out of a block (e.g. DIV,P etc.)*/);
				$this->textbuffer=array(); 
				//Reset values
				$this->Reset();
			}
			$this->strike=false;
		}
		if($tag=='ADDRESS' or $tag=='CENTER') {		// <ADDRESS> or <CENTER> tag
		
			$this->blockjustfinished = true; //Eliminate exceeding left-side spaces
			if(!$this->pbegin and !$this->divbegin and !$this->tablestart) {
				//Save x,y coords ???
				$x = $this->x;
				$y = $this->y;
				//Set some default values
				$this->divwidth = $this->pgwidth - $x + $this->lMargin;
				//Print content
				$this->printbuffer($this->textbuffer);
				$this->textbuffer=array(); 
				//Reset values
				$this->Reset();
			}
			$this->buffer_on=false;
			if ($tag == 'ADDRESS') $this->SetStyle('I',false);
		}
		if($tag=='BIG') {
			$newsize = $this->FontSizePt - 1;
			$this->SetFontSize($newsize);
			$this->SetStyle('B',false);
		}
		if($tag=='SMALL') {
			$newsize = $this->FontSizePt + 1;
			$this->SetFontSize($newsize);
		}
		if($tag=='FONT') {
			if ($this->issetcolor == true) {
				$this->colorarray = array();
				$this->SetTextColor(0);
				$this->issetcolor = false;
			}
			if ($this->issetfont) {
				$this->SetFont('arial');
				$this->issetfont=false;
			}
			if ($this->cssbegin) {
				//Get some attributes back!
				$this->setCSS($this->backupcss);
			}
		}
	}

	function printlistbuffer() {
		//! @return void
		//! @desc Prints all list-related buffered info
		//Save x coordinate
		$x = $this->oldx;
		foreach($this->listitem as $item) {
			//Set default width & height values
			$this->divwidth = $this->pgwidth;
			$this->divheight = $this->lineheight;
			//Get list's buffered data
			$lvl = $item[0];
			$num = $item[1];
			$this->textbuffer = $item[2];
			$occur = $item[3];
			$type = $this->listlist[$lvl][$occur]['TYPE'];
			$maxnum = $this->listlist[$lvl][$occur]['MAXNUM'];
			switch($type) {	//Format type

				case 'A':
					$num = dec2alpha($num,true);
					$maxnum = dec2alpha($maxnum,true);
					$type = str_pad($num,strlen($maxnum),' ',STR_PAD_LEFT) . ".";
					break;
				case 'a':
					$num = dec2alpha($num,false);
					$maxnum = dec2alpha($maxnum,false);
					$type = str_pad($num,strlen($maxnum),' ',STR_PAD_LEFT) . ".";
					break;
				case 'I':
					$num = dec2roman($num,true);
					$maxnum = dec2roman($maxnum,true);
					$type = str_pad($num,strlen($maxnum),' ',STR_PAD_LEFT) . ".";
					break;
				case 'i':
					$num = dec2roman($num,false);
					$maxnum = dec2roman($maxnum,false);
					$type = str_pad($num,strlen($maxnum),' ',STR_PAD_LEFT) . ".";
					break;
				case '1':
					$type = str_pad($num,strlen($maxnum),' ',STR_PAD_LEFT) . ".";
					break;
				case 'disc':
					$type = chr(149);
					break;
				case 'square':
					$type = chr(110); //black square on Zapfdingbats font
					break;
				case 'circle':
					$type = chr(186);
					break;
				default: break;
			}
			$this->x = (5*$lvl) + $x; //Indent list
			//Get bullet width including margins
			$oldsize = $this->FontSize * $this->k;
			if ($type == chr(110)) $this->SetFont('zapfdingbats','',5);
			$type .= ' ';
			$blt_width = $this->GetStringWidth($type)+$this->cMargin*2;
			//Output bullet
			$this->Cell($blt_width,5,$type,'','','L');
			$this->SetFont('arial','',$oldsize);
			$this->divwidth = $this->divwidth + $this->lMargin - $this->x;
			//Print content
			$this->printbuffer($this->textbuffer);
			$this->textbuffer=array();
		}
		//Reset all used values
		$this->listoccur = array();
		$this->listitem = array();
		$this->listlist = array();
		$this->listlvl = 0;
		$this->listnum = 0;
		$this->listtype = '';
		$this->textbuffer = array();
		$this->divwidth = 0;
		$this->divheight = 0;
		$this->oldx = -1;
		//At last, but not least, skip a line
		$this->Ln($this->lineheight);
	}
	
	function printbuffer($arrayaux,$outofblock=false,$is_table=false) {
		//Save some previous parameters
		$this->SetFont($this->PDFFontFamily, $this->FontStyle, $this->FontSizePt);
		$save = array();
		$save['strike'] = $this->strike;
		$save['SUP'] = $this->SUP;
		$save['SUB'] = $this->SUB;
		$save['DOTTED'] = $this->dotted_on;
		$save['DASHED'] = $this->dash_on;
		$this->SetDash(); //restore to no dash
		$this->dash_on = false;
		$this->dotted_on = false;
		$bak_y = $this->y;
		$bak_x = $this->x;
		$align = $this->divalign;
		$oldpage = $this->page;

		//Overall object size == $old_height
		//Line height == $this->divheight
		$old_height = $this->divheight;
		if ($is_table) {
			$this->divheight = 1.1*$this->lineheight;
			$fill = 0;
		} else {
			$this->divheight = $this->lineheight;
			if ($this->FillColor == '1.000 g') $fill = 0; //avoid useless background painting (1.000 g == white background color)
			else $fill = 1;
		}

		$this->newFlowingBlock( $this->divwidth,$this->divheight,$this->divborder,$align,$fill,$is_table);

		$array_size = count($arrayaux);
		for($i=0;$i < $array_size; $i++) {
			$vetor = $arrayaux[$i];
			if ($i == 0 and $vetor[0] != "\n") $vetor[0] = ltrim($vetor[0]);
			if (empty($vetor[0]) and empty($vetor[7])) continue; //Ignore empty text and not carrying an internal link
			//Activating buffer properties
			if(isset($vetor[10]) and !empty($vetor[10])) { //Background color
				$cor = $vetor[10];
				$this->SetFillColor($cor['R'],$cor['G'],$cor['B']);
				$this->divbgcolor = true;
			}
			// Outline parameters
			if(isset($vetor[9]) and !empty($vetor[9])) {
				$cor = $vetor[9]['COLOR'];
				$outlinewidth = $vetor[9]['WIDTH'];
				$this->SetTextOutline($outlinewidth,$cor['R'],$cor['G'],$cor['B']);
				$this->outline_on = true;
			}
			// strike-through the text
			if(isset($vetor[8]) and $vetor[8] === true) {
				$this->strike = true;
			}
			// internal link: <a name="anyvalue">
			if(isset($vetor[7]) and $vetor[7] != '') {
				$this->internallink[$vetor[7]] = array("Y"=>$this->y,"PAGE"=>$this->page );
				$this->Bookmark($vetor[7]." (pg. $this->page)",0,$this->y);
				if (empty($vetor[0])) continue; //Ignore empty text
			}
			// Subscript 
			if(isset($vetor[6]) and $vetor[6] === true) {
				$this->SUB = true;
				$this->SetFontSize(6);
			}
			// Superscript
			if(isset($vetor[5]) and $vetor[5] === true) {
				$this->SUP = true;
				$this->SetFontSize(6);
			}
			if(isset($vetor[4]) and $vetor[4] != '') $this->SetFont($vetor[4]); // Font Family
			if (!empty($vetor[3])) {
				//Font Color
				
				$cor = $vetor[3];
				$this->SetTextColor($cor['R'],$cor['G'],$cor['B']);
			}
			if(isset($vetor[2]) and $vetor[2] != '') { //Bold,Italic,Underline styles
				if (strpos($vetor[2],"B") !== false) $this->SetStyle('B',true);
				if (strpos($vetor[2],"I") !== false) $this->SetStyle('I',true);
				if (strpos($vetor[2],"U") !== false) $this->SetStyle('U',true);
			}
			if(isset($vetor[1]) and $vetor[1] != '') { //LINK
				if (strpos($vetor[1],".") === false) { //assuming every external link has a dot indicating extension (e.g: .html .txt .zip www.somewhere.com etc.) 
					//Repeated reference to same anchor?
					while(array_key_exists($vetor[1],$this->internallink)) $vetor[1]="#".$vetor[1];
					$this->internallink[$vetor[1]] = $this->AddLink();
					$vetor[1] = $this->internallink[$vetor[1]];
				}
				$this->HREF = $vetor[1];
				$this->SetTextColor(0,0,255);
				$this->SetStyle('U',true);
			}
			//Print-out special content
			if (isset($vetor[0]) and $vetor[0]{0} == '?' and $vetor[0]{1} == '?' and $vetor[0]{2} == '?') { //identifier has been identified!
				$content = explode("|x%s#pa@c!e|",$vetor[0]);
				$texto = $content[2];
				$content = explode(",",$content[1]);
				foreach($content as $value) {
					$value = explode("=",$value);
					$specialcontent[$value[0]] = $value[1];
				}
				if ($this->flowingBlockAttr[ 'contentWidth' ] > 0) { // Print out previously accumulated content
					$width_used = $this->flowingBlockAttr[ 'contentWidth' ] / $this->k;
					//Restart Flowing Block
					$this->finishFlowingBlock($outofblock);
					$this->x = $bak_x + ($width_used % $this->divwidth) + 0.5;// 0.5 == margin
					$this->y -= ($this->lineheight + 0.5);
					$extrawidth = 0; //only to be used in case $specialcontent['width'] does not contain all used width (e.g. Select Box)
					if ($specialcontent['type'] == 'select') $extrawidth = 7; //arrow box + margin
					if(($this->x - $bak_x) + $specialcontent['width'] + $extrawidth > $this->divwidth ) {
						$this->x = $bak_x;
						$this->y += $this->lineheight - 1;
					}
					$this->newFlowingBlock( $this->divwidth,$this->divheight,$this->divborder,$align,$fill,$is_table );
				}
					
				switch(strtoupper($specialcontent['type'])) {

					case 'IMAGE':
						//xpos and ypos used in order to support: <div align='center'><img ...></div>
						$xpos = 0;
						$ypos = 0;
						if (isset($specialcontent['ypos']) and $specialcontent['ypos'] != '') $ypos = (float)$specialcontent['ypos']; 
						if (isset($specialcontent['xpos']) and $specialcontent['xpos'] != '') $xpos = (float)$specialcontent['xpos'];
						$width_used = (($this->x - $bak_x) + $specialcontent['width'])*$this->k; //in order to adjust x coordinate later
						//Is this the best way of fixing x,y coordinates?
						$fix_x = ($this->x+2) * $this->k + ($xpos*$this->k); //+2 margin
						$fix_y = ($this->h - (($this->y+2) + $specialcontent['height'])) * $this->k;//+2 margin
						$imgtemp = explode(" ",$texto);
						$imgtemp[5]=$fix_x; // x
						$imgtemp[6]=$fix_y; // y
						$texto = implode(" ",$imgtemp);
						$this->_out($texto);
						//Readjust x coordinate in order to allow text to be placed after this form element
						$this->x = $bak_x;
						$spacesize = $this->CurrentFont[ 'cw' ][ ' ' ] * ( $this->FontSizePt / 1000 );
						$spacenum = (integer)ceil(($width_used / $spacesize));
						//Consider the space used so far in this line as a bunch of spaces
						if ($ypos != 0) $this->Ln($ypos);
						else $this->WriteFlowingBlock(str_repeat(' ',$spacenum));
						break;
					case 'INPUT':
						switch($specialcontent['subtype']) {
							case 'PASSWORD':
							case 'TEXT': //Draw TextField
								$width_used = (($this->x - $bak_x) + $specialcontent['width'])*$this->k; //in order to adjust x coordinate later
								$this->SetFillColor(235,235,235);
								$this->x += 1;
								$this->y += 1;
								$this->Rect($this->x,$this->y,$specialcontent['width'],4.5,'DF');// 4.5 in order to avoid overlapping
								if($texto != '') {
									$this->x += 1;
									$this->Write(5,$texto,$this->x);
									$this->x -= $this->GetStringWidth($texto);
								}
								$this->SetFillColor(255);
								$this->y -= 1;
								//Readjust x coordinate in order to allow text to be placed after this form element
								$this->x = $bak_x;
								$spacesize = $this->CurrentFont[ 'cw' ][ ' ' ] * ( $this->FontSizePt / 1000 );
								$spacenum = (integer)ceil(($width_used / $spacesize));
								//Consider the space used so far in this line as a bunch of spaces
								$this->WriteFlowingBlock(str_repeat(' ',$spacenum));
								break;
							case 'CHECKBOX': //Draw Checkbox
								$width_used = (($this->x - $bak_x) + $specialcontent['width'])*$this->k; //in order to adjust x coordinate later
								$checked = $texto;
								$this->SetFillColor(235,235,235);
								$this->y += 1;
								$this->x += 1;
								$this->Rect($this->x,$this->y,3,3,'DF');
								if ($checked) {
									$this->Line($this->x,$this->y,$this->x+3,$this->y+3);
									$this->Line($this->x,$this->y+3,$this->x+3,$this->y);
								}
								$this->SetFillColor(255);
								$this->y -= 1;
								//Readjust x coordinate in order to allow text to be placed after this form element
								$this->x = $bak_x;
								$spacesize = $this->CurrentFont[ 'cw' ][ ' ' ] * ( $this->FontSizePt / 1000 );
								$spacenum = (integer)ceil(($width_used / $spacesize));
								//Consider the space used so far in this line as a bunch of spaces
								$this->WriteFlowingBlock(str_repeat(' ',$spacenum));
								break;

							case 'RADIO': //Draw Radio button

								$width_used = (($this->x - $bak_x) + $specialcontent['width']+0.5)*$this->k; //in order to adjust x coordinate later
								$checked = $texto;
								$this->x += 2;
								$this->y += 1.5;
								$this->Circle($this->x,$this->y+1.2,1,'D');
								$this->_out('0.000 g');
								if ($checked) $this->Circle($this->x,$this->y+1.2,0.4,'DF');
								$this->y -= 1.5;
								//Readjust x coordinate in order to allow text to be placed after this form element
								$this->x = $bak_x;
								$spacesize = $this->CurrentFont[ 'cw' ][ ' ' ] * ( $this->FontSizePt / 1000 );
								$spacenum = (integer)ceil(($width_used / $spacesize));
								//Consider the space used so far in this line as a bunch of spaces
								$this->WriteFlowingBlock(str_repeat(' ',$spacenum));
								break;
							case 'BUTTON': // Draw a button
							case 'SUBMIT':
							case 'RESET':
								$nihil = ($specialcontent['width']-$this->GetStringWidth($texto))/2;
								$this->x += 1.5;
								$this->y += 1;
								$this->SetFillColor(190,190,190);
								$this->Rect($this->x,$this->y,$specialcontent['width'],4.5,'DF'); // 4.5 in order to avoid overlapping
								$this->x += $nihil;
								$this->Write(5,$texto,$this->x);
								$this->x += $nihil;
								$this->SetFillColor(255);
								$this->y -= 1;
								break;
							default: break;
						}
						break;

					case 'SELECT':

						$width_used = (($this->x - $bak_x) + $specialcontent['width'] + 8)*$this->k; //in order to adjust x coordinate later
						$this->SetFillColor(235,235,235); //light gray
						$this->x += 1.5;
						$this->y += 1;
						$this->Rect($this->x,$this->y,$specialcontent['width']+2,$this->lineheight,'DF'); // +2 == margin
						$this->x += 1;
						if ($texto != '') $this->Write($this->lineheight,$texto,$this->x); //the combobox content
						$this->x += $specialcontent['width'] - $this->GetStringWidth($texto) + 2;
						$this->SetFillColor(190,190,190); //dark gray
						$this->Rect($this->x-1,$this->y,5,5,'DF'); //Arrow Box
						$this->SetFont('zapfdingbats');
						$this->Write($this->lineheight,chr(116),$this->x); //Down arrow
						$this->SetFont('arial');
						$this->SetFillColor(255);
						//Readjust x coordinate in order to allow text to be placed after this form element
						$this->x = $bak_x;
						$spacesize = $this->CurrentFont[ 'cw' ][ ' ' ] * ( $this->FontSizePt / 1000 );
						$spacenum = (integer)ceil(($width_used / $spacesize));
						//Consider the space used so far in this line as a bunch of spaces
						$this->WriteFlowingBlock(str_repeat(' ',$spacenum));
						break;
					case 'TEXTAREA':
						//Setup TextArea properties
						$this->SetFillColor(235,235,235);
						$this->SetFont('courier');
						$this->currentfont='courier';
						$ta_lines = $specialcontent['lines'];
						$ta_height = 1.1*$this->lineheight*$ta_lines;
						$ta_width = $specialcontent['width'];
						//Adjust x,y coordinates
						$this->x += 1.5;
						$this->y += 1.5;
						$linesneeded = $this->WordWrap($texto,$ta_width);
						if ( $linesneeded > $ta_lines ) { //Too many words inside textarea
							$textoaux = explode("\n",$texto);
							$texto = '';
							for($i=0;$i<$ta_lines;$i++) {
								if ($i == $ta_lines-1) $texto .= $textoaux[$i];
								else $texto .= $textoaux[$i] . "\n";
							}
							//Inform the user that some text has been truncated
							$texto{strlen($texto)-1} = ".";
							$texto{strlen($texto)-2} = ".";
							$texto{strlen($texto)-3} = ".";
						}
						$backup_y = $this->y;
						$backup_x = $this->x;
						$this->Rect($this->x,$this->y,$ta_width+3,$ta_height,'DF');
						if ($texto != '') $this->MultiCell($ta_width+3,$this->lineheight,$texto);
						$this->y = $backup_y - 1.5;
						$this->x = $backup_x + $ta_width + 2.5;
						$this->SetFillColor(255);
						$this->SetFont('arial');
						$this->currentfont='';
						break;
					default: break;
				}
			} else { //THE text
				
				if ($vetor[0] == "\n") { //We are reading a <BR> now turned into newline ("\n")
					//Restart Flowing Block
					$this->finishFlowingBlock($outofblock);
					if($outofblock) $this->Ln($this->lineheight);
					$this->x = $bak_x;
					$this->newFlowingBlock( $this->divwidth,$this->divheight,$this->divborder,$align,$fill,$is_table );
				} else $this->WriteFlowingBlock( $vetor[0] , $outofblock );
			}
			//Check if it is the last element. If so then finish printing the block
			if ($i == ($array_size-1)) $this->finishFlowingBlock($outofblock);
			//Now we must deactivate what we have used
			if( (isset($vetor[1]) and $vetor[1] != '') or $this->HREF != '') {
				$this->SetTextColor(0);
				$this->SetStyle('U',false);
				$this->HREF = '';
			}
			if(isset($vetor[2]) and $vetor[2] != '') {
				$this->SetStyle('B',false);
				$this->SetStyle('I',false);
				$this->SetStyle('U',false);
			}
			if(isset($vetor[3]) and $vetor[3] != '') {
				unset($cor);
				$this->SetTextColor(0);
			}
			if(isset($vetor[4]) and $vetor[4] != '') $this->SetFont('arial');
			if(isset($vetor[5]) and $vetor[5] === true) {
				$this->SUP = false;
				$this->SetFontSize(11);
			}
			if(isset($vetor[6]) and $vetor[6] === true) {
				$this->SUB = false;
				$this->SetFontSize(11);
			}
			//vetor7-internal links
			if(isset($vetor[8]) and $vetor[8] === true) { // strike-through the text
				$this->strike = false;
			}
			if(isset($vetor[9]) and !empty($vetor[9])) { // Outline parameters
				$this->SetTextOutline(false);
				$this->outline_on = false;
			}
			if(isset($vetor[10]) and !empty($vetor[10])) { //Background color
				$this->SetFillColor(255);
				$this->divbgcolor = false;
			}
		}//end of for(i=0;i<arraysize;i++)

		//Restore some previously set parameters
		$this->strike = $save['strike'];
		$this->SUP = $save['SUP'];
		$this->SUB = $save['SUB'];
		$this->dotted_on = $save['DOTTED'];
		$this->dash_on = $save['DASHED'];
		if ($this->dash_on) $this->SetDash(2,2);
		//Check whether we have borders to paint or not
		//(only works 100% if whole content spans only 1 page)
		if ($this->cssbegin and ($this->divborder or $this->dash_on or $this->dotted_on or $this->divbgcolor)) {
			if ($oldpage != $this->page) {
				//Only border on last page is painted (known bug)
				$x = $this->lMargin;
				$y = $this->tMargin;
				$old_height = $this->y - $y;
			} else {
				if ($this->oldx < 0) $x	= $this->x;
				else $x = $this->oldx;
				if ($this->oldy < 0) $y	= $this->y - $old_height;
				else $y = $this->oldy;
			}
			if ($this->divborder) $this->Rect($x,$y,$this->divwidth,$old_height);
			if ($this->dash_on) $this->Rect($x,$y,$this->divwidth,$old_height);
			if ($this->dotted_on) $this->DottedRect($x,$y,$this->divwidth,$old_height);
			$this->x = $bak_x;
		}
	}

	function Reset()
	{
	//! @return void
	//! @desc Resets several class attributes

	//	if ( $this->issetcolor !== true )
	//	{
			$this->SetTextColor(0);
			$this->SetDrawColor(0);
			$this->SetFillColor(255);
			$this->colorarray = array();
			$this->bgcolorarray = array();
	$this->issetcolor = false;
	//	}
	$this->HREF = '';
	$this->SetTextOutline(false);

	//$this->strike = false;

		$this->SetFontSize(11);
		$this->SetStyle('B',false);
		$this->SetStyle('I',false);
		$this->SetStyle('U',false);
		$this->SetFont('arial');
		$this->divwidth = 0;
		$this->divheight = 0;
		$this->divalign = "L";
		$this->divrevert = false;
		$this->divborder = 0;
		$this->divbgcolor = false;
		$this->toupper = false;
		$this->tolower = false;
		$this->SetDash(); //restore to no dash
		$this->dash_on = false;
		$this->dotted_on = false;
		$this->oldx = -1;
		$this->oldy = -1;
	}

	function ReadMetaTags($html)
	{
	//! @return void
	//! @desc Pass meta tag info to PDF file properties
		$regexp = '/ (\\w+?)=([^\\s>"]+)/si'; // changes anykey=anyvalue to anykey="anyvalue" (only do this when this happens inside tags)
		$html = preg_replace($regexp," \$1=\"\$2\"",$html);
		$regexp = '/<meta .*?(name|content)="(.*?)" .*?(name|content)="(.*?)".*?>/si';
		preg_match_all($regexp,$html,$aux);
		
		$firstattr = $aux[1];
		$secondattr = $aux[3];
		for( $i = 0 ; $i < count($aux[0]) ; $i++)
		{

			 $name = ( strtoupper($firstattr[$i]) == "NAME" )? strtoupper($aux[2][$i]) : strtoupper($aux[4][$i]);
			 $content = ( strtoupper($firstattr[$i]) == "CONTENT" )? $aux[2][$i] : $aux[4][$i];
			 switch($name)
			 {
				 case "KEYWORDS": $this->SetKeywords($content); break;
				 case "AUTHOR": $this->SetAuthor($content); break;
				 case "DESCRIPTION": $this->SetSubject($content); break;
			 }
		}
		//Comercial do Aplicativo usado (no caso um script):
		$this->SetCreator("X-Space 2.0");
	}

	//////////////////
	/// CSS parser ///
	//////////////////
	function ReadCSS($html)
	{
	//! @desc CSS parser
	//! @return string

	/*
	* This version ONLY supports:	.class {...} / #id { .... }
	* It does NOT support: body{...} / a#hover { ... } / p.right { ... } / other mixed names
	* This function must read the CSS code (internal or external) and order its value inside $this->CSS. 
	*/

		$match = 0; // no match for instance
		$regexp = ''; // This helps debugging: showing what is the REAL string being processed
		
		//CSS inside external files
		$regexp = '/<link rel="stylesheet".*?href="(.+?)"\\s*?\/?>/si'; 
		$match = preg_match_all($regexp,$html,$CSSext);
		$ind = 0;

		while($match){
			//Fix path value
			$path = $CSSext[1][$ind];
			$path = str_replace("\\","/",$path); //If on Windows
			//Get link info and obtain its absolute path
			$regexp = '|^./|';
			$path = preg_replace($regexp,'',$path);
			if (strpos($path,"../") !== false ) //It is a Relative Link
			{
				 $backtrackamount = substr_count($path,"../");
				 $maxbacktrack = substr_count($this->basepath,"/") - 1;
				 $filepath = str_replace("../",'',$path);
				 $path = $this->basepath;
				 //If it is an invalid relative link, then make it go to directory root
				 if ($backtrackamount > $maxbacktrack) $backtrackamount = $maxbacktrack;
				 //Backtrack some directories
				 for( $i = 0 ; $i < $backtrackamount + 1 ; $i++ ) $path = substr( $path, 0 , strrpos($path,"/") );
				 $path = $path . "/" . $filepath; //Make it an absolute path
			}
			elseif( strpos($path,":/") === false) //It is a Local Link
			{
					$path = $this->basepath . $path; 
			}
			//Do nothing if it is an Absolute Link
			//END of fix path value
			$CSSextblock = file_get_contents($path);	

			//Get class/id name and its characteristics from $CSSblock[1]
			$regexp = '/[.# ]([^.]+?)\\s*?\{(.+?)\}/s'; // '/s' PCRE_DOTALL including \n
			preg_match_all( $regexp, $CSSextblock, $extstyle);

			//Make CSS[Name-of-the-class] = array(key => value)
			$regexp = '/\\s*?(\\S+?):(.+?);/si';

			for($i=0; $i < count($extstyle[1]) ; $i++)
			{
				preg_match_all( $regexp, $extstyle[2][$i], $extstyleinfo);
				$extproperties = $extstyleinfo[1];
				$extvalues = $extstyleinfo[2];
				for($j = 0; $j < count($extproperties) ; $j++) 
				{
					//Array-properties and Array-values must have the SAME SIZE!
					$extclassproperties[strtoupper($extproperties[$j])] = trim($extvalues[$j]);
				}
				$this->CSS[$extstyle[1][$i]] = $extclassproperties;
				$extproperties = array();
				$extvalues = array();
				$extclassproperties = array();
			}
			$match--;
			$ind++;
		} //end of match

		$match = 0; // reset value, if needed

		//CSS internal
		//Get content between tags and order it, using regexp
		$regexp = '/<style.*?>(.*?)<\/style>/si'; // it can be <style> or <style type="txt/css"> 
		$match = preg_match($regexp,$html,$CSSblock);

		if ($match) {
			//Get class/id name and its characteristics from $CSSblock[1]
			$regexp = '/[.#]([^.]+?)\\s*?\{(.+?)\}/s'; // '/s' PCRE_DOTALL including \n
			preg_match_all( $regexp, $CSSblock[1], $style);

			//Make CSS[Name-of-the-class] = array(key => value)
			$regexp = '/\\s*?(\\S+?):(.+?);/si';

			for($i=0; $i < count($style[1]) ; $i++)
			{
				preg_match_all( $regexp, $style[2][$i], $styleinfo);
				$properties = $styleinfo[1];
				$values = $styleinfo[2];
				for($j = 0; $j < count($properties) ; $j++) 
				{
					//Array-properties and Array-values must have the SAME SIZE!
					$classproperties[strtoupper($properties[$j])] = trim($values[$j]);
				}
				$this->CSS[$style[1][$i]] = $classproperties;
				$properties = array();
				$values = array();
				$classproperties = array();
			}
		} // end of match

		//Remove CSS (tags and content), if any
		$regexp = '/<style.*?>(.*?)<\/style>/si'; // it can be <style> or <style type="txt/css"> 
		$html = preg_replace($regexp,'',$html);

		return $html;
	}

	function readInlineCSS($html)
	{
	//! @return array
	//! @desc Reads inline CSS and returns an array of properties

		//Fix incomplete CSS code
		$size = strlen($html)-1;
		if ($html{$size} != ';') $html .= ';';
		//Make CSS[Name-of-the-class] = array(key => value)
		$regexp = '|\\s*?(\\S+?):(.+?);|i';
		preg_match_all( $regexp, $html, $styleinfo);
		$properties = $styleinfo[1];
		$values = $styleinfo[2];
		//Array-properties and Array-values must have the SAME SIZE!
		$classproperties = array();
		for($i = 0; $i < count($properties) ; $i++) $classproperties[strtoupper($properties[$i])] = trim($values[$i]);
		
		return $classproperties;
	}

	function setCSS($arrayaux)
	{
	//! @return void
	//! @desc Change some class attributes according to CSS properties
		if (!is_array($arrayaux)) return; //Removes PHP Warning
		foreach($arrayaux as $k => $v)
		{
			switch($k){
					case 'WIDTH':
							$this->divwidth = ConvertSize($v,$this->pgwidth);
							break;
					case 'HEIGHT':
							$this->divheight = ConvertSize($v,$this->pgwidth);
							break;
					case 'BORDER': // width style color (width not supported correctly - it is always considered as normal)
							$prop = explode(' ',$v);
							if ( count($prop) != 3 ) break; // Not supported: borders not fully declared
							//style: dashed dotted none (anything else => solid )
							if (strnatcasecmp($prop[1],"dashed") == 0) //found "dashed"! (ignores case)
							{
								 $this->dash_on = true;
								 $this->SetDash(2,2); //2mm on, 2mm off
							}
							elseif (strnatcasecmp($prop[1],"dotted") == 0) //found "dotted"! (ignores case)
							{
								 $this->dotted_on = true;
							}
							elseif (strnatcasecmp($prop[1],"none") == 0) $this->divborder = 0;
							else $this->divborder = 1;
							//color
							$coul = ConvertColor($prop[2]);
							$this->SetDrawColor($coul['R'],$coul['G'],$coul['B']);
							$this->issetcolor=true;
							break;
					case 'FONT-FAMILY': // one of the $this->fontlist fonts
							//If it is a font list, get all font types
							$aux_fontlist = explode(",",$v);
							$fontarraysize = count($aux_fontlist);
							for($i=0;$i<$fontarraysize;$i++)
							{
								 $fonttype = $aux_fontlist[$i];
								 $fonttype = trim($fonttype);
								 //If font is found, set it, and exit loop
								 if ( in_array(strtolower($fonttype), $this->fontlist) ) {$this->SetFont(strtolower($fonttype));break;}
								 //If font = "courier new" for example, try simply looking for "courier"
								 $fonttype = explode(" ",$fonttype);
								 $fonttype = $fonttype[0];
								 if ( in_array(strtolower($fonttype), $this->fontlist) ) {$this->SetFont(strtolower($fonttype));break;}
							}
							break;
					case 'FONT-SIZE': //Does not support: smaller, larger
							if(is_numeric($v{0}))
							{
								 $mmsize = ConvertSize($v,$this->pgwidth);
								 $this->SetFontSize( $mmsize*(72/25.4) ); //Get size in points (pt)
							}
							else{
								$v = strtoupper($v);
								switch($v)
								{
									 //Values obtained from http://www.w3schools.com/html/html_reference.asp
									 case 'XX-SMALL': $this->SetFontSize( (0.7)* 11);
											 break;
									 case 'X-SMALL': $this->SetFontSize( (0.77) * 11);
											 break;
									 case 'SMALL': $this->SetFontSize( (0.86)* 11);
											 break;
									 case 'MEDIUM': $this->SetFontSize(11);
											 break;
									 case 'LARGE': $this->SetFontSize( (1.2)*11);
											 break;
									 case 'X-LARGE': $this->SetFontSize( (1.5)*11);
											 break;
									 case 'XX-LARGE': $this->SetFontSize( 2*11);
											 break;
								}
							}
							break;
					case 'FONT-STYLE': // italic normal oblique
							switch (strtoupper($v))
							{
								case 'ITALIC': 
								case 'OBLIQUE': 
													$this->SetStyle('I',true);
													break;
								case 'NORMAL': break;
							}
							break;
					case 'FONT-WEIGHT': // normal bold //Does not support: bolder, lighter, 100..900(step value=100)
							switch (strtoupper($v))
							{
								case 'BOLD': 
													$this->SetStyle('B',true);
													break;
								case 'NORMAL': break;
							}
							break;
					case 'TEXT-DECORATION': // none underline //Does not support: overline, blink
							switch (strtoupper($v))
							{
								case 'LINE-THROUGH':
													$this->strike = true;
													break;
								case 'UNDERLINE':
													$this->SetStyle('U',true);
													break;
								case 'NONE': break;
							}
					case 'TEXT-TRANSFORM': // none uppercase lowercase //Does not support: capitalize
							switch (strtoupper($v)) //Not working 100%
							{ 
								case 'UPPERCASE':
													$this->toupper=true;
													break;
								case 'LOWERCASE':
													$this->tolower=true;
													break;
								case 'NONE': break;
							}
					case 'TEXT-ALIGN': //left right center justify
							switch (strtoupper($v))
							{
								case 'LEFT': 
													$this->divalign="L";
													break;
								case 'CENTER': 
													$this->divalign="C";
													break;
								case 'RIGHT': 
													$this->divalign="R";
													break;
								case 'JUSTIFY': 
													$this->divalign="J";
													break;
							}
							break;
					case 'DIRECTION': //ltr(default) rtl
							if (strtolower($v) == 'rtl') $this->divrevert = true;
							break;
					case 'BACKGROUND': // bgcolor only
							$cor = ConvertColor($v);
							$this->bgcolorarray = $cor;
							$this->SetFillColor($cor['R'],$cor['G'],$cor['B']);
							$this->divbgcolor = true;
							break;
					case 'COLOR': // font color
							$cor = ConvertColor($v);
							$this->colorarray = $cor;
							$this->SetTextColor($cor['R'],$cor['G'],$cor['B']);
							$this->issetcolor=true;
							break;
			}//end of switch($k)
		 }//end of foreach
	}

	function SetStyle($tag,$enable)
	{
	//! @return void
	//! @desc Enables/Disables B,I,U styles
		//Modify style and select corresponding font
		$this->$tag+=($enable ? 1 : -1);
		$style='';
		//Fix some SetStyle misuse
		if ($this->$tag < 0) $this->$tag = 0;
		if ($this->$tag > 1) $this->$tag = 1;
		foreach(array('B','I','U') as $s)
			if($this->$s>0)
				$style.=$s;
				
		$this->currentstyle=$style;
		$this->SetFont('',$style);
	}

	function DisableTags($str='')
	{
	//! @return void
	//! @desc Disable some tags using ',' as separator. Enable all tags calling this function without parameters.
		if ($str == '') //enable all tags
		{
			//Insert new supported tags in the long string below.
			$this->enabledtags = "<tt><kbd><samp><option><outline><span><newpage><page_break><s><strike><del><bdo><big><small><address><ins><cite><font><center><sup><sub><input><select><option><textarea><title><form><ol><ul><li><h1><h2><h3><h4><h5><h6><pre><b><u><i><a><img><p><br><strong><em><code><th><tr><blockquote><hr><td><tr><table><div>";
		}
		else
		{
			$str = explode(",",$str);
			foreach($str as $v) $this->enabledtags = str_replace(trim($v),'',$this->enabledtags);
		}
	}

	////////////////////////TABLE CODE (from PDFTable)/////////////////////////////////////
	//Thanks to vietcom (vncommando at yahoo dot com)
	/*		 Modified by Renato Coelho
		 in order to print tables that span more than 1 page and to allow 
		 bold,italic and the likes inside table cells (and alignment now works with styles!)
	*/

	//table		Array of (w, h, bc, nr, wc, hr, cells)
	//w			Width of table
	//h			Height of table
	//nc		Number column
	//nr		Number row
	//hr		List of height of each row
	//wc		List of width of each column
	//cells		List of cells of each rows, cells[i][j] is a cell in the table
	function _tableColumnWidth(&$table){
	//! @return void
		$cs = &$table['cells'];
		$mw = $this->getStringWidth('W');
		$nc = $table['nc'];
		$nr = $table['nr'];
		$listspan = array();
		//Xac dinh do rong cua cac cell va cac cot tuong ung
		for($j = 0 ; $j < $nc ; $j++ ) //columns
		{
			$wc = &$table['wc'][$j];
			for($i = 0 ; $i < $nr ; $i++ ) //rows
			{
				if (isset($cs[$i][$j]) && $cs[$i][$j])
				{
					$c = &$cs[$i][$j];
					$miw = $mw;
					if (isset($c['maxs']) and $c['maxs'] != '') $c['s'] = $c['maxs'];
					$c['maw']	= $c['s'];
					if (isset($c['nowrap'])) $miw = $c['maw'];
					if (isset($c['w']))
					{
						if ($miw<$c['w'])	$c['miw'] = $c['w'];
						if ($miw>$c['w'])	$c['miw'] = $c['w']		= $miw;
						if (!isset($wc['w'])) $wc['w'] = 1;
					}
					else $c['miw'] = $miw;
					if ($c['maw']	< $c['miw']) $c['maw'] = $c['miw'];
					if (!isset($c['colspan']))
					{
						if ($wc['miw'] < $c['miw'])		$wc['miw']	= $c['miw'];
						if ($wc['maw'] < $c['maw'])		$wc['maw']	= $c['maw'];
					}
					else $listspan[] = array($i,$j);
					//Check if minimum width of the whole column is big enough for a huge word to fit
					$auxtext = implode("",$c['text']);
					$minwidth = $this->WordWrap($auxtext,$wc['miw']-2);// -2 == margin
					if ($minwidth < 0 and (-$minwidth) > $wc['miw']) $wc['miw'] = (-$minwidth) +2; //increase minimum width
					if ($wc['miw'] > $wc['maw']) $wc['maw'] = $wc['miw']; //update maximum width, if needed
				}
			}//rows
		}//columns
		//Xac dinh su anh huong cua cac cell colspan len cac cot va nguoc lai
		$wc = &$table['wc'];
		foreach ($listspan as $span)
		{
			list($i,$j) = $span;
			$c = &$cs[$i][$j];
			$lc = $j + $c['colspan'];
			if ($lc > $nc) $lc = $nc;
			
			$wis = $wisa = 0;
			$was = $wasa = 0;
			$list = array();
			for($k=$j;$k<$lc;$k++)
			{
				$wis += $wc[$k]['miw'];
				$was += $wc[$k]['maw'];
				if (!isset($c['w']))
				{
					$list[] = $k;
					$wisa += $wc[$k]['miw'];
					$wasa += $wc[$k]['maw'];
				}
			}
			if ($c['miw'] > $wis)
			{
				if (!$wis)
				{//Cac cot chua co kich thuoc => chia deu
					for($k=$j;$k<$lc;$k++) $wc[$k]['miw'] = $c['miw']/$c['colspan'];
				}
				elseif(!count($list))
				{//Khong co cot nao co kich thuoc auto => chia deu phan du cho tat ca
					$wi = $c['miw'] - $wis;
					for($k=$j;$k<$lc;$k++) $wc[$k]['miw'] += ($wc[$k]['miw']/$wis)*$wi;
				}
				else
				{//Co mot so cot co kich thuoc auto => chia deu phan du cho cac cot auto
					$wi = $c['miw'] - $wis;
					foreach ($list as $k)	$wc[$k]['miw'] += ($wc[$k]['miw']/$wisa)*$wi;
				}
			}
			if ($c['maw'] > $was)
			{
				if (!$wis)
				{//Cac cot chua co kich thuoc => chia deu
					for($k=$j;$k<$lc;$k++) $wc[$k]['maw'] = $c['maw']/$c['colspan'];
				}
				elseif (!count($list))
				{
				//Khong co cot nao co kich thuoc auto => chia deu phan du cho tat ca
					$wi = $c['maw'] - $was;
					for($k=$j;$k<$lc;$k++) $wc[$k]['maw'] += ($wc[$k]['maw']/$was)*$wi;
				}
				else
				{//Co mot so cot co kich thuoc auto => chia deu phan du cho cac cot auto
					$wi = $c['maw'] - $was;
					foreach ($list as $k)	$wc[$k]['maw'] += ($wc[$k]['maw']/$wasa)*$wi;
				}
			}
		}
	}

	function _tableWidth(&$table){
	//! @return void
	//! @desc Calculates the Table Width
	// @desc Xac dinh chieu rong cua table
		$widthcols = &$table['wc'];
		$numcols = $table['nc'];
		$tablewidth = 0;
		for ( $i = 0 ; $i < $numcols ; $i++ )
		{
			$tablewidth += isset($widthcols[$i]['w']) ? $widthcols[$i]['miw'] : $widthcols[$i]['maw'];
		}
		if ($tablewidth > $this->pgwidth) $table['w'] = $this->pgwidth;
		if (isset($table['w']))
		{
			$wis = $wisa = 0;
			$list = array();
			for( $i = 0 ; $i < $numcols ; $i++ )
			{
				$wis += $widthcols[$i]['miw'];
				if (!isset($widthcols[$i]['w'])){ $list[] = $i;$wisa += $widthcols[$i]['miw'];}
			}
			if ($table['w'] > $wis)
			{
				if (!count($list))
				{//Khong co cot nao co kich thuoc auto => chia deu phan du cho tat ca
				//http://www.ksvn.com/anhviet_new.htm - translating comments...
				//bent shrink essence move size measure automatic => divide against give as a whole
					//$wi = $table['w'] - $wis;
					$wi = ($table['w'] - $wis)/$numcols;
					for($k=0;$k<$numcols;$k++) 
						//$widthcols[$k]['miw'] += ($widthcols[$k]['miw']/$wis)*$wi;
						$widthcols[$k]['miw'] += $wi;
				}
				else
				{//Co mot so cot co kich thuoc auto => chia deu phan du cho cac cot auto
					//$wi = $table['w'] - $wis;
					$wi = ($table['w'] - $wis)/count($list);
					foreach ($list as $k)
						//$widthcols[$k]['miw'] += ($widthcols[$k]['miw']/$wisa)*$wi;
						$widthcols[$k]['miw'] += $wi;
				}
			}
			for ($i=0;$i<$numcols;$i++)
			{
				$tablewidth = $widthcols[$i]['miw'];
				unset($widthcols[$i]);
				$widthcols[$i] = $tablewidth;
			}
		}
		else //table has no width defined
		{
			$table['w'] = $tablewidth;
			for ( $i = 0 ; $i < $numcols ; $i++)
			{
				$tablewidth = isset($widthcols[$i]['w']) ? $widthcols[$i]['miw'] : $widthcols[$i]['maw'];
				unset($widthcols[$i]);
				$widthcols[$i] = $tablewidth;
			}
		}
	}
		
	function _tableHeight(&$table){
	//! @return void
	//! @desc Calculates the Table Height
		$cells = &$table['cells'];
		$numcols = $table['nc'];
		$numrows = $table['nr'];
		$listspan = array();
		for( $i = 0 ; $i < $numrows ; $i++ )//rows
		{
			$heightrow = &$table['hr'][$i];
			for( $j = 0 ; $j < $numcols ; $j++ ) //columns
			{
				if (isset($cells[$i][$j]) && $cells[$i][$j])
				{
					$c = &$cells[$i][$j];
					list($x,$cw) = $this->_tableGetWidth($table, $i,$j);
					//Check whether width is enough for this cells' text
					$auxtext = implode("",$c['text']);
					$auxtext2 = $auxtext; //in case we have text with styles
					$nostyles_size = $this->GetStringWidth($auxtext) + 3; // +3 == margin
					$linesneeded = $this->WordWrap($auxtext,$cw-2);// -2 == margin
					if ($c['s'] > $nostyles_size and !isset($c['form'])) //Text with styles
					{
						 $auxtext = $auxtext2; //recover original characteristics (original /n placements)
						 $diffsize = $c['s'] - $nostyles_size; //with bold et al. char width gets a bit bigger than plain char
						 if ($linesneeded == 0) $linesneeded = 1; //to avoid division by zero
						 $diffsize /= $linesneeded;
						 $linesneeded = $this->WordWrap($auxtext,$cw-2-$diffsize);//diffsize used to wrap text correctly
					}
					if (isset($c['form']))
					{
						 $linesneeded = ceil(($c['s']-3)/($cw-2)); //Text + form in a cell
						 //Presuming the use of styles
						 if ( ($this->GetStringWidth($auxtext) + 3) > ($cw-2) ) $linesneeded++;
					}
					$ch = $linesneeded * 1.1 * $this->lineheight;
					//If height is bigger than page height...
					if ($ch > ($this->fh - $this->bMargin - $this->tMargin)) $ch = ($this->fh - $this->bMargin - $this->tMargin);
					//If height is defined and it is bigger than calculated $ch then update values
					if (isset($c['h']) && $c['h'] > $ch)
					{
						 $c['mih'] = $ch; //in order to keep valign working
						 $ch = $c['h'];
					}
					else $c['mih'] = $ch;
					if (isset($c['rowspan']))	$listspan[] = array($i,$j);
					elseif ($heightrow < $ch) $heightrow = $ch;
					if (isset($c['form'])) $c['mih'] = $ch;
				}
			}//end of columns
		}//end of rows
		$heightrow = &$table['hr'];
		foreach ($listspan as $span)
		{
			list($i,$j) = $span;
			$c = &$cells[$i][$j];
			$lr = $i + $c['rowspan'];
			if ($lr > $numrows) $lr = $numrows;
			$hs = $hsa = 0;
			$list = array();
			for($k=$i;$k<$lr;$k++)
			{
				$hs += $heightrow[$k];
				if (!isset($c['h']))
				{
					$list[] = $k;
					$hsa += $heightrow[$k];
				}
			}
			if ($c['mih'] > $hs)
			{
				if (!$hs)
				{//Cac dong chua co kich thuoc => chia deu
					for($k=$i;$k<$lr;$k++) $heightrow[$k] = $c['mih']/$c['rowspan'];
				}
				elseif (!count($list))
				{//Khong co dong nao co kich thuoc auto => chia deu phan du cho tat ca
					$hi = $c['mih'] - $hs;
					for($k=$i;$k<$lr;$k++) $heightrow[$k] += ($heightrow[$k]/$hs)*$hi;
				}
				else
				{//Co mot so dong co kich thuoc auto => chia deu phan du cho cac dong auto
					$hi = $c['mih'] - $hsa;
					foreach ($list as $k) $heightrow[$k] += ($heightrow[$k]/$hsa)*$hi;
				}
			}
		}
	}

	function _tableGetWidth(&$table, $i,$j){
	//! @return array(x,w)
	// @desc Xac dinh toa do va do rong cua mot cell

		$cell = &$table['cells'][$i][$j];
		if ($cell)
		{
			if (isset($cell['x0'])) return array($cell['x0'], $cell['w0']);
			$x = 0;
			$widthcols = &$table['wc'];
			for( $k = 0 ; $k < $j ; $k++ ) $x += $widthcols[$k];
			$w = $widthcols[$j];
			if (isset($cell['colspan']))
			{
				 for ( $k = $j+$cell['colspan']-1 ; $k > $j ; $k-- )	$w += $widthcols[$k];
			}
			$cell['x0'] = $x;
			$cell['w0'] = $w;
			return array($x, $w);
		}
		return array(0,0);
	}

	function _tableGetHeight(&$table, $i,$j){
	//! @return array(y,h)
		$cell = &$table['cells'][$i][$j];
		if ($cell){
			if (isset($cell['y0'])) return array($cell['y0'], $cell['h0']);
			$y = 0;
			$heightrow = &$table['hr'];
			for ($k=0;$k<$i;$k++) $y += $heightrow[$k];
			$h = $heightrow[$i];
			if (isset($cell['rowspan'])){
				for ($k=$i+$cell['rowspan']-1;$k>$i;$k--)
					$h += $heightrow[$k];
			}
			$cell['y0'] = $y;
			$cell['h0'] = $h;
			return array($y, $h);
		}
		return array(0,0);
	}

	function _tableRect($x, $y, $w, $h, $type=1){
	//! @return void
		if ($type==1)	$this->Rect($x, $y, $w, $h);
		elseif (strlen($type)==4){
			$x2 = $x + $w; $y2 = $y + $h;
			if (intval($type{0})) $this->Line($x , $y , $x2, $y );
			if (intval($type{1})) $this->Line($x2, $y , $x2, $y2);
			if (intval($type{2})) $this->Line($x , $y2, $x2, $y2);
			if (intval($type{3})) $this->Line($x , $y , $x , $y2);
		}
	}

	function _tableWrite(&$table){
	//! @desc Main table function
	//! @return void
		$cells = &$table['cells'];
		$numcols = $table['nc'];
		$numrows = $table['nr'];
		$x0 = $this->x;
		$y0 = $this->y;
		$right = $this->pgwidth - $this->rMargin;
		if (isset($table['a']) and ($table['w'] != $this->pgwidth))
		{
			if ($table['a']=='C') $x0 += (($right-$x0) - $table['w'])/2;
			elseif ($table['a']=='R')	$x0 = $right - $table['w'];
		}
		$returny = 0;
		$tableheader = array();
		//Draw Table Contents and Borders
		for( $i = 0 ; $i < $numrows ; $i++ ) //Rows
		{ 
			$skippage = false;
			for( $j = 0 ; $j < $numcols ; $j++ ) //Columns
			{
					if (isset($cells[$i][$j]) && $cells[$i][$j])
					{
						$cell = &$cells[$i][$j];
						list($x,$w) = $this->_tableGetWidth($table, $i, $j);
						list($y,$h) = $this->_tableGetHeight($table, $i, $j);
						$x += $x0;
						$y += $y0;
						$y -= $returny;
						if ((($y + $h) > ($this->fh - $this->bMargin)) && ($y0 >0 || $x0 > 0))
						{
							if (!$skippage)
							{
								 $y -= $y0;
								 $returny += $y;
								 $this->AddPage();
								 if ($this->usetableheader) $this->Header($tableheader);
								 if ($this->usetableheader) $y0 = $this->y;
								 else $y0 = $this->tMargin;
								 $y = $y0;
							}
							$skippage = true;
						}
						//Align
						$this->x = $x; $this->y = $y;
						$align = isset($cell['a'])? $cell['a'] : 'L';
						//Vertical align
						if (!isset($cell['va']) || $cell['va']=='M') $this->y += ($h-$cell['mih'])/2;
						elseif (isset($cell['va']) && $cell['va']=='B') $this->y += $h-$cell['mih'];
						//Fill
						$fill = isset($cell['bgcolor']) ? $cell['bgcolor']
							: (isset($table['bgcolor'][$i]) ? $table['bgcolor'][$i]
							: (isset($table['bgcolor'][-1]) ? $table['bgcolor'][-1] : 0));
						if ($fill)
						{
							$color = ConvertColor($fill);
							$this->SetFillColor($color['R'],$color['G'],$color['B']);
							$this->Rect($x, $y, $w, $h, 'F');
						}
						//Border
						if (isset($cell['border'])) $this->_tableRect($x, $y, $w, $h, $cell['border']);
						elseif (isset($table['border']) && $table['border']) $this->Rect($x, $y, $w, $h);
						$this->divalign=$align;
						$this->divwidth=$w-2;
						//Get info of first row == table header
						if ($this->usetableheader and $i == 0 )
						{
								$tableheader[$j]['x'] = $x;
								$tableheader[$j]['y'] = $y;
								$tableheader[$j]['h'] = $h;
								$tableheader[$j]['w'] = $w;
								$tableheader[$j]['text'] = $cell['text'];
								$tableheader[$j]['textbuffer'] = $cell['textbuffer'];
								$tableheader[$j]['a'] = isset($cell['a'])? $cell['a'] : 'L';
								$tableheader[$j]['va'] = $cell['va'];
								$tableheader[$j]['mih'] = $cell['mih'];
								$tableheader[$j]['bgcolor'] = $fill;
								if ($table['border']) $tableheader[$j]['border'] = 'all';
								elseif (isset($cell['border'])) $tableheader[$j]['border'] = $cell['border'];
						}
						if (!empty($cell['textbuffer'])) $this->printbuffer($cell['textbuffer'],false,true/*inside a table*/);
						//Reset values
						$this->Reset();
					}//end of (if isset(cells)...)
			}// end of columns
			if ($i == $numrows-1) $this->y = $y + $h; //last row jump (update this->y position)
		}// end of rows
	}//END OF FUNCTION _tableWrite()

/////////////////////////END OF TABLE CODE//////////////////////////////////

}//end of Class

?>
