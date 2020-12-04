<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">                
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" cellpadding="0" cellspacing="0">
        			<tr>
        				<td valign="middle"><input name="btninserir"  tabindex="4" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm'))" value="<smarty>$botao[1]</smarty>" /></td>
					</tr>
                    
        			<tr>
        				<td valign="middle"><input name="btnimpostos" tabindex="10" type="button" class="class_botao" id="btnimpostos" onclick="xajax_impostos(xajax.getFormValues('frm'));" value="<smarty>$botao[15]</smarty>" /></td>
					</tr>
                    
        			<tr>
        				<td valign="middle"><input name="btnlibfechamento" tabindex="11" type="button" class="class_botao" id="btnlibfechamento" onclick="abrejanela('listafechamentos','listafechamentos.php',250,400);" value="<smarty>$botao[16]</smarty>" /></td>
					</tr>
                    
        			<tr>
        				<td valign="middle"><input name="btnlibanexos" tabindex="12" type="button" class="class_botao" id="btnlibanexos" onclick="abrejanela('permite_anexos','libera_anexos.php',250,400);" value="<smarty>$botao[17]</smarty>" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnrelatorios" tabindex="14" type="button" class="class_botao" id="btnrelatorios" onclick="location.href='menu_relatorios_fechamento.php'"  value="<smarty>$botao[18]</smarty>" /></td>
					</tr>                  
        			<tr>
        				<td valign="middle"><input name="btnvoltar" tabindex="15" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
					<td width="10%"><label for="funcionario" class="labels"><smarty>$campo[2]</smarty></label><br />
					  <select name="funcionario" tabindex="1" class="caixa" id="funcionario" onkeypress="return keySort(this);" onchange="xajax_tipo_contrato(xajax.getFormValues('frm'));">
					    <smarty>html_options values=$option_funcionario_values output=$option_funcionario_output</smarty>
				      </select>
						<input name="id_fechamento" type="hidden" id="id_fechamento" value="" /></td>
					<td width="10%"><label for="dataini" class="labels"><smarty>$campo[3]</smarty></label><br />
		       			<input name="dataini" tabindex="2" type="text" class="caixa" id="dataini" onkeypress="transformaData(this, event);" value="<smarty>$data_inicial</smarty>" onblur="return checaTamanhoData(this,10);" size="10" maxlength="10" /></td>
					<td width="10%"><label for="datafin" class="labels"><smarty>$campo[4]</smarty></label><br />
		        		<input name="datafin" tabindex="3" type="text" class="caixa" id="datafin" onkeypress="transformaData(this, event);" value="<smarty>$data_final</smarty>" onblur="return checaTamanhoData(this,10);" size="10" maxlength="10" /></td>
					<td width="70%"><label class="labels"><smarty>$campo[5]</smarty></label><br />
						<input name="horasextras" tabindex="15" type="radio" value="1" id="horasextras1" onclick="abreHE(frm.funcionario.value,frm.dataini.value,frm.datafin.value);">
                        <label class="labels">Sim</label><br />
                          <input name="horasextras" tabindex="16" type="radio" value="0"  id="horasextras0" checked>
                          <label class="labels">Não</label>
                    </td>
					<td width="70%"><label class="labels">Proporcional&nbsp;aos&nbsp;dias&nbsp;(MENS)</label><br />
						<input name="proporcional" type="radio" value="1" id="proporcional1">
                        <label class="labels">Sim</label><br />
                          <input name="proporcional" type="radio" value="0"  id="proporcional0" checked>
                          <label class="labels">Não</label>
                    </td>
				</tr>
			</table>
            <table border="0" width="100%">
                <tr valign="top">
                    <td width="10%"><label class="labels"><smarty>$campo[6]</smarty></label><br />
                    <input name="chkmanual" tabindex="17" type="checkbox" id="chkmanual" value="1" onclick="fn_manual(this);">
                    </td>
                    <td width="10%">
                    	<span id="label_manual" style="display:none;"><label class="labels">Valor</label><br /><input name="manual" type="text" class="caixa" id="manual" size="10" maxlength="9" onkeydown="FormataValor(frm.manual, 9, event)" disabled></span>
                    </td>
                    <td width="80%">
                    <span id="label_descricao" style="display:none;"><label for="descricao_manual" class="labels">DESCRIÇÃO</label></span><br />
                    <span id="text_descricao" style="display:none;"><textarea name="descricao_manual" id="descricao_manual" class="caixa" placeholder="Descrição" cols="40" rows="4"></textarea></span>
                    </td>
                    
                </tr>
            </table>           
          <div id="campos_clt" style="visibility:collapse; display:none;">  
		  <table border="0" width="100%">
		    <tr valign="top">
		      <td width="10%"><label for="ferias" class="labels"><smarty>$campo[7]</smarty></label><br />
              	<input name="ferias" tabindex="18" type="text" class="caixa" id="ferias" placeholder="Férias" size="10" maxlength="10" onkeydown="FormataValor(frm.ferias, 10, event)"></td>
		      <td width="10%"><label for="rescisao" class="labels"><smarty>$campo[8]</smarty></label><br />
                <input name="rescisao" tabindex="19" type="text" class="caixa" id="rescisao" placeholder="Rescisão" size="10" maxlength="10" onkeydown="FormataValor(frm.rescisao, 10, event)"></td>
		      <td width="10%"><label for="fgts" class="labels"><smarty>$campo[9]</smarty></label><br />
                <input name="fgts" type="text" tabindex="20" class="caixa" id="fgts" placeholder="FGTS" size="10" maxlength="10" onkeydown="FormataValor(frm.fgts, 10, event)"></td>
		      <td width="13%"><label for="decimoterceiro" class="labels"><smarty>$campo[10]</smarty></label><br />
                <input name="decimoterceiro" tabindex="21" type="text" class="caixa" id="decimoterceiro" placeholder="13º" size="10" maxlength="10" onkeydown="FormataValor(frm.decimoterceiro, 10, event)"></td>     
		      <td width="11%"><label for="salarioproporcional" class="labels"><smarty>$campo[11]</smarty></label><br />
                <input name="salarioproporcional" tabindex="22" type="text" class="caixa" id="salarioproporcional" placeholder="Sal. Prop." size="10" maxlength="10" onkeydown="FormataValor(frm.salarioproporcional, 10, event)"></td> 
		      <td width="12%"><label for="diferenca_clt_ferias" class="labels"><smarty>$campo[12]</smarty></label><br />
                <input name="diferenca_clt_ferias" tabindex="23" type="text" class="caixa" id="diferenca_clt_ferias" placeholder="Difer. Férias" size="10" maxlength="10" onkeydown="FormataValor(frm.diferenca_clt_ferias, 10, event)"><img style="cursor:pointer; vertical-align:middle;" src="<smarty>$lupa</smarty>" alt="Detalhes" width="16" height="16" border="0" onclick="openpage('detalhes', 'fechamentofolha_outros.php?tipo=diferenca_clt_ferias&codfuncionario='+frm.funcionario.value+'&dataini='+frm.dataini.value+'&datafin='+frm.datafin.value+'',700,400)">
                </td>
		      <td width="34%"><label for="diferenca_clt_rescisao" class="labels"><smarty>$campo[13]</smarty></label><br />
                <input name="diferenca_clt_rescisao" tabindex="24" type="text" class="caixa" id="diferenca_clt_rescisao" placeholder="Difer. Resc." size="10" maxlength="10" onkeydown="FormataValor(frm.diferenca_clt_rescisao, 10, event)"><img style="cursor:pointer; vertical-align:middle;" src="<smarty>$lupa</smarty>" alt="Detalhes" width="16" height="16" border="0" onclick="openpage('detalhes', 'fechamentofolha_outros.php?tipo=diferenca_clt_rescisao&codfuncionario='+frm.funcionario.value+'&dataini='+frm.dataini.value+'&datafin='+frm.datafin.value+'',700,400)"></td>
	        </tr>
		    </table>
            </div>            
		  <table border="0" width="100%">
		    <tr>
		      <td width="11%"><label for="outros_descontos" class="labels"><smarty>$campo[14]</smarty></label><br />
              	<input name="outros_descontos" tabindex="25" type="text" class="caixa" placeholder="Outros desc." id="outros_descontos" size="10" maxlength="10" onkeydown="FormataValor(frm.outros_descontos, 10, event)"><img style="cursor:pointer; vertical-align:middle;" src="<smarty>$lupa</smarty>" alt="Detalhes" width="16" height="16" border="0" onclick="openpage('detalhes', 'fechamentofolha_outros.php?tipo=outros_descontos&codfuncionario='+frm.funcionario.value+'&dataini='+frm.dataini.value+'&datafin='+frm.datafin.value+'',700,400)"></td>
	        
		      <td width="89%"><label for="outros_acrescimos" class="labels"><smarty>$campo[15]</smarty></label><br />
              	<input name="outros_acrescimos" tabindex="26" type="text" class="caixa" placeholder="Outros acres." id="outros_acrescimos" size="10" maxlength="10" onkeydown="FormataValor(frm.outros_acrescimos, 10, event)"><img style="cursor:pointer; vertical-align:middle;" src="<smarty>$lupa</smarty>" alt="Detalhes" width="16" height="16" border="0" onclick="openpage('detalhes', 'fechamentofolha_outros.php?tipo=outros_acrescimos&codfuncionario='+frm.funcionario.value+'&dataini='+frm.dataini.value+'&datafin='+frm.datafin.value+'',700,400)"></td>
	           </tr>
		    </table>            
  			<table border="0" width="100%">			  
			  <tr>
				<td><label for="periodo" class="labels"><smarty>$campo[16]</smarty></label><br />
                  <select name="periodo" tabindex="27" class="caixa" id="periodo" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));">
					    <smarty>html_options values=$option_periodo_values output=$option_periodo_output</smarty>
				      </select></td>
				</tr>
			</table></td>
        </tr>
      </table>
	  <div id="div_grid" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>