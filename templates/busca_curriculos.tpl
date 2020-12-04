<link href="classes/css_geral.css" rel="stylesheet" type="text/css" />
<smarty>include file="templates/header_busca.tpl"</smarty>
<form name="frm_curriculos" id="frm_curriculos" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="fundo_cinza">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="middle" class="fundo_cinza" ><input name="btnbuscar" id="btnbuscar" type="button" class="botao_chanfrado" value="Buscar" onclick="xajax_atualizatabela(xajax.getFormValues('frm_curriculos'));" /></td>
				</tr>
				<tr>
					<td valign="middle" class="fundo_cinza" ><input name="btnvoltar" id="btnvoltar" type="button" class="botao_chanfrado" value="Voltar" onclick="history.back();" /></td>
				</tr>
			</table></td>
          <td width="75" rowspan="2" >&nbsp;</td>
          <td colspan="2">&nbsp;</td>
          <td width="6" rowspan="2" class="<smarty>$classe</smarty>">&nbsp;</td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="borda_alto borda_esquerda">
		  <table border="0" width="100%">
				<tr>
					<td width="33%" class="td_sp"><label class="label_descricao_campos">Nome</label>
						<input name="nome" type="text" class="caixa" id="nome" size="50" /></td>
					<td width="7%" class="td_sp"><label class="label_descricao_campos">Cidade</label>
						<select name="cidade" class="caixa" id="cidade" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_cidades_values output=$option_cidades_output</smarty>
						</select>					</td>
					<td width="12%" class="td_sp"><label class="label_descricao_campos">Estado</label>
						<select name="estado" id="estado" class="caixa" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_estados_values output=$option_estados_output</smarty>
								</select></td>
					<td width="11%" class="td_sp"><label class="label_descricao_campos">Modalidade</label>
						<select name="modalidade" id="modalidade" class="caixa" onkeypress="return keySort(this);">
							<smarty>html_options values=$option_modalidade_values output=$option_modalidade_output</smarty>
						</select></td>
					<td width="37%" class="td_sp">&nbsp;</td>
				</tr>
			</table>
          	<table border="0" width="100%">
				<tr>
					<td width="9%" class="td_sp"><label class="label_descricao_campos">Função</label>
						<select name="funcao" class="caixa" id="funcao" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_funcao_values output=$option_funcao_output</smarty>
						</select></td>
					<td width="21%" class="td_sp"><label class="label_descricao_campos">Conhecimentos&nbsp;Espec&iacute;ficos</label> 
						<select name="conhecimentos" class="txt_box" id="conhecimentos" onkeypress="return keySort(this);">
								<option value="" selected="selected" >QUALQUER</option>
								<option value="AUTOCAD" >AUTOCAD</option>
								<option value="MICROSTATION" >MICROSTATION</option>
								<option value="PDS" >PDS</option>
								<option value="PDMS" >PDMS</option>
								<option value="NR10" >NR 10</option>
											</select></td>
					<td width="70%" class="td_sp"><label class="label_descricao_campos"></label></td>
				</tr>
			</table>
          	<table width="95%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td width="29%"><span class="td_sp">
						<label class="label_descricao_campos">Trabalhou&nbsp;na&nbsp;</label>
						<select name="trabalho" class="caixa" id="trabalho" onkeypress="return keySort(this);">
							<option value="" selected="selected" >QUALQUER</option>
							<option value="1" >N&Atilde;O</option>
							<option value="2" >SIM</option>
							<option value="3" >TRABALHOU&nbsp;(RECOMENDADO)</option>
							<option value="4" >TRABALHOU&nbsp;(N&Atilde;O RECOMENDADO)</option>
						</select>
					</span></td>
					<td width="17%"><span class="td_sp">
						<label class="label_descricao_campos">Atualizado</label>
						<select name="atualizado" class="caixa" id="atualizado" onkeypress="return keySort(this);">
							<option value="" selected="selected" >QUALQUER</option>
							<option value="1" >PELO SITE</option>
							<option value="2" >PELA </option>
						</select>
					</span></td>
					<td width="11%"><span class="td_sp">
						<label class="label_descricao_campos">A&nbsp;partir&nbsp;da&nbsp;data</label>
						<input name="data" type="text" class="caixa" id="data" size="12" maxlength="10"  onkeypress="transformaData(this, event);" />
					</span></td>
					<td width="43%">&nbsp;</td>
				</tr>
			</table>
          	<table border="0" width="100%" style="margin-bottom:25px">

				
				<tr>
					<td width="100%"><div class="fonte_descricao_campos" id="aguarde">&nbsp;</div><br />
								<div class="label_descricao_campos" id="registros"></div>								</td>
				</tr>
			</table></td>
        </tr>
      </table>
    <div id="curriculos" style="width:100%;">&nbsp;</div>
</form>
<smarty>include file="templates/footer.tpl"</smarty>