<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_cargos" id="frm_cargos" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle">
						<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="selectAllOptions(obrigatorios);selectAllOptions(desejaveis);selectAllOptions(habilidades1);selectAllOptions(valores);xajax_insere(xajax.getFormValues('frm_cargos'));" value="Inserir" /></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top">
			<div id="a_tabbar" style="width:100%; height:350px; overflow:auto;">
			  <div id="a10" name="Função/Cargo" style="margin-left:3px">
			  <table border="0" width="95%">
					<tr>
						<td width="29%"><label for="funcao" class="labels">Função</label><br />
								<input name="funcao" type="text" class="caixa" id="funcao" size="50" >
								<input type="hidden" name="id_cargo" id="id_cargo" value="" ></td>
						<td width="71%"><label for="cargo" class="labels">Cargo</label><br />
						    <select name="cargo" class="caixa" id="cargo" onkeypress="return keySort(this);">
                              <smarty>html_options values=$option_cargo_values output=$option_cargo_output</smarty>
                            </select></td>
					</tr>
				</table>
			  <table border="0" width="95%">
                <tr>
                  <td width="8%"><label for="escolaridade" class="labels">Escolaridade</label><br />
                      <select name="escolaridade" class="caixa" id="escolaridade" onkeypress="return keySort(this);">
                        <smarty>html_options values=$option_escolaridade_values output=$option_escolaridade_output</smarty>
                      </select>
                  </td>
                  <td width="92%"><label for="formacao" class="labels">Formação</label><br />
                    <input name="formacao" type="text" class="caixa" id="formacao" size="70" placeholder="Formação" ></td>
                </tr>
              </table>
				<table border="0" width="95%">
					<tr valign="top">
						<td width="12%"><label for="experiencia" class="labels">Tempo na atividade</label><br />
							<input name="experiencia" type="text" class="caixa" id="experiencia" size="20" placeholder="Experiência" >						</td>
						<td width="7%"><label for="cbo" class="labels">CBO2002</label><br />
							<input name="cbo" type="text" class="caixa" id="cbo" maxlength="8" size="8" placeholder="CBO">
						</td>
						<td width="18%" ><label for="diretoria" class="labels">Diretoria</label><br />
							<input name="diretoria" type="text" class="caixa" id="diretoria" maxlength="30" size="30" placeholder="Diretoria" >
						</td>
						<td width="63%"><label for="setores" class="labels">Setores</label> (<font size='1'><i>Utilize o CTRL para mais de um setor</i></font>)<br />
						    <select name="setores[]" class="caixa" id="setores" multiple="multiple" onkeypress="return keySort(this);">
                              <smarty>html_options values=$option_setores_values output=$option_setores_output</smarty>
							</select>
						</td>
					</tr>
				</table>
		  		<table border="0" width="95%">
		  			<tr>
						<td width="30%"><label for="missao" class="labels">Missão do cargo </label><br />
								<textarea class="caixa" name="missao" cols="80" rows="2" id="missao" placeholder="Missão"></textarea>
						</td>
					</tr>
					<tr>
						<td width="30%"><label for="atividades" class="labels">Principais Atvidades </label><br />
								<textarea class="caixa" name="atividades" cols="80" rows="5" id="atividades" placeholder="Atividades"></textarea>
						</td>
					</tr>
				</table>
			  </div>
			  <div id="a20" name="Conhecimentos e Habilidades" style="margin-left:3px">
				<table border="0" width="24%" cellpadding="0" cellspacing="0"> 
				  <tr>
					<td width="25%" rowspan="2"><label for="conhecimentos" class="labels">Conhecimentos</label><br />
						<select class="caixa" name="conhecimentos" style="width:30em;" size="18" multiple="multiple" id="conhecimentos" ondblclick="xajax.$('c').onclick();">
						<smarty>html_options values=$option_conhecimentos_values output=$option_conhecimentos_output</smarty>
						</select></td>
					<td width="17%" align="center" valign="middle" rowspan="2"><select class="caixa" name="status_conhecimento" id="status_conhecimento" onkeypress="return keySort(this);">
							<option value="desejaveis">DESEJÁVEL</option>
							<option value="obrigatorios">OBRIGATÓRIO</option>
						</select>
						<input name="c" type="button" id="c" class="class_botao" value="&gt;&gt;" onclick="move_itens('status_conhecimento','conhecimentos');"/></td>
					<td width="58%"><label for="obrigatorios" class="labels">Obrigat&oacute;rios</label><br />
						<select name="obrigatorios[]" class="caixa" size="5" style="width:30em;" id="obrigatorios" multiple="multiple" ondblclick="moveSelectedOptions(this,conhecimentos)">
						</select></td>
				  </tr>
				  <tr>
					<td><label for="desejaveis" class="labels">Desejáveis</label><br />
						<select class="caixa" name="desejaveis[]" size="5" style="width:30em;" id="desejaveis" multiple="multiple" ondblclick="moveSelectedOptions(this,conhecimentos)">
						</select>                        </td>
				  </tr>
				</table>
			  </div>
			  <div id="a30" name="Atitudes e Valores INT" style="margin-left:3px; display:none;">
				  <table border="0" width="25%">
					  <tr>
						<td width="25%" rowspan="2"><label for="habil" class="labels">Habilidades/Valores</label>
							<select class="caixa" name="habil[]" style="width:30em;" size="18" multiple="multiple" id="habil" ondblclick="xajax.$('h').onclick();">
							<smarty>html_options values=$option_habilidades_values output=$option_habilidades_output</smarty>
							</select></td>
						<td width="16%" align="center" valign="middle" rowspan="2">
                        	<select class="caixa" name="status_habilidade" id="status_habilidade" onkeypress="return keySort(this);">
							  <option value="habilidades1">HABILIDADES</option>
							  <option value="valores">VALORES</option>
							</select>
							<input name="btnescolha" id="h" type="button" class="class_botao" value="&gt;&gt;" onclick="move_itens('status_habilidade','habil');"/></td>
						<td width="59%"><label for="habilidade1" class="labels">Habilidades</label><br />
							<select name="habilidades1[]" class="caixa" size="5" style="width:30em;" id="habilidades1" multiple="multiple" ondblclick="moveSelectedOptions(this,habil)">
							</select>
                            </td>
					  </tr>
					  <tr>
						<td><label for="valores" class="labels">Valores INT</label><br />
							<select class="caixa" name="valores[]" size="5" style="width:30em;" id="valores" multiple="multiple" ondblclick="moveSelectedOptions(this,habil)">
							</select>
                        </td>
					  </tr>
					</table>
			  </div>			  
			  <div id="a40" name="Competencias" style="margin-left:3px">
			  	<table border="0">
			  		<tr>
			  			<td><label for="competencias_tecnicas" class="labels">Competências técnicas ou de processos</label><br />
							<textarea class="caixa" name="competencias_tecnicas" cols="80" rows="5" id="competencias_tecnicas"></textarea>
						</td>
			  		</tr>
			  		<tr>
			  			<td><label for="competencias_individuais" class="labels">Competências individuais</label><br />
							<textarea class="caixa" name="competencias_individuais" cols="80" rows="5" id="competencias_individuais"></textarea>
						</td>
			  		</tr>
			  	</table>
			  </div>
			
			<table border="0" width="100%">
			  <tr>
				<td><label for="busca" class="labels">Busca</label><br />
					<input name="busca" type="text" class="caixa" placeholder="Busca" id="busca" onKeyUp="iniciaBusca.verifica(this);" size="50"></td>
			  </tr>
			</table>
            </div>
		  </td>
        </tr>
      </table>
	  <div id="cargos" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>