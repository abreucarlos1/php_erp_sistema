$(document).ready(function(){
	$('.codBarras').mask('99.999.9999999.9');
	
	$('#imgSelecionarComponentes').on('click', function(){
		html = '<iframe height="725px" width="100%" style="border: none;" src="./codigo_inteligente.php?ajax=1" id="iframeCodigoInteligente" name="iframeCodigoInteligente"></iframe>';
		modal(html, 'ggg', 'CADASTRO / SELEÇÃO DE COMPONENTES');
	});
	
	$("#zoom").elevateZoom({
		gallery:'galeria',
		cursor: 'pointer',
		galleryActiveClass: 'active',
		imageCrossfade: true,
		zoomWindowPosition: 11,
		scrollZoom : true
	});
	
	//Selecionar unidade
	$('.selecionarUnidade').on('click', function(){
		html = '<iframe height="630px" width="100%" style="border: none;" src="./unidade.php?ajax=1&ref='+$(this).attr('ref')+'" id="iframeCodigoInteligente" name="iframeCodigoInteligente"></iframe>';
		modal(html, 'gg', 'CADASTRO / SELEÇÃO DE UNIDADE');
	});
	
	$('.selecionarCcusto').on('click', function(){
		modal('<div id="centro_custos"></div>', 'm', 'CLIQUE SOBRE A LINHA PARA SELECIONAR UM CENTRO DE CUSTO');
		$.ajax({
			url: './produtos.php',
			type: 'post',
			data: {ajax:1,funcao:'getCentroCusto'},
			success: function(conteudo){
				grid('centro_custos',true,'345',conteudo);
			}
		});
		
		return false;
	});
	
	$('.lista_fornecedores').on('click', function(){
		var codBarras = $('#codigoComponente').val();
		
		if (codBarras != '')
		{
			modal('<div id="lista_fornecedores"></div></form>', 'g', 'SELEÇÃO DE FORNECEDORES PARA O PRODUTO');
			$.ajax({
				url: './produtos.php',
				type: 'post',
				data: {ajax:1,funcao:'getFornecedores',parametros:codBarras},
				success: function(conteudo){
					grid('lista_fornecedores',true,'250',conteudo);
					
					$('.selecionarUnidade').on('click', function(){
						html = '<iframe height="630px" width="100%" style="border: none;" src="./unidade.php?ajax=1&ref='+$(this).attr('id')+'&adicional=1" id="iframeCodigoInteligente" name="iframeCodigoInteligente"></iframe>';
						modal(html, 'gg', 'CADASTRO / SELEÇÃO DE UNIDADE', 1);
					});
					
					$('.cadastrar_preco_fornecedor').on('click', function(){
						var id = $(this).attr('ref');						
						var txtPreco   = $('#txtPreco_'+id).val();
						var txtPreco2  = $('#txtPreco2_'+id).val();
						var txtUnidade = $('#txtUnidade_'+id).val();
						var txtUnidade2 = $('#txtUnidade2_'+id).val();
						
						$.ajax({
							url: './produtos.php',
							type: 'post',
							data: {ajax:1,funcao:'cadastrar_preco_fornecedor',parametros:{txtPreco:txtPreco,txtUnidade:txtUnidade,txtPreco2:txtPreco2,txtUnidade2:txtUnidade2,codBarras:codBarras,idFornecedor:id}},
							dataType: 'json',
							success: function(retorno){
								if (retorno[0])
								{
									alert(retorno[1]);
									divPopupInst.destroi();
									xajax_atualizaTabelaFornecedor(codBarras);
								}
								else
								{
									alert(retorno[1]);
								}
							}
						});
						
						return false;
					});
				}
			});
		}
		else
		{
			alert('Por favor, selecione um componente!');
		}
		
		return false;
	});
});

function preencheTela()
{
	xajax_preencheTela(xajax.getFormValues('frm_principal'));
}

function selecionar_centro_custo(id, row)
{
	$('#ccusto').val(id);
	divPopupInst.destroi();
	return true;
}

function selecionar_fornecedor(id, row)
{
	modal('<div id="lista_preco"></div>', 'm', 'DIGITE');
	$.ajax({
		url: './produtos.php',
		type: 'post',
		data: {ajax:1,funcao:'seleciona_fornecedor',parametros:id},
		success: function(conteudo){
			grid('lista_fornecedores',true,'250',conteudo);
		}
	});
}

function showModalBuscar()
{
	var html = 	'<label class="labels">Filtrar</label>&nbsp;'+
				'<form id="frmCriaFamilia"><input type="text" id="txtFiltro" name="txtFiltro" size="120" onkeyup="iniciaBusca2.verifica(this.id);txtIdFamilia.value=\'\'" />'+
				'<input type="hidden" id="txtIdFamilia" name="txtIdFamilia" />'+
				'&nbsp;<img src="../imagens/inserir.png" id="imgSelecionarFamilias" style="cursor:pointer" onclick="showModalFamilias()" title="Selecionar Familias" /><label class="labels">Cadastro de Famílias</label>';
		html+='<div style="margin-top: 10px;" id="lista_produtos_cadastrados"></div></form>';
		html+='<br /><img onclick=window.location="./relatorios/rel_lista_produtos_cadastrados_excel.php?filtro="+document.getElementById(\'txtFiltro\').value; style="cursor:pointer;" class="btnRelProdutosCadastradosExcel" src="../imagens/file_xls.png"> <label class="labels">Gerar Relatório em Excel</label>';
		html+='&nbsp;<input type="button" id="btnCriarFamilia" name="btnCriarFamilia" onclick="agregarFamilia();" value="Agregar Família" class="class_botao" style="display:none;float:right;" />';
		
	modal(html, 'gg', 'VISUALIZAR PRODUTOS CADASTRADOS');
}

function showModalFamilias()
{
	var html =  '<form id="frmAlterarFamilia">'+
					'<table><tr><td>'+
						'<label class="labels" style="float:left;width: 110px">Descrição</label>'+
						'<input type="text" value="" name="txtDescricaoFamilia" id="txtDescricaoFamilia" size="75" />'+
					'</td></tr><tr><td>'+
						'<label class="labels" style="float:left;width: 110px">Descrição Longa</label>'+
						'<textarea name="txtDescricaoLongaFamilia" id="txtDescricaoLongaFamilia" cols="56" rows="2"></textarea>'+
					'</td></tr><tr><td>'+
						'<input type="hidden" value="" name="idFamilia" id="idFamilia" />'+
						'<input type="button" class="class_botao" value="Salvar" onclick=xajax_salvar_familia(xajax.getFormValues("frmAlterarFamilia")); />'+
					'</td></tr></table>'+
				'</form><br />'+
				'<div id="lista_familias"></div>';
		
	modal(html, 'm', 'SELECIONE UMA FAMÍLIA CADASTRADA PARA USA-LA NA BUSCA', 1);
	chamaListaFamilias();
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.setImagePath("../includes/dhtmlx_403/codebase/imgs/");	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	switch(tabela)
	{
		case 'centro_custos':
			mygrid.setHeader("Centro&nbsp;Custo, Descrição");
			mygrid.setInitWidths("100,*");
			mygrid.setColAlign("left,left");
			mygrid.setColTypes("ro,ro");
			mygrid.setColSorting("str,str");

			mygrid.attachEvent("onRowSelect",'selecionar_centro_custo');
		break;
		
		case 'div_fornecedor':
			mygrid.setHeader("ID Fornecedor, Preço, Atualização");
			mygrid.setInitWidths("50,*, 100, 100");
			mygrid.setColAlign("left,left,left,left");
			mygrid.setColTypes("ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str");
		break;
		
		case 'lista_fornecedores':
			mygrid.setHeader("Fornecedor, Cidade, Bairro, Preço,Unidade 1, Preço 2, Unidade 2, S");
			mygrid.setInitWidths("*,*,*,80,100,80,100,80,50");
			mygrid.setColAlign("left,left,left,left,left,left,left,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str");

		break;
		
		case 'lista_fornecedores_selecionados':
			mygrid.setHeader("Fornecedor, Preço, Unidade 1, Preço 2, Unidade 2, Atualização, A, E");
			mygrid.setInitWidths("*,60,80,60,80,100,50,50");
			mygrid.setColAlign("left,left,left,left,left,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str");

		break;
		
		case 'lista_produtos_cadastrados':
			mygrid.setHeader("Família, Cod. Barras, Descrição");
			mygrid.setInitWidths("60, 100, *");
			mygrid.setColAlign("left,left,left");
			mygrid.setColTypes("ro,ro,ro");
			mygrid.setColSorting("str,str,str");
			//divPopupInst.destroi();
			
			mygrid.attachEvent("onRowSelect",selecionar_produto);
		break;
		
		case 'unidades':
			mygrid.setHeader("Unidade");
			mygrid.setInitWidths("*");
			mygrid.setColAlign("left");
			mygrid.setColTypes("ro");
			mygrid.setColSorting("str");

			function carregarUnidadeSelecionada(id, row)
			{
				codigo = id.split('_');
				document.getElementById(codigo[1]).value = codigo[0];
				divPopupInst.destroi(1);
			}
			
			mygrid.attachEvent("onRowSelect",carregarUnidadeSelecionada);
		break;
		
		case 'lista_familias':
			mygrid.setHeader("Código, Descrição, S, D");
			mygrid.setInitWidths("100,*,50, 50");
			mygrid.setColAlign("left,left,left,left");
			mygrid.setColTypes("ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str");

			function carregarFamiliaSelecionada(id, row)
			{
				if (row < 2)
				{
					document.getElementById('idFamilia').value = id;
					document.getElementById('txtDescricaoFamilia').value = document.getElementById('txt_'+id).value;
					document.getElementById('txtDescricaoLongaFamilia').value = document.getElementById('txt_longa_'+id).value;
				}
			}
			
			mygrid.attachEvent("onRowSelect",carregarFamiliaSelecionada);
		break;
	}

	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function selecionar_produto(id, row)
{
	id = id.split('_');
	$('#codigoComponente').val(id[0]);
	divPopupInst.destroi();
	$('#codigoComponente').blur();
	return true;
}

var iniciaBusca2 =
{
	buffer: false,
	tempo: 1000,

	verifica : function(textbox)
	{
		setTimeout("iniciaBusca2.compara('" + textbox + "', '" + document.getElementById(textbox).value + "')", this.tempo); 
	},
	compara : function(id, valor)
	{
		if(valor == document.getElementById(id).value && valor != this.buffer)
		{
			this.buffer = valor;
			iniciaBusca2.chamaXajax(valor);
		}
	},

	chamaXajax : function(valor)
	{
		chamaBuscaFiltro();
	}
}

function agregarFamilia()
{
	$.ajax({
		url: './produtos.php',
		type: 'post',
		data: {ajax:1,funcao:'agregarFamilia',parametros:$('#frmCriaFamilia').serializeArray()},
		dataType: 'json',
		success: function(retorno){
			if (retorno[0] == '1')
			{
				alert(retorno[1]);
				chamaBuscaFiltro();
			}
			else 
			{
				alert(retorno[1]);
			}
		}
	});
}

function chamaBuscaFiltro()
{
	$.ajax({
		url: './produtos.php',
		type: 'post',
		data: {ajax:1,funcao:'lista_produtos_cadastrados',parametros:document.getElementById('txtFiltro').value},
		success: function(conteudo){
			if (conteudo != '')
				$('#btnCriarFamilia').show();
			else
				$('#btnCriarFamilia').hide();
			
			grid('lista_produtos_cadastrados',true,'450',conteudo);
		}
	});	
}

function excluirFamiliaSelecionada(idFamilia)
{
	$.ajax({
		url: './produtos.php',
		type: 'post',
		data: {ajax:1,funcao:'excluir_familia',parametros:idFamilia},
		dataType: 'json',
		success: function(retorno){
			if (retorno[0] == '1')
			{
				alert(retorno[1]);
				chamaListaFamilias();
			}
			else 
			{
				alert(retorno[1]);
			}
		}
	});
}

function chamaListaFamilias()
{
	$.ajax({
		url: './produtos.php',
		type: 'post',
		data: {ajax:1,funcao:'lista_familias'},
		success: function(conteudo){
			grid('lista_familias',true,'205',conteudo);
		}
	});	
}

function carregarFamiliaSelecionada(id)
{
	document.getElementById('txtFiltro').value = document.getElementById('txt_'+id).value;
	document.getElementById('txtIdFamilia').value = id;
	divPopupInst.destroi(1);
	chamaBuscaFiltro();
}