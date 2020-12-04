<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
	<form name="frm" id="frm" action="./upload_arquivo_morto.php" enctype="multipart/form-data" target="uploadArquivo" method="POST" style="margin: 0px; padding: 0px;">
		<table width="100%" border="0">
			<tr>
				<td width="116" rowspan="2" valign="top" class="espacamento">
					<table width="100%">
						<tr>
							<td valign="middle">
								<input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" />
								<input type="hidden" id="selecionados" name="selecionados" />
							</td>
						</tr>
						<smarty>if $acessoTotal</smarty>
						<tr>
							<td valign="middle">
								<input name="btnrelatorio" id="btnrelatorio" type="button" class="class_botao" value="Relatório" onclick="window.open('./relatorios/rel_arquivo_morto_pdf.php?idFuncionario='+document.getElementById('funcionario').value);" />
							</td>
						</tr>
						<smarty>/if</smarty>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="3" valign="top" class="espacamento">
					<div id="my_tabbar" style="height:650px;z-index:0;"></div>
					<div id="a10">
						<div id="dv_coordenadores" style="padding:10px; <smarty>if !$acessoTotal</smarty>display:none;<smarty>/if</smarty>">
							<table>
								<tr>
									<td>
										<label for="funcionario" class="labels">COORDENADOR *</label><br />
					                    <select name="funcionario" class="caixa" id="funcionario" onchange="if(this.value>0){showLoader();xajax_atualizatabela_os(this.value);}" onkeypress="return keySort(this);" style="width:350px;">
											<smarty>html_options values=$option_func_values output=$option_func_output</smarty>
										</select>
				                    </td>
				                    <td valign="middle">
				                    	<input name="btnenviar" disabled="disabled" type="button" class="class_botao" id="btnenviar" onclick="xajax_insere(xajax.getFormValues('frm'));" value="Enviar" />
				                    </td>
								</tr>
							</table>
						</div>						
						<div id="div_lista_os" style="width: 100%;">&nbsp;</div><br />
						<!-- smarty>if !$acessoTotal</smarty-->
							<input name="btnaprovar" disabled="disabled" type="button" class="class_botao" id="btnaprovar" onclick="xajax_aprovar(xajax.getFormValues('frm'));" value="Aprovar" />
						<!-- smarty>/if</smarty-->
					</div>
					<div id="a20" style="padding: 10px;">
						<table>
							<tr>
								<td>
									<input type="button" class="class_botao" id="btnLiberar" value="Liberar" onclick="xajax_liberarBloquear(1);" /><br />
									<input type="button" class="class_botao" id="btnBloquear" value="Bloquear" onclick="xajax_liberarBloquear(2, xajax.getFormValues('frm'));" disabled="disabled" />
								</td>
								<td>
									<span class="icone icone-cadeado-aberto" style="vertical-align: middle;display:none;" id="iconeCadeadoAberto"></span>
									<span class="icone icone-cadeado-fechado" style="vertical-align: middle;display:block" id="iconeCadeadoFechado"></span>
								</td>
								<td>
									<input type="button" class="class_botao" id="btnArquivoMorto" onclick=window.open('../includes/documento.php?documento=<smarty>$pasta_ged</smarty>arquivo_morto.zip'); value="ARQUIVO MORTO OFICIAL COMPLETO" disabled="disabled" style="width:auto; height:50px;" />
								</td>
								<td>
									<input type="file" id="fileArquivoMorto" name="fileArquivoMorto" class="caixa" style="display:none;" />
								</td>
							</tr>
						</table>
						
						<fieldset>
							<legend class='labels'>Versões</legend>
							<div id="div_lista_versoes"></div>
						</fieldset>
					</div>
					<div id="a30" style="padding: 10px;">
						<table>
							<tr>
								<td>
									<input type="button" class="class_botao" id="btnLiberarDescarte" value="Liberar" onclick="xajax_liberarBloquearDescarte(1);" /><br />
									<input type="button" class="class_botao" id="btnBloquearDescarte" value="Bloquear" onclick="xajax_liberarBloquearDescarte(2, xajax.getFormValues('frm'));" disabled="disabled" />
								</td>
								<td>
									<span class="icone icone-cadeado-aberto" style="vertical-align: middle;display:none;" id="iconeCadeadoAbertoDescarte"></span>
									<span class="icone icone-cadeado-fechado" style="vertical-align: middle;display:block" id="iconeCadeadoFechadoDescarte"></span>
								</td>
								<td>
									<input type="button" class="class_botao" id="btnArquivoDescarte" onclick=window.open('../includes/documento.php?documento=<smarty>$pasta_ged</smarty>/ARQUIVO_MORTO_DESCARTE.XLSX'); value="MODELO LISTA DE DESCARTE" disabled="disabled" style="width:auto; height:50px;" />
								</td>
								<td>
									<input type="file" id="fileArquivoDescarte" name="fileArquivoDescarte" class="caixa" style="display:none;" />
								</td>
								<td>
									<label for="anoReferencia" class="labels">Ano Referência *</label><br />
				                    <select name="anoReferencia" class="caixa" id="anoReferencia" onkeypress="return keySort(this);" style="width:100px;">
										<option value="">SELECIONE...</option>
										<smarty>html_options values=$anoReferencia output=$anoReferencia</smarty>
									</select>
			                    </td>
							</tr>
						</table>
						
						<fieldset>
							<legend class='labels'>Descartes</legend>
							<div id="div_lista_descartes"></div>
						</fieldset>
					</div>
					<iframe id="uploadArquivo" name="uploadArquivo" style="width:400px;height:100px;display:none;"></iframe>
				</td>
			</tr>
		</table>
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>