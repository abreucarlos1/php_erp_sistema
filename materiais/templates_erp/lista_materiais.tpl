<smarty>include file="../../templates_erp/header.tpl"</smarty>
<div style="width:100%;height:660px;">
	<form name="frm" id="frm" method="POST" style="margin:0px; padding:0px;" target="_blank">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">        
	        <tr>
	          <td width="122" rowspan="2" valign="top" class="espacamento">
			  <table width="100%" cellpadding="0" cellspacing="0">
				<!--<tr>
					<td valign="middle"><input name="btnlistamateriais" id="btnlistamateriais" type="button" class="class_botao" value="Lista materiais" onclick="xajax_getListaMateriais(xajax.getFormValues('frm'));"/></td>
				</tr>-->
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
				<tr>
					<td valign="middle"><input style='display:none;' name="btnlista" id="btnlista" type="button" class="class_botao" value="Lista OS" onclick="xajax_getListaMateriais(xajax.getFormValues('frm'))" /></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnlistaRelatorio" id="btnlistaRelatorio" type="button" class="class_botao" value="Relatórios" onclick="abrirRelatorio();" /></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnImportarLista" id="btnImportarLista" type="button" class="class_botao" value="Importar PDMS" onclick="importarListaPdmsForm();" /></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnExcluirListas" id="btnExcluirListas" type="button" class="class_botao" disabled="disabled" value="Excluir" onclick="excluirListasSelecionadas();" /></td>
				</tr>
			  </table></td>
	          <td width="6" rowspan="2" class="<smarty>$classe</smarty>"> </td>
	        </tr>        
	        <tr>
	          <td colspan="2" valign="top">
			  <table cellspacing="10px" cellpadding="0">
				<tr>
					<td class="td_sp" colspan="3">
						<label class="labels">OS</label><br />
						<select name="id_os" class="caixa" id="id_os" onChange="disciplina.focus();" onBlur="if(this.value!=''){document.getElementById('disciplina').value='';xajax_getDisciplinas(xajax.getFormValues('frm'));xajax_getSpecs(xajax.getFormValues('frm'));}" onkeypress="return keySort(this);" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_values output=$option_output</smarty>
		            </select>
					</td>
				</tr>
				<tr>
					<td width="16%">
						<label class="labels">Disciplina</label><br />
						<select name="disciplina" class="caixa" id="disciplina" onChange="if(verificar()){xajax_atualizatabela(xajax.getFormValues('frm'))}" onkeypress="return keySort(this);" >
							<option value="">ESCOLHA A TAREFA</option>
						</select>
					</td>
					<td>
						<label class="labels">Especs</label><br />
						<select name="specs" class="caixa" id="specs" onkeypress="return keySort(this);" >
							<option value="">TODAS</option>
						</select>
					</td>
					<td> </td>
				</tr>
				<tr>
		        	<td colspan="3">
		        		<label class="labels">Busca</label><br />
						<input name="busca" type="text" class="caixa" id="busca" onkeyup="iniciaBuscaPrincipal.verifica(this);" size="50" />
					</td>
		        </tr>
			  </table>
			  </td>
	        </tr>
	        <tr id="trListasSelecionadas" style="display:none;">
	        	<td colspan="4" align="right">
		        	<label class="labels"><i>Gerar Excel das listas selecionadas abaixo </i></label>
		    		<!--input type='text' id='idListasSelecionadas' name='idListasSelecionadas' -->
				    <span class='icone icone-arquivo-xls cursor' style='float: right !important' onclick="manterListaSelecionados();"></span>
		    	</td>
	        </tr>
	      </table>

        

		<input type='hidden' id='idListaOsPrincipal' name='idListaOsPrincipal' />
		<div id="documentos" style="width:100%;margin-top: 15px;"></div>
		<table width="100%" id="tableLinkListaOs" style="display:none;">
			<tr>
				<td><label class='labels'><a href="javascript:void(0);" onclick="xajax_getListaMateriais(xajax.getFormValues('frm'))">Lista de Materiais da OS</a></label></td>
			</tr>		
		</table>
	</form>
</div>
<smarty>include file="../../templates_erp/footer.tpl"</smarty>