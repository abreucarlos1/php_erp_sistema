<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<style type="text/css">
div.gridbox table.row20px tr td
{
	height:auto !important;
	vertical-align:text-top;
}
</style>

<div id="frame" style="width: 100%; height: 700px;">
  <form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
      <table width="100%" border="0">
          <tr>
              <td width="116" valign="top" class="espacamento">
                  <table width="100%" border="0">
                      <tr>
                          <td valign="middle">
                              <input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_inserir_pedido(xajax.getFormValues('frm', true))" value="Inserir" />
                          </td>
                      </tr>
                      <tr>
                          <td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="xajax_voltar();" />
                          </td>
                      </tr>
                      <tr>
                          <td valign="middle"><input name="btnrelatorios" id="btnrelatorios" type="button" class="class_botao" value="Relatórios" onclick="abrir_relatorios();" />
                          </td>
                      </tr>
                      <tr>
                          <td valign="middle"><input name="btncadastros" id="btncadastros" type="button" class="class_botao" value="Cadastros" onclick="abrir_cadastros();" />
                          </td>
                      </tr>
                      <tr>
                      	<td><label class="labels">OS</label>
                      	<label class="labels" id="lblOS"></label>
                      </tr>
                  </table>
              </td>
              <td>
              	<input type="hidden" name="id_os" id="id_os" value="" />
              	<input type="hidden" name="id_cliente" id="id_cliente" value="" />
                  <!-- table border="0" width="100%">
                      <tr>
                          <td width="17%"><label for="id_os" class="labels"><smarty>$campo[2]</smarty></label><br />
                           <select name="id_os" id="id_os" class="caixa" onchange="xajax_carrega_total_orcamento(this.value);" onkeypress="return keySort(this);">
                                  <smarty>*html_options values=$option_os_values output=$option_os_output*</smarty>
                          </select>
                          </td>
                      </tr>
                  </table-->
                  <table border="0">
                      <tr>
                          <td width="5%" align="left"><label for="valor_total" class="labels"><smarty>$campo[15]</smarty></label><br />
                           <input name="valor_total" id="valor_total" type="text" class="caixa" placeholder="Valor total" size="20" value="0" />
                          </td>
                          <!--td width="18%"><label for="condicao_pgto" class="labels">Condi��es&nbsp;de&nbsp;PGTO</label><br />
                           <select name="condicao_pgto" id="condicao_pgto" class="caixa" onkeypress="return keySort(this);">
                                  <smarty>html_options values=$option_cond_values output=$option_cond_output</smarty>
                          </select>
                          </td-->
                          <td width="5%"><label for="data_pedido" class="labels">Data&nbsp;Início</label><br />
                          	<input name="data_pedido" type="text" class="caixa" id="data_pedido" size="10" onKeyPress="transformaData(this, event);" value="<smarty>$smarty.now|date_format:'%d/%m/%Y'</smarty>" onBlur="verificaDataErro(this.value, 'data_pedido');" /> 
                          </td>
                          <td width="5%"><label for="data_termino" class="labels">Data&nbsp;Término</label><br />
                          	<input name="data_termino" type="text" class="caixa" id="data_termino" size="10" onKeyPress="transformaData(this, event);" value="" onBlur="verificaDataErro(this.value, 'data_termino');" /> 
                          </td>
                          <td width="85%"><label for="ref_cliente" class="labels">Ref.:&nbsp;Cliente</label><br />
                          <input name="ref_cliente" type="text" class="caixa" id="ref_cliente" /> 
                          </td>
                      </tr>
                  </table>
                  <table border="0">
                      <tr>
                          <td width="5%" align="left"><label for="obs_pedido" class="labels">Observações:</label><br />
                              <input name="obs_pedido" id="obs_pedido" type="text" class="caixa" placeholder="Observações" style="width:100%;" value="" />
                          </td>
                          <td width="5%" align="left">
                              <label for="coord_cli" class="labels">Coordenador&nbsp;Cliente:</label><br />
                              <input name="coord_cli" id="coord_cli" disabled="disabled" type="text" class="caixa" placeholder="Coordenador Cliente" style="width:100%;" value="" />
                          </td>
                          <td width="90%" align="left">
                              <label for="coord_dvm" class="labels">Coordenador:</label><br />
                              <input name="coord_dvm" id="coord_dvm" disabled="disabled" type="text" class="caixa" placeholder="Coordenador" style="width:300px;" value="" />
                          </td>
                      </tr>
                      <tr>
                          <td valign="middle" colspan="2"><label for="busca" class="labels">Busca</label><br />                          
                          	<input name="busca" id="busca" size="55" type="text" placeholder="Busca" class="caixa" value="" onkeyup="iniciaBusca2.verifica(this);" />
                          </td>
                          <td width="63%"><label for="exibir" class="labels">Filtrar Status OS:</label><br />
							<select name="exibir" class="caixa" id="exibir" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(busca.value,this.value);">
								<smarty>html_options values=$option_status_os_values output=$option_status_os_output</smarty>
							</select>
						  </td>
                      </tr>
                  </table>
              </td>
          </tr>
      </table>
      
      <table width="100%">
      <tr>
          <td>
			  <div class="labels" style="text-align: right;">Foram encontrados&nbsp;<span id="divNumeroRegistros"></span>&nbsp;registros</div>
              <div id="my_tabbar" style="height:480px;"> </div>
		          <div id="a10">
		              <div id="dv_ped">
						  <div id="div_pedidos">&nbsp;</div>
		                  <div id="gridPaginacao" style="float: left;"></div>
		              </div>
		          </div>
		          
		          <div id="a20">
		              <div id="dv_itens">
		                  <input name="pedido_numero" type="hidden" id="pedido_numero" />
		                  <input name="id_bms_item" type="hidden" id="id_bms_item" />
		                  <input name="id_bms_medicao" type="hidden" id="id_bms_medicao" />
		  
		                  <table border="0">
		                      <tr>
		                          <td><label for="numero_item" class="labels"><smarty>$campo[3]</smarty></label><br />
		                           <input name="numero_item" type="text" class="caixa" id="numero_item" size="10" value="1.0" />
		                          </td>
		                          <td><label for="descricao_item" class="labels"><smarty>$campo[4]</smarty></label><br /> 
		                          <input name="descricao_item" type="text" class="caixa" style="text-transform:none !important;" id="descricao_item" placeholder="Descrição" size="50" />
		                          </td>
		                          <td><label for="quantidade" class="labels"><smarty>$campo[5]</smarty></label><br />
		                           <input name="quantidade" type="text" class="caixa" id="quantidade" placeholder="Quantidade" size="15" maxlength="10" onKeyDown="FormataValor(frm.quantidade, 10, event)" />
		                          </td>
		                          <td><label for="id_unidade" class="labels"><smarty>$campo[6]</smarty></label><br />
		                           <select name="id_unidade" id="id_unidade" class="caixa" onkeypress="return keySort(this);" onchange="exibirOcultarValorHora();">
		                                  <smarty>html_options values=$option_unidade_values output=$option_unidade_output</smarty>
		                          </select>
		                          </td>
		                          <td><label for="valor" class="labels"><smarty>$campo[7]</smarty> Total</label><br />
		                           <input name="valor" type="text" class="caixa" id="valor" placeholder="Valor" size="10" maxlength="10" onKeyDown="FormataValor(frm.valor, 10, event)" />
		                          </td>
		                          <td><span id="spanHoraCalculo" style="display:none;"><label for="valor" class="labels"><smarty>$campo[7]</smarty> Hora</label><br />
		                           <input name="valor_hora" onKeyUp="calcularHora();" type="text" class="caixa" id="valor_hora" placeholder="Valor" size="10" maxlength="10" onKeyDown="FormataValor(frm.valor_hora, 10, event)" /></span>
		                          </td>
		                      </tr>
		                  </table>
		                  <table border="0" width="100%">
		                      <tr>
		                          <td width="100%" align="left">
		                          <input name="btninserir_itens" id="btninserir_itens" type="button" class="class_botao" value="<smarty>$botao[1]</smarty>" onclick="xajax_insere_itens(xajax.getFormValues('frm', true));" />
		                          &nbsp;<input name="btnlimpar_form" id="btnlimpar_formItens" type="button" class="class_botao" value="Cancelar" onclick="xajax_limparFormItens();xajax_atualizatabela_itens(document.getElementById('pedido_numero').value);" />
		                          </td>
		                      </tr>
		                  </table>
		                  <div id="div_itens" style="width: 100%;">&nbsp;</div>
		              </div>
		          </div>  
		          <div id="a30">
		              <div id="dv_prog">
		                  <table border="0">
		                      <tr>
		                          <td><label for="id_item" class="labels"><smarty>$campo[3]</smarty></label><br /> 
		                          <select style="width:150px;" name="id_item" id="id_item" class="caixa" onkeypress="return keySort(this);" onchange="if(this.value!=''){xajax_preenchevalor(this.value);xajax_atualizatabela_medicoes(this.value);}else{xajax_limparFormMedicoes();limparTabelaMedicoes()}">
		                          	<option value="">SELECIONE</option>
		                          </select>
		                          </td>
		                          <td><label for="valor_item" class="labels">Valor&nbsp;Total</label><br />
		                          <input name="valor_item" type="text" class="caixa" id="valor_item" size="15" value="0" readonly="readonly" />
		                          </td>
		                          <td><label for="valor_planejado" class="labels">Valor&nbsp;Planj.</label><br />
		                           <input name="valor_planejado" type="text" class="caixa" id="valor_planejado" placeholder="Valor Pln." size="15" onKeyDown="FormataValor(frm.valor_planejado, 10, event);" onkeypress="num_only();" onkeyup="xajax_calcula_valor_percent(document.getElementById('valor_item').value,this.value,'per',document.getElementById('quantidade_item').value);" />
		                          </td>
		                          <td><label for="id_status" class="labels">Status</label><br />
		                           <select name="id_status" id="id_status" class="caixa" onchange="habilitaMedicao(this.value);" onkeypress="return keySort(this);">
		                                  <option value="">SELECIONE</option>
		                          </select>
		                          </td>
		                          <td><label for="valor_medido" class="labels">Valor&nbsp;Medido</label><br />
		                           <input name="valor_medido" type="text" class="caixa" disabled="disabled" placeholder="Valor medido" id="valor_medido" size="15" onKeyDown="FormataValor(frm.valor_medido, 10, event);" onkeypress="num_only();" onkeyup="xajax_calcula_valor_percent(document.getElementById('valor_item').value,this.value,'per_med',document.getElementById('quantidade_item').value);" />
		                          </td>
		                          <td rowspan="3" align="left" valign="top">
		                              <table width="75%" align="right" class="auto_lista">
		                              	<caption class="labels">Legendas:</caption>
		                              	<tr><td><b>F</b></td><td>Faturar Medição</td></tr>
		                              	<tr><td><b>E</b></td><td>Excluir</td></tr>
		                              	<tr><td><b>O</b></td><td>Observações</td></tr>
		                              	<tr><td><b>A</b></td><td>Apontamento de horas</td></tr>
		                              	<tr><td><b>ME</b></td><td>Medir exato (100% do planejado)</td></tr>
		                              </table>
		                          </td>
		                       </tr>
		                       <tr>
		                          <td><label for="data_item" class="labels"><smarty>$campo[8]</smarty></label><br />
		                           <input name="data_item" type="text" class="caixa" id="data_item" size="10" onKeyPress="transformaData(this, event);" value="<smarty>$smarty.now|date_format:'%d/%m/%Y'</smarty>" onBlur="verificaDataErro(this.value, 'data_item');" />
		                          </td>
		                          <td><label for="quantidade_item" class="labels">Qtd.&nbsp;Total <span id="unidadeLbl"></span></label><br />
		                           <input name="quantidade_item" type="text" class="caixa" placeholder="Quantidade item" id="quantidade_item" size="15" value="0" readonly="readonly" />
		                          </td>
		                          <td><label for="percent_planejado" class="labels">%&nbsp;Planej.</label><br />
		                           <input name="percent_planejado" type="text" class="caixa" placeholder="% planejado" id="percent_planejado" size="15" maxlength="10" onKeyDown="FormataValor(frm.percent_planejado, 10, event);" onkeypress="num_only();" onkeyup="xajax_calcula_valor_percent(document.getElementById('valor_item').value,this.value,'val',document.getElementById('quantidade_item').value);" />
		                          </td>
		                          <td></td>
		                          <td><label for="percent_medido" class="labels">%&nbsp;Progresso</label><br />
		                           <input name="percent_medido" type="text" class="caixa" placeholder="% medido" disabled="disabled" id="percent_medido" size="15" maxlength="10" onKeyDown="FormataValor(frm.percent_medido, 10, event);" onkeypress="num_only();" onkeyup="xajax_calcula_valor_percent(document.getElementById('valor_item').value,this.value,'val_med',document.getElementById('quantidade_item').value);" />
		                          </td>
		                      </tr>
		                      <tr>
		                      	  <td colspan="2">
		                            	<input type="checkbox" name="chk_replicar" id="chk_replicar" value="1" />
		                            	<label class="labels">Replicar </label>
		                            	<input type="text" size="3" name="txt_num_replicas" id="txt_num_replicas" class="caixa" onBlur="showModalDatasReplicas(this.value);" onKeyPress="num_only();" />
		                            	<label class="labels"> vezes</label>
		                            	<input type="hidden" name="datas_replica_definidas" id="datas_replica_definidas" readonly="readonly" />
		                            	<input type="hidden" name="qtds_replica_definidas" id="qtds_replica_definidas" readonly="readonly" />
		                          </td>
		                          <td><label class="labels">Qtd.&nbsp;Planj.<span id="unidadePlanLbl"></span></label><br />
		                           <input name="quantidade_planejada" type="text" placeholder="Quantidade planejada" class="caixa" id="quantidade_planejada" size="15" onKeyDown="FormataValor(frm.quantidade_planejada, 10, event);" onkeypress="num_only();" onkeyup="xajax_calcula_quantidade(document.getElementById('valor_item').value,this.value,'per',document.getElementById('quantidade_item').value);" />
		                          </td>
		                          <td></td>
		                          <td><label class="labels">Qtd.&nbsp;Medido. <span id="unidadeMedLbl"></span></label><br />
		                           <input name="quantidade_medida" type="text" class="caixa" placeholder="Quantidade medida" disabled="disabled" id="quantidade_medida" size="15" onKeyDown="FormataValor(frm.quantidade_medida, 10, event);" onkeypress="num_only();" onkeyup="xajax_calcula_quantidade(document.getElementById('valor_item').value,this.value,'per_med',document.getElementById('quantidade_item').value);" />
		                          </td>
		                      </tr>
		                  </table>
		                  <table border="0" width="100%">
		                      <tr>
		                          <td width="40%" align="left"><input name="btninserir_medicoes" id="btninserir_medicoes" type="button" class="class_botao" value="<smarty>$botao[1]</smarty>" onclick="xajax_insere_medicoes(xajax.getFormValues('frm', true));" />&nbsp;
		                              <input name="btnlimpar_form" id="btnlimpar_form" type="button" class="class_botao" value="Cancelar" onclick="xajax_limparFormMedicoes();xajax_preenchevalor(document.getElementById('id_item').value);" />
		                              <input name="btnexcluir_selecionados" id="btnexcluir_selecionados" class="class_botao" value="Excluir Selecionados" disabled="disabled" style="width:auto;" onclick="excluir_itens_selecionados();" type="button" />
		                          </td>
		                      </tr>
		                  </table>
		                  <div id="div_medicoes" style="width: 100%;">&nbsp;</div>
		              </div>
		          </div>         
		          <div id="a40">
		              <div id="div_bms">
		                  <label for="data_bms" class="labels">Data:</label><br />
		                  <input name="data_bms" type="text" class="caixa" placeholder="Data BMS" id="data_bms" size="10" onKeyPress="transformaData(this, event);" value="" onBlur="verificaDataErro(this.value, 'data_bms');" /><br />
		                  <input name="btngerar_bms" id="btngerar_bms" type="button" class="class_botao" value="Gerar BMS" onclick="xajax_gerar_bms(document.getElementById('pedido_numero').value, document.getElementById('data_bms').value);" />
		              </div>
		          </div>
		          <div id="a50">
		              <div id="dv_ped_fin">
						  <div id="div_pedidos_finalizados">&nbsp;</div>
		              </div>
		          </div>
          </td>
      </tr>      
    </table>  
  </form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>