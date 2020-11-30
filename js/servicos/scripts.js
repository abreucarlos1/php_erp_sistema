$(document).ready(function(){
	$('label[for]').append(' <b style="color:red">*</b>');
	
	$('#btninserir').on('click', function(){
		$.ajax({
			url: './servicos.php',
			data: $('#frmCadastro').serialize()+'&acao=inserir',
			type: 'post',
			dataType: 'json',
			success: function(retorno){
				if (retorno > '0')
				{
					alert('Registro inserido com sucesso!');
					window.location = './servicos.php';
				}
				else
				{
					alert('Por favor, preencha todos os campos!');
					return false;
				}
			}	
		});
	});
	
	$('.btnExclusao').on('click', function(){
		var id = $(this).attr('id');
		
		$.ajax({
			url: './servicos.php',
			data: {acao: 'excluir', id: id},
			type: 'post',
			dataType: 'json',
			success: function(retorno){
				if (retorno == '1')
				{
					alert('Exclusão realizada com sucesso!');
					window.location = './servicos.php';
				}
				else
				{
					alert('Houve uma falha ao tentar excluir o registro!');
					return false;
				}
			}
		});
	});
});

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