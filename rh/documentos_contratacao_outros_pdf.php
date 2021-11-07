<?php
/*
		Ficha do candidato	
		
		Criado por Carlos
		
		local/Nome do arquivo:
		../rh/documentos_contratacao_outros_pdf.php
		
		Versão 0 --> VERSÃO INICIAL - 19/04/2017
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu	
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

require_once(INCLUDE_DIR."WriteTag.php");
require_once(INCLUDE_DIR."antiInjection.php");

$db = new banco_dados();

class PDF extends PDF_WriteTag
{
    public $retangulo = false;
	public $logoSim = true;
	
	function Header()
	{
		$posicao = $this->CurOrientation == 'H' ? 245 : 155;
		if ($this->logoSim)
			$this->Image(DIR_IMAGENS.'logo_pb.png',$posicao,15,40);
	}

	function Footer(){
	    if ($this->retangulo == true)
	       $this->Rect(10, 10, 190, 277, 'D');
	    
	    $this->SetFont('Arial','',8);
		$this->SetY(-18);
		$this->Cell(0,10,$this->PageNo().'/{totalPages}',0,0,'R');
	}
}

if ($_GET['parte'] == 'epi')
{
	//CONSULTA PRINCIPAL
	$sql = "SELECT 
				f.funcionario, sa.setor_aso, ef.empresa_func, f.data_inicio, rf.descricao as atividade, numero_contrato, clt_matricula ";
	$sql .= "FROM ".DATABASE.".funcionarios f ";
	$sql .= "JOIN ".DATABASE.".salarios s ON s.id_salario = f.id_salario AND s.reg_del = 0 ";
	$sql .= "LEFT JOIN ".DATABASE.".empresa_funcionarios ef ON ((f.id_empfunc = ef.id_empfunc OR ef.empresa_socio = f.id_funcionario) AND ef.reg_del = 0) ";
	$sql .= "JOIN ".DATABASE.".setor_aso sa ON sa.id_setor_aso = f.id_setor_aso AND sa.reg_del = 0 ";
	$sql .= "JOIN ".DATABASE.".rh_funcoes rf ON rf.id_funcao = f.id_funcao AND rf.reg_del = 0  ";
	$sql .= "JOIN ".DATABASE.".pj_contratos pc ON pc.id_funcionario = f.id_funcionario AND pc.reg_del = 0 ";
	$sql .= "WHERE f.id_funcionario = ".$_GET['idFuncionario']." ";
	$sql .= "AND f.reg_del = 0 ";
	
	$db->select($sql, "MYSQL", true);
	$regsPrincipal = $db->array_select[0];
	
	//POPULANDO AS VARIÁVEIS
	$empresaFunc = $regsPrincipal['empresa_func'];
	$nomeFuncionario = $regsPrincipal['funcionario'];
	$setorAso = $regsPrincipal['setor_aso'];
	$atividadeFunc = $regsPrincipal['atividade'];
	$dataInicio = !empty($regsPrincipal['data_inicio']) && $regsPrincipal['data_inicio'] != '0000-00-00' ? mysql_php($regsPrincipal['data_inicio']) : '';
	$anoContrato = substr($regsPrincipal['numero_contrato'], -4);
	$numContrato = str_replace($anoContrato, '', $regsPrincipal['numero_contrato']);
	$matricula = $regsPrincipal['clt_matricula'];
	
	$pdf=new PDF();
	$pdf->SetMargins(15,10);
	$pdf->SetAutoPageBreak(false,15);
	$pdf->SetLineWidth(0.2);
	
	if (!empty($empresaFunc))
	{
		$pdf->logoSim = false;
	}
		
	$pdf->SetStyle("b","arial","B",10,"0,0,0");
	$pdf->SetStyle("p","arial","",10,"0,0,0",15);
	
	$pdf->AliasNbPages('{totalPages}');
	$pdf->AddPage('H');
	$pdf->SetMargins(15,25);
	
	$pdf->setX(0);
	$pdf->WriteTag(0,7,"<p><b>FICHA DE CONTROLE DE DISTRIBUIÇÃO DE EPI`S</b></p>",0,'J',0);
	
	$pdf->Ln(5);
	
	if (!empty($empresaFunc))
	{
		$textoEmpresa = $empresaFunc;
		$textoFuncionario[0] = "Nome do Subcontratado:";
		$textoFuncionario[1] = $nomeFuncionario;
	}
	else
	{
		$textoEmpresa = " LTDA";
		$textoFuncionario[0] = "Nome do Contratado:";
		$textoFuncionario[1] = $nomeFuncionario;
	}
	
	$x = $pdf->getX();
	$y = $pdf->getY();
	$pdf->SetFont('Arial','B',9);
	$pdf->MultiCell(30,4,$textoFuncionario[0],1,'L',0);
	$pdf->SetFont('Arial','',9);
	
	$pdf->setXY($x+30,$y);
	$pdf->MultiCell(100,8,$textoFuncionario[1],1,'L',0);
	
	$pdf->setXY($x+130,$y);
	$pdf->SetFont('Arial','B',9);
	$pdf->MultiCell(30,4,'Empresa Contratada',1,'L',0);
	$pdf->SetFont('Arial','',9);
	$pdf->setXY($x+160,$y);
	$pdf->MultiCell(110,8,$textoEmpresa,1,'L',0);
	
	
	$x = $pdf->getX();
	$y = $pdf->getY();
	$pdf->SetFont('Arial','B',9);
	$pdf->MultiCell(30,8,'Cargo',1,'L',0);
	$pdf->SetFont('Arial','',9);
	
	$pdf->setXY($x+30,$y);
	$pdf->MultiCell(100,8,$atividadeFunc,1,'L',0);
	
	$pdf->setXY($x+130,$y);
	$pdf->SetFont('Arial','B',9);
	$pdf->MultiCell(30,8,'Data Contratual',1,'L',0);
	$pdf->SetFont('Arial','',9);
	$pdf->setXY($x+160,$y);
	$pdf->MultiCell(30,8,$dataInicio,1,'L',0);
	
	$pdf->setXY($x+190,$y);
	$pdf->SetFont('Arial','B',9);
	$pdf->MultiCell(20,8,'Matrícula',1,'L',0);
	$pdf->SetFont('Arial','',9);
	$pdf->setXY($x+210,$y);
	
	if (intval($matricula) > 0)
		$pdf->MultiCell(60,8,$matricula,1,'L',0);
	else
		$pdf->MultiCell(60,8,$numContrato.'/'.$anoContrato,1,'L',0);
	
	$pdf->Ln(5);
	
	# CLAUSULAS #
	$sql = "SELECT * FROM ".DATABASE.".pj_clausula ";
	$sql .= "JOIN ".DATABASE.".pj_tipo_contratacao ON id_tipo_contratacao = id_tipo_contrato AND pj_tipo_contratacao.reg_del = 0 ";
	$sql .= "WHERE tipo_contratacao = 'FICHA DE EPI' ";
	$sql .= "AND pj_clausula.reg_del = 0 ";
	$sql .= "ORDER BY numero ";
	
	montarClausulas($sql, $pdf, array(), true);
		
	//Tabela de epis fornecidos
	$pdf->Ln();
	$pdf->SetFont('Arial','B',8);
	
	$y = $pdf->getY();
	
	$pdf->setX(20);
	$pdf->MultiCell(20,5,"data",1,'C',0);
	
	$pdf->setXY(40,$y);
	$pdf->MultiCell(12,5,"Quant.",1,'C',0);
	
	$pdf->setXY(52,$y);
	$pdf->MultiCell(24,5,"CA",1,'C',0);
	
	$pdf->setXY(76,$y);
	$pdf->MultiCell(90,5,"Descrição do EPI",1,'C',0);
	
	$pdf->setXY(166,$y);
	$pdf->MultiCell(40,5,"Visto após o Recebimento",1,'C',0);
	
	$pdf->setXY(206,$y);
	$pdf->MultiCell(30,5,"Data de Devolução",1,'C',0);

	$pdf->setXY(236,$y);
	$pdf->MultiCell(45,5,"Visto após a Devolução",1,'C',0);
	
	$pdf->SetFont('Arial','',8);
	
	$sql = "SELECT epi, fxe_data_entrega, ca, fxe_qtd FROM ".DATABASE.".funcionario_x_epi a
			JOIN ".DATABASE.".epi b ON id_epi = fxe_id_epi  
			WHERE a.reg_del = 0 
			AND b.reg_del = 0 
			AND fxe_id_funcionario = ".$_GET['idFuncionario'];
	
	$total = 21;
	
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$pdf, &$total){
		$pdf->setX(20);
		$pdf->Cell(20, 7, mysql_php($reg['fxe_data_entrega']), 1, 0, 'L');
		$pdf->Cell(12, 7, $reg['fxe_qtd'], 1, 0, 'L');
		$pdf->Cell(24, 7, $reg['ca'], 1, 0, 'L');
		$pdf->Cell(90, 7, $reg['epi'], 1, 0, 'L');
		$pdf->Cell(40, 7, '', 1, 0, 'L');
		$pdf->Cell(30, 7, '', 1, 0, 'L');
		$pdf->Cell(45, 7, '', 1, 1, 'L');
		
		$total --;
	});

	$pulou = false;
	for($i=0;$i<$total;$i++)
	{
		if ($pdf->getY() >= 190 && !$pulou)
		{
			$pdf->addPage('H');
			$pdf->SetFont('Arial','B',8);
			$y = $pdf->getY();
			$pdf->setX(20);
			$pdf->MultiCell(20,5,"Data",1,'C',0);
			
			$pdf->setXY(40,$y);
			$pdf->MultiCell(12,5,"Quant.",1,'C',0);
			
			$pdf->setXY(52,$y);
			$pdf->MultiCell(24,5,"CA",1,'C',0);
			
			$pdf->setXY(76,$y);
			$pdf->MultiCell(90,5,"Descrição do EPI",1,'C',0);
			
			$pdf->setXY(166,$y);
			$pdf->MultiCell(40,5,"Visto após o Recebimento",1,'C',0);
			
			$pdf->setXY(206,$y);
			$pdf->MultiCell(30,5,"Data de Devolução",1,'C',0);
		
			$pdf->setXY(236,$y);
			$pdf->MultiCell(45,5,"Visto após a Devolução",1,'C',0);
			
			$pulou = true;
		}
		
		$pdf->setX(20);
		$pdf->Cell(20, 7, '', 1, 0, 'L');
		$pdf->Cell(12, 7, '', 1, 0, 'L');
		$pdf->Cell(24, 7, '', 1, 0, 'L');
		$pdf->Cell(90, 7, '', 1, 0, 'L');
		$pdf->Cell(40, 7, '', 1, 0, 'L');
		$pdf->Cell(30, 7, '', 1, 0, 'L');
		$pdf->Cell(45, 7, '', 1, 1, 'L');
	}
	
	$pdf->ln(15);
	$pdf->Cell(102,5,"Assinatura _____________________________________________________",0,0,'L',0);
	$pdf->Cell(20,5,"Data ______/______/______",0,0,'L',0);
	
	$pdf->ln(15);
	$pdf->Cell(85,5,"Data de baixa dos materiais devolvidos: ______/______/______",0,0,'L',0);
	$pdf->Cell(20,5,"Nome Legível ______________________________________________________",0,0,'L',0);
	
	# FIM DAS CLAUSULAS #
	$pdf->Output();
}

if ($_GET['parte'] == 'termo_ti')
{
	$sql = "SELECT funcionario, id_funcionario, year(data_inicio) ano, month(data_inicio) mes, day(data_inicio) dia
			FROM ".DATABASE.".funcionarios 
			WHERE id_funcionario = ".$_GET['idFuncionario']." AND reg_del = 0 ";
	
	$db->select($sql, 'MYSQL', true);
	
	$pdf=new PDF();
	$pdf->SetMargins(15,10);
	$pdf->SetAutoPageBreak(true,30);
	$pdf->SetLineWidth(0.5);
	
	$pdf->SetStyle("b","arial","B",11,"0,0,0");
	$pdf->SetStyle("p","arial","",11,"0,0,0",15);
	
	$pdf->AliasNbPages('{totalPages}');
	$pdf->AddPage();
	$pdf->SetMargins(15,25);

	$pdf->setY(30);
	
	# CLAUSULAS #
	$sql = "SELECT * FROM ".DATABASE.".pj_clausula ";
	$sql .= "JOIN ".DATABASE.".pj_tipo_contratacao ON id_tipo_contratacao = id_tipo_contrato AND pj_tipo_contratacao.reg_del = 0 ";
	$sql .= "WHERE tipo_contratacao = 'TERMO RESPONSABILIDADE TI' ";
	$sql .= "AND pj_clausula.reg_del = 0 ";
	$sql .= "ORDER BY numero";
	
	montarClausulas($sql, $pdf);
	
	$db->array_select[0]['dia'] = sprintf('%02d', $db->array_select[0]['dia']);
	
	$mes = meses(intval($db->array_select[0]['mes']-1), 1);
	$texto = "<b>".CIDADE.", ".$db->array_select[0]['dia']." de ".$mes." de ".$db->array_select[0]['ano']."</b>";
		
	$pdf->WriteTag(0, 5, $texto,0,'J',0,0);
	$pdf->Ln(10);
	
	$pdf->Cell(20,5,"_____________________________________________________",0,1,'L',0);
	$pdf->Cell(20,5,$db->array_select[0]['funcionario'],0,0,'L',0);
	
	# FIM DAS CLAUSULAS #
	$pdf->Output();
}
else if ($_GET['parte'] == 'ios')
{
	//CONSULTA PRINCIPAL
	$sql = "SELECT 
				f.funcionario, sa.setor_aso, ef.empresa_func, data_inicio, rf.descricao as atividade,
				year(f.data_inicio) ano, month(f.data_inicio) mes, day(f.data_inicio) dia ";
	$sql .= "FROM ".DATABASE.".funcionarios f ";
	$sql .= "JOIN ".DATABASE.".salarios s ON s.id_salario = f.id_salario AND s.reg_del = 0 ";
	$sql .= "LEFT JOIN ".DATABASE.".empresa_funcionarios ef ON (f.id_empfunc = ef.id_empfunc OR ef.empresa_socio = f.id_funcionario AND ef.reg_del = 0) ";
	$sql .= "JOIN ".DATABASE.".setor_aso sa ON sa.id_setor_aso = f.id_setor_aso AND sa.reg_del = 0 ";
	$sql .= "JOIN ".DATABASE.".rh_funcoes rf ON rf.id_funcao = f.id_funcao AND rf.reg_del = 0  ";
	$sql .= "WHERE f.id_funcionario = ".$_GET['idFuncionario']." ";
	$sql .= "AND f.reg_del = 0 ";
	
	$db->select($sql, "MYSQL", true);
	
	$regsPrincipal = $db->array_select[0];
	
	//POPULANDO AS VARIÁVEIS
	$empresaFunc = $regsPrincipal['empresa_func'];
	$nomeFuncionario = $regsPrincipal['funcionario'];
	$setorAso = $regsPrincipal['setor_aso'];
	$atividadeFunc = $regsPrincipal['atividade'];
	$dataInicio = mysql_php($regsPrincipal['data_inicio']);
	
	//Trocas de conteudo dinâmico dentro das clausulas aqui
	$variaveis = array(
		'{{nome_empresa}}' 	=> $empresaFunc,
		'{{ATIVIDADE}}' => $atividadeFunc,
		'{{descricoes_empresa}}' => !empty($empresaFunc) ? 'informamos ao representante da empresa '.$empresaFunc : 'informamos a todos os colaboradores da '
	);
	
	$pdf=new PDF();
	$pdf->SetMargins(15,10);
	$pdf->SetAutoPageBreak(true,30);
	$pdf->SetLineWidth(0.5);
	
	if (!empty($empresaFunc))
	{
		$pdf->logoSim = false;
	}
	
	$pdf->SetStyle("b","arial","B",11,"0,0,0");
	$pdf->SetStyle("p","arial","",11,"0,0,0",15);
	
	$pdf->AliasNbPages('{totalPages}');
	$pdf->AddPage();
	$pdf->SetMargins(15,25);

	$pdf->setX(0);
	$pdf->WriteTag(0,7,"<p><b>IOS (Instrução de Ordem de Serviço)</b></p><p><b>DATA: </b>".$dataInicio."</p>",0,'J',0);
	
	$pdf->Ln(5);
	if (!empty($empresaFunc))
	{
		$pdf->setX(0);
		$pdf->WriteTag(0,5,"<p><b>EMPRESA: </b>".$empresaFunc."</p>",0,'J',0);
		$pdf->Ln();
	}
	
	$pdf->setX(0);
	$pdf->WriteTag(0,5,"<p><b>ATIVIDADE: </b>".$atividadeFunc."</p>",0,'J',0);
	$pdf->Ln();
	
	$pdf->setX(0);
	$pdf->WriteTag(0,5,"<p><b>SETOR: </b>".$setorAso."</p>",0,'J',0);
	$pdf->Ln(10);
	
	
	# CLAUSULAS #
	$sql = "SELECT * FROM ".DATABASE.".pj_clausula ";
	$sql .= "JOIN ".DATABASE.".pj_tipo_contratacao ON id_tipo_contratacao = id_tipo_contrato AND pj_tipo_contratacao.reg_del = 0 ";
	$sql .= "WHERE tipo_contratacao = 'IOS' ";
	$sql .= "AND pj_clausula.reg_del = 0 ";
	$sql .= "ORDER BY numero";
	
	montarClausulas($sql, $pdf, $variaveis);
	
	$db->array_select[0]['dia'] = sprintf('%02d', $db->array_select[0]['dia']);
	
	$mes = meses(intval($db->array_select[0]['mes']-1), 1);
	$texto = "<b>Mogi das Cruzes, ".$db->array_select[0]['dia']." de ".$mes." de ".$db->array_select[0]['ano']."</b>";
	
	$pdf->Ln(5);
	$pdf->WriteTag(0, 5, "<b>Recebi a 1ª via deste documento</b>",0,'J',0,0);
	$pdf->Ln(10);
	
	$pdf->WriteTag(0, 5, $texto,0,'J',0,0);
	$pdf->Ln(10);
	
	$pdf->Cell(20,5,"_____________________________________________________",0,1,'L',0);
	$pdf->Cell(20,5,$nomeFuncionario,0,0,'L',0);
	
	# FIM DAS CLAUSULAS #
	$pdf->Output();
}
else if ($_GET['parte'] == 'seguro')
{
	//downloadFile('./modelos_pdf/formulario_seguro.pdf');
	exit;
}
else if ($_GET['parte'] == 'aditamento')
{
    //CONSULTA PRINCIPAL    
    $sql = "SELECT
			f.funcionario, pc.numero_contrato, pc.data_inicio, pc.data_fim, ef.empresa_func, ef.empresa_cnpj, ef.empresa_cidade, ef.empresa_estado, ef.empresa_endereco,
			ef.empresa_bairro, id_tipo_contratacao, sa.setor_aso, salario_mensalista, salario_hora, testemunha_1, testemunha_2, l.descricao local_trabalho ";
    $sql .= "FROM ".DATABASE.".funcionarios f ";
    $sql .= "JOIN ".DATABASE.".local l ON l.id_local = f.id_local ";
    $sql .= "JOIN ".DATABASE.".salarios s ON s.id_salario = f.id_salario ";
    $sql .= "JOIN ".DATABASE.".empresa_funcionarios ef ON (ef.empresa_socio = f.id_funcionario) AND ef.reg_del = 0 ";
    $sql .= "JOIN ".DATABASE.".pj_contratos pc ON pc.id_funcionario = f.id_funcionario AND pc.reg_del = 0 ";
    $sql .= "JOIN ".DATABASE.".setor_aso sa ON sa.id_setor_aso = f.id_setor_aso AND sa.reg_del = 0 ";
    $sql .= "WHERE f.id_funcionario = ".$_GET['idFuncionario']." ";
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
        '{{data_contrato}}'	=> mysql_php($dataInicio),
        '{{final_contrato}}'	=> mysql_php($dataFinal),
        '{{pagamento}}'			=> 'R$ '.$valorContrato.$complValorCont,
        '{{local_trabalho}}'	=> $localTrabalho,
        '{{contrato_numero}}' => $numContrato,
        '{{contrato_ano}}' => $anoContrato,
        '{{prazo_inicio}}' => $_GET['prazo_inicio'],
        '{{prazo_final}}' => $_GET['prazo_final'],
        '{{prazo_meses}}' => $_GET['prazo_meses']
    );
    
    $pdf=new PDF();
    $pdf->retangulo = true;
    $pdf->SetMargins(10,10,10);
    $pdf->SetAutoPageBreak(true,30);
    $pdf->SetLineWidth(0.5);
    
    $pdf->SetStyle("b","arial","B",11,"0,0,0");
    $pdf->SetStyle("p","arial","",11,"0,0,0",15);
    
    $pdf->AliasNbPages('{totalPages}');
    $pdf->AddPage();
    $pdf->SetMargins(15,25);
        
    $pdf->setXY(0,30);
    $pdf->WriteTag(0,7,"<p><b>".$_GET['numero_termo']."TERMO DE ADITAMENTO AO CONTRATO Nº ".$numContrato.'/'.$anoContrato."</b></p>",0,'C',0);
    
    # CLAUSULAS #
    $sql = "SELECT * FROM ".DATABASE.".pj_clausula ";
    $sql .= "JOIN ".DATABASE.".pj_tipo_contratacao ON id_tipo_contratacao = id_tipo_contrato AND pj_tipo_contratacao.reg_del = 0 ";
    $sql .= "WHERE tipo_contratacao = 'aditamento' ";
	$sql .= "AND pj_clausula.reg_del = 0 ";
    $sql .= "ORDER BY numero";
    
    montarClausulas($sql, $pdf, $variaveis);
    
    $sql = "SELECT funcionario, id_funcionario, year(data_inicio) ano, month(data_inicio) mes, day(data_inicio) dia
			FROM ".DATABASE.".funcionarios 
			WHERE id_funcionario = ".$_GET['idFuncionario']." 
			AND reg_del = 0 ";
   
    $db->select($sql, 'MYSQL', true);
    
    $db->array_select[0]['dia'] = sprintf('%02d', $db->array_select[0]['dia']);
    ########################################## FIM DAS CLAUSULAS ############################################################
    
    ########################################## ÚLTIMA PAGINA - ASSINATURAS ##################################################
    $pdf->AddPage();
    criarAssinaturas($pdf, $nomeSocio, $empresaFunc, $testemunhas, $_GET['data_emissao']);
    $pdf->Output();
}

function criarAssinaturas(&$pdf, $nomeSocio, $empresaFunc, $testemunhas, $dataInicio)
{
    $db = new banco_dados();
    if (empty($testemunhas))
    {
        exit('Por favor, salve o registro antes de gerar este arquivo. Lembrando-se de selecionar as testemunhas!');    
    }
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(0,15,"Mogi das Cruzes, ".mysql_php($dataInicio),0,0,'R',0);
    $pdf->Ln(30);
    
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(0,5,"DIRETOR FINANCEIRO",0,1,'C',0);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,5,"Diretor ".DATABASE." e Financeiro",0,1,'C',0);
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

function montarClausulas($sql, &$pdf, $variaveis = array(), $bordas = 0)
{
	$db = new banco_dados();
	
	$db->select($sql,'MYSQL', function($reg, $i) use(&$pdf, &$variaveis){
		//Demais clausulas
		$pdf->SetFont('Arial','B',11);
		
		foreach($variaveis as $var => $conteudo)
		{
			$reg['descricao_clausula'] = str_replace($var, $conteudo, $reg['descricao_clausula']);
			$reg['clausula'] = str_replace($var, $conteudo, $reg['clausula']);
		}
		
		$pdf->MultiCell(0,7,$reg['clausula'],0,'L',0);
		
		$inicioPosTabelaCores = strpos($reg['descricao_clausula'], '{{tabela_cores}}');
		
		$pdf->SetFont('Arial','',11);
		
		if ($inicioPosTabelaCores)
		{
			$finalPosTabelaCores = $inicioPosTabelaCores+strlen('{{tabela_cores}}');
			
			//Quanto achamos a tag {{tabela_cores}}, dividimos a string em uma parte anterior e uma posterior colocando entre elas uma tabela predefinida
			$pdf->WriteTag(0,7,substr($reg['descricao_clausula'], 0, $inicioPosTabelaCores),0,'J',0,0);
			tabelaCores($pdf);
			$pdf->WriteTag(0,5,substr($reg['descricao_clausula'], $finalPosTabelaCores),0,'J',0,0);
		}
		else
		{
			$texto = $reg['descricao_clausula'];
			$pdf->WriteTag(0,7,$texto,0,'J',0,0);
		}
		
		$pdf->ln(5);
	});
}

function tabelaCores(&$pdf)
{
	$pdf->Ln();
	$pdf->SetFont('Arial','B',9);
	$pdf->setX(30);
	$pdf->Cell(20,5,"Cores",1,0,'L',0);
	$pdf->Cell(100,5,"Materiais",1,1,'L',0);
		
	$pdf->SetFont('Arial','',9);
	
	$pdf->SetFillColor(255, 255, 0);
	$pdf->setX(30);
	$pdf->Cell(20,5,"Amarela",1,0,'L',1);
	$pdf->Cell(100,5,"Metal",1,1,'L',0);
	
	$pdf->SetFillColor(255, 0, 0);
	$pdf->setX(30);
	$pdf->Cell(20,5,"Vermelha",1,0,'L',1);
	$pdf->Cell(100,5,"Embalagens, frascos, sacos plásticos, etc.",1,1,'L',0);
	
	$pdf->SetFillColor(0, 0, 255);
	$pdf->SetTextColor(255, 255, 255);
	$pdf->setX(30);
	$pdf->Cell(20,5,"Azul",1,0,'L',1);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,5,"Folhas, embalagens de papel, embalagens de papelão, etc.",1,1,'L',0);
	
	$pdf->SetFillColor(100, 100, 100);
	$pdf->setX(30);
	$pdf->Cell(20,5,"Cinza",1,0,'L',1);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,5,"Restos de alimentos, cascas de frutas, etc.",1,1,'L',0);
	
	$pdf->SetFillColor(0, 155, 0);
	$pdf->setX(30);
	$pdf->Cell(20,5,"Verde",1,0,'L',1);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,5,"Embalagens, pedaços de vidro, frascos de vidro, etc.",1,1,'L',0);
}

?>