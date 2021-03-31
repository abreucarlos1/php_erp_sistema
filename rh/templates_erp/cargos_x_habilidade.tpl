<link href="../../classes/css_geral.css" rel="stylesheet" type="text/css" />
<smarty>include file="../../templates/header.tpl"</smarty>
<form name="frm_cargos" id="frm_cargos" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="fundo_cinza">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="middle" class="fundo_cinza" >
						<input name="btninserir" type="button" class="botao_chanfrado" id="btninserir" onclick="selectAllOptions(obrigatorios);selectAllOptions(desejaveis);selectAllOptions(habilidades1);selectAllOptions(valores);xajax_insere(xajax.getFormValues('frm_cargos'));" value="Inserir" /></td>
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
			<div id="a_tabbar" mode="top" class="dhtmlxTabBar" imgpath="../includes/dhtmlx/dhtmlxTabbar/codebase/imgs/" margin="5" style="width:100%; height:360px; margin-top:5px; margin-right:5px;" tabstyle="modern" skinColors="#F1F4F5,#F1F4F5">
			  <div id="a0" name="Função/Cargo" style="margin-left:3px">
			  <table border="0" width="95%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="18%" class="td_sp"><label class="label_descricao_campos">Função</label>
								<input name="funcao" type="text" class="caixa" id="funcao" size="80" />
								<input type="hidden" name="id_cargo" id="id_cargo" value="" /></td>
						<td width="15%" class="td_sp"><label class="label_descricao_campos">Cargo
						    <select name="cargo" class="caixa" id="cargo" onkeypress="return keySort(this);">
                              <smarty>html_options values=$option_cargo_values output=$option_cargo_output</smarty>
                            </select>
</label></td>
						<td width="67%" class="td_sp"> </td>
					</tr>
				</table>
			  <table border="0" width="95%" cellpadding="0" cellspacing="0">
                <tr>
                  <td width="12%" class="td_sp"><label class="label_descricao_campos">Escolaridade
                      <select name="escolaridade" class="caixa" id="escolaridade" onkeypress="return keySort(this);">
                        <smarty>html_options values=$option_escolaridade_values output=$option_escolaridade_output</smarty>
                      </select>
                  </label></td>
                  <td width="60%" class="td_sp"><label class="label_descricao_campos"></label>                    <label class="label_descricao_campos"></label>                    <label class="label_descricao_campos"></label>
                    <label class="label_descricao_campos">Formação</label>
                    <input name="formacao" type="text" class="caixa" id="formacao" size="100" /></td>
                  <td width="28%" class="td_sp"> </td>
                </tr>
              </table>
<table border="0" width="95%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="16%" class="td_sp"><label class="label_descricao_campos">Tempo na atividade</label>
							<input name="experiencia" type="text" class="caixa" id="experiencia" size="20" />						</td>
						<!-- <td width="10%" class="td_sp"><label class="label_descricao_campos">Categoria</label>
							<input name="categoria" type="text" class="caixa" id="categoria" maxlength="3" size="5" /></td> -->
						<td width="10%" class="td_sp"><label class="label_descricao_campos">CBO2002</label>
							<input name="cbo" type="text" class="caixa" id="cbo" maxlength="6" size="6" onkeypress="num_only()" /></td>
						<td width="64%" class="td_sp"> </td>
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
			  </div>
			  <div id="a1" name="Conhecimentos e Habilidades " style="margin-left:3px">
				<table border="0" width="24%" cellpadding="0" cellspacing="0"> 
				  <tr>
					<td width="25%" rowspan="2" class="td_sp"><label class="label_descricao_campos">Conhecimentos</label>
						<select class="caixa" name="conhecimentos" style="width:30em;" size="18" multiple="multiple" id="conhecimentos" ondblclick="xajax.$('c').onclick();">
						<smarty>html_options values=$option_conhecimentos_values output=$option_conhecimentos_output</smarty>
						</select></td>
					<td width="17%" align="center" valign="middle" rowspan="2" class="td_sp"><select class="caixa" name="status_conhecimento" id="status_conhecimento" onkeypress="return keySort(this);">
							<option value="0">DESEJÁVEL</option>
							<option value="1">OBRIGATÓRIO</option>
						</select>
						<input name="c" type="button" id="c" class="botao_cinza" value="&gt;&gt;" onclick="move_itens('status_conhecimento',document.getElementById('conhecimentos'),document.getElementById('desejaveis'),document.getElementById('obrigatorios'));"/></td>
					<td width="58%" class="td_sp"><label class="label_descricao_campos">Obrigat&oacute;rios</label>
						<select name="obrigatorios[]" class="caixa" size="5" style="width:30em;" id="obrigatorios" multiple="multiple" ondblclick="moveSelectedOptions(this,conhecimentos)">
						</select>                        </td>
				  </tr>
				  <tr>
					<td class="td_sp"><label class="label_descricao_campos">Desejáveis</label>
						<select class="caixa" name="desejaveis[]" size="5" style="width:30em;" id="desejaveis" multiple="multiple" ondblclick="moveSelectedOptions(this,conhecimentos)">
						</select>                        </td>
				  </tr>
				</table>
			  </div>
			  <div id="a2" name="Atitudes e Valores INT" style="margin-left:3px">
			  <table border="0" width="25%" cellpadding="0" cellspacing="0">
				  <tr>
					<td width="25%" rowspan="2" class="td_sp"><label class="label_descricao_campos">Habilidades/Valores</label>
						<select class="caixa" name="habil" style="width:30em;" size="18" multiple="multiple" id="habil" ondblclick="xajax.$('h').onclick();">
						<smarty>html_options values=$option_habilidades_values output=$option_habilidades_output</smarty>
						</select></td>
					<td width="16%" align="center" valign="middle" rowspan="2" class="td_sp"><select class="caixa" name="status_habilidade" id="status_habilidade" onkeypress="return keySort(this);">
						  <option value="0">LIDERANÇA</option>
						  <option value="1">VALORES</option>
						</select>
						<input name="btnescolha" id="h" type="button" class="botao_cinza" value="&gt;&gt;" onclick="move_itens('status_habilidade',document.getElementById('habil'),document.getElementById('habilidades1'),document.getElementById('valores'));"/></td>
					<td width="59%" class="td_sp"><label class="label_descricao_campos">Habilidades</label>
						<select name="habilidades1[]" class="caixa" size="5" style="width:30em;" id="habilidades1" multiple="multiple" ondblclick="moveSelectedOptions(this,habil)">
						</select>                        </td>
				  </tr>
				  <tr>
					<td class="td_sp"><label class="label_descricao_campos">Valores INT</label>
						<select class="caixa" name="valores[]" size="5" style="width:30em;" id="valores" multiple="multiple" ondblclick="moveSelectedOptions(this,habil)">
						</select>                        </td>
				  </tr>
				</table>
			  </div>
			</div>
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
	  <div id="cargos" style="width:100%;"> </div>
</form>
<smarty>include file="../../templates/footer.tpl"</smarty>