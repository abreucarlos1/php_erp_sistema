<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
    <form name="frm" id="frm" method="POST">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">                
            <tr>
                <td width="116" valign="top" class="espacamento">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td valign="middle">
                                <input name="btnbuscar" type="button" class="class_botao" id="btnbuscar" onclick="xajax_buscar(xajax.getFormValues('frm'));" value="Realizar Busca" />
                            </td>
                        </tr>
                        <tr>
                            <td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
                        </tr>
                    </table>
                    <input name="chave" type="hidden" id="chave" value="" />
                </td>
                <td colspan="2" valign="top">
                    <table border="0" width="95%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="80px" class="td_sp"><label class="labels">Banco</label><br />
                                <select name="bancos" class="caixa" id="bancos" onkeypress="return keySort(this);" onchange="xajax_getTabelasBanco(this.value);">
                                    <smarty>html_options values=$option_bancos_values output=$option_bancos_output</smarty>
                                </select>
                            </td>
                            <td class="td_sp">
                            	<div id='tdTabelas' style='display:none;'><label class="labels">Tabelas</label><br />
                                <select name="tabelas" class="caixa" id="tabelas" onkeypress="return keySort(this);" onchange="xajax_getCamposTabela(document.getElementById('bancos').value, this.value);"></select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="td_sp" ><div id='tdCampos' style='display:none;'><label class="labels" style="vertical-align:top;">Campos</label><br />
                                <select name="campos[]" class="caixa" id="campos" multiple="multiple" style="height:200px;" onkeypress="return keySort(this);"></select>
                                </div>
                            </td>
                            <td class="td_sp" valign="top">
                            	<table width="100%" id="tableClausulas" style="display:none;">
			                        <tr>
			                            <td>
			                                <label class="labels">Cláusulas</label>
			                            </td>
			                        </tr>
			                        <tr>
			                            <td>
			                                <textarea cols="80" rows="9" id="clausulas" name="clausulas"></textarea>
			                            </td>
			                        </tr>
			                    </table>
                            </td>
                        </tr>
                        <tr>
	                        <td width="50%" class="td_sp" colspan="2">
								<label class="labels">Selecione um Usuario (Apenas para consulta)</label><br />
								<select id='login' name='login' class="caixa" onchange="xajax_carrega_senha(this.value);">
									<option>Escolha um usuario...</option>
									<smarty>while $usu = mysqli_fetch_assoc($usuarios)</smarty>
										<option value="<smarty>$usu['login']</smarty>"><smarty>$usu['funcionario']</smarty> - <smarty>sprintf('%05d', $usu['CodFuncionario'])</smarty></option>
									<smarty>/while</smarty>
								</select>
							</td>
						</tr>
                    </table>
                </td>
            </tr>
        </table>
    </form>
    <div align="left"><sub><i>* Para realizar alteracoes, altere o valor do campo e clique fora do formulario.</i></sub></div>
    <div id="div_lista" style="width:100%;padding:2px;"> </div>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>