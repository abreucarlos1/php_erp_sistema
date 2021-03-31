<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_os" id="frm_os" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="3" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle">
						<input name="btninserir" type="button" class="class_botao" id="btninserir" value="Inserir" onclick="xajax_insere(xajax.getFormValues('frm_os'));">
					</td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td width="11%"><label for="os" class="labels">OS</label><br />
						<input name="os" type="text" class="caixa" id="os" size="14" disabled="disabled">
						<input name="id_os" type="hidden" id="id_os" value="" /></td>
					<td width="89%"><label for="oscliente" class="labels">OS - Cliente</label><br /> 
						<input name="oscliente" type="text" class="caixa" id="oscliente" size="19"></td>
				</tr>
			</table>
		  <table width="100%" border="0">
				<tr>
					<td width="27%"><label for="descricao" class="labels">Descrição</label><br />
						<input name="descricao" type="text" class="caixa" id="descricao" size="100"></td>
				</tr>
			</table>
          	<table width="100%" border="0">
				<tr>
					<td width="37%"><label for="titulo_1" class="labels">Título 1*</label><br />
						<input name="titulo_1" type="text" class="caixa" id="titulo_1" placeholder="Título 1" size="50" /></td>
					<td width="63%"><label for="titulo_2" class="labels">Título 2</label><br />
						<input name="titulo_2" type="text" class="caixa" id="titulo_2" placeholder="Título 2" size="50" /></td>
				</tr>
			</table>
          	<table width="100%" border="0">
				<tr>
					<td width="6%"><label for="cliente" class="labels">Cliente</label><br />
						<select name="cliente" class="caixa" id="cliente" onchange="xajax_preencheCombo(this.options[this.selectedIndex].value, 'CONTATO','coordcli');" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_cliente_values output=$option_cliente_output</smarty>
						</select></td>
				</tr>
			</table>
          	<table width="100%" border="0">
				<tr>
					<td width="19%"><label for="coord" class="labels">Coordenador</label><br /> 
						<select name="coord" class="caixa" id="coord" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_coord_values output=$option_coord_output</smarty>
						</select></td>
					<td width="81%"><label for="coordaux" class="labels">Coordenador Aux./Supervisor</label><br /> 
						<select name="coordaux" class="caixa" id="coordaux" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_coordca_values output=$option_coordca_output</smarty>
						</select></td>
				</tr>
			</table>
          	<table width="100%" border="0">
				<tr>
					<td width="16%"><label for="coordcli" class="labels">Coordenador Cliente</label><br />
                    	<select name="coordcli" class="caixa" id="coordcli" onkeypress="return keySort(this);">
								<option value="0">SELECIONE</option>
						</select>
                     </td>
					<td width="8%"><label for="datainicio" class="labels">Data início</label><br />
						<input name="datainicio" type="text" class="caixa" id="datainicio" onKeyPress="return txtBoxFormat(document.frm_os, 'datainicio', '99/99/9999', event);" maxlength="10" size="12"></td>
					<td width="76%"><label for="datafim" class="labels">Data Final</label><br />
						<input name="datafim" type="text" class="caixa" id="datafim" onKeyPress="return txtBoxFormat(document.frm_os, 'datafim', '99/99/9999', event);" maxlength="10" size="12"></td>
				</tr>
			</table>          	
          	<table border="0" width="100%">
				<tr>
					<td width="37%"><label for="busca" class="labels">Buscar</label><br />
                    <input name="busca" type="text" class="caixa" id="busca" placeholder="Busca" onkeyup="iniciaBusca.verifica(this);" size="50"></td>
					<td width="63%"><label for="exibir" class="labels">Exibir:</label><br />
						<select name="exibir" class="caixa" id="exibir" onkeypress="return keySort(this);" onchange="xajax_atualizatabela('',this.value);">
						<smarty>html_options values=$option_status_values output=$option_status_output</smarty>
						</select>
					</td>
				</tr>
			</table></td>
        </tr>
      </table>
	  <div id="os_tabela" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>