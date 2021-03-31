<?php
/*
		Relatório acompanhamento OS	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../relatorios/rel_acompanhamento_os.php
		
		Versão 0 --> VERSÃO INICIAL - 14/01/2005
		Versão 1 --> ATUALIZAÇÃO LAY OUT - 28/03/2006
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 14/11/2017 - Carlos Abreu
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

$pdf=new PDF('l','mm',A4);

$db = new banco_dados;

$pdf->SetAutoPageBreak(true,5);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.5);

$id_os = $_POST["id_os"];

$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".funcionarios, ".DATABASE.".ordem_servico ";
$sql .= "LEFT JOIN ".DATABASE.".contatos ON (ordem_servico.id_cod_resp = contatos.id_contato AND contatos.reg_del = 0) ";
$sql .= "WHERE ordem_servico.id_os = '" . $id_os . "' ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";
$sql .= "AND ordem_servico.id_cod_coord = funcionarios.id_funcionario ";

$db->select($sql,'MYSQL', true);

$reg_os = $db->array_select[0];

$sql = "SELECT * FROM ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.id_funcionario = '" . $reg_os["id_funcionario"] . "' ";
$sql .= "AND funcionarios.id_usuario = usuarios.id_usuario ";
$sql .= "AND usuarios.reg_del = 0 ";

$db->select($sql,'MYSQL', true);

$reg_func = $db->array_select[0];

//obtem as datas previstas e reais
/*
$sql = "SELECT * FROM AF8010 ";
$sql .= "WHERE D_E_L_E_T_ = '' ";
$sql .= "AND AF8_PROJET = '".sprintf("%010d",$reg_os["os"])."' ";

$db->select($sql ,'MSSQL', true);

$regs0 = $db->array_select[0];
*/

//Seta o cabeçalho
$pdf->departamento="SISTEMA ERP";
$pdf->titulo="ACOMPANHAMENTO DE PROJETOS DE ENGENHARIA - OS nº " . $reg_os["os"];
$pdf->codigodoc="000"; 
$pdf->codigo=01;
$pdf->emissao=date("d/m/Y");

$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial','',7);

$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".os_x_adicionais ";
$sql .= "WHERE os_x_adicionais.id_os_adicional = '".$id_os."' ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND os_x_adicionais.reg_del = 0 ";
$sql .= "AND ordem_servico.id_os = os_x_adicionais.id_os_raiz ";

$db->select($sql,'MYSQL', true);

$reg_ad = $db->array_select[0];

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

$sql = "SELECT setor, funcionario, categoria  FROM ".DATABASE.".os_x_funcionarios, ".DATABASE.".funcionarios, ".DATABASE.".rh_funcoes, ".DATABASE.".setores ";
$sql .= "WHERE os_x_funcionarios.id_funcionario = funcionarios.id_funcionario ";
$sql .= "AND os_x_funcionarios.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND rh_funcoes.reg_del = 0 ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND funcionarios.id_funcao = rh_funcoes.id_funcao ";
$sql .= "AND os_x_funcionarios.id_os = '" . $id_os . "' ";
$sql .= "AND funcionarios.id_setor = setores.id_setor ";
$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
$sql .= "ORDER BY setor, funcionario ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $reg_func)
{
	$pdf->Cell(50,4,$reg_func["setor"],0,0,'C',0);
	$pdf->Cell(150,4,$reg_func["funcionario"] . " - " . $reg_func["categoria"],0,1,'C',0);	
}

$sql = "SELECT * FROM ".DATABASE.".os_x_entradas_saidas ";
$sql .= "WHERE id_os = '" . $reg_os["id_os"] . "' ";
$sql .= "AND reg_del = 0 ";

$db->select($sql,'MYSQL', true);

$regs = $db->array_select[0];

$sql = "SELECT nome_validador, data_validacao FROM ".DATABASE.".os_x_validacao ";
$sql .= "WHERE id_os = '" . $reg_os["id_os"] . "' ";
$sql .= "AND reg_del = 0 ";

$db->select($sql,'MYSQL', true);

$reg_validacao = $db->array_select[0];

$sql = "SELECT * FROM ".DATABASE.".os_x_analise_critica_final ";
$sql .= "WHERE id_os = '" . $reg_os["id_os"] . "' ";
$sql .= "AND reg_del = 0 ";

$db->select($sql,'MYSQL', true);

$reg_acf = $db->array_select[0];

if($regs["ata_reuniao"])
{
	$chk_ata_reuniao = "X";
}
else
{
	$chk_ata_reuniao_na = "X";
}

if($regs["chk_list"])
{
	$chk_list = "X";
}
else
{
	$chk_list_na = "X";
}

if($regs["req_func"])
{
	$chk_req_func = "X";
}
else
{
	$chk_req_func_na = "X";
}

if($regs["req_estat"])
{
	$chk_req_estat = "X";
}
else
{
	$chk_req_estat_na = "X";
}

if($regs["inf_proj"])
{
	$chk_inf_proj = "X";
}
else
{
	$chk_inf_proj_na = "X";
}

if($regs["escop_forn"])
{
	$chk_escop_forn = "X";
}
else
{
	$chk_escop_forn_na = "X";
}

if($regs["referencias"])
{
	$chk_referencias = "X";
}
else
{
	$chk_referencias_na = "X";
}

if($regs["exclusoes"])
{
	$chk_exclusoes = "X";
}
else
{
	$chk_exclusoes_na = "X";
}


if($regs["solic_num"])
{

	$chk_solic_num = "X";
}
else
{

	$chk_solic_num_na = "X";
}

$pdf->Cell(125,4,"Entradas",1,0,'C',0);
$pdf->Cell(125,4,"Saídas",1,1,'C',0);

$pdf->Ln(3);

$pdf->Cell(125,4,"1 - Reunião inicial com o cliente (kick off meeting).",0,0,'L',0);
$pdf->Cell(50,4,"Ata de reunião e/ou anotações. ",0,0,'L',0);
$pdf->Cell(5,4,$chk_ata_reuniao,1,0,'C',0);
$pdf->Cell(10,4,"Sim",0,0,'L',0);
$pdf->Cell(5,4,$chk_ata_reuniao_na,1,0,'C',0);
$pdf->Cell(10,4,"N/A",0,0,'L',0);
$pdf->Cell(20,4,"data: " . mysql_php($regs["data_ata"]),0,1,'L',0);

$pdf->Cell(125,4,"2 - Informações para execução do projeto.",0,1,'L',0);

$pdf->Cell(125,4,"* Levantamento no campo (planta e/ou arquivo técnico do cliente)",0,0,'L',0);
$pdf->Cell(50,4,"Check list preenchido e dados coletados. ",0,0,'L',0);
$pdf->Cell(5,4,$chk_list,1,0,'C',0);
$pdf->Cell(10,4,"Sim",0,0,'L',0);
$pdf->Cell(5,4,$chk_list_na,1,0,'C',0);
$pdf->Cell(10,4,"N/A",0,0,'L',0);
$pdf->MultiCell(50,4,"Obs: " . $regs["obs_chk_list"],0,'L',0);

$pdf->Cell(125,4,"* Requisitos de funcionamento e de desempenho.",0,0,'L',0);
$pdf->Cell(50,4,"Requisitos levantados.",0,0,'L',0);
$pdf->Cell(5,4,$chk_req_func,1,0,'C',0);
$pdf->Cell(10,4,"Sim",0,0,'L',0);
$pdf->Cell(5,4,$chk_req_func_na,1,0,'C',0);
$pdf->Cell(10,4,"N/A",0,0,'L',0);
$pdf->MultiCell(50,4,"Obs: " . $regs["obs_req_func"],0,'L',0);

$pdf->Cell(125,4,"* Requisitos estatutários e regulamentares aplicáveis.",0,0,'L',0);
$pdf->Cell(50,4,"Requisitos levantados.",0,0,'L',0);
$pdf->Cell(5,4,$chk_req_estat,1,0,'C',0);
$pdf->Cell(10,4,"Sim",0,0,'L',0);
$pdf->Cell(5,4,$chk_req_estat_na,1,0,'C',0);
$pdf->Cell(10,4,"N/A",0,0,'L',0);
$pdf->MultiCell(50,4,"Obs: " . $regs["obs_req_estat"],0,'L',0);

$pdf->Cell(125,4,"* Informações originadas de projetos anteriores semelhantes, se aplicáveis.",0,0,'L',0);
$pdf->Cell(50,4,"Requisitos levantados.",0,0,'L',0);
$pdf->Cell(5,4,$chk_inf_proj,1,0,'C',0);
$pdf->Cell(10,4,"Sim",0,0,'L',0);
$pdf->Cell(5,4,$chk_inf_proj_na,1,0,'C',0);
$pdf->Cell(10,4,"N/A",0,0,'L',0);
$pdf->MultiCell(50,4,"Obs: " . $regs["obs_req_inf_proj"],0,1,'L',0);

$pdf->Cell(125,4,"* Escopo de fornecimento",0,0,'L',0);
$pdf->Cell(50,4,"Escopo definido.",0,0,'L',0);
$pdf->Cell(5,4,$chk_escop_forn,1,0,'C',0);
$pdf->Cell(10,4,"Sim",0,0,'L',0);
$pdf->Cell(5,4,$chk_escop_forn_na,1,0,'C',0);
$pdf->Cell(10,4,"N/A",0,0,'L',0);
$pdf->MultiCell(50,4,"Obs: " . $regs["obs_escop_forn"],0,'L',0);

$pdf->Cell(125,4,"* Referências",0,0,'L',0);
$pdf->Cell(50,4,"Referências levantadas.",0,0,'L',0);
$pdf->Cell(5,4,$chk_referencias,1,0,'C',0);
$pdf->Cell(10,4,"Sim",0,0,'L',0);
$pdf->Cell(5,4,$chk_referencias_na,1,0,'C',0);
$pdf->Cell(10,4,"N/A",0,0,'L',0);
$pdf->MultiCell(50,4,"Obs: " . $regs["obs_referencias"],0,'L',0);

$pdf->Cell(125,4,"* Exclusões",0,0,'L',0);
$pdf->Cell(50,4,"Exclusões definidas.",0,0,'L',0);
$pdf->Cell(5,4,$chk_exclusoes,1,0,'C',0);
$pdf->Cell(10,4,"Sim",0,0,'L',0);
$pdf->Cell(5,4,$chk_exclusoes_na,1,0,'C',0);
$pdf->Cell(10,4,"N/A",0,0,'L',0);
$pdf->MultiCell(50,4,"Obs: " . $regs["obs_exclusoes"],0,'L',0);

$pdf->Cell(125,4,"3 - Solicitação de números para os documentos novos e/ou solicitação de documentos existentes do projeto.",0,0,'L',0);
$pdf->Cell(50,4,"Solicitações realizadas.",0,0,'L',0);
$pdf->Cell(5,4,$chk_solic_num,1,0,'C',0);
$pdf->Cell(10,4,"Sim",0,0,'L',0);
$pdf->Cell(5,4,$chk_solic_num_na,1,0,'C',0);
$pdf->Cell(10,4,"N/A",0,0,'L',0);
$pdf->MultiCell(50,4,"Obs: " . $regs["obs_solic_num"],0,'L',0);

$pdf->Ln(3);

$sql = "SELECT * FROM ".DATABASE.".os_x_analise_critica_inicial ";
$sql .= "WHERE id_os = '" . $reg_os["id_os"] . "' ";
$sql .= "AND reg_del = 0 ";

$db->select($sql,'MYSQL', true);

$regs = $db->array_select[0];

$pdf->Cell(250,4,"Análise crítica inicial (para a análise crítica periódica utilize o Procedimento de Execução de Projetos).",1,1,'C',0);
$pdf->Cell(125,4,"1 - Existem recursos para a execução do projeto?",0,0,'L',0);
$pdf->Cell(200,4,maiusculas($regs["recursos_execucao"]),0,1,'L',0);


$sql = "SELECT * FROM ".DATABASE.".os_x_analise_critica_periodica ";
$sql .= "WHERE id_os = '" . $reg_os["id_os"] . "' ";
$sql .= "AND reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if($db->numero_registros>0)
{
	$str_perio = "SIM";
	
	$str_lista = "Imprimir lista de pendências";
}
else
{
	$str_perio = "NÃO";
}	

$pdf->Cell(125,4,"2 - Existem problemas para a realização do projeto? Descreva as ações necessárias.",0,0,'L',0);
$pdf->Cell(200,4,$str_perio,0,1,'L',0);
$pdf->Cell(200,4,$str_lista,0,1,'L',0);

$pdf->SetFont('Arial','',7);
$pdf->Ln(5);

//Verifica se estamos no final da página
if($pdf->getY()>190)
{
	$pdf->addPage();
}

$pdf->Cell(125,4,"Verificação do projeto",1,0,'C',0);
$pdf->Cell(125,4,"Controle de alterações e revisões",1,1,'C',0);

$x_multicell_1 = $pdf->GetX();
$y_multicell_1 = $pdf->GetY();
$pdf->MultiCell(125,4,"Conforme a Análise e Verificação de Projeto, executada em todos os documentos que são finalizados para entrega ao cliente ao longo do projeto, de acordo com  cronograma consolidado", 0,'L',0);
$pdf->SetXY($x_multicell_1+125,$y_multicell_1);
$pdf->MultiCell(125,4,"As alterações são registradas no formulário de Solicitação de Alterações e/ou Serviços. As revisões são anotadas no próprio documento do projeto e registradas pelo Arquivo Técnico.",0,'L',0);

//Verifica se estamos no final da página
if($pdf->getY()>190)
{
	$pdf->addPage();
}

$pdf->Cell(125,4,"Validação do projeto",1,0,'C',0);
$pdf->Cell(125,4,"Validação pelo responsável técnico",1,1,'C',0);

$x_multicell_2 = $pdf->GetX();
$y_multicell_2 = $pdf->GetY();
$pdf->MultiCell(125,4,"A validação do projeto é evidenciada por: 1) Análise e verificação dos documentos do projeto emitidos para o cliente e 2) Análise final pelo responsável técnico",0,'L',0);
$pdf->SetXY($x_multicell_2+125,$y_multicell_2);
$pdf->Cell(100,4,"Coordenador: " . $reg_validacao["nome_validador"],0,0,'L',0);
$pdf->Cell(25,4,"Data: " . mysql_php($reg_validacao["data_validacao"]),0,1,'L',0);

if($pdf->GetY()>=145)
{
	$pdf->addPage();
}

$pdf->Cell(250,4,"Análise crítica final (Utilize o Procedimento de Execução de Projetos).",1,1,'C',0);

$x_multicell_3 = $pdf->GetX();
$y_multicell_3 = $pdf->GetY();
$pdf->MultiCell(125,4,"1 - Os prazos foram cumpridos conforme previsto no cronograma? Se ocorreram atrasos, quais foram as principais causas? Comentários / Justificativas:",0,'L',0);
$pdf->SetXY($x_multicell_3+125,$y_multicell_3);

$x_multicell = $pdf->GetX();
$y_multicell = $pdf->GetY();
$pdf->MultiCell(125,4,$reg_acf["txt_prazos"],0,1,'L',0);

$pdf->Ln(5);

$x_multicell_4 = $pdf->GetX();
$y_multicell_4 = $pdf->GetY();
$pdf->MultiCell(125,4,"2 - As não-conformidades, se houve, foram resolvidas eficazmente? Comentários:",0,'L',0);
$pdf->SetXY($x_multicell_4+125,$y_multicell_4);

$x_multicell = $pdf->GetX();
$y_multicell = $pdf->GetY();
$pdf->MultiCell(125,4,$reg_acf["txt_naoconforme"],0,1,'L',0);

$pdf->Ln(5);

$x_multicell_5 = $pdf->GetX();
$y_multicell_5 = $pdf->GetY();
$pdf->MultiCell(125,4,"3 - A equipe estava corretamente dimensionada? Os profissionais demonstraram competência técnica? Comentários:",0,'L',0);
$pdf->SetXY($x_multicell_5+125,$y_multicell_5);

$x_multicell = $pdf->GetX();
$y_multicell = $pdf->GetY();
$pdf->MultiCell(125,4,$reg_acf["txt_equipe"],0,1,'L',0);

$pdf->Ln(5);

$x_multicell_6 = $pdf->GetX();
$y_multicell_6 = $pdf->GetY();
$pdf->MultiCell(125,4,"4 - A qualidade do projeto foi adequadamente verificada?  Houve Realimentação do cliente (sugestões, elogios, reclamações etc.)? Descreva:",0,'L',0);
$pdf->SetXY($x_multicell_6+125,$y_multicell_6);

$x_multicell = $pdf->GetX();
$y_multicell = $pdf->GetY();
$pdf->MultiCell(125,4,$reg_acf["txt_qualidade"],0,1,'L',0);

$pdf->Ln(5);

$x_multicell_7 = $pdf->GetX();
$y_multicell_7 = $pdf->GetY();
$pdf->MultiCell(125,4,"5 - Constatou-se necessidade de melhorias para os novos projetos? Descreva: ",0,'L',0);
$pdf->SetXY($x_multicell_7+125,$y_multicell_7);

$x_multicell = $pdf->GetX();
$y_multicell = $pdf->GetY();
$pdf->MultiCell(125,4,$reg_acf["txt_melhorias"],0,1,'L',0); //85

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