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
$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
$sql .= "AND ordem_servico.id_cod_resp = contatos.id_contato ";
$sql .= "AND ordem_servico.id_cod_coord = funcionarios.id_funcionario ";

$db->select($sql,'MYSQL',true);

$reg_os = $db->array_select[0];

if($_POST["lista_pendencia"])
{

	?>
	<table width="100%" border="1">
			  <tr>
			    <td>&nbsp;</td>
			    <td colspan="8" align="center"><div align="center" class="style3" style="vertical-align: middle;">LISTA&nbsp;DE&nbsp;PENDÊNCIAS DE ENGENHARIA</div></td>
      </tr>
			  <tr>
			    <td colspan="2" valign="middle"><div class="style3" style="vertical-align: middle;">OS&nbsp;Nº;&nbsp;<?= sprintf("%05d",$reg_os["os"]) ?></p></td>
			    <td colspan="4" valign="middle"><div class="style3" style="vertical-align: middle;">Título:&nbsp;<?= $reg_os["descricao"] ?></p></td>
			    <td colspan="3" valign="middle"><div class="style2" style="vertical-align: middle;">Emissão:&nbsp;<?= date("d/m/Y") ?></p></td>
      </tr>
			  <tr>
			    <td colspan="9">&nbsp;</td>
      </tr>
			  <tr>
				<td width="5%"><div align="center" class="style2" style="vertical-align: middle;">Item</div></td>
				<td width="15%"><div align="center" class="style2" style="vertical-align: middle;">Identificação&nbsp;do&nbsp;Problema</div></td>
				<td width="15%"><div align="center" class="style2" style="vertical-align: middle;">Disciplina</div></td>
				<td width="15%"><div align="center" class="style2" style="vertical-align: middle;">Solicitado&nbsp;por</div></td>
				<td width="15%"><div align="center" class="style2" style="vertical-align: middle;">Responsável&nbsp;pela solução</div></td>
				<td width="10%"><div align="center" class="style2" style="vertical-align: middle;">Status</div></td>
				<td width="10%"><div align="center" class="style2" style="vertical-align: middle;">Data</div></td>
				<td width="12%"><div align="center" class="style2" style="vertical-align: middle;">Observação</div></td>
				<td width="11%"><div align="center" class="style2" style="vertical-align: middle;">Ação&nbsp;Corretiva</div></td>
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
	
	$sql = "SELECT * FROM ".DATABASE.".usuarios ";
	$sql .= "WHERE usuarios.id_funcionario = '" . $reg_os["id_funcionario"] . "' ";
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
    		<td colspan="2">&nbsp;</td>
            <td colspan="5"><div align="left" class="style3" style="vertical-align: middle;">ACOMPANHAMENTO&nbsp;DE&nbsp;PROJETOS&nbsp;DE&nbsp;ENGENHARIA&nbsp;-&nbsp;OS&nbsp;Nº&nbsp;<?= sprintf("%05d",$reg_os["os"]) ?></div></td>
    	    <td colspan="2"><div align="left" class="style2" style="vertical-align: middle;">Emissão:&nbsp;<?= date("d/m/Y") ?></div></td>
   	    </tr>
    	<tr>
    	  <td colspan="3"><div class="style2" style="vertical-align: middle;">Cliente:&nbsp;<?= $reg_os["empresa"] ?></div></td>
    	  <td width="1%">&nbsp;</td>
    	  <td width="29%"><div class="style2" style="vertical-align: middle;">Projeto&nbsp;Inicio:&nbsp;<?= mysql_php($reg_os["projeto_inicio"]) ?></div></td>
    	  <td colspan="2"><div class="style2" style="vertical-align: middle;">Projeto&nbsp;Fim:&nbsp;<?= mysql_php($reg_os["projeto_termino"]) ?>
    	  </div></td>
    	  <td width="14%">&nbsp;</td>
    	  <td width="25%"><div class="style2" style="vertical-align: middle;">As&nbsp;Built:&nbsp;<?= mysql_php($reg_av["data_asbuilt"]) ?></div></td>
  	  </tr>
    	<tr>
    	  <td colspan="9"><div class="style2" style="vertical-align: middle;">Projeto:&nbsp;<?= $reg_os["descricao"] ?></div></td>
   	  </tr>
    	<tr>
    	  <td colspan="3"><div class="style2" style="vertical-align: middle;">Coord.&nbsp;Cliente:&nbsp;<?= $reg_os["nome_contato"] ?></div></td>
    	  <td>&nbsp;</td>
    	  <td colspan="5"><div class="style2" style="vertical-align: middle;">E-mail:&nbsp;<?= $reg_os["email"] ?></DIV></td>
   	  </tr>
    	<tr>
    	  <td colspan="3"><div class="style2" style="vertical-align: middle;">Coord.&nbsp;:&nbsp;<?= $reg_os["funcionario"] ?></div></td>
    	  <td>&nbsp;</td>
    	  <td colspan="5"><div class="style2" style="vertical-align: middle;">E-mail:&nbsp;<?= $reg_func["email"] ?></div></td>
   	  </tr>
    	<tr>
    	  <td colspan="9">&nbsp;</td>
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
    	  <td colspan="9">&nbsp;</td>
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
    	  <td colspan="5">&nbsp;</td>
    	  <td width="6%">&nbsp;</td>
    	  <td width="16%"><div align="center" class="style2" style="vertical-align: middle;">SIM</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">N/A</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">Data</div></td>
  	  </tr>
    	<tr>
    	  <td colspan="5"><div class="style1" style="vertical-align: middle;">1&nbsp;-&nbsp;Reuniãoo&nbsp;inicial&nbsp;com&nbsp;o&nbsp;cliente&nbsp;(kick&nbsp;off&nbsp;meeting)</div></td>
    	  <td><div class="style1" style="vertical-align: middle;">Ata&nbsp;de&nbsp;reunião&nbsp;e/ou&nbsp;anotações</div></td>
    	  <td><div align="center" style="vertical-align: middle;"><?= $chk_ata_reuniao ?></div></td>
    	  <td><div align="center" style="vertical-align: middle;"><?= $chk_ata_reuniao_na ?></div></td>
    	  <td><div align="center" style="vertical-align: middle;"><?= mysql_php($regs["data_ata"]) ?></div></td>
  	  </tr>
    	<tr>
    	  <td colspan="9"><div class="style1" style="vertical-align: middle;">2&nbsp;-&nbsp;Informações&nbsp;para&nbsp;execução&nbsp;do&nbsp;projeto</div></td>
   	  </tr>
    	<tr>
    	  <td colspan="5">&nbsp;</td>
    	  <td>&nbsp;</td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">SIM</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">N/A</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">Obs:</div></td>
  	  </tr>
    	<tr>
    	  <td colspan="5"><div class="style1" style="vertical-align: middle;">*&nbsp;Levantamento&nbsp;no&nbsp;campo&nbsp;(planta&nbsp;e/ou&nbsp;arquivo&nbsp;técnico&nbsp;do&nbsp;cliente);</div></td>
    	  <td><div class="style1" style="vertical-align: middle;">Check-list&nbsp;preenchido&nbsp;e&nbsp;dados&nbsp;coletados</div></td>
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
    	  <td colspan="5"><div class="style1" style="vertical-align: middle;">*&nbsp;Requisitos&nbsp;de&nbsp;funcionamento&nbsp;e&nbsp;de&nbsp;desempenho;</div></td>
    	  <td><span class="style1">Requisitos&nbsp;levantados</span></td>
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
    	  <td colspan="5"><div class="style1" style="vertical-align: middle;">*&nbsp;Requisitos&nbsp;estatutários&nbsp;e&nbsp;regulamentares&nbsp;aplicáveis;</div></td>
    	  <td><div class="style1" style="vertical-align: middle;">Requisitos&nbsp;levantados</div></td>
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
    	  <td colspan="5"><div class="style1" style="vertical-align: middle;">*&nbsp;Informações&nbsp;originadas&nbsp;de&nbsp;projetos&nbsp;anteriores&nbsp;semelhantes,&nbsp;se&nbsp;aplicáveis;</div></td>
    	  <td><div class="style1" style="vertical-align: middle;">Requisitos&nbsp;levantados</div></td>
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
    	  <td colspan="5"><div class="style1" style="vertical-align: middle;">*&nbsp;Escopo&nbsp;de&nbsp;fornecimento;</div></td>
    	  <td><div class="style1" style="vertical-align: middle;">Escopo&nbsp;definido</div></td>
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
    	  <td colspan="5"><div class="style1" style="vertical-align: middle;">*&nbsp;Referências;</div></td>
    	  <td><div class="style1" style="vertical-align: middle;">Referências&nbsp;levantadas</div></td>
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
    	  <td colspan="5"><div class="style1" style="vertical-align: middle;">*&nbsp;Exclusões;</div></td>
    	  <td><div class="style1" style="vertical-align: middle;">Exclusões&nbsp;definidas</div></td>
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
    	  <td colspan="5"><div class="style1" style="vertical-align: middle;">3&nbsp;-&nbsp;Solicitação&nbsp;de&nbsp;números&nbsp;para&nbsp;os&nbsp;documentos&nbsp;novos e/ou solicitação&nbsp;de&nbsp;documentos&nbsp;existentes&nbsp;do&nbsp;projeto.</div></td>
    	  <td><div class="style1" style="vertical-align: middle;">Solicitações&nbsp;realizadas</div></td>
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
    	  <td colspan="9">&nbsp;</td>
   	  </tr>
    	<tr>
    	  <td colspan="9"><div align="center" class="style2" style="vertical-align: middle;">Análise&nbsp;crítica&nbsp;inicial&nbsp;(para&nbsp;análise&nbsp;crítica&nbsp;periódica,&nbsp;utilize&nbsp;o&nbsp;Procedimento&nbsp;de&nbsp;Execução&nbsp;de&nbsp;Projetos&nbsp;).</div></td>
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
    	  <td colspan="3"><div align="center" class="style1" style="vertical-align: middle;">1&nbsp;-&nbsp;Existem&nbsp;recursos&nbsp;para&nbsp;a&nbsp;execução&nbsp;do&nbsp;projeto?</div></td>
    	  <td colspan="6"><?= maiusculas($regs["recursos_execucao"]) ?></td>
   	  </tr>
    	<tr>
    	  <td colspan="3"><div align="center" class="style1" style="vertical-align: middle;">2&nbsp;-&nbsp;Existem&nbsp;problemas&nbsp;para&nbsp;a&nbsp;realização&nbsp;do&nbsp;projeto?&nbsp;Descreva&nbsp;as&nbsp;ações&nbsp;necessárias.</div></td>
    	  <td colspan="6"><?= $str_perio ?></td>
   	  </tr>
    	<tr>
    	  <td width="1%"><div align="center" class="style2" style="vertical-align: middle;">Item</div></td>
    	  <td width="1%"><div align="center" class="style2" style="vertical-align: middle;">Identificação&nbsp;do&nbsp;Problema</div></td>
    	  <td width="7%"><div align="center" class="style2" style="vertical-align: middle;">Disciplina</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">Solicitado&nbsp;por</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">Responsável&nbsp;pela solução</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">Status</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">Data</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">Observação</div></td>
    	  <td><div align="center" class="style2" style="vertical-align: middle;">Ação&nbsp;Corretiva</div></td>
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
    	  <td colspan="9">&nbsp;</td>
   	  </tr>
        <tr>
          <td colspan="5"><div align="center" class="style2" style="vertical-align: middle;">Verificação&nbsp;do&nbsp;projeto</div></td>
          <td>&nbsp;</td>
          <td colspan="3"><div align="center" class="style2" style="vertical-align: middle;">Controle&nbsp;de&nbsp;alterações&nbsp;e&nbsp;revisões</div></td>
        </tr>
        <tr>
          <td colspan="5"><div class="style1">Conforme Análise e Verificação de Projeto, executada em todos os documentos que são finalizados para a entrega ao cliente ao longo do projetos, de acordo com o cronagrama consolidado</div></td>
          <td>&nbsp;</td>
          <td colspan="3"><div class="style1">As alterações são registradas no formulário Solicitação de Alteração e/o Serviço. As revisões são anotadas no próprio documento do projeto e registradas pelo Arquivo Técnico.</div></td>
        </tr>
        <tr>
          <td colspan="9">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="5"><div align="center" class="style2" style="vertical-align: middle;">Validação&nbsp;do&nbsp;projeto</div></td>
          <td>&nbsp;</td>
          <td colspan="3"><div align="center" class="style2" style="vertical-align: middle;">Validação&nbsp;pelo&nbsp;responsável&nbsp;técnico</div></td>
        </tr>
        <tr>
          <td colspan="5"><p class="style1">A&nbsp;validação&nbsp;do&nbsp;projeto&nbsp;é&nbsp;evidenciada&nbsp;por:</p>
            <p class="style1">1)&nbsp;Análise&nbsp;e&nbsp;verificação&nbsp;dos&nbsp;documentos&nbsp;do&nbsp;projeto&nbsp;emitidos&nbsp;para&nbsp;o&nbsp;cliente&nbsp;e,</p>
          <p class="style1">2)&nbsp;Análise&nbsp;final&nbsp;pelo&nbsp;responsável&nbsp;técnico</p></td>
          <td>&nbsp;</td>
          <td><div class="style2">Coordenador:<?= $reg_validacao["nome_validador"] ?></div></td>
          <td>&nbsp;</td>
          <td><div class="style2">data:<?= mysql_php($reg_validacao["data_validacao"]) ?></div></td>
        </tr>
        <tr>
          <td colspan="9">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="9"><div align="center" class="style2" style="vertical-align: middle;">Análise&nbsp;crítica&nbsp;final&nbsp;(Utilize&nbsp;o&nbsp;Procedimento&nbsp;de&nbsp;Execução&nbsp;de&nbsp;Projetos)</div></td>
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
          <td colspan="9">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="2"><div class="style2" style="vertical-align: middle;">COORDENADOR</div></td>
          <td>&nbsp;</td>
          <td colspan="6"><div class="style2" style="vertical-align: middle;">DATA</div></td>
        </tr>
    </table>
    
    
    
    
	
	<?php
}

?>
