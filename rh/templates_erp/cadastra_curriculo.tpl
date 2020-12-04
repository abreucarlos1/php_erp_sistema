<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="" value="Inserir" disabled="disabled" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
					</tr>
       			</table>
		  </td>
        	<td colspan="2" valign="top" class="espacamento">
					<table width="100%" border="0">
                        <tr>
                            <td>
                                <label for="name" class="labels">Nome *</label><br />
                                <input name="nome" id="nome" class="caixa" size="40" placeholder="Nome" type="text">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="email" class="labels">E-Mail *</label><br />
                                <input name="email" id="email" class="caixa" size="40" placeholder="E-mail" type="text">
                            </td>
                        </tr>									
                        <tr>
                            <td>
                                <label for="cpf" class="labels">CPF *</label><br />
                                <input name="cpf" id="cpf" class="caixa" size="40" placeholder="CPF" type="text">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="estado" class='labels'>Estado *</label><br />
                                <select name="estado" class="caixa" id="estado" onkeypress="return keySort(this);" onchange="xajax_preencheCombo(this.value, 'cidade')">
                                    <smarty>html_options values=$option_estados_values output=$option_estados_output</smarty>
                                </select>                                            
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="cidade" class='labels'>Cidade *</label><br />
                                <select name="cidade" class="caixa" id="cidade" onkeypress="return keySort(this);">
                                    <option value="">Selecione um estado para carregar as cidades</option>
                                </select>                                            
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="telefone" class="labels">Telefone *</label><br />
                                <input name="telefone" id="telefone" class="caixa" size="15" placeholder="Telefone" type="text">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="celular" class="labels">Celular</label><br />
                                <input name="celular" id="celular" class="caixa" size="15" placeholder="Celular" type="text">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table>
                                    <tr>
                                        <td>
                                            <label for="trabalhou" class="labels" style='width:100%'>Já&nbsp;trabalhou&nbsp;na&nbsp;Devemada?</label><br />
                                             <select name="trabalhou" id="trabalhou" class="caixa" onkeypress="return keySort(this);">
                                                <option value="sim">Sim</option>
                                                <option value="não" selected="selected">Não</option>
                                            </select>	
                                        </td>
                                        <td>
                                            <label for="entrevistado" class="labels" style='width:100%'>Entrevistado?</label><br />
                                            <select name="entrevistado" id="entrevistado" class="caixa" onkeypress="return keySort(this);">
                                                <option value="sim">Sim</option>
                                                <option value="não" selected="selected">Não</option>
                                            </select>	
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="setor" class="labels">Setor</label><br />
                                <select name="setor" class="caixa" id="setor" onkeypress="return keySort(this);">
                                    <smarty>html_options values=$option_setor_values output=$option_setor_output</smarty>
                                </select>
                                
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="funcao" class="labels">Função</label><br />                                
                                <select name="funcao" class="caixa" id="funcao" onkeypress="return keySort(this);">
                                    <smarty>html_options values=$option_cargo_values output=$option_cargo_output</smarty>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <label class="labels" style='width:80%'>Deseja receber notificações de vagas</label>
                                <input name="notificacoes" id="notificacoes" value="1" type="checkbox">
                            </td>
                        </tr>
                    </table>
                        <table width="100%">
                            <tr>
                                <td>
                                    <label class="labels">Autocad</label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                               		<input name="autocad" value="Avançado" type="radio">
                                    <label class="labels">Avançado</label>
                                    
                                    <input name="autocad" value="Intermediário" type="radio"> 
                                    <label class="labels">Intermediário</label>
                                    
                                    <input name="autocad" value="Básico" type="radio">
                                    <label class="labels">Básico</label>                                    
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="labels">PDMS</label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                	<input name="pdms" value="Avançado" type="radio">	
                                    <label class="labels">Avançado</label>
                                    
                                    <input name="pdms" value="Intermediário" type="radio">
                                    <label class="labels">Intermediário</label>
                                    
                                    <input name="pdms" value="Básico" type="radio">
                                    <label class="labels">Básico</label>
                                    
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="labels">Anexe o seu currículo</label><br />
                                    <input name="arquivo" type="file" class="caixa">
                                </td>
                            </tr>
					</table>
           </td>
        </tr>
      </table>

</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>