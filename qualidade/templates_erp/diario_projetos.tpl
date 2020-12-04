<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<style type="text/css">

div.gridbox table.row20px tr td

{
height:auto !important;
vertical-align:middle;

}

</style>

<div id="frame" style="width:100%; height:700px;">
	<form name="frm" id="frm" action="" method="POST" style="margin:0px; padding:0px;">
		<table width="100%" border="0">
			<tr>
			  <td width="116" valign="top" class="espacamento" >
			  <table width="100%" border="0">
              		<tr>
                    	<td valign="middle"><input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_inserir(xajax.getFormValues('frm',true));" disabled="disabled" value="Incluir" />
                        </td>
                    </tr>
					<tr>
						<td valign="middle"><input name="btnexportar" type="button" class="class_botao" id="btnexportar" onclick="exportar();" disabled="disabled" value="Exportar" />
						</td>
					</tr>
					<tr>
						<td valign="middle"><input name="btnvoltar" type="button" class="class_botao" id="btnvoltar" onclick="history.back();" value="Voltar" />
						</td>
					</tr>
				</table></td>
			</tr>
			<tr>
			  <td colspan="2" valign="top" class="espacamento" >
              
       	<div id="my_tabbar" style="height:600px;"> 
                                    
                	<div id="a10">     
                		<div id="div_os">                
                            <table width="100%" border="0">
                            <tr>
                                <td width="7%"><label for="id_os" class="labels">OS:</label><br /> 
                                   <select name="id_os" class="caixa" id="id_os" onchange="valida(this.value);" onkeypress="return keySort(this);">
                                    <smarty>html_options values=$option_os_values output=$option_os_output</smarty>
                                  </select>
                                 </td>
                            </tr>			    
                            </table>
                            <table width="100%" border="0">
                           <tr>
                              <td width="7%"><label for="descricao_item" class="labels">Texto:</label><br /> 
							     <textarea name="descricao_item" id="descricao_item" cols="100" rows="10" class="caixa" placeholder="Descrição" disabled="disabled"></textarea></td>
                            </tr>
                            </table>                            
						</div>
            		</div>
                    <div id="a20">     
                      <div id="itens">&nbsp;</div>
                  </div>
              </div>
              </td>
		  </tr>
		  </table>
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>