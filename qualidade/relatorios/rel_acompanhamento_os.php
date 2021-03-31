<?php
/*
		Relatório Acompanhamento OS	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		/relatorios/rel_acompanhamento_os.php
		
		Versão 0 --> VERSÃO INICIAL - 14/01/2005
		Versão 1 --> Atualização LAY OUT - 28/03/2006	
		Versão 2 --> ajuste banco de dados - 07/05/2015 -  Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{
	//Page header
	function Header()
	{
		//Logo
		$this->Image(DIR_IMAGENS.'logo_pb.png',26,16,40);
		$this->Ln(1);
		$this->SetFont('Arial','',6);
		$this->Cell(228,4,'',0,0,'L',0);
		$this->Cell(15,4,'DOC:',0,0,'L',0);
		$this->Cell(12,4,$this->setor() . '-' . $this->codigodoc() . '-' .$this->codigo(),0,1,'R',0);
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
		$this->Cell(255,4,$this->Revisao(),0,1,'R',0);
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

$db = new banco_dados;

$pdf=new PDF('l','mm',A4);
$pdf->SetAutoPageBreak(true,5);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.5);

$id_os = $_POST["id_os"];

$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".funcionarios, ".DATABASE.".ordem_servico ";
$sql .= "LEFT JOIN ".DATABASE.".contatos ON (ordem_servico.id_cod_resp = contatos.id_contato) ";
$sql .= "WHERE ordem_servico.id_os = '" . $id_os . "' ";
$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";
$sql .= "AND ordem_servico.id_cod_coord = funcionarios.id_funcionario ";

$db->select($sql,'MYSQL',true);

$reg_os = $db->array_select[0];

$sql = "SELECT * FROM ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.id_funcionario = '" . $reg_os["id_funcionario"] . "' ";
$sql .= "AND funcionarios.id_usuario = usuarios.id_usuario ";

$db->select($sql,'MYSQL',true);;

$reg_func = $db->array_select[0];

//obtem as datas previstas e reais
/*
$sql = "SELECT * FROM AF8010 WITH(NOLOCK) ";
$sql .= "WHERE D_E_L_E_T_ = '' ";
$sql .= "AND AF8_PROJET = '".sprintf("%010d",$reg_os["os"])."' ";

$db->select($sql,'MSSQL', true);

$regs0 = $db->array_select[0];
*/

//Seta o cabeçalho
$pdf->departamento=NOME_EMPRESA;
$pdf->titulo="ACOMPANHAMENTO DE PROJETOS DE ENGENHARIA - OS Nº " . $reg_os["os"];
$pdf->codigodoc="304"; //"00"; //"04";
$pdf->codigo=01; //Numero OS
$pdf->emissao=date("d/m/Y");

$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial','',7);

$pdf->Cell(100,4,"Cliente: " . $reg_os["empresa"],0,0,'L',0);
$pdf->Cell(50,4,"Projeto Início prev.: " . mysql_php(protheus_mysql($regs0["AF8_START"])),0,0,'L',0);
$pdf->Cell(50,4,"Projeto Término prev.: " . mysql_php(protheus_mysql($regs0["AF8_FINISH"])),0,1,'L',0);
$pdf->Cell(100,4,"",0,0,'L',0);
$pdf->Cell(50,4,"Projeto Início real: " . mysql_php(protheus_mysql($regs0["AF8_DTATUI"])),0,0,'L',0);
$pdf->Cell(50,4,"Projeto Término real: " . mysql_php(protheus_mysql($regs0["AF8_DTATUF"])),0,1,'L',0);

$pdf->Cell(100,4,"Coordenador Cliente: " . $reg_os["nome_contato"],0,0,'L',0);

$pdf->Cell(50,4,"E-mail: " . $reg_os["email"],0,1,'L',0);

$pdf->Cell(100,4,"Coordenador: " . $reg_os["funcionario"],0,0,'L',0);

$pdf->Cell(50,4,"E-mail: ". $reg_func["email"],0,1,'L',0);

$pdf->Cell(50,4,"Especialidade(s) no Projeto",1,0,'C',0);
$pdf->Cell(150,4,"Equipe",1,1,'C',0);

$x_multicell_8 = $pdf->GetX();
$y_multicell_8 = $pdf->GetY();

$sql = "SELECT * FROM ".DATABASE.".os_x_funcionarios, ".DATABASE.".funcionarios, ".DATABASE.".rh_funcoes, ".DATABASE.".setores ";
$sql .= "WHERE os_x_funcionarios.id_funcionario = funcionarios.id_funcionario ";
$sql .= "AND funcionarios.id_funcao = rh_funcoes.id_funcao ";
$sql .= "AND os_x_funcionarios.id_os = '" . $id_os . "' ";
$sql .= "AND funcionarios.id_setor = setores.id_setor ";
$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO') ";
$sql .= "ORDER BY setor, funcionario ";

$db->select($sql,'MYSQL',true);;

foreach($db->array_select as $reg_func)
{
	$pdf->Cell(50,4,$reg_func["setor"],0,0,'C',0);
	$pdf->Cell(150,4,$reg_func["funcionario"] . " - " . $reg_func["categoria"],0,1,'C',0);
}

$sql = "SELECT nome_validador, data_validacao FROM ".DATABASE.".os_x_validacao ";
$sql .= "WHERE id_os = '" . $reg_os["id_os"] . "' ";

$db->select($sql,'MYSQL',true);;

$reg_validacao = $db->array_select[0];

$sql = "SELECT * FROM ".DATABASE.".os_x_analise_critica_final ";
$sql .= "WHERE id_os = '" . $reg_os["id_os"] . "' ";

$db->select($sql,'MYSQL',true);;

$reg_acf = $db->array_select[0];

//Verifica se estamos no final da página
if($pdf->getY()>190)
{
	//Adiciona uma página
	$pdf->addPage();
}

$pdf->Cell(125,4,"Verificação do projeto",1,0,'C',0);
$pdf->Cell(125,4,"Controle de alterações e revisões",1,1,'C',0);

$x_multicell_1 = $pdf->GetX();
$y_multicell_1 = $pdf->GetY();

$pdf->MultiCell(125,4,"Conforme a Análise e Verificação de Projeto, executada em todos os documentos que são finalizados para entrega ao cliente ao longo do projeto, de acordo com  cronograma consolidado", 0,'L',0);
$pdf->SetXY($x_multicell_1+125,$y_multicell_1);
$pdf->MultiCell(125,4,"As alterações são registradas no formulário Solicitação de Alterações e/ou Serviços. As revisões são anotadas no próprio documento do projeto e registradas pelo Arquivo Técnico.",0,'L',0);

//Verifica se estamos no final da página
if($pdf->getY()>190)
{
	$pdf->addPage();
}

$pdf->Cell(125,4,"Validação do projeto",1,0,'C',0);
$pdf->Cell(125,4,"Validação pelo responsável técnico - ". NOME_EMPRESA,1,1,'C',0);

$x_multicell_2 = $pdf->GetX();
$y_multicell_2 = $pdf->GetY();
$pdf->MultiCell(125,4,"A validação do projeto é evidenciada por: 1) Análise e verificação dos documentos do projeto emitidos para o cliente e 2) Análise final pelo responsável técnico da ".NOME_EMPRESA,0,'L',0);
$pdf->SetXY($x_multicell_2+125,$y_multicell_2);
$pdf->Cell(100,4,"Coordenador: " . $reg_validacao["nome_validador"],0,0,'L',0);
$pdf->Cell(25,4,"Data: " . mysql_php($reg_validacao["data_validacao"]),0,1,'L',0);

if($pdf->GetY()>=145)
{
	$pdf->addPage();
}

$pdf->Cell(250,4,"Análise crítica final (Utilize o Procedimento de Execução de Projetos).",1,1,'C',0);

$pdf->MultiCell(250,4,"1 - Quais foram os aspectos positivos mais relevantes?",0,1,'L',0);

$pdf->MultiCell(250,4,maiusculas(addslashes($reg_acf["txt_asp_positivos"])),0,1,'L',0);

$pdf->Ln(5);

$pdf->MultiCell(250,4,"2 - Quais foram os aspectos negativos mais relevantes?",0,1,'L',0);

$pdf->MultiCell(250,4,maiusculas(addslashes($reg_acf["txt_asp_negativos"])),0,1,'L',0);

$pdf->Ln(5);

$pdf->Cell(100,4,"Nome: " . $reg_acf["nome_analise_final"],0,0,'L',0);
$pdf->Cell(25,4,"Data: " . mysql_php($reg_acf["data_analise_final"]),0,1,'L',0);

$pdf->Ln(5);

if($pdf->page == count($pdf->pages))
{
	$pdf->Line(25,200,65,200);
	$pdf->Line(90,200,130,200);

	$pdf->SetXY(25,200);
	
	$pdf->Cell(40,5,"COORDENADOR",0,0,'C',0);
	$pdf->Cell(25);
	$pdf->Cell(40,5,"DATA",0,0,'C',0);
	$pdf->Cell(35);

}

$pdf->Output('ACOMPANHAMENTO_OS_'.date('dmYhis').'.pdf', 'D');

?>