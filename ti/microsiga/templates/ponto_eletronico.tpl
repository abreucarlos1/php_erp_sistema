<link href="../../classes/css_geral.css" rel="stylesheet" type="text/css" />
<smarty>include file="../../templates/header.tpl"</smarty>
<form name="frm_ponto" id="frm_ponto" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="fundo_cinza">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="middle" class="fundo_cinza" >
						<input name="btninserir" type="button" class="botao_chanfrado" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm_ponto'));" value="Inserir" />					</td>
				</tr>
				<tr>
					<td valign="middle" class="fundo_cinza" ><input name="btnvoltar" id="btnvoltar" type="button" class="botao_chanfrado" value="Voltar" onclick="history.back();" /></td>
				</tr>
			</table></td>
          <td width="69" rowspan="2" > </td>
          <td colspan="2"> </td>
          <td width="6" rowspan="2" class="<smarty>$classe</smarty>"> </td>
        </tr>        
        <tr>
          <td colspan="2" valign="top">
			
			 
			  <table border="0" width="95%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="18%" class="td_sp"><label class="label_descricao_campos">Função</label>
								<input name="funcao" type="text" class="caixa" id="funcao" size="100" />
								<input type="hidden" name="id_cargo" id="id_cargo" value="" /></td>
						<td width="15%" class="td_sp"><label class="label_descricao_campos">Escolaridade</label>
							<select name="escolaridade" class="caixa" id="escolaridade" onkeypress="return keySort(this);">
							<smarty>
							html_options values=$option_escolaridade_values output=$option_escolaridade_output
							</smarty>
							</select></td>
						<td width="67%" class="td_sp"> </td>
					</tr>
				</table>
			  <table border="0" width="95%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="60%" class="td_sp"><label class="label_descricao_campos">Formação</label>
								<input name="formacao" type="text" class="caixa" id="formacao" size="100" /></td>
						<td width="12%" class="td_sp"><label class="label_descricao_campos">Tempo na atividade</label>
							<input name="experiencia" type="text" class="caixa" id="experiencia" size="20" />						</td>
						<td width="7%" class="td_sp"><label class="label_descricao_campos">Categoria</label>
							<input name="categoria" type="text" class="caixa" id="categoria" maxlength="3" size="5" /></td>
						<td width="21%" class="td_sp"><label class="label_descricao_campos">CBO2002</label>
							<input name="cbo" type="text" class="caixa" id="cbo" maxlength="6" size="6" /></td>
						<td width="21%" class="td_sp"> </td>
					</tr>
				</table>
			  <table border="0" width="95%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="30%" class="td_sp"><label class="label_descricao_campos">Principais
								Atvidades </label>
								<textarea class="caixa" name="atividades" cols="100" rows="7" id="atividades"></textarea>
						</td>
						<td width="70%" class="td_sp"> </td>
					</tr>
				</table>
			  
			
			<table border="0" width="95%" cellspacing="0" cellpadding="0">
							  
							  <tr>
								<td class="td_sp"><label class="label_descricao_campos">Busca</label>
									<input name="busca" type="text" class="caixa" id="busca" onKeyUp="iniciaBusca.verifica(this);" size="50"></td>
								<td width="43%" class="td_sp"> </td>
							  </tr>
							  <tr>
								<td colspan="2"><div id="aguarde" class="fonte_descricao_campos">Aguarde...buscando no banco de dados....</div></td>
							  </tr>
							</table>
		  </td>
        </tr>
      </table>
	  <div id="ponto" style="width:100%;"> </div>
</form>
<smarty>include file="../../templates/footer.tpl"</smarty>