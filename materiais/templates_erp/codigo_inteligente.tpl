<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>

<div style="width:100%;height:750px;">
<form name="frm" id="frm" method="POST" style="margin:0px; padding:0px;" target="_blank">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">        
        <tr>
          <td width="122" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td valign="middle"><input name="btninserir" id="btninserir" type="button" class="class_botao" value="Inserir" onclick="xajax_inserir(xajax.getFormValues('frm'));"/></td>
			</tr>
			<smarty>if !isset($ocultarCabecalhoRodape)</smarty>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
			<smarty>/if</smarty>
			<smarty>if isset($ocultarCabecalhoRodape)</smarty>
				<tr>
					<td valign="middle"><input name="btnselecionar" id="btnselecionar" onclick="window.parent.document.getElementById('codigoComponente').value = this.name;window.parent.preencheTela();window.parent.divPopupInst.destroi();" disabled="disabled" type="button" class="class_botao" value="Selecionar" /></td>
				</tr>
			<smarty>/if</smarty>
		  </table></td>
          <td width="6" rowspan="2" class="<smarty>$classe</smarty>"> </td>
        </tr>        
        <tr>
          <td colspan="2" valign="top">
		  <table cellspacing="10px" cellpadding="0">
			<tr>
				<td align="left"><label class="labels">GRUPOS</label>
					<br />
                    <select name="codigo_grupo" class="caixa" onchange="xajax_getSubGrupos(xajax.getFormValues('frm'));criaCodigoInteligente();" id="codigo_grupo" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_grupos_values output=$option_grupos_output</smarty>
					</select>
				</td>
				<td align="left"><label class="labels">SUBGRUPOS</label><br />
			    	<select class="caixa" name="id_sub_grupo" id="id_sub_grupo" onchange="xajax_getAtributos(xajax.getFormValues('frm'));xajax_getAtributos(xajax.getFormValues('frm'),'',0,0);criaCodigoInteligente();xajax_atualiza_tabela_principal(xajax.getFormValues('frm'));"><option value="">SELECIONE UM GRUPO</option></select>
				</td>				
				<!--td>
					<img src="../imagens/inserir.png" id="imgSelecionarFamilias" style="cursor:pointer" onclick="showModalFamilias()" title="Selecionar Familias" /><label class="labels">FAMILIA</label><br />
					<input type="text" class="caixa" readonly="readonly" size="60" id="txtDescricaoFamilia" name="txtDescricaoFamilia" />
					<input type="hidden" value="" id="idFamilia" name="idFamilia" />
				</td-->
			</tr>
		  </table></td>
        </tr>
      </table>
      <fieldset style="height: auto;">
      	<legend class="labels">Atributos</legend>
      	<div id="divItens"></div>
      </fieldset><br />
      
      <fieldset style="height: 90px;">
      	<legend class="labels">Composição do Código</legend>
      	  <input type="hidden" value="" id="codigoBarras" name="codigoBarras" />
	      <input type="hidden" value="" id="codigoInteligenteValue" name="codigoInteligenteValue" />
	      <input type="hidden" value="" id="descricaoCodigoValue" name="descricaoCodigoValue" />
	      <input type="hidden" value="" id="descricaoCodigoCompletoValue" name="descricaoCodigoCompletoValue" />
	      <input type="hidden" value="" id="unidadesPesos" name="unidadesPesos" />
	      <div style="float: left; width: 100%"><label class="labels" style='float:left;'><b>Código Inteligente:</b> <span id="codigoInteligente" style='font-size:10px;'></span></label></div><br />
	      <div style="float: left; width: 100%"><label class="labels" style='float:left;'><b>Família:</b> <span id="descricaoCodigo" style='font-size:10px;'></span></label></div>
	      <div style="float: left; width: 100%">
	      	<label class="labels" style='float:left;'><b>Descrição longa:</b></label>
	      	<textarea style="float:left;" id="descricaoLongaFamilia" name="descricaoLongaFamilia" cols="66" rows="1"></textarea>
	      </div>
	  </fieldset>
	  
	  <!--fieldset style="height: 100px; width:254px; float: right;">
      	<legend class="labels">Códigos agregados</legend>
      	<div id="listaCodigosAgregados"></div>
      </fieldset-->
</form>
<div id="codigos" style="margin-top: 10px;"></div>
<div id="gridPaginacao" style="float: left;"> </div>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>