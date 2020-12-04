<?php
/*
		Relatório Apontamentos por periodo
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_diastrabalhados.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/

ini_set('memory_limit', '512M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{
	var $TituloOS = "";
	
	var $ano_curr = "";
	
	//Page header
	function Header()
	{
		$this->Image(DIR_IMAGENS.'logo_pb.png',6,16,40);
		$this->Ln(15);
		$this->SetFont('Arial','',6);
		$this->Cell(258,4,'',0,0,'L',0);
		$this->Cell(15,4,'DOC:',0,0,'L',0);
		$this->Cell(12,4,$this->setor() . '-' . $this->codigodoc() . '-' .$this->codigo(),0,1,'R',0);
		$this->SetLineWidth(0.3);
		$this->Line(263,23,289,23);
		$this->Cell(258,4,'',0,0,0);
		$this->Cell(15,4,'EMISSÃO:',0,0,'L',0); //aqui
		$this->Cell(15,4,$this->Emissao(),0,1,'L',0); //aqui
		$this->Line(263,27,289,27);
		$this->Cell(258,4,'',0,0,'L',0);
		$this->Cell(15,4,'FOLHA:',0,0,'L',0);
		$this->Cell(15,4,$this->PageNo().' de {nb}',0,0,'R',0);
		$this->Line(263,31,289,31);
		$this->Ln(7);
		$this->SetFont('Arial','B',12);
		$this->Cell(50,4,$this->TituloOS,0,0,'L',0);
		$this->Cell(235,4,$this->Titulo(),0,1,'R',0);
		$this->SetFont('Arial','B',8);
		$this->Cell(258,4,'',0,0,'L',0);
		$this->SetFont('Arial','',9);

		$this->SetLineWidth(1);
		$this->SetDrawColor(0,0,0);
		$this->Line(5,40,290,40);
		$this->SetLineWidth(0.3);
		$this->SetXY(5,45);

		$this->Cell(40,15,"NOME DO FUNCIONÁRIO",1,0,'C',0);

		$this->HCell(210,5,"SEMANA / DATA",1,0,'C',0);
		$y = $this->GetY();
		$this->HCell(10,15,"HH N.",1,0,'C',0);
		$this->HCell(10,15,"HH A.",1,0,'C',0);
		$this->HCell(15,15,"QTD",1,0,'C',0);
		$this->SetY($y+5);
		
		$this->Cell(40,5,"",0,0,'C',0);
	
		$mes = $_POST["mes"];
		
		$ano = $_POST["ano"];
		
		if ($mes==1)
		{
			$mes = 12;
			$ano = $ano-1;
			$data_ini = "26/" . $mes . "/" . $ano;
		}
		else
		{ 
			$mesant = $mes - 1;
			$data_ini = "26/" . $mesant . "/" . $ano;
		}
		
		// 3 será o mes corrente (fevereiro)
		//$m = 2;
		$temp = explode("/",$data_ini);
		
		$d = $temp[0]; //26
		$m = $temp[1]; //02 //março
		$a = $temp[2]; //2006
		
		$d1 = $temp[0]; //26
		$m1 = $temp[1]; //02 //março
		$a1 = $temp[2]; //2006
		
		$mm = $m;
		
		$diasestampa = mktime(0,0,0,$mm+1,0,$ano);
		$diasarray = getdate($diasestampa);
		$diasdomes = $diasarray["mday"];
		
		// Numero de dias na semana
		$numdias = 1;
		
		// loop de semanas
		for($i=1;$i<=$diasdomes;$i++)
		{			
			if($d1==$diasdomes+1)
			{
				$d1 = 1;
				$m1++;
				
				if($m1==13)
				{
					$m1=1;
					$a1++;
				}
			}
			
			$dd1 = $d1;	
	
			$semana = date('W',mktime(0,0,0,$m1,$dd1+1,$a1));
			
			$semanapos = date('W',mktime(0,0,0,$m1,$dd1+2,$a1));		

			$this->SetFont('Arial','',8);
			
			if($semana==$semanapos)
			{
				$numdias++;
			}
			else
			{
				$this->HCell((210/$diasdomes)*($numdias),5,$semana,1,0,'C',0);
				$numdias = 1;
			}
			
			$d1++;
		}
		
		if($semana==$semanapos)
		{
			$this->HCell((210/($diasdomes))*($numdias-1),5,$semana,1,0,'C',0);
		}
		
		$this->Ln(5);
	
		$this->Cell(40,5,"",1,0,'C',0);
		
		// loop de dias
		for($i=1;$i<=$diasdomes;$i++)
		{			
			$diasestampa1 = mktime(0,0,0,$m,$d,$a);
			$diasarray1 = getdate($diasestampa1);
			$fimsemana = $diasarray1["wday"];
			
			if($d==$diasdomes+1)
			{
				$d = 1;
				
				$m++;
				
				if($m==13)
				{
					$m=1;
					$a++;
				}
			}
	
			$this->SetFont('Arial','',8);
			
			if($fimsemana==0 || $fimsemana==6)
			{
				$this->SetFillColor(255,153,204);
				$this->HCell(210/$diasdomes,5,$d."/".$m,1,0,'C',1);
			}
			else
			{
				
				$this->HCell(210/$diasdomes,5,$d."/".$m,1,0,'C',0);
			}
			
			$d++;
		}
		// loop de dias
		
		$this->SetXY(5,60);	
	}	
	
	//Page footer
	function Footer()
	{
		
	}
}

$db = new banco_dados;

$arrTitulo = array('JANEIRO', 'FEVEREIRO', 'MARÇO', 'ABRIL', 'MAIO', 'JUNHO', 'JULHO', 'AGOSTO', 'SETEMBRO', 'OUTUBRO', 'NOVEMBRO', 'DEZEMBRO');

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE setores.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $regs)
{
	if($_POST["chk_".$regs["id_setor"]]==1)
	{
		$setor[] = sprintf("%010d",$regs["id_setor"]);
		$setord[] = $regs["id_setor"];
	}
}

if(count($setor)==0)
{
	die("Deve ser escolhida pelo menos uma disciplina.");
}

$filtro_setor = implode(",",$setor);
$filtro_setord = implode(",",$setord);

$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.reg_del = 0 ";
$sql .= "GROUP BY situacao ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $regs)
{
	if($_POST["chks_".str_replace(" ","",$regs["situacao"])]==1)
	{
		$status[] = "'".$regs["situacao"]."'";
	}
}

$filtro_status = implode(",",$status);

$pdf=new PDF('L','mm',a4);
$pdf->SetAutoPageBreak(true,10);
$pdf->SetMargins(5,5);
$pdf->SetLineWidth(0.25);
$pdf->SetFont('Arial','',10);

$pdf->ano_curr = $_POST["ano"];

$ano_curr = $_POST["ano"];

$pdf->titulo = $arrTitulo[$_POST['mes']-1]."/".$ano_curr;

if($_POST["atuacao"]!="")
{
	$filtro_atuacao = $_POST["atuacao"];
}

$os_rev = explode("#",$_POST["id_os"]); //0 - id_os / 1 - versao_documento

if($os_rev[0])
{
	$sql = "SELECT * FROM ".DATABASE.".ordem_servico ";
	$sql .= "WHERE ordem_servico.id_os = '" . $os_rev[0] . "' ";
	$sql .= "AND ordem_servico.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$reg_os = $db->array_select[0];
	
	$pdf->TituloOS = "OS " . $reg_os["os"];
}


$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".salarios ";
$sql .= "WHERE funcionarios.id_funcionario = salarios.id_funcionario ";
$sql .= "AND salarios.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";

if(count($status)>0)
{
	$sql .= "AND funcionarios.situacao IN (".$filtro_status.") ";
}

$sql .= "GROUP BY  tipo_contrato ";
$sql .= "ORDER BY  tipo_contrato ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $regs)
{
	if($_POST["chk1_".$regs[" tipo_contrato"]]==1 || $_POST["chk1_TODOS"]=="-1")
	{
		$array_contrato[] = $regs[" tipo_contrato"];
	}
}

if(count($array_contrato)==0)
{
	die("Deve ser escolhido pelo menos um tipo de contrato.");
}

$filtro_contrato = implode(",",$array_contrato);

$pdf->setor="PLN";
$pdf->codigodoc="301"; //"00";
$pdf->codigo="01"; //Numero OS
$pdf->setorextenso=$setor; //"INFORMATICA"
$pdf->emissao=date('d/m/Y');
		
$pdf->AliasNbPages();
$pdf->AddPage();

$mes = $_POST["mes"];

$ano = $_POST["ano"];

if ($mes==1)
{
	$mes=12;
	$ano=$ano-1;
	$data_ini = "26/" . $mes . "/" . $ano;
}
else
{ 
	$mesant = $mes - 1;
	$data_ini = "26/" . $mesant . "/" . $ano;
}

$temp = explode("/",$data_ini);

$d = $temp[0]; //26
$m = $temp[1]; //02 //março
$a = $temp[2]; //2006

$diasestampa = mktime(0,0,0,$m+1,0,$ano);

$diasarray = getdate($diasestampa);

$diasdomes = $diasarray["mday"];

// loop de dias
for($i=1;$i<=$diasdomes;$i++)
{	
	if($d==$diasdomes+1)
	{
		$d = 1;
		
		$m++;
		
		if($m==13)
		{
			$m=1;
			$a++;
		}
	}

	$data[$i]=$a."-". sprintf('%02d',$m) ."-".sprintf('%02d',$d);
	
	$d++;
}
// loop de dias

$datai = $data[1];

$dataf = $data[count($data)];

//PROTHEUS
//Monta as datas/funcionarios
/*
$sql = "SELECT * FROM AFU010 WITH(NOLOCK), AE8010 WITH(NOLOCK), AF8010 WITH(NOLOCK) ";
$sql .= "WHERE AFU_DATA BETWEEN '" . mysql_protheus($datai) . "' AND '" . mysql_protheus($dataf) . "' ";
$sql .= "AND AFU010.D_E_L_E_T_ = '' ";
$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8_PROJET = AFU_PROJET ";
$sql .= "AND AF8_REVISA = AFU_REVISA ";
$sql .= "AND AE8010.AE8_FILIAL = AFU010.AFU_FILIAL ";
$sql .= "AND AE8_RECURS NOT LIKE 'ORC_%' ";
$sql .= "AND AE8_RECURS = AFU_RECURS ";

if(count($setor)>0)
{
	$sql .= "AND AE8_EQUIP IN (".$filtro_setor.") ";
}

if($os_rev[0]) //Filtro por OS - Sol. Fernando 04/03/2010
{
	$sql .= "AND AFU_PROJET = '" . sprintf("%010d",$reg_os["os"]) . "' ";
	$sql .= "AND AFU_REVISA = '" . $os_rev[1] . "' ";
}

$sql .= "ORDER BY AFU_DATA, AFU_RECURS ";

$db->select($sql,'MSSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$array_func = $db->array_select;

foreach ($array_func as $regs1)
{
	$codfuncionario_p = substr($regs1["AFU_RECURS"],-5);
	
	$sql = "SELECT  tipo_contrato FROM ".DATABASE.".salarios ";
	$sql .= "WHERE salarios.id_funcionario = '".(int)$codfuncionario_p."' ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$reg_sal = $db->array_select[0];
	
	$contrato_p[intval($codfuncionario_p)] = $reg_sal[" tipo_contrato"];
	
	$reg_data_p[intval($codfuncionario_p)] .= protheus_mysql($regs1["AFU_DATA"]).'/';
	
	$reg_hor_p[protheus_mysql($regs1["AFU_DATA"]).'#'.intval($codfuncionario_p)] += $regs1["AFU_HQUANT"]*3600;
}

//Obtem horas da data e os funcionarios
$sql = "SELECT AFU_RECURS, AE8_DESCRI, SUM(AFU_HQUANT) AS HN FROM AFU010 WITH(NOLOCK), AE8010 WITH(NOLOCK), AF8010 WITH(NOLOCK) ";
//$sql .= "WHERE AE8_ATIVO = '1' ";
$sql .= "WHERE AE8010.AE8_FILIAL = AFU010.AFU_FILIAL ";
$sql .= "AND AF8010.AF8_FILIAL = AFU010.AFU_FILIAL ";
$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
$sql .= "AND AFU010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AFU010.AFU_CTRRVS = '1' ";
$sql .= "AND AF8_PROJET = AFU_PROJET ";
$sql .= "AND AF8_REVISA = AFU_REVISA ";
$sql .= "AND AE8_RECURS NOT LIKE 'ORC_%' ";
$sql .= "AND AE8_RECURS = AFU_RECURS ";

if(count($setor)>0)
{
	$sql .= "AND AE8_EQUIP IN (".$filtro_setor.") ";
}

$sql .= "AND AFU_DATA BETWEEN '" . mysql_protheus($datai) . "' AND '" . mysql_protheus($dataf) . "' ";

if($os_rev[0]) //Filtro por OS - Sol. Fernando 04/03/2010
{
	$sql .= "AND AFU_PROJET = '" . sprintf("%010d",$reg_os["os"]) . "' ";
	$sql .= "AND AFU_REVISA = '" . $os_rev[1] . "' ";
}

$sql .= "GROUP BY AFU_RECURS, AE8_DESCRI ";
$sql .= "ORDER BY AE8_DESCRI ";

$db->select($sql,'MSSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$i = 1;

foreach ($db->array_select as $regs)
{	
	$segundos = $regs["HN"]*3600;
	
	$codfuncionario_p = substr($regs["AFU_RECURS"],-5);

	$funcid_p[intval($codfuncionario_p)] = intval($codfuncionario_p);
	$funchn_p[intval($codfuncionario_p)] = sec_to_time($segundos,false);
	$funcha_p[intval($codfuncionario_p)] = 0;
	$funcht_p[intval($codfuncionario_p)] = sec_to_time($segundos,false);	
	
	$i++;
}
*/

//Monta as datas/funcionarios
$sql = "SELECT *, TIME_TO_SEC(hora_normal) AS HN, TIME_TO_SEC(hora_adicional) AS HA, TIME_TO_SEC(hora_adicional_noturna) AS HAN ";
$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".funcionarios ";
$sql .= "WHERE apontamento_horas.id_funcionario = funcionarios.id_funcionario ";
$sql .= "AND apontamento_horas.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND apontamento_horas.data BETWEEN '" . $datai . "' AND '" . $dataf . "' ";

if($os_rev[0]) //Filtro por OS
{
	$sql .= "AND apontamento_horas.id_os = '" . $os_rev[0] . "' ";
}

$sql .= "AND funcionarios.id_setor IN (" . $filtro_setord . ") ";

$sql .= "ORDER BY apontamento_horas.data, funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$array_func = $db->array_select;

foreach ($array_func as $regs1)
{
	$sql = "SELECT  tipo_contrato FROM ".DATABASE.".salarios ";
	$sql .= "WHERE salarios.id_funcionario = '".$regs1["id_funcionario"]."' ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$reg_sal = $db->array_select[0];
	
	$contrato[$regs1["id_funcionario"]] = $reg_sal[" tipo_contrato"];
	
	$reg_data[$regs1["id_funcionario"]] .= $regs1["data"].'/';
	
	$reg_hor[$regs1["data"].'#'.$regs1["id_funcionario"]] += ($regs1["HN"]+$regs1["HA"]+$regs1["HAN"]);
}

//Obtem horas da data e os funcionarios
$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal)) AS HN, SUM(TIME_TO_SEC(hora_adicional)) AS HA, SUM(TIME_TO_SEC(hora_adicional_noturna)) AS HAN FROM ".DATABASE.".funcionarios, ".DATABASE.".local, ".DATABASE.".apontamento_horas ";
$sql .= "WHERE apontamento_horas.id_funcionario = funcionarios.id_funcionario ";
$sql .= "AND apontamento_horas.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND local.reg_del = 0 ";
$sql .= "AND funcionarios.situacao IN (".$filtro_status.") ";
$sql .= "AND apontamento_horas.data BETWEEN '" . $datai . "' AND '" . $dataf . "' ";
$sql .= "AND funcionarios.id_local = local.id_local ";

if($_POST["id_os"]) //Filtro por OS
{
	$sql .= "AND apontamento_horas.id_os = '" . $os_rev[0] . "' ";
}

if($_POST["local_trabalho"])
{
	$sql .= "AND funcionarios.id_local = '".$_POST["local_trabalho"]."' ";
}

if($_POST["atuacao"]!="")
{
	$sql .= "AND funcionarios.nivel_atuacao = '".$_POST["atuacao"]."' ";
}

if(count($setord)>0)
{
	$sql .= "AND funcionarios.id_setor IN (" . $filtro_setord . ") ";
}

$sql .= "GROUP BY apontamento_horas.id_funcionario ";
$sql .= "ORDER BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$i = 1;

foreach ($db->array_select as $regs)
{	
	$funcionarios[$i] = $regs["funcionario"];
	$local[$i] = $regs["descricao"];
	$funcid[$i] = $regs["id_funcionario"];
	$funchn[$i] = sec_to_time($regs["HN"],false);
	$funcha[$i] = sec_to_time($regs["HA"]+$regs["HAN"],false);
	$funcht[$i] = sec_to_time($regs["HN"]+$regs["HA"]+$regs["HAN"],false);;
	
	$tot_hn += $regs["HN"];
	$tot_ha += $regs["HA"]+$regs["HAN"];	
	$tot_ht += $regs["HN"]+$regs["HA"]+$regs["HAN"];
	
	$i++;
}	

$pdf->SetFont('Arial','',10);

for($i=1;$i<=count($funcionarios);$i++)
{
	if($pdf->GetY()>=192)
	{
		$pdf->addPage();
	}

	if($filtro_contrato=="" && false)
	{
		$pdf->HCell(10,5,sprintf("%03d",$funcid[$i]),1,0,'C',0);
		
		$pdf->HCell(60,5,$funcionarios[$i],1,0,'C',0);
		
		$pdf->HCell(10,5,$contrato[$funcid[$i]],1,0,'C',0);
		
		$pdf->SetFillColor(235,235,235);
	
		$xx = explode('/',$reg_data[$funcid[$i]]);
		
		// Imprime os dias		
		for($f=1;$f<=$diasdomes;$f++)
		{
	
			if(in_array($data[$f],$xx))
			{
				if($reg_hor[$data[$f].'#'.$funcid[$i]]==0) //Se for 00:00 hora 
				{
					$pdf->HCell(210/$diasdomes,5,number_format(($reg_hor[$data[$f].'#'.$funcid[$i]])/3600,2),1,0,'C',0); //sem preenchimento
				}
				else
				{
					$pdf->HCell(210/$diasdomes,5,number_format(($reg_hor[$data[$f].'#'.$funcid[$i]])/3600,2),1,0,'C',1); //com preenchimento
				}
			}
			else
			{
				$pdf->HCell(210/$diasdomes,5,'',1,0,'C',0);
			}					
		}				
	
		$pdf->HCell(10,5,$funchn[$i],1,0,'C',0);
		$pdf->HCell(10,5,$funcha[$i],1,0,'C',0);
		$pdf->HCell(15,5,$funcht[$i],1,1,'C',0);
		
		//PROTHEUS
		/*
		$pdf->HCell(80,5,'APONTAMENTOS CONFIRMADOS',1,0,'C',0);
		
		$pdf->SetFillColor(235,235,235);
	
		$xx = explode('/',$reg_data_p[$funcid_p[$funcid[$i]]]);
		
		// Imprime os dias
		for($f=1;$f<=$diasdomes;$f++)
		{
	
			if(in_array($data[$f],$xx))
			{
				if($reg_hor_p[$data[$f].'#'.$funcid_p[$funcid[$i]]]==0) //Se for 00:00 hora 
				{
					$pdf->HCell(210/$diasdomes,5,($reg_hor_p[$data[$f].'#'.$funcid_p[$funcid[$i]]])/3600,1,0,'C',0); //sem preenchimento
				}
				else
				{
					$pdf->HCell(210/$diasdomes,5,($reg_hor_p[$data[$f].'#'.$funcid_p[$funcid[$i]]])/3600,1,0,'C',1); //com preenchimento
				}
			}
			else
			{
				$pdf->HCell(210/$diasdomes,5,'',1,0,'C',0);
			}
					
		}
	
		$pdf->HCell(10,5,$funchn_p[$funcid[$i]],1,0,'C',0);
		$pdf->HCell(10,5,$funcha_p[$funcid[$i]],1,0,'C',0);
		$pdf->HCell(15,5,$funcht_p[$funcid[$i]],1,1,'C',0);
		*/		
	}
	else
	{
		if(in_array($contrato[$funcid[$i]],$array_contrato))
		{
			$pdf->HCell(40,5,$funcionarios[$i],1,0,'C',0);
			
			$pdf->SetFillColor(235,235,235);
		
			$xx = explode('/',$reg_data[$funcid[$i]]);
			
			// Imprime os dias
			for($f=1;$f<=$diasdomes;$f++)
			{				
				if(in_array($data[$f],$xx))
				{
					if($reg_hor[$data[$f].'#'.$funcid[$i]]==0) //Se for 00:00 hora 
					{
						$pdf->HCell(210/$diasdomes,5,sec_to_time(($reg_hor[$data[$f].'#'.$funcid[$i]]),false),1,0,'C',0); //sem preenchimento
					}
					else
					{
						$pdf->HCell(210/$diasdomes,5,sec_to_time(($reg_hor[$data[$f].'#'.$funcid[$i]]),false),1,0,'C',1); //com preenchimento
					}
				}
				else
				{
					$pdf->HCell(210/$diasdomes,5,'',1,0,'C',0);					
				}						
			}
		
			$pdf->HCell(10,5,$funchn[$i],1,0,'C',0);
			$pdf->HCell(10,5,$funcha[$i],1,0,'C',0);
			$pdf->HCell(15,5,$funcht[$i],1,1,'C',0);
			
			//PROTHEUS
			$pdf->HCell(10,5,sprintf("%03d",$funcid[$i]),1,0,'C',0);
			$pdf->HCell(20,5,$local[$i],1,0,'C',0);
			$pdf->HCell(10,5,$contrato[$funcid[$i]],1,0,'C',0);
			
			$pdf->SetFillColor(235,235,235);
		
			$xx = explode('/',$reg_data_p[$funcid_p[$funcid[$i]]]);
			
			// Imprime os dias
			for($f=1;$f<=$diasdomes;$f++)
			{		
				if(in_array($data[$f],$xx))
				{
					if($reg_hor_p[$data[$f].'#'.$funcid_p[$funcid[$i]]]==0) //Se for 00:00 hora 
					{
						$pdf->HCell(210/$diasdomes,5,sec_to_time(($reg_hor_p[$data[$f].'#'.$funcid_p[$funcid[$i]]]),false),1,0,'C',0); //sem preenchimento
					}
					else
					{
						$pdf->HCell(210/$diasdomes,5,sec_to_time(($reg_hor_p[$data[$f].'#'.$funcid_p[$funcid[$i]]]),false),1,0,'C',1); //com preenchimento
					}
				}
				else
				{
					$pdf->HCell(210/$diasdomes,5,'',1,0,'C',0);
				}						
			}
			
			/*
			$pdf->HCell(10,5,$funchn_p[$funcid[$i]],1,0,'C',0);
			$pdf->HCell(10,5,$funcha_p[$funcid[$i]],1,0,'C',0);
			$pdf->HCell(15,5,$funcht_p[$funcid[$i]],1,1,'C',0);
			*/

			$pdf->Ln(1);		
		}		
	}
}

$pdf->HCell(250,5,'TOTAL: ',0,0,'R',0);
$pdf->HCell(10,5,sec_to_time($tot_hn,false),1,0,'C',0);
$pdf->HCell(10,5,sec_to_time($tot_ha,false),1,0,'C',0);
$pdf->HCell(15,5,sec_to_time($tot_ht,false),1,1,'C',0);

$pdf->Output('RELATORIO_APONTAMENTOS_'.date('dmYhis').'.pdf', 'D');

?>