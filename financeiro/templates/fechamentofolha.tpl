<link href="../../classes/css_geral.css" rel="stylesheet" type="text/css" />
<smarty>include file="../../templates/header.tpl"</smarty>
<form name="frm_fechamentofolha" id="frm_fechamentofolha" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">        
        <tr>
          <td width="122" rowspan="3" valign="top" class="fundo_cinza">
		  <table width="82%" cellpadding="0" cellspacing="0">
				<tr>
				  <td valign="middle" class="fundo_cinza" ><input name="Incluir" type="button" class="botao_chanfrado" id="Incluir" value="Incluir" onClick="xajax_insere(xajax.getFormValues('frm_fechamentofolha'));"></td>
			  </tr>
				<tr>
				  <td valign="middle" class="fundo_cinza" ><input name="Calcular impostos" type="button" class="botao_chanfrado" id="Calcular impostos" value="Calcular impostos" onCLick="javascript:if(confirm('Deseja calcular os impostos?')) { xajax_calcula_impostos(xajax.getFormValues('frm_fechamentofolha')); }"></td>
			  </tr>
				<tr>
				  <td valign="middle" class="fundo_cinza" ><input name="Relatorios" type="button" class="botao_chanfrado" id="Relatorios" value="Relatórios" onClick="javascript:location.href='relatorios.php?periodo=';"></td>
			  </tr>
				<tr>
				  <td valign="middle" class="fundo_cinza" ><input name="Liberar" type="button" class="botao_chanfrado" id="Liberar" value="Liberar fechamento" onClick="javascript:abrejanela('listafechamentos','listafechamentos.php',600,300);"></td>
			  </tr>
				<tr>
				  <td valign="middle" class="fundo_cinza" ><input name="Visualizar" type="button" class="botao_chanfrado" id="Visualizar" value="Visualizar" onClick="if(document.getElementById('codfuncionario').selectedIndex==0 || document.getElementById('periodo').selectedIndex==0){alert('É necessário selecionar um Funcionário e um período.');}else{abrejanela('fechamento','relatoriofechamentohoras.php?periodo='+document.forms[0].periodo.value+'&id_funcionario='+document.forms[0].codfuncionario.value+'','900','550');}"></td>
			  </tr>
				<tr>
					<td valign="middle" class="fundo_cinza" ><input name="btnvoltar" id="btnvoltar" type="button" class="botao_chanfrado" value="Voltar" onClick="history.back();" /></td>
				</tr>
				<tr>
				  <td valign="middle" class="fundo_cinza" ><input name="id_porcadicionais" type="hidden" id="id_porcadicionais"" value="0" id+"porc_adicionais></td>
			  </tr>
		  </table></td>
          <td width="65" rowspan="2" >&nbsp;</td>
          <td colspan="2">&nbsp;</td>
          <td width="8" rowspan="3" class="<smarty>$classe</smarty>">&nbsp;</td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="borda_alto borda_esquerda">
		  <table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
				  <td width="14%" class="td_sp"><label class="label_descricao_campos">Funcionário</label>
					<select name="codfuncionario" class="caixa" id="codfuncionario" onChange="">
					<option value="">SELECIONE</option>
					<smarty>html_options values=$option_funcionarios_values output=$option_funcionarios_output</smarty>
				  </select>				  </td>
				  <td width="10%" class="td_sp"><label class="label_descricao_campos">Data&nbsp;inicial</label>
				  	<input name="dataini" type="text" class="caixa" id="dataini" size="10" maxlength="10" onKeyPress="return txtBoxFormat(document.frm_fechamentofolha, 'dataini', '99/99/9999', event);" onKeyUp="return autoTab(this, 10, event);" value="<smarty>$smarty.get.data_ini</smarty>"></td>
				  <td width="10%" class="td_sp"><label class="label_descricao_campos">Data&nbsp;final</label>
				  	<input name="datafin" type="text" class="caixa" id="datafin" size="10" maxlength="10" onKeyPress="return txtBoxFormat(document.frm_fechamentofolha, 'datafin', '99/99/9999', event);" onKeyUp="return autoTab(this, 10, event);" value="<smarty>$smarty.get.data_fin</smarty>"></td>
				  <td width="13%" class="td_sp"><label class="label_descricao_campos">Horas&nbsp;adicionais</label>
				  	<input name="horasextras" type="radio" value="1" id="horasextras1" onClick="javascript:abreHE(document.forms[0].codfuncionario.value,document.forms[0].dataini,document.forms[0].datafin);"><label class="label_descricao_campos">Sim</label>
					<input name="horasextras" type="radio" value="0"  id="horasextras0" checked><label class="label_descricao_campos">Não</label></td>
				  <td width="22%" class="td_sp">&nbsp;</td>
				  <td width="18%" class="td_sp">
					<input name="chkmanual" type="checkbox" class="menu" id="chkmanual" value="1" onClick="fn_manual(this)">
					<label class="label_descricao_campos">Cálculo&nbsp;manual</label>
					<input name="manual" type="text" class="caixa" id="manual" size="20" maxlength="9" onKeyDown="FormataValor(document.forms[0].manual, 9, event)" disabled></td>
				<td width="13%">&nbsp;</td>
				</tr>
			  </table>
			  <table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
				  <td width="14%" class="td_sp"><label class="label_descricao_campos">Férias&nbsp;(R$)</label> 
				  	<input name="ferias" type="text" class="caixa" id="ferias" size="15" maxlength="9" onKeyDown="FormataValor(document.forms[0].ferias, 9, event)"></td>
				  <td width="14%" class="td_sp"><label class="label_descricao_campos">Rescisão&nbsp;(R$)</label> 
				  	<input name="rescisao" type="text" class="caixa" id="rescisao" size="15" maxlength="9" onKeyDown="FormataValor(document.forms[0].rescisao, 9, event)"></td>
				  <td width="14%" class="td_sp"><label class="label_descricao_campos">FGTS&nbsp;(R$)</label> 
				  	<input name="fgts" type="text" class="caixa" id="fgts" size="15" maxlength="9" onKeyDown="FormataValor(document.forms[0].fgts, 9, event)"></td>
				  <td width="14%" class="td_sp"><label class="label_descricao_campos">Décimo&nbsp;terceiro&nbsp;(R$)</label>
				  	<input name="decimoterceiro" type="text" class="caixa" id="decimoterceiro" size="15" maxlength="9" onKeyDown="FormataValor(document.forms[0].decimoterceiro, 9, event)"></td>
				  <td width="20%" class="td_sp"><label class="label_descricao_campos">Salário&nbsp;Proporcional&nbsp;CLT&nbsp;(R$)</label>
				  	<input name="salarioproporcional" type="text" class="caixa" id="salarioproporcional" size="15" maxlength="9" onKeyDown="FormataValor(document.forms[0].decimoterceiro, 9, event)"></td>
				  <td width="14%" class="td_sp"><label id="text_descricao" style="display:none;" class="label_descricao_campos">DESCRIÇÃO</label><br />
				  <div id="label_descricao" style="display:none; position:absolute">
				  	<textarea name="descricao_manual" class="caixa" cols="40" rows="4"></textarea>
				  </div></td>
				<td width="10%" class="td_sp">&nbsp;</td>
				</tr>
			  </table>
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
				  <tr>
					<td width="14%" class="td_sp"><label class="label_descricao_campos">Dif.&nbsp;CLT&nbsp;Férias</label>
						<input name="diferenca_clt_ferias" type="text" class="caixa" id="diferenca_clt_ferias" size="10" maxlength="20" onKeyDown="FormataValor(document.forms[0].diferenca_clt_ferias, 9, event)">
						<a href="javascript:openpage('detalhes', 'fechamentofolha_outros_smarty.php?tipo=diferenca_clt_ferias&codfuncionario='+document.forms[0].codfuncionario.value+'&data_ini='+document.forms[0].dataini.value+'&data_fin='+document.forms[0].datafin.value+'',600,500)"><img src="../images/buttons_action/procurar.png" alt="Detalhes" width="16" height="16" border="0"></a></td>
					<td width="14%" class="td_sp"><label class="label_descricao_campos">Dif.&nbsp;CLT&nbsp;Resc.</label>
						<input name="diferenca_clt_rescisao" type="text" class="caixa" id="diferenca_clt_rescisao" size="10" maxlength="20" onKeyDown="FormataValor(document.forms[0].diferenca_clt_rescisao, 9, event)">
						<a href="javascript:openpage('detalhes', 'fechamentofolha_outros_smarty.php?tipo=diferenca_clt_rescisao&codfuncionario='+document.forms[0].codfuncionario.value+'&data_ini='+document.forms[0].dataini.value+'&data_fin='+document.forms[0].datafin.value+'',600,500)"><img src="../images/buttons_action/procurar.png" alt="Detalhes" width="16" height="16" border="0"></a></td>
					<td width="14%" class="td_sp"><label class="label_descricao_campos">Outros&nbsp;Desc.</label>
						<input name="outros_descontos" type="text" class="caixa" id="outros_descontos" size="10" maxlength="20" onKeyDown="FormataValor(document.forms[0].outros_descontos, 9, event)">
						<a href="javascript:openpage('detalhes', 'fechamentofolha_outros_smarty.php?tipo=outros_descontos&codfuncionario='+document.forms[0].codfuncionario.value+'&data_ini='+document.forms[0].dataini.value+'&data_fin='+document.forms[0].datafin.value+'',600,500)"><img src="../images/buttons_action/procurar.png" alt="Detalhes" width="16" height="16" border="0"></a></td>
					<td width="15%" class="td_sp"><label class="label_descricao_campos">Outros&nbsp;Acréscimos</label>
						<input name="outros_acrescimos" type="text" class="caixa" id="outros_acrescimos" size="10" maxlength="20" onKeyDown="FormataValor(document.forms[0].outros_acrescimos, 9, event)">
						<a href="javascript:openpage('detalhes', 'fechamentofolha_outros_smarty.php?tipo=outros_acrescimos&codfuncionario='+document.forms[0].codfuncionario.value+'&data_ini='+document.forms[0].dataini.value+'&data_fin='+document.forms[0].datafin.value+'',600,500)"><img src="../images/buttons_action/procurar.png" alt="Detalhes" width="16" height="16" border="0"></a></td>
					<td width="43%" class="td_sp">&nbsp;</td>
				  </tr>
			  </table>
				<table width="100%" border="0">
				  
				  <tr>
				    <td valign="top" class="kks_nivel3">&nbsp;</td>
			      </tr>
				  <tr>
					<td width="19%" valign="top" class="kks_nivel3">
                    <span class="fonte_descricao_campos">Visualizar período:</span>
                    <select name="periodo" class="caixa" onChange="atualiza_periodo(this)">
                    <option value="">PERÍODO ATUAL</option>
					<smarty>html_options values=$option_periodos_values output=$option_periodos_output</smarty>                    
                    </select></td>
				  </tr>
			  </table>     
              			  
		  </td>
        </tr>
        
        <tr>
          <td class="fundo_azul">&nbsp;</td>
          <td colspan="2" class="<smarty>$classe</smarty>">&nbsp;</td>
        </tr>
      </table>
      <div id="fechamentofolha" style="width:100%;"></div>            
</form>
<smarty>include file="../../templates/footer.tpl"</smarty>