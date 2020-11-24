<?php
/*
	Relatório GED / GRD	
	
	Criado por Carlos Abreu / Otávio Pamplona
	
	local/Nome do arquivo:
	../arquivotec/relatorios/rel_ged_grd.php
	
	Versão 0 --> Criação - 19/12/2007
	Versão 1 --> Classe BD - 22/09/2014 - Carlos Abreu
	Versão 2 --> Alterar layout, revisoes - 09/10/2014 - Carlos Abreu
	Versão 3 --> alterar a sequencia de campos Ndvm e Ncli - 30/10/2014 - Carlos Abreu
	Versão 4 --> Grava arquivo GRD na pasta de Projetos - 28/09/2015 - Carlos Abreu
	Versão 5 --> Alterado os digitos de OS/numero pacote - 27/04/2016 - Carlos Abreu
	Versão 6 --> alteração dos caminhos do arquivo - 19/09/2016 - Carlos Abreu
	Versão 7 --> unificação das tabelas numero_cliente e numeros_interno - 10/05/2017 - Carlos Abreu
	Versão 8 --> Inclusão dos campos reg_del nas consultas - 14/11/2017 - Carlos Abreu
		
*/
if(!defined('INCLUDE_DIR'))
{
	require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));
}

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{
	var $titulo;
	var $situacao;
	
	function legendas()
	{		
		$this->SetLineWidth(0.3);

		//Caixa da Finalidade de Emissão
		//Linha acima
		$this->Line(10,$this->GetY(), 200, $this->GetY());
		//Linha esquerda
		$this->Line(10,$this->GetY(),10,$this->GetY()+35);
		//Linha baixo
		$this->Line(10,$this->GetY()+35,200,$this->GetY()+35);
		//Linha direita
		$this->Line(200,$this->GetY(),200,$this->GetY()+35);
		
		$this->SetFont('Arial','B',8);
		//Conteúdo da Finalidade de Emissão		
		$this->Ln(1);
		$this->Cell(190,4,"TIPO DE EMISSÃO (TE)",0,0,'C',0);
		
		$this->Ln(4);
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"PA",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Para Aprovação",0,0,'L',0);
		
		//Espa�amento
		$this->Cell(10,4,"",0,0,'L',0);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"PC",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Para Construção",0,0,'L',0);
		
		//Espa�amento
		$this->Cell(10,4,"",0,0,'L',0);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"CE",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Certificado",0,0,'L',0);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"CO",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Para Comentários",0,0,'L',0);
		
		//Line feed
		$this->Ln(4);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"CA",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Cancelado",0,0,'L',0);
		
		//Espa�amento
		$this->Cell(10,4,"",0,0,'L',0);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"CC",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Conforme Construído \"AS BUILT\"",0,0,'L',0);
		
		//Espa�amento
		$this->Cell(10,4,"",0,0,'L',0);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"PR",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Preliminar",0,0,'L',0);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"CN",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Para Conhecimento",0,0,'L',0);	

		//Line feed
		$this->Ln(4);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"DV",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Devolução Documento",0,0,'L',0);
		
		//Espa�amento
		$this->Cell(10,4,"",0,0,'L',0);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"CS",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Para Consulta",0,0,'L',0);
		
		//Espa�amento
		$this->Cell(10,4,"",0,0,'L',0);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"CV",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Cópia Avançada",0,0,'L',0);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"PO",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Para Orçamento",0,0,'L',0);
		
		//Line feed
		$this->Ln(4);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"LC",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Liberado para Compra",0,0,'L',0);
		
		//Espa�amento
		$this->Cell(10,4,"",0,0,'L',0);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"LE",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Liberado para Execução",0,0,'L',0);
		
		//Espa�amento
		$this->Cell(10,4,"",0,0,'L',0);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"CM",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Conforme Comprado",0,0,'L',0);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"CT",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Para Cotação",0,0,'L',0);		
		
		//Line feed
		$this->Ln(4);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"RC",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Revisado pelo Cliente",0,0,'L',0);
		
		//alterado 03/09/2013
		//Espa�amento
		$this->Cell(10,4,"",0,0,'L',0);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"A",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Aprovado",0,0,'L',0);
		
		
		$this->Cell(10,4,"",0,0,'L',0);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"EI",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Emissão Interna",0,0,'L',0);	
			
		
		//line feed
		$this->Ln(5);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(190,4,"TIPO DE CÓPIAS",0,0,'C',0);
		
		//line feed
		$this->Ln(4);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"E",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Cópia Eletrônica",0,0,'L',0);
		
		//Espa�amento
		$this->Cell(10,4,"",0,0,'L',0);
		
		$this->SetFont('Arial','B',8);
		$this->Cell(5,4,"P",0,0,'L',0);
		
		$this->SetFont('Arial','',8);
		$this->Cell(40,4," = Cópia em Papel",0,0,'L',0);
		
	}
	
	function titulos()
	{
		$this->SetFont('Arial','B',6);
		$this->Cell(6,4,"Item",1,0,'L',0);
		$this->Cell(22,4,"Nº Interno",1,0,'L',0); //30
		$this->HCell(8,4,"Rev. Int.",1,0,'C',0);
		$this->Cell(37,4,"Nº Cliente",1,0,'L',0); //20
		$this->Cell(8,4,"Rev Cli",1,0,'C',0);
		$this->Cell(70,4,"Título",1,0,'C',0); //60
		$this->Cell(6,4,"Fmt",1,0,'C',0);
		$this->Cell(6,4,"Fls",1,0,'C',0);
		$this->Cell(6,4,"TE",1,0,'C',0);
		$this->Cell(12,4,"Nº Cópias",1,0,'C',0);
		$this->Cell(9,4,"Tipo",1,0,'C',0);	
	}	

	//Page header
	function Header()
	{
		$this->Image(DIR_IMAGENS.'logo_pb.png',10,10,40);
	
		if($this->watermark)
		{
			$this->Image($this->watermark,35,135,140);
		}
		
		$this->SetDrawColor(0,0,0);
		$this->SetFont('Arial','B',11);
		$this->Cell(42,5,'',0,0,'C',0);
		$this->HCell(107,5,$this->titulo,0,0,'C',0);
		
		$this->SetFont('Arial','',6);
		$this->Cell(29,5,"EMISSÃO:",0,0,'R',0);		
		$this->Cell(13,5,$this->data_grd,0,1,'R',0);
		$this->Cell(42,5,'',0,0,'C',0);
		$this->SetFont('Arial','B',7);
		$this->HCell(107,5,$this->empresa,0,0,'C',0);
		$this->SetFont('Arial','',6);
		$this->Cell(29,5,"FOLHA:",0,0,'R',0);
		$this->Cell(13,5, $this->PageNo() . " de {nb}",0,1,'R',0);
		
		$this->Cell(42,5,'',0,0,'C',0);
		$this->SetFont('Arial','B',7);
		$this->HCell(107,5,$this->projeto,0,0,'C',0);
		$this->SetFont('Arial','B',9);
		$this->Cell(42,5,"Nº GRD: " . $this->numero_grd,0,1,'R',0);
		
		//legendas
		$this->legendas();
		//legendas
		
		$this->Ln(7);
		
		//titulos
		$this->titulos();
		//titulos
			
		$this->Ln(5);		
	}	
	
	//Page footer
	function Footer()
	{
		$this->SetFont('Arial','',5);
		$this->SetXY(10,290);
		$this->Cell(200,5,"Ver.:3",0,0,'R',0);		
	}
}

class GRD
{
	var $id_grd; //ID
	var $numero_grd; //Número da GRD
	var $ordem; //Ordenação [""=por numeros_interno, "1"=por numcli]
	var $saida_tela = false; //Se a saída será a tela [true] ou o arquivo [false] (padrão)	
	
	//Acrescentado por Carlos Abreu - 29/09/2010
	var $disciplina;
	var $documento;
	var $preview;
	
	var $pdf; //Declara a ref. ao objeto

	//Método de inicialização
	function __construct()
	{
		$this->pdf = new PDF('p','mm'); //Instancia o objeto PDF
	}

	//Método para preenchimento do PDF
	function saida($prefixo = '')
	{		
		$this->pdf->SetAutoPageBreak(true,10);
		$this->pdf->SetMargins(10,10);
		$this->pdf->SetLineWidth(0.3);
		
		$db = new banco_dados;
		
		$this->pdf->SetTitle("GUIA DE REMESSA DE DOCUMENTOS");
		
		$this->pdf->titulo="GUIA DE REMESSA DE DOCUMENTOS";
		
		if($this->id_ged_pacote || $this->preview)
		{
			$this->pdf->watermark = DIR_IMAGENS.'wm_pre_visualizacao.png';
		}
		
		//pega a empresa
		if($this->id_ged_pacote)
		{			
			$sql = "SELECT *, ged_pacotes.data as data_grd, ordem_servico.descricao AS OS_Descricao FROM ".DATABASE.".ged_pacotes, ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico, ".DATABASE.".empresas ";
			$sql .= "WHERE ged_pacotes.reg_del = 0 ";
			$sql .= "AND ged_versoes.reg_del = 0 ";
			$sql .= "AND ged_arquivos.reg_del = 0 ";
			$sql .= "AND numeros_interno.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND empresas.reg_del = 0 ";
			$sql .= "AND ged_pacotes.id_ged_pacote = ged_versoes.id_ged_pacote ";
			$sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
			$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
			$sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
			$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";			
			$sql .= "AND ged_pacotes.id_ged_pacote = '" . $this->id_ged_pacote . "' ";
		}
		else
		{		
			$sql = "SELECT *, grd.data_emissao as data_grd, ordem_servico.descricao AS OS_Descricao FROM ".DATABASE.".grd, ".DATABASE.".ged_pacotes, ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico, ".DATABASE.".empresas ";
			$sql .= "WHERE ged_pacotes.reg_del = 0 ";
			$sql .= "AND ged_versoes.reg_del = 0 ";
			$sql .= "AND ged_arquivos.reg_del = 0 ";
			$sql .= "AND numeros_interno.reg_del = 0 ";
			$sql .= "AND grd.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND empresas.reg_del = 0 ";
			$sql .= "AND grd.id_ged_pacote = ged_pacotes.id_ged_pacote ";
			$sql .= "AND ged_pacotes.id_ged_pacote = ged_versoes.id_ged_pacote ";
			$sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
			$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
			$sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
			$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";			
			$sql .= "AND grd.id_grd = '" . $this->id_grd . "' ";
		}
		
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro != '')
		{
			die("Não foi possível realizar a seleção: ". $db->erro); 
		}
		
		$reg_empresa = $db->array_select[0];
		
		$this->pdf->data_grd = mysql_php($reg_empresa["data_grd"]);
		$this->pdf->numero_grd = sprintf("%05d",$reg_empresa["os"]) . "-". sprintf("%04d",$reg_empresa["numero_pacote"]);
		$this->pdf->empresa = $reg_empresa["abreviacao"];
		$this->pdf->projeto = $reg_empresa["OS_Descricao"];
		
		$this->pdf->AliasNbPages();
		
		$this->pdf->AddPage();
		
		$this->pdf->SetFont('Arial','',6);
		
		//Armazena codigos_emissao em array
		$sql = "SELECT id_codigo_emissao, codigos_emissao FROM ".DATABASE.".codigos_emissao ";
		$sql .= "WHERE reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro != '')
		{
			die("Não foi possível realizar a seleção: ". $db->erro); 
		}
		
		foreach($db->array_select as $reg_cod_emissao)
		{
			$codigos_emissao[$reg_cod_emissao["id_codigo_emissao"]] = $reg_cod_emissao["codigos_emissao"];
		}
		
		//Armazena codigos_copia em array
		$sql = "SELECT id_codigo_copia, codigos_copia FROM ".DATABASE.".codigos_copia ";
		$sql .= "WHERE reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro != '')
		{
			die("Não foi possível realizar a seleção: ". $db->erro); 
		}
		
		foreach($db->array_select as $reg_cod_copia)
		{
			$codigos_copia[$reg_cod_copia["id_codigo_copia"]] = $reg_cod_copia["codigos_copia"];
		}
		
		//Armazena os formatos em array
		$sql = "SELECT id_formato, formato FROM ".DATABASE.".formatos ";
		$sql .= "WHERE reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro != '')
		{
			die("Não foi possível realizar a seleção: ". $db->erro); 
		}
		
		foreach($db->array_select as $cont)
		{
			$comboformato[$cont["id_formato"]] = $cont["formato"];
		}
		
		$sql = "SELECT numerico, alfanumerico FROM ".DATABASE.".codigos_revisao ";
		$sql .= "WHERE reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro != '')
		{
			die("Não foi possível realizar a seleção: ". $db->erro); 
		}

		foreach($db->array_select as $reg_revisao)
		{
			$codigos_revisao[$reg_revisao["numerico"]] = $reg_revisao["alfanumerico"];		
		}
		
		//pega os documentos		
		if($this->id_ged_pacote)
		{
			$sql = "SELECT *, numeros_interno.numero_folhas AS soma_numfolhas, numeros_interno.id_formato FROM ".DATABASE.".ged_pacotes, ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico, ".DATABASE.".setores, ".DATABASE.".atividades ";
			$sql .= "WHERE ged_pacotes.reg_del = 0 ";
			$sql .= "AND ged_versoes.reg_del = 0 ";
			$sql .= "AND ged_arquivos.reg_del = 0 ";
			$sql .= "AND numeros_interno.reg_del = 0 ";
			$sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND setores.reg_del = 0 ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "AND ged_pacotes.id_ged_pacote = ged_versoes.id_ged_pacote ";
			$sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
			$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
			$sql .= "AND numeros_interno.id_atividade = atividades.id_atividade ";
			$sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
			
			//ACRESCENTADO EM 14/09/2009 POR CARLOS ABREU
			$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
			$sql .= "AND solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno ";
			$sql .= "AND ged_pacotes.id_ged_pacote = '" . $this->id_ged_pacote . "' "; 
			$sql .= "GROUP BY numeros_interno.id_os, numeros_interno.sequencia, setores.id_setor ";
		
		}
		else
		{
			
			$sql = "SELECT id_ged_pacote FROM ".DATABASE.".grd, ".DATABASE.".grd_versoes ";
			$sql .= "WHERE grd.reg_del = 0 ";
			$sql .= "AND grd_versoes.reg_del = 0 ";
			$sql .= "AND grd.id_grd = grd_versoes.id_grd ";
			$sql .= "AND grd.id_grd = '" . $this->id_grd . "' "; 
			$sql .= "GROUP BY grd.id_ged_pacote ";
			
			$db->select($sql,'MYSQL',true);
			
			if ($db->erro != '')
			{
				die("Não foi possível realizar a seleção: ". $db->erro); 
			}
			
			$reg_pct = $db->array_select[0];
			
			$sql = "SELECT *, ged_versoes.numero_folhas AS soma_numfolhas, numeros_interno.id_formato FROM ".DATABASE.".ged_pacotes, ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico, ".DATABASE.".setores, ".DATABASE.".atividades ";
			$sql .= "WHERE ged_pacotes.reg_del = 0 ";
			$sql .= "AND ged_versoes.reg_del = 0 ";
			$sql .= "AND ged_arquivos.reg_del = 0 ";
			$sql .= "AND numeros_interno.reg_del = 0 ";
			$sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND setores.reg_del = 0 ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "AND ged_pacotes.id_ged_pacote = ged_versoes.id_ged_pacote ";
			$sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
			$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
			$sql .= "AND numeros_interno.id_atividade = atividades.id_atividade ";
			$sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
			$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
			$sql .= "AND solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno ";
			$sql .= "AND ged_pacotes.id_ged_pacote = '" . $reg_pct["id_ged_pacote"] . "' ";
			
			//ACRESCENTADO POR CARLOS ABREU
			//29/10/2010			
			if($this->disciplina!="" && $this->disciplina!="0")
			{
				$sql .= "AND numeros_interno.id_disciplina = '".$this->disciplina."' ";
			}
			
			if($this->documento!="" && $this->documento!="0")
			{
				$sql .= "AND numeros_interno.id_atividade = '".$this->documento."' ";
			}
						 
			$sql .= "GROUP BY numeros_interno.id_os, numeros_interno.sequencia, setores.id_setor ";
		}
		
		//Seleciona a ordenação
		if($this->ordem=="1") // Por numcli
		{
			$sql .= "ORDER BY numeros_interno.id_os, numeros_interno.numero_cliente ASC ";
		}
		
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro != '')
		{
			die("Não foi possível realizar a seleção: ". $db->erro); 
		}
		
		$maior_tamanho_linha = 0;
		
		$item = 1;
		
		foreach($db->array_select as $reg_grd)
		{
			$cod_cliente = "";	
			
			if($reg_grd["cod_cliente"]!="")
			{
				$cod_cliente = $reg_grd["cod_cliente"];
			}			
			
			$this->pdf->Cell(6,4,$item,0,0,'L',0);
			
			$this->pdf->HCell(22,4, PREFIXO_DOC_GED . sprintf("%05d",$reg_grd["os"]) . "-" . $reg_grd["sigla"] . "-" . $reg_grd["sequencia"],0,0,'L',0); //30

			$this->pdf->Cell(8,4,$reg_grd["revisao_interna"],0,0,'C',0); //30	
			
			$this->pdf->HCell(37,4,$reg_grd["numero_cliente"],0,0,'L',0); //20		
			
			$this->pdf->Cell(8,4,$reg_grd["revisao_cliente"],0,0,'C',0); //30		
		
			$tamanho_linha = 0;

			$doc_y = $this->pdf->GetY();
			
			$doc_x = $this->pdf->GetX();
		
			if($reg_grd["tag"]!=="")
			{
				$this->pdf->HCell(70,5,substr($reg_grd["tag"],0,50),0,1,'C',0);
			}
			else
			{
				$this->pdf->HCell(70,5,substr($reg_grd["complemento"],0,50),0,1,'C',0);	
			}
			if($reg_grd["tag2"]!=="")
			{
				$this->pdf->SetX($doc_x);
				$this->pdf->HCell(70,5,substr($reg_grd["tag2"],0,50),0,1,'C',0);
				//Incrementa o tamanho da linha
				$tamanho_linha+=5;
			}
			if($reg_grd["tag3"]!=="")
			{
				$this->pdf->SetX($doc_x);
				$this->pdf->HCell(70,5,substr($reg_grd["tag3"],0,50),0,1,'C',0);			
				//Incrementa o tamanho da linha
				$tamanho_linha+=5;			
			}
			if($reg_grd["tag4"]!=="")
			{
				$this->pdf->SetX($doc_x);
				$this->pdf->HCell(70,5,substr($reg_grd["tag4"],0,50),0,0,'C',0);		
				//Incrementa o tamanho da linha
				$tamanho_linha+=5;
			}
			
			$tamanho_linha+=5;			
			
			$this->pdf->SetXY($doc_x+70,$doc_y);			
		
			$this->pdf->Cell(6,4,$comboformato[$reg_grd["id_formato"]],0,0,'C',0);		
			$this->pdf->Cell(6,4,$reg_grd["soma_numfolhas"],0,0,'C',0);
			$this->pdf->Cell(6,4,$codigos_emissao[$reg_grd["id_fin_emissao"]],0,0,'C',0);
			$this->pdf->Cell(12,4,$reg_grd["copias"],0,0,'C',0);
			$this->pdf->Cell(9,4,$codigos_copia[$reg_grd["id_codigo_emissao"]],0,0,'C',0);
		
			if($tamanho_linha>$maior_tamanho_linha)
			{
				$this->pdf->Ln($tamanho_linha);
			}
			else
			{
				$this->pdf->Ln($maior_tamanho_linha);			
			}
			
			if($reg_grd["obs"]!='')
			{
				$this->pdf->SetFont('Arial','B',7);
				$this->pdf->Cell(10,5,"Obs.:",0,0,'L',0);
				$this->pdf->HCell(180,5,$reg_grd["obs"],0,1,'L',0);
			}
			
			$this->pdf->SetLineWidth(0.1);

			//LINHA DO OBS.
			$this->pdf->Line(10,$this->pdf->GetY(), 200, $this->pdf->GetY());	

			$this->pdf->SetFont('Arial','',6);		
			
			$this->pdf->Ln(4);
			//Incrementa o item
			$item++;
			
			if($tamanho_linha > $maior_tamanho_linha)
			{
				$maior_tamanho_linha = $tamanho_linha;
			}
					
			if($this->pdf->GetY()>250)
			{
				$this->pdf->addPage();			
			}		
		}
		
		$this->pdf->Ln(5);
				
		if($this->pdf->GetY()>250)
		{
			$this->pdf->addPage();
		
		}

		$this->pdf->SetLineWidth(0.3);
		
		//Caixa das assinaturas
		//Linha acima
		$this->pdf->Line(10,$this->pdf->GetY(), 200, $this->pdf->GetY());
		//Linha esquerda
		$this->pdf->Line(10,$this->pdf->GetY(),10,$this->pdf->GetY()+35);
		//Linha baixo
		$this->pdf->Line(10,$this->pdf->GetY()+35,200,$this->pdf->GetY()+35);
		//Linha direita
		$this->pdf->Line(200,$this->pdf->GetY(),200,$this->pdf->GetY()+35);				
		
		if($this->id_ged_pacote)
		{
			//Pega empresa, nome_contato, Coordenador DVM
			$sql = "SELECT * FROM ".DATABASE.".ged_pacotes, ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno, ";
			$sql .= "".DATABASE.".ordem_servico, ".DATABASE.".contatos, ".DATABASE.".funcionarios, ".DATABASE.".empresas ";
			$sql .= "WHERE ged_pacotes.reg_del = 0 ";
			$sql .= "AND numeros_interno.reg_del = 0 ";
			$sql .= "AND ged_arquivos.reg_del = 0 ";
			$sql .= "AND ged_versoes.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND contatos.reg_del = 0 ";
			$sql .= "AND funcionarios.reg_del = 0 ";
			$sql .= "AND empresas.reg_del = 0 ";
			$sql .= "AND ged_pacotes.id_ged_pacote = ged_versoes.id_ged_pacote ";
			$sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
			$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
			$sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
			$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
			$sql .= "AND ordem_servico.id_cod_resp = contatos.id_contato ";
			$sql .= "AND ordem_servico.id_cod_coord = funcionarios.id_funcionario ";
			$sql .= "AND ged_pacotes.id_ged_pacote = '" . $this->id_ged_pacote . "' ";
		}
		else
		{		
			//Pega empresa, nome_contato, Coordenador
			$sql = "SELECT * FROM ".DATABASE.".grd, ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno, ";
			$sql .= "".DATABASE.".ordem_servico, ".DATABASE.".contatos, ".DATABASE.".funcionarios, ".DATABASE.".empresas ";
			$sql .= "WHERE grd.reg_del = 0 ";
			$sql .= "AND numeros_interno.reg_del = 0 ";
			$sql .= "AND ged_arquivos.reg_del = 0 ";
			$sql .= "AND ged_versoes.reg_del = 0 ";
			$sql .= "AND contatos.reg_del = 0 ";
			$sql .= "AND funcionarios.reg_del = 0 ";
			$sql .= "AND empresas.reg_del = 0 ";
			$sql .= "AND grd.id_ged_pacote = ged_versoes.id_ged_pacote ";
			$sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
			$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
			$sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
			$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
			$sql .= "AND ordem_servico.id_cod_resp = contatos.id_contato ";
			$sql .= "AND ordem_servico.id_cod_coord = funcionarios.id_funcionario ";
			$sql .= "AND grd.id_grd = '" . $this->id_grd . "' ";
		}
		
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro != '')
		{
			die("Não foi possível realizar a seleção: ". $db->erro); 
		}
		
		$reg_info = $db->array_select[0];
		
		$this->pdf->Ln(1);
		
		$this->pdf->SetLineWidth(0.1);
		
		$this->pdf->SetFont('Arial','B',8);
		
		$this->pdf->Cell(95,4,$reg_info["abreviacao"],0,0,'C',0);
		
		$this->pdf->Cell(95,4,NOME_EMPRESA,0,0,'C',0);
		
		$this->pdf->Ln(4);
		
		$this->pdf->SetFont('Arial','',8);
		
		$this->pdf->Cell(95,4,"   " . $reg_info["nome_contato"],0,0,'C',0);
		
		$this->pdf->Cell(95,4,"ENGº " . $reg_info["Funcionario"],0,0,'C',0);
		
		$this->pdf->Ln(15);		
		
		//Linha da assinatura DESTINATÁRIO
		$this->pdf->Line(20,$this->pdf->GetY(),100,$this->pdf->GetY());
		
		//Linha da assinatura EMITENTE
		$this->pdf->Line(110,$this->pdf->GetY(),190,$this->pdf->GetY());
		
		$this->pdf->Ln(1);
		
		$this->pdf->Cell(95,4,"DESTINATÁRIO",0,0,'C',0);
		$this->pdf->Cell(95,4,"EMITENTE",0,0,'C',0);
		
		$this->pdf->SetFont('Arial','',6);
		$this->pdf->Ln(10);
		$this->pdf->Cell(10,4,"",0,0,'C',0);
		$this->pdf->Cell(95,4,"DATA: _____ DE ________________ DE " . date("Y"),0,0,'L',0);
		$this->pdf->Cell(85,4,"1ª VIA - CLIENTE / 2ª VIA - DEVOLVER PROTOCOLADA",0,0,'R',0);
		$this->pdf->SetFont('Arial','B',8);
		
		//Pedido em 06/07/2015
		//Chamado #2354
		if($this->id_ged_pacote || $this->preview)
		{
			$this->pdf->Ln(10);
			$this->pdf->Cell(38,6,"COORDENADOR:",'',0,'L',0);
			$this->pdf->Cell(55,6,"____________________________________________________",'',1,'L',0);
			$this->pdf->Cell(38,6,"SUPERVISOR:",'',0,'L',0);
			$this->pdf->Cell(55,6,"____________________________________________________",'',1,'L',0);
			$this->pdf->Cell(38,6,"SOLICITANTE:",'',0,'L',0);
			$this->pdf->Cell(55,6,"____________________________________________________",'',1,'L',0);
			$this->pdf->Cell(38,6,"EXECUTANTE:",'',0,'L',0);
			$this->pdf->Cell(55,6,"____________________________________________________",'',1,'L',0);
			$this->pdf->Cell(38,6,"VERIFICADOR:",'',0,'L',0);
			$this->pdf->Cell(55,6,"____________________________________________________",'',1,'L',0);
			$this->pdf->Cell(38,6,"GRUPO ARQUIVO:",'',0,'L',0);
			$this->pdf->Cell(55,6,"____________________________________________________",'',1,'L',0);
			$this->pdf->Cell(38,6,"GRUPO PLANEJAMENTO:",'',0,'L',0);
			$this->pdf->Cell(55,6,"____________________________________________________",'',0,'L',0);
		}
		
		//Se a saída for a tela
		if($this->saida_tela)
		{
			//Se for a última GRD do processo
			if($this->finalizado)
			{
				//Mostra o PDF
				//$this->pdf->Output();
				$this->pdf->Output('GRD_'.date('dmYhis').'.pdf', 'D');
			}
		}
		else
		{
			//Cria o PDF em arquivo
			$this->numero_grd = $this->pdf->numero_grd;
			
			$array_rpl = array("'","\"",")","(","\\","/",".",":","&","%");
			
			$abreviacao_cliente = str_replace($array_rpl, " ",maiusculas(tiraacentos($reg_empresa["abreviacao_GED"])));		
		
			$descricao_os = str_replace($array_rpl," ",maiusculas(tiraacentos($reg_empresa["OS_Descricao"])));
			
			//Seleciona informações da OS para GRD
			//28/09/2015 -  Carlos Abreu				
			$diretorio_grd = DOCUMENTOS_GED . $abreviacao_cliente . "/" . $reg_empresa["os"] . "-" .$descricao_os. "/" . substr($reg_empresa["os"],0,4) . GRD;  
			
			if(!is_dir($diretorio_grd))
			{
				$erro = mkdir($diretorio_grd);
				
				if(!$erro)
				{
					die("Erro ao tentar criar a pasta temporária no servidor. ".$erro ."-". $diretorio_grd);
				}
			}	
			
			$this->pdf->Output($diretorio_grd."/".$prefixo.$this->pdf->numero_grd . '.pdf','F');	
		}		
	}
}

//Se forem fornecidos os argumentos
if($_GET["id_grd"] || $_GET["id_ged_pacote"])
{
	$grd = new GRD(); //Instancia o objeto
	$grd->id_grd = $_GET["id_grd"]; 
	$grd->id_ged_pacote = $_GET["id_ged_pacote"];
	$grd->ordem = $_GET["ordem"]; //Ordem: [""=por numeros_interno, "1"=por numcli]
	$grd->preview = $_GET["previsu"];
	$grd->saida_tela=true; //Seta a saída como tela
	$grd->finalizado=true; //Seta o processo como finalizado
	$grd->saida(); //Cria o PDF
}
else
{
	$db = new banco_dados;

	$contagem_grds = 0; //Zera a contagem
	
	//Checa se foi fornecida um id_os
	if($_GET["id_os"])
	{		
		$grd = new GRD(); //Instancia o objeto

		$grd->saida_tela=true; //Seta a saída como tela
		
		$grd->finalizado=false; //Seta o processo como não finalizado
		
		$sql = "SELECT id_grd FROM ".DATABASE.".grd ";
		$sql .= "WHERE grd.reg_del = 0 ";
		$sql .= "AND grd.id_os = '" . $_GET["id_os"] . "' ";
		$sql .= "ORDER BY grd.id_grd ASC ";
				
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro != '')
		{
			die("Não foi possível realizar a seleção: ". $db->erro); 
		}
		
		$num_regs = $db->numero_registros;
	
		//Passa em todas as GRDs da OS selecionada
		foreach($db->array_select as $reg_grds)
		{
			//Incrementa a contagem
			$contagem_grds++;
		
			$grd->id_grd = $reg_grds["id_grd"];

			//Se a GRD atual for a última no processo
			if($num_regs==$contagem_grds)
			{
				$grd->finalizado=true; //Seta o processo como finalizado
			}
			
			//Cria o PDF
			$grd->saida();		
		}
	}
	else
	{
		//Checa se foi fornecida um id_os
		if($_POST["id_os"])
		{
			$grd = new GRD(); //Instancia o objeto
	
			$grd->saida_tela=true; //Seta a saída como tela
			$grd->finalizado=false; //Seta o processo como não finalizado
		
			$grd->disciplina = $_POST["disciplina"];
			$grd->documento = $_POST["id_atividade"];
	
			$grd->preview = $_POST["previsu"];			

			$sql = "SELECT id_grd FROM ".DATABASE.".ged_pacotes, ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno, ".DATABASE.".grd ";
			$sql .= "WHERE ged_pacotes.reg_del = 0 ";
			$sql .= "AND ged_versoes.reg_del = 0 ";
			$sql .= "AND ged_arquivos.reg_del = 0 ";
			$sql .= "AND numeros_interno.reg_del = 0 ";
			$sql .= "AND grd.reg_del = 0 ";
			$sql .= "AND grd.id_os = '" . $_POST["id_os"] . "' ";		
			$sql .= "AND ged_pacotes.id_ged_pacote = grd.id_ged_pacote ";
			$sql .= "AND ged_pacotes.id_ged_pacote = ged_versoes.id_ged_pacote ";
			$sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
			$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
			
			if($_POST["id_atividade"]!="" && $_POST["id_atividade"]!="0")
			{
				$sql .= "AND numeros_interno.id_atividade = '".$_POST["id_atividade"]."' ";
			}
			
			if($_POST["disciplina"]!="" && $_POST["disciplina"]!="")
			{
				$sql .= "AND numeros_interno.id_disciplina = '".$_POST["disciplina"]."' ";
			}
			
			$sql .= "GROUP BY grd.id_grd ";		
			$sql .= "ORDER BY grd.id_grd ASC ";	
			
			$db->select($sql,'MYSQL',true);
		
			if($db->numero_registros>0)
			{	
				//Passa em todas as GRDs da OS selecionada
				foreach($db->array_select as $reg_grds)
				{
					//Incrementa a contagem
					$contagem_grds++;
				
					$grd->id_grd = $reg_grds["id_grd"];
		
					//Se a GRD atual for a última no processo
					if($db->numero_registros==$contagem_grds)
					{
						$grd->finalizado=true; //Seta o processo como finalizado
					}
					
					//Cria o PDF
					$grd->saida();			
				}		
			}
			else
			{
				echo "<font color=\"#FF0000\">NÃO EXISTE GRD/DOCUMENTOS COM ESTAS OPÇÕES DE FILTRO.</FONT><br>".$_POST["id_os"]." - ".$_POST["id_atividade"]." - ".$_POST["disciplina"];
			}
		}		
	}
}

?>