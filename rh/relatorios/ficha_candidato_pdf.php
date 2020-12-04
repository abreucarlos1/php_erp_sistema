<?php
/*
		Ficha do candidato	
		
		Criado por Carlos Eduardo
		
		local/Nome do arquivo:
		../rh/controle_aprovados.php
	
		Versão 0 --> VERSÃO INICIAL - 04/05/2016
		Versão 1 --> 28/03/2006
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu	
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{
	public $cargo = '';
	public $salario = '';
	function Header()
	{
		$this->Image(DIR_IMAGENS.'logo_pb.png',26,10,40);
		
		$this->SetFont('Arial','',6);
		$this->Cell(158,4,'DATA:',0,0,'R',0); //aqui
		$this->Cell(12,4,$this->emissao(),0,1,'R',0); //aqui
	}

	function Footer(){}
}

$db = new banco_dados;

//Instanciation of inherited class
$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(false,30);
$pdf->SetMargins(15,10);
$pdf->SetLineWidth(0.5);

//Seta o cabeçalho
$pdf->titulo="FICHA CADASTRAL";
$pdf->emissao=date("d/m/Y");

$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetLineWidth(0.1);
$sql = "SELECT
			*
		FROM
			".DATABASE.".candidatos
			LEFT JOIN(
				SELECT
					cdp_candidato_id, cdp_idade, cdp_sexo, cdp_etnia, cdp_data_nasc, 
                    cdp_peso, cdp_tp_sangue, cdp_altura, cdp_naturalidade,
                    cdp_uf_nasc, cdp_est_civil, cdp_data_casamento,
                    cdp_n_filhos, cdp_nome_conjuge, cdp_nome_pai, cdp_nome_mae,
                    cdp_endereco, cdp_bairro, cdp_cidade, cdp_uf, cdp_cep, cdp_fone,
                    cdp_cel, cdp_fone_recados, cdp_agencia, cdp_cc
				FROM
					".DATABASE.".candidatos_dados_pessoais
			        LEFT JOIN (
			            SELECT * FROM ".DATABASE.".bancos WHERE bancos.reg_del = 0
			        ) bancos
		        	ON id_banco = cdp_banco 
			) dp
			ON cdp_candidato_id = id
			JOIN(
				SELECT CONVERT(CONCAT(id_funcao,',',id_cargo_grupo) USING utf8) as cargo_id, descricao as Descricao_cargo FROM ".DATABASE.".rh_funcoes WHERE rh_funcoes.reg_del = 0
			) cargo
		  	ON cargo_id = cargo_pretendido
			LEFT JOIN(
				SELECT * FROM ".DATABASE.".candidatos_documentos WHERE candidatos_documentos.reg_del = 0
			) cd
			ON cd_candidato_id = id
			LEFT JOIN(
				SELECT * FROM ".DATABASE.".candidatos_emprego_anterior WHERE candidatos_emprego_anterior.reg_del = 0
			) cea
			ON cea_candidato_id = id
			LEFT JOIN(
				SELECT * FROM ".DATABASE.".candidatos_informacoes_adicionais WHERE candidatos_informacoes_adicionais.reg_del = 0
			) cia
			ON cia_candidato_id = id
			LEFT JOIN(
				SELECT * FROM ".DATABASE.".candidatos_epi WHERE candidatos_epi.reg_del = 0
			) ce
			ON ce_candidato_id = id
			LEFT JOIN(
				SELECT * FROM ".DATABASE.".candidatos_interno WHERE candidatos_interno.reg_del = 0
			) cdvm
			ON cdvm_candidato_id = id
		WHERE
			candidatos.reg_del = 0
			AND id = '{$_GET['idCandidato']}'";

$db->select($sql, 'MYSQL', function($reg, $i) use(&$pdf){
    $arrayAuxiliar = array(
		'sexo' => array(
			'M' => 'MASCULINO',
			'F' => 'FEMININO'
		),
		'estado_civil' => array(
			'C' => 'CASADO',
			'S' => 'SOLTEIRO',
			'V' => 'VIÚVO'
		),
		'oculos' => array(
			0 => 'Sobrepor',
			1 => 'Comum'
		),
	);

	$sql = "SELECT
				DISTINCT X5_TABELA, X5_CHAVE, X5_DESCRI
			FROM
				SX5010
			WHERE
				D_E_L_E_T_ = '' 
				AND (
					(X5_TABELA IN('34') AND X5_CHAVE = '{$reg['cdp_nacionalidade']}')
				)";
	
	$db2 = new banco_dados();
	$db2->select($sql, 'MSSQL', function($regs, $j) use(&$arrayAuxiliar){
		$arrayAuxiliar['nacionalidade'] = $regs['X5_DESCRI'];
	});
	
	$pdf->SetLineWidth(0.3);
	$pdf->Ln(5);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(190,4,'FICHA CADASTRAL',0,0,'R',0);
	
	$pdf->SetFillColor(230,230,230);
	
	$pdf->SetDrawColor(0,0,0);
				
	$pdf->Line(25,40,195,40);
	$pdf->SetXY(25,28);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(10, 5, 'Cargo: ', 0, 0, 'L', 0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(128, 5, $reg['Descricao_cargo'], 0, 0, 'L', 0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(12, 5, 'Salário: ', 0, 0, 'L', 0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(100, 5, 'R$ '.number_format($reg['salario_pretendido'], 2, ',', '.'), 0, 1, 'L', 0);
	
	$pdf->Ln(5);
	
	//DADOS PESSOAIS
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(190,5,"DADOS PESSOAIS:",0,1,'L',1);
	$pdf->Ln(2);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(10,5,'Nome:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(45,5,$reg['nome'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(10,5,'Sexo:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(20,5,$arrayAuxiliar['sexo'][$reg['cdp_sexo']],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(10,5,'Idade:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(6,5,$reg['cdp_idade'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(9,5,'Etnia:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(15,5,$reg['cdp_etnia'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(30,5,'data de Nascimento:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(15,5,mysql_php($reg['cdp_data_nasc']),'0',1,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(9,5,'Peso:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(8,5,$reg['cdp_peso'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(23,5,'tipo Sanguíneo:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(8,5,$reg['cdp_tp_sangue'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(10,5,'Altura:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(8,5,number_format($reg['cdp_altura'], 2, ',', '.'),'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(22,5,'Nacionalidade:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(25,5,$arrayAuxiliar['nacionalidade'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(16,5,'Natural de:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(41,5,$reg['cdp_naturalidade'],'0',1,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(11,5,'estado:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(8,5,$reg['cdp_uf_nasc'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(18,5,'Estado Civil:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(18,5,$arrayAuxiliar['estado_civil'][$reg['cdp_est_civil']],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(24,5,'Data Casamento:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(18,5,mysql_php($reg['cdp_data_casamento']),'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(14,5,'N. Filhos:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(8,5,$reg['cdp_n_filhos'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(13,5,'Cônjuge:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(38,5,$reg['cdp_nome_conjuge'],'0',1,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(15,5,'Nome Pai:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(67,5,$reg['cdp_nome_pai'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(16,5,'Nome Mãe:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(72,5,$reg['cdp_nome_mae'],'0',1,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(16,5,'Endereço:',0,1,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(122,5,$reg['cdp_endereco'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(16,5,'Bairro:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(46,5,$reg['cdp_bairro'],'0',1,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(12,5,'Cidade:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(54,5,$reg['cdp_cidade'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(12,5,'estado:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(8,5,$reg['cdp_uf'],'0',0,'L',0);
		
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(8,5,'cep:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(16,5,$reg['cdp_cep'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(9,5,'Fone:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(22,5,$reg['cdp_fone'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(7,5,'Cel:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(22,5,$reg['cdp_cel'],'0',1,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(22,5,'Fone Recados:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(22,5,$reg['cdp_fone_recados'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(11,5,'E-Mail:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(55,5,$reg['email'],'0',1,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(11,5,'Banco:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(40,5,$reg['instituicao'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(13,5,'Agência:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(10,5,$reg['cdp_agencia'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(7,5,'C/C:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(15,5,$reg['cdp_cc'],'0',1,'L',0);
	
	$pdf->Ln();
	
	//DOCUMENTOS
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(190,5,"DOCUMENTOS:",0,1,'L',1);
	$pdf->Ln(2);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(35,5,'Carteira Profissional Nº:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(18,5,$reg['cd_ctps'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(10,5,'Série:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(11,5,$reg['cd_ctps_serie'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(14,5,'Emissão:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(16,5,mysql_php($reg['cd_ctps_emissao']),'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(25,5,'Título Eleitor Nº:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(22,5,$reg['cd_titulo_eleitor'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(9,5,'Zona:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(10,5,$reg['cd_titulo_zona'],'0',1,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(11,5,'Seção:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(12,5,$reg['cd_titulo_secao'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(10,5,'RG Nº:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(20,5,$reg['cd_rg'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(19,5,'RG Emissão:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(16,5,mysql_php($reg['cd_rg_emissao']),'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(23,5,'Órgão Emissor:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(12,5,$reg['cd_rg_orgao'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(10,5,'CPF:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(23,5,$reg['cd_cpf'],'0',1,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(11,5,'PIS Nº:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(20,5,$reg['cd_pis'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(21,5,'Reservista Nº:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(20,5,$reg['cd_pis'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(10,5,'Série:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(13,5,$reg['cd_reservista_serie'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(27,5,'Reservista cidade:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(48,5,$reg['cd_reservista_cidade'],'0',1,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(10,5,'CNPJ:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(27,5,$reg['cd_cnpj'],'0',0,'L',0);
	
	$simples = $reg['cd_opcao'] == 1 ? 'X' : '';
	$lucroPresumido = $reg['cd_opcao'] == 2 ? 'X' : ''; 
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(30,5,"Optante Simples ( {$simples} )",0,0,'L',0);
	$pdf->Cell(30,5,"Lucro Presumido ( {$lucroPresumido} )",0,1,'L',0);
	
	$pdf->ln();
	
	//FORMAÇÃO
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(190,5,"FORMAÇÃO:",0,1,'L',1);
	$pdf->Ln(2);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(35,5,'Curso/Modalidade',0,0,'L',0);
	$pdf->Cell(67,5,'Instituição',0,0,'L',0);
	$pdf->Cell(18,5,'Ano início',0,0,'L',0);
	$pdf->Cell(18,5,'Ano término',0,0,'L',0);
	$pdf->Cell(18,5,'Completo',0,0,'L',0);
	$pdf->Cell(18,5,'Até Série',0,1,'L',0);
	
	$sql = "SELECT
				*
			FROM
				".DATABASE.".candidatos_formacao
				LEFT JOIN(
				  SELECT id_rh_instituicao_ensino, instituicao_ensino FROM ".DATABASE.".rh_instituicao_ensino WHERE rh_instituicao_ensino.reg_del = 0
				) inst
				ON id_rh_instituicao_ensino = cf_instituicao
			WHERE
				cf_candidato_id = {$reg['id']}";
	
	$pdf->SetFont('Arial','',7);
	$db2->select($sql, 'MYSQL', function($regCF, $k) use($pdf){
		$pdf->Cell(35,5,$regCF['cf_curso'],0,0,'L',0);
		$pdf->Cell(67,5,$regCF['instituicao_ensino'],0,0,'L',0);
		$pdf->Cell(18,5,$regCF['cf_mes_inicio'],0,0,'L',0);
		$pdf->Cell(18,5,$regCF['cf_mes_conclusao'],0,0,'L',0);
		$pdf->Cell(18,5,$regCF['cf_completou'],0,0,'L',0);
		$pdf->Cell(18,5,$regCF['cf_serie'],0,1,'L',0);
	});

	$pdf->ln();
	
	//CURSOS DE ESPECIALIZAÇÃO
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(190,5,"CURSOS DE ESPECIALIZAÇÃO:",0,1,'L',1);
	$pdf->Ln(2);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(35,5,'Idioma',0,0,'L',0);
	$pdf->Cell(67,5,'Instituição',0,0,'L',0);
	$pdf->Cell(18,5,'Durção',0,0,'L',0);
	$pdf->Cell(18,5,'Término',0,0,'L',0);
	$pdf->Cell(18,5,'Nível',0,1,'L',0);
	
	$sql = "SELECT
				*
			FROM
				".DATABASE.".candidatos_cursos
			WHERE
				reg_del = 0
				AND ccu_candidato_id = {$reg['id']}";
	
	$pdf->SetFont('Arial','',7);
	$db2->select($sql, 'MYSQL', function($regCF, $k) use($pdf){
		$pdf->Cell(35,5,$regCF['ccu_curso'],0,0,'L',0);
		$pdf->Cell(67,5,$regCF['ccu_instituicao'],0,0,'L',0);
		$pdf->Cell(18,5,$regCF['ccu_mes_inicio'],0,0,'L',0);
		$pdf->Cell(18,5,$regCF['ccu_mes_conclusao'],0,0,'L',0);
		$pdf->Cell(18,5,$regCF['ccu_nivel'],0,1,'L',0);
	});
	
	$pdf->Ln();
	
	//EMPREGOS ANTERIORES
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(190,5,"EMPREGO ANTERIOR:",0,1,'L',1);
	$pdf->Ln(2);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(15,5,'Empresa:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(118,5,$reg['cea_empresa'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(15,5,'Telefone:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(22,5,$reg['cea_fone'],'0',1,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(15,5,'Endereço:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(74,5,$reg['cea_endereco'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(15,5,'cidade:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(55,5,$reg['cea_cidade'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(6,5,'UF:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(5,5,$reg['cea_uf'],'0',1,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(23,5,'cargo exercido:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(83,5,$reg['cea_cargo'],'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(16,5,'Admissão:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(16,5,mysql_php($reg['cea_admissao']),'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(16,5,'Demissão:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(16,5,mysql_php($reg['cea_demissao']),'0',1,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(22,5,'Salário Inicial:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(18,5,'R$ '.number_format($reg['cea_sal_ini'], 2, ',', '.'),'0',0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,5,'Salário Final:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(18,5,'R$ '.number_format($reg['cea_sal_fim'], 2, ',', '.'),'0',1,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(45,5,'Descrição sumária das tarefas:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(125,5,$reg['cea_descricao'],0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(25,5,'Motivo da saída:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(145,5,$reg['cea_mot_saida'],0,'L',0);
	
	$pdf->Ln();
	
	$pularPagina = true;
	if ($pdf->getY() >= 200)
	{
		$pdf->addPage();
		$pdf->SetXY(25,24);
		$pularPagina = false;
	}
	
	//INFORMAÇÕES ADICIONAIS
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(190,5,"INFORMAÇÕES ADICIONAIS:",0,1,'L',1);
	$pdf->Ln(2);
	
	$sim = $reg['cia_disp_viagens'] == 1 ? 'X' : '';
	$nao = $reg['cia_disp_viagens'] == 0 ? 'X' : ''; 
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(55,5,'Tem disponibilidade para viagens?',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(12,5,"Sim( {$sim} )",0,0,'L',0);
	$pdf->Cell(12,5,"Não( {$nao} )",0,1,'L',0);
	
	$sim = $reg['cia_disp_cidades'] == 1 ? 'X' : '';
	$nao = $reg['cia_disp_cidades'] == 0 ? 'X' : ''; 
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(80,5,'Tem disponibilidade para trabalhar em outras cidades?',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(12,5,"Sim( {$sim} )",0,0,'L',0);
	$pdf->Cell(12,5,"Não( {$nao} )",0,1,'L',0);
	
	$sim = $reg['cia_disp_turnos'] == 1 ? 'X' : '';
	$nao = $reg['cia_disp_turnos'] == 0 ? 'X' : ''; 
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(70,5,'Tem disponibilidade para trabalhar em turnos?',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(12,5,"Sim( {$sim} )",0,0,'L',0);
	$pdf->Cell(12,5,"Não( {$nao} )",0,1,'L',0);
	
	$sim = $reg['cia_vt'] == 1 ? 'X' : '';
	$nao = $reg['cia_vt'] == 0 ? 'X' : ''; 
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(35,5,'Utiliza vale transporte?',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(12,5,"Sim( {$sim} )",0,0,'L',0);
	$pdf->Cell(12,5,"Não( {$nao} )",0,1,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(52,5,'Quantidade de passagens por dia ida:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(7,5,$reg['cia_qtd_ida'],0,0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(9,5,'Volta:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(7,5,$reg['cia_qtd_volta'],0,1,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(35,5,'Valor por passagem ida:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(15,5,'R$ '.number_format($reg['cia_val_ida'], 2, ',', '.'),0,0,'L',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(9,5,'Volta:',0,0,'L',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(15,5,'R$ '.number_format($reg['cia_val_volta'], 2, ',', '.'),0,1,'L',0);
	
	$pdf->Ln();
	
	//ÉREA TÉCNICA - EPI'S
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(190,5,"ÁREA TÉCNICA - EPI'S:",0,1,'L',1);
	$pdf->Ln(2);
	
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(25,5,'Nº Calçado: '.$reg['ce_num_calcado'],0,0,'L',0);
	$pdf->Cell(25,5,'Tam. Calça: '.$reg['ce_tam_calca'],0,0,'L',0);
	$pdf->Cell(25,5,'Tam. Jaleco: '.$reg['ce_tam_jaleco'],0,0,'L',0);
	$pdf->Cell(35,5,'Tam. Camisa Social: '.$reg['ce_tam_camisa'],0,0,'L',0);
	$pdf->Cell(30,5,'Tipo de óculos: '.$arrayAuxiliar['oculos'][$reg['ce_tp_oculos']],0,1,'L',0);
	
	if($pularPagina)
	{
		$pdf->AddPage();
		$pdf->SetXY(25,24);
	}
	else
		$pdf->ln(20);	
	
	//ÁREA TÉCNICA - EPI'S
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(190,5,"USO INTERNO:",0,1,'C',1);
	$pdf->Ln(2);
	
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(190,5,'Em análise geral, frente ao perfil de '.$reg['nome'].', o candidato NÃO APRESENTA(  ) / APRESENTA(  ) potencial de comportamento e habilidades a ser aproveitado, sendo considerado APTO(  ) / INAPTO(  ) para assumir a posição que se propõe.',0,'L',0);
	
	$pdf->Ln();
	
	$pdf->Cell(60,8,"_______________________, ___/___/____",0,0,'L',0);
	$pdf->Cell(0,8,"Requisitante: ___________________________________________________________",0,1,'L',0);
	$pdf->Cell(60,8,"",0,0,'L',0);
	$pdf->Cell(80,8,"Área: _________________________ Local de Trab.: __________________________",0,0,'L',0);
	
	$pdf->Ln(10);
	
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(190,5,"OBSERVAÇÕES GERAIS:",0,1,'L',0);
	$pdf->Cell(190,20,"",1,0,'L',0);
	$pdf->Ln(25);
	
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(190,5,"Aprovado(  ) / Stand By(  ) / Reprovado(  ):",0,1,'C',0);
	$pdf->Ln();
	
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(138,5,"Aprovado por _____________________________ Depto. _____________________________",0,0,'L',0);
	$pdf->Cell(40,5,"Data ____/____/____",0,0,'L',0);
	$pdf->Ln(10);
	
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(138,5,"Aprovado por _____________________________ Depto. _____________________________",0,0,'L',0);
	$pdf->Cell(40,5,"Data ____/____/____",0,0,'L',0);
	$pdf->Ln(10);
});

$pdf->Output('FICHA_CANDIDATO_'.date('dmYhis').'.pdf', 'D');

?>