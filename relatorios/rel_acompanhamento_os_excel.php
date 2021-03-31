<?php
/*
		Relatório Acompanhamento OS
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../relatorios/rel_acompanhamento_os_excel.php
	
		Versão 0 --> VERSÃO INICIAL : 10/03/2015 - Carlos Abreu
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
*/	

header("Content-Type: application/vnd.ms-excel");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

$db = new banco_dados();

?>
	<style type="text/css">
	<!--
	.style1 {
		font-size: 12px;
		
	}
	
	.style2 {
	font-size: 14px;
	font-weight: bold;
	
	}
	.style3 {
		font-size: 18px;
		font-weight: bold;
	}
	-->
	</style>
<?php

if($_POST["lista_pendencia"])
{
	$id_os = $_POST["id_os_lista"];
}
else
{
	$id_os = $_POST["id_os"];
}

$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".empresas, ".DATABASE.".contatos, ".DATABASE.".funcionarios ";
$sql .= "WHERE ordem_servico.id_os = '" . $id_os . "' ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND contatos.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";
$sql .= "AND ordem_servico.id_cod_resp = contatos.id_contato ";
$sql .= "AND ordem_servico.id_cod_coord = funcionarios.id_funcionario ";

$db->select($sql,'MYSQL',true);

$reg_os = $db->array_select[0];

if($_POST["lista_pendencia"])
{

	?>
	<table width="100%" border="1">
			  <tr>
			    <td> </td>
			    <td colspan="8" align="center"><div align="center" class="style3" style="vertical-align: middle;">LISTA DE PENDÊNCIAS DE ENGENHARIA</div></td>
      </tr>
			  <tr>
			    <td colspan="2" valign="middle"><div class="style3" style="vertical-align: middle;">OS Nº; <?= sprintf("%05d",$reg_os["os"]) ?></p></td>
			    <td colspan="4" valign="middle"><div class="style3" style="vertical-align: middle;">Título: <?= $reg_os["descricao"] ?></p></td>
			    <td colspan="3" valign="middle"><div class="style2" style="vertical-align: middle;">Emissão: <?= date("d/m/Y") ?></p></td>
      </tr>
			  <tr>
			    <td colspan="9"> </td>
      </tr>
			  <tr>
				<td width="5%"><div align="center" class="style2" style="vertical-align: middle;">Item</div></td>
				<td width="15%"><div align="center" class="style2" style="vertical-align: middle;">Identificação do Problema</div></td>
				<td width="15%"><div align="center" class="style2" style="vertical-align: middle;">Disciplina</div></td>
				<td width="15%"><div align="center" class="style2" style="vertical-align: middle;">Solicitado por</div></td>
				<td width="15%"><div align="center" class="style2" style="vertical-align: middle;">Responsável pela solução</div></td>
				<td width="10%"><div align="center" class="style2" style="vertical-align: middle;">Status</div></td>
				<td width="10%"><div align="center" class="style2" style="vertical-align: middle;">Data</div></td>
				<td width="12%"><div align="center" class="style2" style="vertical-align: middle;">Observação</div></td>
				<td width="11%"><div align="center" class="style2" style="vertical-align: middle;">Ação Corretiva</div></td>
			  </tr>
			<?php
            $sql = "SELECT * FROM ".DATABASE.".os_x_analise_critica_inicial ";
            $sql .= "WHERE os_x_analise_critica_inicial.id_os = '" . $reg_os["id_os"] . "' ";
			$sql .= "AND os_x_analise_critica_inicial.reg_del = 0 ";
            
            $db->select($sql,'MYSQL',true);
            
            $regs = $db->array_select[0];
            
            $sql = "SELECT * FROM ".DATABASE.".os_x_analise_critica_periodica ";
            $sql .= "LEFT JOIN ".DATABASE.".setores ON (os_x_analise_critica_periodica.id_disciplina = setores.id_setor AND setores.reg_del = 0) ";
            $sql .= "WHERE os_x_analise_critica_periodica.id_os = '" . $reg_os["id_os"] . "' ";
			$sql .= "AND os_x_analise_critica_periodica.reg_del ";
            
            $db->select($sql,'MYSQL',true);

			$item = 1;
			
			foreach($db->array_select as $regs)
			{
				$fonte = "#000000";
				switch ($regs["status_ap"])
				{
					case 1: $status_ap = "PENDENTE";
							$fonte = "#FF0000";
					break;
					
					case 2: $status_ap = "RESOLVIDO";
							$fonte = "#0000FF";
					break;
					
					case 3: $status_ap = "INFORMAÇÃO";
							$fonte = "#00FF00";
					break;
					
					default : $status_ap = "";
				}
			
				?>
				  <tr>
					<td><div align="center" class="style1" style="vertical-align: middle;"><?= $item++ ?></div></td>
					<td><div align="center" class="style1" style="vertical-align: middle;"><?= $regs["identificacao_problema_ap"] ?></div></td>
					<td><div align="center" class="style1" style="vertical-align: middle;"><?= $regs["setor"] ?></div></td>
					<td><div align="center" class="style1" style="vertical-align: middle;"><?= $regs["solicitado_por"] ?></div></td>
					<td><div align="center" class="style1" style="vertical-align: middle;"><?= $regs["solucao_por"] ?></div></td>
					<td><div align="center" class="style1" style="color:<?= $fonte ?>;vertical-align: middle;"><?= $status_ap ?></div></td>
					<td><div align="center" class="style1" style="vertical-align: middle;"><?= mysql_php($regs["data_ap"]) ?></div></td>
					<td><div align="center" class="style1" style="vertical-align: middle;"><?= $regs["solucao_possivel_ap"] ?></div></td>
					<td><div align="center" class="style1" style="vertical-align: middle;"><?= $regs["acao_corretiva_ap"] ?></div></td>
				  </tr>
				<?php
			
			}
			
			?>        
		</table>

<?php

}
else
{
	
	$sql = "SELECT * FROM ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.id_funcionario = '" . $reg_os["id_funcionario"] . "' ";
	$sql .= "AND funcionarios.id_usuario = usuarios.id_usuario ";
	$sql .= "AND usuarios.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	$reg_func = $db->array_select[0];
	
	$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".os_x_adicionais ";
	$sql .= "WHERE os_x_adicionais.id_os_adicional = '".$reg_os["id_os"]."' ";
	$sql .= "AND ordem_servico.id_os = os_x_adicionais.id_os_raiz ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND os_x_adicionais.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	$reg_ad = $db->array_select[0];
	
	?>
    <table width="100%" border="1">
    	<tr>
    		<td colspan="2"> </td>
            <td colspan="5"><div align="left" class="style3" style="vertical-align: middle;">ACOMPANHAMENTO DE PROJETOS DE ENGENHARIA - OS Nº <?= sprintf("%05d",$reg_os["os"]) ?></div></td>
    	    <td colspan="2"><div align="left" class="style2" style="vertical-align: middle;">Emissão: <?= date("d/m/Y") ?></div></td>
   	    </tr>
    	<tr>
    	  <td colspan="3"><div class="style2" style="vertical-align: middle;">Cliente: <?= $reg_os["empresa"] ?></div></td>
    	  <td width="1%"> </td>
    	  <td width="29%"><div class="style2" style="vertical-align: middle;">Projeto Inicio: <?= mysql_php($reg_os["projeto_inicio"]) ?></div></td>
    	  <td colspan="2"><div class="style2" style="vertical-align: middle;">Projeto Fim: <?= mysql_php($reg_os["projeto_termino"]) ?>
    	  </div></td>
    	  <td width="14%"> </td>
    	  <td width="25%"><div class="style2" style="vertical-align: middle;">As Built: <?= mysql_php($reg_av["data_asbuilt"]) ?></div></td>
  	  </tr>
    	<tr>
    	  <td colspan="9"><div class="style2" style="vertical-align: middle;">Projeto: <?= $reg_os["descricao"] ?></div></td>
   	  </tr>
    	<tr>
    	  <td colspan="3"><div class="style2" style="vertical-align: middle;">Coord. Cliente: <?= $reg_os["nome_contato"] ?></div></td>
    	  <td> </td>
    	  <td colspan="5"><div class="style2" style="vertical-align: middle;">E-mail: <?= $reg_os["email"] ?></DIV></td>
   	  </tr>
    	<tr>
    	  <td colspan="3"><div class="style2" style="vertical-align: middle;">Coord. : <?= $reg_os["funcionario"] ?></div></td>
    	  <td> </td>
    	  <td colspan="5"><div class="style2" style="vertical-align: middle;">E-mail: <?= $reg_func["email"] ?></div></td>
   	  </tr>
    	<tr>
    	  <td colspan="9"> </td>
   	  </tr>
    	<tr>
    	  <td colspan="5"><div align="center" class="style3" style="vertical-align: middle;">Especialidade</div></td>
    	  <td colspan="4"><div align="center" class="style3" style="vertical-align: middle;">Equipe</div></td>
   	  </tr>
		<?php
        $sql = "SELECT * FROM ".DATABASE.".os_x_funcionarios, ".DATABASE.".funcionarios, ".DATABASE.".rh_cargos, ".DATABASE.".setores ";
        $sql .= "WHERE os_x_funcionarios.id_funcionario = funcionarios.id_funcionario ";
        $sql .= "AND funcionarios.id_funcao = rh_cargos.id_funcao ";
        $sql .= "AND os_x_funcionarios.id_os = '" . $reg_os["id_os"] . "' ";
        $sql .= "AND funcionarios.id_setor = setores.id_setor ";
        $sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
        $sql .= "ORDER BY setor, funcionario ";
        
        $db->select($sql,'MYSQL',true);
        
        foreach($db->array_select as $reg_func)
        {
            ?>
              <tr>
                <td colspan="5"><div class="style2" style="vertical-align: middle;"<?= $reg_func["setor"] ?>></div></td>
                <td colspan="4"><div class="style2" style="vertical-align: middle;"<?= $reg_func["funcionario"] . " - " . $reg_func["categoria"] ?>></div></td>
              </tr>    
            <?php
        }
        ?> 
      
    	<tr>
    	  <td colspan="9"> </td>
   	  </tr>
    	<tr>
    	  <td colspan="5"><div align="center" class="style3" style="vertical-align: middle;">ENTRADAS</div></td>
    	  <td colspan="4"><div align="center" class="style3" style="vertical-align: middle;">SAÍDAS</div></td>
   	  </tr>

		<?php
        $sql = "SELECT * FROM ".DATABASE.".os_x_entradas_saidas ";
        $sql .= "WHERE id_os = '" . $reg_os["id_os"] . "' ";
        
        $db->select($sql,'MYSQL',true);
        
        $regs = $db->array_select[0];
        
        $sql = "SELECT nome_validador, data_validacao FROM ".DATABASE.".os_x_validacao ";
        $sql .= "WHERE id_os = '" . $reg_os["id_os"] . "' ";
        
        $db->select($sql,'MYSQL',true);
        
        $reg_validacao = $db->array_select[0];
        
        $sql = "SELECT * FROM ".DATABASE.".os_x_analise_critica_final ";
        $sql .= "WHERE id_os = '" . $reg_os["id_os"] . "' ";
        
        $db->select($sql,'MYSQL',true);
        
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
        
        ?>  
      
    	<tr>
    	  <td colspan="5"> </td>
    	  <td width="6%"> </td>
    	  <td width="16%"><div align="center" class="style2" style="vertical-align: middle;">SIM</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">N/A</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">Data</div></td>
  	  </tr>
    	<tr>
    	  <td colspan="5"><div class="style1" style="vertical-align: middle;">1 - Reuniãoo inicial com o cliente (kick off meeting)</div></td>
    	  <td><div class="style1" style="vertical-align: middle;">Ata de reunião e/ou anotações</div></td>
    	  <td><div align="center" style="vertical-align: middle;"><?= $chk_ata_reuniao ?></div></td>
    	  <td><div align="center" style="vertical-align: middle;"><?= $chk_ata_reuniao_na ?></div></td>
    	  <td><div align="center" style="vertical-align: middle;"><?= mysql_php($regs["data_ata"]) ?></div></td>
  	  </tr>
    	<tr>
    	  <td colspan="9"><div class="style1" style="vertical-align: middle;">2 - Informações para execução do projeto</div></td>
   	  </tr>
    	<tr>
    	  <td colspan="5"> </td>
    	  <td> </td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">SIM</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">N/A</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">Obs:</div></td>
  	  </tr>
    	<tr>
    	  <td colspan="5"><div class="style1" style="vertical-align: middle;">* Levantamento no campo (planta e/ou arquivo técnico do cliente);</div></td>
    	  <td><div class="style1" style="vertical-align: middle;">Check-list preenchido e dados coletados</div></td>
    	  <td><div align="center">
    	    <?= $chk_list ?>
  	    </div></td>
    	  <td><div align="center">
    	    <?= $chk_list_na ?>
  	    </div></td>
    	  <td><div align="center">
    	    <?= $regs["obs_chk_list"] ?>
  	    </div></td>
  	  </tr>
    	<tr>
    	  <td colspan="5"><div class="style1" style="vertical-align: middle;">* Requisitos de funcionamento e de desempenho;</div></td>
    	  <td><span class="style1">Requisitos levantados</span></td>
    	  <td><div align="center">
    	    <?= $chk_req_func ?>
  	    </div></td>
    	  <td><div align="center">
    	    <?= $chk_req_func_na ?>
  	    </div></td>
    	  <td><div align="center">
    	    <?= $regs["obs_req_func"] ?>
  	    </div></td>
  	  </tr>
    	<tr>
    	  <td colspan="5"><div class="style1" style="vertical-align: middle;">* Requisitos estatutários e regulamentares aplicáveis;</div></td>
    	  <td><div class="style1" style="vertical-align: middle;">Requisitos levantados</div></td>
    	  <td><div align="center">
    	    <?= $chk_req_estat ?>
  	    </div></td>
    	  <td><div align="center">
    	    <?= $chk_req_estat_na ?>
  	    </div></td>
    	  <td><div align="center">
    	    <?= $regs["obs_req_estat"] ?>
  	    </div></td>
  	  </tr>
    	<tr>
    	  <td colspan="5"><div class="style1" style="vertical-align: middle;">* Informações originadas de projetos anteriores semelhantes, se aplicáveis;</div></td>
    	  <td><div class="style1" style="vertical-align: middle;">Requisitos levantados</div></td>
    	  <td><div align="center">
    	    <?= $chk_inf_proj ?>
  	    </div></td>
    	  <td><div align="center">
    	    <?= $chk_inf_proj_na ?>
  	    </div></td>
    	  <td><div align="center">
    	    <?= $regs["obs_req_inf_proj"] ?>
  	    </div></td>
  	  </tr>
    	<tr>
    	  <td colspan="5"><div class="style1" style="vertical-align: middle;">* Escopo de fornecimento;</div></td>
    	  <td><div class="style1" style="vertical-align: middle;">Escopo definido</div></td>
    	  <td><div align="center">
    	    <?= $chk_escop_forn ?>
  	    </div></td>
    	  <td><div align="center">
    	    <?= $chk_escop_forn_na ?>
  	    </div></td>
    	  <td><div align="center">
    	    <?= $regs["obs_escop_forn"] ?>
  	    </div></td>
  	  </tr>
    	<tr>
    	  <td colspan="5"><div class="style1" style="vertical-align: middle;">* Referências;</div></td>
    	  <td><div class="style1" style="vertical-align: middle;">Referências levantadas</div></td>
    	  <td><div align="center">
    	    <?= $chk_referencias ?>
  	    </div></td>
    	  <td><div align="center">
    	    <?= $chk_referencias_na ?>
  	    </div></td>
    	  <td><div align="center">
    	    <?= $regs["obs_referencias"] ?>
  	    </div></td>
  	  </tr>
    	<tr>
    	  <td colspan="5"><div class="style1" style="vertical-align: middle;">* Exclusões;</div></td>
    	  <td><div class="style1" style="vertical-align: middle;">Exclusões definidas</div></td>
    	  <td><div align="center">
    	    <?= $chk_exclusoes ?>
  	    </div></td>
    	  <td><div align="center">
    	    <?= $chk_exclusoes_na ?>
  	    </div></td>
    	  <td><div align="center">
    	    <?= $regs["obs_exclusoes"] ?>
  	    </div></td>
  	  </tr>
    	<tr>
    	  <td colspan="5"><div class="style1" style="vertical-align: middle;">3 - Solicitação de números para os documentos novos e/ou solicitação de documentos existentes do projeto.</div></td>
    	  <td><div class="style1" style="vertical-align: middle;">Solicitações realizadas</div></td>
    	  <td><div align="center">
    	    <?= $chk_solic_num ?>
  	    </div></td>
    	  <td><div align="center">
    	    <?= $chk_solic_num_na ?>
  	    </div></td>
    	  <td><div align="center">
    	    <?= $regs["obs_solic_num"] ?>
  	    </div></td>
  	  </tr>
    	<tr>
    	  <td colspan="9"> </td>
   	  </tr>
    	<tr>
    	  <td colspan="9"><div align="center" class="style2" style="vertical-align: middle;">Análise crítica inicial (para análise crítica periódica, utilize o Procedimento de Execução de Projetos ).</div></td>
   	  </tr>
		<?php
        $sql = "SELECT * FROM ".DATABASE.".os_x_analise_critica_inicial ";
        $sql .= "WHERE id_os = '" . $reg_os["id_os"] . "' ";
        
        $db->select($sql,'MYSQL',true);
        
        $regs = $db->array_select[0];
        
        $sql = "SELECT * FROM ".DATABASE.".os_x_analise_critica_periodica ";
        $sql .= "LEFT JOIN ".DATABASE.".setores ON (os_x_analise_critica_periodica.id_disciplina = setores.id_setor) ";
        $sql .= "WHERE id_os = '" . $reg_os["id_os"] . "' ";
        
        $db->select($sql,'MYSQL',true);
		
		$array_ap = $db->array_select;
        
        if($db->numero_registros>0)
        {
            $str_perio = "SIM";
        }
        else
        {
            $str_perio = "NÃO";
        }
        
        ?>    	
        
        <tr>
    	  <td colspan="3"><div align="center" class="style1" style="vertical-align: middle;">1 - Existem recursos para a execução do projeto?</div></td>
    	  <td colspan="6"><?= maiusculas($regs["recursos_execucao"]) ?></td>
   	  </tr>
    	<tr>
    	  <td colspan="3"><div align="center" class="style1" style="vertical-align: middle;">2 - Existem problemas para a realização do projeto? Descreva as ações necessárias.</div></td>
    	  <td colspan="6"><?= $str_perio ?></td>
   	  </tr>
    	<tr>
    	  <td width="1%"><div align="center" class="style2" style="vertical-align: middle;">Item</div></td>
    	  <td width="1%"><div align="center" class="style2" style="vertical-align: middle;">Identificação do Problema</div></td>
    	  <td width="7%"><div align="center" class="style2" style="vertical-align: middle;">Disciplina</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">Solicitado por</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">Responsável pela solução</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">Status</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">Data</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">Observação</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">Ação Corretiva</div></td>
  	  </tr>
			<?php
			$item = 1;
			
			foreach($array_ap as $regs)
			{
				$fonte = "#000000";
				switch ($regs["status_ap"])
				{
					case 1: $status_ap = "PENDENTE";
							$fonte = "#FF0000";
					break;
					
					case 2: $status_ap = "RESOLVIDO";
							$fonte = "#0000FF";
					break;
					
					case 3: $status_ap = "INFORMAÇÃO";
							$fonte = "#00FF00";
					break;
					
					default : $status_ap = "";
				}
			
				?>
				  <tr>
					<td><div align="center" class="style1" style="vertical-align: middle;"><?= $item++ ?></div></td>
					<td><div class="style1" style="vertical-align: middle;"><?= $regs["identificacao_problema_ap"] ?></div></td>
					<td><div align="center" class="style1" style="vertical-align: middle;"><?= $regs["setor"] ?></div></td>
					<td><div align="center" class="style1" style="vertical-align: middle;"><?= $regs["solicitado_por"] ?></div></td>
					<td><div align="center" class="style1" style="vertical-align: middle;"><?= $regs["solucao_por"] ?></div></td>
					<td><div align="center" class="style1" style="vertical-align: middle; color:<?= $fonte ?>;"><?= $status_ap ?></div></td>
					<td><div align="center" class="style1" style="vertical-align: middle;"><?= mysql_php($regs["data_ap"]) ?></div></td>
					<td><div class="style1" style="vertical-align: middle;"><?= $regs["solucao_possivel_ap"] ?></div></td>
					<td><div class="style1" style="vertical-align: middle;"><?= $regs["acao_corretiva_ap"] ?></div></td>
				  </tr>
				<?php
			
			}
			
			?>  
        <tr>
    	  <td colspan="9"> </td>
   	  </tr>
        <tr>
          <td colspan="5"><div align="center" class="style2" style="vertical-align: middle;">Verificação do projeto</div></td>
          <td> </td>
          <td colspan="3"><div align="center" class="style2" style="vertical-align: middle;">Controle de alterações e revisões</div></td>
        </tr>
        <tr>
          <td colspan="5"><div class="style1">Conforme Análise e Verificação de Projeto, executada em todos os documentos que são finalizados para a entrega ao cliente ao longo do projetos, de acordo com o cronagrama consolidado</div></td>
          <td> </td>
          <td colspan="3"><div class="style1">As alterações são registradas no formulário Solicitação de Alteração e/o Serviço. As revisões são anotadas no próprio documento do projeto e registradas pelo Arquivo Técnico.</div></td>
        </tr>
        <tr>
          <td colspan="9"> </td>
        </tr>
        <tr>
          <td colspan="5"><div align="center" class="style2" style="vertical-align: middle;">Validação do projeto</div></td>
          <td> </td>
          <td colspan="3"><div align="center" class="style2" style="vertical-align: middle;">Validação pelo responsável técnico</div></td>
        </tr>
        <tr>
          <td colspan="5"><p class="style1">A validação do projeto é evidenciada por:</p>
            <p class="style1">1) Análise e verificação dos documentos do projeto emitidos para o cliente e,</p>
          <p class="style1">2) Análise final pelo responsável técnico</p></td>
          <td> </td>
          <td><div class="style2">Coordenador:<?= $reg_validacao["nome_validador"] ?></div></td>
          <td> </td>
          <td><div class="style2">data:<?= mysql_php($reg_validacao["data_validacao"]) ?></div></td>
        </tr>
        <tr>
          <td colspan="9"> </td>
        </tr>
        <tr>
          <td colspan="9"><div align="center" class="style2" style="vertical-align: middle;">Análise crítica final (Utilize o Procedimento de Execução de Projetos)</div></td>
        </tr>
        <tr>
          <td colspan="9"><div  class="style2" style="vertical-align: middle;">1 - Os prazos foram cumpridos conforme previsto no cronograma? Se ocorreram atrasos, quais foram as principais causas? Comentários/Justificativa:</div></td>
        </tr>
        <tr>
          <td colspan="9"><div  class="style1" style="vertical-align: middle;"><?= $reg_acf["txt_prazos"] ?></div></td>
        </tr>
        <tr>
          <td colspan="9"><div  class="style2" style="vertical-align: middle;">2 - As não-conformidades, se houve, foram resolvidas eficazmente? Comentários:</div></td>
        </tr>
        <tr>
          <td colspan="9"><div  class="style1" style="vertical-align: middle;"><?= $reg_acf["txt_naoconforme"] ?></div></td>
        </tr>
        <tr>
          <td colspan="9"><div  class="style2" style="vertical-align: middle;">3 - A equipe estava corretamente dimensionada? Os profissionais demonstraram competência técnica? Comentários:</div></td>
        </tr>
        <tr>
          <td colspan="9"><div  class="style1" style="vertical-align: middle;"><?= $reg_acf["txt_equipe"] ?></div></td>
        </tr>
        <tr>
          <td colspan="9"><div  class="style2" style="vertical-align: middle;">4 - A qualidade do projeto foi adequadamente verificada? Houve Realimentação do cliente (sugestões, elogios, reclamações e etc.)? Descreva:</div></td>
        </tr>
        <tr>
          <td colspan="9"><div  class="style1" style="vertical-align: middle;"><?= $reg_acf["txt_qualidade"] ?></div></td>
        </tr>
        <tr>
          <td colspan="9"><div  class="style2" style="vertical-align: middle;">5 - Constatou-se necessidade de melhorias para os novos projetos? Descreva:</div></td>
        </tr>
        <tr>
          <td colspan="9"><div  class="style1" style="vertical-align: middle;"><?= $reg_acf["txt_melhorias"] ?></div></td>
        </tr>
        <tr>
          <td colspan="9"> </td>
        </tr>
        <tr>
          <td colspan="2"><div class="style2" style="vertical-align: middle;">COORDENADOR</div></td>
          <td> </td>
          <td colspan="6"><div class="style2" style="vertical-align: middle;">DATA</div></td>
        </tr>
    </table>
    
    
    
    
	
	<?php
}

?>
