<?php
/*
		Relatório de atividades para orçamento	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../planejamento/relatorios/rel_atividadesporcent.php
		
		Versão 0 --> VERSÃO INICIAL : 02/03/2006		
		Versão 1 --> atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
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
	$this->Cell(228,4,'',0,0,'L',0);
	$this->Cell(15,4,'DOC:',0,0,'L',0);
	$this->Cell(12,4,"",0,1,'R',0);
	$this->SetLineWidth(0.3);
	$this->Line(254,19.5,280,19.5);
	$this->Cell(240,4,'EMISSÃO:',0,0,'R',0); //aqui
	$this->Cell(15,4,$this->Emissao(),0,1,'R',0); //aqui
	$this->Line(254,23.5,280,23.5);
	$this->Cell(228,4,'',0,0,'L',0);
	$this->Cell(15,4,'FOLHA:',0,0,'L',0);
	$this->Cell(13,4,$this->PageNo().' de {nb}',0,0,'R',0);
	$this->Line(254,27.5,280,27.5);
	$this->Ln(8);
	$this->SetFont('Arial','B',12);
	$this->Cell(255,4,$this->Titulo(),0,1,'R',0);
	$this->SetFont('Arial','B',8);
	$this->SetFont('Arial','',9);
	$this->SetLineWidth(1);
	$this->SetDrawColor(0,0,0);
	$this->Line(25,40,280,40);
	$this->SetLineWidth(0.5);
	$this->SetXY(25,43);
	
}

//Page footer
function Footer()
{

}
}

$pdf=new PDF('L','mm',A4);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.3);

$pdf->titulo="LISTA DE ATIVIDADES PARA ORÇAMENTO";
$pdf->emissao=date("d/m/Y");
$pdf->versao_documento="0";

$pdf->AliasNbPages();

$disciplinaant = "";
$disciplina = "";

$db = new banco_dados;

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE setores.reg_del = 0 ";
$sql .= "ORDER BY setor ";

$db->select($sql,'MYSQL',true);

$array_setores = $db->array_select;	

foreach ($array_setores as $regcc)
{	
	$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".atividades ";
	$sql .= "LEFT JOIN ".DATABASE.".formatos ON (atividades.id_formato = formatos.id_formato AND formatos.reg_del = 0) ";
	$sql .= "WHERE LEFT(atividades.codigo,3)='".$regcc["abreviacao"]."' ";
	$sql .= "AND atividades_orcamento.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND atividades.descricao NOT LIKE \"\" ";
	$sql .= "AND atividades.id_atividade = atividades_orcamento.id_atividade ";
	$sql .= "ORDER BY descricao ";
	
	$db->select($sql,'MYSQL',true);		
	
	foreach ($db->array_select as $regs)
	{
		if($regcc["setor"]!= $setor_old)
		{
		
			$pdf->AddPage();
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(255,5,$regcc["setor"],0,1,'R',0);
			$pdf->SetDrawColor(128,128,128);
			$pdf->SetLineWidth(0.5);
			$pdf->Line(25,50,280,50);
			$pdf->Ln(4);
			$pdf->Cell(20,5,"CÓDIGO",0,0,'C',0);
			$pdf->Cell(3);
			$pdf->Cell(100,5,"DESCRIÇÃO",0,0,'L',0);
			$pdf->Cell(50,5,"%",0,0,'C',0);
			$pdf->Cell(25,5,"FORMATO",0,0,'C',0);
			$pdf->Cell(25,5,"H. ESTIMADAS",0,1,'C',0);
			$pdf->Ln(4);
			$pdf->SetFont('Arial','',8);
			
			$setor_old = $regcc["setor"];
		}
		
		$txt = '';
		
		$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".rh_funcoes ";
		$sql .= "WHERE id_atividade = '".$regs["id_atividade"]."' ";
		$sql .= "AND atividades_orcamento.reg_del = 0 ";
		$sql .= "AND rh_funcoes.reg_del = 0 ";
		$sql .= "AND atividades_orcamento.id_funcao = rh_funcoes.id_funcao ";
		$sql .= "ORDER BY ordem ";
		
		$db->select($sql,'MYSQL',true);		
		
		foreach ($db->array_select as $regs1)
		{
			$txt .= $regs1["categoria"].' '.$regs1["porcentagem"].'%'.' | ';
		}
		
		if($regs["id_atividade"]!= $atividade)
		{
			$pdf->Cell(20,5,$regs["codigo"],0,0,'C',0);
			$pdf->Cell(3);
			$pdf->HCell(100,5,$regs["descricao"],0,0,'L',0);
			$pdf->HCell(50,5,$txt,0,0,'C',0);
			$pdf->Cell(25,5,$regs["formato"],0,0,'C',0);
			$pdf->Cell(25,5,$regs["horasestimadas"],0,1,'C',0);
			
			$atividade = $regs["id_atividade"];
		}
		
	}
}

$pdf->Output('RELATORIO_ATIVIDADES_PORC_'.date('dmYhis').'.pdf', 'D');

?> 