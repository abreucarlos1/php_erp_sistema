function liberaPlanoAcao(valorSelecionado, objetoALiberar)
{
	//Antes da valeria sair era plano de ação, agora são metas portanto em qualquer valor deve liberar
	//if (valorSelecionado <= 4 && valorSelecionado != '')
	if (valorSelecionado <= 10 && valorSelecionado > 0)
	{
		document.getElementById('textarea_'+objetoALiberar).disabled = '';
	}
	else
	{
		document.getElementById('textarea_'+objetoALiberar).innerHTML = '';
		document.getElementById('textarea_'+objetoALiberar).disabled = 'disabled';
	}
}

function grid(tabela, autoh, height, xml)
{
	function doOnRowSelected(row, col)
	{
		if (col <= 2)
		{
			var dados = row.split('_');
			if (dados[1] > 0)
			{
				xajax_montaTelaPDI(dados[0], dados[1], '0');
				xajax_montaTelaMetas(dados[0], dados[1], '0');
			}
		}
	}
	
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.setImagePath("../includes/dhtmlx_403/codebase/imgs/");	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	switch(tabela)
	{
		case 'div_avaliados': 
			mygrid.setHeader("Data&nbsp;Avaliação, Fornecedor, Avaliador,I,Consenso,PDI, Metas");
			mygrid.setInitWidths("100,250,250,50,80,50,60");
			mygrid.setColAlign("left,left,left,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str");
		break;
		case 'divMetasItens':
			mygrid.setHeader("METAS, PESO %, RESULTADO %, I, D");
			mygrid.setInitWidths("400, 100, 120, 50, 50");
			mygrid.setColAlign("left,left,left,center, center");
			mygrid.setColTypes("ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str");
		break;
		case 'div_criterios':
			mygrid.setHeader("&nbsp;, &nbsp;, &nbsp;, &nbsp;");
			mygrid.setInitWidths("60, 60, 60, *");
			mygrid.enableMultiline(true);
			mygrid.setColAlign("left,left,left,left");
			mygrid.setColTypes("ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str");
		break;
		case 'div_monitor':
			mygrid.setHeader("Colaborador, Avaliador, I, Nota, AA, Consenso, Limpar Nota, Limpar AA, Limpar consenso");
			mygrid.setInitWidths("*,*,80,80,80,80,80,80,90");
			//mygrid.enableMultiline(true);
			mygrid.setColAlign("left,left,left,left,left,left,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str");
		break;
		case 'div_monitor_candidatos':
			mygrid.setHeader("Candidato, I, Nota, Limpar Nota");
			mygrid.setInitWidths("*,80,80,100");
			//mygrid.enableMultiline(true);
			mygrid.setColAlign("left,left,left,left");
			mygrid.setColTypes("ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str");
		break;
	}

	//mygrid.attachEvent('onRowSelect', doOnRowSelected);
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function adiciona_linha(row_index)
{
	id = mygrid.getRowId(row_index);
	
	nid = id.split('_');
	nid[1]++;
	
	mygrid.addRow(nid[0]+'_'+nid[1],'',row_index+1);
	mygrid.copyRowContent(id,nid[0]+'_'+nid[1]);

	var elements = $('.txt_meta_'+nid[0]);
	
	var idNovo = elements.length;

	var j = 0;
	
	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = 'txt_meta_['+nid[0]+']['+i+']';
		elements[i].name = 'txt_meta['+i+']';
	}
	
	var elements = $('.txt_peso_'+nid[0]);
	
	var idNovo = elements.length;

	var j = 0;
	
	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = 'txt_peso_['+nid[0]+']['+i+']';
		elements[i].name = 'txt_peso['+i+']';
		elements[i].setAttribute('ref', i);
	}
	
	var elements = $('.txt_resultado_'+nid[0]);
	
	var idNovo = elements.length;

	var j = 0;
	
	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = 'txt_resultado_['+nid[0]+']['+i+']';
		elements[i].name = 'txt_resultado['+i+']';
	}
	
	var elements = $('.img_remover_'+nid[0]);
	
	var idNovo = elements.length;

	var j = 0;
	
	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = i;
	}
	
	var elements = $('.img_adicionar_'+nid[0]);
	
	var idNovo = elements.length;

	var j = 0;
	
	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = i;
	}
	
	$('#btnGravarMetas').attr('disabled',false);
	
	return true;
}

function removerLinha(row_index, valor)
{
	id = mygrid.getRowId(row_index);
	
	mygrid.deleteRow(id);
	
	if (valor != undefined)
	{
		$('#totalRestante').val(parseFloat($('#totalRestante').val()) + parseFloat(valor));
		$('#btnGravarMetas').attr('disabled',false);
	}

	return true;
}

function liberarSaldo(valor)
{
	if (valor == undefined || valor == '' || valor == 'NaN')
		return false;
	
	valor = parseFloat(valor.replace(',', '.'));
	var totalRestante = parseFloat($('#totalRestante').val());
	var total = totalRestante + valor;
	
	$('#totalRestante').val(total);
}

function calcularTotal(valor,row_id)
{
	valor = valor.replace(',', '.');
	var totalRestante = $('#totalRestante').val();
	if (totalRestante - valor < 0)
	{
		alert('O total de 100% já foi atingido.');
		return false;
		//removerLinha(mygrid.getRowIndex(row_id));
	}
	else
	{
		$('#totalRestante').val(totalRestante - valor);
	}
	
	if ($('#totalRestante').val() == '0')
		$('#btnGravarMetas').attr('disabled',false);
	
	return true;
}