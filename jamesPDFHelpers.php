<?php


defined('_JEXEC') || define('_JEXEC', 1);
defined('JPATH_BASE') || define( 'JPATH_BASE', realpath(dirname(__FILE__) . "../../../..")); 
defined('DS') || define( 'DS', DIRECTORY_SEPARATOR );
$library_path = JPATH_BASE . DS . 'libraries';
$component_path = JPATH_BASE . DS . 'components' . DS . 'com_inventorymgmt';
$model_path = $component_path . DS . 'models';
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' ); 
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' ); 
    $mainframe = JFactory::getApplication('site'); 
    $mainframe->initialise();

require_once ($model_path . "/master.php");
require_once( $library_path .  '/fpdf/fpdf.php');

    



abstract class jamesPDFHelpers extends FPDF {

    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        parent::__construct($orientation, $unit, $size);
        //Add some different fonts
        $this->AddFont('pepsi', '', 'pepsi.php');
        $this->AddFont('sports-world', '', 'Sports World-Regular.php');

        //Initialization of WriteHTML()
        $this->B=0;
        $this->I=0;
        $this->U=0;
        $this->HREF='';
        $this->issetfont=false;
        $this->issetcolor=false;
        $this->writeHTML_ALIGN = '';
        $this->writeHTML_FontList = array('times', 'arial', 'courier', 'pepsi', 'sports-world');
    }


    public function setDWCImage($width, $height, $x = null, $y = null, $location = null) {
        $images_path = JPATH_BASE . DS . 'images';
        if(!$location) {
            $model = new InventorymgmtModelMaster();
            $whs = $model->getUserLocation();
        } else {
            $whs = $location;
        }
        switch ($whs) {
            case "CHA" :
            //Chatt Image
            $this->Image($images_path . DS . 'chattanooga.png', $x, $y, $width, $height);
            case "NSH" :
            $this->Image($images_path . DS . 'nashville.png', $x, $y, $width, $height);
            default :
            //DWC Image
            $this->Image($images_path . DS . 'powell.png', $x, $y, $width, $height);

        } 
    }
    /**
     * This will take a string and break it apart. Then it will put it back together
     * and measure the width of the string. If it is past the given width it will
     * add it to a new array.
     * @param string $txt to be parsed 
     * @param float $maxwidth width of the cell
     * @param array $breakOn Array of items to break the lines on
     * @since 3.7.3
     * @return array of each line
     */
    public function WordWrap($txt, $maxwidth, $breakOn = array('<br>', '<br/>', '<br />', "\\r", "\\n")){
        if (trim($txt)==='')
            return 0;
        $txt = str_replace($breakOn, "\n", $txt);

        $lines = explode("\n", $txt);

        $rt = array();

        foreach ($lines as $line)
        {
            $words = explode(' ', $line);
            $text = '';

            foreach ($words as $word)
            {

                //If a really long word then cut the word as max length
                if($this->GetStringWidth($word) > $maxwidth) {
                    $temp = str_split($word);
                    $word = "";
                    for($i = 0; $i < count($temp); $i++) {
                        if($this->GetStringWidth($word . $temp[$i]) >= $maxwidth) {
                            break;
                        }
                        $word = $word . $temp[$i];
                    }
                }
                $wordwidth = $this->GetStringWidth($text . $word);
                if ($wordwidth >= $maxwidth) {
                    $rt[] = rtrim($text);
                    $text = '';
                }

                $text .= $word . " ";


            }
            $rt[] = rtrim($text);
        }

        return $rt;
    }

    /**
     * Will take the $w = width value and word wrap the text on each line for specified lines
     * @param float $w width of the cell, also the max width the words can go before being broken
     * @param float $h height of the cell
     * @param string $txt Text to write to the PDF
     * @param int $ln The new line height
     * @param int $maxLines Max number of lines to write
     * @param string $ellipses what to append to the last line to show there is more
     * @param int $border Place a border around the cell
     * @param string $align Align the Text in the cell
     * @param bool $fill Fill the cell with color
     * @param string $link Add a link to this Cell
     * @return string Left over String if any
     * @since 3.7.3
     */
    public function WordWrapCell($w, $h, $txt, $ln = 0, $maxLines = 999, $ellipses = '...', $border = 0, $align = '', $fill = false, $link = '') {
        $text = $this->WordWrap($txt, $w);
        $max = (count($text) < $maxLines) ? count($text) : $maxLines;
        for ($i = 0; $i < $max; $i++) {
            $line = $text[$i];
            if($i == $max && count($text) > $max)
                $line .= $ellipses;
            $this->Cell($w, $h, $line, $border, 0, $align, $fill, $link);
            $this->Ln($ln);
        }
        $returnedText = '';
        if($i < count($text)) {
            for($j = $i; $j < count($text); $j++) {
                $returnedText .= $text[$j] . ' ';
            }
            $returnedText = trim($returnedText);
        }
        return $returnedText;

    }

    public function WordWrapStrippedCell($w, $h, $txt, $fill = false, $ln = 0, $maxLines = 999, $ellipses = '...', $border = 0, $align = 'L', $link = '', $setX = null) {
        $text = $this->WordWrap($txt, $w);
        $max = (count($text) < $maxLines) ? count($text) : $maxLines;
        for ($i = 0; $i < $max; $i++) {
            $line = $text[$i];
            if($i == $max && count($text) > $max)
                $line .= $ellipses;
            if($setX && $i > 0)
                $this->StrippedCell(($this->GetX() + $setX), $h, '', $border, 0, $fill, 225, $align, $link);
            $this->StrippedCell($w, $h, $line, $border, 0, $fill, 225, $align, $link);
            $this->Ln($ln);
        }
        $returnedText = '';
        if($i < count($text)) {
            for($j = $i; $j < count($text); $j++) {
                $returnedText .= $text[$j] . ' ';
            }
            $returnedText = trim($returnedText);
        }
        return $returnedText;
    }


    /**
     * Will add a watermark to the page, must be in the Header
     * @param float $x
     * @param float $y
     * @param string $txt
     * @param float $angle
     * @param int $fontSize
     * @param mixed $color
     * @since 3.7.3
     */
    public function AddWatermark($x, $y, $txt, $angle = 0, $color = 200, $fontSize = 50) {
        $pcolor = $this->TextColor; //Get previous Color
        $this->SetFont('Arial', 'B', $fontSize);
        $this->SetTextColor($color);
        $this->RotatedText($x, $y, $txt, $angle);
        $this->SetTextColor($pcolor);
    }
    
    /**
     * Will rotate the text on a given angle
     * @param float $x
     * @param float $y
     * @param string $txt
     * @param float $angle
     * @since 3.7.3
     */
    public function RotatedText($x, $y, $txt, $angle) {
        //Text rotated around its origin
        $this->Rotate($angle,$x,$y);
        $this->Text($x,$y,$txt);
        $this->Rotate(0);
    }
    
    /**
     * This is an extension to Cell. Takes all arguments as Cell but allows you 
     * to change the font size, style, and family for this Cell only, will convert back to original
     * @param float $width
     * @param float $height
     * @param string $text
     * @param mixed $border
     * @param int $lineReturn
     * @param string $alignment
     * @param float $fontSize
     * @param string $fontStyle
     * @param string $fontFamily
     * @since 3.7.3
     */
    public function ChangeFontSizeCell($width, $height, $text, $border, $lineReturn, $alignment, $fontSize, $fontStyle = null, $fontFamily = null ) {
        $pFamily = $this->FontFamily;
        $fontFamily = (!$fontFamily) ?  $this->FontFamily : $fontFamily;
        $pStyle = $this->FontStyle;
        $fontStyle = (!$fontStyle) ?  $this->FontStyle : $fontStyle;
        $pSize = $this->GetFontSize();
        $this->SetFont($fontFamily, $fontStyle, $fontSize);
        $this->Cell($width, $height, $text, $border, $lineReturn, $alignment);
        $this->SetFont($pFamily, $pStyle, $pSize);
    }
    
     /**
     * This is an extension of the Cell, Takes all same arguments as Cell except it will decrease the font size
     * until the text will fit inside of the cell
     * @param double $w width of cell 
     * @param double $h height of cell
     * @param string $txt text to print
     * @param int $b border
     * @param double $ln line break
     * @param String $align Text alignment
     * @param boolean $fill Cell Fill
     * @param String $link Add Link
      * @since 3.7.3
     */
    public function FitCell($w, $h, $txt, $b = 0, $ln = 0, $align = 'L', $fill = false, $link = null) {
        $startFontSize = $this->FontSizePt;
        while($this->GetStringWidth($txt) > ($w - 0.1)) {
            $this->SetFontSize($this->FontSizePt - 0.01); 
        }
        $this->Cell($w, $h, $txt, $b, $ln, $align, $fill, $link);
        $this->SetFontSize($startFontSize);
    }
    
    /**
     * Extension of the Cell Function that will fill the cell with a color
     * @param float $w Width
     * @param float $h Height
     * @param String $txt Text to print
     * @param mixed $b Border
     * @param int $ln line break
     * @param boolean $fill Fill the cell with color
     * @param int $fillColor Value 0-255 for the background
     * @param String $align Alignment of the Text
     * @param mixed $link Link in Document
     * @since 3.7.3
     */
    public function StrippedCell($w, $h, $txt, $b = 0, $ln = 0, $fill = false, $fillColor = 225, $align = 'L', $link = null) {
        if(!$h || $h == 0) {
            $h = 0.015 * $this->GetFontSize();
        }
        $this->SetFillColor($fillColor);
        $this->Cell($w, $h, $txt, $b, $ln, $align, $fill, $link);
    }
    
    /**
     * Extension of the FitCell Function that will fill the cell with a color
     * @param float $w Width
     * @param float $h Height
     * @param String $txt Text to print
     * @param mixed $b Border
     * @param int $ln line break
     * @param boolean $fill Fill the cell with color
     * @param int $fillColor Value 0-255 for the background
     * @param String $align Alignment of the Text
     * @param mixed $link Link in Document
     * @since 3.7.3
     */
    public function StrippedFitCell($w, $h, $txt, $b = 0, $ln = 0, $fill = false, $fillColor = 225, $align = 'L', $link = null) {
        $this->SetFillColor($fillColor);
        $this->FitCell($w, $h, $txt, $b, $ln, $align, $fill, $link);
    }

    /**
     * This will add the Fax Number, on error send email, and on success send email to the document at location 0.5, 0.5 of the document based on defined measurement
     * Only need to use this if you will be using the sendFax function, otherwise this function is of no use for sendFax2 function
     * @param string $faxnumber to fax to
     * @param string $errorReturnEmail - If error send error report to this email
     * @param string $successReturnEmail - On Successful Fax send a confirmation email
     * @since 3.7.3
     */
    public function SetFaxTo($faxnumber, $errorReturnEmail = null, $successReturnEmail = null) {
        $user = JFactory::getUser();
        $this->SetAuthor($user->name);
        $prevFontSize = $this->GetFontSize();
        $onError = "";
        if(!$errorReturnEmail) {
            $onError = $user->email;
           // $this->debug("On Fax Error send email to " . $onError);
        } else {
            $onError = $errorReturnEmail;
        }
        $this->SetFontSize(0.1);

        //Logic for the phone number
	    if(strlen($faxnumber) == 7) {
	    	//Local call, affix the 865 on the front
		    $faxnumber = "865" . $faxnumber;
	    } else if(strlen($faxnumber) == 10) {
	    	//break apart the phone number
		    $temp = substr($faxnumber, 0, 3);
		    //If the area code is NOT 865 then put a 1 at the front otherwise it is a local call so don't do anything
		    if($temp <> "865")
		    {
			    $faxnumber = "1" . $faxnumber;
		    }
	    } else if(strlen($faxnumber) == 11) {
	    	//nothing to do, phone number is correct length
	    }
        $this->Text(0.5, 1, "Fax-Nr:" . $faxnumber);
        $this->Text(0.5, 2, "Fax-Error:" . $onError . ":Fax-Error");
        if($successReturnEmail) {
            $this->Text(0.5, 3, "Fax-Success:" . $successReturnEmail . ":Fax-Success");
        }
        $this->Text(0.5, 4, "Fax-Owner:" . $user->name . ":Fax-Owner");
        $this->SetFontSize($prevFontSize);
    }

    /**
     * Make the Text following Bold
     * @param bool $boolean Make Bold Or Not
     * @since 3.7.3
     */
    public function SetBold($boolean = true) {
        if($boolean) {
            $this->SetFont($this->GetFontFamily(), 'B');
        } else {
            $this->SetFont($this->GetFontFamily(), '');
        }
    }
    
    
    /**
     * These are variables used to generate the Code128 Barcode
     * @since 3.7.3
     */
    private $T128, $ABCset, $Aset, $Bset, $Cset, $SetFrom, $SetTo, $JStart, $JSwap;
    /**
     * This will produce a Barcode 128 format at given x:y
     * @param float $x
     * @param float $y
     * @param string $code
     * @param float $w
     * @param float $h
     * @since 3.7.3
     */ 
    public function Code128($x, $y, $code, $w, $h, $printText = false) {
        $txt = $code;                                       
        $this->JStart = array("A"=>103, "B"=>104, "C"=>105); 
        $this->JSwap = array("A"=>101, "B"=>100, "C"=>99);
        
        $this->T128[] = array(2, 1, 2, 2, 2, 2);           //0 : [ ]               
        $this->T128[] = array(2, 2, 2, 1, 2, 2);           //1 : [!]
        $this->T128[] = array(2, 2, 2, 2, 2, 1);           //2 : ["]
        $this->T128[] = array(1, 2, 1, 2, 2, 3);           //3 : [#]
        $this->T128[] = array(1, 2, 1, 3, 2, 2);           //4 : [$]
        $this->T128[] = array(1, 3, 1, 2, 2, 2);           //5 : [%]
        $this->T128[] = array(1, 2, 2, 2, 1, 3);           //6 : [&]
        $this->T128[] = array(1, 2, 2, 3, 1, 2);           //7 : [']
        $this->T128[] = array(1, 3, 2, 2, 1, 2);           //8 : [(]
        $this->T128[] = array(2, 2, 1, 2, 1, 3);           //9 : [)]
        $this->T128[] = array(2, 2, 1, 3, 1, 2);           //10 : [*]
        $this->T128[] = array(2, 3, 1, 2, 1, 2);           //11 : [+]
        $this->T128[] = array(1, 1, 2, 2, 3, 2);           //12 : [,]
        $this->T128[] = array(1, 2, 2, 1, 3, 2);           //13 : [-]
        $this->T128[] = array(1, 2, 2, 2, 3, 1);           //14 : [.]
        $this->T128[] = array(1, 1, 3, 2, 2, 2);           //15 : [/]
        $this->T128[] = array(1, 2, 3, 1, 2, 2);           //16 : [0]
        $this->T128[] = array(1, 2, 3, 2, 2, 1);           //17 : [1]
        $this->T128[] = array(2, 2, 3, 2, 1, 1);           //18 : [2]
        $this->T128[] = array(2, 2, 1, 1, 3, 2);           //19 : [3]
        $this->T128[] = array(2, 2, 1, 2, 3, 1);           //20 : [4]
        $this->T128[] = array(2, 1, 3, 2, 1, 2);           //21 : [5]
        $this->T128[] = array(2, 2, 3, 1, 1, 2);           //22 : [6]
        $this->T128[] = array(3, 1, 2, 1, 3, 1);           //23 : [7]
        $this->T128[] = array(3, 1, 1, 2, 2, 2);           //24 : [8]
        $this->T128[] = array(3, 2, 1, 1, 2, 2);           //25 : [9]
        $this->T128[] = array(3, 2, 1, 2, 2, 1);           //26 : [:]
        $this->T128[] = array(3, 1, 2, 2, 1, 2);           //27 : [;]
        $this->T128[] = array(3, 2, 2, 1, 1, 2);           //28 : [<]
        $this->T128[] = array(3, 2, 2, 2, 1, 1);           //29 : [=]
        $this->T128[] = array(2, 1, 2, 1, 2, 3);           //30 : [>]
        $this->T128[] = array(2, 1, 2, 3, 2, 1);           //31 : [?]
        $this->T128[] = array(2, 3, 2, 1, 2, 1);           //32 : [@]
        $this->T128[] = array(1, 1, 1, 3, 2, 3);           //33 : [A]
        $this->T128[] = array(1, 3, 1, 1, 2, 3);           //34 : [B]
        $this->T128[] = array(1, 3, 1, 3, 2, 1);           //35 : [C]
        $this->T128[] = array(1, 1, 2, 3, 1, 3);           //36 : [D]
        $this->T128[] = array(1, 3, 2, 1, 1, 3);           //37 : [E]
        $this->T128[] = array(1, 3, 2, 3, 1, 1);           //38 : [F]
        $this->T128[] = array(2, 1, 1, 3, 1, 3);           //39 : [G]
        $this->T128[] = array(2, 3, 1, 1, 1, 3);           //40 : [H]
        $this->T128[] = array(2, 3, 1, 3, 1, 1);           //41 : [I]
        $this->T128[] = array(1, 1, 2, 1, 3, 3);           //42 : [J]
        $this->T128[] = array(1, 1, 2, 3, 3, 1);           //43 : [K]
        $this->T128[] = array(1, 3, 2, 1, 3, 1);           //44 : [L]
        $this->T128[] = array(1, 1, 3, 1, 2, 3);           //45 : [M]
        $this->T128[] = array(1, 1, 3, 3, 2, 1);           //46 : [N]
        $this->T128[] = array(1, 3, 3, 1, 2, 1);           //47 : [O]
        $this->T128[] = array(3, 1, 3, 1, 2, 1);           //48 : [P]
        $this->T128[] = array(2, 1, 1, 3, 3, 1);           //49 : [Q]
        $this->T128[] = array(2, 3, 1, 1, 3, 1);           //50 : [R]
        $this->T128[] = array(2, 1, 3, 1, 1, 3);           //51 : [S]
        $this->T128[] = array(2, 1, 3, 3, 1, 1);           //52 : [T]
        $this->T128[] = array(2, 1, 3, 1, 3, 1);           //53 : [U]
        $this->T128[] = array(3, 1, 1, 1, 2, 3);           //54 : [V]
        $this->T128[] = array(3, 1, 1, 3, 2, 1);           //55 : [W]
        $this->T128[] = array(3, 3, 1, 1, 2, 1);           //56 : [X]
        $this->T128[] = array(3, 1, 2, 1, 1, 3);           //57 : [Y]
        $this->T128[] = array(3, 1, 2, 3, 1, 1);           //58 : [Z]
        $this->T128[] = array(3, 3, 2, 1, 1, 1);           //59 : [[]
        $this->T128[] = array(3, 1, 4, 1, 1, 1);           //60 : [\]
        $this->T128[] = array(2, 2, 1, 4, 1, 1);           //61 : []]
        $this->T128[] = array(4, 3, 1, 1, 1, 1);           //62 : [^]
        $this->T128[] = array(1, 1, 1, 2, 2, 4);           //63 : [_]
        $this->T128[] = array(1, 1, 1, 4, 2, 2);           //64 : [`]
        $this->T128[] = array(1, 2, 1, 1, 2, 4);           //65 : [a]
        $this->T128[] = array(1, 2, 1, 4, 2, 1);           //66 : [b]
        $this->T128[] = array(1, 4, 1, 1, 2, 2);           //67 : [c]
        $this->T128[] = array(1, 4, 1, 2, 2, 1);           //68 : [d]
        $this->T128[] = array(1, 1, 2, 2, 1, 4);           //69 : [e]
        $this->T128[] = array(1, 1, 2, 4, 1, 2);           //70 : [f]
        $this->T128[] = array(1, 2, 2, 1, 1, 4);           //71 : [g]
        $this->T128[] = array(1, 2, 2, 4, 1, 1);           //72 : [h]
        $this->T128[] = array(1, 4, 2, 1, 1, 2);           //73 : [i]
        $this->T128[] = array(1, 4, 2, 2, 1, 1);           //74 : [j]
        $this->T128[] = array(2, 4, 1, 2, 1, 1);           //75 : [k]
        $this->T128[] = array(2, 2, 1, 1, 1, 4);           //76 : [l]
        $this->T128[] = array(4, 1, 3, 1, 1, 1);           //77 : [m]
        $this->T128[] = array(2, 4, 1, 1, 1, 2);           //78 : [n]
        $this->T128[] = array(1, 3, 4, 1, 1, 1);           //79 : [o]
        $this->T128[] = array(1, 1, 1, 2, 4, 2);           //80 : [p]
        $this->T128[] = array(1, 2, 1, 1, 4, 2);           //81 : [q]
        $this->T128[] = array(1, 2, 1, 2, 4, 1);           //82 : [r]
        $this->T128[] = array(1, 1, 4, 2, 1, 2);           //83 : [s]
        $this->T128[] = array(1, 2, 4, 1, 1, 2);           //84 : [t]
        $this->T128[] = array(1, 2, 4, 2, 1, 1);           //85 : [u]
        $this->T128[] = array(4, 1, 1, 2, 1, 2);           //86 : [v]
        $this->T128[] = array(4, 2, 1, 1, 1, 2);           //87 : [w]
        $this->T128[] = array(4, 2, 1, 2, 1, 1);           //88 : [x]
        $this->T128[] = array(2, 1, 2, 1, 4, 1);           //89 : [y]
        $this->T128[] = array(2, 1, 4, 1, 2, 1);           //90 : [z]
        $this->T128[] = array(4, 1, 2, 1, 2, 1);           //91 : [{]
        $this->T128[] = array(1, 1, 1, 1, 4, 3);           //92 : [|]
        $this->T128[] = array(1, 1, 1, 3, 4, 1);           //93 : [}]
        $this->T128[] = array(1, 3, 1, 1, 4, 1);           //94 : [~]
        $this->T128[] = array(1, 1, 4, 1, 1, 3);           //95 : [DEL]
        $this->T128[] = array(1, 1, 4, 3, 1, 1);           //96 : [FNC3]
        $this->T128[] = array(4, 1, 1, 1, 1, 3);           //97 : [FNC2]
        $this->T128[] = array(4, 1, 1, 3, 1, 1);           //98 : [SHIFT]
        $this->T128[] = array(1, 1, 3, 1, 4, 1);           //99 : [Cswap]
        $this->T128[] = array(1, 1, 4, 1, 3, 1);           //100 : [Bswap]                
        $this->T128[] = array(3, 1, 1, 1, 4, 1);           //101 : [Aswap]
        $this->T128[] = array(4, 1, 1, 1, 3, 1);           //102 : [FNC1]
        $this->T128[] = array(2, 1, 1, 4, 1, 2);           //103 : [Astart]
        $this->T128[] = array(2, 1, 1, 2, 1, 4);           //104 : [Bstart]
        $this->T128[] = array(2, 1, 1, 2, 3, 2);           //105 : [Cstart]
        $this->T128[] = array(2, 3, 3, 1, 1, 1);           //106 : [STOP]
        $this->T128[] = array(2, 1);                       //107 : [END BAR]

        for ($i = 32; $i <= 95; $i++) {                                            
                $this->ABCset .= chr($i);
        }
        $this->Aset = $this->ABCset;
        $this->Bset = $this->ABCset;

        for ($i = 0; $i <= 31; $i++) {
                $this->ABCset .= chr($i);
                $this->Aset .= chr($i);
        }
        for ($i = 96; $i <= 127; $i++) {
                $this->ABCset .= chr($i);
                $this->Bset .= chr($i);
        }
        for ($i = 200; $i <= 210; $i++) {                                           
                $this->ABCset .= chr($i);
                $this->Aset .= chr($i);
                $this->Bset .= chr($i);
        }
        $this->Cset="0123456789".chr(206);

        for ($i=0; $i<96; $i++) {                                                   
                @$this->SetFrom["A"] .= chr($i);
                @$this->SetFrom["B"] .= chr($i + 32);
                @$this->SetTo["A"] .= chr(($i < 32) ? $i+64 : $i-32);
                @$this->SetTo["B"] .= chr($i);
        }
        for ($i=96; $i<107; $i++) {                                                 
                @$this->SetFrom["A"] .= chr($i + 104);
                @$this->SetFrom["B"] .= chr($i + 104);
                @$this->SetTo["A"] .= chr($i);
                @$this->SetTo["B"] .= chr($i);
        }
            
        $Aguid = "";                                                                      
        $Bguid = "";
        $Cguid = "";
        for ($i=0; $i < strlen($code); $i++) {
                $needle = substr($code,$i,1);
                $Aguid .= ((strpos($this->Aset,$needle)===false) ? "N" : "O"); 
                $Bguid .= ((strpos($this->Bset,$needle)===false) ? "N" : "O"); 
                $Cguid .= ((strpos($this->Cset,$needle)===false) ? "N" : "O");
        }

        $SminiC = "OOOO";
        $IminiC = 4;

        $crypt = "";
        while ($code > "") {

            $i = strpos($Cguid,$SminiC);                                                
            if ($i!==false) {
                    $Aguid [$i] = "N";
                    $Bguid [$i] = "N";
            }

            if (substr($Cguid,0,$IminiC) == $SminiC) {                                  
                    $crypt .= chr(($crypt > "") ? $this->JSwap["C"] : $this->JStart["C"]);  
                    $made = strpos($Cguid,"N");                                             
                    if ($made === false) {
                            $made = strlen($Cguid);
                    }
                    if (fmod($made,2)==1) {
                            $made--;                                                            
                    }
                    for ($i=0; $i < $made; $i += 2) {
                            $crypt .= chr(strval(substr($code,$i,2)));                          
                    }
                    $jeu = "C";
            } else {
                    $madeA = strpos($Aguid,"N");                                            
                    if ($madeA === false) {
                            $madeA = strlen($Aguid);
                    }
                    $madeB = strpos($Bguid,"N");                                            
                    if ($madeB === false) {
                            $madeB = strlen($Bguid);
                    }
                    $made = (($madeA < $madeB) ? $madeB : $madeA );                         
                    $jeu = (($madeA < $madeB) ? "B" : "A" );                                

                    $crypt .= chr(($crypt > "") ? $this->JSwap[$jeu] : $this->JStart[$jeu]); 

                    $crypt .= strtr(substr($code, 0,$made), $this->SetFrom[$jeu], $this->SetTo[$jeu]); 

            }
            $code = substr($code,$made);                                           
            $Aguid = substr($Aguid,$made);
            $Bguid = substr($Bguid,$made);
            $Cguid = substr($Cguid,$made);
        }                                                                          

        $check = ord($crypt[0]);                                                   
        for ($i=0; $i<strlen($crypt); $i++) {
                $check += (ord($crypt[$i]) * $i);
        }
        $check %= 103;

        $crypt .= chr($check) . chr(106) . chr(107);                               

        $i = (strlen($crypt) * 11) - 8;                                            
        $modul = $w/$i;

        for ($i=0; $i<strlen($crypt); $i++) {                                      
                $c = $this->T128[ord($crypt[$i])];
                for ($j=0; $j<count($c); $j++) {
                        $this->Rect($x,$y,$c[$j]*$modul,$h,"F");
                        $x += ($c[$j++]+$c[$j])*$modul;
                }
        }
        
        if($printText) {
            $curFontSize = $this->GetFontSize();
            $this->SetFontSize(($w * $h)*15);
            $curX = $this->GetX();
            $curY = $this->GetY();
            $this->SetXY($x - $w, $y + ($h * 0.85));
            $this->Cell($w, $h/2, $txt, 0, 0, 'C');
            $this->SetFontSize($curFontSize);
            $this->SetXY($curX, $curY);            
        }
    }
    
    /**
     * Will return the current Font Size
     * @since 3.7.3
     * @return double 
     */
    public function GetFontSize() {
        return $this->FontSizePt;
    }
    
    /**
     * Will return the current Font Family
     * @since 3.7.3
     * @return string
     */
    public function GetFontFamily() {
        return $this->FontFamily;
    }
    
    /**
     * Will return the current Font Style (Bold, Italic, Underline, None)
     * @since 3.7.3
     * @return string
     */
    public function GetFontStyle() {
        return $this->FontStyle;
    }
    
    /**
     * This will return the size of the page
     * @since 3.7.3
     * @return mixed page size
     */
    public function GetPageSize() {
        return $this->CurPageSize;
    }

    public function GetPageWidth() {
        return $this->w;
    }
    
    /**
     * Will return the current page rotation (Port, Landscape)
     * @since 3.7.3
     * @return string page rotation
     */
    public function GetPageRotation() {
        return $this->CurOrientation;
    }

    
    protected $angle;
    private function Rotate($angle,$x=-1,$y=-1) {
        if($x==-1)
            $x=$this->x;
        if($y==-1)
            $y=$this->y;
        if($this->angle!=0)
            $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0)
        {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }

    function Ln($h = null)
    {
        if($h == null)
            $h = 0.015 * $this->GetFontSize();
        parent::Ln($h); // TODO: Change the autogenerated stub
    }


    /**
     * WriteHTML Will take a HTML String and write it as PDF
     * @param string $html The HTML String to be parsed
     * @param float $ln The new line \n
     * @param int $h Cell Height
     * @author Clément Lavoillotte <http://www.fpdf.org/en/script/script42.php>
     * @since 3.7.3
     */
    public function WriteHTML($html,$ln = 0, $h = 0 )
    {
        $this->writeHTML_Ln = $ln;
        if(!$h || $h ==0)
            $h = $ln;
        //HTML parser
        $html=strip_tags($html,"<b><u><i><a><img><p><br><strong><em><font><tr><blockquote>"); //supprime tous les tags sauf ceux reconnus
        $html=str_replace("\n",'<br>',$html); //remplace retour à la ligne par un espace
        $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE); //éclate la chaîne avec les balises
        foreach($a as $i=>$e)
        {
            if($i%2==0)
            {
                $pageWidth = $this->GetPageWidth() - ($this->lMargin + $this->rMargin + 0.05);
                $text = $this->WordWrap(stripslashes($this->_txtentities($e)), $pageWidth, array("\\r", "\\n"));
                //Text
                if($this->HREF)
                    $this->_PutLink($this->HREF,$e);
                elseif(!empty($this->writeHTML_ALIGN)) {
                    if($this->writeHTML_ALIGN === "center") {
                        for ($i = 0; $i < count($text); $i++) {
                            $t = $text[$i];
                            if($i > 0)
                                $this->Ln();
                            $stringWidth = $this->GetStringWidth($t);
                            $x = ($pageWidth - $stringWidth) / 2;
                            $this->SetX($x);
                            $this->Write($h, stripslashes($this->_txtentities($t)));
                        }
                    }
                    $this->Ln(0.15);
                }
                else {
                    for ($i = 0; $i < count($text); $i++) {
                        $t = $text[$i];
                        if ($i > 0)
                            $this->Ln();
                        $this->Write($h, stripslashes($this->_txtentities($t)));
                    }


                }
            }
            else
            {
                //Tag
                if($e[0]=='/')
                    $this->_CloseTag(strtoupper(substr($e,1)));
                else
                {
                    //Extract attributes
                    $a2=explode(' ',$e);
                    $tag=strtoupper(array_shift($a2));
                    $attr=array();
                    foreach($a2 as $v)
                    {
                        if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
                            $attr[strtoupper($a3[1])]=$a3[2];
                    }
                    $this->_OpenTag($tag,$attr);
                }
            }
        }
    }
    //variables of html parser
    private $B;
    private $I;
    private $U;
    private $writeHTML_Ln, $writeHTML_ALIGN, $writeHTML_PrevFontSize, $writeHTML_FontList;
    private $HREF;
    private $fontList;
    private $issetfont;
    private $issetcolor;
    private function _OpenTag($tag, $attr)
    {
        //Opening tag
        switch($tag){
            case 'STRONG':
                $this->_SetStyle('B',true);
                break;
            case 'EM':
                $this->_SetStyle('I',true);
                break;
            case 'B':
            case 'I':
            case 'U':
                $this->_SetStyle($tag,true);
                break;
            case 'A':
                $this->HREF=$attr['HREF'];
                break;
            case 'IMG':
                if(isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT']))) {
                    if(!isset($attr['WIDTH']))
                        $attr['WIDTH'] = 0;
                    if(!isset($attr['HEIGHT']))
                        $attr['HEIGHT'] = 0;
                    $this->Image($attr['SRC'], $this->GetX(), $this->GetY(), $this->_px2mm($attr['WIDTH']), $this->_px2mm($attr['HEIGHT']));
                }
                break;
            case 'TR':
            case 'BLOCKQUOTE':
            case 'BR':
                if(isset($attr['LN']))
                    $this->Ln($attr['LN']);
                else
                    $this->Ln($this->writeHTML_Ln);
                break;
            case 'P':
                $this->Ln($this->writeHTML_Ln*2);
                $this->writeHTML_ALIGN=$attr['ALIGN'];
                break;
            case 'FONT':
                if (isset($attr['COLOR']) && $attr['COLOR']!='') {
                    $coul=$this->_hex2dec($attr['COLOR']);
                    $this->SetTextColor($coul['R'],$coul['V'],$coul['B']);
                    $this->issetcolor=true;
                }
                if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->writeHTML_FontList)) {
                    $this->SetFont(strtolower($attr['FACE']));
                    $this->issetfont=true;
                }
                if(isset($attr['SIZE']) && $attr['SIZE'] != '') {
                    $this->writeHTML_PrevFontSize = $this->GetFontSize();
                    $this->SetFontSize($attr['SIZE']);
                }
                break;
        }
    }
    private function _CloseTag($tag)
    {
        //Closing tag
        if($tag=='STRONG')
            $tag='B';
        if($tag=='EM')
            $tag='I';
        if($tag=='B' || $tag=='I' || $tag=='U')
            $this->_SetStyle($tag,false);
        if($tag=='A')
            $this->HREF='';
        if($tag=='FONT'){
            if ($this->issetcolor==true) {
                $this->SetTextColor(0);
            }
            if ($this->issetfont) {
                $this->SetFont('arial');
                $this->issetfont=false;
            }
            if($this->writeHTML_PrevFontSize) {
                $this->SetFontSize($this->writeHTML_PrevFontSize);
                $this->writeHTML_PrevFontSize = '';
            }
        }
        if($tag=='P') {
            $this->writeHTML_ALIGN = '';
        }
    }
    private function _SetStyle($tag, $enable)
    {
        //Modify style and select corresponding font
        $this->$tag+=($enable ? 1 : -1);
        $style='';
        foreach(array('B','I','U') as $s)
        {
            if($this->$s>0)
                $style.=$s;
        }
        $this->SetFont('',$style);
    }
    private function _PutLink($URL, $txt)
    {
        //Put a hyperlink
        $this->SetTextColor(0,0,255);
        $this->_SetStyle('U',true);
        $this->Write(5,$txt,$URL);
        $this->_SetStyle('U',false);
        $this->SetTextColor(0);
    }
    //function _hex2dec
    //returns an associative array (keys: R,G,B) from
    //a hex html code (e.g. #3FE5AA)
    private function _hex2dec($couleur = "#000000"){
        $R = substr($couleur, 1, 2);
        $rouge = hexdec($R);
        $V = substr($couleur, 3, 2);
        $vert = hexdec($V);
        $B = substr($couleur, 5, 2);
        $bleu = hexdec($B);
        $tbl_couleur = array();
        $tbl_couleur['R']=$rouge;
        $tbl_couleur['V']=$vert;
        $tbl_couleur['B']=$bleu;
        return $tbl_couleur;
    }
    //conversion pixel -> millimeter at 72 dpi
    private function _px2mm($px){
        return $px*25.4/72;
    }
    private function _txtentities($html){
        $trans = get_html_translation_table(HTML_ENTITIES);
        $trans = array_flip($trans);
        return strtr($html, $trans);
    }
    
   
    
    
}




