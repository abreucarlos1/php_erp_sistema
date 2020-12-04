<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>

<link rel="stylesheet" href="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.css">
<script src="../includes/jquery/jquery.min.js"></script>
<script src="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	if ($('#tmpHidden').length > 0)
	{
		$('#txt_area').val($('#tmpHidden').val());
	}
	
	$('._tooltip').tooltip();
	
	if ($('.datepicker').length)
	{
		$(".datepicker").datepicker({
			dateFormat: 'dd/mm/yy',
			dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'],
			dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
			dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
			monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
			monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
			nextText: 'Próximo',
			prevText: 'Anterior'
		});
	};
	
	$('label[for]').append(' <b style="color:red">*</b>');
	
	$('#pedidoVia').on('change', function(){
		var pedido = $(this).val();
		
		$('.lblOculta').hide();
				
		if (pedido == '')
		{
			return false;
		}
		
		if ($('.'+pedido).length)
			$('.'+pedido).show();
	});
	
	$('#btnrelatorio').on('click', function(){
		atuais = $('#chkatuais').is(':checked') ? 1 : 0;
		var url = './relatorios/inventario.php?atuais='+atuais;
	
		window.open(url,'_blank');
	});
	
	$('#chkatuais').on('click', function(){
		atuais = $('#chkatuais').is(':checked') ? 1 : 0;
		var url = './inventario.php?atuais='+atuais;
	
		location.href = url;
	});
});
</script>
	
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_inventario" id="frm_inventario" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">                
	<tr>
		<td width="116" valign="top" class="espacamento">
			<table width="100%" border="0">
			<tr>
				<td valign="middle">
					<input name="btninserir" type="button" class="class_botao" id="btninserir" value="Inserir" onclick="xajax_insere(xajax.getFormValues('frm_inventario'));" />
				</td>
			</tr>
        	<tr>
        		<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
			</tr>
			<tr>
				<td valign="middle">
					<input name="btnrelatorio" type="button" class="class_botao" id="btnrelatorio" value="Relatório" />
				</td>
			</tr>
			<tr>
				<td>
					<input name="chkatuais" type="checkbox" class="caixa" id="chkatuais" <smarty>$checadoAtuais</smarty> value="1" /><label class="labels">Em Aberto</label>
				</td>
			</tr>
			<tr>
				
			</tr>
       		</table>
		</td>
        <td colspan="2" valign="top" class="espacamento">
        	<table>
				<tr>
					<td><label for="solicitante" class="labels">Usuário solicitante</label><br />
						<select id='solicitante' name='solicitante' class="caixa" style="width:250px;">
							<smarty>html_options values=$option_func_values output=$option_func_output</smarty>							
						</select>
					</td>
					<td>
						<label for="equipamento" class="labels">Equipamento</label><br />
						<select id='equipamento' name='equipamento' class="caixa" style="width:250px;">
							<smarty>html_options values=$option_equip_values output=$option_equip_output</smarty>
						</select>
					</td>
					<td><label for="data_retirada" class="labels">Data Retirada</label><br />
                        <input type="text" id="data_retirada" name="data_retirada" class="datepicker obrigatorio form-control input-sm caixa" value='<smarty>$smarty.now|date_format:"%d/%m/%Y"</smarty>' />
					</td>
				</tr>
				<tr>
					<smarty>if $area == 'TI'</smarty>
					<td colspan="3"><label class="labels">Acessórios</label><br />
						<smarty>foreach $acessorios as $ac</smarty>
							<input type="checkbox" class="chk" name="acessorios[]" value="<smarty>$ac['id_acessorio']</smarty>" /> <label class="labels"><smarty>$ac['acessorio']</smarty></label>
						<smarty>/foreach</smarty>
	                        </td>
					<smarty>/if</smarty>
				</tr>
				<tr>
					<td>
						<label for="pedidoVia" class="labels">Pedido via</label><br />
						<select name="pedidoVia" id="pedidoVia" class="obrigatorio form-control input-sm caixa">
							<option value="">Selecione...</option>
							<option value="lblChamado">Chamado</option>
							<option value="lblEmail">E-mail</option>
							<option value="lblVerbal">Verbal</option>
							<option value="lblOutros">Outros</option>
						</select>
					</td>
					<td class="td_sp lblOs">
						<label class="labels">Código OS</label><br />
						<select name="os" id="os" class="form-control input-sm caixa">
							<smarty>html_options values=$option_os_values output=$option_os_output</smarty>
						</select>
					</td>
					<td>
						<label for="equipamento" class="labels">Local de trabalho</label><br />
						<select id='locaisTrabalho' name='locaisTrabalho' class="caixa" style="width:200px">
							<smarty>html_options values=$option_local_values output=$option_local_output</smarty>
						</select>
					</td>
				</tr>
				<tr>
					<td class="lblOculta lblChamado" style="display:none;">
						<label class="labels">Código Chamado</label><br />
						<input type="text" id="chamado" name="chamado" class="form-control input-sm caixa" />
					</td>
					
					<td class="lblOculta lblOutros" style="display:none;">
						<label class="labels">Descrição</label><br />
                            <textarea id="descricaoOutros" name="descricaoOutros" class="form-control input-sm caixa"></textarea>
					</td>
					<td class="lblOculta lblEmail" style="display:none;">
						<label class="labels">E-mail</label><br />
                            <textarea id="email" name="email" class="form-control input-sm caixa"></textarea>
					</td>
					
					<td class="lblOculta lblVerbal" style="display:none;">
						<label class="labels">Detalhes</label><br />
                            <textarea id="descricaoVerbal" name="descricaoVerbal" class="form-control input-sm caixa"></textarea>
					</td>
					
				</tr>
			</table>
  		</td>
	</tr>
	</table>
</form>
	<div id="listagem"></div>
</div>
<smarty>include file="../../templates_erp/footer.tpl"</smarty>