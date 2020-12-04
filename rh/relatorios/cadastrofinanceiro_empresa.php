<?php
/*
	 Relatório Financeiro empresa
	 
	 Criado por Carlos Abreu  
	 
	local/Nome do arquivo:
	../rh/relatorios/cadastrofinanceiro_empresa.php
	 
	 Versão 0 --> VERSÃO INICIAL : 10/06/2017
	 Versão 1 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu
 */
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");


class PDF extends HTML2FPDF
{
//Page header
function Header()
{
	$this->Image(DIR_IMAGENS.'logo_pb.png',26,16,40);
	$this->Ln(1);
	$this->SetFont('Arial','',6);
	$this->Cell(146,4,'',0,0,'L',0);
	$this->Cell(12,4,'DOC:',0,0,'L',0);
	$this->Cell(12,4,$this->setor() . '-' . $this->codigodoc() . '-' .$this->codigo(),0,1,'R',0);
	$this->SetLineWidth(0.3);
	$this->Line(172,19.5,195,19.5);
	$this->Line(172,23.5,195,23.5);
	$this->Cell(146,4,'',0,0,'L',0);
	$this->Cell(12,4,'FOLHA:',0,0,'L',0);
	$this->Cell(12,4,$this->PageNo().' de {nb}',0,0,'R',0);
	$this->Ln(8);
	$this->SetFont('Arial','B',12);
	$this->Cell(170,4,$this->Titulo(),0,1,'R',0);
	$this->SetFont('Arial','B',8);
	$this->Cell(170,4,$this->Revisao(),0,1,'R',0);
	$this->SetFont('Arial','',9);
	$this->SetLineWidth(1);
	$this->SetDrawColor(0,0,0);
	$this->Line(25,40,195,40);
	$this->SetXY(25,45);
}

//Page footer
function Footer()
{	
	$this->Line(25,280,66.75,280);
	$this->SetXY(25,280);
	$this->Cell(40,5,"De acordo - Contratado",0,0,'C',0);
	$this->Line(77.25,280,100,280);
	$this->SetXY(67.75,280);
	$this->Cell(40,5,"Data",0,0,'C',0);
	$this->Line(110.5,280,152.25,280);
	$this->SetXY(110.5,280);
	$this->Cell(40,5,"Diretor Comercial Financeiro",0,0,'C',0);
	$this->Line(162.75,280,185.5,280);
	$this->SetXY(153.25,280);
	$this->Cell(40,5,"data",0,0,'C',0);	
}

function chkbox($checado = false)
{
	$y_anterior = $this->GetY();

	$x1 = $this->GetX()+1;
	$y1 = $this->GetY()+1;
	
	$x2 = $x1+3;
	$y2 = $y1+3;
	
	$this->SetLineWidth(0.1);
	$this->SetDrawColor(0,0,0);
	
	$this->Line($x1,$y1,$x2,$y1); //sup
	$this->Line($x1,$y2,$x2,$y2); //inf
	$this->Line($x1,$y1,$x1,$y2); //esq
	$this->Line($x2,$y1,$x2,$y2); //dir
	
	if($checado)
	{
		$this->SetLineWidth(0.5);
		
		$this->Line($x1+0.5,$y1+1.3,$x1+1.3,$y1+2.3);
		$this->Line($x1+1.3,$y1+2.3,$x2-0.3,$y1+0.3);
	}
	
	$this->SetY($y_anterior);
	$this->SetX($x2-1);
	$this->Cell(2,3,' ',0,0,'C',0);

	$this->SetLineWidth(0.1);
}

}

$db = new banco_dados;

$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(true,30);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.5);

$pdf->departamento=NOME_EMPRESA;
$pdf->titulo="CADASTRO FINANCEIRO - EMPRESA";
$pdf->setor="FIN";
$pdf->codigodoc="01"; //"00";
$pdf->codigo="01"; //Numero OS
$pdf->setorextenso=$setor; //"INFORMATICA"
$pdf->emissao=$data_ini;

$data_ini = php_mysql($data_ini);
$datafim = php_mysql($datafim);

$pdf->AliasNbPages();

$sql = "SELECT *, financeiro_requisicoes.observacoes AS financeiro_observacoes, financeiro_requisicoes.status AS requisicao_status, rh_funcoes.descricao AS cargo ,local.descricao AS local ";
$sql .= "FROM ".DATABASE.".financeiro_requisicoes, ".DATABASE.".rh_candidatos, ".DATABASE.".requisicoes_pessoal, ".DATABASE.".rh_funcoes, ".DATABASE.".local ";
$sql .= "WHERE financeiro_requisicoes.id_rh_candidato = rh_candidatos.id_rh_candidato ";
$sql .= "AND financeiro_requisicoes.reg_del = 0 ";
$sql .= "AND rh_candidatos.reg_del = 0 ";
$sql .= "AND requisicoes_pessoal.reg_del = 0 ";
$sql .= "AND rh_funcoes.reg_del = 0 ";
$sql .= "AND local.reg_del = 0 ";
$sql .= "AND rh_candidatos.id_requisicao = requisicoes_pessoal.id_requisicao ";
$sql .= "AND financeiro_requisicoes.id_local = local.id_local ";
$sql .= "AND requisicoes_pessoal.id_cargo = rh_funcoes.id_funcao ";
$sql .= "AND financeiro_requisicoes.id_rh_candidato = '" . $_GET["id_rh_candidato"] . "' ";

$db->select($sql,'MYSQL',true);

$reg_financeiro = $db->array_select[0];

$pdf->AddPage();

/* Array de checkboxes */
$chk_status[$reg_financeiro["requisicao_status"]] = true; //status
$chk_tipocontrato[$reg_financeiro["tipo_contrato"]] = true; //tipo de Contrato
$chk_salarioregistro[$reg_financeiro["salario_registro_tipo"]] = true; //Salário Registro
$chk_salarioempresa[$reg_financeiro["salario_empresa_tipo"]] = true; //Salário empresa
$chk_salarioajudacusto[$reg_financeiro["salario_ajudacusto_tipo"]] = true; //Salário Ajuda
$chk_salariohoraextra[$reg_financeiro["salario_horaextra_tipo"]] = true; // Salário Hora extra
$chk_in_transporte[$reg_financeiro["in_transporte"]] = true; //Inclusões transporte
$chk_in_refeicao[$reg_financeiro["in_refeicao"]] = true; //Inclusões refeição
$chk_in_hotel[$reg_financeiro["in_hotel"]] = true; //Inclusões hotel
$chk_in_outros[$reg_financeiro["in_outros"]] = true; //Inclusões outros
$chk_fp_unibanco[$reg_financeiro["fp_unibanco"]] = true; //FP Unibanco
$chk_fp_doc[$reg_financeiro["fp_doc"]] = true; //FP DOC
$chk_fp_cheque[$reg_financeiro["fp_cheque"]] = true; //FP Cheque
$chk_fp_moeda[$reg_financeiro["fp_moeda"]] = true; //FP Moeda

switch($reg_financeiro["tipo_contrato"])
{
	case "CLT":
	$tipo_contrato = "CLT";
	
	break;
	
	case "EST":
	$tipo_contrato = "ESTAGIÁRIO";
		
	break;
	
	case "SC":
	$tipo_contrato = "SOCIEDADE CIVIL";
	
	break;

	case "SC+CLT":
	$tipo_contrato = "SOCIEDADE CIVIL + CLT";
	
	break;

	case "SC+MENS":
	$tipo_contrato = "SOCIEDADE CIVIL (MENSALISTA)";
		
	break;

	case "SC+CLT+MENS":
	$tipo_contrato = "SOCIEDADE CIVIL + CLT (MENSALISTA)";
	
	break;

	case "SOCIO":
	$tipo_contrato = "SÓCIO";	
	
	break;
}

$pdf->SetFont('Arial','B',8);
$pdf->Cell(40,5,"status",0,0,'L',0);
$pdf->chkbox($chk_status["1"]);
$pdf->SetFont('Arial','',8);
$pdf->Cell(30,5,"Admissão",0,0,'L',0);

$pdf->Cell(5,5,"",0,0,'C',0);
$pdf->chkbox($chk_status["2"]);
$pdf->Cell(30,5,"Reajuste salarial",0,0,'L',0);

$pdf->Cell(5,5,"",0,0,'C',0);
$pdf->chkbox($chk_status["3"]);
$pdf->Cell(30,5,"Outro",0,1,'L',0);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(40,5,"Tipo de Contrato",0,0,'L',0);

$pdf->SetFont('Arial','',8);
$pdf->Cell(40,5,$tipo_contrato,0,0,'L',0);

$pdf->Ln(5);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"Nome",0,0,'L',0);
$pdf->SetFont('Arial','',8);
$pdf->Cell(100,5,$reg_financeiro["nome"],1,0,'L',0);

$pdf->Ln(6);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"Cargo",0,0,'L',0);
$pdf->SetFont('Arial','',8);
$pdf->Cell(100,5,$reg_financeiro["cargo"],1,0,'L',0);

$pdf->Ln(6);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"Data de início",0,0,'L',0);
$pdf->SetFont('Arial','',8);
$pdf->Cell(17,5,mysql_php($reg_financeiro["data_inicio"]),0,0,'L',0);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(50,5,"Data prevista para iniciar empresa",0,0,'L',0);
$pdf->SetFont('Arial','',8);
$pdf->Cell(17,5,mysql_php($reg_financeiro["data_empresa"]),0,1,'L',0);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(30,5,"Local de Trabalho",0,0,'L',0);
$pdf->SetFont('Arial','',8);
$pdf->Cell(17,5,$reg_financeiro["local"],0,1,'L',0);

$pdf->Ln(6);

$pdf->Line(25,$pdf->GetY(),195,$pdf->GetY());

$pdf->Ln(2);

$pdf->SetFont('Arial','B',8);
$pdf->VCell(10,15,"Salário",0,0,'C',0);
$pdf->SetFont('Arial','',8);
$pdf->Cell(40,5,"Registro",1,0,'C',0);
$pdf->Cell(40,5,"Empresa",1,0,'C',0);
$pdf->Cell(40,5,"Ajuda de Custo",1,0,'C',0);
$pdf->Cell(40,5,"Hora Extra",1,1,'C',0);
$pdf->SetX(35);
$pdf->Cell(40,5,"R$ " . number_format($reg_financeiro["salario_registro"],2,",","."),1,0,'C',0);
$pdf->Cell(40,5,"R$ " . number_format($reg_financeiro["salario_empresa"],2,",","."),1,0,'C',0);
$pdf->Cell(40,5,"R$ " . number_format($reg_financeiro["salario_ajudacusto"],2,",","."),1,0,'C',0);
$pdf->Cell(40,5,"R$ " . number_format($reg_financeiro["salario_horaextra"],2,",","."),1,1,'C',0);

$y_chk = $pdf->GetY();

//Salário Registro
$pdf->SetX(35);
$pdf->chkbox($chk_salarioregistro["1"]);
$pdf->Cell(10,5,"Mensal",0,0,'L',0);
$pdf->Cell(10,5,"",0,0,'C',0);
$pdf->chkbox($chk_salarioregistro["2"]);
$pdf->Cell(10,5,"Hora",0,0,'L',0);
//Salário empresa
$pdf->chkbox($chk_salarioempresa["1"]);
$pdf->Cell(10,5,"Mensal",0,0,'L',0);
$pdf->Cell(10,5,"",0,0,'C',0);
$pdf->chkbox($chk_salarioempresa["2"]);
$pdf->Cell(10,5,"Hora",0,0,'L',0);
//Ajuda de Custo
$pdf->chkbox($chk_salarioajudacusto["1"]);
$pdf->Cell(10,5,"Mensal",0,0,'L',0);
$pdf->Cell(10,5,"",0,0,'C',0);
$pdf->chkbox($chk_salarioajudacusto["2"]);
$pdf->Cell(10,5,"Hora",0,0,'L',0);
//Hora extra
$pdf->chkbox($chk_salariohoraextra["1"]);
$pdf->Cell(10,5,"Mensal",0,0,'L',0);
$pdf->Cell(10,5,"",0,0,'C',0);
$pdf->chkbox($chk_salariohoraextra["2"]);
$pdf->Cell(10,5,"Hora",0,0,'L',0);

//Linha chkboxes esquerda vertical
$pdf->Line(35,$y_chk,35,$y_chk+5);
//Linha chkboxes inferior horizontal
$pdf->Line(35,$y_chk+5,195,$y_chk+5);
//Linha chkboxes direita vertical
$pdf->Line(195,$y_chk,195,$y_chk+5);

//Linha 2
$pdf->Line(55,$y_chk,55,$y_chk+5);
//Linha 3
$pdf->Line(75,$y_chk,75,$y_chk+5);
//Linha 4
$pdf->Line(95,$y_chk,95,$y_chk+5);
//Linha 5
$pdf->Line(115,$y_chk,115,$y_chk+5);
//Linha 6
$pdf->Line(135,$y_chk,135,$y_chk+5);
//Linha 7
$pdf->Line(155,$y_chk,155,$y_chk+5);
//Linha 8
$pdf->Line(175,$y_chk,175,$y_chk+5);

$pdf->Ln(6);

$pdf->SetFont('Arial','B',8);
$pdf->VCell(10,15,"Inclusões",0,0,'C',0);
$pdf->SetFont('Arial','',8);
$pdf->Cell(2,3,"",0,1,'C',0);
$pdf->SetX(35);
$pdf->Cell(40,5,"Transporte",1,0,'C',0);
$pdf->Cell(40,5,"Refeição",1,0,'C',0);
$pdf->Cell(40,5,"Hotel",1,0,'C',0);
$pdf->Cell(40,5,"Outros",1,1,'C',0);

$y_chk = $pdf->GetY();

//Transporte
$pdf->SetX(35);
$pdf->chkbox($chk_in_transporte[1]);
$pdf->Cell(10,5,"Sim",0,0,'L',0);
$pdf->Cell(10,5,"",0,0,'C',0);
$pdf->chkbox($chk_in_transporte[0]);
$pdf->Cell(10,5,"Não",0,0,'L',0);
//Refei��o
$pdf->chkbox($chk_in_refeicao[1]);
$pdf->Cell(10,5,"Sim",0,0,'L',0);
$pdf->Cell(10,5,"",0,0,'C',0);
$pdf->chkbox($chk_in_refeicao[0]);
$pdf->Cell(10,5,"Não",0,0,'L',0);
//Hotel
$pdf->chkbox($chk_in_hotel[1]);
$pdf->Cell(10,5,"Sim",0,0,'L',0);
$pdf->Cell(10,5,"",0,0,'C',0);
$pdf->chkbox($chk_in_hotel[0]);
$pdf->Cell(10,5,"Não",0,0,'L',0);
//Outros
$pdf->chkbox($chk_in_outros[1]);
$pdf->Cell(10,5,"Sim",0,0,'L',0);
$pdf->Cell(10,5,"",0,0,'C',0);
$pdf->chkbox($chk_in_refeicao[0]);
$pdf->Cell(10,5,"Não",0,0,'L',0);

//Linha chkboxes esquerda vertical
$pdf->Line(35,$y_chk,35,$y_chk+5);
//Linha chkboxes inferior horizontal
$pdf->Line(35,$y_chk+5,195,$y_chk+5);
//Linha chkboxes direita vertical
$pdf->Line(195,$y_chk,195,$y_chk+5);

//Linha 2
$pdf->Line(55,$y_chk,55,$y_chk+5);
//Linha 3
$pdf->Line(75,$y_chk,75,$y_chk+5);
//Linha 4
$pdf->Line(95,$y_chk,95,$y_chk+5);
//Linha 5
$pdf->Line(115,$y_chk,115,$y_chk+5);
//Linha 6
$pdf->Line(135,$y_chk,135,$y_chk+5);
//Linha 7
$pdf->Line(155,$y_chk,155,$y_chk+5);
//Linha 8
$pdf->Line(175,$y_chk,175,$y_chk+5);

$pdf->Ln(10);

$pdf->Line(25,$pdf->GetY(),195,$pdf->GetY());

$pdf->Ln(4);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(30,5,"Forma de valor_pagamento",0,0,'L',0);

$pdf->SetFont('Arial','',8);
$pdf->chkbox($chk_fp_unibanco[1]);
$pdf->Cell(20,5,"Unibanco",0,0,'L',0);
$pdf->chkbox($chk_fp_doc[1]);
$pdf->Cell(20,5,"DOC",0,0,'L',0);
$pdf->chkbox($chk_fp_cheque[1]);
$pdf->Cell(20,5,"Cheque",0,0,'L',0);
$pdf->chkbox($chk_fp_moeda[1]);
$pdf->Cell(20,5,"Moeda",0,0,'L',0);

$pdf->Ln(6);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"Observações",0,1,'L',0);
$pdf->SetFont('Arial','',8);

//Linha esquerda vertical
$pdf->Line(25,$pdf->GetY(),25,$pdf->GetY()+100);
//Linha topo horizontal
$pdf->Line(25,$pdf->GetY(),195,$pdf->GetY());
//Linha direita vertical
$pdf->Line(195,$pdf->GetY(), 195, $pdf->GetY()+100);
//Linha baixo horizontal
$pdf->Line(25,$pdf->GetY()+100,195,$pdf->GetY()+100);

$pdf->MultiCell(170,5,$reg_financeiro["financeiro_observacoes"],0,'L',0);

$pdf->Output('CADASTRO_FINANCEIRO_'.date('dmYhis').'.pdf', 'D');
?> 