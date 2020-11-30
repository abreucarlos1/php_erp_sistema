$(document).ready(function(){
	$('.tooltip').tooltip();
	$('.cursor-pointer').css('cursor', 'pointer');
	
	$("#tabs").tabs({
		beforeLoad: function( event, ui ) {
	        ui.jqXHR.fail(function() {
	          ui.panel.html(
	            "Couldn't load this tab. We'll try to fix this as soon as possible. " +
	            "If this wouldn't be a demo." );
	        });
		},
		load: function()
		{
			propagarExclusao();
		}
	});
	
	$('label[for]').append(' <b style="color:red">*</b>');
	
	$("#txt_data_solicitacao").datepicker({
		dateFormat: 'dd/mm/yy',
		dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'],
		dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
		dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
		monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
		monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
		nextText: 'Próximo',
		prevText: 'Anterior'
	});
	
	$('#btninserir').on('click', function(){
		if (!verificaForm())
			return false;
		
		$.ajax({
			url: './gestao_mudancas.php',
			data: $('#frmCadastro').serialize()+'&acao=inserir',
			type: 'post',
			dataType: 'json',
			success: function(retorno){
				if (retorno == '1')
				{
					alert('Registro inserido com sucesso!');
					window.location = './gestao_mudancas.php';
				}
				else
				{
					alert('Houve uma falha ao tentar inserir o registro!');
					return false;
				}
			}	
		});
	});
	
	propagarExclusao();
});

function propagarExclusao()
{
	$('.btnExclusao').on('click', function(){
		var id = $(this).attr('id');
		
		$.ajax({
			url: './gestao_mudancas.php',
			data: {acao: 'excluir', id: id},
			type: 'post',
			dataType: 'json',
			success: function(retorno){
				if (retorno == '1')
				{
					alert('Exclusão realizada com sucesso!');
					window.location = './gestao_mudancas.php';
				}
				else
				{
					alert('Houve uma falha ao tentar excluir o registro!');
					return false;
				}
			}
		});
	});
}

function verificaForm()
{
	var itens = 0;
	//Otimizar esta operação
	//Abaixo verifico se existem itens obrigatórios não preenchidos para desabilitar ou habilitar o botão inserir
	$('.obrigatorio').each(function(){
	  if ($.trim($(this).val()) === '')
	  {
	    itens++;
	  }
	});
	
	if (itens > 0)
		return false;
	else
		return true;
}

function listaEditar(id)
{
	$.ajax({
		url: './gestao_mudancas.php',
		data: {acao: 'editarAprovada', id: id},
		type: 'post',
		dataType: 'json',
		success: function(retorno){
			if (retorno['status'] == '1')
			{
				modal(retorno['html'], 'g');
				getListaTarefas(id);
			}
			else
			{
				alert('Houve uma falha ao tentar realizar esta operação!');
				return false;
			}
		}
	});
}

function listaAnalisar(id)
{
	if (confirm('Deseja enviar esta GMUD para análise?'))
	{
		mudarStatus(id, 1);
	}
	else
		return false;
}

function listaAprovar(id)
{
	if (confirm('Deseja aprovar a execução desta GMUD?'))
	{
		mudarStatus(id, 2);
	}
	else
		return false;
}

function mudarStatus(id, status)
{
	$.ajax({
		url: './gestao_mudancas.php',
		data: {acao: 'mudarStatus', id: id, status: status},
		type: 'post',
		dataType: 'json',
		success: function(retorno){
			if (retorno == '1')
			{
				alert('Operação realizada com sucesso!');
				window.location = './gestao_mudancas.php';
			}
			else
			{
				alert('Houve uma falha ao tentar realizar esta operação!');
				return false;
			}
		}
	});
}

function cadastrarTarefa()
{
	$.ajax({
		url: './gestao_mudancas.php',
		data: $('#frmCadastrarTarefa').serialize()+'&acao=cadastrarTarefa',
		type: 'post',
		dataType: 'json',
		success: function(retorno){
			if (retorno)
			{
				alert('Operação realizada com sucesso!');
				getListaTarefas($('#id_gmud').val());
				document.frmCadastrarTarefa.reset();
				$('#id_gmudt').val('');
			}
			else
			{
				alert('Houve uma falha ao tentar realizar esta operação!');
				return false;
			}
		}
	});
}

function imprimirTap()
{
	window.open('./gestao_mudancas.php?acao=imprimirTap&id='+$('#id_gmud').val(), '_blank');
}

function getListaTarefas(id)
{
	$.ajax({
		url: './gestao_mudancas.php',
		data: {acao: 'getListaTarefas', id: id},
		type: 'post',
		dataType: 'json',
		success: function(retorno){
			if (retorno['status'] == 1)
			{
				$('#divListaTarefasGmud').html(retorno['listaHtml']);
			}
			else
			{
				alert('Houve uma falha ao tentar encontrar a lista!');
				return false;
			}
		}
	});
}

function editarTarefaGmudt(id)
{
	$.ajax({
		url: './gestao_mudancas.php',
		data: {acao: 'getTarefaById', id: id},
		type: 'post',
		dataType: 'json',
		success: function(retorno){
			if (retorno['status'] == 1)
			{
				console.log(retorno['dados'][0].id_gmudt);
				$('#id_gmudt').val(retorno['dados'][0].id_gmudt);
				$('#descTarefa').val(retorno['dados'][0].descricao_gmudt);
				$('#selStatusGmudt').val(retorno['dados'][0].status_gmudt);
				$('#selIdFuncGmudt').val(retorno['dados'][0].id_funcionario_gmudt);
				$('#qtd_horas').val(retorno['dados'][0].qtd_horas);
			}
			else
			{
				alert('Houve uma falha ao tentar encontrar o registro!');
				return false;
			}
		}
	});
}

function excluirTarefaGmudt(id)
{
	$.ajax({
		url: './gestao_mudancas.php',
		data: {acao: 'excluirTarefa', id: id},
		type: 'post',
		dataType: 'json',
		success: function(retorno){
			if (retorno == '1')
			{
				alert('Exclusão realizada com sucesso!');
				getListaTarefas($('#id_gmud').val());
			}
			else
			{
				alert('Houve uma falha ao tentar excluir o registro!');
				return false;
			}
		}
	});	
}

function gravarRiscoProjeto()
{
	if ($('#riscos').val() != '')
	{
		$.ajax({
			url: './gestao_mudancas.php',
			data: {risco: $('#riscos').val(), grau:$('#selGraviRisco').val(), idGmud: $('#id_gmud').val(), acao: 'gravarRisco'},
			type: 'post',
			dataType: 'json',
			success: function(retorno){
				if (retorno > 0)
				{
					alert('Risco inserido corretamente!');
					$('#riscos').val('');
					$('#selGraviRisco').val('');
				}
				else
				{
					alert('Houve uma falha ao tentar inserir o registro!');
					return false;
				}
			}	
		});
	}
	
	return false;
}