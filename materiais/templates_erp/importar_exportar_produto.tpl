<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>

<script src="../includes/jquery/jquery.min.js"></script>
<div style="width:100%;height:660px;">
	<form name="frm" id="frm" method="POST" style="margin:0px; padding:0px;" action="./importar_exportar_produto.php?gerarPlanilha=1">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">        
	        <tr>
	          <td width="122" rowspan="2" valign="top" class="espacamento">
			  <table width="100%" cellpadding="0" cellspacing="0">
				<!--<tr>
					<td valign="middle"><input name="btnlistamateriais" id="btnlistamateriais" type="button" class="class_botao" value="Lista materiais" onclick="xajax_getListaMateriais(xajax.getFormValues('frm'));"/></td>
				</tr>-->
				<tr>
					<td valign="middle"><input name="btnlista" id="btnlista" type="submit" class="class_botao" value="Gerar Arquivo" /></td>
				</tr>
				<tr>
					<td valign="middle">
						<input name="btnImportar" onclick="abrirArquivoImportacao();" id="btnImportar" type="button" class="class_botao" value="Importar" />
					</td>
				</tr>
				<tr>
					<td valign="middle">
						<input name="btnImportarCurto" onclick="abrirArquivoImportacaoDescricaoCurta();" id="btnImportarCurto" type="button" class="class_botao" value="Imp. Desc. Cur." />
					</td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
			  </table></td>
	        </tr>        
	        <tr>
	          <td colspan="2" valign="top">
				  <table cellspacing="10px" cellpadding="0">
					<tr>
						<td align="left"><label class="labels">GRUPOS</label>
							<br />
		                    <select name="codigo_grupo" class="caixa" onchange="xajax_getSubGrupos(xajax.getFormValues('frm'));" id="codigo_grupo" onkeypress="return keySort(this);">
								<smarty>html_options values=$option_grupos_values output=$option_grupos_output</smarty>
							</select>
						</td>
						<td align="left"><label class="labels">SUBGRUPOS</label><br />
					    	<select class="caixa" name="id_sub_grupo" id="id_sub_grupo"><option value="">SELECIONE UM GRUPO</option></select>
						</td>
					</tr>
				  </table>
			  </td>
	        </tr>
	      </table>
		<div id="documentos" style="width:100%;margin-top: 15px;"></div>
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>