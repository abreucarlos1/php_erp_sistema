<?php
/*
		Ficha do candidato	
		
		Criado por Carlos
		
		local/Nome do arquivo:
		../rh/controle_aprovados.php
		
		Versão 0 --> VERSÃO INICIAL - 04/05/2016
		Ultima Atualização: 28/05/2016
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu	
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");
require_once(INCLUDE_DIR."WriteTag.php");
require_once(INCLUDE_DIR."antiInjection.php");

class PDF extends PDF_WriteTag
{
	public $anoContrato;
	public $numContrato;
	public $exibirNumeracao = true;
	
	function Header()
	{
		$this->Image(DIR_IMAGENS.'logo_pb.png',155,15,40);
	}

	function Footer(){
		$this->Rect(10, 10, 190, 277, 'D');
		 
		$this->SetY(-18);
	    $this->SetFont('Arial','I',8);
	    $this->Cell(130,5,"Contrato de Prestação de Serviços n. ".$this->numContrato.'/'.$this->anoContrato,0,0,'L',0);
	    
	    if ($this->exibirNumeracao)
	    	$this->Cell(50,10,$this->PageNo().'/{totalPages}',0,0,'R');
	}
}

$db = new banco_dados();

//CONSULTA PRINCIPAL
$sql = "SELECT 
			f.funcionario, pc.numero_contrato, pc.data_inicio, pc.data_fim, ef.empresa_func, ef.empresa_cnpj, ef.empresa_cidade, ef.empresa_estado, ef.empresa_endereco, 
			ef.empresa_bairro, id_tipo_contratacao, sa.setor_aso, salario_mensalista, salario_hora, testemunha_1, testemunha_2, l.descricao local_trabalho ";
$sql .= "FROM ".DATABASE.".funcionarios f ";
$sql .= "JOIN ".DATABASE.".local l ON l.id_local = f.id_local AND l.reg_del = 0  ";
$sql .= "JOIN ".DATABASE.".salarios s ON s.id_salario = f.id_salario AND s.reg_del = 0 ";
$sql .= "JOIN ".DATABASE.".empresa_funcionarios ef ON (f.id_empfunc = ef.id_empfunc OR ef.empresa_socio = f.id_funcionario AND ef.reg_del = 0) ";
$sql .= "JOIN ".DATABASE.".pj_contratos pc ON pc.id_funcionario = f.id_funcionario AND pc.reg_del = 0 ";
$sql .= "JOIN ".DATABASE.".setor_aso sa ON sa.id_setor_aso = f.id_setor_aso AND sa.reg_del = 0 ";
$sql .= "WHERE pc.id_contrato = ".$_GET['idContrato']." ";
$sql .= "AND f.reg_del = 0 ";

$regsPrincipal = $db->select($sql, "MYSQL", function($reg, $i){
	return $reg;
});

//POPULANDO AS VARIÁVEIS
$anoContrato = substr($regsPrincipal[0]['numero_contrato'], -4);
$numContrato = str_replace($anoContrato, '', $regsPrincipal[0]['numero_contrato']);

$empresaFunc = $regsPrincipal[0]['empresa_func'];
$empresaCnpj = $regsPrincipal[0]['empresa_cnpj'];
$empresaCidadeEstado = $regsPrincipal[0]['empresa_cidade'].' / '.$regsPrincipal[0]['empresa_estado'];
$empresaEndereco = $regsPrincipal[0]['empresa_endereco'];
$empresaBairro = $regsPrincipal[0]['empresa_bairro'];
$nomeSocio = $regsPrincipal[0]['funcionario'];
$localTrabalho = str_replace(CIDADE, NOME_EMPRESA, $regsPrincipal[0]['local_trabalho']);

$dataInicio = $regsPrincipal[0]['data_inicio'];
$dataFinal = $regsPrincipal[0]['data_fim'];
$tipoContrato = $regsPrincipal[0]['id_tipo_contratacao'];

if (intval($regsPrincipal[0]['salario_hora']) > 0)
{
	$valorContrato 	= number_format($regsPrincipal[0]['salario_hora'], 2, ',', '.');
	$complValorCont = ' por hora';
	$periodoRef = '26 - 25';
}
else
{
	$valorContrato 	= number_format($regsPrincipal[0]['salario_mensalista'], 2, ',', '.');
	$complValorCont = ' por mês';
	$periodoRef = '1 - 30/31';
}

$setorAso = $regsPrincipal[0]['setor_aso'];

$testemunhas = $regsPrincipal[0]['testemunha_1'].','.$regsPrincipal[0]['testemunha_2'];

//Trocas de conteudo dinâmico dentro das clausulas aqui
$variaveis = array(
	'{{nome_empresa}}' 	=> $empresaFunc,
	'{{cpnj}}' 			=> $empresaCnpj,
	'{{cidade_estado}}' => $empresaCidadeEstado,
	'{{endereco}}' 		=> $empresaEndereco,
	'{{bairro}}' 		=> $empresaBairro,
	'{{nome_socio}}' 	=> $nomeSocio,
	'{{area}}'			=> $setorAso,
	'{{tempo_contrato}}'	=> meses_dias($dataInicio, $dataFinal),
	'{{inicio_contrato}}'	=> mysql_php($dataInicio),
	'{{final_contrato}}'	=> mysql_php($dataFinal),
	'{{pagamento}}'			=> 'R$ '.$valorContrato.$complValorCont,
	'{{local_trabalho}}'	=> $localTrabalho
);

//Criação da pasta do funcionario
$pastaArquivos = DOCUMENTOS_RH.'documentos_funcionarios/';
$nomePasta = str_replace(' ', '_', maiusculas(tiraacentos($nomeSocio)));
$pastaCompleta = $pastaArquivos.$nomePasta;

if (!is_dir($pastaCompleta))
{
	$res = mkdir($pastaCompleta, '777');
}

if($_GET['parte'] == 'contrato')
{
	$nomeArquivo = 'contrato_'.$numContrato.'-'.$anoContrato.'.pdf';
	$arquivo = $pastaCompleta.'/'.$nomeArquivo;

	if (is_file($arquivo))
	{
		downloadFile($arquivo, $nomeArquivo);
	}
	
	$pdf=new PDF();
	$pdf->SetAutoPageBreak(false,30);
	$pdf->SetMargins(20,25);
	$pdf->SetLineWidth(0.5);
	
	$pdf->numContrato = $numContrato;
	$pdf->anoContrato = $anoContrato;
	
	$pdf->SetStyle("b","arial","B",11,"0,0,0");
	$pdf->SetStyle("p","arial","",11,"0,0,0",15);
	
	$pdf->AliasNbPages('{totalPages}');
	$pdf->AddPage();
	
	########################################## CAPA DO CONTRATO #########################################################
	$pdf->SetY(80);
	$pdf->SetFont('Arial','B',20);
	$pdf->Cell(25,15,"",0,0,'C',0);
	$pdf->MultiCell(140,10,"CONTRATO DE PRESTAÇÃOO DE SERVIÇOS DE PROJETOS INDUSTRIAIS",0,'C',0);
	
	$pdf->SetY(130);
	$pdf->SetFont('Arial','B',16);
	$pdf->Cell(15,15,"",0,0,'C',0);
	$pdf->MultiCell(140,10,"CONTRATO N. ".$numContrato.'/'.$anoContrato,0,'L',0);
	$pdf->Ln(10);
	
	$pdf->SetY(160);
	$pdf->Cell(15,5,"",0,0,'C',0);
	$pdf->MultiCell(140,10,"EMPRESA CONTRATADA: ".$empresaFunc,0,'L',0);
	$pdf->Ln(10);
	
	$pdf->SetY(225);
	$pdf->Cell(15,5,"",0,0,'C',0);
	$pdf->MultiCell(170,5,"DATA: ".mysql_php($dataInicio),0,'L',0);
	$pdf->Ln(10);
	
	########################################## FIM CAPA DO CONTRATO #########################################################
	
	########################################## CLAUSULAS ####################################################################
	$pdf->AddPage();
	$pdf->SetMargins(20,25);
	$pdf->SetAutoPageBreak(true,20);
	
	$sql = "SELECT * FROM ".DATABASE.".pj_clausula ";
	$sql .= "JOIN ".DATABASE.".pj_tipo_contratacao ON id_tipo_contratacao = id_tipo_contrato AND pj_tipo_contratacao.reg_del = 0 ";
	$sql .= "WHERE id_tipo_contrato = '".$tipoContrato."' ";
	$sql .= "AND pj_clausula.reg_del = 0 ";
	$sql .= "ORDER BY numero";
	
	$pdf->SetY(30);
	$regsClausulas = $db->select($sql,'MYSQL', function($reg, $i) use(&$pdf, &$variaveis){
		if ($i == 0)
		{
			//Titulo antes da clausula 1
			$pdf->SetFont('Arial','B',14);
			$pdf->MultiCell(170,8,$reg['clausula'],0,'C',0);
			$pdf->ln(5);
			
			foreach($variaveis as $var => $conteudo)
			{
				$reg['descricao_clausula'] = str_replace($var, $conteudo, $reg['descricao_clausula']);
			}
			
			//Texto antes da clausula 1
			$pdf->SetFont('Arial','',11);
			$pdf->WriteTag(0,7,$reg['descricao_clausula'],0,'J',0);
			$pdf->ln(5);
		}
		else
		{
			//Demais clausulas
			$pdf->SetFont('Arial','B',11);
			$pdf->MultiCell(0,8,$reg['clausula'],0,'L',0);
			
			foreach($variaveis as $var => $conteudo)
			{
				$reg['descricao_clausula'] = str_replace($var, $conteudo, $reg['descricao_clausula']);
			}
			
			//Demais Textos
			$pdf->SetFont('Arial','',11);
			$pdf->WriteTag(170,7,$reg['descricao_clausula'],0,'J',0,0);
			$pdf->ln(5);	
		}
	});
	########################################## FIM DAS CLAUSULAS ############################################################
	
	########################################## ÚLTIMA PÁGINA - ASSINATURAS ##################################################
	$pdf->AddPage();
	criarAssinaturas($pdf, $nomeSocio, $empresaFunc, $testemunhas, $dataInicio);
	##################################### FIM ÚLTIMA PÁGINA - ASSINATURAS ####################################################
	
	$pdf->Output($arquivo, 'F');
	downloadFile($arquivo, $nomeArquivo);
}
else if ($_GET['parte'] == 'anexo1')
{
	########################################## ANEXO 1 DO CONTRATO ###########################################################

	$nomeArquivo = 'anexo1_'.$numContrato.'-'.$anoContrato.'.pdf';
	$arquivo = $pastaCompleta.'/'.$nomeArquivo;

	if (is_file($arquivo))
	{
		downloadFile($arquivo, $nomeArquivo);
	}
	
	$pdf2=new PDF();
	$pdf2->SetAutoPageBreak(false,30);
	$pdf2->SetMargins(20,25);
	$pdf2->SetLineWidth(0.5);
	
	$pdf2->numContrato = $numContrato;
	$pdf2->anoContrato = $anoContrato;
	
	$pdf2->SetStyle("b","arial","B",11,"0,0,0");
	$pdf2->SetStyle("p","arial","",11,"0,0,0",15);
	
	$pdf2->AliasNbPages('{totalPages}');
	$pdf2->AddPage();
	
	$pdf2->SetFont('Arial','B',11);
	$pdf2->Cell(0,5,"ANEXO I",0,1,'C',0);
	$pdf2->Cell(0,5,"CONTRATO Nº ".$numContrato.'/'.$anoContrato,0,1,'C',0);
	$pdf2->Cell(0,15,"REMUNERAÇÃO DOS SERVIÇOS",0,1,'L',0);
	
	$pdf2->SetFont('Arial','',11);
	
	$texto = "<p>O valor da remuneração da <b>CONTRATADA</b>, referida na <b>CLÁUSULA SEGUNDA</b>, deste contrato, considerando as atividades especÍficas que compÕem,
	os serviÇos contratados, será de <b>{{pagamento}}</b>, preço fixo e irreajustável. Sendo realizada a medição da Contratada, referida na cláusula oitava, deste contrato,  
	referente ao período de ".$periodoRef." de cada mês.</p>";
	
	foreach($variaveis as $var => $conteudo)
	{
		$texto = str_replace($var, $conteudo, $texto);
	}
	
	$pdf2->WriteTag(0,7,$texto,0,'J',0,0);
	$pdf2->ln(5);
	
	$pdf2->SetY(80);
	criarAssinaturas($pdf2, $nomeSocio, $empresaFunc, $testemunhas, $dataInicio);
	
	########################################## FIM CAPA DO CONTRATO #########################################################
	
	$pdf2->Output($arquivo, 'F');
	downloadFile($arquivo, $nomeArquivo);
}

function criarAssinaturas(&$pdf, $nomeSocio, $empresaFunc, $testemunhas, $dataInicio)
{
	$db = new banco_dados();
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(0,15,CIDADE . ", ".mysql_php($dataInicio).'.',0,0,'R',0);
	$pdf->Ln(30);
	
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(0,5,"DIRETOR FINANCEIRO",0,1,'C',0);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(0,5,"Diretor Comercial e Financeiro",0,1,'C',0);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(0,5,NOME_EMPRESA,0,1,'C',0);
	$pdf->Ln(15);
	
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(0,5,"DIRETOR COMERCIAL",0,1,'C',0);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(0,5,"Diretor Geral",0,1,'C',0);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(0,5,NOME_EMPRESA,0,1,'C',0);
	
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(0,5,$nomeSocio,0,1,'C',0);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(0,5,"Sócio Diretor",0,1,'C',0);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(0,5,$empresaFunc,0,1,'C',0);
	
	$sql = "SELECT funcionario, identidade_num FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE id_funcionario IN(".$testemunhas.") ";
	$sql .= "AND reg_del = 0 ";
	
	$db->select($sql, 'MYSQL', true);
	
	$pdf->Ln(15);
	foreach($db->array_select as $test)
	{
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(0,5,$test['funcionario'],0,1,'C',0);
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(0,5,"RG: ".$test['identidade_num'],0,1,'C',0);
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(0,5,"TESTEMUNHA",0,1,'C',0);
		
		$pdf->SetY($pdf->GetY() + 15);
	}	
}

?>