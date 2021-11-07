/*		Include de rotinas do sistema GED
		Criado por Carlos Abreu
		Local/Nome do arquivo:
		../arquivotec/ged.js
		Última alteração: 27/10/2014
*/

function adiciona_input_file(div_pai)
{
	//Instancia a classe
	elementosInst = new elementos();

	//Referencia os elementos-filhos
	div_filhos = elementosInst.$(div_pai).getElementsByTagName("input");

	//Passa nos elementos-filhos do div fornecido
	for(x=0;x<div_filhos.length;x++)
	{		
		nome_campo = div_filhos[x].name.split("_")[0];
		numero_campo = div_filhos[x].name.split("_")[1];
	}

	//Remove a imagem do input file anterior
	elementosInst.$(div_pai).removeChild(elementosInst.$('img_' + numero_campo));

	prox_numero_campo = parseInt(numero_campo)+1;
	
	//Cria o elemento <input> file
	nome_input_file = div_pai.split("_")[1];
	input_file = elementosInst.criar('input');
	input_file.type = 'file';
	input_file.id = 'input_' + prox_numero_campo;
	input_file.name = 'input_' + prox_numero_campo;
	
	//Cria o elemento <img>
	img_file = elementosInst.criar('img');
	img_file.id = 'img_' + prox_numero_campo;
	img_file.name = 'img_' + prox_numero_campo;
	img_file.src = '../imagens/add.png';
	img_file.style.cursor = 'pointer';
	img_file.style.marginLeft = '2px';
	img_file.alt = 'Adicionar outro comentário';
	img_file.onclick = function () { adiciona_input_file(div_pai); };
	
	quebra = elementosInst.criar('br');	
	
	//Apenda ambos no div fornecido
	elementosInst.$(div_pai).appendChild(input_file);
	elementosInst.$(div_pai).appendChild(img_file);
	elementosInst.$(div_pai).appendChild(quebra);
}

function divPopup()
{
	//Instancia a classe
	elementosInst = new elementos();
	
	this.largura = 300; //Altura do div interno (branco)
	this.altura = 120; //Largura do div interno (branco)
	this.top_acrescimo = 0; //Distância do topo do div interno (branco), valores negativos aproximam do topo

	this._criaFundo = function ()
	{
		this.div_fundo = elementosInst.criar('div');
		this.iframe_iebug = elementosInst.criar('iframe');
		
		this.div_fundo.id = 'div_fundo';
		this.div_fundo.style.backgroundColor = '#000000';
		this.div_fundo.style.filter = 'alpha(opacity=20)';
		this.div_fundo.style.float = 'left';
		this.div_fundo.style.opacity = '.20';
		
		this.div_fundo.style.left = '0px';
		this.div_fundo.style.top = '0px';
		this.div_fundo.style.position = 'absolute';
		this.div_fundo.style.padding = '150px';
		document.body.scrollTop=0;
		document.body.scroll = "no";
		this.div_fundo.style.width = document.body.clientWidth;
		this.div_fundo.style.height = document.body.clientHeight;
		this.div_fundo.style.zIndex = '1';
		/* Iframe para corrigir um BUG com combos que não respeitam zIndex no IE6 */
		this.iframe_iebug.id = 'iframe_iebug';
		this.iframe_iebug.style.display = 'block';
		this.iframe_iebug.style.left = '0px';
		this.iframe_iebug.style.top = '0px';
		this.iframe_iebug.style.position = 'absolute';
		this.iframe_iebug.style.width = document.body.clientWidth;
		this.iframe_iebug.style.height = document.body.clientHeight;
		this.iframe_iebug.style.backgroundColor = '#000000';		
		this.iframe_iebug.style.filter = 'alpha(opacity=20)';
		this.iframe_iebug.style.opacity = '.20';
		this.iframe_iebug.style.zIndex = '0';	
	};	
	
	this._criaConteudo = function ()
	{
		this.div_conteudo = elementosInst.criar('div');
		
		this.div_conteudo.id = 'div_conteudo';
		this.div_conteudo.style.backgroundColor = '#FFFFFF';
		this.div_conteudo.style.border = '1px solid #000000';
		this.div_conteudo.style.filter = 'alpha(opacity=140)';
		this.div_conteudo.style.opacity = '100';
		this.div_conteudo.style.padding = '10px';

		this.div_conteudo.style.left = (document.body.clientWidth/2)-(this.largura/2) + 'px';
		this.div_conteudo.style.top = (document.body.clientHeight/2)-(this.altura/2) + this.top_acrescimo + 'px';
		this.div_conteudo.style.position = 'absolute';
		this.div_conteudo.style.width = this.largura + 'px';
		this.div_conteudo.style.height = this.altura + 'px';	
		this.div_conteudo.style.zIndex = '1';
		this.div_conteudo.style.overflow = 'auto';
	};	
	
	this._apendaElementos = function ()
	{
		document.body.appendChild(this.div_fundo);
		document.body.appendChild(this.iframe_iebug);
		document.body.appendChild(this.div_conteudo);

	};
	
	this.destroi = function ()
	{
		if(elementosInst.$('div_fundo'))
		{			
			elementosInst.remover('document.body','div_fundo');	
			elementosInst.remover('document.body','iframe_iebug');		
		}			

		if(elementosInst.$('div_conteudo'))
		{		
			elementosInst.remover('document.body','div_conteudo');		
		}
	
		document.body.scroll = "yes";
	};

	this.inserir = function()
	{
		//Se não existirem os divs 
		if(!elementosInst.$('div_fundo') && !elementosInst.$('div_conteudo'))
		{
			//Se forem passados altura e largura como argumentos
			if(arguments[0] && arguments[1])
			{
				this.largura = arguments[0];
				this.altura = arguments[1];
			}
			
			//Chama as funções internas
			this._criaFundo();
			this._criaConteudo();
			this._apendaElementos();
		}
	};
}


//var multi_selector; //Declara o objeto (global)

//criado em 16/07/2013 - carlos abreu
//Cria div popup de upload de arquivos
function popupUpload_grid(checkout)
{	
	this.metodo = 0; //0=upload normal;1=checkout;
	
	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();
	
	divPopupInst.altura = 450; //140
	divPopupInst.largura = 1000; //650
	divPopupInst.top_acrescimo = -50;
	
	divPopupInst.inserir();
	
	xajax_preencheNRDocumentos_grid(xajax.getFormValues('frm'),checkout);
		
}

//funções utilizada no Adicionar e Checkout
function startUpload(id)
{
	  document.getElementById('upload_'+id).innerHTML = '<img width="100px" src="../imagens/loader.gif" />';	
      document.getElementById('upload_'+id).style.visibility = 'visible';
      document.getElementById('txtup_'+id).style.visibility = 'hidden';
	  document.getElementById('delete_'+id).style.visibility = 'hidden';
	  
	  setTimeout('',3000);
	    
      return true;
}

//usado para dar o refresh na lista de arquivos
function dir_up()
{
	xajax_seta_checkin_checkout(document.getElementById('id_os').value);
	
	xajax_preencheArquivos(xajax.getFormValues('frm'));
	
	return true;
}

function stopUpload(success,id,filename,tamanho,erro,msg)
{
      var result = '';
	  
	  switch (erro)
	  {
		  case 0:
			  if (success == 1)
			  {
				 result = '<span class="labels">Concluído!</span>';
				 document.getElementById('txtup_'+id).innerHTML = filename;
				 document.getElementById('delete_'+id).style.visibility = 'visible';		 
			  }
			  else 
			  {
				 result = '<span class="labels">Erro!'+msg+'</span>';
			  }      
			  document.getElementById('upload_'+id).innerHTML = result;	  
			  document.getElementById('tam_'+id).innerHTML = tamanho;
			  document.getElementById('txtup_'+id).style.visibility = 'visible';  
		  break;
		  
		  case 1:
		  		alert("O documento existe no banco de dados. Utilize o recurso \"check-in\" para atualizá-lo.");
		  break;
		  
		  case 2:
		  		alert("Arquivo(s) adicionado(s) com sucesso.");
		  break;
		  
		  case 3:
		  		alert("Erro no banco de dados.");
		  break; 
		  
		  case 4:
		  		alert("Erro ao tentar criar a pasta Nr do Documento no servidor. (1)\n"+msg);
		  break;
		  
		  case 5:
		  		alert("ERRO. Diretório não foi criado.");
		  break;
		  
		  case 6:
		  		alert("O arquivo fornecido ("+filename+") não possui extensão. Altere o arquivo e tente novamente.");
		  break;
		  
		  case 7:
		  		alert("ERRO no upload do arquivo.");
		  break; 
	  }	  
    
      return true;   
}

function delUpload(id)
{
      document.getElementById('txtup_'+id).innerHTML = '<input class="caixa" name="myfile_'+id+'" type="file" size="30" />  <input type="submit" name="submitBtn" id="submitBtn" value="Upload" />';
	  document.getElementById('upload_'+id).innerHTML = "";
	  document.getElementById('delete_'+id).style.visibility = 'hidden';
	  document.getElementById('tam_'+id).innerHTML = "";
      return true;
}

//funções utilizada no comentarios
function startUpload_comentarios()
{
	  document.getElementById('inf_upload').innerHTML = '<img width="100px" src="../imagens/loader.gif" />';	
      document.getElementById('inf_upload').style.visibility = 'visible';
	  
	  setTimeout('',3000);
	    
      return true;
}

function stopUpload_comentarios(id_ged_versao,success,erro)
{
      var result = '';
	  
	  if (success == 1)
	  {
		 result = '<span class="labels">Concluído! '+erro+'</span>';
		 document.getElementById('upload_1').style.display = 'none';
	  }
	  else 
	  {
		 result = '<span class="labels">Erro! '+erro+'</span>';
	  }      
	  
	  document.getElementById('inf_upload').innerHTML = result;
	  
	  xajax_preencheVersoes_comentarios(id_ged_versao);	  
    
      return true;   
}


//Cria div popup de envio de arquivos ao Arquivo Técnico
function popupEnvia(id_os)
{
	//Instancia as classes
	divPopupInst = new divPopup();
	elementosInst = new elementos();
	
	divPopupInst.largura = 1000;
	divPopupInst.altura = 550;
	
	divPopupInst.inserir();
	
	xajax_preencheArquivosSol(id_os);
}

//propriedades ged pacotes
function popupVersoes_comentarios(id_ged_versao)
{
	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();
	
	divPopupInst.altura = 460;
	
	divPopupInst.largura = 820;
	
	divPopupInst.inserir();
	
	conteudo = '';
	
	conteudo = '<div id="div_titulo" class="labels"> </div>';
	conteudo += '<div> </div>';
	conteudo += '<div id="div_tab" style="width:100%; height:350px;">';
	//conteudo += '</div>';
		conteudo += '<div id="a1">';
			conteudo += '<form name="frm_prop" id="frm_prop" action="" method="post">';
			conteudo += '<div id="div_propriedades" name="div_propriedades"> </div>';
			conteudo += '<table border="0" width="100%">';			
			conteudo += '<tr><td colspan="2"> </td></tr>';
			conteudo += '<tr><td><input type="button" value="Alterar Propriedades" onclick="xajax_atualizaPropriedades(xajax.getFormValues(\'frm_prop\'));"></td>';
			conteudo += '</tr></table>';
			conteudo += '<input type="hidden" name="id_ged_versao" id="id_ged_versao" value="'+id_ged_versao+'">';
		conteudo += '</form></div>';
	
		conteudo += '<div id="a2">';
			conteudo += '<form name="frm_ver" id="frm_ver" action="" method="post">';
			conteudo += '<label class="labels">Versões:</label>';
			conteudo += '<div id="conteudo_versoes"> </div>';
		conteudo += '</form></div>';
			
		conteudo += '<div id="a3">';
			conteudo += '<table border="0" width="100%">';			
			conteudo += '<tr><td colspan="2"><input type="checkbox" name="chk_comentarios" id="chk_comentarios" value="1" onclick="if(this.checked){xajax.$(\'div_comentario\').style.display=\'inline\';xajax.$(\'upload_1\').style.display=\'inline\';}else{xajax.$(\'div_comentario\').style.display=\'none\';xajax.$(\'upload_1\').style.display=\'none\';}"><label class="labels">Incluir comentários</label></td></tr>';
			conteudo += '<tr><td colspan="2">';
			conteudo += '<form name="frm_teste" id="frm_teste" action="upload_comentarios.php?id_ged_versao='+id_ged_versao+'" target="upload_target" method="post" enctype="multipart/form-data" onsubmit="startUpload_comentarios();">';
			conteudo += '<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;display:none;"></iframe>';
			conteudo += '<div id="div_comentario" name="div_comentario" style="width:100%; height:180px; padding:10px; display:none; border: solid #CCCCCC 1px;overflow:auto;">';
			conteudo += '<div id="div_comentarios_existentes"> </div>';

			conteudo += '<input type="file" name="input_1" id="input_1"><img name="img_1" id="img_1" src="../imagens/add.png" style="cursor:pointer; margin-left:2px;" alt="Adicionar outro comentário" onclick="adiciona_input_file(\'div_comentario\');"><br>';
			conteudo += '</div><input type="submit" name="upload_1" style="display:none;" value="Upload"><br><p style="visibility:hidden;" id="inf_upload"> </p></form></td></tr>';
			conteudo += '</tr></table>';
		conteudo += '</div>';
		
		conteudo += '<div id="a4">';
			conteudo += '<table border="0" width="100%">';			
			conteudo += '<tr><td colspan="2">';
			conteudo += '<div id="div_desbloqueios"> </div>';
			conteudo += '<br></td></tr></table>';
		conteudo += '</div>';	
	conteudo += '</div>';
	conteudo += '<div id="div_voltar"> </div>';
	divPopupInst.div_conteudo.innerHTML = conteudo;
	
	var myTabbar;
	
	myTabbar = new dhtmlXTabBar("div_tab");
	
	myTabbar.addTab("a1_", "Propriedades", null, null, true);
	myTabbar.addTab("a2_", "Versões");
	myTabbar.addTab("a3_", "Comentários");
	myTabbar.addTab("a4_", "Desbloqueios");
	
	myTabbar.tabs("a1_").attachObject("a1");
	myTabbar.tabs("a2_").attachObject("a2");
	myTabbar.tabs("a3_").attachObject("a3");
	myTabbar.tabs("a4_").attachObject("a4");
	myTabbar.enableAutoReSize(true);
	
	xajax_preencheVersoes_comentarios(id_ged_versao);	
}

//criado em 23/07/2013 - carlos abreu
//Cria div popup de titulos de arquivos
function popupTitulos(id_ged_versao)
{
	RCmenuInst.destroi();	
	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();
	
	divPopupInst.altura = 250;
	
	divPopupInst.largura = 550;
	
	divPopupInst.inserir();
	
	conteudo = '<div id="div_tit"> </div>';
		
	divPopupInst.div_conteudo.innerHTML = conteudo;	
	
	xajax_preencheTitulos(id_ged_versao);	
}

//criado em 10/02/2014 - carlos abreu
//Cria div popup de titulos de arquivos
function popupSolDesBloq(id_ged_versao)
{	
	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();
	
	divPopupInst.altura = 250;
	
	divPopupInst.largura = 550;
	
	divPopupInst.inserir();
	
	conteudo = '<div id="div_desbloq"> </div>';
	
	divPopupInst.div_conteudo.innerHTML = conteudo;	
	
	xajax_sol_desbloquear(id_ged_versao);	
}

//Cria div popup de visualização das GRDs
function popupGRDs()
{	
	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();	
	
	divPopupInst.altura = 300;
	
	divPopupInst.largura = 500;
	
	divPopupInst.inserir();
	
	conteudo = '<form action="" method="post" name="frm_grds" id="frm_grds">';
	conteudo += '<div id="div_titulo" class="labels"> </div>';
	conteudo += '<div id="div_ordem" style="width:100%; text-align:right"><label class="labels">Ordenar por: </span><select name="ordem" id="ordem" class="caixa"><option value="" selected>NUMINT (padrão)</option><option value="1">NUMCLI</option></select></div>';
	conteudo += '<div> </div>';	
	conteudo += '<div id="conteudo_grds" style="height:150px; overflow:auto; border-style:solid; border-color:#999999;border-width:1px;"> </div>';
	conteudo += '<input type="hidden" name="id_ged_pacote" id="id_ged_pacote" value="">';
	conteudo += '<p><input type="button" value="Voltar" onclick="divPopupInst.destroi();"></p>';
	
	divPopupInst.div_conteudo.innerHTML = conteudo;
}

function popupBuscaAvancada(id_os,id_disciplina)
{	
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();
	
	divPopupInst.altura = screen.availHeight/1.5;
	
	divPopupInst.largura = screen.availWidth/1.1;
	
	divPopupInst.inserir();
	
	conteudo = '<form action="" method="post" name="frm_buscaavancada" id="frm_buscaavancada">';
	
	conteudo += '<input type="hidden" name="id_os_ant" id="id_os_ant" value="'+id_os+'">';
	conteudo += '<input type="hidden" name="id_disciplina_ant" id="id_disciplina_ant" value="'+id_disciplina+'">';
	
	conteudo += '<div id="div_titulo" class="labels"> </div>';
	conteudo += '<div id="div_tipo_busca" style="float:left;padding:2px;">';
	conteudo += '<div class="labels">Tipo de Busca</div><select name="tipo_busca" id="tipo_busca" class="caixa" onchange = xajax_preencheBuscaAvancada(this.options[this.selectedIndex].value);><option value="">SELECIONE</option><option value="1">PROJETO</option><option value="2">REFERÊNCIA</option></select>';
	conteudo += '</div>';
	
	conteudo += '<div id="div_id_cliente" style="float:left;padding:2px;">';
	conteudo += '<div class="labels">Cliente</div><select name="busca_id_cliente" id="busca_id_cliente" class="caixa" onchange = xajax_preenche_os_BuscaAvancada(xajax.getFormValues("frm_buscaavancada"));xajax.$("id_os").options[xajax.$("id_os").selectedIndex].value=this.value;xajax.$("btn_rel").disabled=false;"><option value="">TODOS</option></select>';
	conteudo += '</div>';
	
	conteudo += '<div id="div_id_os" style="float:left;padding:2px;">';
	conteudo += '<div class="labels">OS</div><select name="busca_id_os" id="busca_id_os" class="caixa" onchange = xajax_preenchedisciplina(this.options[this.selectedIndex].value,"");xajax.$("id_os").options[xajax.$("id_os").selectedIndex].value=this.value;xajax.$("btn_rel").disabled=false;><option value="">TODAS</option></select>';
	conteudo += '</div>';
	conteudo += '<div id="div_id_disciplina" style="float:left;">';
	conteudo += '<div class="labels">Disciplina</div><select name="busca_id_disciplina" id="busca_id_disciplina" class="caixa" onchange = xajax_preenchedocumentos(this.options[this.selectedIndex].value,xajax.$("busca_id_os").options[xajax.$("busca_id_os").selectedIndex].value,true);xajax.$("disciplina").options[xajax.$("disciplina").selectedIndex].value=this.value><option value="">TODAS</option></select>';
	conteudo += '</div>';
	conteudo += '<div id="div_CodAtividade">';
	conteudo += '<div class="labels">Atividade</div><select name="busca_CodAtividade" class="caixa"><option value="">TODAS</option></select>';
	conteudo += '</div>';
	conteudo += '<div id="div_titulo1" style="clear:left; float:left;">';
	conteudo += '<div class="labels">Busca</div><input type="text" name="busca_texto" class="caixa" size="70">';
	conteudo += '</div>';	
	conteudo += '<div> </div>';	
	conteudo += '</div>';	
	conteudo += '<div id="div_busca_resultados" style="width:1150px; height:400px; clear:left;"> </div>';	
	conteudo += '<p><input type="button" value="Buscar" onclick = xajax_buscaArquivosAvancada(xajax.getFormValues("frm_buscaavancada"));><input name="btn_relatorios" id="btn_rel" type="button" value="Relat&oacute;rios" onclick = popupRel(); disabled="disabled" /><input type="button" value="Voltar" onclick = xajax.$("id_os").options[xajax.$("id_os").selectedIndex].value=document.getElementById("id_os_ant").value;xajax.$("disciplina").options[xajax.$("disciplina").selectedIndex].value=document.getElementById("id_disciplina_ant").value;divPopupInst.destroi();></p>';
	
	conteudo += '</form>';	
	divPopupInst.div_conteudo.innerHTML = conteudo;	
}

//Cria efeito de "item selecionado" ao passar com mouse
function highlight(div)
{
	if(div.id.substring(0,1)=="a")
	{
		var array_itens = document.getElementById('div_arquivos').getElementsByTagName('div');
		var cor_fundo = '#FFFFFF'; //Comentar aqui
	}
	else if(div.id.substring(0,1)=="m")
	{
		var array_itens = document.getElementById('menu_div_fundo').getElementsByTagName('div');
		var cor_fundo = '#E6E6E6';
	}
	
	//Tira o highlight de todos os itens
	for(x=0;x<array_itens.length;x++)
	{
		array_itens[x].style.backgroundColor = cor_fundo;
		array_itens[x].style.color = '#000000';
	}

	//Determina o highlight do item clicado
	div.style.backgroundColor = '#1B4470';
	div.style.color = '#FFFFFF';
	
	//Coloca o nome do arquivo no hidden
	if(document.getElementById('nome_arquivo'))
	{
		document.getElementById('nome_arquivo').value = div.innerHTML;
	}
}

function pulaCampo(campo, tecla)
{
	array_campo = campo.name.split("_");
	str_campo = array_campo[0];
	str_id = array_campo[1];
	
	array_grp_campos = new Array();
	
	array_grp = document.getElementsByTagName("input");
	
	for(x=0;x<array_grp.length;x++)
	{
		if(array_grp[x].name.indexOf(str_campo,0)==0)
		{
			array_grp_campos[array_grp_campos.length] = array_grp[x].name;
			
			if(array_grp[x].name==campo.name)
			{
				id_atual = array_grp_campos.length-1;
			}
		}
	}

	switch(tecla)
	{
		case 38:	//tecla acima
			if(id_atual>0)
			{
				document.getElementById(array_grp_campos[id_atual-1]).focus();
			}
		break;	
		
		
		case 40: //tecla abaixo
			if(id_atual<array_grp_campos.length-1)
			{
				document.getElementById(array_grp_campos[id_atual+1]).focus();
			}
		break;
	}
}

//Cria o menu que é ativado com um "right click"
function RCmenu()
{
	elementosInst = new elementos();
	
	this.item_nr = 0;
	this.corfundo = '#E6E6E6';
	this.largura = '100px';
	this.altura = '70px';
	
	this._criaFundo = function (x,y)
	{
		this.div_fundo = elementosInst.criar('div');		
		this.div_fundo.id = 'menu_div_fundo';
		this.div_fundo.style.backgroundColor = this.corfundo;
		this.div_fundo.style.padding = '2px';
		this.div_fundo.style.borderStyle = 'outset';
		this.div_fundo.style.borderWidth = '2px';
		this.div_fundo.style.position = 'absolute';
		this.div_fundo.style.width = this.largura;
		this.div_fundo.style.height = 'auto';
		this.div_fundo.style.left = x;
		this.div_fundo.style.top = y;
		//inserido por carlos abreu - 16/09/2010
		this.div_fundo.style.zIndex = '100';
		
	};
	
	this._criaItem = function (descricao, evento, status, borda_cima)
	{
		this.div_item = elementosInst.criar('div');		
		this.div_item.id = 'm_div_item_' + this.item_nr;
		this.div_item.innerHTML = descricao;
		this.div_item.className = 'fonte_11';
		this.div_item.style.fontSize = '13px';
		this.div_item.style.color = '#000000';
		this.div_item.onmouseover = function () { highlight(this); }
		
		//Separador (borda em cima)
		if(borda_cima==1) //arguments[2]==1
		{
			this.div_item.style.borderTopStyle = 'groove';
			this.div_item.style.borderWidth = '2px';			
		}

		if(status==1)
		{
			this.div_item.onclick = evento;	
		}
		else
		{
			this.div_item.disabled = true;
		}
		
		this.div_fundo.appendChild(this.div_item);

		this.item_nr++;
	};	
	
	this._apendaMenu = function()
	{
		document.body.appendChild(this.div_fundo);		
	};
	
	this._insereItens = function (array_itens)
	{		
		for(i=0;i<array_itens.length;i++)
		{
			this._criaItem(array_itens[i][0],array_itens[i][1],array_itens[i][2],array_itens[i][3]);	
		}
	};
	
	this.insere = function (x,y,array_itens)
	{
		this.destroi();
		this.altura = (array_itens.length * 11.6)+'px';		
		this._criaFundo(x,y);
		this._insereItens(array_itens);
		this._apendaMenu();
		
	};
	
	this.destroi = function ()
	{
		if(elementosInst.$('menu_div_fundo'))
		{
			elementosInst.remover('document.body','menu_div_fundo');
		}		
	};	
}

function buscaMenu(string,id)
{
	destino = document.getElementById(id);

	buscamenuInst = new RCmenu();

	if(document.getElementById('menu_div_fundo'))
	{
		buscamenuInst.destroi();	
	}
	else
	{
		buscamenuInst.corfundo = '#FFFFFF';
		buscamenuInst.largura = '200px';
		buscamenuInst.altura = '100px';
		buscamenuInst._criaFundo(destino.offsetLeft,destino.offsetTop+20);
		buscamenuInst.div_fundo.style.overflow = 'auto';
		buscamenuInst._apendaMenu();
		xajax_buscaArquivos(string);
	}
}

function popupPropriedades(id_ged_versao)
{
	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();
	
	divPopupInst.inserir(520,460);
	
	RCmenuInst.destroi();
	
	xajax_preenchePropriedades(id_ged_versao);	
}

function popupPropriedadesRef(id)
{
	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();
	
	divPopupInst.inserir(500,420);
	
	RCmenuInst.destroi();
	
	xajax_preenchePropriedadesRef(id);	
}

//cria popup dos botões de relatório
function popupRel()
{
	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();	
	
	divPopupInst.inserir(150,220);

	conteudo = '';
	conteudo += '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
	conteudo += '<tr>';
	conteudo += '<td valign="middle" ><input name="btn_listadocs_dvm" id="btn_listadocs_dvm" class="class_botao" type="button" value="Lista Docs. Interno" onclick = xajax.$("ordem_lista_documentos").value="numdvm";xajax.$("frm").target="_blank";xajax.$("frm").action="ged_lista_documentos.php";xajax.$("frm").submit(); /></td>';
	conteudo += '</tr>';
	conteudo += '<tr>';
	conteudo += '<td valign="middle" ><input name="btn_listadocs_emit" id="btn_listadocs_emit" class="class_botao" type="button" value="Lista Docs. Emitidos" onclick = xajax.$("ordem_lista_documentos").value="emitidos";xajax.$("chk_emitidos").value="1";xajax.$("frm").target="_blank";xajax.$("frm").action="ged_lista_documentos.php";xajax.$("frm").submit(); /></td>';
	conteudo += '</tr>';
	conteudo += '<tr>';
	conteudo += '<td valign="middle" ><input name="btn_listadocs_cli" id="btn_listadocs_cli" class="class_botao" type="button"  value="Lista Docs. Cliente" onclick = xajax.$("ordem_lista_documentos").value="numcliente";xajax.$("frm").target="_blank";xajax.$("frm").action="ged_lista_documentos.php";xajax.$("frm").submit(); /></td>';
	conteudo += '</tr>';
	conteudo += '<tr>';
	conteudo += '<td valign="middle" ><input name="btn_listadocs_xl" id="btn_listadocs_xl" class="class_botao" type="button" value="Lista Docs.&Excel" onclick = xajax.$("chk_excel").value="1";xajax.$("frm").target="_blank";xajax.$("frm").action="ged_lista_documentos.php";xajax.$("frm").submit();xajax.$("chk_excel").value=""; /></td>';
	conteudo += '</tr>';
	conteudo += '<tr>';
	conteudo += '<td valign="middle" ><input name="btn_listadocsref" id="btn_listadocsref" class="class_botao" type="button" value="Lista Docs. Ref." onclick = xajax.$("frm").target="_blank";xajax.$("frm").action="../relatorios/ged_lista_documentos_ref.php";xajax.$("frm").submit(); /></td>';
	conteudo += '</tr>';
	conteudo += '<tr>';
	conteudo += '<td valign="middle" ><input name="btn_grd" id="btn_grd" type="button" class="class_botao" value="GRD" onclick = xajax.$("frm").target="_blank";xajax.$("frm").action="relatorios/rel_ged_grd.php";xajax.$("frm").submit(); /></td>';
	conteudo += '</tr>';
	conteudo += '<tr>';
	conteudo += '<td valign="middle" ><input name="btn_voltar" id="btn_voltar" type="button" class="class_botao" value="Voltar" onclick = divPopupInst.destroi(); /></td>';
	conteudo += '</tr>';
	conteudo += '<tr>';
	conteudo += '<td valign="middle" > </td>';
	conteudo += '</tr>';

	divPopupInst.div_conteudo.innerHTML = conteudo;
}

function popupComentarios(id_ged_versao)
{
	divPopupInst.destroi();
	
	//Instancia as classes
	divPopupInst = new divPopup();

	divPopupInst.inserir(500,420);
	
	conteudo = '<div id="div_cabecalho_comentarios"> </div>';

	divPopupInst.div_conteudo.innerHTML = conteudo;
		
	xajax_preencheComentarios(id_ged_versao);
}

function seta_combo(indice,formulario)
{
	for ( i=0; i < document.forms[formulario].elements.length; i++) 
	{
         if ( document.forms[formulario].elements[i].lang == 'sel' ) 
		 { 
             document.forms[formulario].elements[i].selectedIndex = indice-1;
         }
	}
}