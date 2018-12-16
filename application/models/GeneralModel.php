<?php
/**
 * Created by PhpStorm.
 * User: Hitesh
 * Date: 16-Dec-18
 * Time: 3:40 AM
 * Coded by Hitesh with love
 * Find me at confused1108.github.io
 */
define('FPDF_VERSION','1.81');

class FPDF
{
    protected $page;               // current page number
    protected $n;                  // current object number
    protected $offsets;            // array of object offsets
    protected $buffer;             // buffer holding in-memory PDF
    protected $pages;              // array containing pages
    protected $state;              // current document state
    protected $compress;           // compression flag
    protected $k;                  // scale factor (number of points in user unit)
    protected $DefOrientation;     // default orientation
    protected $CurOrientation;     // current orientation
    protected $StdPageSizes;       // standard page sizes
    protected $DefPageSize;        // default page size
    protected $CurPageSize;        // current page size
    protected $CurRotation;        // current page rotation
    protected $PageInfo;           // page-related data
    protected $wPt, $hPt;          // dimensions of current page in points
    protected $w, $h;              // dimensions of current page in user unit
    protected $lMargin;            // left margin
    protected $tMargin;            // top margin
    protected $rMargin;            // right margin
    protected $bMargin;            // page break margin
    protected $cMargin;            // cell margin
    protected $x, $y;              // current position in user unit
    protected $lasth;              // height of last printed cell
    protected $LineWidth;          // line width in user unit
    protected $fontpath;           // path containing fonts
    protected $CoreFonts;          // array of core font names
    protected $fonts;              // array of used fonts
    protected $FontFiles;          // array of font files
    protected $encodings;          // array of encodings
    protected $cmaps;              // array of ToUnicode CMaps
    protected $FontFamily;         // current font family
    protected $FontStyle;          // current font style
    protected $underline;          // underlining flag
    protected $CurrentFont;        // current font info
    protected $FontSizePt;         // current font size in points
    protected $FontSize;           // current font size in user unit
    protected $DrawColor;          // commands for drawing color
    protected $FillColor;          // commands for filling color
    protected $TextColor;          // commands for text color
    protected $ColorFlag;          // indicates whether fill and text colors are different
    protected $WithAlpha;          // indicates whether alpha channel is used
    protected $ws;                 // word spacing
    protected $images;             // array of used images
    protected $PageLinks;          // array of links in pages
    protected $links;              // array of internal links
    protected $AutoPageBreak;      // automatic page breaking
    protected $PageBreakTrigger;   // threshold used to trigger page breaks
    protected $InHeader;           // flag set when processing header
    protected $InFooter;           // flag set when processing footer
    protected $AliasNbPages;       // alias for total number of pages
    protected $ZoomMode;           // zoom display mode
    protected $LayoutMode;         // layout display mode
    protected $metadata;           // document properties
    protected $PDFVersion;         // PDF version number

    /*******************************************************************************
     *                               Public methods                                 *
     *******************************************************************************/

    function __construct($orientation='P', $unit='mm', $size='A4')
    {
        // Some checks
        $this->_dochecks();
        // Initialization of properties
        $this->state = 0;
        $this->page = 0;
        $this->n = 2;
        $this->buffer = '';
        $this->pages = array();
        $this->PageInfo = array();
        $this->fonts = array();
        $this->FontFiles = array();
        $this->encodings = array();
        $this->cmaps = array();
        $this->images = array();
        $this->links = array();
        $this->InHeader = false;
        $this->InFooter = false;
        $this->lasth = 0;
        $this->FontFamily = '';
        $this->FontStyle = '';
        $this->FontSizePt = 12;
        $this->underline = false;
        $this->DrawColor = '0 G';
        $this->FillColor = '0 g';
        $this->TextColor = '0 g';
        $this->ColorFlag = false;
        $this->WithAlpha = false;
        $this->ws = 0;
        // Font path
        if(defined('FPDF_FONTPATH'))
        {
            $this->fontpath = FPDF_FONTPATH;
            if(substr($this->fontpath,-1)!='/' && substr($this->fontpath,-1)!='\\')
                $this->fontpath .= '/';
        }
        elseif(is_dir(dirname(__FILE__).'/font'))
            $this->fontpath = dirname(__FILE__).'/font/';
        else
            $this->fontpath = '';
        // Core fonts
        $this->CoreFonts = array('courier', 'helvetica', 'times', 'symbol', 'zapfdingbats');
        // Scale factor
        if($unit=='pt')
            $this->k = 1;
        elseif($unit=='mm')
            $this->k = 72/25.4;
        elseif($unit=='cm')
            $this->k = 72/2.54;
        elseif($unit=='in')
            $this->k = 72;
        else
            $this->Error('Incorrect unit: '.$unit);
        // Page sizes
        $this->StdPageSizes = array('a3'=>array(841.89,1190.55), 'a4'=>array(595.28,841.89), 'a5'=>array(420.94,595.28),
            'letter'=>array(612,792), 'legal'=>array(612,1008));
        $size = $this->_getpagesize($size);
        $this->DefPageSize = $size;
        $this->CurPageSize = $size;
        // Page orientation
        $orientation = strtolower($orientation);
        if($orientation=='p' || $orientation=='portrait')
        {
            $this->DefOrientation = 'P';
            $this->w = $size[0];
            $this->h = $size[1];
        }
        elseif($orientation=='l' || $orientation=='landscape')
        {
            $this->DefOrientation = 'L';
            $this->w = $size[1];
            $this->h = $size[0];
        }
        else
            $this->Error('Incorrect orientation: '.$orientation);
        $this->CurOrientation = $this->DefOrientation;
        $this->wPt = $this->w*$this->k;
        $this->hPt = $this->h*$this->k;
        // Page rotation
        $this->CurRotation = 0;
        // Page margins (1 cm)
        $margin = 28.35/$this->k;
        $this->SetMargins($margin,$margin);
        // Interior cell margin (1 mm)
        $this->cMargin = $margin/10;
        // Line width (0.2 mm)
        $this->LineWidth = .567/$this->k;
        // Automatic page break
        $this->SetAutoPageBreak(true,2*$margin);
        // Default display mode
        $this->SetDisplayMode('default');
        // Enable compression
        $this->SetCompression(true);
        // Set default PDF version number
        $this->PDFVersion = '1.3';
    }

    function SetMargins($left, $top, $right=null)
    {
        // Set left, top and right margins
        $this->lMargin = $left;
        $this->tMargin = $top;
        if($right===null)
            $right = $left;
        $this->rMargin = $right;
    }

    function SetLeftMargin($margin)
    {
        // Set left margin
        $this->lMargin = $margin;
        if($this->page>0 && $this->x<$margin)
            $this->x = $margin;
    }

    function SetTopMargin($margin)
    {
        // Set top margin
        $this->tMargin = $margin;
    }

    function SetRightMargin($margin)
    {
        // Set right margin
        $this->rMargin = $margin;
    }

    function SetAutoPageBreak($auto, $margin=0)
    {
        // Set auto page break mode and triggering margin
        $this->AutoPageBreak = $auto;
        $this->bMargin = $margin;
        $this->PageBreakTrigger = $this->h-$margin;
    }

    function SetDisplayMode($zoom, $layout='default')
    {
        // Set display mode in viewer
        if($zoom=='fullpage' || $zoom=='fullwidth' || $zoom=='real' || $zoom=='default' || !is_string($zoom))
            $this->ZoomMode = $zoom;
        else
            $this->Error('Incorrect zoom display mode: '.$zoom);
        if($layout=='single' || $layout=='continuous' || $layout=='two' || $layout=='default')
            $this->LayoutMode = $layout;
        else
            $this->Error('Incorrect layout display mode: '.$layout);
    }

    function SetCompression($compress)
    {
        // Set page compression
        if(function_exists('gzcompress'))
            $this->compress = $compress;
        else
            $this->compress = false;
    }

    function SetTitle($title, $isUTF8=false)
    {
        // Title of document
        $this->metadata['Title'] = $isUTF8 ? $title : utf8_encode($title);
    }

    function SetAuthor($author, $isUTF8=false)
    {
        // Author of document
        $this->metadata['Author'] = $isUTF8 ? $author : utf8_encode($author);
    }

    function SetSubject($subject, $isUTF8=false)
    {
        // Subject of document
        $this->metadata['Subject'] = $isUTF8 ? $subject : utf8_encode($subject);
    }

    function SetKeywords($keywords, $isUTF8=false)
    {
        // Keywords of document
        $this->metadata['Keywords'] = $isUTF8 ? $keywords : utf8_encode($keywords);
    }

    function SetCreator($creator, $isUTF8=false)
    {
        // Creator of document
        $this->metadata['Creator'] = $isUTF8 ? $creator : utf8_encode($creator);
    }

    function AliasNbPages($alias='{nb}')
    {
        // Define an alias for total number of pages
        $this->AliasNbPages = $alias;
    }

    function Error($msg)
    {
        // Fatal error
        throw new Exception('FPDF error: '.$msg);
    }

    function Close()
    {
        // Terminate document
        if($this->state==3)
            return;
        if($this->page==0)
            $this->AddPage();
        // Page footer
        $this->InFooter = true;
        $this->Footer();
        $this->InFooter = false;
        // Close page
        $this->_endpage();
        // Close document
        $this->_enddoc();
    }

    function AddPage($orientation='', $size='', $rotation=0)
    {
        // Start a new page
        if($this->state==3)
            $this->Error('The document is closed');
        $family = $this->FontFamily;
        $style = $this->FontStyle.($this->underline ? 'U' : '');
        $fontsize = $this->FontSizePt;
        $lw = $this->LineWidth;
        $dc = $this->DrawColor;
        $fc = $this->FillColor;
        $tc = $this->TextColor;
        $cf = $this->ColorFlag;
        if($this->page>0)
        {
            // Page footer
            $this->InFooter = true;
            $this->Footer();
            $this->InFooter = false;
            // Close page
            $this->_endpage();
        }
        // Start new page
        $this->_beginpage($orientation,$size,$rotation);
        // Set line cap style to square
        $this->_out('2 J');
        // Set line width
        $this->LineWidth = $lw;
        $this->_out(sprintf('%.2F w',$lw*$this->k));
        // Set font
        if($family)
            $this->SetFont($family,$style,$fontsize);
        // Set colors
        $this->DrawColor = $dc;
        if($dc!='0 G')
            $this->_out($dc);
        $this->FillColor = $fc;
        if($fc!='0 g')
            $this->_out($fc);
        $this->TextColor = $tc;
        $this->ColorFlag = $cf;
        // Page header
        $this->InHeader = true;
        $this->Header();
        $this->InHeader = false;
        // Restore line width
        if($this->LineWidth!=$lw)
        {
            $this->LineWidth = $lw;
            $this->_out(sprintf('%.2F w',$lw*$this->k));
        }
        // Restore font
        if($family)
            $this->SetFont($family,$style,$fontsize);
        // Restore colors
        if($this->DrawColor!=$dc)
        {
            $this->DrawColor = $dc;
            $this->_out($dc);
        }
        if($this->FillColor!=$fc)
        {
            $this->FillColor = $fc;
            $this->_out($fc);
        }
        $this->TextColor = $tc;
        $this->ColorFlag = $cf;
    }

    function Header()
    {
        // To be implemented in your own inherited class
    }

    function Footer()
    {
        // To be implemented in your own inherited class
    }

    function PageNo()
    {
        // Get current page number
        return $this->page;
    }

    function SetDrawColor($r, $g=null, $b=null)
    {
        // Set color for all stroking operations
        if(($r==0 && $g==0 && $b==0) || $g===null)
            $this->DrawColor = sprintf('%.3F G',$r/255);
        else
            $this->DrawColor = sprintf('%.3F %.3F %.3F RG',$r/255,$g/255,$b/255);
        if($this->page>0)
            $this->_out($this->DrawColor);
    }

    function SetFillColor($r, $g=null, $b=null)
    {
        // Set color for all filling operations
        if(($r==0 && $g==0 && $b==0) || $g===null)
            $this->FillColor = sprintf('%.3F g',$r/255);
        else
            $this->FillColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
        $this->ColorFlag = ($this->FillColor!=$this->TextColor);
        if($this->page>0)
            $this->_out($this->FillColor);
    }

    function SetTextColor($r, $g=null, $b=null)
    {
        // Set color for text
        if(($r==0 && $g==0 && $b==0) || $g===null)
            $this->TextColor = sprintf('%.3F g',$r/255);
        else
            $this->TextColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
        $this->ColorFlag = ($this->FillColor!=$this->TextColor);
    }

    function GetStringWidth($s)
    {
        // Get width of a string in the current font
        $s = (string)$s;
        $cw = &$this->CurrentFont['cw'];
        $w = 0;
        $l = strlen($s);
        for($i=0;$i<$l;$i++)
            $w += $cw[$s[$i]];
        return $w*$this->FontSize/1000;
    }

    function SetLineWidth($width)
    {
        // Set line width
        $this->LineWidth = $width;
        if($this->page>0)
            $this->_out(sprintf('%.2F w',$width*$this->k));
    }

    function Line($x1, $y1, $x2, $y2)
    {
        // Draw a line
        $this->_out(sprintf('%.2F %.2F m %.2F %.2F l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k));
    }

    function Rect($x, $y, $w, $h, $style='')
    {
        // Draw a rectangle
        if($style=='F')
            $op = 'f';
        elseif($style=='FD' || $style=='DF')
            $op = 'B';
        else
            $op = 'S';
        $this->_out(sprintf('%.2F %.2F %.2F %.2F re %s',$x*$this->k,($this->h-$y)*$this->k,$w*$this->k,-$h*$this->k,$op));
    }

    function AddFont($family, $style='', $file='')
    {
        // Add a TrueType, OpenType or Type1 font
        $family = strtolower($family);
        if($file=='')
            $file = str_replace(' ','',$family).strtolower($style).'.php';
        $style = strtoupper($style);
        if($style=='IB')
            $style = 'BI';
        $fontkey = $family.$style;
        if(isset($this->fonts[$fontkey]))
            return;
        $info = $this->_loadfont($file);
        $info['i'] = count($this->fonts)+1;
        if(!empty($info['file']))
        {
            // Embedded font
            if($info['type']=='TrueType')
                $this->FontFiles[$info['file']] = array('length1'=>$info['originalsize']);
            else
                $this->FontFiles[$info['file']] = array('length1'=>$info['size1'], 'length2'=>$info['size2']);
        }
        $this->fonts[$fontkey] = $info;
    }

    function SetFont($family, $style='', $size=0)
    {
        // Select a font; size given in points
        if($family=='')
            $family = $this->FontFamily;
        else
            $family = strtolower($family);
        $style = strtoupper($style);
        if(strpos($style,'U')!==false)
        {
            $this->underline = true;
            $style = str_replace('U','',$style);
        }
        else
            $this->underline = false;
        if($style=='IB')
            $style = 'BI';
        if($size==0)
            $size = $this->FontSizePt;
        // Test if font is already selected
        if($this->FontFamily==$family && $this->FontStyle==$style && $this->FontSizePt==$size)
            return;
        // Test if font is already loaded
        $fontkey = $family.$style;
        if(!isset($this->fonts[$fontkey]))
        {
            // Test if one of the core fonts
            if($family=='arial')
                $family = 'helvetica';
            if(in_array($family,$this->CoreFonts))
            {
                if($family=='symbol' || $family=='zapfdingbats')
                    $style = '';
                $fontkey = $family.$style;
                if(!isset($this->fonts[$fontkey]))
                    $this->AddFont($family,$style);
            }
            else
                $this->Error('Undefined font: '.$family.' '.$style);
        }
        // Select it
        $this->FontFamily = $family;
        $this->FontStyle = $style;
        $this->FontSizePt = $size;
        $this->FontSize = $size/$this->k;
        $this->CurrentFont = &$this->fonts[$fontkey];
        if($this->page>0)
            $this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
    }

    function SetFontSize($size)
    {
        // Set font size in points
        if($this->FontSizePt==$size)
            return;
        $this->FontSizePt = $size;
        $this->FontSize = $size/$this->k;
        if($this->page>0)
            $this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
    }

    function AddLink()
    {
        // Create a new internal link
        $n = count($this->links)+1;
        $this->links[$n] = array(0, 0);
        return $n;
    }

    function SetLink($link, $y=0, $page=-1)
    {
        // Set destination of internal link
        if($y==-1)
            $y = $this->y;
        if($page==-1)
            $page = $this->page;
        $this->links[$link] = array($page, $y);
    }

    function Link($x, $y, $w, $h, $link)
    {
        // Put a link on the page
        $this->PageLinks[$this->page][] = array($x*$this->k, $this->hPt-$y*$this->k, $w*$this->k, $h*$this->k, $link);
    }

    function Text($x, $y, $txt)
    {
        // Output a string
        if(!isset($this->CurrentFont))
            $this->Error('No font has been set');
        $s = sprintf('BT %.2F %.2F Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
        if($this->underline && $txt!='')
            $s .= ' '.$this->_dounderline($x,$y,$txt);
        if($this->ColorFlag)
            $s = 'q '.$this->TextColor.' '.$s.' Q';
        $this->_out($s);
    }

    function AcceptPageBreak()
    {
        // Accept automatic page break or not
        return $this->AutoPageBreak;
    }

    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        // Output a cell
        $k = $this->k;
        if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
        {
            // Automatic page break
            $x = $this->x;
            $ws = $this->ws;
            if($ws>0)
            {
                $this->ws = 0;
                $this->_out('0 Tw');
            }
            $this->AddPage($this->CurOrientation,$this->CurPageSize,$this->CurRotation);
            $this->x = $x;
            if($ws>0)
            {
                $this->ws = $ws;
                $this->_out(sprintf('%.3F Tw',$ws*$k));
            }
        }
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $s = '';
        if($fill || $border==1)
        {
            if($fill)
                $op = ($border==1) ? 'B' : 'f';
            else
                $op = 'S';
            $s = sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
        }
        if(is_string($border))
        {
            $x = $this->x;
            $y = $this->y;
            if(strpos($border,'L')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
            if(strpos($border,'T')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
            if(strpos($border,'R')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
            if(strpos($border,'B')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
        }
        if($txt!=='')
        {
            if(!isset($this->CurrentFont))
                $this->Error('No font has been set');
            if($align=='R')
                $dx = $w-$this->cMargin-$this->GetStringWidth($txt);
            elseif($align=='C')
                $dx = ($w-$this->GetStringWidth($txt))/2;
            else
                $dx = $this->cMargin;
            if($this->ColorFlag)
                $s .= 'q '.$this->TextColor.' ';
            $s .= sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$this->_escape($txt));
            if($this->underline)
                $s .= ' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
            if($this->ColorFlag)
                $s .= ' Q';
            if($link)
                $this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$this->GetStringWidth($txt),$this->FontSize,$link);
        }
        if($s)
            $this->_out($s);
        $this->lasth = $h;
        if($ln>0)
        {
            // Go to next line
            $this->y += $h;
            if($ln==1)
                $this->x = $this->lMargin;
        }
        else
            $this->x += $w;
    }

    function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
    {
        // Output text with automatic or explicit line breaks
        if(!isset($this->CurrentFont))
            $this->Error('No font has been set');
        $cw = &$this->CurrentFont['cw'];
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r",'',$txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n")
            $nb--;
        $b = 0;
        if($border)
        {
            if($border==1)
            {
                $border = 'LTRB';
                $b = 'LRT';
                $b2 = 'LR';
            }
            else
            {
                $b2 = '';
                if(strpos($border,'L')!==false)
                    $b2 .= 'L';
                if(strpos($border,'R')!==false)
                    $b2 .= 'R';
                $b = (strpos($border,'T')!==false) ? $b2.'T' : $b2;
            }
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $ns = 0;
        $nl = 1;
        while($i<$nb)
        {
            // Get next character
            $c = $s[$i];
            if($c=="\n")
            {
                // Explicit line break
                if($this->ws>0)
                {
                    $this->ws = 0;
                    $this->_out('0 Tw');
                }
                $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                $nl++;
                if($border && $nl==2)
                    $b = $b2;
                continue;
            }
            if($c==' ')
            {
                $sep = $i;
                $ls = $l;
                $ns++;
            }
            $l += $cw[$c];
            if($l>$wmax)
            {
                // Automatic line break
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                    if($this->ws>0)
                    {
                        $this->ws = 0;
                        $this->_out('0 Tw');
                    }
                    $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
                }
                else
                {
                    if($align=='J')
                    {
                        $this->ws = ($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
                        $this->_out(sprintf('%.3F Tw',$this->ws*$this->k));
                    }
                    $this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
                    $i = $sep+1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                $nl++;
                if($border && $nl==2)
                    $b = $b2;
            }
            else
                $i++;
        }
        // Last chunk
        if($this->ws>0)
        {
            $this->ws = 0;
            $this->_out('0 Tw');
        }
        if($border && strpos($border,'B')!==false)
            $b .= 'B';
        $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
        $this->x = $this->lMargin;
    }

    function Write($h, $txt, $link='')
    {
        // Output text in flowing mode
        if(!isset($this->CurrentFont))
            $this->Error('No font has been set');
        $cw = &$this->CurrentFont['cw'];
        $w = $this->w-$this->rMargin-$this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r",'',$txt);
        $nb = strlen($s);
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while($i<$nb)
        {
            // Get next character
            $c = $s[$i];
            if($c=="\n")
            {
                // Explicit line break
                $this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',false,$link);
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                if($nl==1)
                {
                    $this->x = $this->lMargin;
                    $w = $this->w-$this->rMargin-$this->x;
                    $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
                }
                $nl++;
                continue;
            }
            if($c==' ')
                $sep = $i;
            $l += $cw[$c];
            if($l>$wmax)
            {
                // Automatic line break
                if($sep==-1)
                {
                    if($this->x>$this->lMargin)
                    {
                        // Move to next line
                        $this->x = $this->lMargin;
                        $this->y += $h;
                        $w = $this->w-$this->rMargin-$this->x;
                        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
                        $i++;
                        $nl++;
                        continue;
                    }
                    if($i==$j)
                        $i++;
                    $this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',false,$link);
                }
                else
                {
                    $this->Cell($w,$h,substr($s,$j,$sep-$j),0,2,'',false,$link);
                    $i = $sep+1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                if($nl==1)
                {
                    $this->x = $this->lMargin;
                    $w = $this->w-$this->rMargin-$this->x;
                    $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
                }
                $nl++;
            }
            else
                $i++;
        }
        // Last chunk
        if($i!=$j)
            $this->Cell($l/1000*$this->FontSize,$h,substr($s,$j),0,0,'',false,$link);
    }

    function Ln($h=null)
    {
        // Line feed; default value is the last cell height
        $this->x = $this->lMargin;
        if($h===null)
            $this->y += $this->lasth;
        else
            $this->y += $h;
    }

    function Image($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='')
    {
        // Put an image on the page
        if($file=='')
            $this->Error('Image file name is empty');
        if(!isset($this->images[$file]))
        {
            // First use of this image, get info
            if($type=='')
            {
                $pos = strrpos($file,'.');
                if(!$pos)
                    $this->Error('Image file has no extension and no type was specified: '.$file);
                $type = substr($file,$pos+1);
            }
            $type = strtolower($type);
            if($type=='jpeg')
                $type = 'jpg';
            $mtd = '_parse'.$type;
            if(!method_exists($this,$mtd))
                $this->Error('Unsupported image type: '.$type);
            $info = $this->$mtd($file);
            $info['i'] = count($this->images)+1;
            $this->images[$file] = $info;
        }
        else
            $info = $this->images[$file];

        // Automatic width and height calculation if needed
        if($w==0 && $h==0)
        {
            // Put image at 96 dpi
            $w = -96;
            $h = -96;
        }
        if($w<0)
            $w = -$info['w']*72/$w/$this->k;
        if($h<0)
            $h = -$info['h']*72/$h/$this->k;
        if($w==0)
            $w = $h*$info['w']/$info['h'];
        if($h==0)
            $h = $w*$info['h']/$info['w'];

        // Flowing mode
        if($y===null)
        {
            if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
            {
                // Automatic page break
                $x2 = $this->x;
                $this->AddPage($this->CurOrientation,$this->CurPageSize,$this->CurRotation);
                $this->x = $x2;
            }
            $y = $this->y;
            $this->y += $h;
        }

        if($x===null)
            $x = $this->x;
        $this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q',$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$info['i']));
        if($link)
            $this->Link($x,$y,$w,$h,$link);
    }

    function GetPageWidth()
    {
        // Get current page width
        return $this->w;
    }

    function GetPageHeight()
    {
        // Get current page height
        return $this->h;
    }

    function GetX()
    {
        // Get x position
        return $this->x;
    }

    function SetX($x)
    {
        // Set x position
        if($x>=0)
            $this->x = $x;
        else
            $this->x = $this->w+$x;
    }

    function GetY()
    {
        // Get y position
        return $this->y;
    }

    function SetY($y, $resetX=true)
    {
        // Set y position and optionally reset x
        if($y>=0)
            $this->y = $y;
        else
            $this->y = $this->h+$y;
        if($resetX)
            $this->x = $this->lMargin;
    }

    function SetXY($x, $y)
    {
        // Set x and y positions
        $this->SetX($x);
        $this->SetY($y,false);
    }

    function Output($dest='', $name='', $isUTF8=false)
    {
        // Output PDF to some destination
        $this->Close();
        if(strlen($name)==1 && strlen($dest)!=1)
        {
            // Fix parameter order
            $tmp = $dest;
            $dest = $name;
            $name = $tmp;
        }
        if($dest=='')
            $dest = 'I';
        if($name=='')
            $name = 'doc.pdf';
        switch(strtoupper($dest))
        {
            case 'I':
                // Send to standard output
                $this->_checkoutput();
                if(PHP_SAPI!='cli')
                {
                    // We send to a browser
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: inline; '.$this->_httpencode('filename',$name,$isUTF8));
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                }
                echo $this->buffer;
                break;
            case 'D':
                // Download file
                $this->_checkoutput();
                header('Content-Type: application/x-download');
                header('Content-Disposition: attachment; '.$this->_httpencode('filename',$name,$isUTF8));
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                echo $this->buffer;
                break;
            case 'F':
                // Save to local file
                if(!file_put_contents($name,$this->buffer))
                    $this->Error('Unable to create output file: '.$name);
                break;
            case 'S':
                // Return as a string
                return $this->buffer;
            default:
                $this->Error('Incorrect output destination: '.$dest);
        }
        return '';
    }

    /*******************************************************************************
     *                              Protected methods                               *
     *******************************************************************************/

    protected function _dochecks()
    {
        // Check mbstring overloading
        if(ini_get('mbstring.func_overload') & 2)
            $this->Error('mbstring overloading must be disabled');
        // Ensure runtime magic quotes are disabled
        if(get_magic_quotes_runtime())
            @set_magic_quotes_runtime(0);
    }

    protected function _checkoutput()
    {
        if(PHP_SAPI!='cli')
        {
            if(headers_sent($file,$line))
                $this->Error("Some data has already been output, can't send PDF file (output started at $file:$line)");
        }
        if(ob_get_length())
        {
            // The output buffer is not empty
            if(preg_match('/^(\xEF\xBB\xBF)?\s*$/',ob_get_contents()))
            {
                // It contains only a UTF-8 BOM and/or whitespace, let's clean it
                ob_clean();
            }
            else
                $this->Error("Some data has already been output, can't send PDF file");
        }
    }

    protected function _getpagesize($size)
    {
        if(is_string($size))
        {
            $size = strtolower($size);
            if(!isset($this->StdPageSizes[$size]))
                $this->Error('Unknown page size: '.$size);
            $a = $this->StdPageSizes[$size];
            return array($a[0]/$this->k, $a[1]/$this->k);
        }
        else
        {
            if($size[0]>$size[1])
                return array($size[1], $size[0]);
            else
                return $size;
        }
    }

    protected function _beginpage($orientation, $size, $rotation)
    {
        $this->page++;
        $this->pages[$this->page] = '';
        $this->state = 2;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->FontFamily = '';
        // Check page size and orientation
        if($orientation=='')
            $orientation = $this->DefOrientation;
        else
            $orientation = strtoupper($orientation[0]);
        if($size=='')
            $size = $this->DefPageSize;
        else
            $size = $this->_getpagesize($size);
        if($orientation!=$this->CurOrientation || $size[0]!=$this->CurPageSize[0] || $size[1]!=$this->CurPageSize[1])
        {
            // New size or orientation
            if($orientation=='P')
            {
                $this->w = $size[0];
                $this->h = $size[1];
            }
            else
            {
                $this->w = $size[1];
                $this->h = $size[0];
            }
            $this->wPt = $this->w*$this->k;
            $this->hPt = $this->h*$this->k;
            $this->PageBreakTrigger = $this->h-$this->bMargin;
            $this->CurOrientation = $orientation;
            $this->CurPageSize = $size;
        }
        if($orientation!=$this->DefOrientation || $size[0]!=$this->DefPageSize[0] || $size[1]!=$this->DefPageSize[1])
            $this->PageInfo[$this->page]['size'] = array($this->wPt, $this->hPt);
        if($rotation!=0)
        {
            if($rotation%90!=0)
                $this->Error('Incorrect rotation value: '.$rotation);
            $this->CurRotation = $rotation;
            $this->PageInfo[$this->page]['rotation'] = $rotation;
        }
    }

    protected function _endpage()
    {
        $this->state = 1;
    }

    protected function _loadfont($font)
    {
        // Load a font definition file from the font directory
        if(strpos($font,'/')!==false || strpos($font,"\\")!==false)
            $this->Error('Incorrect font definition file name: '.$font);

        $type = 'Core';
        $name = 'Helvetica-Bold';
        $up = -100;
        $ut = 50;
        $cw = array(
            chr(0)=>278,chr(1)=>278,chr(2)=>278,chr(3)=>278,chr(4)=>278,chr(5)=>278,chr(6)=>278,chr(7)=>278,chr(8)=>278,chr(9)=>278,chr(10)=>278,chr(11)=>278,chr(12)=>278,chr(13)=>278,chr(14)=>278,chr(15)=>278,chr(16)=>278,chr(17)=>278,chr(18)=>278,chr(19)=>278,chr(20)=>278,chr(21)=>278,
            chr(22)=>278,chr(23)=>278,chr(24)=>278,chr(25)=>278,chr(26)=>278,chr(27)=>278,chr(28)=>278,chr(29)=>278,chr(30)=>278,chr(31)=>278,' '=>278,'!'=>333,'"'=>474,'#'=>556,'$'=>556,'%'=>889,'&'=>722,'\''=>238,'('=>333,')'=>333,'*'=>389,'+'=>584,
            ','=>278,'-'=>333,'.'=>278,'/'=>278,'0'=>556,'1'=>556,'2'=>556,'3'=>556,'4'=>556,'5'=>556,'6'=>556,'7'=>556,'8'=>556,'9'=>556,':'=>333,';'=>333,'<'=>584,'='=>584,'>'=>584,'?'=>611,'@'=>975,'A'=>722,
            'B'=>722,'C'=>722,'D'=>722,'E'=>667,'F'=>611,'G'=>778,'H'=>722,'I'=>278,'J'=>556,'K'=>722,'L'=>611,'M'=>833,'N'=>722,'O'=>778,'P'=>667,'Q'=>778,'R'=>722,'S'=>667,'T'=>611,'U'=>722,'V'=>667,'W'=>944,
            'X'=>667,'Y'=>667,'Z'=>611,'['=>333,'\\'=>278,']'=>333,'^'=>584,'_'=>556,'`'=>333,'a'=>556,'b'=>611,'c'=>556,'d'=>611,'e'=>556,'f'=>333,'g'=>611,'h'=>611,'i'=>278,'j'=>278,'k'=>556,'l'=>278,'m'=>889,
            'n'=>611,'o'=>611,'p'=>611,'q'=>611,'r'=>389,'s'=>556,'t'=>333,'u'=>611,'v'=>556,'w'=>778,'x'=>556,'y'=>556,'z'=>500,'{'=>389,'|'=>280,'}'=>389,'~'=>584,chr(127)=>350,chr(128)=>556,chr(129)=>350,chr(130)=>278,chr(131)=>556,
            chr(132)=>500,chr(133)=>1000,chr(134)=>556,chr(135)=>556,chr(136)=>333,chr(137)=>1000,chr(138)=>667,chr(139)=>333,chr(140)=>1000,chr(141)=>350,chr(142)=>611,chr(143)=>350,chr(144)=>350,chr(145)=>278,chr(146)=>278,chr(147)=>500,chr(148)=>500,chr(149)=>350,chr(150)=>556,chr(151)=>1000,chr(152)=>333,chr(153)=>1000,
            chr(154)=>556,chr(155)=>333,chr(156)=>944,chr(157)=>350,chr(158)=>500,chr(159)=>667,chr(160)=>278,chr(161)=>333,chr(162)=>556,chr(163)=>556,chr(164)=>556,chr(165)=>556,chr(166)=>280,chr(167)=>556,chr(168)=>333,chr(169)=>737,chr(170)=>370,chr(171)=>556,chr(172)=>584,chr(173)=>333,chr(174)=>737,chr(175)=>333,
            chr(176)=>400,chr(177)=>584,chr(178)=>333,chr(179)=>333,chr(180)=>333,chr(181)=>611,chr(182)=>556,chr(183)=>278,chr(184)=>333,chr(185)=>333,chr(186)=>365,chr(187)=>556,chr(188)=>834,chr(189)=>834,chr(190)=>834,chr(191)=>611,chr(192)=>722,chr(193)=>722,chr(194)=>722,chr(195)=>722,chr(196)=>722,chr(197)=>722,
            chr(198)=>1000,chr(199)=>722,chr(200)=>667,chr(201)=>667,chr(202)=>667,chr(203)=>667,chr(204)=>278,chr(205)=>278,chr(206)=>278,chr(207)=>278,chr(208)=>722,chr(209)=>722,chr(210)=>778,chr(211)=>778,chr(212)=>778,chr(213)=>778,chr(214)=>778,chr(215)=>584,chr(216)=>778,chr(217)=>722,chr(218)=>722,chr(219)=>722,
            chr(220)=>722,chr(221)=>667,chr(222)=>667,chr(223)=>611,chr(224)=>556,chr(225)=>556,chr(226)=>556,chr(227)=>556,chr(228)=>556,chr(229)=>556,chr(230)=>889,chr(231)=>556,chr(232)=>556,chr(233)=>556,chr(234)=>556,chr(235)=>556,chr(236)=>278,chr(237)=>278,chr(238)=>278,chr(239)=>278,chr(240)=>611,chr(241)=>611,
            chr(242)=>611,chr(243)=>611,chr(244)=>611,chr(245)=>611,chr(246)=>611,chr(247)=>584,chr(248)=>611,chr(249)=>611,chr(250)=>611,chr(251)=>611,chr(252)=>611,chr(253)=>556,chr(254)=>611,chr(255)=>556);
        $enc = 'cp1252';
        $uv = array(0=>array(0,128),128=>8364,130=>8218,131=>402,132=>8222,133=>8230,134=>array(8224,2),136=>710,137=>8240,138=>352,139=>8249,140=>338,142=>381,145=>array(8216,2),147=>array(8220,2),149=>8226,150=>array(8211,2),152=>732,153=>8482,154=>353,155=>8250,156=>339,158=>382,159=>376,160=>array(160,96));


        if(!isset($name))
            $this->Error('Could not include font definition file');
        if(isset($enc))
            $enc = strtolower($enc);
        if(!isset($subsetted))
            $subsetted = false;
        return get_defined_vars();
    }

    protected function _isascii($s)
    {
        // Test if string is ASCII
        $nb = strlen($s);
        for($i=0;$i<$nb;$i++)
        {
            if(ord($s[$i])>127)
                return false;
        }
        return true;
    }

    protected function _httpencode($param, $value, $isUTF8)
    {
        // Encode HTTP header field parameter
        if($this->_isascii($value))
            return $param.'="'.$value.'"';
        if(!$isUTF8)
            $value = utf8_encode($value);
        if(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')!==false)
            return $param.'="'.rawurlencode($value).'"';
        else
            return $param."*=UTF-8''".rawurlencode($value);
    }

    protected function _UTF8toUTF16($s)
    {
        // Convert UTF-8 to UTF-16BE with BOM
        $res = "\xFE\xFF";
        $nb = strlen($s);
        $i = 0;
        while($i<$nb)
        {
            $c1 = ord($s[$i++]);
            if($c1>=224)
            {
                // 3-byte character
                $c2 = ord($s[$i++]);
                $c3 = ord($s[$i++]);
                $res .= chr((($c1 & 0x0F)<<4) + (($c2 & 0x3C)>>2));
                $res .= chr((($c2 & 0x03)<<6) + ($c3 & 0x3F));
            }
            elseif($c1>=192)
            {
                // 2-byte character
                $c2 = ord($s[$i++]);
                $res .= chr(($c1 & 0x1C)>>2);
                $res .= chr((($c1 & 0x03)<<6) + ($c2 & 0x3F));
            }
            else
            {
                // Single-byte character
                $res .= "\0".chr($c1);
            }
        }
        return $res;
    }

    protected function _escape($s)
    {
        // Escape special characters
        if(strpos($s,'(')!==false || strpos($s,')')!==false || strpos($s,'\\')!==false || strpos($s,"\r")!==false)
            return str_replace(array('\\','(',')',"\r"), array('\\\\','\\(','\\)','\\r'), $s);
        else
            return $s;
    }

    protected function _textstring($s)
    {
        // Format a text string
        if(!$this->_isascii($s))
            $s = $this->_UTF8toUTF16($s);
        return '('.$this->_escape($s).')';
    }

    protected function _dounderline($x, $y, $txt)
    {
        // Underline text
        $up = $this->CurrentFont['up'];
        $ut = $this->CurrentFont['ut'];
        $w = $this->GetStringWidth($txt)+$this->ws*substr_count($txt,' ');
        return sprintf('%.2F %.2F %.2F %.2F re f',$x*$this->k,($this->h-($y-$up/1000*$this->FontSize))*$this->k,$w*$this->k,-$ut/1000*$this->FontSizePt);
    }

    protected function _parsejpg($file)
    {
        // Extract info from a JPEG file
        $a = getimagesize($file);
        if(!$a)
            $this->Error('Missing or incorrect image file: '.$file);
        if($a[2]!=2)
            $this->Error('Not a JPEG file: '.$file);
        if(!isset($a['channels']) || $a['channels']==3)
            $colspace = 'DeviceRGB';
        elseif($a['channels']==4)
            $colspace = 'DeviceCMYK';
        else
            $colspace = 'DeviceGray';
        $bpc = isset($a['bits']) ? $a['bits'] : 8;
        $data = file_get_contents($file);
        return array('w'=>$a[0], 'h'=>$a[1], 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'DCTDecode', 'data'=>$data);
    }

    protected function _parsepng($file)
    {
        // Extract info from a PNG file
        $f = fopen($file,'rb');
        if(!$f)
            $this->Error('Can\'t open image file: '.$file);
        $info = $this->_parsepngstream($f,$file);
        fclose($f);
        return $info;
    }

    protected function _parsepngstream($f, $file)
    {
        // Check signature
        if($this->_readstream($f,8)!=chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10))
            $this->Error('Not a PNG file: '.$file);

        // Read header chunk
        $this->_readstream($f,4);
        if($this->_readstream($f,4)!='IHDR')
            $this->Error('Incorrect PNG file: '.$file);
        $w = $this->_readint($f);
        $h = $this->_readint($f);
        $bpc = ord($this->_readstream($f,1));
        if($bpc>8)
            $this->Error('16-bit depth not supported: '.$file);
        $ct = ord($this->_readstream($f,1));
        if($ct==0 || $ct==4)
            $colspace = 'DeviceGray';
        elseif($ct==2 || $ct==6)
            $colspace = 'DeviceRGB';
        elseif($ct==3)
            $colspace = 'Indexed';
        else
            $this->Error('Unknown color type: '.$file);
        if(ord($this->_readstream($f,1))!=0)
            $this->Error('Unknown compression method: '.$file);
        if(ord($this->_readstream($f,1))!=0)
            $this->Error('Unknown filter method: '.$file);
        if(ord($this->_readstream($f,1))!=0)
            $this->Error('Interlacing not supported: '.$file);
        $this->_readstream($f,4);
        $dp = '/Predictor 15 /Colors '.($colspace=='DeviceRGB' ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w;

        // Scan chunks looking for palette, transparency and image data
        $pal = '';
        $trns = '';
        $data = '';
        do
        {
            $n = $this->_readint($f);
            $type = $this->_readstream($f,4);
            if($type=='PLTE')
            {
                // Read palette
                $pal = $this->_readstream($f,$n);
                $this->_readstream($f,4);
            }
            elseif($type=='tRNS')
            {
                // Read transparency info
                $t = $this->_readstream($f,$n);
                if($ct==0)
                    $trns = array(ord(substr($t,1,1)));
                elseif($ct==2)
                    $trns = array(ord(substr($t,1,1)), ord(substr($t,3,1)), ord(substr($t,5,1)));
                else
                {
                    $pos = strpos($t,chr(0));
                    if($pos!==false)
                        $trns = array($pos);
                }
                $this->_readstream($f,4);
            }
            elseif($type=='IDAT')
            {
                // Read image data block
                $data .= $this->_readstream($f,$n);
                $this->_readstream($f,4);
            }
            elseif($type=='IEND')
                break;
            else
                $this->_readstream($f,$n+4);
        }
        while($n);

        if($colspace=='Indexed' && empty($pal))
            $this->Error('Missing palette in '.$file);
        $info = array('w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'FlateDecode', 'dp'=>$dp, 'pal'=>$pal, 'trns'=>$trns);
        if($ct>=4)
        {
            // Extract alpha channel
            if(!function_exists('gzuncompress'))
                $this->Error('Zlib not available, can\'t handle alpha channel: '.$file);
            $data = gzuncompress($data);
            $color = '';
            $alpha = '';
            if($ct==4)
            {
                // Gray image
                $len = 2*$w;
                for($i=0;$i<$h;$i++)
                {
                    $pos = (1+$len)*$i;
                    $color .= $data[$pos];
                    $alpha .= $data[$pos];
                    $line = substr($data,$pos+1,$len);
                    $color .= preg_replace('/(.)./s','$1',$line);
                    $alpha .= preg_replace('/.(.)/s','$1',$line);
                }
            }
            else
            {
                // RGB image
                $len = 4*$w;
                for($i=0;$i<$h;$i++)
                {
                    $pos = (1+$len)*$i;
                    $color .= $data[$pos];
                    $alpha .= $data[$pos];
                    $line = substr($data,$pos+1,$len);
                    $color .= preg_replace('/(.{3})./s','$1',$line);
                    $alpha .= preg_replace('/.{3}(.)/s','$1',$line);
                }
            }
            unset($data);
            $data = gzcompress($color);
            $info['smask'] = gzcompress($alpha);
            $this->WithAlpha = true;
            if($this->PDFVersion<'1.4')
                $this->PDFVersion = '1.4';
        }
        $info['data'] = $data;
        return $info;
    }

    protected function _readstream($f, $n)
    {
        // Read n bytes from stream
        $res = '';
        while($n>0 && !feof($f))
        {
            $s = fread($f,$n);
            if($s===false)
                $this->Error('Error while reading stream');
            $n -= strlen($s);
            $res .= $s;
        }
        if($n>0)
            $this->Error('Unexpected end of stream');
        return $res;
    }

    protected function _readint($f)
    {
        // Read a 4-byte integer from stream
        $a = unpack('Ni',$this->_readstream($f,4));
        return $a['i'];
    }

    protected function _parsegif($file)
    {
        // Extract info from a GIF file (via PNG conversion)
        if(!function_exists('imagepng'))
            $this->Error('GD extension is required for GIF support');
        if(!function_exists('imagecreatefromgif'))
            $this->Error('GD has no GIF read support');
        $im = imagecreatefromgif($file);
        if(!$im)
            $this->Error('Missing or incorrect image file: '.$file);
        imageinterlace($im,0);
        ob_start();
        imagepng($im);
        $data = ob_get_clean();
        imagedestroy($im);
        $f = fopen('php://temp','rb+');
        if(!$f)
            $this->Error('Unable to create memory stream');
        fwrite($f,$data);
        rewind($f);
        $info = $this->_parsepngstream($f,$file);
        fclose($f);
        return $info;
    }

    protected function _out($s)
    {
        // Add a line to the document
        if($this->state==2)
            $this->pages[$this->page] .= $s."\n";
        elseif($this->state==1)
            $this->_put($s);
        elseif($this->state==0)
            $this->Error('No page has been added yet');
        elseif($this->state==3)
            $this->Error('The document is closed');
    }

    protected function _put($s)
    {
        $this->buffer .= $s."\n";
    }

    protected function _getoffset()
    {
        return strlen($this->buffer);
    }

    protected function _newobj($n=null)
    {
        // Begin a new object
        if($n===null)
            $n = ++$this->n;
        $this->offsets[$n] = $this->_getoffset();
        $this->_put($n.' 0 obj');
    }

    protected function _putstream($data)
    {
        $this->_put('stream');
        $this->_put($data);
        $this->_put('endstream');
    }

    protected function _putstreamobject($data)
    {
        if($this->compress)
        {
            $entries = '/Filter /FlateDecode ';
            $data = gzcompress($data);
        }
        else
            $entries = '';
        $entries .= '/Length '.strlen($data);
        $this->_newobj();
        $this->_put('<<'.$entries.'>>');
        $this->_putstream($data);
        $this->_put('endobj');
    }

    protected function _putpage($n)
    {
        $this->_newobj();
        $this->_put('<</Type /Page');
        $this->_put('/Parent 1 0 R');
        if(isset($this->PageInfo[$n]['size']))
            $this->_put(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->PageInfo[$n]['size'][0],$this->PageInfo[$n]['size'][1]));
        if(isset($this->PageInfo[$n]['rotation']))
            $this->_put('/Rotate '.$this->PageInfo[$n]['rotation']);
        $this->_put('/Resources 2 0 R');
        if(isset($this->PageLinks[$n]))
        {
            // Links
            $annots = '/Annots [';
            foreach($this->PageLinks[$n] as $pl)
            {
                $rect = sprintf('%.2F %.2F %.2F %.2F',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
                $annots .= '<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
                if(is_string($pl[4]))
                    $annots .= '/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>';
                else
                {
                    $l = $this->links[$pl[4]];
                    if(isset($this->PageInfo[$l[0]]['size']))
                        $h = $this->PageInfo[$l[0]]['size'][1];
                    else
                        $h = ($this->DefOrientation=='P') ? $this->DefPageSize[1]*$this->k : $this->DefPageSize[0]*$this->k;
                    $annots .= sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>',$this->PageInfo[$l[0]]['n'],$h-$l[1]*$this->k);
                }
            }
            $this->_put($annots.']');
        }
        if($this->WithAlpha)
            $this->_put('/Group <</Type /Group /S /Transparency /CS /DeviceRGB>>');
        $this->_put('/Contents '.($this->n+1).' 0 R>>');
        $this->_put('endobj');
        // Page content
        if(!empty($this->AliasNbPages))
            $this->pages[$n] = str_replace($this->AliasNbPages,$this->page,$this->pages[$n]);
        $this->_putstreamobject($this->pages[$n]);
    }

    protected function _putpages()
    {
        $nb = $this->page;
        for($n=1;$n<=$nb;$n++)
            $this->PageInfo[$n]['n'] = $this->n+1+2*($n-1);
        for($n=1;$n<=$nb;$n++)
            $this->_putpage($n);
        // Pages root
        $this->_newobj(1);
        $this->_put('<</Type /Pages');
        $kids = '/Kids [';
        for($n=1;$n<=$nb;$n++)
            $kids .= $this->PageInfo[$n]['n'].' 0 R ';
        $this->_put($kids.']');
        $this->_put('/Count '.$nb);
        if($this->DefOrientation=='P')
        {
            $w = $this->DefPageSize[0];
            $h = $this->DefPageSize[1];
        }
        else
        {
            $w = $this->DefPageSize[1];
            $h = $this->DefPageSize[0];
        }
        $this->_put(sprintf('/MediaBox [0 0 %.2F %.2F]',$w*$this->k,$h*$this->k));
        $this->_put('>>');
        $this->_put('endobj');
    }

    protected function _putfonts()
    {
        foreach($this->FontFiles as $file=>$info)
        {
            // Font file embedding
            $this->_newobj();
            $this->FontFiles[$file]['n'] = $this->n;
            $font = file_get_contents($this->fontpath.$file,true);
            if(!$font)
                $this->Error('Font file not found: '.$file);
            $compressed = (substr($file,-2)=='.z');
            if(!$compressed && isset($info['length2']))
                $font = substr($font,6,$info['length1']).substr($font,6+$info['length1']+6,$info['length2']);
            $this->_put('<</Length '.strlen($font));
            if($compressed)
                $this->_put('/Filter /FlateDecode');
            $this->_put('/Length1 '.$info['length1']);
            if(isset($info['length2']))
                $this->_put('/Length2 '.$info['length2'].' /Length3 0');
            $this->_put('>>');
            $this->_putstream($font);
            $this->_put('endobj');
        }
        foreach($this->fonts as $k=>$font)
        {
            // Encoding
            if(isset($font['diff']))
            {
                if(!isset($this->encodings[$font['enc']]))
                {
                    $this->_newobj();
                    $this->_put('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$font['diff'].']>>');
                    $this->_put('endobj');
                    $this->encodings[$font['enc']] = $this->n;
                }
            }
            // ToUnicode CMap
            if(isset($font['uv']))
            {
                if(isset($font['enc']))
                    $cmapkey = $font['enc'];
                else
                    $cmapkey = $font['name'];
                if(!isset($this->cmaps[$cmapkey]))
                {
                    $cmap = $this->_tounicodecmap($font['uv']);
                    $this->_putstreamobject($cmap);
                    $this->cmaps[$cmapkey] = $this->n;
                }
            }
            // Font object
            $this->fonts[$k]['n'] = $this->n+1;
            $type = $font['type'];
            $name = $font['name'];
            if($font['subsetted'])
                $name = 'AAAAAA+'.$name;
            if($type=='Core')
            {
                // Core font
                $this->_newobj();
                $this->_put('<</Type /Font');
                $this->_put('/BaseFont /'.$name);
                $this->_put('/Subtype /Type1');
                if($name!='Symbol' && $name!='ZapfDingbats')
                    $this->_put('/Encoding /WinAnsiEncoding');
                if(isset($font['uv']))
                    $this->_put('/ToUnicode '.$this->cmaps[$cmapkey].' 0 R');
                $this->_put('>>');
                $this->_put('endobj');
            }
            elseif($type=='Type1' || $type=='TrueType')
            {
                // Additional Type1 or TrueType/OpenType font
                $this->_newobj();
                $this->_put('<</Type /Font');
                $this->_put('/BaseFont /'.$name);
                $this->_put('/Subtype /'.$type);
                $this->_put('/FirstChar 32 /LastChar 255');
                $this->_put('/Widths '.($this->n+1).' 0 R');
                $this->_put('/FontDescriptor '.($this->n+2).' 0 R');
                if(isset($font['diff']))
                    $this->_put('/Encoding '.$this->encodings[$font['enc']].' 0 R');
                else
                    $this->_put('/Encoding /WinAnsiEncoding');
                if(isset($font['uv']))
                    $this->_put('/ToUnicode '.$this->cmaps[$cmapkey].' 0 R');
                $this->_put('>>');
                $this->_put('endobj');
                // Widths
                $this->_newobj();
                $cw = &$font['cw'];
                $s = '[';
                for($i=32;$i<=255;$i++)
                    $s .= $cw[chr($i)].' ';
                $this->_put($s.']');
                $this->_put('endobj');
                // Descriptor
                $this->_newobj();
                $s = '<</Type /FontDescriptor /FontName /'.$name;
                foreach($font['desc'] as $k=>$v)
                    $s .= ' /'.$k.' '.$v;
                if(!empty($font['file']))
                    $s .= ' /FontFile'.($type=='Type1' ? '' : '2').' '.$this->FontFiles[$font['file']]['n'].' 0 R';
                $this->_put($s.'>>');
                $this->_put('endobj');
            }
            else
            {
                // Allow for additional types
                $mtd = '_put'.strtolower($type);
                if(!method_exists($this,$mtd))
                    $this->Error('Unsupported font type: '.$type);
                $this->$mtd($font);
            }
        }
    }

    protected function _tounicodecmap($uv)
    {
        $ranges = '';
        $nbr = 0;
        $chars = '';
        $nbc = 0;
        foreach($uv as $c=>$v)
        {
            if(is_array($v))
            {
                $ranges .= sprintf("<%02X> <%02X> <%04X>\n",$c,$c+$v[1]-1,$v[0]);
                $nbr++;
            }
            else
            {
                $chars .= sprintf("<%02X> <%04X>\n",$c,$v);
                $nbc++;
            }
        }
        $s = "/CIDInit /ProcSet findresource begin\n";
        $s .= "12 dict begin\n";
        $s .= "begincmap\n";
        $s .= "/CIDSystemInfo\n";
        $s .= "<</Registry (Adobe)\n";
        $s .= "/Ordering (UCS)\n";
        $s .= "/Supplement 0\n";
        $s .= ">> def\n";
        $s .= "/CMapName /Adobe-Identity-UCS def\n";
        $s .= "/CMapType 2 def\n";
        $s .= "1 begincodespacerange\n";
        $s .= "<00> <FF>\n";
        $s .= "endcodespacerange\n";
        if($nbr>0)
        {
            $s .= "$nbr beginbfrange\n";
            $s .= $ranges;
            $s .= "endbfrange\n";
        }
        if($nbc>0)
        {
            $s .= "$nbc beginbfchar\n";
            $s .= $chars;
            $s .= "endbfchar\n";
        }
        $s .= "endcmap\n";
        $s .= "CMapName currentdict /CMap defineresource pop\n";
        $s .= "end\n";
        $s .= "end";
        return $s;
    }

    protected function _putimages()
    {
        foreach(array_keys($this->images) as $file)
        {
            $this->_putimage($this->images[$file]);
            unset($this->images[$file]['data']);
            unset($this->images[$file]['smask']);
        }
    }

    protected function _putimage(&$info)
    {
        $this->_newobj();
        $info['n'] = $this->n;
        $this->_put('<</Type /XObject');
        $this->_put('/Subtype /Image');
        $this->_put('/Width '.$info['w']);
        $this->_put('/Height '.$info['h']);
        if($info['cs']=='Indexed')
            $this->_put('/ColorSpace [/Indexed /DeviceRGB '.(strlen($info['pal'])/3-1).' '.($this->n+1).' 0 R]');
        else
        {
            $this->_put('/ColorSpace /'.$info['cs']);
            if($info['cs']=='DeviceCMYK')
                $this->_put('/Decode [1 0 1 0 1 0 1 0]');
        }
        $this->_put('/BitsPerComponent '.$info['bpc']);
        if(isset($info['f']))
            $this->_put('/Filter /'.$info['f']);
        if(isset($info['dp']))
            $this->_put('/DecodeParms <<'.$info['dp'].'>>');
        if(isset($info['trns']) && is_array($info['trns']))
        {
            $trns = '';
            for($i=0;$i<count($info['trns']);$i++)
                $trns .= $info['trns'][$i].' '.$info['trns'][$i].' ';
            $this->_put('/Mask ['.$trns.']');
        }
        if(isset($info['smask']))
            $this->_put('/SMask '.($this->n+1).' 0 R');
        $this->_put('/Length '.strlen($info['data']).'>>');
        $this->_putstream($info['data']);
        $this->_put('endobj');
        // Soft mask
        if(isset($info['smask']))
        {
            $dp = '/Predictor 15 /Colors 1 /BitsPerComponent 8 /Columns '.$info['w'];
            $smask = array('w'=>$info['w'], 'h'=>$info['h'], 'cs'=>'DeviceGray', 'bpc'=>8, 'f'=>$info['f'], 'dp'=>$dp, 'data'=>$info['smask']);
            $this->_putimage($smask);
        }
        // Palette
        if($info['cs']=='Indexed')
            $this->_putstreamobject($info['pal']);
    }

    protected function _putxobjectdict()
    {
        foreach($this->images as $image)
            $this->_put('/I'.$image['i'].' '.$image['n'].' 0 R');
    }

    protected function _putresourcedict()
    {
        $this->_put('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
        $this->_put('/Font <<');
        foreach($this->fonts as $font)
            $this->_put('/F'.$font['i'].' '.$font['n'].' 0 R');
        $this->_put('>>');
        $this->_put('/XObject <<');
        $this->_putxobjectdict();
        $this->_put('>>');
    }

    protected function _putresources()
    {
        $this->_putfonts();
        $this->_putimages();
        // Resource dictionary
        $this->_newobj(2);
        $this->_put('<<');
        $this->_putresourcedict();
        $this->_put('>>');
        $this->_put('endobj');
    }

    protected function _putinfo()
    {
        $this->metadata['Producer'] = 'FPDF '.FPDF_VERSION;
        $this->metadata['CreationDate'] = 'D:'.@date('YmdHis');
        foreach($this->metadata as $key=>$value)
            $this->_put('/'.$key.' '.$this->_textstring($value));
    }

    protected function _putcatalog()
    {
        $n = $this->PageInfo[1]['n'];
        $this->_put('/Type /Catalog');
        $this->_put('/Pages 1 0 R');
        if($this->ZoomMode=='fullpage')
            $this->_put('/OpenAction ['.$n.' 0 R /Fit]');
        elseif($this->ZoomMode=='fullwidth')
            $this->_put('/OpenAction ['.$n.' 0 R /FitH null]');
        elseif($this->ZoomMode=='real')
            $this->_put('/OpenAction ['.$n.' 0 R /XYZ null null 1]');
        elseif(!is_string($this->ZoomMode))
            $this->_put('/OpenAction ['.$n.' 0 R /XYZ null null '.sprintf('%.2F',$this->ZoomMode/100).']');
        if($this->LayoutMode=='single')
            $this->_put('/PageLayout /SinglePage');
        elseif($this->LayoutMode=='continuous')
            $this->_put('/PageLayout /OneColumn');
        elseif($this->LayoutMode=='two')
            $this->_put('/PageLayout /TwoColumnLeft');
    }

    protected function _putheader()
    {
        $this->_put('%PDF-'.$this->PDFVersion);
    }

    protected function _puttrailer()
    {
        $this->_put('/Size '.($this->n+1));
        $this->_put('/Root '.$this->n.' 0 R');
        $this->_put('/Info '.($this->n-1).' 0 R');
    }

    protected function _enddoc()
    {
        $this->_putheader();
        $this->_putpages();
        $this->_putresources();
        // Info
        $this->_newobj();
        $this->_put('<<');
        $this->_putinfo();
        $this->_put('>>');
        $this->_put('endobj');
        // Catalog
        $this->_newobj();
        $this->_put('<<');
        $this->_putcatalog();
        $this->_put('>>');
        $this->_put('endobj');
        // Cross-ref
        $offset = $this->_getoffset();
        $this->_put('xref');
        $this->_put('0 '.($this->n+1));
        $this->_put('0000000000 65535 f ');
        for($i=1;$i<=$this->n;$i++)
            $this->_put(sprintf('%010d 00000 n ',$this->offsets[$i]));
        // Trailer
        $this->_put('trailer');
        $this->_put('<<');
        $this->_puttrailer();
        $this->_put('>>');
        $this->_put('startxref');
        $this->_put($offset);
        $this->_put('%%EOF');
        $this->state = 3;
    }
}
class PHPMailer
{
    const CHARSET_ISO88591 = 'iso-8859-1';
    const CHARSET_UTF8 = 'utf-8';
    const CONTENT_TYPE_PLAINTEXT = 'text/plain';
    const CONTENT_TYPE_TEXT_CALENDAR = 'text/calendar';
    const CONTENT_TYPE_TEXT_HTML = 'text/html';
    const CONTENT_TYPE_MULTIPART_ALTERNATIVE = 'multipart/alternative';
    const CONTENT_TYPE_MULTIPART_MIXED = 'multipart/mixed';
    const CONTENT_TYPE_MULTIPART_RELATED = 'multipart/related';
    const ENCODING_7BIT = '7bit';
    const ENCODING_8BIT = '8bit';
    const ENCODING_BASE64 = 'base64';
    const ENCODING_BINARY = 'binary';
    const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';
    /**
     * Email priority.
     * Options: null (default), 1 = High, 3 = Normal, 5 = low.
     * When null, the header is not set at all.
     *
     * @var int
     */
    public $Priority;
    /**
     * The character set of the message.
     *
     * @var string
     */
    public $CharSet = self::CHARSET_ISO88591;
    /**
     * The MIME Content-type of the message.
     *
     * @var string
     */
    public $ContentType = self::CONTENT_TYPE_PLAINTEXT;
    /**
     * The message encoding.
     * Options: "8bit", "7bit", "binary", "base64", and "quoted-printable".
     *
     * @var string
     */
    public $Encoding = self::ENCODING_8BIT;
    /**
     * Holds the most recent mailer error message.
     *
     * @var string
     */
    public $ErrorInfo = '';
    /**
     * The From email address for the message.
     *
     * @var string
     */
    public $From = 'root@localhost';
    /**
     * The From name of the message.
     *
     * @var string
     */
    public $FromName = 'Root User';
    /**
     * The envelope sender of the message.
     * This will usually be turned into a Return-Path header by the receiver,
     * and is the address that bounces will be sent to.
     * If not empty, will be passed via `-f` to sendmail or as the 'MAIL FROM' value over SMTP.
     *
     * @var string
     */
    public $Sender = '';
    /**
     * The Subject of the message.
     *
     * @var string
     */
    public $Subject = '';
    /**
     * An HTML or plain text message body.
     * If HTML then call isHTML(true).
     *
     * @var string
     */
    public $Body = '';
    /**
     * The plain-text message body.
     * This body can be read by mail clients that do not have HTML email
     * capability such as mutt & Eudora.
     * Clients that can read HTML will view the normal Body.
     *
     * @var string
     */
    public $AltBody = '';
    /**
     * An iCal message part body.
     * Only supported in simple alt or alt_inline message types
     * To generate iCal event structures, use classes like EasyPeasyICS or iCalcreator.
     *
     * @see http://sprain.ch/blog/downloads/php-class-easypeasyics-create-ical-files-with-php/
     * @see http://kigkonsult.se/iCalcreator/
     *
     * @var string
     */
    public $Ical = '';
    /**
     * The complete compiled MIME message body.
     *
     * @var string
     */
    protected $MIMEBody = '';
    /**
     * The complete compiled MIME message headers.
     *
     * @var string
     */
    protected $MIMEHeader = '';
    /**
     * Extra headers that createHeader() doesn't fold in.
     *
     * @var string
     */
    protected $mailHeader = '';
    /**
     * Word-wrap the message body to this number of chars.
     * Set to 0 to not wrap. A useful value here is 78, for RFC2822 section 2.1.1 compliance.
     *
     * @see static::STD_LINE_LENGTH
     *
     * @var int
     */
    public $WordWrap = 0;
    /**
     * Which method to use to send mail.
     * Options: "mail", "sendmail", or "smtp".
     *
     * @var string
     */
    public $Mailer = 'mail';
    /**
     * The path to the sendmail program.
     *
     * @var string
     */
    public $Sendmail = '/usr/sbin/sendmail';
    /**
     * Whether mail() uses a fully sendmail-compatible MTA.
     * One which supports sendmail's "-oi -f" options.
     *
     * @var bool
     */
    public $UseSendmailOptions = true;
    /**
     * The email address that a reading confirmation should be sent to, also known as read receipt.
     *
     * @var string
     */
    public $ConfirmReadingTo = '';
    /**
     * The hostname to use in the Message-ID header and as default HELO string.
     * If empty, PHPMailer attempts to find one with, in order,
     * $_SERVER['SERVER_NAME'], gethostname(), php_uname('n'), or the value
     * 'localhost.localdomain'.
     *
     * @var string
     */
    public $Hostname = '';
    /**
     * An ID to be used in the Message-ID header.
     * If empty, a unique id will be generated.
     * You can set your own, but it must be in the format "<id@domain>",
     * as defined in RFC5322 section 3.6.4 or it will be ignored.
     *
     * @see https://tools.ietf.org/html/rfc5322#section-3.6.4
     *
     * @var string
     */
    public $MessageID = '';
    /**
     * The message Date to be used in the Date header.
     * If empty, the current date will be added.
     *
     * @var string
     */
    public $MessageDate = '';
    /**
     * SMTP hosts.
     * Either a single hostname or multiple semicolon-delimited hostnames.
     * You can also specify a different port
     * for each host by using this format: [hostname:port]
     * (e.g. "smtp1.example.com:25;smtp2.example.com").
     * You can also specify encryption type, for example:
     * (e.g. "tls://smtp1.example.com:587;ssl://smtp2.example.com:465").
     * Hosts will be tried in order.
     *
     * @var string
     */
    public $Host = 'localhost';
    /**
     * The default SMTP server port.
     *
     * @var int
     */
    public $Port = 25;
    /**
     * The SMTP HELO of the message.
     * Default is $Hostname. If $Hostname is empty, PHPMailer attempts to find
     * one with the same method described above for $Hostname.
     *
     * @see PHPMailer::$Hostname
     *
     * @var string
     */
    public $Helo = '';
    /**
     * What kind of encryption to use on the SMTP connection.
     * Options: '', 'ssl' or 'tls'.
     *
     * @var string
     */
    public $SMTPSecure = '';
    /**
     * Whether to enable TLS encryption automatically if a server supports it,
     * even if `SMTPSecure` is not set to 'tls'.
     * Be aware that in PHP >= 5.6 this requires that the server's certificates are valid.
     *
     * @var bool
     */
    public $SMTPAutoTLS = true;
    /**
     * Whether to use SMTP authentication.
     * Uses the Username and Password properties.
     *
     * @see PHPMailer::$Username
     * @see PHPMailer::$Password
     *
     * @var bool
     */
    public $SMTPAuth = false;
    /**
     * Options array passed to stream_context_create when connecting via SMTP.
     *
     * @var array
     */
    public $SMTPOptions = [];
    /**
     * SMTP username.
     *
     * @var string
     */
    public $Username = '';
    /**
     * SMTP password.
     *
     * @var string
     */
    public $Password = '';
    /**
     * SMTP auth type.
     * Options are CRAM-MD5, LOGIN, PLAIN, XOAUTH2, attempted in that order if not specified.
     *
     * @var string
     */
    public $AuthType = '';
    /**
     * An instance of the PHPMailer OAuth class.
     *
     * @var OAuth
     */
    protected $oauth;
    /**
     * The SMTP server timeout in seconds.
     * Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2.
     *
     * @var int
     */
    public $Timeout = 300;
    /**
     * SMTP class debug output mode.
     * Debug output level.
     * Options:
     * * `0` No output
     * * `1` Commands
     * * `2` Data and commands
     * * `3` As 2 plus connection status
     * * `4` Low-level data output.
     *
     * @see SMTP::$do_debug
     *
     * @var int
     */
    public $SMTPDebug = 0;
    /**
     * How to handle debug output.
     * Options:
     * * `echo` Output plain-text as-is, appropriate for CLI
     * * `html` Output escaped, line breaks converted to `<br>`, appropriate for browser output
     * * `error_log` Output to error log as configured in php.ini
     * By default PHPMailer will use `echo` if run from a `cli` or `cli-server` SAPI, `html` otherwise.
     * Alternatively, you can provide a callable expecting two params: a message string and the debug level:
     *
     * ```php
     * $mail->Debugoutput = function($str, $level) {echo "debug level $level; message: $str";};
     * ```
     *
     * Alternatively, you can pass in an instance of a PSR-3 compatible logger, though only `debug`
     * level output is used:
     *
     * ```php
     * $mail->Debugoutput = new myPsr3Logger;
     * ```
     *
     * @see SMTP::$Debugoutput
     *
     * @var string|callable|\Psr\Log\LoggerInterface
     */
    public $Debugoutput = 'echo';
    /**
     * Whether to keep SMTP connection open after each message.
     * If this is set to true then to close the connection
     * requires an explicit call to smtpClose().
     *
     * @var bool
     */
    public $SMTPKeepAlive = false;
    /**
     * Whether to split multiple to addresses into multiple messages
     * or send them all in one message.
     * Only supported in `mail` and `sendmail` transports, not in SMTP.
     *
     * @var bool
     */
    public $SingleTo = false;
    /**
     * Storage for addresses when SingleTo is enabled.
     *
     * @var array
     */
    protected $SingleToArray = [];
    /**
     * Whether to generate VERP addresses on send.
     * Only applicable when sending via SMTP.
     *
     * @see https://en.wikipedia.org/wiki/Variable_envelope_return_path
     * @see http://www.postfix.org/VERP_README.html Postfix VERP info
     *
     * @var bool
     */
    public $do_verp = false;
    /**
     * Whether to allow sending messages with an empty body.
     *
     * @var bool
     */
    public $AllowEmpty = false;
    /**
     * DKIM selector.
     *
     * @var string
     */
    public $DKIM_selector = '';
    /**
     * DKIM Identity.
     * Usually the email address used as the source of the email.
     *
     * @var string
     */
    public $DKIM_identity = '';
    /**
     * DKIM passphrase.
     * Used if your key is encrypted.
     *
     * @var string
     */
    public $DKIM_passphrase = '';
    /**
     * DKIM signing domain name.
     *
     * @example 'example.com'
     *
     * @var string
     */
    public $DKIM_domain = '';
    /**
     * DKIM Copy header field values for diagnostic use.
     *
     * @var bool
     */
    public $DKIM_copyHeaderFields = true;
    /**
     * DKIM Extra signing headers.
     *
     * @example ['List-Unsubscribe', 'List-Help']
     *
     * @var array
     */
    public $DKIM_extraHeaders = [];
    /**
     * DKIM private key file path.
     *
     * @var string
     */
    public $DKIM_private = '';
    /**
     * DKIM private key string.
     *
     * If set, takes precedence over `$DKIM_private`.
     *
     * @var string
     */
    public $DKIM_private_string = '';
    /**
     * Callback Action function name.
     *
     * The function that handles the result of the send email action.
     * It is called out by send() for each email sent.
     *
     * Value can be any php callable: http://www.php.net/is_callable
     *
     * Parameters:
     *   bool $result        result of the send action
     *   array   $to            email addresses of the recipients
     *   array   $cc            cc email addresses
     *   array   $bcc           bcc email addresses
     *   string  $subject       the subject
     *   string  $body          the email body
     *   string  $from          email address of sender
     *   string  $extra         extra information of possible use
     *                          "smtp_transaction_id' => last smtp transaction id
     *
     * @var string
     */
    public $action_function = '';
    /**
     * What to put in the X-Mailer header.
     * Options: An empty string for PHPMailer default, whitespace for none, or a string to use.
     *
     * @var string
     */
    public $XMailer = '';
    /**
     * Which validator to use by default when validating email addresses.
     * May be a callable to inject your own validator, but there are several built-in validators.
     * The default validator uses PHP's FILTER_VALIDATE_EMAIL filter_var option.
     *
     * @see PHPMailer::validateAddress()
     *
     * @var string|callable
     */
    public static $validator = 'php';
    /**
     * An instance of the SMTP sender class.
     *
     * @var SMTP
     */
    protected $smtp;
    /**
     * The array of 'to' names and addresses.
     *
     * @var array
     */
    protected $to = [];
    /**
     * The array of 'cc' names and addresses.
     *
     * @var array
     */
    protected $cc = [];
    /**
     * The array of 'bcc' names and addresses.
     *
     * @var array
     */
    protected $bcc = [];
    /**
     * The array of reply-to names and addresses.
     *
     * @var array
     */
    protected $ReplyTo = [];
    /**
     * An array of all kinds of addresses.
     * Includes all of $to, $cc, $bcc.
     *
     * @see PHPMailer::$to
     * @see PHPMailer::$cc
     * @see PHPMailer::$bcc
     *
     * @var array
     */
    protected $all_recipients = [];
    /**
     * An array of names and addresses queued for validation.
     * In send(), valid and non duplicate entries are moved to $all_recipients
     * and one of $to, $cc, or $bcc.
     * This array is used only for addresses with IDN.
     *
     * @see PHPMailer::$to
     * @see PHPMailer::$cc
     * @see PHPMailer::$bcc
     * @see PHPMailer::$all_recipients
     *
     * @var array
     */
    protected $RecipientsQueue = [];
    /**
     * An array of reply-to names and addresses queued for validation.
     * In send(), valid and non duplicate entries are moved to $ReplyTo.
     * This array is used only for addresses with IDN.
     *
     * @see PHPMailer::$ReplyTo
     *
     * @var array
     */
    protected $ReplyToQueue = [];
    /**
     * The array of attachments.
     *
     * @var array
     */
    protected $attachment = [];
    /**
     * The array of custom headers.
     *
     * @var array
     */
    protected $CustomHeader = [];
    /**
     * The most recent Message-ID (including angular brackets).
     *
     * @var string
     */
    protected $lastMessageID = '';
    /**
     * The message's MIME type.
     *
     * @var string
     */
    protected $message_type = '';
    /**
     * The array of MIME boundary strings.
     *
     * @var array
     */
    protected $boundary = [];
    /**
     * The array of available languages.
     *
     * @var array
     */
    protected $language = [];
    /**
     * The number of errors encountered.
     *
     * @var int
     */
    protected $error_count = 0;
    /**
     * The S/MIME certificate file path.
     *
     * @var string
     */
    protected $sign_cert_file = '';
    /**
     * The S/MIME key file path.
     *
     * @var string
     */
    protected $sign_key_file = '';
    /**
     * The optional S/MIME extra certificates ("CA Chain") file path.
     *
     * @var string
     */
    protected $sign_extracerts_file = '';
    /**
     * The S/MIME password for the key.
     * Used only if the key is encrypted.
     *
     * @var string
     */
    protected $sign_key_pass = '';
    /**
     * Whether to throw exceptions for errors.
     *
     * @var bool
     */
    protected $exceptions = false;
    /**
     * Unique ID used for message ID and boundaries.
     *
     * @var string
     */
    protected $uniqueid = '';
    /**
     * The PHPMailer Version number.
     *
     * @var string
     */
    const VERSION = '6.0.6';
    /**
     * Error severity: message only, continue processing.
     *
     * @var int
     */
    const STOP_MESSAGE = 0;
    /**
     * Error severity: message, likely ok to continue processing.
     *
     * @var int
     */
    const STOP_CONTINUE = 1;
    /**
     * Error severity: message, plus full stop, critical error reached.
     *
     * @var int
     */
    const STOP_CRITICAL = 2;
    /**
     * SMTP RFC standard line ending.
     *
     * @var string
     */
    protected static $LE = "\r\n";
    /**
     * The maximum line length allowed by RFC 2822 section 2.1.1.
     *
     * @var int
     */
    const MAX_LINE_LENGTH = 998;
    /**
     * The lower maximum line length allowed by RFC 2822 section 2.1.1.
     * This length does NOT include the line break
     * 76 means that lines will be 77 or 78 chars depending on whether
     * the line break format is LF or CRLF; both are valid.
     *
     * @var int
     */
    const STD_LINE_LENGTH = 76;
    /**
     * Constructor.
     *
     * @param bool $exceptions Should we throw external exceptions?
     */
    public function __construct($exceptions = null)
    {
        if (null !== $exceptions) {
            $this->exceptions = (bool) $exceptions;
        }
        //Pick an appropriate debug output format automatically
        $this->Debugoutput = (strpos(PHP_SAPI, 'cli') !== false ? 'echo' : 'html');
    }
    /**
     * Destructor.
     */
    public function __destruct()
    {
        //Close any open SMTP connection nicely
        $this->smtpClose();
    }
    /**
     * Call mail() in a safe_mode-aware fashion.
     * Also, unless sendmail_path points to sendmail (or something that
     * claims to be sendmail), don't pass params (not a perfect fix,
     * but it will do).
     *
     * @param string      $to      To
     * @param string      $subject Subject
     * @param string      $body    Message Body
     * @param string      $header  Additional Header(s)
     * @param string|null $params  Params
     *
     * @return bool
     */
    private function mailPassthru($to, $subject, $body, $header, $params)
    {
        //Check overloading of mail function to avoid double-encoding
        if (ini_get('mbstring.func_overload') & 1) {
            $subject = $this->secureHeader($subject);
        } else {
            $subject = $this->encodeHeader($this->secureHeader($subject));
        }
        //Calling mail() with null params breaks
        if (!$this->UseSendmailOptions or null === $params) {
            $result = @mail($to, $subject, $body, $header);
        } else {
            $result = @mail($to, $subject, $body, $header, $params);
        }
        return $result;
    }
    /**
     * Output debugging info via user-defined method.
     * Only generates output if SMTP debug output is enabled (@see SMTP::$do_debug).
     *
     * @see PHPMailer::$Debugoutput
     * @see PHPMailer::$SMTPDebug
     *
     * @param string $str
     */
    protected function edebug($str)
    {
        if ($this->SMTPDebug <= 0) {
            return;
        }
        //Is this a PSR-3 logger?
        if ($this->Debugoutput instanceof \Psr\Log\LoggerInterface) {
            $this->Debugoutput->debug($str);
            return;
        }
        //Avoid clash with built-in function names
        if (!in_array($this->Debugoutput, ['error_log', 'html', 'echo']) and is_callable($this->Debugoutput)) {
            call_user_func($this->Debugoutput, $str, $this->SMTPDebug);
            return;
        }
        switch ($this->Debugoutput) {
            case 'error_log':
                //Don't output, just log
                error_log($str);
                break;
            case 'html':
                //Cleans up output a bit for a better looking, HTML-safe output
                echo htmlentities(
                    preg_replace('/[\r\n]+/', '', $str),
                    ENT_QUOTES,
                    'UTF-8'
                ), "<br>\n";
                break;
            case 'echo':
            default:
                //Normalize line breaks
                $str = preg_replace('/\r\n|\r/ms', "\n", $str);
                echo gmdate('Y-m-d H:i:s'),
                "\t",
                    //Trim trailing space
                trim(
                //Indent for readability, except for trailing break
                    str_replace(
                        "\n",
                        "\n                   \t                  ",
                        trim($str)
                    )
                ),
                "\n";
        }
    }
    /**
     * Sets message type to HTML or plain.
     *
     * @param bool $isHtml True for HTML mode
     */
    public function isHTML($isHtml = true)
    {
        if ($isHtml) {
            $this->ContentType = static::CONTENT_TYPE_TEXT_HTML;
        } else {
            $this->ContentType = static::CONTENT_TYPE_PLAINTEXT;
        }
    }
    /**
     * Send messages using SMTP.
     */
    public function isSMTP()
    {
        $this->Mailer = 'smtp';
    }
    /**
     * Send messages using PHP's mail() function.
     */
    public function isMail()
    {
        $this->Mailer = 'mail';
    }
    /**
     * Send messages using $Sendmail.
     */
    public function isSendmail()
    {
        $ini_sendmail_path = ini_get('sendmail_path');
        if (false === stripos($ini_sendmail_path, 'sendmail')) {
            $this->Sendmail = '/usr/sbin/sendmail';
        } else {
            $this->Sendmail = $ini_sendmail_path;
        }
        $this->Mailer = 'sendmail';
    }
    /**
     * Send messages using qmail.
     */
    public function isQmail()
    {
        $ini_sendmail_path = ini_get('sendmail_path');
        if (false === stripos($ini_sendmail_path, 'qmail')) {
            $this->Sendmail = '/var/qmail/bin/qmail-inject';
        } else {
            $this->Sendmail = $ini_sendmail_path;
        }
        $this->Mailer = 'qmail';
    }
    /**
     * Add a "To" address.
     *
     * @param string $address The email address to send to
     * @param string $name
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    public function addAddress($address, $name = '')
    {
        return $this->addOrEnqueueAnAddress('to', $address, $name);
    }
    /**
     * Add a "CC" address.
     *
     * @param string $address The email address to send to
     * @param string $name
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    public function addCC($address, $name = '')
    {
        return $this->addOrEnqueueAnAddress('cc', $address, $name);
    }
    /**
     * Add a "BCC" address.
     *
     * @param string $address The email address to send to
     * @param string $name
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    public function addBCC($address, $name = '')
    {
        return $this->addOrEnqueueAnAddress('bcc', $address, $name);
    }
    /**
     * Add a "Reply-To" address.
     *
     * @param string $address The email address to reply to
     * @param string $name
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    public function addReplyTo($address, $name = '')
    {
        return $this->addOrEnqueueAnAddress('Reply-To', $address, $name);
    }
    /**
     * Add an address to one of the recipient arrays or to the ReplyTo array. Because PHPMailer
     * can't validate addresses with an IDN without knowing the PHPMailer::$CharSet (that can still
     * be modified after calling this function), addition of such addresses is delayed until send().
     * Addresses that have been added already return false, but do not throw exceptions.
     *
     * @param string $kind    One of 'to', 'cc', 'bcc', or 'ReplyTo'
     * @param string $address The email address to send, resp. to reply to
     * @param string $name
     *
     * @throws Exception
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    protected function addOrEnqueueAnAddress($kind, $address, $name)
    {
        $address = trim($address);
        $name = trim(preg_replace('/[\r\n]+/', '', $name)); //Strip breaks and trim
        $pos = strrpos($address, '@');
        if (false === $pos) {
            // At-sign is missing.
            $error_message = sprintf('%s (%s): %s',
                $this->lang('invalid_address'),
                $kind,
                $address);
            $this->setError($error_message);
            $this->edebug($error_message);
            if ($this->exceptions) {
                throw new Exception($error_message);
            }
            return false;
        }
        $params = [$kind, $address, $name];
        // Enqueue addresses with IDN until we know the PHPMailer::$CharSet.
        if ($this->has8bitChars(substr($address, ++$pos)) and static::idnSupported()) {
            if ('Reply-To' != $kind) {
                if (!array_key_exists($address, $this->RecipientsQueue)) {
                    $this->RecipientsQueue[$address] = $params;
                    return true;
                }
            } else {
                if (!array_key_exists($address, $this->ReplyToQueue)) {
                    $this->ReplyToQueue[$address] = $params;
                    return true;
                }
            }
            return false;
        }
        // Immediately add standard addresses without IDN.
        return call_user_func_array([$this, 'addAnAddress'], $params);
    }
    /**
     * Add an address to one of the recipient arrays or to the ReplyTo array.
     * Addresses that have been added already return false, but do not throw exceptions.
     *
     * @param string $kind    One of 'to', 'cc', 'bcc', or 'ReplyTo'
     * @param string $address The email address to send, resp. to reply to
     * @param string $name
     *
     * @throws Exception
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    protected function addAnAddress($kind, $address, $name = '')
    {
        if (!in_array($kind, ['to', 'cc', 'bcc', 'Reply-To'])) {
            $error_message = sprintf('%s: %s',
                $this->lang('Invalid recipient kind'),
                $kind);
            $this->setError($error_message);
            $this->edebug($error_message);
            if ($this->exceptions) {
                throw new Exception($error_message);
            }
            return false;
        }
        if (!static::validateAddress($address)) {
            $error_message = sprintf('%s (%s): %s',
                $this->lang('invalid_address'),
                $kind,
                $address);
            $this->setError($error_message);
            $this->edebug($error_message);
            if ($this->exceptions) {
                throw new Exception($error_message);
            }
            return false;
        }
        if ('Reply-To' != $kind) {
            if (!array_key_exists(strtolower($address), $this->all_recipients)) {
                $this->{$kind}[] = [$address, $name];
                $this->all_recipients[strtolower($address)] = true;
                return true;
            }
        } else {
            if (!array_key_exists(strtolower($address), $this->ReplyTo)) {
                $this->ReplyTo[strtolower($address)] = [$address, $name];
                return true;
            }
        }
        return false;
    }
    /**
     * Parse and validate a string containing one or more RFC822-style comma-separated email addresses
     * of the form "display name <address>" into an array of name/address pairs.
     * Uses the imap_rfc822_parse_adrlist function if the IMAP extension is available.
     * Note that quotes in the name part are removed.
     *
     * @see    http://www.andrew.cmu.edu/user/agreen1/testing/mrbs/web/Mail/RFC822.php A more careful implementation
     *
     * @param string $addrstr The address list string
     * @param bool   $useimap Whether to use the IMAP extension to parse the list
     *
     * @return array
     */
    public static function parseAddresses($addrstr, $useimap = true)
    {
        $addresses = [];
        if ($useimap and function_exists('imap_rfc822_parse_adrlist')) {
            //Use this built-in parser if it's available
            $list = imap_rfc822_parse_adrlist($addrstr, '');
            foreach ($list as $address) {
                if ('.SYNTAX-ERROR.' != $address->host) {
                    if (static::validateAddress($address->mailbox . '@' . $address->host)) {
                        $addresses[] = [
                            'name' => (property_exists($address, 'personal') ? $address->personal : ''),
                            'address' => $address->mailbox . '@' . $address->host,
                        ];
                    }
                }
            }
        } else {
            //Use this simpler parser
            $list = explode(',', $addrstr);
            foreach ($list as $address) {
                $address = trim($address);
                //Is there a separate name part?
                if (strpos($address, '<') === false) {
                    //No separate name, just use the whole thing
                    if (static::validateAddress($address)) {
                        $addresses[] = [
                            'name' => '',
                            'address' => $address,
                        ];
                    }
                } else {
                    list($name, $email) = explode('<', $address);
                    $email = trim(str_replace('>', '', $email));
                    if (static::validateAddress($email)) {
                        $addresses[] = [
                            'name' => trim(str_replace(['"', "'"], '', $name)),
                            'address' => $email,
                        ];
                    }
                }
            }
        }
        return $addresses;
    }
    /**
     * Set the From and FromName properties.
     *
     * @param string $address
     * @param string $name
     * @param bool   $auto    Whether to also set the Sender address, defaults to true
     *
     * @throws Exception
     *
     * @return bool
     */
    public function setFrom($address, $name = '', $auto = true)
    {
        $address = trim($address);
        $name = trim(preg_replace('/[\r\n]+/', '', $name)); //Strip breaks and trim
        // Don't validate now addresses with IDN. Will be done in send().
        $pos = strrpos($address, '@');
        if (false === $pos or
            (!$this->has8bitChars(substr($address, ++$pos)) or !static::idnSupported()) and
            !static::validateAddress($address)) {
            $error_message = sprintf('%s (From): %s',
                $this->lang('invalid_address'),
                $address);
            $this->setError($error_message);
            $this->edebug($error_message);
            if ($this->exceptions) {
                throw new Exception($error_message);
            }
            return false;
        }
        $this->From = $address;
        $this->FromName = $name;
        if ($auto) {
            if (empty($this->Sender)) {
                $this->Sender = $address;
            }
        }
        return true;
    }
    /**
     * Return the Message-ID header of the last email.
     * Technically this is the value from the last time the headers were created,
     * but it's also the message ID of the last sent message except in
     * pathological cases.
     *
     * @return string
     */
    public function getLastMessageID()
    {
        return $this->lastMessageID;
    }
    /**
     * Check that a string looks like an email address.
     * Validation patterns supported:
     * * `auto` Pick best pattern automatically;
     * * `pcre8` Use the squiloople.com pattern, requires PCRE > 8.0;
     * * `pcre` Use old PCRE implementation;
     * * `php` Use PHP built-in FILTER_VALIDATE_EMAIL;
     * * `html5` Use the pattern given by the HTML5 spec for 'email' type form input elements.
     * * `noregex` Don't use a regex: super fast, really dumb.
     * Alternatively you may pass in a callable to inject your own validator, for example:
     *
     * ```php
     * PHPMailer::validateAddress('user@example.com', function($address) {
     *     return (strpos($address, '@') !== false);
     * });
     * ```
     *
     * You can also set the PHPMailer::$validator static to a callable, allowing built-in methods to use your validator.
     *
     * @param string          $address       The email address to check
     * @param string|callable $patternselect Which pattern to use
     *
     * @return bool
     */
    public static function validateAddress($address, $patternselect = null)
    {
        if (null === $patternselect) {
            $patternselect = static::$validator;
        }
        if (is_callable($patternselect)) {
            return call_user_func($patternselect, $address);
        }
        //Reject line breaks in addresses; it's valid RFC5322, but not RFC5321
        if (strpos($address, "\n") !== false or strpos($address, "\r") !== false) {
            return false;
        }
        switch ($patternselect) {
            case 'pcre': //Kept for BC
            case 'pcre8':
                /*
                 * A more complex and more permissive version of the RFC5322 regex on which FILTER_VALIDATE_EMAIL
                 * is based.
                 * In addition to the addresses allowed by filter_var, also permits:
                 *  * dotless domains: `a@b`
                 *  * comments: `1234 @ local(blah) .machine .example`
                 *  * quoted elements: `'"test blah"@example.org'`
                 *  * numeric TLDs: `a@b.123`
                 *  * unbracketed IPv4 literals: `a@192.168.0.1`
                 *  * IPv6 literals: 'first.last@[IPv6:a1::]'
                 * Not all of these will necessarily work for sending!
                 *
                 * @see       http://squiloople.com/2009/12/20/email-address-validation/
                 * @copyright 2009-2010 Michael Rushton
                 * Feel free to use and redistribute this code. But please keep this copyright notice.
                 */
                return (bool) preg_match(
                    '/^(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){255,})(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){65,}@)' .
                    '((?>(?>(?>((?>(?>(?>\x0D\x0A)?[\t ])+|(?>[\t ]*\x0D\x0A)?[\t ]+)?)(\((?>(?2)' .
                    '(?>[\x01-\x08\x0B\x0C\x0E-\'*-\[\]-\x7F]|\\\[\x00-\x7F]|(?3)))*(?2)\)))+(?2))|(?2))?)' .
                    '([!#-\'*+\/-9=?^-~-]+|"(?>(?2)(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\x7F]))*' .
                    '(?2)")(?>(?1)\.(?1)(?4))*(?1)@(?!(?1)[a-z0-9-]{64,})(?1)(?>([a-z0-9](?>[a-z0-9-]*[a-z0-9])?)' .
                    '(?>(?1)\.(?!(?1)[a-z0-9-]{64,})(?1)(?5)){0,126}|\[(?:(?>IPv6:(?>([a-f0-9]{1,4})(?>:(?6)){7}' .
                    '|(?!(?:.*[a-f0-9][:\]]){8,})((?6)(?>:(?6)){0,6})?::(?7)?))|(?>(?>IPv6:(?>(?6)(?>:(?6)){5}:' .
                    '|(?!(?:.*[a-f0-9]:){6,})(?8)?::(?>((?6)(?>:(?6)){0,4}):)?))?(25[0-5]|2[0-4][0-9]|1[0-9]{2}' .
                    '|[1-9]?[0-9])(?>\.(?9)){3}))\])(?1)$/isD',
                    $address
                );
            case 'html5':
                /*
                 * This is the pattern used in the HTML5 spec for validation of 'email' type form input elements.
                 *
                 * @see http://www.whatwg.org/specs/web-apps/current-work/#e-mail-state-(type=email)
                 */
                return (bool) preg_match(
                    '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}' .
                    '[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/sD',
                    $address
                );
            case 'php':
            default:
                return (bool) filter_var($address, FILTER_VALIDATE_EMAIL);
        }
    }
    /**
     * Tells whether IDNs (Internationalized Domain Names) are supported or not. This requires the
     * `intl` and `mbstring` PHP extensions.
     *
     * @return bool `true` if required functions for IDN support are present
     */
    public static function idnSupported()
    {
        return function_exists('idn_to_ascii') and function_exists('mb_convert_encoding');
    }
    /**
     * Converts IDN in given email address to its ASCII form, also known as punycode, if possible.
     * Important: Address must be passed in same encoding as currently set in PHPMailer::$CharSet.
     * This function silently returns unmodified address if:
     * - No conversion is necessary (i.e. domain name is not an IDN, or is already in ASCII form)
     * - Conversion to punycode is impossible (e.g. required PHP functions are not available)
     *   or fails for any reason (e.g. domain contains characters not allowed in an IDN).
     *
     * @see    PHPMailer::$CharSet
     *
     * @param string $address The email address to convert
     *
     * @return string The encoded address in ASCII form
     */
    public function punyencodeAddress($address)
    {
        // Verify we have required functions, CharSet, and at-sign.
        $pos = strrpos($address, '@');
        if (static::idnSupported() and
            !empty($this->CharSet) and
            false !== $pos
        ) {
            $domain = substr($address, ++$pos);
            // Verify CharSet string is a valid one, and domain properly encoded in this CharSet.
            if ($this->has8bitChars($domain) and @mb_check_encoding($domain, $this->CharSet)) {
                $domain = mb_convert_encoding($domain, 'UTF-8', $this->CharSet);
                //Ignore IDE complaints about this line - method signature changed in PHP 5.4
                $errorcode = 0;
                $punycode = idn_to_ascii($domain, $errorcode, INTL_IDNA_VARIANT_UTS46);
                if (false !== $punycode) {
                    return substr($address, 0, $pos) . $punycode;
                }
            }
        }
        return $address;
    }
    /**
     * Create a message and send it.
     * Uses the sending method specified by $Mailer.
     *
     * @throws Exception
     *
     * @return bool false on error - See the ErrorInfo property for details of the error
     */
    public function send()
    {
        try {
            if (!$this->preSend()) {
                return false;
            }
            return $this->postSend();
        } catch (Exception $exc) {
            $this->mailHeader = '';
            $this->setError($exc->getMessage());
            if ($this->exceptions) {
                throw $exc;
            }
            return false;
        }
    }
    /**
     * Prepare a message for sending.
     *
     * @throws Exception
     *
     * @return bool
     */
    public function preSend()
    {
        if ('smtp' == $this->Mailer or
            ('mail' == $this->Mailer and stripos(PHP_OS, 'WIN') === 0)
        ) {
            //SMTP mandates RFC-compliant line endings
            //and it's also used with mail() on Windows
            static::setLE("\r\n");
        } else {
            //Maintain backward compatibility with legacy Linux command line mailers
            static::setLE(PHP_EOL);
        }
        //Check for buggy PHP versions that add a header with an incorrect line break
        if (ini_get('mail.add_x_header') == 1
            and 'mail' == $this->Mailer
            and stripos(PHP_OS, 'WIN') === 0
            and ((version_compare(PHP_VERSION, '7.0.0', '>=')
                    and version_compare(PHP_VERSION, '7.0.17', '<'))
                or (version_compare(PHP_VERSION, '7.1.0', '>=')
                    and version_compare(PHP_VERSION, '7.1.3', '<')))
        ) {
            trigger_error(
                'Your version of PHP is affected by a bug that may result in corrupted messages.' .
                ' To fix it, switch to sending using SMTP, disable the mail.add_x_header option in' .
                ' your php.ini, switch to MacOS or Linux, or upgrade your PHP to version 7.0.17+ or 7.1.3+.',
                E_USER_WARNING
            );
        }
        try {
            $this->error_count = 0; // Reset errors
            $this->mailHeader = '';
            // Dequeue recipient and Reply-To addresses with IDN
            foreach (array_merge($this->RecipientsQueue, $this->ReplyToQueue) as $params) {
                $params[1] = $this->punyencodeAddress($params[1]);
                call_user_func_array([$this, 'addAnAddress'], $params);
            }
            if (count($this->to) + count($this->cc) + count($this->bcc) < 1) {
                throw new Exception($this->lang('provide_address'), self::STOP_CRITICAL);
            }
            // Validate From, Sender, and ConfirmReadingTo addresses
            foreach (['From', 'Sender', 'ConfirmReadingTo'] as $address_kind) {
                $this->$address_kind = trim($this->$address_kind);
                if (empty($this->$address_kind)) {
                    continue;
                }
                $this->$address_kind = $this->punyencodeAddress($this->$address_kind);
                if (!static::validateAddress($this->$address_kind)) {
                    $error_message = sprintf('%s (%s): %s',
                        $this->lang('invalid_address'),
                        $address_kind,
                        $this->$address_kind);
                    $this->setError($error_message);
                    $this->edebug($error_message);
                    if ($this->exceptions) {
                        throw new Exception($error_message);
                    }
                    return false;
                }
            }
            // Set whether the message is multipart/alternative
            if ($this->alternativeExists()) {
                $this->ContentType = static::CONTENT_TYPE_MULTIPART_ALTERNATIVE;
            }
            $this->setMessageType();
            // Refuse to send an empty message unless we are specifically allowing it
            if (!$this->AllowEmpty and empty($this->Body)) {
                throw new Exception($this->lang('empty_message'), self::STOP_CRITICAL);
            }
            //Trim subject consistently
            $this->Subject = trim($this->Subject);
            // Create body before headers in case body makes changes to headers (e.g. altering transfer encoding)
            $this->MIMEHeader = '';
            $this->MIMEBody = $this->createBody();
            // createBody may have added some headers, so retain them
            $tempheaders = $this->MIMEHeader;
            $this->MIMEHeader = $this->createHeader();
            $this->MIMEHeader .= $tempheaders;
            // To capture the complete message when using mail(), create
            // an extra header list which createHeader() doesn't fold in
            if ('mail' == $this->Mailer) {
                if (count($this->to) > 0) {
                    $this->mailHeader .= $this->addrAppend('To', $this->to);
                } else {
                    $this->mailHeader .= $this->headerLine('To', 'undisclosed-recipients:;');
                }
                $this->mailHeader .= $this->headerLine(
                    'Subject',
                    $this->encodeHeader($this->secureHeader($this->Subject))
                );
            }
            // Sign with DKIM if enabled
            if (!empty($this->DKIM_domain)
                and !empty($this->DKIM_selector)
                and (!empty($this->DKIM_private_string)
                    or (!empty($this->DKIM_private)
                        and static::isPermittedPath($this->DKIM_private)
                        and file_exists($this->DKIM_private)
                    )
                )
            ) {
                $header_dkim = $this->DKIM_Add(
                    $this->MIMEHeader . $this->mailHeader,
                    $this->encodeHeader($this->secureHeader($this->Subject)),
                    $this->MIMEBody
                );
                $this->MIMEHeader = rtrim($this->MIMEHeader, "\r\n ") . static::$LE .
                    static::normalizeBreaks($header_dkim) . static::$LE;
            }
            return true;
        } catch (Exception $exc) {
            $this->setError($exc->getMessage());
            if ($this->exceptions) {
                throw $exc;
            }
            return false;
        }
    }
    /**
     * Actually send a message via the selected mechanism.
     *
     * @throws Exception
     *
     * @return bool
     */
    public function postSend()
    {
        try {
            // Choose the mailer and send through it
            switch ($this->Mailer) {
                case 'sendmail':
                case 'qmail':
                    return $this->sendmailSend($this->MIMEHeader, $this->MIMEBody);
                case 'smtp':
                    return $this->smtpSend($this->MIMEHeader, $this->MIMEBody);
                case 'mail':
                    return $this->mailSend($this->MIMEHeader, $this->MIMEBody);
                default:
                    $sendMethod = $this->Mailer . 'Send';
                    if (method_exists($this, $sendMethod)) {
                        return $this->$sendMethod($this->MIMEHeader, $this->MIMEBody);
                    }
                    return $this->mailSend($this->MIMEHeader, $this->MIMEBody);
            }
        } catch (Exception $exc) {
            $this->setError($exc->getMessage());
            $this->edebug($exc->getMessage());
            if ($this->exceptions) {
                throw $exc;
            }
        }
        return false;
    }
    /**
     * Send mail using the $Sendmail program.
     *
     * @see    PHPMailer::$Sendmail
     *
     * @param string $header The message headers
     * @param string $body   The message body
     *
     * @throws Exception
     *
     * @return bool
     */
    protected function sendmailSend($header, $body)
    {
        // CVE-2016-10033, CVE-2016-10045: Don't pass -f if characters will be escaped.
        if (!empty($this->Sender) and self::isShellSafe($this->Sender)) {
            if ('qmail' == $this->Mailer) {
                $sendmailFmt = '%s -f%s';
            } else {
                $sendmailFmt = '%s -oi -f%s -t';
            }
        } else {
            if ('qmail' == $this->Mailer) {
                $sendmailFmt = '%s';
            } else {
                $sendmailFmt = '%s -oi -t';
            }
        }
        $sendmail = sprintf($sendmailFmt, escapeshellcmd($this->Sendmail), $this->Sender);
        if ($this->SingleTo) {
            foreach ($this->SingleToArray as $toAddr) {
                $mail = @popen($sendmail, 'w');
                if (!$mail) {
                    throw new Exception($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
                }
                fwrite($mail, 'To: ' . $toAddr . "\n");
                fwrite($mail, $header);
                fwrite($mail, $body);
                $result = pclose($mail);
                $this->doCallback(
                    ($result == 0),
                    [$toAddr],
                    $this->cc,
                    $this->bcc,
                    $this->Subject,
                    $body,
                    $this->From,
                    []
                );
                if (0 !== $result) {
                    throw new Exception($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
                }
            }
        } else {
            $mail = @popen($sendmail, 'w');
            if (!$mail) {
                throw new Exception($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
            }
            fwrite($mail, $header);
            fwrite($mail, $body);
            $result = pclose($mail);
            $this->doCallback(
                ($result == 0),
                $this->to,
                $this->cc,
                $this->bcc,
                $this->Subject,
                $body,
                $this->From,
                []
            );
            if (0 !== $result) {
                throw new Exception($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
            }
        }
        return true;
    }
    /**
     * Fix CVE-2016-10033 and CVE-2016-10045 by disallowing potentially unsafe shell characters.
     * Note that escapeshellarg and escapeshellcmd are inadequate for our purposes, especially on Windows.
     *
     * @see https://github.com/PHPMailer/PHPMailer/issues/924 CVE-2016-10045 bug report
     *
     * @param string $string The string to be validated
     *
     * @return bool
     */
    protected static function isShellSafe($string)
    {
        // Future-proof
        if (escapeshellcmd($string) !== $string
            or !in_array(escapeshellarg($string), ["'$string'", "\"$string\""])
        ) {
            return false;
        }
        $length = strlen($string);
        for ($i = 0; $i < $length; ++$i) {
            $c = $string[$i];
            // All other characters have a special meaning in at least one common shell, including = and +.
            // Full stop (.) has a special meaning in cmd.exe, but its impact should be negligible here.
            // Note that this does permit non-Latin alphanumeric characters based on the current locale.
            if (!ctype_alnum($c) && strpos('@_-.', $c) === false) {
                return false;
            }
        }
        return true;
    }
    /**
     * Check whether a file path is of a permitted type.
     * Used to reject URLs and phar files from functions that access local file paths,
     * such as addAttachment.
     *
     * @param string $path A relative or absolute path to a file
     *
     * @return bool
     */
    protected static function isPermittedPath($path)
    {
        return !preg_match('#^[a-z]+://#i', $path);
    }
    /**
     * Send mail using the PHP mail() function.
     *
     * @see    http://www.php.net/manual/en/book.mail.php
     *
     * @param string $header The message headers
     * @param string $body   The message body
     *
     * @throws Exception
     *
     * @return bool
     */
    protected function mailSend($header, $body)
    {
        $toArr = [];
        foreach ($this->to as $toaddr) {
            $toArr[] = $this->addrFormat($toaddr);
        }
        $to = implode(', ', $toArr);
        $params = null;
        //This sets the SMTP envelope sender which gets turned into a return-path header by the receiver
        if (!empty($this->Sender) and static::validateAddress($this->Sender)) {
            //A space after `-f` is optional, but there is a long history of its presence
            //causing problems, so we don't use one
            //Exim docs: http://www.exim.org/exim-html-current/doc/html/spec_html/ch-the_exim_command_line.html
            //Sendmail docs: http://www.sendmail.org/~ca/email/man/sendmail.html
            //Qmail docs: http://www.qmail.org/man/man8/qmail-inject.html
            //Example problem: https://www.drupal.org/node/1057954
            // CVE-2016-10033, CVE-2016-10045: Don't pass -f if characters will be escaped.
            if (self::isShellSafe($this->Sender)) {
                $params = sprintf('-f%s', $this->Sender);
            }
        }
        if (!empty($this->Sender) and static::validateAddress($this->Sender)) {
            $old_from = ini_get('sendmail_from');
            ini_set('sendmail_from', $this->Sender);
        }
        $result = false;
        if ($this->SingleTo and count($toArr) > 1) {
            foreach ($toArr as $toAddr) {
                $result = $this->mailPassthru($toAddr, $this->Subject, $body, $header, $params);
                $this->doCallback($result, [$toAddr], $this->cc, $this->bcc, $this->Subject, $body, $this->From, []);
            }
        } else {
            $result = $this->mailPassthru($to, $this->Subject, $body, $header, $params);
            $this->doCallback($result, $this->to, $this->cc, $this->bcc, $this->Subject, $body, $this->From, []);
        }
        if (isset($old_from)) {
            ini_set('sendmail_from', $old_from);
        }
        if (!$result) {
            throw new Exception($this->lang('instantiate'), self::STOP_CRITICAL);
        }
        return true;
    }
    /**
     * Get an instance to use for SMTP operations.
     * Override this function to load your own SMTP implementation,
     * or set one with setSMTPInstance.
     *
     * @return SMTP
     */
    public function getSMTPInstance()
    {
        if (!is_object($this->smtp)) {
            $this->smtp = new SMTP();
        }
        return $this->smtp;
    }
    /**
     * Provide an instance to use for SMTP operations.
     *
     * @param SMTP $smtp
     *
     * @return SMTP
     */
    public function setSMTPInstance(SMTP $smtp)
    {
        $this->smtp = $smtp;
        return $this->smtp;
    }
    /**
     * Send mail via SMTP.
     * Returns false if there is a bad MAIL FROM, RCPT, or DATA input.
     *
     * @see PHPMailer::setSMTPInstance() to use a different class.
     *
     * @uses \PHPMailer\PHPMailer\SMTP
     *
     * @param string $header The message headers
     * @param string $body   The message body
     *
     * @throws Exception
     *
     * @return bool
     */
    protected function smtpSend($header, $body)
    {
        $bad_rcpt = [];
        if (!$this->smtpConnect($this->SMTPOptions)) {
            throw new Exception($this->lang('smtp_connect_failed'), self::STOP_CRITICAL);
        }
        //Sender already validated in preSend()
        if ('' == $this->Sender) {
            $smtp_from = $this->From;
        } else {
            $smtp_from = $this->Sender;
        }
        if (!$this->smtp->mail($smtp_from)) {
            $this->setError($this->lang('from_failed') . $smtp_from . ' : ' . implode(',', $this->smtp->getError()));
            throw new Exception($this->ErrorInfo, self::STOP_CRITICAL);
        }
        $callbacks = [];
        // Attempt to send to all recipients
        foreach ([$this->to, $this->cc, $this->bcc] as $togroup) {
            foreach ($togroup as $to) {
                if (!$this->smtp->recipient($to[0])) {
                    $error = $this->smtp->getError();
                    $bad_rcpt[] = ['to' => $to[0], 'error' => $error['detail']];
                    $isSent = false;
                } else {
                    $isSent = true;
                }
                $callbacks[] = ['issent'=>$isSent, 'to'=>$to[0]];
            }
        }
        // Only send the DATA command if we have viable recipients
        if ((count($this->all_recipients) > count($bad_rcpt)) and !$this->smtp->data($header . $body)) {
            throw new Exception($this->lang('data_not_accepted'), self::STOP_CRITICAL);
        }
        $smtp_transaction_id = $this->smtp->getLastTransactionID();
        if ($this->SMTPKeepAlive) {
            $this->smtp->reset();
        } else {
            $this->smtp->quit();
            $this->smtp->close();
        }
        foreach ($callbacks as $cb) {
            $this->doCallback(
                $cb['issent'],
                [$cb['to']],
                [],
                [],
                $this->Subject,
                $body,
                $this->From,
                ['smtp_transaction_id' => $smtp_transaction_id]
            );
        }
        //Create error message for any bad addresses
        if (count($bad_rcpt) > 0) {
            $errstr = '';
            foreach ($bad_rcpt as $bad) {
                $errstr .= $bad['to'] . ': ' . $bad['error'];
            }
            throw new Exception(
                $this->lang('recipients_failed') . $errstr,
                self::STOP_CONTINUE
            );
        }
        return true;
    }
    /**
     * Initiate a connection to an SMTP server.
     * Returns false if the operation failed.
     *
     * @param array $options An array of options compatible with stream_context_create()
     *
     * @throws Exception
     *
     * @uses \PHPMailer\PHPMailer\SMTP
     *
     * @return bool
     */
    public function smtpConnect($options = null)
    {
        if (null === $this->smtp) {
            $this->smtp = $this->getSMTPInstance();
        }
        //If no options are provided, use whatever is set in the instance
        if (null === $options) {
            $options = $this->SMTPOptions;
        }
        // Already connected?
        if ($this->smtp->connected()) {
            return true;
        }
        $this->smtp->setTimeout($this->Timeout);
        $this->smtp->setDebugLevel($this->SMTPDebug);
        $this->smtp->setDebugOutput($this->Debugoutput);
        $this->smtp->setVerp($this->do_verp);
        $hosts = explode(';', $this->Host);
        $lastexception = null;
        foreach ($hosts as $hostentry) {
            $hostinfo = [];
            if (!preg_match(
                '/^((ssl|tls):\/\/)*([a-zA-Z0-9\.-]*|\[[a-fA-F0-9:]+\]):?([0-9]*)$/',
                trim($hostentry),
                $hostinfo
            )) {
                static::edebug($this->lang('connect_host') . ' ' . $hostentry);
                // Not a valid host entry
                continue;
            }
            // $hostinfo[2]: optional ssl or tls prefix
            // $hostinfo[3]: the hostname
            // $hostinfo[4]: optional port number
            // The host string prefix can temporarily override the current setting for SMTPSecure
            // If it's not specified, the default value is used
            //Check the host name is a valid name or IP address before trying to use it
            if (!static::isValidHost($hostinfo[3])) {
                static::edebug($this->lang('connect_host') . ' ' . $hostentry);
                continue;
            }
            $prefix = '';
            $secure = $this->SMTPSecure;
            $tls = ('tls' == $this->SMTPSecure);
            if ('ssl' == $hostinfo[2] or ('' == $hostinfo[2] and 'ssl' == $this->SMTPSecure)) {
                $prefix = 'ssl://';
                $tls = false; // Can't have SSL and TLS at the same time
                $secure = 'ssl';
            } elseif ('tls' == $hostinfo[2]) {
                $tls = true;
                // tls doesn't use a prefix
                $secure = 'tls';
            }
            //Do we need the OpenSSL extension?
            $sslext = defined('OPENSSL_ALGO_SHA256');
            if ('tls' === $secure or 'ssl' === $secure) {
                //Check for an OpenSSL constant rather than using extension_loaded, which is sometimes disabled
                if (!$sslext) {
                    throw new Exception($this->lang('extension_missing') . 'openssl', self::STOP_CRITICAL);
                }
            }
            $host = $hostinfo[3];
            $port = $this->Port;
            $tport = (int) $hostinfo[4];
            if ($tport > 0 and $tport < 65536) {
                $port = $tport;
            }
            if ($this->smtp->connect($prefix . $host, $port, $this->Timeout, $options)) {
                try {
                    if ($this->Helo) {
                        $hello = $this->Helo;
                    } else {
                        $hello = $this->serverHostname();
                    }
                    $this->smtp->hello($hello);
                    //Automatically enable TLS encryption if:
                    // * it's not disabled
                    // * we have openssl extension
                    // * we are not already using SSL
                    // * the server offers STARTTLS
                    if ($this->SMTPAutoTLS and $sslext and 'ssl' != $secure and $this->smtp->getServerExt('STARTTLS')) {
                        $tls = true;
                    }
                    if ($tls) {
                        if (!$this->smtp->startTLS()) {
                            throw new Exception($this->lang('connect_host'));
                        }
                        // We must resend EHLO after TLS negotiation
                        $this->smtp->hello($hello);
                    }
                    if ($this->SMTPAuth) {
                        if (!$this->smtp->authenticate(
                            $this->Username,
                            $this->Password,
                            $this->AuthType,
                            $this->oauth
                        )
                        ) {
                            throw new Exception($this->lang('authenticate'));
                        }
                    }
                    return true;
                } catch (Exception $exc) {
                    $lastexception = $exc;
                    $this->edebug($exc->getMessage());
                    // We must have connected, but then failed TLS or Auth, so close connection nicely
                    $this->smtp->quit();
                }
            }
        }
        // If we get here, all connection attempts have failed, so close connection hard
        $this->smtp->close();
        // As we've caught all exceptions, just report whatever the last one was
        if ($this->exceptions and null !== $lastexception) {
            throw $lastexception;
        }
        return false;
    }
    /**
     * Close the active SMTP session if one exists.
     */
    public function smtpClose()
    {
        if (null !== $this->smtp) {
            if ($this->smtp->connected()) {
                $this->smtp->quit();
                $this->smtp->close();
            }
        }
    }
    /**
     * Set the language for error messages.
     * Returns false if it cannot load the language file.
     * The default language is English.
     *
     * @param string $langcode  ISO 639-1 2-character language code (e.g. French is "fr")
     * @param string $lang_path Path to the language file directory, with trailing separator (slash)
     *
     * @return bool
     */
    public function setLanguage($langcode = 'en', $lang_path = '')
    {
        // Backwards compatibility for renamed language codes
        $renamed_langcodes = [
            'br' => 'pt_br',
            'cz' => 'cs',
            'dk' => 'da',
            'no' => 'nb',
            'se' => 'sv',
            'rs' => 'sr',
            'tg' => 'tl',
        ];
        if (isset($renamed_langcodes[$langcode])) {
            $langcode = $renamed_langcodes[$langcode];
        }
        // Define full set of translatable strings in English
        $PHPMAILER_LANG = [
            'authenticate' => 'SMTP Error: Could not authenticate.',
            'connect_host' => 'SMTP Error: Could not connect to SMTP host.',
            'data_not_accepted' => 'SMTP Error: data not accepted.',
            'empty_message' => 'Message body empty',
            'encoding' => 'Unknown encoding: ',
            'execute' => 'Could not execute: ',
            'file_access' => 'Could not access file: ',
            'file_open' => 'File Error: Could not open file: ',
            'from_failed' => 'The following From address failed: ',
            'instantiate' => 'Could not instantiate mail function.',
            'invalid_address' => 'Invalid address: ',
            'mailer_not_supported' => ' mailer is not supported.',
            'provide_address' => 'You must provide at least one recipient email address.',
            'recipients_failed' => 'SMTP Error: The following recipients failed: ',
            'signing' => 'Signing Error: ',
            'smtp_connect_failed' => 'SMTP connect() failed.',
            'smtp_error' => 'SMTP server error: ',
            'variable_set' => 'Cannot set or reset variable: ',
            'extension_missing' => 'Extension missing: ',
        ];
        if (empty($lang_path)) {
            // Calculate an absolute path so it can work if CWD is not here
            $lang_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
        }
        //Validate $langcode
        if (!preg_match('/^[a-z]{2}(?:_[a-zA-Z]{2})?$/', $langcode)) {
            $langcode = 'en';
        }
        $foundlang = true;
        $lang_file = $lang_path . 'phpmailer.lang-' . $langcode . '.php';
        // There is no English translation file
        if ('en' != $langcode) {
            // Make sure language file path is readable
            if (!static::isPermittedPath($lang_file) || !file_exists($lang_file)) {
                $foundlang = false;
            } else {
                // Overwrite language-specific strings.
                // This way we'll never have missing translation keys.
                $foundlang = include $lang_file;
            }
        }
        $this->language = $PHPMAILER_LANG;
        return (bool) $foundlang; // Returns false if language not found
    }
    /**
     * Get the array of strings for the current language.
     *
     * @return array
     */
    public function getTranslations()
    {
        return $this->language;
    }
    /**
     * Create recipient headers.
     *
     * @param string $type
     * @param array  $addr An array of recipients,
     *                     where each recipient is a 2-element indexed array with element 0 containing an address
     *                     and element 1 containing a name, like:
     *                     [['joe@example.com', 'Joe User'], ['zoe@example.com', 'Zoe User']]
     *
     * @return string
     */
    public function addrAppend($type, $addr)
    {
        $addresses = [];
        foreach ($addr as $address) {
            $addresses[] = $this->addrFormat($address);
        }
        return $type . ': ' . implode(', ', $addresses) . static::$LE;
    }
    /**
     * Format an address for use in a message header.
     *
     * @param array $addr A 2-element indexed array, element 0 containing an address, element 1 containing a name like
     *                    ['joe@example.com', 'Joe User']
     *
     * @return string
     */
    public function addrFormat($addr)
    {
        if (empty($addr[1])) { // No name provided
            return $this->secureHeader($addr[0]);
        }
        return $this->encodeHeader($this->secureHeader($addr[1]), 'phrase') . ' <' . $this->secureHeader(
                $addr[0]
            ) . '>';
    }
    /**
     * Word-wrap message.
     * For use with mailers that do not automatically perform wrapping
     * and for quoted-printable encoded messages.
     * Original written by philippe.
     *
     * @param string $message The message to wrap
     * @param int    $length  The line length to wrap to
     * @param bool   $qp_mode Whether to run in Quoted-Printable mode
     *
     * @return string
     */
    public function wrapText($message, $length, $qp_mode = false)
    {
        if ($qp_mode) {
            $soft_break = sprintf(' =%s', static::$LE);
        } else {
            $soft_break = static::$LE;
        }
        // If utf-8 encoding is used, we will need to make sure we don't
        // split multibyte characters when we wrap
        $is_utf8 = static::CHARSET_UTF8 === strtolower($this->CharSet);
        $lelen = strlen(static::$LE);
        $crlflen = strlen(static::$LE);
        $message = static::normalizeBreaks($message);
        //Remove a trailing line break
        if (substr($message, -$lelen) == static::$LE) {
            $message = substr($message, 0, -$lelen);
        }
        //Split message into lines
        $lines = explode(static::$LE, $message);
        //Message will be rebuilt in here
        $message = '';
        foreach ($lines as $line) {
            $words = explode(' ', $line);
            $buf = '';
            $firstword = true;
            foreach ($words as $word) {
                if ($qp_mode and (strlen($word) > $length)) {
                    $space_left = $length - strlen($buf) - $crlflen;
                    if (!$firstword) {
                        if ($space_left > 20) {
                            $len = $space_left;
                            if ($is_utf8) {
                                $len = $this->utf8CharBoundary($word, $len);
                            } elseif ('=' == substr($word, $len - 1, 1)) {
                                --$len;
                            } elseif ('=' == substr($word, $len - 2, 1)) {
                                $len -= 2;
                            }
                            $part = substr($word, 0, $len);
                            $word = substr($word, $len);
                            $buf .= ' ' . $part;
                            $message .= $buf . sprintf('=%s', static::$LE);
                        } else {
                            $message .= $buf . $soft_break;
                        }
                        $buf = '';
                    }
                    while (strlen($word) > 0) {
                        if ($length <= 0) {
                            break;
                        }
                        $len = $length;
                        if ($is_utf8) {
                            $len = $this->utf8CharBoundary($word, $len);
                        } elseif ('=' == substr($word, $len - 1, 1)) {
                            --$len;
                        } elseif ('=' == substr($word, $len - 2, 1)) {
                            $len -= 2;
                        }
                        $part = substr($word, 0, $len);
                        $word = substr($word, $len);
                        if (strlen($word) > 0) {
                            $message .= $part . sprintf('=%s', static::$LE);
                        } else {
                            $buf = $part;
                        }
                    }
                } else {
                    $buf_o = $buf;
                    if (!$firstword) {
                        $buf .= ' ';
                    }
                    $buf .= $word;
                    if (strlen($buf) > $length and '' != $buf_o) {
                        $message .= $buf_o . $soft_break;
                        $buf = $word;
                    }
                }
                $firstword = false;
            }
            $message .= $buf . static::$LE;
        }
        return $message;
    }
    /**
     * Find the last character boundary prior to $maxLength in a utf-8
     * quoted-printable encoded string.
     * Original written by Colin Brown.
     *
     * @param string $encodedText utf-8 QP text
     * @param int    $maxLength   Find the last character boundary prior to this length
     *
     * @return int
     */
    public function utf8CharBoundary($encodedText, $maxLength)
    {
        $foundSplitPos = false;
        $lookBack = 3;
        while (!$foundSplitPos) {
            $lastChunk = substr($encodedText, $maxLength - $lookBack, $lookBack);
            $encodedCharPos = strpos($lastChunk, '=');
            if (false !== $encodedCharPos) {
                // Found start of encoded character byte within $lookBack block.
                // Check the encoded byte value (the 2 chars after the '=')
                $hex = substr($encodedText, $maxLength - $lookBack + $encodedCharPos + 1, 2);
                $dec = hexdec($hex);
                if ($dec < 128) {
                    // Single byte character.
                    // If the encoded char was found at pos 0, it will fit
                    // otherwise reduce maxLength to start of the encoded char
                    if ($encodedCharPos > 0) {
                        $maxLength -= $lookBack - $encodedCharPos;
                    }
                    $foundSplitPos = true;
                } elseif ($dec >= 192) {
                    // First byte of a multi byte character
                    // Reduce maxLength to split at start of character
                    $maxLength -= $lookBack - $encodedCharPos;
                    $foundSplitPos = true;
                } elseif ($dec < 192) {
                    // Middle byte of a multi byte character, look further back
                    $lookBack += 3;
                }
            } else {
                // No encoded character found
                $foundSplitPos = true;
            }
        }
        return $maxLength;
    }
    /**
     * Apply word wrapping to the message body.
     * Wraps the message body to the number of chars set in the WordWrap property.
     * You should only do this to plain-text bodies as wrapping HTML tags may break them.
     * This is called automatically by createBody(), so you don't need to call it yourself.
     */
    public function setWordWrap()
    {
        if ($this->WordWrap < 1) {
            return;
        }
        switch ($this->message_type) {
            case 'alt':
            case 'alt_inline':
            case 'alt_attach':
            case 'alt_inline_attach':
                $this->AltBody = $this->wrapText($this->AltBody, $this->WordWrap);
                break;
            default:
                $this->Body = $this->wrapText($this->Body, $this->WordWrap);
                break;
        }
    }
    /**
     * Assemble message headers.
     *
     * @return string The assembled headers
     */
    public function createHeader()
    {
        $result = '';
        $result .= $this->headerLine('Date', '' == $this->MessageDate ? self::rfcDate() : $this->MessageDate);
        // To be created automatically by mail()
        if ($this->SingleTo) {
            if ('mail' != $this->Mailer) {
                foreach ($this->to as $toaddr) {
                    $this->SingleToArray[] = $this->addrFormat($toaddr);
                }
            }
        } else {
            if (count($this->to) > 0) {
                if ('mail' != $this->Mailer) {
                    $result .= $this->addrAppend('To', $this->to);
                }
            } elseif (count($this->cc) == 0) {
                $result .= $this->headerLine('To', 'undisclosed-recipients:;');
            }
        }
        $result .= $this->addrAppend('From', [[trim($this->From), $this->FromName]]);
        // sendmail and mail() extract Cc from the header before sending
        if (count($this->cc) > 0) {
            $result .= $this->addrAppend('Cc', $this->cc);
        }
        // sendmail and mail() extract Bcc from the header before sending
        if ((
                'sendmail' == $this->Mailer or 'qmail' == $this->Mailer or 'mail' == $this->Mailer
            )
            and count($this->bcc) > 0
        ) {
            $result .= $this->addrAppend('Bcc', $this->bcc);
        }
        if (count($this->ReplyTo) > 0) {
            $result .= $this->addrAppend('Reply-To', $this->ReplyTo);
        }
        // mail() sets the subject itself
        if ('mail' != $this->Mailer) {
            $result .= $this->headerLine('Subject', $this->encodeHeader($this->secureHeader($this->Subject)));
        }
        // Only allow a custom message ID if it conforms to RFC 5322 section 3.6.4
        // https://tools.ietf.org/html/rfc5322#section-3.6.4
        if ('' != $this->MessageID and preg_match('/^<.*@.*>$/', $this->MessageID)) {
            $this->lastMessageID = $this->MessageID;
        } else {
            $this->lastMessageID = sprintf('<%s@%s>', $this->uniqueid, $this->serverHostname());
        }
        $result .= $this->headerLine('Message-ID', $this->lastMessageID);
        if (null !== $this->Priority) {
            $result .= $this->headerLine('X-Priority', $this->Priority);
        }
        if ('' == $this->XMailer) {
            $result .= $this->headerLine(
                'X-Mailer',
                'PHPMailer ' . self::VERSION . ' (https://github.com/PHPMailer/PHPMailer)'
            );
        } else {
            $myXmailer = trim($this->XMailer);
            if ($myXmailer) {
                $result .= $this->headerLine('X-Mailer', $myXmailer);
            }
        }
        if ('' != $this->ConfirmReadingTo) {
            $result .= $this->headerLine('Disposition-Notification-To', '<' . $this->ConfirmReadingTo . '>');
        }
        // Add custom headers
        foreach ($this->CustomHeader as $header) {
            $result .= $this->headerLine(
                trim($header[0]),
                $this->encodeHeader(trim($header[1]))
            );
        }
        if (!$this->sign_key_file) {
            $result .= $this->headerLine('MIME-Version', '1.0');
            $result .= $this->getMailMIME();
        }
        return $result;
    }
    /**
     * Get the message MIME type headers.
     *
     * @return string
     */
    public function getMailMIME()
    {
        $result = '';
        $ismultipart = true;
        switch ($this->message_type) {
            case 'inline':
                $result .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';');
                $result .= $this->textLine("\tboundary=\"" . $this->boundary[1] . '"');
                break;
            case 'attach':
            case 'inline_attach':
            case 'alt_attach':
            case 'alt_inline_attach':
                $result .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_MIXED . ';');
                $result .= $this->textLine("\tboundary=\"" . $this->boundary[1] . '"');
                break;
            case 'alt':
            case 'alt_inline':
                $result .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_ALTERNATIVE . ';');
                $result .= $this->textLine("\tboundary=\"" . $this->boundary[1] . '"');
                break;
            default:
                // Catches case 'plain': and case '':
                $result .= $this->textLine('Content-Type: ' . $this->ContentType . '; charset=' . $this->CharSet);
                $ismultipart = false;
                break;
        }
        // RFC1341 part 5 says 7bit is assumed if not specified
        if (static::ENCODING_7BIT != $this->Encoding) {
            // RFC 2045 section 6.4 says multipart MIME parts may only use 7bit, 8bit or binary CTE
            if ($ismultipart) {
                if (static::ENCODING_8BIT == $this->Encoding) {
                    $result .= $this->headerLine('Content-Transfer-Encoding', static::ENCODING_8BIT);
                }
                // The only remaining alternatives are quoted-printable and base64, which are both 7bit compatible
            } else {
                $result .= $this->headerLine('Content-Transfer-Encoding', $this->Encoding);
            }
        }
        if ('mail' != $this->Mailer) {
            $result .= static::$LE;
        }
        return $result;
    }
    /**
     * Returns the whole MIME message.
     * Includes complete headers and body.
     * Only valid post preSend().
     *
     * @see PHPMailer::preSend()
     *
     * @return string
     */
    public function getSentMIMEMessage()
    {
        return rtrim($this->MIMEHeader . $this->mailHeader, "\n\r") . static::$LE . static::$LE . $this->MIMEBody;
    }
    /**
     * Create a unique ID to use for boundaries.
     *
     * @return string
     */
    protected function generateId()
    {
        $len = 32; //32 bytes = 256 bits
        if (function_exists('random_bytes')) {
            $bytes = random_bytes($len);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($len);
        } else {
            //Use a hash to force the length to the same as the other methods
            $bytes = hash('sha256', uniqid((string) mt_rand(), true), true);
        }
        //We don't care about messing up base64 format here, just want a random string
        return str_replace(['=', '+', '/'], '', base64_encode(hash('sha256', $bytes, true)));
    }
    /**
     * Assemble the message body.
     * Returns an empty string on failure.
     *
     * @throws Exception
     *
     * @return string The assembled message body
     */
    public function createBody()
    {
        $body = '';
        //Create unique IDs and preset boundaries
        $this->uniqueid = $this->generateId();
        $this->boundary[1] = 'b1_' . $this->uniqueid;
        $this->boundary[2] = 'b2_' . $this->uniqueid;
        $this->boundary[3] = 'b3_' . $this->uniqueid;
        if ($this->sign_key_file) {
            $body .= $this->getMailMIME() . static::$LE;
        }
        $this->setWordWrap();
        $bodyEncoding = $this->Encoding;
        $bodyCharSet = $this->CharSet;
        //Can we do a 7-bit downgrade?
        if (static::ENCODING_8BIT == $bodyEncoding and !$this->has8bitChars($this->Body)) {
            $bodyEncoding = static::ENCODING_7BIT;
            //All ISO 8859, Windows codepage and UTF-8 charsets are ascii compatible up to 7-bit
            $bodyCharSet = 'us-ascii';
        }
        //If lines are too long, and we're not already using an encoding that will shorten them,
        //change to quoted-printable transfer encoding for the body part only
        if (static::ENCODING_BASE64 != $this->Encoding and static::hasLineLongerThanMax($this->Body)) {
            $bodyEncoding = static::ENCODING_QUOTED_PRINTABLE;
        }
        $altBodyEncoding = $this->Encoding;
        $altBodyCharSet = $this->CharSet;
        //Can we do a 7-bit downgrade?
        if (static::ENCODING_8BIT == $altBodyEncoding and !$this->has8bitChars($this->AltBody)) {
            $altBodyEncoding = static::ENCODING_7BIT;
            //All ISO 8859, Windows codepage and UTF-8 charsets are ascii compatible up to 7-bit
            $altBodyCharSet = 'us-ascii';
        }
        //If lines are too long, and we're not already using an encoding that will shorten them,
        //change to quoted-printable transfer encoding for the alt body part only
        if (static::ENCODING_BASE64 != $altBodyEncoding and static::hasLineLongerThanMax($this->AltBody)) {
            $altBodyEncoding = static::ENCODING_QUOTED_PRINTABLE;
        }
        //Use this as a preamble in all multipart message types
        $mimepre = 'This is a multi-part message in MIME format.' . static::$LE;
        switch ($this->message_type) {
            case 'inline':
                $body .= $mimepre;
                $body .= $this->getBoundary($this->boundary[1], $bodyCharSet, '', $bodyEncoding);
                $body .= $this->encodeString($this->Body, $bodyEncoding);
                $body .= static::$LE;
                $body .= $this->attachAll('inline', $this->boundary[1]);
                break;
            case 'attach':
                $body .= $mimepre;
                $body .= $this->getBoundary($this->boundary[1], $bodyCharSet, '', $bodyEncoding);
                $body .= $this->encodeString($this->Body, $bodyEncoding);
                $body .= static::$LE;
                $body .= $this->attachAll('attachment', $this->boundary[1]);
                break;
            case 'inline_attach':
                $body .= $mimepre;
                $body .= $this->textLine('--' . $this->boundary[1]);
                $body .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';');
                $body .= $this->textLine("\tboundary=\"" . $this->boundary[2] . '"');
                $body .= static::$LE;
                $body .= $this->getBoundary($this->boundary[2], $bodyCharSet, '', $bodyEncoding);
                $body .= $this->encodeString($this->Body, $bodyEncoding);
                $body .= static::$LE;
                $body .= $this->attachAll('inline', $this->boundary[2]);
                $body .= static::$LE;
                $body .= $this->attachAll('attachment', $this->boundary[1]);
                break;
            case 'alt':
                $body .= $mimepre;
                $body .= $this->getBoundary($this->boundary[1], $altBodyCharSet, static::CONTENT_TYPE_PLAINTEXT, $altBodyEncoding);
                $body .= $this->encodeString($this->AltBody, $altBodyEncoding);
                $body .= static::$LE;
                $body .= $this->getBoundary($this->boundary[1], $bodyCharSet, static::CONTENT_TYPE_TEXT_HTML, $bodyEncoding);
                $body .= $this->encodeString($this->Body, $bodyEncoding);
                $body .= static::$LE;
                if (!empty($this->Ical)) {
                    $body .= $this->getBoundary($this->boundary[1], '', static::CONTENT_TYPE_TEXT_CALENDAR . '; method=REQUEST', '');
                    $body .= $this->encodeString($this->Ical, $this->Encoding);
                    $body .= static::$LE;
                }
                $body .= $this->endBoundary($this->boundary[1]);
                break;
            case 'alt_inline':
                $body .= $mimepre;
                $body .= $this->getBoundary($this->boundary[1], $altBodyCharSet, static::CONTENT_TYPE_PLAINTEXT, $altBodyEncoding);
                $body .= $this->encodeString($this->AltBody, $altBodyEncoding);
                $body .= static::$LE;
                $body .= $this->textLine('--' . $this->boundary[1]);
                $body .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';');
                $body .= $this->textLine("\tboundary=\"" . $this->boundary[2] . '"');
                $body .= static::$LE;
                $body .= $this->getBoundary($this->boundary[2], $bodyCharSet, static::CONTENT_TYPE_TEXT_HTML, $bodyEncoding);
                $body .= $this->encodeString($this->Body, $bodyEncoding);
                $body .= static::$LE;
                $body .= $this->attachAll('inline', $this->boundary[2]);
                $body .= static::$LE;
                $body .= $this->endBoundary($this->boundary[1]);
                break;
            case 'alt_attach':
                $body .= $mimepre;
                $body .= $this->textLine('--' . $this->boundary[1]);
                $body .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_ALTERNATIVE . ';');
                $body .= $this->textLine("\tboundary=\"" . $this->boundary[2] . '"');
                $body .= static::$LE;
                $body .= $this->getBoundary($this->boundary[2], $altBodyCharSet, static::CONTENT_TYPE_PLAINTEXT, $altBodyEncoding);
                $body .= $this->encodeString($this->AltBody, $altBodyEncoding);
                $body .= static::$LE;
                $body .= $this->getBoundary($this->boundary[2], $bodyCharSet, static::CONTENT_TYPE_TEXT_HTML, $bodyEncoding);
                $body .= $this->encodeString($this->Body, $bodyEncoding);
                $body .= static::$LE;
                if (!empty($this->Ical)) {
                    $body .= $this->getBoundary($this->boundary[2], '', static::CONTENT_TYPE_TEXT_CALENDAR . '; method=REQUEST', '');
                    $body .= $this->encodeString($this->Ical, $this->Encoding);
                }
                $body .= $this->endBoundary($this->boundary[2]);
                $body .= static::$LE;
                $body .= $this->attachAll('attachment', $this->boundary[1]);
                break;
            case 'alt_inline_attach':
                $body .= $mimepre;
                $body .= $this->textLine('--' . $this->boundary[1]);
                $body .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_ALTERNATIVE . ';');
                $body .= $this->textLine("\tboundary=\"" . $this->boundary[2] . '"');
                $body .= static::$LE;
                $body .= $this->getBoundary($this->boundary[2], $altBodyCharSet, static::CONTENT_TYPE_PLAINTEXT, $altBodyEncoding);
                $body .= $this->encodeString($this->AltBody, $altBodyEncoding);
                $body .= static::$LE;
                $body .= $this->textLine('--' . $this->boundary[2]);
                $body .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';');
                $body .= $this->textLine("\tboundary=\"" . $this->boundary[3] . '"');
                $body .= static::$LE;
                $body .= $this->getBoundary($this->boundary[3], $bodyCharSet, static::CONTENT_TYPE_TEXT_HTML, $bodyEncoding);
                $body .= $this->encodeString($this->Body, $bodyEncoding);
                $body .= static::$LE;
                $body .= $this->attachAll('inline', $this->boundary[3]);
                $body .= static::$LE;
                $body .= $this->endBoundary($this->boundary[2]);
                $body .= static::$LE;
                $body .= $this->attachAll('attachment', $this->boundary[1]);
                break;
            default:
                // Catch case 'plain' and case '', applies to simple `text/plain` and `text/html` body content types
                //Reset the `Encoding` property in case we changed it for line length reasons
                $this->Encoding = $bodyEncoding;
                $body .= $this->encodeString($this->Body, $this->Encoding);
                break;
        }
        if ($this->isError()) {
            $body = '';
            if ($this->exceptions) {
                throw new Exception($this->lang('empty_message'), self::STOP_CRITICAL);
            }
        } elseif ($this->sign_key_file) {
            try {
                if (!defined('PKCS7_TEXT')) {
                    throw new Exception($this->lang('extension_missing') . 'openssl');
                }
                // @TODO would be nice to use php://temp streams here
                $file = tempnam(sys_get_temp_dir(), 'mail');
                if (false === file_put_contents($file, $body)) {
                    throw new Exception($this->lang('signing') . ' Could not write temp file');
                }
                $signed = tempnam(sys_get_temp_dir(), 'signed');
                //Workaround for PHP bug https://bugs.php.net/bug.php?id=69197
                if (empty($this->sign_extracerts_file)) {
                    $sign = @openssl_pkcs7_sign(
                        $file,
                        $signed,
                        'file://' . realpath($this->sign_cert_file),
                        ['file://' . realpath($this->sign_key_file), $this->sign_key_pass],
                        []
                    );
                } else {
                    $sign = @openssl_pkcs7_sign(
                        $file,
                        $signed,
                        'file://' . realpath($this->sign_cert_file),
                        ['file://' . realpath($this->sign_key_file), $this->sign_key_pass],
                        [],
                        PKCS7_DETACHED,
                        $this->sign_extracerts_file
                    );
                }
                @unlink($file);
                if ($sign) {
                    $body = file_get_contents($signed);
                    @unlink($signed);
                    //The message returned by openssl contains both headers and body, so need to split them up
                    $parts = explode("\n\n", $body, 2);
                    $this->MIMEHeader .= $parts[0] . static::$LE . static::$LE;
                    $body = $parts[1];
                } else {
                    @unlink($signed);
                    throw new Exception($this->lang('signing') . openssl_error_string());
                }
            } catch (Exception $exc) {
                $body = '';
                if ($this->exceptions) {
                    throw $exc;
                }
            }
        }
        return $body;
    }
    /**
     * Return the start of a message boundary.
     *
     * @param string $boundary
     * @param string $charSet
     * @param string $contentType
     * @param string $encoding
     *
     * @return string
     */
    protected function getBoundary($boundary, $charSet, $contentType, $encoding)
    {
        $result = '';
        if ('' == $charSet) {
            $charSet = $this->CharSet;
        }
        if ('' == $contentType) {
            $contentType = $this->ContentType;
        }
        if ('' == $encoding) {
            $encoding = $this->Encoding;
        }
        $result .= $this->textLine('--' . $boundary);
        $result .= sprintf('Content-Type: %s; charset=%s', $contentType, $charSet);
        $result .= static::$LE;
        // RFC1341 part 5 says 7bit is assumed if not specified
        if (static::ENCODING_7BIT != $encoding) {
            $result .= $this->headerLine('Content-Transfer-Encoding', $encoding);
        }
        $result .= static::$LE;
        return $result;
    }
    /**
     * Return the end of a message boundary.
     *
     * @param string $boundary
     *
     * @return string
     */
    protected function endBoundary($boundary)
    {
        return static::$LE . '--' . $boundary . '--' . static::$LE;
    }
    /**
     * Set the message type.
     * PHPMailer only supports some preset message types, not arbitrary MIME structures.
     */
    protected function setMessageType()
    {
        $type = [];
        if ($this->alternativeExists()) {
            $type[] = 'alt';
        }
        if ($this->inlineImageExists()) {
            $type[] = 'inline';
        }
        if ($this->attachmentExists()) {
            $type[] = 'attach';
        }
        $this->message_type = implode('_', $type);
        if ('' == $this->message_type) {
            //The 'plain' message_type refers to the message having a single body element, not that it is plain-text
            $this->message_type = 'plain';
        }
    }
    /**
     * Format a header line.
     *
     * @param string     $name
     * @param string|int $value
     *
     * @return string
     */
    public function headerLine($name, $value)
    {
        return $name . ': ' . $value . static::$LE;
    }
    /**
     * Return a formatted mail line.
     *
     * @param string $value
     *
     * @return string
     */
    public function textLine($value)
    {
        return $value . static::$LE;
    }
    /**
     * Add an attachment from a path on the filesystem.
     * Never use a user-supplied path to a file!
     * Returns false if the file could not be found or read.
     * Explicitly *does not* support passing URLs; PHPMailer is not an HTTP client.
     * If you need to do that, fetch the resource yourself and pass it in via a local file or string.
     *
     * @param string $path        Path to the attachment
     * @param string $name        Overrides the attachment name
     * @param string $encoding    File encoding (see $Encoding)
     * @param string $type        File extension (MIME) type
     * @param string $disposition Disposition to use
     *
     * @throws Exception
     *
     * @return bool
     */
    public function addAttachment($path, $name = '', $encoding = self::ENCODING_BASE64, $type = '', $disposition = 'attachment')
    {
        try {
            if (!static::isPermittedPath($path) || !@is_file($path)) {
                throw new Exception($this->lang('file_access') . $path, self::STOP_CONTINUE);
            }
            // If a MIME type is not specified, try to work it out from the file name
            if ('' == $type) {
                $type = static::filenameToType($path);
            }
            $filename = basename($path);
            if ('' == $name) {
                $name = $filename;
            }
            $this->attachment[] = [
                0 => $path,
                1 => $filename,
                2 => $name,
                3 => $encoding,
                4 => $type,
                5 => false, // isStringAttachment
                6 => $disposition,
                7 => $name,
            ];
        } catch (Exception $exc) {
            $this->setError($exc->getMessage());
            $this->edebug($exc->getMessage());
            if ($this->exceptions) {
                throw $exc;
            }
            return false;
        }
        return true;
    }
    /**
     * Return the array of attachments.
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachment;
    }
    /**
     * Attach all file, string, and binary attachments to the message.
     * Returns an empty string on failure.
     *
     * @param string $disposition_type
     * @param string $boundary
     *
     * @return string
     */
    protected function attachAll($disposition_type, $boundary)
    {
        // Return text of body
        $mime = [];
        $cidUniq = [];
        $incl = [];
        // Add all attachments
        foreach ($this->attachment as $attachment) {
            // Check if it is a valid disposition_filter
            if ($attachment[6] == $disposition_type) {
                // Check for string attachment
                $string = '';
                $path = '';
                $bString = $attachment[5];
                if ($bString) {
                    $string = $attachment[0];
                } else {
                    $path = $attachment[0];
                }
                $inclhash = hash('sha256', serialize($attachment));
                if (in_array($inclhash, $incl)) {
                    continue;
                }
                $incl[] = $inclhash;
                $name = $attachment[2];
                $encoding = $attachment[3];
                $type = $attachment[4];
                $disposition = $attachment[6];
                $cid = $attachment[7];
                if ('inline' == $disposition and array_key_exists($cid, $cidUniq)) {
                    continue;
                }
                $cidUniq[$cid] = true;
                $mime[] = sprintf('--%s%s', $boundary, static::$LE);
                //Only include a filename property if we have one
                if (!empty($name)) {
                    $mime[] = sprintf(
                        'Content-Type: %s; name="%s"%s',
                        $type,
                        $this->encodeHeader($this->secureHeader($name)),
                        static::$LE
                    );
                } else {
                    $mime[] = sprintf(
                        'Content-Type: %s%s',
                        $type,
                        static::$LE
                    );
                }
                // RFC1341 part 5 says 7bit is assumed if not specified
                if (static::ENCODING_7BIT != $encoding) {
                    $mime[] = sprintf('Content-Transfer-Encoding: %s%s', $encoding, static::$LE);
                }
                if (!empty($cid)) {
                    $mime[] = sprintf('Content-ID: <%s>%s', $cid, static::$LE);
                }
                // If a filename contains any of these chars, it should be quoted,
                // but not otherwise: RFC2183 & RFC2045 5.1
                // Fixes a warning in IETF's msglint MIME checker
                // Allow for bypassing the Content-Disposition header totally
                if (!(empty($disposition))) {
                    $encoded_name = $this->encodeHeader($this->secureHeader($name));
                    if (preg_match('/[ \(\)<>@,;:\\"\/\[\]\?=]/', $encoded_name)) {
                        $mime[] = sprintf(
                            'Content-Disposition: %s; filename="%s"%s',
                            $disposition,
                            $encoded_name,
                            static::$LE . static::$LE
                        );
                    } else {
                        if (!empty($encoded_name)) {
                            $mime[] = sprintf(
                                'Content-Disposition: %s; filename=%s%s',
                                $disposition,
                                $encoded_name,
                                static::$LE . static::$LE
                            );
                        } else {
                            $mime[] = sprintf(
                                'Content-Disposition: %s%s',
                                $disposition,
                                static::$LE . static::$LE
                            );
                        }
                    }
                } else {
                    $mime[] = static::$LE;
                }
                // Encode as string attachment
                if ($bString) {
                    $mime[] = $this->encodeString($string, $encoding);
                } else {
                    $mime[] = $this->encodeFile($path, $encoding);
                }
                if ($this->isError()) {
                    return '';
                }
                $mime[] = static::$LE;
            }
        }
        $mime[] = sprintf('--%s--%s', $boundary, static::$LE);
        return implode('', $mime);
    }
    /**
     * Encode a file attachment in requested format.
     * Returns an empty string on failure.
     *
     * @param string $path     The full path to the file
     * @param string $encoding The encoding to use; one of 'base64', '7bit', '8bit', 'binary', 'quoted-printable'
     *
     * @throws Exception
     *
     * @return string
     */
    protected function encodeFile($path, $encoding = self::ENCODING_BASE64)
    {
        try {
            if (!static::isPermittedPath($path) || !file_exists($path)) {
                throw new Exception($this->lang('file_open') . $path, self::STOP_CONTINUE);
            }
            $file_buffer = file_get_contents($path);
            if (false === $file_buffer) {
                throw new Exception($this->lang('file_open') . $path, self::STOP_CONTINUE);
            }
            $file_buffer = $this->encodeString($file_buffer, $encoding);
            return $file_buffer;
        } catch (Exception $exc) {
            $this->setError($exc->getMessage());
            return '';
        }
    }
    /**
     * Encode a string in requested format.
     * Returns an empty string on failure.
     *
     * @param string $str      The text to encode
     * @param string $encoding The encoding to use; one of 'base64', '7bit', '8bit', 'binary', 'quoted-printable'
     *
     * @return string
     */
    public function encodeString($str, $encoding = self::ENCODING_BASE64)
    {
        $encoded = '';
        switch (strtolower($encoding)) {
            case static::ENCODING_BASE64:
                $encoded = chunk_split(
                    base64_encode($str),
                    static::STD_LINE_LENGTH,
                    static::$LE
                );
                break;
            case static::ENCODING_7BIT:
            case static::ENCODING_8BIT:
                $encoded = static::normalizeBreaks($str);
                // Make sure it ends with a line break
                if (substr($encoded, -(strlen(static::$LE))) != static::$LE) {
                    $encoded .= static::$LE;
                }
                break;
            case static::ENCODING_BINARY:
                $encoded = $str;
                break;
            case static::ENCODING_QUOTED_PRINTABLE:
                $encoded = $this->encodeQP($str);
                break;
            default:
                $this->setError($this->lang('encoding') . $encoding);
                break;
        }
        return $encoded;
    }
    /**
     * Encode a header value (not including its label) optimally.
     * Picks shortest of Q, B, or none. Result includes folding if needed.
     * See RFC822 definitions for phrase, comment and text positions.
     *
     * @param string $str      The header value to encode
     * @param string $position What context the string will be used in
     *
     * @return string
     */
    public function encodeHeader($str, $position = 'text')
    {
        $matchcount = 0;
        switch (strtolower($position)) {
            case 'phrase':
                if (!preg_match('/[\200-\377]/', $str)) {
                    // Can't use addslashes as we don't know the value of magic_quotes_sybase
                    $encoded = addcslashes($str, "\0..\37\177\\\"");
                    if (($str == $encoded) and !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str)) {
                        return $encoded;
                    }
                    return "\"$encoded\"";
                }
                $matchcount = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
                break;
            /* @noinspection PhpMissingBreakStatementInspection */
            case 'comment':
                $matchcount = preg_match_all('/[()"]/', $str, $matches);
            //fallthrough
            case 'text':
            default:
                $matchcount += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
                break;
        }
        //RFCs specify a maximum line length of 78 chars, however mail() will sometimes
        //corrupt messages with headers longer than 65 chars. See #818
        $lengthsub = 'mail' == $this->Mailer ? 13 : 0;
        $maxlen = static::STD_LINE_LENGTH - $lengthsub;
        // Try to select the encoding which should produce the shortest output
        if ($matchcount > strlen($str) / 3) {
            // More than a third of the content will need encoding, so B encoding will be most efficient
            $encoding = 'B';
            //This calculation is:
            // max line length
            // - shorten to avoid mail() corruption
            // - Q/B encoding char overhead ("` =?<charset>?[QB]?<content>?=`")
            // - charset name length
            $maxlen = static::STD_LINE_LENGTH - $lengthsub - 8 - strlen($this->CharSet);
            if ($this->hasMultiBytes($str)) {
                // Use a custom function which correctly encodes and wraps long
                // multibyte strings without breaking lines within a character
                $encoded = $this->base64EncodeWrapMB($str, "\n");
            } else {
                $encoded = base64_encode($str);
                $maxlen -= $maxlen % 4;
                $encoded = trim(chunk_split($encoded, $maxlen, "\n"));
            }
            $encoded = preg_replace('/^(.*)$/m', ' =?' . $this->CharSet . "?$encoding?\\1?=", $encoded);
        } elseif ($matchcount > 0) {
            //1 or more chars need encoding, use Q-encode
            $encoding = 'Q';
            //Recalc max line length for Q encoding - see comments on B encode
            $maxlen = static::STD_LINE_LENGTH - $lengthsub - 8 - strlen($this->CharSet);
            $encoded = $this->encodeQ($str, $position);
            $encoded = $this->wrapText($encoded, $maxlen, true);
            $encoded = str_replace('=' . static::$LE, "\n", trim($encoded));
            $encoded = preg_replace('/^(.*)$/m', ' =?' . $this->CharSet . "?$encoding?\\1?=", $encoded);
        } elseif (strlen($str) > $maxlen) {
            //No chars need encoding, but line is too long, so fold it
            $encoded = trim($this->wrapText($str, $maxlen, false));
            if ($str == $encoded) {
                //Wrapping nicely didn't work, wrap hard instead
                $encoded = trim(chunk_split($str, static::STD_LINE_LENGTH, static::$LE));
            }
            $encoded = str_replace(static::$LE, "\n", trim($encoded));
            $encoded = preg_replace('/^(.*)$/m', ' \\1', $encoded);
        } else {
            //No reformatting needed
            return $str;
        }
        return trim(static::normalizeBreaks($encoded));
    }
    /**
     * Check if a string contains multi-byte characters.
     *
     * @param string $str multi-byte text to wrap encode
     *
     * @return bool
     */
    public function hasMultiBytes($str)
    {
        if (function_exists('mb_strlen')) {
            return strlen($str) > mb_strlen($str, $this->CharSet);
        }
        // Assume no multibytes (we can't handle without mbstring functions anyway)
        return false;
    }
    /**
     * Does a string contain any 8-bit chars (in any charset)?
     *
     * @param string $text
     *
     * @return bool
     */
    public function has8bitChars($text)
    {
        return (bool) preg_match('/[\x80-\xFF]/', $text);
    }
    /**
     * Encode and wrap long multibyte strings for mail headers
     * without breaking lines within a character.
     * Adapted from a function by paravoid.
     *
     * @see http://www.php.net/manual/en/function.mb-encode-mimeheader.php#60283
     *
     * @param string $str       multi-byte text to wrap encode
     * @param string $linebreak string to use as linefeed/end-of-line
     *
     * @return string
     */
    public function base64EncodeWrapMB($str, $linebreak = null)
    {
        $start = '=?' . $this->CharSet . '?B?';
        $end = '?=';
        $encoded = '';
        if (null === $linebreak) {
            $linebreak = static::$LE;
        }
        $mb_length = mb_strlen($str, $this->CharSet);
        // Each line must have length <= 75, including $start and $end
        $length = 75 - strlen($start) - strlen($end);
        // Average multi-byte ratio
        $ratio = $mb_length / strlen($str);
        // Base64 has a 4:3 ratio
        $avgLength = floor($length * $ratio * .75);
        for ($i = 0; $i < $mb_length; $i += $offset) {
            $lookBack = 0;
            do {
                $offset = $avgLength - $lookBack;
                $chunk = mb_substr($str, $i, $offset, $this->CharSet);
                $chunk = base64_encode($chunk);
                ++$lookBack;
            } while (strlen($chunk) > $length);
            $encoded .= $chunk . $linebreak;
        }
        // Chomp the last linefeed
        return substr($encoded, 0, -strlen($linebreak));
    }
    /**
     * Encode a string in quoted-printable format.
     * According to RFC2045 section 6.7.
     *
     * @param string $string The text to encode
     *
     * @return string
     */
    public function encodeQP($string)
    {
        return static::normalizeBreaks(quoted_printable_encode($string));
    }
    /**
     * Encode a string using Q encoding.
     *
     * @see http://tools.ietf.org/html/rfc2047#section-4.2
     *
     * @param string $str      the text to encode
     * @param string $position Where the text is going to be used, see the RFC for what that means
     *
     * @return string
     */
    public function encodeQ($str, $position = 'text')
    {
        // There should not be any EOL in the string
        $pattern = '';
        $encoded = str_replace(["\r", "\n"], '', $str);
        switch (strtolower($position)) {
            case 'phrase':
                // RFC 2047 section 5.3
                $pattern = '^A-Za-z0-9!*+\/ -';
                break;
            /*
             * RFC 2047 section 5.2.
             * Build $pattern without including delimiters and []
             */
            /* @noinspection PhpMissingBreakStatementInspection */
            case 'comment':
                $pattern = '\(\)"';
            /* Intentional fall through */
            case 'text':
            default:
                // RFC 2047 section 5.1
                // Replace every high ascii, control, =, ? and _ characters
                /** @noinspection SuspiciousAssignmentsInspection */
                $pattern = '\000-\011\013\014\016-\037\075\077\137\177-\377' . $pattern;
                break;
        }
        $matches = [];
        if (preg_match_all("/[{$pattern}]/", $encoded, $matches)) {
            // If the string contains an '=', make sure it's the first thing we replace
            // so as to avoid double-encoding
            $eqkey = array_search('=', $matches[0]);
            if (false !== $eqkey) {
                unset($matches[0][$eqkey]);
                array_unshift($matches[0], '=');
            }
            foreach (array_unique($matches[0]) as $char) {
                $encoded = str_replace($char, '=' . sprintf('%02X', ord($char)), $encoded);
            }
        }
        // Replace spaces with _ (more readable than =20)
        // RFC 2047 section 4.2(2)
        return str_replace(' ', '_', $encoded);
    }
    /**
     * Add a string or binary attachment (non-filesystem).
     * This method can be used to attach ascii or binary data,
     * such as a BLOB record from a database.
     *
     * @param string $string      String attachment data
     * @param string $filename    Name of the attachment
     * @param string $encoding    File encoding (see $Encoding)
     * @param string $type        File extension (MIME) type
     * @param string $disposition Disposition to use
     */
    public function addStringAttachment(
        $string,
        $filename,
        $encoding = self::ENCODING_BASE64,
        $type = '',
        $disposition = 'attachment'
    ) {
        // If a MIME type is not specified, try to work it out from the file name
        if ('' == $type) {
            $type = static::filenameToType($filename);
        }
        // Append to $attachment array
        $this->attachment[] = [
            0 => $string,
            1 => $filename,
            2 => basename($filename),
            3 => $encoding,
            4 => $type,
            5 => true, // isStringAttachment
            6 => $disposition,
            7 => 0,
        ];
    }
    /**
     * Add an embedded (inline) attachment from a file.
     * This can include images, sounds, and just about any other document type.
     * These differ from 'regular' attachments in that they are intended to be
     * displayed inline with the message, not just attached for download.
     * This is used in HTML messages that embed the images
     * the HTML refers to using the $cid value.
     * Never use a user-supplied path to a file!
     *
     * @param string $path        Path to the attachment
     * @param string $cid         Content ID of the attachment; Use this to reference
     *                            the content when using an embedded image in HTML
     * @param string $name        Overrides the attachment name
     * @param string $encoding    File encoding (see $Encoding)
     * @param string $type        File MIME type
     * @param string $disposition Disposition to use
     *
     * @return bool True on successfully adding an attachment
     */
    public function addEmbeddedImage($path, $cid, $name = '', $encoding = self::ENCODING_BASE64, $type = '', $disposition = 'inline')
    {
        if (!static::isPermittedPath($path) || !@is_file($path)) {
            $this->setError($this->lang('file_access') . $path);
            return false;
        }
        // If a MIME type is not specified, try to work it out from the file name
        if ('' == $type) {
            $type = static::filenameToType($path);
        }
        $filename = basename($path);
        if ('' == $name) {
            $name = $filename;
        }
        // Append to $attachment array
        $this->attachment[] = [
            0 => $path,
            1 => $filename,
            2 => $name,
            3 => $encoding,
            4 => $type,
            5 => false, // isStringAttachment
            6 => $disposition,
            7 => $cid,
        ];
        return true;
    }
    /**
     * Add an embedded stringified attachment.
     * This can include images, sounds, and just about any other document type.
     * If your filename doesn't contain an extension, be sure to set the $type to an appropriate MIME type.
     *
     * @param string $string      The attachment binary data
     * @param string $cid         Content ID of the attachment; Use this to reference
     *                            the content when using an embedded image in HTML
     * @param string $name        A filename for the attachment. If this contains an extension,
     *                            PHPMailer will attempt to set a MIME type for the attachment.
     *                            For example 'file.jpg' would get an 'image/jpeg' MIME type.
     * @param string $encoding    File encoding (see $Encoding), defaults to 'base64'
     * @param string $type        MIME type - will be used in preference to any automatically derived type
     * @param string $disposition Disposition to use
     *
     * @return bool True on successfully adding an attachment
     */
    public function addStringEmbeddedImage(
        $string,
        $cid,
        $name = '',
        $encoding = self::ENCODING_BASE64,
        $type = '',
        $disposition = 'inline'
    ) {
        // If a MIME type is not specified, try to work it out from the name
        if ('' == $type and !empty($name)) {
            $type = static::filenameToType($name);
        }
        // Append to $attachment array
        $this->attachment[] = [
            0 => $string,
            1 => $name,
            2 => $name,
            3 => $encoding,
            4 => $type,
            5 => true, // isStringAttachment
            6 => $disposition,
            7 => $cid,
        ];
        return true;
    }
    /**
     * Check if an embedded attachment is present with this cid.
     *
     * @param string $cid
     *
     * @return bool
     */
    protected function cidExists($cid)
    {
        foreach ($this->attachment as $attachment) {
            if ('inline' == $attachment[6] and $cid == $attachment[7]) {
                return true;
            }
        }
        return false;
    }
    /**
     * Check if an inline attachment is present.
     *
     * @return bool
     */
    public function inlineImageExists()
    {
        foreach ($this->attachment as $attachment) {
            if ('inline' == $attachment[6]) {
                return true;
            }
        }
        return false;
    }
    /**
     * Check if an attachment (non-inline) is present.
     *
     * @return bool
     */
    public function attachmentExists()
    {
        foreach ($this->attachment as $attachment) {
            if ('attachment' == $attachment[6]) {
                return true;
            }
        }
        return false;
    }
    /**
     * Check if this message has an alternative body set.
     *
     * @return bool
     */
    public function alternativeExists()
    {
        return !empty($this->AltBody);
    }
    /**
     * Clear queued addresses of given kind.
     *
     * @param string $kind 'to', 'cc', or 'bcc'
     */
    public function clearQueuedAddresses($kind)
    {
        $this->RecipientsQueue = array_filter(
            $this->RecipientsQueue,
            function ($params) use ($kind) {
                return $params[0] != $kind;
            }
        );
    }
    /**
     * Clear all To recipients.
     */
    public function clearAddresses()
    {
        foreach ($this->to as $to) {
            unset($this->all_recipients[strtolower($to[0])]);
        }
        $this->to = [];
        $this->clearQueuedAddresses('to');
    }
    /**
     * Clear all CC recipients.
     */
    public function clearCCs()
    {
        foreach ($this->cc as $cc) {
            unset($this->all_recipients[strtolower($cc[0])]);
        }
        $this->cc = [];
        $this->clearQueuedAddresses('cc');
    }
    /**
     * Clear all BCC recipients.
     */
    public function clearBCCs()
    {
        foreach ($this->bcc as $bcc) {
            unset($this->all_recipients[strtolower($bcc[0])]);
        }
        $this->bcc = [];
        $this->clearQueuedAddresses('bcc');
    }
    /**
     * Clear all ReplyTo recipients.
     */
    public function clearReplyTos()
    {
        $this->ReplyTo = [];
        $this->ReplyToQueue = [];
    }
    /**
     * Clear all recipient types.
     */
    public function clearAllRecipients()
    {
        $this->to = [];
        $this->cc = [];
        $this->bcc = [];
        $this->all_recipients = [];
        $this->RecipientsQueue = [];
    }
    /**
     * Clear all filesystem, string, and binary attachments.
     */
    public function clearAttachments()
    {
        $this->attachment = [];
    }
    /**
     * Clear all custom headers.
     */
    public function clearCustomHeaders()
    {
        $this->CustomHeader = [];
    }
    /**
     * Add an error message to the error container.
     *
     * @param string $msg
     */
    protected function setError($msg)
    {
        ++$this->error_count;
        if ('smtp' == $this->Mailer and null !== $this->smtp) {
            $lasterror = $this->smtp->getError();
            if (!empty($lasterror['error'])) {
                $msg .= $this->lang('smtp_error') . $lasterror['error'];
                if (!empty($lasterror['detail'])) {
                    $msg .= ' Detail: ' . $lasterror['detail'];
                }
                if (!empty($lasterror['smtp_code'])) {
                    $msg .= ' SMTP code: ' . $lasterror['smtp_code'];
                }
                if (!empty($lasterror['smtp_code_ex'])) {
                    $msg .= ' Additional SMTP info: ' . $lasterror['smtp_code_ex'];
                }
            }
        }
        $this->ErrorInfo = $msg;
    }
    /**
     * Return an RFC 822 formatted date.
     *
     * @return string
     */
    public static function rfcDate()
    {
        // Set the time zone to whatever the default is to avoid 500 errors
        // Will default to UTC if it's not set properly in php.ini
        date_default_timezone_set(@date_default_timezone_get());
        return date('D, j M Y H:i:s O');
    }
    /**
     * Get the server hostname.
     * Returns 'localhost.localdomain' if unknown.
     *
     * @return string
     */
    protected function serverHostname()
    {
        $result = '';
        if (!empty($this->Hostname)) {
            $result = $this->Hostname;
        } elseif (isset($_SERVER) and array_key_exists('SERVER_NAME', $_SERVER)) {
            $result = $_SERVER['SERVER_NAME'];
        } elseif (function_exists('gethostname') and gethostname() !== false) {
            $result = gethostname();
        } elseif (php_uname('n') !== false) {
            $result = php_uname('n');
        }
        if (!static::isValidHost($result)) {
            return 'localhost.localdomain';
        }
        return $result;
    }
    /**
     * Validate whether a string contains a valid value to use as a hostname or IP address.
     * IPv6 addresses must include [], e.g. `[::1]`, not just `::1`.
     *
     * @param string $host The host name or IP address to check
     *
     * @return bool
     */
    public static function isValidHost($host)
    {
        //Simple syntax limits
        if (empty($host)
            or !is_string($host)
            or strlen($host) > 256
        ) {
            return false;
        }
        //Looks like a bracketed IPv6 address
        if (trim($host, '[]') != $host) {
            return (bool) filter_var(trim($host, '[]'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        }
        //If removing all the dots results in a numeric string, it must be an IPv4 address.
        //Need to check this first because otherwise things like `999.0.0.0` are considered valid host names
        if (is_numeric(str_replace('.', '', $host))) {
            //Is it a valid IPv4 address?
            return (bool) filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        }
        if (filter_var('http://' . $host, FILTER_VALIDATE_URL)) {
            //Is it a syntactically valid hostname?
            return true;
        }
        return false;
    }
    /**
     * Get an error message in the current language.
     *
     * @param string $key
     *
     * @return string
     */
    protected function lang($key)
    {
        if (count($this->language) < 1) {
            $this->setLanguage('en'); // set the default language
        }
        if (array_key_exists($key, $this->language)) {
            if ('smtp_connect_failed' == $key) {
                //Include a link to troubleshooting docs on SMTP connection failure
                //this is by far the biggest cause of support questions
                //but it's usually not PHPMailer's fault.
                return $this->language[$key] . ' https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting';
            }
            return $this->language[$key];
        }
        //Return the key as a fallback
        return $key;
    }
    /**
     * Check if an error occurred.
     *
     * @return bool True if an error did occur
     */
    public function isError()
    {
        return $this->error_count > 0;
    }
    /**
     * Add a custom header.
     * $name value can be overloaded to contain
     * both header name and value (name:value).
     *
     * @param string      $name  Custom header name
     * @param string|null $value Header value
     */
    public function addCustomHeader($name, $value = null)
    {
        if (null === $value) {
            // Value passed in as name:value
            $this->CustomHeader[] = explode(':', $name, 2);
        } else {
            $this->CustomHeader[] = [$name, $value];
        }
    }
    /**
     * Returns all custom headers.
     *
     * @return array
     */
    public function getCustomHeaders()
    {
        return $this->CustomHeader;
    }
    /**
     * Create a message body from an HTML string.
     * Automatically inlines images and creates a plain-text version by converting the HTML,
     * overwriting any existing values in Body and AltBody.
     * Do not source $message content from user input!
     * $basedir is prepended when handling relative URLs, e.g. <img src="/images/a.png"> and must not be empty
     * will look for an image file in $basedir/images/a.png and convert it to inline.
     * If you don't provide a $basedir, relative paths will be left untouched (and thus probably break in email)
     * Converts data-uri images into embedded attachments.
     * If you don't want to apply these transformations to your HTML, just set Body and AltBody directly.
     *
     * @param string        $message  HTML message string
     * @param string        $basedir  Absolute path to a base directory to prepend to relative paths to images
     * @param bool|callable $advanced Whether to use the internal HTML to text converter
     *                                or your own custom converter @see PHPMailer::html2text()
     *
     * @return string $message The transformed message Body
     */
    public function msgHTML($message, $basedir = '', $advanced = false)
    {
        preg_match_all('/(src|background)=["\'](.*)["\']/Ui', $message, $images);
        if (array_key_exists(2, $images)) {
            if (strlen($basedir) > 1 && '/' != substr($basedir, -1)) {
                // Ensure $basedir has a trailing /
                $basedir .= '/';
            }
            foreach ($images[2] as $imgindex => $url) {
                // Convert data URIs into embedded images
                //e.g. "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                if (preg_match('#^data:(image/(?:jpe?g|gif|png));?(base64)?,(.+)#', $url, $match)) {
                    if (count($match) == 4 and static::ENCODING_BASE64 == $match[2]) {
                        $data = base64_decode($match[3]);
                    } elseif ('' == $match[2]) {
                        $data = rawurldecode($match[3]);
                    } else {
                        //Not recognised so leave it alone
                        continue;
                    }
                    //Hash the decoded data, not the URL so that the same data-URI image used in multiple places
                    //will only be embedded once, even if it used a different encoding
                    $cid = hash('sha256', $data) . '@phpmailer.0'; // RFC2392 S 2
                    if (!$this->cidExists($cid)) {
                        $this->addStringEmbeddedImage($data, $cid, 'embed' . $imgindex, static::ENCODING_BASE64, $match[1]);
                    }
                    $message = str_replace(
                        $images[0][$imgindex],
                        $images[1][$imgindex] . '="cid:' . $cid . '"',
                        $message
                    );
                    continue;
                }
                if (// Only process relative URLs if a basedir is provided (i.e. no absolute local paths)
                    !empty($basedir)
                    // Ignore URLs containing parent dir traversal (..)
                    and (strpos($url, '..') === false)
                    // Do not change urls that are already inline images
                    and 0 !== strpos($url, 'cid:')
                    // Do not change absolute URLs, including anonymous protocol
                    and !preg_match('#^[a-z][a-z0-9+.-]*:?//#i', $url)
                ) {
                    $filename = basename($url);
                    $directory = dirname($url);
                    if ('.' == $directory) {
                        $directory = '';
                    }
                    $cid = hash('sha256', $url) . '@phpmailer.0'; // RFC2392 S 2
                    if (strlen($basedir) > 1 and '/' != substr($basedir, -1)) {
                        $basedir .= '/';
                    }
                    if (strlen($directory) > 1 and '/' != substr($directory, -1)) {
                        $directory .= '/';
                    }
                    if ($this->addEmbeddedImage(
                        $basedir . $directory . $filename,
                        $cid,
                        $filename,
                        static::ENCODING_BASE64,
                        static::_mime_types((string) static::mb_pathinfo($filename, PATHINFO_EXTENSION))
                    )
                    ) {
                        $message = preg_replace(
                            '/' . $images[1][$imgindex] . '=["\']' . preg_quote($url, '/') . '["\']/Ui',
                            $images[1][$imgindex] . '="cid:' . $cid . '"',
                            $message
                        );
                    }
                }
            }
        }
        $this->isHTML(true);
        // Convert all message body line breaks to LE, makes quoted-printable encoding work much better
        $this->Body = static::normalizeBreaks($message);
        $this->AltBody = static::normalizeBreaks($this->html2text($message, $advanced));
        if (!$this->alternativeExists()) {
            $this->AltBody = 'This is an HTML-only message. To view it, activate HTML in your email application.'
                . static::$LE;
        }
        return $this->Body;
    }
    /**
     * Convert an HTML string into plain text.
     * This is used by msgHTML().
     * Note - older versions of this function used a bundled advanced converter
     * which was removed for license reasons in #232.
     * Example usage:
     *
     * ```php
     * // Use default conversion
     * $plain = $mail->html2text($html);
     * // Use your own custom converter
     * $plain = $mail->html2text($html, function($html) {
     *     $converter = new MyHtml2text($html);
     *     return $converter->get_text();
     * });
     * ```
     *
     * @param string        $html     The HTML text to convert
     * @param bool|callable $advanced Any boolean value to use the internal converter,
     *                                or provide your own callable for custom conversion
     *
     * @return string
     */
    public function html2text($html, $advanced = false)
    {
        if (is_callable($advanced)) {
            return call_user_func($advanced, $html);
        }
        return html_entity_decode(
            trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/si', '', $html))),
            ENT_QUOTES,
            $this->CharSet
        );
    }
    /**
     * Get the MIME type for a file extension.
     *
     * @param string $ext File extension
     *
     * @return string MIME type of file
     */
    public static function _mime_types($ext = '')
    {
        $mimes = [
            'xl' => 'application/excel',
            'js' => 'application/javascript',
            'hqx' => 'application/mac-binhex40',
            'cpt' => 'application/mac-compactpro',
            'bin' => 'application/macbinary',
            'doc' => 'application/msword',
            'word' => 'application/msword',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'class' => 'application/octet-stream',
            'dll' => 'application/octet-stream',
            'dms' => 'application/octet-stream',
            'exe' => 'application/octet-stream',
            'lha' => 'application/octet-stream',
            'lzh' => 'application/octet-stream',
            'psd' => 'application/octet-stream',
            'sea' => 'application/octet-stream',
            'so' => 'application/octet-stream',
            'oda' => 'application/oda',
            'pdf' => 'application/pdf',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            'smi' => 'application/smil',
            'smil' => 'application/smil',
            'mif' => 'application/vnd.mif',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'wbxml' => 'application/vnd.wap.wbxml',
            'wmlc' => 'application/vnd.wap.wmlc',
            'dcr' => 'application/x-director',
            'dir' => 'application/x-director',
            'dxr' => 'application/x-director',
            'dvi' => 'application/x-dvi',
            'gtar' => 'application/x-gtar',
            'php3' => 'application/x-httpd-php',
            'php4' => 'application/x-httpd-php',
            'php' => 'application/x-httpd-php',
            'phtml' => 'application/x-httpd-php',
            'phps' => 'application/x-httpd-php-source',
            'swf' => 'application/x-shockwave-flash',
            'sit' => 'application/x-stuffit',
            'tar' => 'application/x-tar',
            'tgz' => 'application/x-tar',
            'xht' => 'application/xhtml+xml',
            'xhtml' => 'application/xhtml+xml',
            'zip' => 'application/zip',
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'mp2' => 'audio/mpeg',
            'mp3' => 'audio/mpeg',
            'm4a' => 'audio/mp4',
            'mpga' => 'audio/mpeg',
            'aif' => 'audio/x-aiff',
            'aifc' => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'ram' => 'audio/x-pn-realaudio',
            'rm' => 'audio/x-pn-realaudio',
            'rpm' => 'audio/x-pn-realaudio-plugin',
            'ra' => 'audio/x-realaudio',
            'wav' => 'audio/x-wav',
            'mka' => 'audio/x-matroska',
            'bmp' => 'image/bmp',
            'gif' => 'image/gif',
            'jpeg' => 'image/jpeg',
            'jpe' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'webp' => 'image/webp',
            'heif' => 'image/heif',
            'heifs' => 'image/heif-sequence',
            'heic' => 'image/heic',
            'heics' => 'image/heic-sequence',
            'eml' => 'message/rfc822',
            'css' => 'text/css',
            'html' => 'text/html',
            'htm' => 'text/html',
            'shtml' => 'text/html',
            'log' => 'text/plain',
            'text' => 'text/plain',
            'txt' => 'text/plain',
            'rtx' => 'text/richtext',
            'rtf' => 'text/rtf',
            'vcf' => 'text/vcard',
            'vcard' => 'text/vcard',
            'ics' => 'text/calendar',
            'xml' => 'text/xml',
            'xsl' => 'text/xml',
            'wmv' => 'video/x-ms-wmv',
            'mpeg' => 'video/mpeg',
            'mpe' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mp4' => 'video/mp4',
            'm4v' => 'video/mp4',
            'mov' => 'video/quicktime',
            'qt' => 'video/quicktime',
            'rv' => 'video/vnd.rn-realvideo',
            'avi' => 'video/x-msvideo',
            'movie' => 'video/x-sgi-movie',
            'webm' => 'video/webm',
            'mkv' => 'video/x-matroska',
        ];
        $ext = strtolower($ext);
        if (array_key_exists($ext, $mimes)) {
            return $mimes[$ext];
        }
        return 'application/octet-stream';
    }
    /**
     * Map a file name to a MIME type.
     * Defaults to 'application/octet-stream', i.e.. arbitrary binary data.
     *
     * @param string $filename A file name or full path, does not need to exist as a file
     *
     * @return string
     */
    public static function filenameToType($filename)
    {
        // In case the path is a URL, strip any query string before getting extension
        $qpos = strpos($filename, '?');
        if (false !== $qpos) {
            $filename = substr($filename, 0, $qpos);
        }
        $ext = static::mb_pathinfo($filename, PATHINFO_EXTENSION);
        return static::_mime_types($ext);
    }
    /**
     * Multi-byte-safe pathinfo replacement.
     * Drop-in replacement for pathinfo(), but multibyte- and cross-platform-safe.
     *
     * @see    http://www.php.net/manual/en/function.pathinfo.php#107461
     *
     * @param string     $path    A filename or path, does not need to exist as a file
     * @param int|string $options Either a PATHINFO_* constant,
     *                            or a string name to return only the specified piece
     *
     * @return string|array
     */
    public static function mb_pathinfo($path, $options = null)
    {
        $ret = ['dirname' => '', 'basename' => '', 'extension' => '', 'filename' => ''];
        $pathinfo = [];
        if (preg_match('#^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^\.\\\\/]+?)|))[\\\\/\.]*$#im', $path, $pathinfo)) {
            if (array_key_exists(1, $pathinfo)) {
                $ret['dirname'] = $pathinfo[1];
            }
            if (array_key_exists(2, $pathinfo)) {
                $ret['basename'] = $pathinfo[2];
            }
            if (array_key_exists(5, $pathinfo)) {
                $ret['extension'] = $pathinfo[5];
            }
            if (array_key_exists(3, $pathinfo)) {
                $ret['filename'] = $pathinfo[3];
            }
        }
        switch ($options) {
            case PATHINFO_DIRNAME:
            case 'dirname':
                return $ret['dirname'];
            case PATHINFO_BASENAME:
            case 'basename':
                return $ret['basename'];
            case PATHINFO_EXTENSION:
            case 'extension':
                return $ret['extension'];
            case PATHINFO_FILENAME:
            case 'filename':
                return $ret['filename'];
            default:
                return $ret;
        }
    }
    /**
     * Set or reset instance properties.
     * You should avoid this function - it's more verbose, less efficient, more error-prone and
     * harder to debug than setting properties directly.
     * Usage Example:
     * `$mail->set('SMTPSecure', 'tls');`
     *   is the same as:
     * `$mail->SMTPSecure = 'tls';`.
     *
     * @param string $name  The property name to set
     * @param mixed  $value The value to set the property to
     *
     * @return bool
     */
    public function set($name, $value = '')
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
            return true;
        }
        $this->setError($this->lang('variable_set') . $name);
        return false;
    }
    /**
     * Strip newlines to prevent header injection.
     *
     * @param string $str
     *
     * @return string
     */
    public function secureHeader($str)
    {
        return trim(str_replace(["\r", "\n"], '', $str));
    }
    /**
     * Normalize line breaks in a string.
     * Converts UNIX LF, Mac CR and Windows CRLF line breaks into a single line break format.
     * Defaults to CRLF (for message bodies) and preserves consecutive breaks.
     *
     * @param string $text
     * @param string $breaktype What kind of line break to use; defaults to static::$LE
     *
     * @return string
     */
    public static function normalizeBreaks($text, $breaktype = null)
    {
        if (null === $breaktype) {
            $breaktype = static::$LE;
        }
        // Normalise to \n
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        // Now convert LE as needed
        if ("\n" !== $breaktype) {
            $text = str_replace("\n", $breaktype, $text);
        }
        return $text;
    }
    /**
     * Return the current line break format string.
     *
     * @return string
     */
    public static function getLE()
    {
        return static::$LE;
    }
    /**
     * Set the line break format string, e.g. "\r\n".
     *
     * @param string $le
     */
    protected static function setLE($le)
    {
        static::$LE = $le;
    }
    /**
     * Set the public and private key files and password for S/MIME signing.
     *
     * @param string $cert_filename
     * @param string $key_filename
     * @param string $key_pass            Password for private key
     * @param string $extracerts_filename Optional path to chain certificate
     */
    public function sign($cert_filename, $key_filename, $key_pass, $extracerts_filename = '')
    {
        $this->sign_cert_file = $cert_filename;
        $this->sign_key_file = $key_filename;
        $this->sign_key_pass = $key_pass;
        $this->sign_extracerts_file = $extracerts_filename;
    }
    /**
     * Quoted-Printable-encode a DKIM header.
     *
     * @param string $txt
     *
     * @return string
     */
    public function DKIM_QP($txt)
    {
        $line = '';
        $len = strlen($txt);
        for ($i = 0; $i < $len; ++$i) {
            $ord = ord($txt[$i]);
            if (((0x21 <= $ord) and ($ord <= 0x3A)) or $ord == 0x3C or ((0x3E <= $ord) and ($ord <= 0x7E))) {
                $line .= $txt[$i];
            } else {
                $line .= '=' . sprintf('%02X', $ord);
            }
        }
        return $line;
    }
    /**
     * Generate a DKIM signature.
     *
     * @param string $signHeader
     *
     * @throws Exception
     *
     * @return string The DKIM signature value
     */
    public function DKIM_Sign($signHeader)
    {
        if (!defined('PKCS7_TEXT')) {
            if ($this->exceptions) {
                throw new Exception($this->lang('extension_missing') . 'openssl');
            }
            return '';
        }
        $privKeyStr = !empty($this->DKIM_private_string) ?
            $this->DKIM_private_string :
            file_get_contents($this->DKIM_private);
        if ('' != $this->DKIM_passphrase) {
            $privKey = openssl_pkey_get_private($privKeyStr, $this->DKIM_passphrase);
        } else {
            $privKey = openssl_pkey_get_private($privKeyStr);
        }
        if (openssl_sign($signHeader, $signature, $privKey, 'sha256WithRSAEncryption')) {
            openssl_pkey_free($privKey);
            return base64_encode($signature);
        }
        openssl_pkey_free($privKey);
        return '';
    }
    /**
     * Generate a DKIM canonicalization header.
     * Uses the 'relaxed' algorithm from RFC6376 section 3.4.2.
     * Canonicalized headers should *always* use CRLF, regardless of mailer setting.
     *
     * @see    https://tools.ietf.org/html/rfc6376#section-3.4.2
     *
     * @param string $signHeader Header
     *
     * @return string
     */
    public function DKIM_HeaderC($signHeader)
    {
        //Unfold all header continuation lines
        //Also collapses folded whitespace.
        //Note PCRE \s is too broad a definition of whitespace; RFC5322 defines it as `[ \t]`
        //@see https://tools.ietf.org/html/rfc5322#section-2.2
        //That means this may break if you do something daft like put vertical tabs in your headers.
        $signHeader = preg_replace('/\r\n[ \t]+/', ' ', $signHeader);
        $lines = explode("\r\n", $signHeader);
        foreach ($lines as $key => $line) {
            //If the header is missing a :, skip it as it's invalid
            //This is likely to happen because the explode() above will also split
            //on the trailing LE, leaving an empty line
            if (strpos($line, ':') === false) {
                continue;
            }
            list($heading, $value) = explode(':', $line, 2);
            //Lower-case header name
            $heading = strtolower($heading);
            //Collapse white space within the value
            $value = preg_replace('/[ \t]{2,}/', ' ', $value);
            //RFC6376 is slightly unclear here - it says to delete space at the *end* of each value
            //But then says to delete space before and after the colon.
            //Net result is the same as trimming both ends of the value.
            //by elimination, the same applies to the field name
            $lines[$key] = trim($heading, " \t") . ':' . trim($value, " \t");
        }
        return implode("\r\n", $lines);
    }
    /**
     * Generate a DKIM canonicalization body.
     * Uses the 'simple' algorithm from RFC6376 section 3.4.3.
     * Canonicalized bodies should *always* use CRLF, regardless of mailer setting.
     *
     * @see    https://tools.ietf.org/html/rfc6376#section-3.4.3
     *
     * @param string $body Message Body
     *
     * @return string
     */
    public function DKIM_BodyC($body)
    {
        if (empty($body)) {
            return "\r\n";
        }
        // Normalize line endings to CRLF
        $body = static::normalizeBreaks($body, "\r\n");
        //Reduce multiple trailing line breaks to a single one
        return rtrim($body, "\r\n") . "\r\n";
    }
    /**
     * Create the DKIM header and body in a new message header.
     *
     * @param string $headers_line Header lines
     * @param string $subject      Subject
     * @param string $body         Body
     *
     * @return string
     */
    public function DKIM_Add($headers_line, $subject, $body)
    {
        $DKIMsignatureType = 'rsa-sha256'; // Signature & hash algorithms
        $DKIMcanonicalization = 'relaxed/simple'; // Canonicalization of header/body
        $DKIMquery = 'dns/txt'; // Query method
        $DKIMtime = time(); // Signature Timestamp = seconds since 00:00:00 - Jan 1, 1970 (UTC time zone)
        $subject_header = "Subject: $subject";
        $headers = explode(static::$LE, $headers_line);
        $from_header = '';
        $to_header = '';
        $date_header = '';
        $current = '';
        $copiedHeaderFields = '';
        $foundExtraHeaders = [];
        $extraHeaderKeys = '';
        $extraHeaderValues = '';
        $extraCopyHeaderFields = '';
        foreach ($headers as $header) {
            if (strpos($header, 'From:') === 0) {
                $from_header = $header;
                $current = 'from_header';
            } elseif (strpos($header, 'To:') === 0) {
                $to_header = $header;
                $current = 'to_header';
            } elseif (strpos($header, 'Date:') === 0) {
                $date_header = $header;
                $current = 'date_header';
            } elseif (!empty($this->DKIM_extraHeaders)) {
                foreach ($this->DKIM_extraHeaders as $extraHeader) {
                    if (strpos($header, $extraHeader . ':') === 0) {
                        $headerValue = $header;
                        foreach ($this->CustomHeader as $customHeader) {
                            if ($customHeader[0] === $extraHeader) {
                                $headerValue = trim($customHeader[0]) .
                                    ': ' .
                                    $this->encodeHeader(trim($customHeader[1]));
                                break;
                            }
                        }
                        $foundExtraHeaders[$extraHeader] = $headerValue;
                        $current = '';
                        break;
                    }
                }
            } else {
                if (!empty($$current) and strpos($header, ' =?') === 0) {
                    $$current .= $header;
                } else {
                    $current = '';
                }
            }
        }
        foreach ($foundExtraHeaders as $key => $value) {
            $extraHeaderKeys .= ':' . $key;
            $extraHeaderValues .= $value . "\r\n";
            if ($this->DKIM_copyHeaderFields) {
                $extraCopyHeaderFields .= "\t|" . str_replace('|', '=7C', $this->DKIM_QP($value)) . ";\r\n";
            }
        }
        if ($this->DKIM_copyHeaderFields) {
            $from = str_replace('|', '=7C', $this->DKIM_QP($from_header));
            $to = str_replace('|', '=7C', $this->DKIM_QP($to_header));
            $date = str_replace('|', '=7C', $this->DKIM_QP($date_header));
            $subject = str_replace('|', '=7C', $this->DKIM_QP($subject_header));
            $copiedHeaderFields = "\tz=$from\r\n" .
                "\t|$to\r\n" .
                "\t|$date\r\n" .
                "\t|$subject;\r\n" .
                $extraCopyHeaderFields;
        }
        $body = $this->DKIM_BodyC($body);
        $DKIMlen = strlen($body); // Length of body
        $DKIMb64 = base64_encode(pack('H*', hash('sha256', $body))); // Base64 of packed binary SHA-256 hash of body
        if ('' == $this->DKIM_identity) {
            $ident = '';
        } else {
            $ident = ' i=' . $this->DKIM_identity . ';';
        }
        $dkimhdrs = 'DKIM-Signature: v=1; a=' .
            $DKIMsignatureType . '; q=' .
            $DKIMquery . '; l=' .
            $DKIMlen . '; s=' .
            $this->DKIM_selector .
            ";\r\n" .
            "\tt=" . $DKIMtime . '; c=' . $DKIMcanonicalization . ";\r\n" .
            "\th=From:To:Date:Subject" . $extraHeaderKeys . ";\r\n" .
            "\td=" . $this->DKIM_domain . ';' . $ident . "\r\n" .
            $copiedHeaderFields .
            "\tbh=" . $DKIMb64 . ";\r\n" .
            "\tb=";
        $toSign = $this->DKIM_HeaderC(
            $from_header . "\r\n" .
            $to_header . "\r\n" .
            $date_header . "\r\n" .
            $subject_header . "\r\n" .
            $extraHeaderValues .
            $dkimhdrs
        );
        $signed = $this->DKIM_Sign($toSign);
        return static::normalizeBreaks($dkimhdrs . $signed) . static::$LE;
    }
    /**
     * Detect if a string contains a line longer than the maximum line length
     * allowed by RFC 2822 section 2.1.1.
     *
     * @param string $str
     *
     * @return bool
     */
    public static function hasLineLongerThanMax($str)
    {
        return (bool) preg_match('/^(.{' . (self::MAX_LINE_LENGTH + strlen(static::$LE)) . ',})/m', $str);
    }
    /**
     * Allows for public read access to 'to' property.
     * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
     *
     * @return array
     */
    public function getToAddresses()
    {
        return $this->to;
    }
    /**
     * Allows for public read access to 'cc' property.
     * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
     *
     * @return array
     */
    public function getCcAddresses()
    {
        return $this->cc;
    }
    /**
     * Allows for public read access to 'bcc' property.
     * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
     *
     * @return array
     */
    public function getBccAddresses()
    {
        return $this->bcc;
    }
    /**
     * Allows for public read access to 'ReplyTo' property.
     * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
     *
     * @return array
     */
    public function getReplyToAddresses()
    {
        return $this->ReplyTo;
    }
    /**
     * Allows for public read access to 'all_recipients' property.
     * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
     *
     * @return array
     */
    public function getAllRecipientAddresses()
    {
        return $this->all_recipients;
    }
    /**
     * Perform a callback.
     *
     * @param bool   $isSent
     * @param array  $to
     * @param array  $cc
     * @param array  $bcc
     * @param string $subject
     * @param string $body
     * @param string $from
     * @param array  $extra
     */
    protected function doCallback($isSent, $to, $cc, $bcc, $subject, $body, $from, $extra)
    {
        if (!empty($this->action_function) and is_callable($this->action_function)) {
            call_user_func($this->action_function, $isSent, $to, $cc, $bcc, $subject, $body, $from, $extra);
        }
    }
    /**
     * Get the OAuth instance.
     *
     * @return OAuth
     */
    public function getOAuth()
    {
        return $this->oauth;
    }
    /**
     * Set an OAuth instance.
     *
     * @param OAuth $oauth
     */
    public function setOAuth(OAuth $oauth)
    {
        $this->oauth = $oauth;
    }
}
class SMTP
{
    /**
     * The PHPMailer SMTP version number.
     *
     * @var string
     */
    const VERSION = '6.0.6';
    /**
     * SMTP line break constant.
     *
     * @var string
     */
    const LE = "\r\n";
    /**
     * The SMTP port to use if one is not specified.
     *
     * @var int
     */
    const DEFAULT_PORT = 25;
    /**
     * The maximum line length allowed by RFC 2822 section 2.1.1.
     *
     * @var int
     */
    const MAX_LINE_LENGTH = 998;
    /**
     * Debug level for no output.
     */
    const DEBUG_OFF = 0;
    /**
     * Debug level to show client -> server messages.
     */
    const DEBUG_CLIENT = 1;
    /**
     * Debug level to show client -> server and server -> client messages.
     */
    const DEBUG_SERVER = 2;
    /**
     * Debug level to show connection status, client -> server and server -> client messages.
     */
    const DEBUG_CONNECTION = 3;
    /**
     * Debug level to show all messages.
     */
    const DEBUG_LOWLEVEL = 4;
    /**
     * Debug output level.
     * Options:
     * * self::DEBUG_OFF (`0`) No debug output, default
     * * self::DEBUG_CLIENT (`1`) Client commands
     * * self::DEBUG_SERVER (`2`) Client commands and server responses
     * * self::DEBUG_CONNECTION (`3`) As DEBUG_SERVER plus connection status
     * * self::DEBUG_LOWLEVEL (`4`) Low-level data output, all messages.
     *
     * @var int
     */
    public $do_debug = self::DEBUG_OFF;
    /**
     * How to handle debug output.
     * Options:
     * * `echo` Output plain-text as-is, appropriate for CLI
     * * `html` Output escaped, line breaks converted to `<br>`, appropriate for browser output
     * * `error_log` Output to error log as configured in php.ini
     * Alternatively, you can provide a callable expecting two params: a message string and the debug level:
     *
     * ```php
     * $smtp->Debugoutput = function($str, $level) {echo "debug level $level; message: $str";};
     * ```
     *
     * Alternatively, you can pass in an instance of a PSR-3 compatible logger, though only `debug`
     * level output is used:
     *
     * ```php
     * $mail->Debugoutput = new myPsr3Logger;
     * ```
     *
     * @var string|callable|\Psr\Log\LoggerInterface
     */
    public $Debugoutput = 'echo';
    /**
     * Whether to use VERP.
     *
     * @see http://en.wikipedia.org/wiki/Variable_envelope_return_path
     * @see http://www.postfix.org/VERP_README.html Info on VERP
     *
     * @var bool
     */
    public $do_verp = false;
    /**
     * The timeout value for connection, in seconds.
     * Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2.
     * This needs to be quite high to function correctly with hosts using greetdelay as an anti-spam measure.
     *
     * @see http://tools.ietf.org/html/rfc2821#section-4.5.3.2
     *
     * @var int
     */
    public $Timeout = 300;
    /**
     * How long to wait for commands to complete, in seconds.
     * Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2.
     *
     * @var int
     */
    public $Timelimit = 300;
    /**
     * Patterns to extract an SMTP transaction id from reply to a DATA command.
     * The first capture group in each regex will be used as the ID.
     * MS ESMTP returns the message ID, which may not be correct for internal tracking.
     *
     * @var string[]
     */
    protected $smtp_transaction_id_patterns = [
        'exim' => '/[\d]{3} OK id=(.*)/',
        'sendmail' => '/[\d]{3} 2.0.0 (.*) Message/',
        'postfix' => '/[\d]{3} 2.0.0 Ok: queued as (.*)/',
        'Microsoft_ESMTP' => '/[0-9]{3} 2.[\d].0 (.*)@(?:.*) Queued mail for delivery/',
        'Amazon_SES' => '/[\d]{3} Ok (.*)/',
        'SendGrid' => '/[\d]{3} Ok: queued as (.*)/',
        'CampaignMonitor' => '/[\d]{3} 2.0.0 OK:([a-zA-Z\d]{48})/',
    ];
    /**
     * The last transaction ID issued in response to a DATA command,
     * if one was detected.
     *
     * @var string|bool|null
     */
    protected $last_smtp_transaction_id;
    /**
     * The socket for the server connection.
     *
     * @var ?resource
     */
    protected $smtp_conn;
    /**
     * Error information, if any, for the last SMTP command.
     *
     * @var array
     */
    protected $error = [
        'error' => '',
        'detail' => '',
        'smtp_code' => '',
        'smtp_code_ex' => '',
    ];
    /**
     * The reply the server sent to us for HELO.
     * If null, no HELO string has yet been received.
     *
     * @var string|null
     */
    protected $helo_rply = null;
    /**
     * The set of SMTP extensions sent in reply to EHLO command.
     * Indexes of the array are extension names.
     * Value at index 'HELO' or 'EHLO' (according to command that was sent)
     * represents the server name. In case of HELO it is the only element of the array.
     * Other values can be boolean TRUE or an array containing extension options.
     * If null, no HELO/EHLO string has yet been received.
     *
     * @var array|null
     */
    protected $server_caps = null;
    /**
     * The most recent reply received from the server.
     *
     * @var string
     */
    protected $last_reply = '';
    /**
     * Output debugging info via a user-selected method.
     *
     * @param string $str   Debug string to output
     * @param int    $level The debug level of this message; see DEBUG_* constants
     *
     * @see SMTP::$Debugoutput
     * @see SMTP::$do_debug
     */
    protected function edebug($str, $level = 0)
    {
        if ($level > $this->do_debug) {
            return;
        }
        //Is this a PSR-3 logger?
        if ($this->Debugoutput instanceof \Psr\Log\LoggerInterface) {
            $this->Debugoutput->debug($str);
            return;
        }
        //Avoid clash with built-in function names
        if (!in_array($this->Debugoutput, ['error_log', 'html', 'echo']) and is_callable($this->Debugoutput)) {
            call_user_func($this->Debugoutput, $str, $level);
            return;
        }
        switch ($this->Debugoutput) {
            case 'error_log':
                //Don't output, just log
                error_log($str);
                break;
            case 'html':
                //Cleans up output a bit for a better looking, HTML-safe output
                echo gmdate('Y-m-d H:i:s'), ' ', htmlentities(
                    preg_replace('/[\r\n]+/', '', $str),
                    ENT_QUOTES,
                    'UTF-8'
                ), "<br>\n";
                break;
            case 'echo':
            default:
                //Normalize line breaks
                $str = preg_replace('/\r\n|\r/ms', "\n", $str);
                echo gmdate('Y-m-d H:i:s'),
                "\t",
                    //Trim trailing space
                trim(
                //Indent for readability, except for trailing break
                    str_replace(
                        "\n",
                        "\n                   \t                  ",
                        trim($str)
                    )
                ),
                "\n";
        }
    }
    /**
     * Connect to an SMTP server.
     *
     * @param string $host    SMTP server IP or host name
     * @param int    $port    The port number to connect to
     * @param int    $timeout How long to wait for the connection to open
     * @param array  $options An array of options for stream_context_create()
     *
     * @return bool
     */
    public function connect($host, $port = null, $timeout = 30, $options = [])
    {
        static $streamok;
        //This is enabled by default since 5.0.0 but some providers disable it
        //Check this once and cache the result
        if (null === $streamok) {
            $streamok = function_exists('stream_socket_client');
        }
        // Clear errors to avoid confusion
        $this->setError('');
        // Make sure we are __not__ connected
        if ($this->connected()) {
            // Already connected, generate error
            $this->setError('Already connected to a server');
            return false;
        }
        if (empty($port)) {
            $port = self::DEFAULT_PORT;
        }
        // Connect to the SMTP server
        $this->edebug(
            "Connection: opening to $host:$port, timeout=$timeout, options=" .
            (count($options) > 0 ? var_export($options, true) : 'array()'),
            self::DEBUG_CONNECTION
        );
        $errno = 0;
        $errstr = '';
        if ($streamok) {
            $socket_context = stream_context_create($options);
            set_error_handler([$this, 'errorHandler']);
            $this->smtp_conn = stream_socket_client(
                $host . ':' . $port,
                $errno,
                $errstr,
                $timeout,
                STREAM_CLIENT_CONNECT,
                $socket_context
            );
            restore_error_handler();
        } else {
            //Fall back to fsockopen which should work in more places, but is missing some features
            $this->edebug(
                'Connection: stream_socket_client not available, falling back to fsockopen',
                self::DEBUG_CONNECTION
            );
            set_error_handler([$this, 'errorHandler']);
            $this->smtp_conn = fsockopen(
                $host,
                $port,
                $errno,
                $errstr,
                $timeout
            );
            restore_error_handler();
        }
        // Verify we connected properly
        if (!is_resource($this->smtp_conn)) {
            $this->setError(
                'Failed to connect to server',
                '',
                (string) $errno,
                (string) $errstr
            );
            $this->edebug(
                'SMTP ERROR: ' . $this->error['error']
                . ": $errstr ($errno)",
                self::DEBUG_CLIENT
            );
            return false;
        }
        $this->edebug('Connection: opened', self::DEBUG_CONNECTION);
        // SMTP server can take longer to respond, give longer timeout for first read
        // Windows does not have support for this timeout function
        if (substr(PHP_OS, 0, 3) != 'WIN') {
            $max = ini_get('max_execution_time');
            // Don't bother if unlimited
            if (0 != $max and $timeout > $max) {
                @set_time_limit($timeout);
            }
            stream_set_timeout($this->smtp_conn, $timeout, 0);
        }
        // Get any announcement
        $announce = $this->get_lines();
        $this->edebug('SERVER -> CLIENT: ' . $announce, self::DEBUG_SERVER);
        return true;
    }
    /**
     * Initiate a TLS (encrypted) session.
     *
     * @return bool
     */
    public function startTLS()
    {
        if (!$this->sendCommand('STARTTLS', 'STARTTLS', 220)) {
            return false;
        }
        //Allow the best TLS version(s) we can
        $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;
        //PHP 5.6.7 dropped inclusion of TLS 1.1 and 1.2 in STREAM_CRYPTO_METHOD_TLS_CLIENT
        //so add them back in manually if we can
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
        }
        // Begin encrypted connection
        set_error_handler([$this, 'errorHandler']);
        $crypto_ok = stream_socket_enable_crypto(
            $this->smtp_conn,
            true,
            $crypto_method
        );
        restore_error_handler();
        return (bool) $crypto_ok;
    }
    /**
     * Perform SMTP authentication.
     * Must be run after hello().
     *
     * @see    hello()
     *
     * @param string $username The user name
     * @param string $password The password
     * @param string $authtype The auth type (CRAM-MD5, PLAIN, LOGIN, XOAUTH2)
     * @param OAuth  $OAuth    An optional OAuth instance for XOAUTH2 authentication
     *
     * @return bool True if successfully authenticated
     */
    public function authenticate(
        $username,
        $password,
        $authtype = null,
        $OAuth = null
    ) {
        if (!$this->server_caps) {
            $this->setError('Authentication is not allowed before HELO/EHLO');
            return false;
        }
        if (array_key_exists('EHLO', $this->server_caps)) {
            // SMTP extensions are available; try to find a proper authentication method
            if (!array_key_exists('AUTH', $this->server_caps)) {
                $this->setError('Authentication is not allowed at this stage');
                // 'at this stage' means that auth may be allowed after the stage changes
                // e.g. after STARTTLS
                return false;
            }
            $this->edebug('Auth method requested: ' . ($authtype ? $authtype : 'UNSPECIFIED'), self::DEBUG_LOWLEVEL);
            $this->edebug(
                'Auth methods available on the server: ' . implode(',', $this->server_caps['AUTH']),
                self::DEBUG_LOWLEVEL
            );
            //If we have requested a specific auth type, check the server supports it before trying others
            if (null !== $authtype and !in_array($authtype, $this->server_caps['AUTH'])) {
                $this->edebug('Requested auth method not available: ' . $authtype, self::DEBUG_LOWLEVEL);
                $authtype = null;
            }
            if (empty($authtype)) {
                //If no auth mechanism is specified, attempt to use these, in this order
                //Try CRAM-MD5 first as it's more secure than the others
                foreach (['CRAM-MD5', 'LOGIN', 'PLAIN', 'XOAUTH2'] as $method) {
                    if (in_array($method, $this->server_caps['AUTH'])) {
                        $authtype = $method;
                        break;
                    }
                }
                if (empty($authtype)) {
                    $this->setError('No supported authentication methods found');
                    return false;
                }
                self::edebug('Auth method selected: ' . $authtype, self::DEBUG_LOWLEVEL);
            }
            if (!in_array($authtype, $this->server_caps['AUTH'])) {
                $this->setError("The requested authentication method \"$authtype\" is not supported by the server");
                return false;
            }
        } elseif (empty($authtype)) {
            $authtype = 'LOGIN';
        }
        switch ($authtype) {
            case 'PLAIN':
                // Start authentication
                if (!$this->sendCommand('AUTH', 'AUTH PLAIN', 334)) {
                    return false;
                }
                // Send encoded username and password
                if (!$this->sendCommand(
                    'User & Password',
                    base64_encode("\0" . $username . "\0" . $password),
                    235
                )
                ) {
                    return false;
                }
                break;
            case 'LOGIN':
                // Start authentication
                if (!$this->sendCommand('AUTH', 'AUTH LOGIN', 334)) {
                    return false;
                }
                if (!$this->sendCommand('Username', base64_encode($username), 334)) {
                    return false;
                }
                if (!$this->sendCommand('Password', base64_encode($password), 235)) {
                    return false;
                }
                break;
            case 'CRAM-MD5':
                // Start authentication
                if (!$this->sendCommand('AUTH CRAM-MD5', 'AUTH CRAM-MD5', 334)) {
                    return false;
                }
                // Get the challenge
                $challenge = base64_decode(substr($this->last_reply, 4));
                // Build the response
                $response = $username . ' ' . $this->hmac($challenge, $password);
                // send encoded credentials
                return $this->sendCommand('Username', base64_encode($response), 235);
            case 'XOAUTH2':
                //The OAuth instance must be set up prior to requesting auth.
                if (null === $OAuth) {
                    return false;
                }
                $oauth = $OAuth->getOauth64();
                // Start authentication
                if (!$this->sendCommand('AUTH', 'AUTH XOAUTH2 ' . $oauth, 235)) {
                    return false;
                }
                break;
            default:
                $this->setError("Authentication method \"$authtype\" is not supported");
                return false;
        }
        return true;
    }
    /**
     * Calculate an MD5 HMAC hash.
     * Works like hash_hmac('md5', $data, $key)
     * in case that function is not available.
     *
     * @param string $data The data to hash
     * @param string $key  The key to hash with
     *
     * @return string
     */
    protected function hmac($data, $key)
    {
        if (function_exists('hash_hmac')) {
            return hash_hmac('md5', $data, $key);
        }
        // The following borrowed from
        // http://php.net/manual/en/function.mhash.php#27225
        // RFC 2104 HMAC implementation for php.
        // Creates an md5 HMAC.
        // Eliminates the need to install mhash to compute a HMAC
        // by Lance Rushing
        $bytelen = 64; // byte length for md5
        if (strlen($key) > $bytelen) {
            $key = pack('H*', md5($key));
        }
        $key = str_pad($key, $bytelen, chr(0x00));
        $ipad = str_pad('', $bytelen, chr(0x36));
        $opad = str_pad('', $bytelen, chr(0x5c));
        $k_ipad = $key ^ $ipad;
        $k_opad = $key ^ $opad;
        return md5($k_opad . pack('H*', md5($k_ipad . $data)));
    }
    /**
     * Check connection state.
     *
     * @return bool True if connected
     */
    public function connected()
    {
        if (is_resource($this->smtp_conn)) {
            $sock_status = stream_get_meta_data($this->smtp_conn);
            if ($sock_status['eof']) {
                // The socket is valid but we are not connected
                $this->edebug(
                    'SMTP NOTICE: EOF caught while checking if connected',
                    self::DEBUG_CLIENT
                );
                $this->close();
                return false;
            }
            return true; // everything looks good
        }
        return false;
    }
    /**
     * Close the socket and clean up the state of the class.
     * Don't use this function without first trying to use QUIT.
     *
     * @see quit()
     */
    public function close()
    {
        $this->setError('');
        $this->server_caps = null;
        $this->helo_rply = null;
        if (is_resource($this->smtp_conn)) {
            // close the connection and cleanup
            fclose($this->smtp_conn);
            $this->smtp_conn = null; //Makes for cleaner serialization
            $this->edebug('Connection: closed', self::DEBUG_CONNECTION);
        }
    }
    /**
     * Send an SMTP DATA command.
     * Issues a data command and sends the msg_data to the server,
     * finializing the mail transaction. $msg_data is the message
     * that is to be send with the headers. Each header needs to be
     * on a single line followed by a <CRLF> with the message headers
     * and the message body being separated by an additional <CRLF>.
     * Implements RFC 821: DATA <CRLF>.
     *
     * @param string $msg_data Message data to send
     *
     * @return bool
     */
    public function data($msg_data)
    {
        //This will use the standard timelimit
        if (!$this->sendCommand('DATA', 'DATA', 354)) {
            return false;
        }
        /* The server is ready to accept data!
         * According to rfc821 we should not send more than 1000 characters on a single line (including the LE)
         * so we will break the data up into lines by \r and/or \n then if needed we will break each of those into
         * smaller lines to fit within the limit.
         * We will also look for lines that start with a '.' and prepend an additional '.'.
         * NOTE: this does not count towards line-length limit.
         */
        // Normalize line breaks before exploding
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $msg_data));
        /* To distinguish between a complete RFC822 message and a plain message body, we check if the first field
         * of the first line (':' separated) does not contain a space then it _should_ be a header and we will
         * process all lines before a blank line as headers.
         */
        $field = substr($lines[0], 0, strpos($lines[0], ':'));
        $in_headers = false;
        if (!empty($field) and strpos($field, ' ') === false) {
            $in_headers = true;
        }
        foreach ($lines as $line) {
            $lines_out = [];
            if ($in_headers and $line == '') {
                $in_headers = false;
            }
            //Break this line up into several smaller lines if it's too long
            //Micro-optimisation: isset($str[$len]) is faster than (strlen($str) > $len),
            while (isset($line[self::MAX_LINE_LENGTH])) {
                //Working backwards, try to find a space within the last MAX_LINE_LENGTH chars of the line to break on
                //so as to avoid breaking in the middle of a word
                $pos = strrpos(substr($line, 0, self::MAX_LINE_LENGTH), ' ');
                //Deliberately matches both false and 0
                if (!$pos) {
                    //No nice break found, add a hard break
                    $pos = self::MAX_LINE_LENGTH - 1;
                    $lines_out[] = substr($line, 0, $pos);
                    $line = substr($line, $pos);
                } else {
                    //Break at the found point
                    $lines_out[] = substr($line, 0, $pos);
                    //Move along by the amount we dealt with
                    $line = substr($line, $pos + 1);
                }
                //If processing headers add a LWSP-char to the front of new line RFC822 section 3.1.1
                if ($in_headers) {
                    $line = "\t" . $line;
                }
            }
            $lines_out[] = $line;
            //Send the lines to the server
            foreach ($lines_out as $line_out) {
                //RFC2821 section 4.5.2
                if (!empty($line_out) and $line_out[0] == '.') {
                    $line_out = '.' . $line_out;
                }
                $this->client_send($line_out . static::LE, 'DATA');
            }
        }
        //Message data has been sent, complete the command
        //Increase timelimit for end of DATA command
        $savetimelimit = $this->Timelimit;
        $this->Timelimit = $this->Timelimit * 2;
        $result = $this->sendCommand('DATA END', '.', 250);
        $this->recordLastTransactionID();
        //Restore timelimit
        $this->Timelimit = $savetimelimit;
        return $result;
    }
    /**
     * Send an SMTP HELO or EHLO command.
     * Used to identify the sending server to the receiving server.
     * This makes sure that client and server are in a known state.
     * Implements RFC 821: HELO <SP> <domain> <CRLF>
     * and RFC 2821 EHLO.
     *
     * @param string $host The host name or IP to connect to
     *
     * @return bool
     */
    public function hello($host = '')
    {
        //Try extended hello first (RFC 2821)
        return $this->sendHello('EHLO', $host) or $this->sendHello('HELO', $host);
    }
    /**
     * Send an SMTP HELO or EHLO command.
     * Low-level implementation used by hello().
     *
     * @param string $hello The HELO string
     * @param string $host  The hostname to say we are
     *
     * @return bool
     *
     * @see    hello()
     */
    protected function sendHello($hello, $host)
    {
        $noerror = $this->sendCommand($hello, $hello . ' ' . $host, 250);
        $this->helo_rply = $this->last_reply;
        if ($noerror) {
            $this->parseHelloFields($hello);
        } else {
            $this->server_caps = null;
        }
        return $noerror;
    }
    /**
     * Parse a reply to HELO/EHLO command to discover server extensions.
     * In case of HELO, the only parameter that can be discovered is a server name.
     *
     * @param string $type `HELO` or `EHLO`
     */
    protected function parseHelloFields($type)
    {
        $this->server_caps = [];
        $lines = explode("\n", $this->helo_rply);
        foreach ($lines as $n => $s) {
            //First 4 chars contain response code followed by - or space
            $s = trim(substr($s, 4));
            if (empty($s)) {
                continue;
            }
            $fields = explode(' ', $s);
            if (!empty($fields)) {
                if (!$n) {
                    $name = $type;
                    $fields = $fields[0];
                } else {
                    $name = array_shift($fields);
                    switch ($name) {
                        case 'SIZE':
                            $fields = ($fields ? $fields[0] : 0);
                            break;
                        case 'AUTH':
                            if (!is_array($fields)) {
                                $fields = [];
                            }
                            break;
                        default:
                            $fields = true;
                    }
                }
                $this->server_caps[$name] = $fields;
            }
        }
    }
    /**
     * Send an SMTP MAIL command.
     * Starts a mail transaction from the email address specified in
     * $from. Returns true if successful or false otherwise. If True
     * the mail transaction is started and then one or more recipient
     * commands may be called followed by a data command.
     * Implements RFC 821: MAIL <SP> FROM:<reverse-path> <CRLF>.
     *
     * @param string $from Source address of this message
     *
     * @return bool
     */
    public function mail($from)
    {
        $useVerp = ($this->do_verp ? ' XVERP' : '');
        return $this->sendCommand(
            'MAIL FROM',
            'MAIL FROM:<' . $from . '>' . $useVerp,
            250
        );
    }
    /**
     * Send an SMTP QUIT command.
     * Closes the socket if there is no error or the $close_on_error argument is true.
     * Implements from RFC 821: QUIT <CRLF>.
     *
     * @param bool $close_on_error Should the connection close if an error occurs?
     *
     * @return bool
     */
    public function quit($close_on_error = true)
    {
        $noerror = $this->sendCommand('QUIT', 'QUIT', 221);
        $err = $this->error; //Save any error
        if ($noerror or $close_on_error) {
            $this->close();
            $this->error = $err; //Restore any error from the quit command
        }
        return $noerror;
    }
    /**
     * Send an SMTP RCPT command.
     * Sets the TO argument to $toaddr.
     * Returns true if the recipient was accepted false if it was rejected.
     * Implements from RFC 821: RCPT <SP> TO:<forward-path> <CRLF>.
     *
     * @param string $address The address the message is being sent to
     *
     * @return bool
     */
    public function recipient($address)
    {
        return $this->sendCommand(
            'RCPT TO',
            'RCPT TO:<' . $address . '>',
            [250, 251]
        );
    }
    /**
     * Send an SMTP RSET command.
     * Abort any transaction that is currently in progress.
     * Implements RFC 821: RSET <CRLF>.
     *
     * @return bool True on success
     */
    public function reset()
    {
        return $this->sendCommand('RSET', 'RSET', 250);
    }
    /**
     * Send a command to an SMTP server and check its return code.
     *
     * @param string    $command       The command name - not sent to the server
     * @param string    $commandstring The actual command to send
     * @param int|array $expect        One or more expected integer success codes
     *
     * @return bool True on success
     */
    protected function sendCommand($command, $commandstring, $expect)
    {
        if (!$this->connected()) {
            $this->setError("Called $command without being connected");
            return false;
        }
        //Reject line breaks in all commands
        if (strpos($commandstring, "\n") !== false or strpos($commandstring, "\r") !== false) {
            $this->setError("Command '$command' contained line breaks");
            return false;
        }
        $this->client_send($commandstring . static::LE, $command);
        $this->last_reply = $this->get_lines();
        // Fetch SMTP code and possible error code explanation
        $matches = [];
        if (preg_match('/^([0-9]{3})[ -](?:([0-9]\\.[0-9]\\.[0-9]) )?/', $this->last_reply, $matches)) {
            $code = $matches[1];
            $code_ex = (count($matches) > 2 ? $matches[2] : null);
            // Cut off error code from each response line
            $detail = preg_replace(
                "/{$code}[ -]" .
                ($code_ex ? str_replace('.', '\\.', $code_ex) . ' ' : '') . '/m',
                '',
                $this->last_reply
            );
        } else {
            // Fall back to simple parsing if regex fails
            $code = substr($this->last_reply, 0, 3);
            $code_ex = null;
            $detail = substr($this->last_reply, 4);
        }
        $this->edebug('SERVER -> CLIENT: ' . $this->last_reply, self::DEBUG_SERVER);
        if (!in_array($code, (array) $expect)) {
            $this->setError(
                "$command command failed",
                $detail,
                $code,
                $code_ex
            );
            $this->edebug(
                'SMTP ERROR: ' . $this->error['error'] . ': ' . $this->last_reply,
                self::DEBUG_CLIENT
            );
            return false;
        }
        $this->setError('');
        return true;
    }
    /**
     * Send an SMTP SAML command.
     * Starts a mail transaction from the email address specified in $from.
     * Returns true if successful or false otherwise. If True
     * the mail transaction is started and then one or more recipient
     * commands may be called followed by a data command. This command
     * will send the message to the users terminal if they are logged
     * in and send them an email.
     * Implements RFC 821: SAML <SP> FROM:<reverse-path> <CRLF>.
     *
     * @param string $from The address the message is from
     *
     * @return bool
     */
    public function sendAndMail($from)
    {
        return $this->sendCommand('SAML', "SAML FROM:$from", 250);
    }
    /**
     * Send an SMTP VRFY command.
     *
     * @param string $name The name to verify
     *
     * @return bool
     */
    public function verify($name)
    {
        return $this->sendCommand('VRFY', "VRFY $name", [250, 251]);
    }
    /**
     * Send an SMTP NOOP command.
     * Used to keep keep-alives alive, doesn't actually do anything.
     *
     * @return bool
     */
    public function noop()
    {
        return $this->sendCommand('NOOP', 'NOOP', 250);
    }
    /**
     * Send an SMTP TURN command.
     * This is an optional command for SMTP that this class does not support.
     * This method is here to make the RFC821 Definition complete for this class
     * and _may_ be implemented in future.
     * Implements from RFC 821: TURN <CRLF>.
     *
     * @return bool
     */
    public function turn()
    {
        $this->setError('The SMTP TURN command is not implemented');
        $this->edebug('SMTP NOTICE: ' . $this->error['error'], self::DEBUG_CLIENT);
        return false;
    }
    /**
     * Send raw data to the server.
     *
     * @param string $data    The data to send
     * @param string $command Optionally, the command this is part of, used only for controlling debug output
     *
     * @return int|bool The number of bytes sent to the server or false on error
     */
    public function client_send($data, $command = '')
    {
        //If SMTP transcripts are left enabled, or debug output is posted online
        //it can leak credentials, so hide credentials in all but lowest level
        if (self::DEBUG_LOWLEVEL > $this->do_debug and
            in_array($command, ['User & Password', 'Username', 'Password'], true)) {
            $this->edebug('CLIENT -> SERVER: <credentials hidden>', self::DEBUG_CLIENT);
        } else {
            $this->edebug('CLIENT -> SERVER: ' . $data, self::DEBUG_CLIENT);
        }
        set_error_handler([$this, 'errorHandler']);
        $result = fwrite($this->smtp_conn, $data);
        restore_error_handler();
        return $result;
    }
    /**
     * Get the latest error.
     *
     * @return array
     */
    public function getError()
    {
        return $this->error;
    }
    /**
     * Get SMTP extensions available on the server.
     *
     * @return array|null
     */
    public function getServerExtList()
    {
        return $this->server_caps;
    }
    /**
     * Get metadata about the SMTP server from its HELO/EHLO response.
     * The method works in three ways, dependent on argument value and current state:
     *   1. HELO/EHLO has not been sent - returns null and populates $this->error.
     *   2. HELO has been sent -
     *     $name == 'HELO': returns server name
     *     $name == 'EHLO': returns boolean false
     *     $name == any other string: returns null and populates $this->error
     *   3. EHLO has been sent -
     *     $name == 'HELO'|'EHLO': returns the server name
     *     $name == any other string: if extension $name exists, returns True
     *       or its options (e.g. AUTH mechanisms supported). Otherwise returns False.
     *
     * @param string $name Name of SMTP extension or 'HELO'|'EHLO'
     *
     * @return mixed
     */
    public function getServerExt($name)
    {
        if (!$this->server_caps) {
            $this->setError('No HELO/EHLO was sent');
            return;
        }
        if (!array_key_exists($name, $this->server_caps)) {
            if ('HELO' == $name) {
                return $this->server_caps['EHLO'];
            }
            if ('EHLO' == $name || array_key_exists('EHLO', $this->server_caps)) {
                return false;
            }
            $this->setError('HELO handshake was used; No information about server extensions available');
            return;
        }
        return $this->server_caps[$name];
    }
    /**
     * Get the last reply from the server.
     *
     * @return string
     */
    public function getLastReply()
    {
        return $this->last_reply;
    }
    /**
     * Read the SMTP server's response.
     * Either before eof or socket timeout occurs on the operation.
     * With SMTP we can tell if we have more lines to read if the
     * 4th character is '-' symbol. If it is a space then we don't
     * need to read anything else.
     *
     * @return string
     */
    protected function get_lines()
    {
        // If the connection is bad, give up straight away
        if (!is_resource($this->smtp_conn)) {
            return '';
        }
        $data = '';
        $endtime = 0;
        stream_set_timeout($this->smtp_conn, $this->Timeout);
        if ($this->Timelimit > 0) {
            $endtime = time() + $this->Timelimit;
        }
        $selR = [$this->smtp_conn];
        $selW = null;
        while (is_resource($this->smtp_conn) and !feof($this->smtp_conn)) {
            //Must pass vars in here as params are by reference
            if (!stream_select($selR, $selW, $selW, $this->Timelimit)) {
                $this->edebug(
                    'SMTP -> get_lines(): timed-out (' . $this->Timeout . ' sec)',
                    self::DEBUG_LOWLEVEL
                );
                break;
            }
            //Deliberate noise suppression - errors are handled afterwards
            $str = @fgets($this->smtp_conn, 515);
            $this->edebug('SMTP INBOUND: "' . trim($str) . '"', self::DEBUG_LOWLEVEL);
            $data .= $str;
            // If response is only 3 chars (not valid, but RFC5321 S4.2 says it must be handled),
            // or 4th character is a space, we are done reading, break the loop,
            // string array access is a micro-optimisation over strlen
            if (!isset($str[3]) or (isset($str[3]) and $str[3] == ' ')) {
                break;
            }
            // Timed-out? Log and break
            $info = stream_get_meta_data($this->smtp_conn);
            if ($info['timed_out']) {
                $this->edebug(
                    'SMTP -> get_lines(): timed-out (' . $this->Timeout . ' sec)',
                    self::DEBUG_LOWLEVEL
                );
                break;
            }
            // Now check if reads took too long
            if ($endtime and time() > $endtime) {
                $this->edebug(
                    'SMTP -> get_lines(): timelimit reached (' .
                    $this->Timelimit . ' sec)',
                    self::DEBUG_LOWLEVEL
                );
                break;
            }
        }
        return $data;
    }
    /**
     * Enable or disable VERP address generation.
     *
     * @param bool $enabled
     */
    public function setVerp($enabled = false)
    {
        $this->do_verp = $enabled;
    }
    /**
     * Get VERP address generation mode.
     *
     * @return bool
     */
    public function getVerp()
    {
        return $this->do_verp;
    }
    /**
     * Set error messages and codes.
     *
     * @param string $message      The error message
     * @param string $detail       Further detail on the error
     * @param string $smtp_code    An associated SMTP error code
     * @param string $smtp_code_ex Extended SMTP code
     */
    protected function setError($message, $detail = '', $smtp_code = '', $smtp_code_ex = '')
    {
        $this->error = [
            'error' => $message,
            'detail' => $detail,
            'smtp_code' => $smtp_code,
            'smtp_code_ex' => $smtp_code_ex,
        ];
    }
    /**
     * Set debug output method.
     *
     * @param string|callable $method The name of the mechanism to use for debugging output, or a callable to handle it
     */
    public function setDebugOutput($method = 'echo')
    {
        $this->Debugoutput = $method;
    }
    /**
     * Get debug output method.
     *
     * @return string
     */
    public function getDebugOutput()
    {
        return $this->Debugoutput;
    }
    /**
     * Set debug output level.
     *
     * @param int $level
     */
    public function setDebugLevel($level = 0)
    {
        $this->do_debug = $level;
    }
    /**
     * Get debug output level.
     *
     * @return int
     */
    public function getDebugLevel()
    {
        return $this->do_debug;
    }
    /**
     * Set SMTP timeout.
     *
     * @param int $timeout The timeout duration in seconds
     */
    public function setTimeout($timeout = 0)
    {
        $this->Timeout = $timeout;
    }
    /**
     * Get SMTP timeout.
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->Timeout;
    }
    /**
     * Reports an error number and string.
     *
     * @param int    $errno   The error number returned by PHP
     * @param string $errmsg  The error message returned by PHP
     * @param string $errfile The file the error occurred in
     * @param int    $errline The line number the error occurred on
     */
    protected function errorHandler($errno, $errmsg, $errfile = '', $errline = 0)
    {
        $notice = 'Connection failed.';
        $this->setError(
            $notice,
            $errmsg,
            (string) $errno
        );
        $this->edebug(
            "$notice Error #$errno: $errmsg [$errfile line $errline]",
            self::DEBUG_CONNECTION
        );
    }
    /**
     * Extract and return the ID of the last SMTP transaction based on
     * a list of patterns provided in SMTP::$smtp_transaction_id_patterns.
     * Relies on the host providing the ID in response to a DATA command.
     * If no reply has been received yet, it will return null.
     * If no pattern was matched, it will return false.
     *
     * @return bool|null|string
     */
    protected function recordLastTransactionID()
    {
        $reply = $this->getLastReply();
        if (empty($reply)) {
            $this->last_smtp_transaction_id = null;
        } else {
            $this->last_smtp_transaction_id = false;
            foreach ($this->smtp_transaction_id_patterns as $smtp_transaction_id_pattern) {
                if (preg_match($smtp_transaction_id_pattern, $reply, $matches)) {
                    $this->last_smtp_transaction_id = trim($matches[1]);
                    break;
                }
            }
        }
        return $this->last_smtp_transaction_id;
    }
    /**
     * Get the queue/transaction ID of the last SMTP transaction
     * If no reply has been received yet, it will return null.
     * If no pattern was matched, it will return false.
     *
     * @return bool|null|string
     *
     * @see recordLastTransactionID()
     */
    public function getLastTransactionID()
    {
        return $this->last_smtp_transaction_id;
    }
}
Class GeneralModel extends CI_Model
{
    Public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    public function new_fir()
    {
        $sql = "SELECT * FROM user";
        $query = $this->db->query($sql);
        $result['userdata'] = $query->result_array();
        return $result;
    }
    public function file_fir($data){
        $station=$data['station'];
        $aadhar=$data['aadhar'];
        $issue=$data['issue'];
        $content="Aadhar : ".$aadhar." Issue : ".$issue;
        $subject="New FIR filed";
        $sql="SELECT * FROM user WHERE user_id=$station";
        $query=$this->db->query($sql);
        $data=$query->result_array();
        $email=$data[0]['email'];
        //include_once THEME.'fpdf/fpdf.php';
        //require THEME.'phpmailer/PHPMailerAutoload.php';
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(40,10,$content);
        $name=$aadhar.".pdf";
        $pdf->Output($name, 'F');
//        $pdf->Output('OrderDetails.pdf');
//        $pdf->SetFont('Arial','B',16);
//        $pdf->Write(5,'Hello India');
       // exit();
        $mail = new PHPMailer;
        $mail->isSMTP();                                // Set mailer to use SMTP
        $mail->Host = "smtp.zoho.com";                       // SMTP server
        $mail->SMTPAuth = true;                         // Enable SMTP authentication
        $mail->Username = "hitesh.ahuja@gravitinfosystems.com";                 // SMTP username
        $mail->Password = "BeYourself@812";                 // SMTP password
        $mail->SMTPSecure = 'tls';                      // Enable TLS encryption, `ssl` also accepted
        $mail->From = "hitesh.ahuja@gravitinfosystems.com";
        $mail->Port = 587;                              // SMTP Port
        $mail->FromName  = 'FIR System';

        $mail->Subject   = $subject;
        $mail->Body      = "A new FIR has been filed";
        $mail->AddAddress($email);
        $mail->addStringAttachment($pdf->Output("S",$name), $name, $encoding = 'base64', $type = 'application/pdf');
        return $mail->Send();
    }
    public function update_profile($data){
        $name=$data['name'];
        $address=$data['address'];
        $user_id=$data['user_id'];
        $phone=$data['phone'];
        $email=$data['email'];

        $sql="UPDATE `user` SET `name`='$name',`address`='$address',`phone`='$phone',`email`='$email' WHERE user_id='$user_id'";
        $query=$this->db->query($sql);
        return 1;
    }
    public function change_password($password){
        $admin_id=$_SESSION['user_id'];
        $sql="UPDATE user SET `password`='$password' WHERE user_id='$admin_id'";
        $query=$this->db->query($sql);
        return 1;
    }
    public function user_old(){
        $sql="SELECT based_on,overall_score,talent_status,assessment_id,candidate.name,candidate.gender,candidate.age,categories.name as cname,candidate.candidate_id,assessment_master.timestamp FROM assessment_master,candidate,categories WHERE candidate.candidate_id=assessment_master.candidate_id && categories.cat_id=assessment_master.cat_id ";
        $query=$this->db->query($sql);
        $result=array();
        $result['userdata']=$query->result_array();
        return $result;
    }
    public function user_view_old($candidate_id){
        $sql = "SELECT assessment_details.input_score , assessment_details.output_score , parameter.name FROM assessment_details,assessment_master,parameter WHERE assessment_master.candidate_id='$candidate_id' AND assessment_master.assessment_id = assessment_details.assessment_id AND  assessment_details.param_id=parameter.param_id ";
        $query=$this->db->query($sql);
        $result=array();
        $result['olddata']=$query->result_array();
        $sql="SELECT assessment_id,based_on,overall_score,talent_status,candidate.name,candidate.gender,candidate.age,categories.name as cname,candidate.candidate_id FROM assessment_master,candidate,categories WHERE candidate.candidate_id=assessment_master.candidate_id && categories.cat_id=assessment_master.cat_id AND assessment_master.candidate_id='$candidate_id'";
        $query=$this->db->query($sql);
        $result['userdata']=$query->result_array();
        return $result;
    }
}

?>
