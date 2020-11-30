<?php
/*
		Relat�rio de atividades	
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:
		../planejamento/relatorios/rel_atividades.php
		
		Vers�o 0 --> VERS�O INICIAL : 02/03/2006		
		Versao 1 --> atualiza��o classe banco de dados - 22/01/2015 - Carlos Abreu
		Vers�o 2 --> Inclus�o dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{

var $titulo;
var $emissao;
var $versao_documento;

function Titulo()
{
	return $this->titulo;
}

function Emissao()
{
	return $this->emissao;
}

function Revisao()
{
	return $this->versao_documento;
}

function HCell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='')
{
    //Output a cell
    $k=$this->k;
    if($this->y+$h>$this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak())
    {
        $x=$this->x;
        $ws=$this->ws;
        if($ws>0)
        {
            $this->ws=0;
            $this->_out('0 Tw');
        }
        $this->AddPage($this->CurOrientation);
        $this->x=$x;
        if($ws>0)
        {
            $this->ws=$ws;
            $this->_out(sprintf('%.3f Tw', $ws*$k));
        }
    }
    if($w==0)
        $w=$this->w-$this->rMargin-$this->x;
    $s='';
// begin change Cell function 12.08.2003 
    if($fill==1 or $border>0)
    {
        if($fill==1)
            $op=($border>0) ? 'B' : 'f';
        else
            $op='S';
        if ($border>1) {
            $s=sprintf(' q %.2f w %.2f %.2f %.2f %.2f re %s Q ', $border, 
                $this->x*$k, ($this->h-$this->y)*$k, $w*$k, -$h*$k, $op);
        }
        else
            $s=sprintf('%.2f %.2f %.2f %.2f re %s ', $this->x*$k, ($this->h-$this->y)*$k, $w*$k, -$h*$k, $op);
    }
    if(is_string($border))
    {
        $x=$this->x;
        $y=$this->y;
        if(is_int(strpos($border, 'L')))
            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ', $x*$k, ($this->h-$y)*$k, $x*$k, ($this->h-($y+$h))*$k);
        else if(is_int(strpos($border, 'l')))
            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ', $x*$k, ($this->h-$y)*$k, $x*$k, ($this->h-($y+$h))*$k);
            
        if(is_int(strpos($border, 'T')))
            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ', $x*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-$y)*$k);
        else if(is_int(strpos($border, 't')))
            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ', $x*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-$y)*$k);
        
        if(is_int(strpos($border, 'R')))
            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ', ($x+$w)*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
        else if(is_int(strpos($border, 'r')))
            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ', ($x+$w)*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
        
        if(is_int(strpos($border, 'B')))
            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ', $x*$k, ($this->h-($y+$h))*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
        else if(is_int(strpos($border, 'b')))
            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ', $x*$k, ($this->h-($y+$h))*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
    }
    if (trim($txt)!='') {
        $cr=substr_count($txt, "\n");
        if ($cr>0) { // Multi line
            $txts = explode("\n", $txt);
            $lines = count($txts);
            //$dy=($h-2*$this->cMargin)/$lines;
            for($l=0;$l<$lines;$l++) {
                $txt=$txts[$l];
                $w_txt=$this->GetStringWidth($txt);
                if($align=='R')
                    $dx=$w-$w_txt-$this->cMargin;
                elseif($align=='C')
                    $dx=($w-$w_txt)/2;
                else
                    $dx=$this->cMargin;

                $txt=str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt)));
                if($this->ColorFlag)
                    $s.='q '.$this->TextColor.' ';
                $s.=sprintf('BT %.2f %.2f Td (%s) Tj ET ', 
                    ($this->x+$dx)*$k, 
                    ($this->h-($this->y+.5*$h+(.7+$l-$lines/2)*$this->FontSize))*$k, 
                    $txt);
                if($this->underline)
                    $s.=' '.$this->_dounderline($this->x+$dx, $this->y+.5*$h+.3*$this->FontSize, $txt);
                if($this->ColorFlag)
                    $s.='Q ';
                if($link)
                    $this->Link($this->x+$dx, $this->y+.5*$h-.5*$this->FontSize, $w_txt, $this->FontSize, $link);
            }
        }
        else { // Single line
            $w_txt=$this->GetStringWidth($txt);
            $Tz=100;
            if ($w_txt>$w-2*$this->cMargin) { // Need compression
                $Tz=($w-2*$this->cMargin)/$w_txt*100;
                $w_txt=$w-2*$this->cMargin;
            }
            if($align=='R')
                $dx=$w-$w_txt-$this->cMargin;
            elseif($align=='C')
                $dx=($w-$w_txt)/2;
            else
                $dx=$this->cMargin;
            $txt=str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt)));
            if($this->ColorFlag)
                $s.='q '.$this->TextColor.' ';
            $s.=sprintf('q BT %.2f %.2f Td %.2f Tz (%s) Tj ET Q ', 
                        ($this->x+$dx)*$k, 
                        ($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k, 
                        $Tz, $txt);
            if($this->underline)
                $s.=' '.$this->_dounderline($this->x+$dx, $this->y+.5*$h+.3*$this->FontSize, $txt);
            if($this->ColorFlag)
                $s.='Q ';
            if($link)
                $this->Link($this->x+$dx, $this->y+.5*$h-.5*$this->FontSize, $w_txt, $this->FontSize, $link);
        }
    }
// end change Cell function 12.08.2003
    if($s)
        $this->_out($s);
    $this->lasth=$h;
    if($ln>0)
    {
        //Go to next line
        $this->y+=$h;
        if($ln==1)
            $this->x=$this->lMargin;
    }
    else
        $this->x+=$w;
}

//Page header
function Header()
{
    
	$this->Image(DIR_IMAGENS.'logo_pb.png',26,16,40);
	$this->Ln(1);
	$this->SetFont('Arial','',6);
	$this->Cell(146,4,'',0,0,'L',0);
	$this->Cell(12,4,'DOC:',0,0,'L',0);
	$this->Cell(12,4,"",0,1,'R',0); //setor - C�digo Documento - Sequencia
	$this->SetLineWidth(0.3);
	$this->Line(172,19.5,195,19.5);
	$this->Cell(158,4,'EMISSÃO:',0,0,'R',0); //aqui
	$this->Cell(12,4,$this->Emissao(),0,1,'R',0); //aqui
	$this->Line(172,23.5,195,23.5);
	$this->Cell(146,4,'',0,0,'L',0);
	$this->Cell(12,4,'FOLHA:',0,0,'L',0);
	$this->Cell(12,4,$this->PageNo().' de {nb}',0,0,'R',0);
	$this->Line(172,27.5,195,27.5);
	$this->Ln(8);
	$this->SetFont('Arial','B',12);
	$this->Cell(170,4,$this->Titulo(),0,1,'R',0);
	$this->SetFont('Arial','B',8);
	$this->SetFont('Arial','',9);
	$this->SetLineWidth(1);
	$this->SetDrawColor(0,0,0);
	$this->Line(25,40,195,40);
	$this->SetXY(25,45);
	
}

//Page footer
function Footer()
{

}
}

$db = new banco_dados;

$pdf=new PDF('p','mm',A4);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.3);

$pdf->titulo="LISTA DE ATIVIDADES";
$pdf->emissao=date("d/m/Y");
$pdf->versao_documento="0";

$pdf->AliasNbPages();

$disciplinaant = "";
$disciplina = "";

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE setores.reg_del = 0 ";
$sql .= "ORDER BY setor";

$db->select($sql,'MYSQL',true);

$array_setores = $db->array_select;
	
foreach($array_setores as $regcc)
{

	$pdf->AddPage();
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(170,5,$regcc["setor"],0,1,'R',0);
	$pdf->SetDrawColor(128,128,128);
	$pdf->SetLineWidth(0.5);
	$pdf->Line(25,50,195,50);
	$pdf->Ln(4);
	$pdf->Cell(20,5,"C�DIGO",0,0,'C',0);
	$pdf->Cell(3);
	$pdf->Cell(100,5,"DESCRI��O",0,1,'L',0);
	$pdf->Ln(4);
	$pdf->SetFont('Arial','',8);
	
	$sql = "SELECT * FROM ".DATABASE.".atividades ";
	$sql .= "WHERE LEFT(atividades.codigo,3)= ".$regcc["abreviacao"]." ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "ORDER BY descricao ";
	
	$db->select($sql,'MYSQL',true);		
	
	foreach($db->array_select as $regs)
	{
		$pdf->Cell(20,5,$regs["codigo"],0,0,'C',0);
		$pdf->Cell(3);
		$pdf->Cell(100,5,$regs["descricao"],0,1,'L',0);		
	}
}

$pdf->Output('RELATORIO_ATIVIDADES_'.date('dmYhis').'.pdf', 'D');
?> 