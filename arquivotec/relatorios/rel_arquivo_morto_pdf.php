<?php
/*
    Ficha do candidato
     
    Criado por Carlos
     
    local/Nome do arquivo:
        ./relatorios/rel_arquivo_morto_pdf.php
     
    Versão 0 --> VERSÃO INICIAL - 22/02/2018 - Carlos
 */
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{
    function Header()
    {
        $this->Image(DIR_IMAGENS.'logo_pb.png',15,11,30);
        
        //Criando as bordas do cabecalho
        $this->setY(10);
        $this->Cell(40, 10, '',1,0,'',0);
        $this->Cell(150, 10, '',1,0,'C',0);
        
        $this->setXY(50,13);
        $this->SetFont('Arial','B',9);
        $this->Cell(150, 5, $this->titulo,0,1,'C',0);
        
        $this->setY(10);
        $this->SetFont('Arial','i',6);
        $this->Cell(190,4,'Emissão: '.date('d/m/Y'),0,1,'R',0); //aqui
    }

}

$db = new banco_dados();

$filtroCoord = !empty($_GET['idFuncionario']) ? 'AND f.id_funcionario = '.$_GET['idFuncionario'] : '';

//Todas as Os's aprovadas para o Arquivo Morto
$sql =
"SELECT 
	f.id_funcionario, f.funcionario Coordenador, a.os, a.descricao, e.empresa, grd.ultima_emissao, b.data
FROM
	".DATABASE.".ordem_servico a
	JOIN ".DATABASE.".arquivo_morto_aprovadas b ON b.reg_del = 0 AND b.id_os = a.id_os
    JOIN ".DATABASE.".funcionarios f ON f.reg_del = 0 AND f.id_funcionario = a.id_cod_coord ".$filtroCoord."
    JOIN ".DATABASE.".empresas e ON e.reg_del = 0 AND e.id_empresa = a.id_empresa
    JOIN (
		SELECT id_os, MAX(data_emissao) ultima_emissao FROM ".DATABASE.".grd g1 
		JOIN ".DATABASE.".grd_versoes gv ON gv.id_grd = g1.id_grd AND gv.reg_del = 0
		WHERE
			g1.reg_del = 0
		GROUP BY id_os
    ) grd ON grd.id_os = a.id_os
WHERE
	a.reg_del = 0
	AND b.status = 1
ORDER BY
    funcionario, OS DESC";

$pdf=new PDF();
$pdf->SetAutoPageBreak(false,30);
$pdf->SetMargins(10,10);
$pdf->SetLineWidth(0.1);

$pdf->titulo = 'RELATÓRIO DE OS APROVADAS PARA ARQUIVO MORTO';

$pdf->AddPage();
$pdf->SetY(20);

$controleCabecalho = array();
$db->select($sql, 'MYSQL', function($reg, $i) use(&$pdf, &$controleCabecalho){
    //Controle de cabecalho de coordenadores
    if (!key_exists($reg['id_funcionario'], $controleCabecalho))
    {
        $pdf->ln(5);
        
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(25,5,'COORDENADOR:','B',0,'L',0);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(0,5,$reg['coordenador'],'B',1,'L',0);
        
        //Cabecalho da tabela abaixo do coordenador
        $pdf->SetFont('Arial','B',7);
        $pdf->Cell(70,5,'OS','B',0,'L',0);
        $pdf->Cell(70,5,'CLIENTE','B',0,'L',0);
        $pdf->Cell(30,5,'ÚLTIMA EMISSÃO','B',0,'L',0);
        $pdf->Cell(20,5,'APROVAÇÃO','B',1,'L',0);
        $pdf->SetFont('Arial','',7);
        
        $controleCabecalho[$reg['id_funcionario']] = true;        
    }
    
    //calculo da altura correta de cada celula
    $alturaOsAux = intval(strlen($reg['os']." - ".$reg['descricao']) / 43) + 1;
    $alturaClienteAux = intval(strlen($reg['empresa']) / 43) + 1;
    $alturaCliente = $alturaClienteAux == $alturaOsAux ? 5 : $alturaOsAux * 5;
    $alturaOs = $alturaClienteAux == $alturaOsAux ? 5 : $alturaClienteAux * 5;
    
    if ($alturaClienteAux == $alturaOsAux)
        $demaisAlturas = $alturaOsAux * 5;
    else
        $demaisAlturas = $alturaCliente > $alturaOs ? $alturaCliente : $alturaOs;
    
    //Restante das linhas do relatorio
    $y = $pdf->getY();
    $pdf->MultiCell(70,$alturaOs,$reg['os']." - ".$reg['descricao'],'T','L',0);
    
    $y2 = $pdf->getY()-$y;
    $pdf->setXY(80,$y);
    
    $y = $pdf->getY();
    $pdf->MultiCell(70,$alturaCliente,$reg['empresa'].'-'.$alturaOsAux.'-'.$alturaClienteAux,'T','L',0);
    
    $pdf->setXY(150,$y);
    $pdf->Cell(30,$demaisAlturas,mysql_php($reg['ultima_emissao']),'T',0,'L',0);
    $pdf->Cell(20,$demaisAlturas,mysql_php($reg['data']),'T',1,'L',0);
    
    if ($pdf->getY() > 275)
    {
        $pdf->addPage();
        $pdf->SetY(25);
    }
});

if ($db->numero_registros == 0)
{
    exit("<script>alert('Nenhum registro para gerar o relatorio');window.close();</script>");
}

$pdf->Output();