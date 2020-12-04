/*		Include de rotinas do sistema GED
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		Local/Nome do arquivo:
		../arquivotec/ged.js
		�ltima altera��o: 15/10/2010
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
	img_file.src = '../images/silk/add.gif';
	img_file.style.cursor = 'pointer';
	img_file.style.marginLeft = '2px';
	img_file.alt = 'Adicionar outro coment�rio';
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
this.top_acrescimo = 0; //Dist�ncia do topo do div interno (branco), valores negativos aproximam do topo

	this._criaFundo = function ()
	{

		this.div_fundo = elementosInst.criar('div');
		this.iframe_iebug = elementosInst.criar('iframe');
		
		this.div_fundo.id = 'div_fundo';
		this.div_fundo.style.backgroundColor = '#000000';
		//this.div_fundo.style.background = 'transparent';
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
		/* Iframe para corrigir um BUG com combos que n�o respeitam zIndex no IE6 */
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
//		this.div_conteudo.innerHTML = conteudo;
		this.div_conteudo.style.zIndex = '1';
		this.div_conteudo.style.overflow = 'auto';

		
	};	
	
	this._apendaElementos = function ()
	{
		//document.getElementById('barra_menu').style.display = 'none';
		
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
		//Se n�o existirem os divs 
		if(!elementosInst.$('div_fundo') && !elementosInst.$('div_conteudo'))
		{
			//Se forem passados altura e largura como argumentos
			if(arguments[0] && arguments[1])
			{
				this.largura = arguments[0];
				this.altura = arguments[1];
			}
			
			//Chama as fun��es internas
			this._criaFundo();
			this._criaConteudo();
			this._apendaElementos();
		}
	};
}

function divPopupAv()
{

//Instancia a classe
elementosInst = new elementos();

this.largura = 300; //Altura do div interno (branco)
this.altura = 120; //Largura do div interno (branco)
this.top_acrescimo = 0; //Dist�ncia do topo do div interno (branco), valores negativos aproximam do topo

	this._criaFundo = function ()
	{

		this.div_fundo = elementosInst.criar('div');
		this.iframe_iebug = elementosInst.criar('iframe');
		
		this.div_fundo.id = 'div_fundo1';
		this.div_fundo.style.backgroundColor = '#000000';
		//this.div_fundo.style.background = 'transparent';
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
		/* Iframe para corrigir um BUG com combos que n�o respeitam zIndex no IE6 */
		this.iframe_iebug.id = 'iframe_iebug1';
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
		
		this.div_conteudo.id = 'div_conteudo1';
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
		if(elementosInst.$('div_fundo1'))
		{			
			elementosInst.remover('document.body','div_fundo1');	
			elementosInst.remover('document.body','iframe_iebug1');		
		}		

		if(elementosInst.$('div_conteudo1'))
		{		
			elementosInst.remover('document.body','div_conteudo1');		
		}
		
		document.body.scroll = "yes";

	};

	this.inserir = function()
	{
		//Se n�o existirem os divs 
		if(!elementosInst.$('div_fundo1') && !elementosInst.$('div_conteudo1'))
		{
			//Se forem passados altura e largura como argumentos
			if(arguments[0] && arguments[1])
			{
				this.largura = arguments[0];
				this.altura = arguments[1];
			}
			
			//Chama as fun��es internas
			this._criaFundo();
			this._criaConteudo();
			this._apendaElementos();
		}
	};

}

var multi_selector; //Declara o objeto (global)

function input_file_upload(input_pai)
{
	var teste;
	
	var inc_revisa = 1;
	
	var id_ged_versao = 0;
	
	this.grid_numdvmsel = new dhtmlXGridObject();
	
	this.grid_numdvmsel.attachToObject(input_pai);
	this.grid_numdvmsel.imgURL = "../includes/dhtmlx/dhtmlxGrid/codebase/imgs/";
	
	this.grid_numdvmsel.setHeader("ID, Documento, Arquivo, Excluir");
	
	this.grid_numdvmsel.setInitWidths("0,400,250,50");
	
	this.grid_numdvmsel.setColAlign("left,left,left,right");
	
	this.grid_numdvmsel.setColTypes("ro,ro,ro,ro");
	
	this.grid_numdvmsel.setColSorting("str,str,str,str");
	
	//this.grid_numdvmsel.setHeader("ID, Documento, Arquivo, Aumenta Rev., Excluir");
	
	//this.grid_numdvmsel.setInitWidths("0,400,250,100,50"); //0,100,400,50,370
	
	//this.grid_numdvmsel.setColAlign("left,left,left,right,right");
	//this.grid_numdvmsel.setColTypes("ro,ro,ro,ro,ro");	
	//this.grid_numdvmsel.setColSorting("str,str,str,str,str");
	
	this.grid_numdvmsel.enableAutoHeight(true,200);	
	
	this.grid_numdvmsel.setSkin("modern");
		
	this.grid_numdvmsel.init();

	// Where to write the list
	this.input_pai = input_pai;
	// How many elements?
	this.count = 0;
	// How many elements?
	this.id = 0;
	
	this.numdvm = 0;
	
	this.novo_id = 0;
	
	this.name = '';
	
	this.array_options = new Array();
	
	this.sel_numdvm = document.getElementById('nr_documento');

	/**
	 * Add a new file input element
	 */
	this.addElement = function(element)
	{

		// Make sure it's a file input element
		if(element.tagName == 'INPUT' && element.type == 'file')
		{
			if(document.getElementById('nr_documento').options.length>0)
			{
				idx_selecionar = this.sel_numdvm.selectedIndex;
				
				//Carlos Abreu - 25/05/2010
				//SEPARA O ID_NUMDVM DO ID_GED_VERSAO
				sep_array = this.sel_numdvm.options[this.sel_numdvm.selectedIndex].value.split('#');
				
				this.id_numdvm = sep_array[0];
				
				this.txt_numdvm = this.sel_numdvm.options[this.sel_numdvm.selectedIndex].text;

				this.sel_numdvm.remove(this.sel_numdvm.selectedIndex);
				
				//this.sel_numdvm.options.length-1
				if(this.sel_numdvm.options.length>1 && idx_selecionar<this.sel_numdvm.options.length)
				{
					this.sel_numdvm.options[idx_selecionar].selected = true;
				}
			}

			// Add reference to this object
			element.multi_selector = this;

			// What to do when a file is selected
			element.onchange = function()
			{
				novo_id = this.multi_selector.retornaNovoId();			
			
				// New file input
				var new_element = document.createElement('input');
				
				new_element.type = 'file';
			
				this.name_tmp = 'arquivo_' + document.getElementById('nr_documento').options[document.getElementById('nr_documento').selectedIndex].value;

				//Carlos Abreu - 25/05/2010
				//SEPARA O NOME ARQUIVO DO ID_GED_VERSAO
				teste = this.name_tmp.split('#');
				
				this.name = teste[0];
				
				id_ged_versao = teste[1];				
				
				//habilita o combobox de aumenta revis�o no grid
				if(teste[1])
				{
					inc_revisa = 1;	
				}
				else
				{
					inc_revisa = 0;
				}				
		
				//proximo_id = parseInt(novo_id)+1;
				this.id = 'file_'+novo_id;

				// Add new element
				this.parentNode.insertBefore(new_element, this);

				// Apply 'update' to element
				this.multi_selector.addElement(new_element);

				// Update list
				this.multi_selector.addListRow(this);

				this.style.display = 'none';
				
				if(this.multi_selector.sel_numdvm.options.length==0)
				{
					new_element.disabled=true;
				}

				zebraSelect(document.getElementById('nr_documento'));
			};

			// File element counter
			this.count++;
			// Most recent element
			this.current_element = element;
			
		} 
		else 
		{
			// This can only be applied to file input elements!
			alert( 'Error: not a file input element' );
		};

	};
	
	this.addOptions = function ()
	{
		for(x=0;x<this.array_options.length;x++)
		{
			document.getElementById('nr_documento').options[document.getElementById('nr_documento').length] = new Option(this.array_options[x][0],this.array_options[x][1]);
		}
	};

	this.excluir = function(id) 
	{
		if(confirm('Confirma a exclus�o do item da lista?'))
		{
			//Antes de excluir, � necess�rio "devolver" o NumDVM ao <select>
			this.sel_numdvm.options[this.sel_numdvm.options.length] = new Option(this.grid_numdvmsel.cells(id,1).getValue(),this.grid_numdvmsel.cells(id,0).getValue());

			//Exclui do GRID
			this.grid_numdvmsel.deleteRow(id); //Remove o item do Grid
			
			//Exclui o input file 
			multi_selector.current_element.parentNode.removeChild(document.getElementById('file_'+id)); //Remove o input file
			
			zebraSelect(document.getElementById('nr_documento'));
		}
	};

	/**
	 * Add a new row to the list of files
	 */
	this.addListRow = function(element)
	{
		novo_id = this.retornaNovoId();
		
		var img_excluir = '<img src="../images/buttons_action/apagar.gif" onclick="multi_selector.excluir(\''+novo_id+'\');" style="cursor:pointer;" title="Excluir da lista">';
		
		if(inc_revisa==0)
		{
			desabilita = 'disabled';
		}
		else
		{
			desabilita = '';
		}		
		
		//var cb_rev = '<select name="rev_'+this.id_numdvm+'" id="rev_'+this.id_numdvm+'" class="caixa" '+desabilita+' ><option value="1">SIM</option><option value="0">N�O</option></select>';
	
		valor_inputfile = element.value.split("\\")[element.value.split("\\").length-1];
		
		//var valores = new Array(this.id_numdvm,this.txt_numdvm,valor_inputfile,cb_rev,img_excluir);
		
		var valores = new Array(this.id_numdvm,this.txt_numdvm,valor_inputfile,img_excluir);

		//Adiciona o registro
		this.grid_numdvmsel.addRow(novo_id,valores);
	};
	
	this.retornaRowIds = function() 
	{
		var rows_id = this.grid_numdvmsel.getAllRowIds();
	
		var array_rows_id = new Array();
		
		if(rows_id.length>0)
		{
			var array_rows_id = rows_id.split(",");
		}
		
		return array_rows_id;
	};
	
	this.retornaNovoId = function ()
	{		
		var array_rows_id = this.retornaRowIds();
		
		var num_rows;
		
		if(array_rows_id[0])
		{
			this.novo_id = parseInt(array_rows_id[array_rows_id.length-1])+1;
		}
		else
		{
			this.novo_id = 1;
		}
		
		return this.novo_id;
	};
}


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
	
	conteudo = '<div id="div_nrdocs">&nbsp;</div>';
	
	conteudo += '<tr><td><input type="button" name="btn_checkout_voltar" id="btn_checkout_voltar" value="Voltar" onclick="divPopupInst.destroi();dir_up();" class="fonte_botao"></td><td>&nbsp;</td></tr>';
		
	divPopupInst.div_conteudo.innerHTML = conteudo;	
	
	xajax_preencheNRDocumentos_grid(xajax.getFormValues('frm_ged'),checkout);
	
}

//fun�oes utilizada no Adicionar e Checkout
function startUpload(id)
{
	  document.getElementById('upload_'+id).innerHTML = '<img width=\"100px\" src=\"../images/loader.gif\" />';	
      document.getElementById('upload_'+id).style.visibility = 'visible';
      document.getElementById('txtup_'+id).style.visibility = 'hidden';
	  document.getElementById('delete_'+id).style.visibility = 'hidden';
	  
	  setTimeout('',3000);
	    
      return true;
}

//usado para dar o refresh na lista de arquivos
function dir_up()
{
	//xajax_preencheArquivos(document.getElementById('diretorio').value);
	
	//xajax_buscaArquivosInicial(xajax.getFormValues('frm_ged'));
	
	xajax_seta_checkin_checkout(document.getElementById('id_os').value);
	
	xajax_preencheArquivos(xajax.getFormValues('frm_ged'));
	
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
				 result = '<span class="labels">Conclu�do!<\/span>';
				 document.getElementById('txtup_'+id).innerHTML = filename;
				 document.getElementById('delete_'+id).style.visibility = 'visible';		 
			  }
			  else {
				 result = '<span class="labels">Erro!'+msg+'<\/span>';
			  }      
			  document.getElementById('upload_'+id).innerHTML = result;	  
			  document.getElementById('tam_'+id).innerHTML = tamanho;
			  document.getElementById('txtup_'+id).style.visibility = 'visible';  
		  break;
		  
		  case 1:
		  		alert("O documento existe no banco de dados. Utilize o recurso \"check-in\" para atualiz�-lo.");
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
		  		alert("ERRO. Diret�rio n�o foi criado.");
		  break;
		  
		  case 6:
		  		alert("O arquivo fornecido ("+filename+") n�o possui extens�o. Altere o arquivo e tente novamente.");
		  break;
		  
		  case 7:
		  		alert("ERRO no upload do arquivo.");
		  break; 
	  }
	  
    
      return true;   
}

function delUpload(id)
{
      document.getElementById('txtup_'+id).innerHTML = '<input class="caixa" name="myfile_'+id+'" type="file" size="30" />&nbsp;&nbsp;<input type="submit" name="submitBtn" class="caixa" value="Upload" />';
	  document.getElementById('upload_'+id).innerHTML = "";
	  document.getElementById('delete_'+id).style.visibility = 'hidden';
	  document.getElementById('tam_'+id).innerHTML = "";
      return true;
}

//fun�oes utilizada no comentarios
function startUpload_comentarios()
{
	  document.getElementById('inf_upload').innerHTML = '<img width=\"100px\" src=\"../images/loader.gif\" />';	
      document.getElementById('inf_upload').style.visibility = 'visible';
	  
	  setTimeout('',3000);
	    
      return true;
}

function stopUpload_comentarios(id_ged_versao,success,erro)
{
      var result = '';
	  
	  if (success == 1)
	  {
		 result = '<span class="labels">Conclu�do! '+erro+'</span>';
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


//fun�oes utilizada no desbloquio
/*
function startUpload_desbloqueio()
{
	  document.getElementById('inf_upload').innerHTML = '<img width=\"100px\" src=\"../images/loader.gif\" />';	
      document.getElementById('inf_upload').style.visibility = 'visible';
	  
	  setTimeout('',3000);
	    
      return true;
}

function stopUpload_desbloqueio(id_ged_versao,success,erro)
{
      var result = '';
	  
	  if (success == 1)
	  {
		 result = '<span class="labels">Conclu�do! '+erro+'</span>';
		 document.getElementById('upload_1').style.display = 'none';
	  }
	  else 
	  {
		 result = '<span class="labels">Erro! '+erro+'</span>';
	  }      
	  
	  document.getElementById('inf_upload').innerHTML = result;
	  
      return true;   
}
*/

//end fun��es

function zebraSelect(obj)
{	
	obj_options = obj.options;
	
	for (x=0;x<obj_options.length;x++)
	{
		if(x%2)
		{
			obj_options[x].style.backgroundColor = '#EDEDED';
		}
		else
		{
			obj_options[x].style.backgroundColor = '#FFFFFF';				
		}
	}	
}

//Cria div popup de upload de arquivos de referencia
function popupUploadRef(caminho)
{
	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();
	
	divPopupInst.altura = 160;
	
	divPopupInst.inserir();
		
	conteudo = '<form name="arquivos" action="ged.php" onSubmit="xajax.upload(\'uploadArquivoRef\',\'arquivos\');" method="post" enctype="multipart/form-data">';
	conteudo += '<div class="fonte_descricao_campos">Digite a revis�o:</div>';
	conteudo += '<div>';
	conteudo += '<input type="text" name="revisao" id="revisao" size="2" maxlength="2" class="caixa">';
	conteudo += '</div>';	
	conteudo += '<span class="fonte_descricao_campos">Selecione o arquivo:</span>';
	conteudo += '<input type="file" name="arquivo" id="arquivo">';
	//conteudo += '<input type="hidden" name="caminho" value="'+elementosInst.$('caminho').value+'">';	
	//conteudo += '<input type="text" size="100" name="caminho" value="'+caminho+'">';
	conteudo += '<p><input type="submit" value="Enviar" class="fonte_botao"><input type="button" value="Voltar" onclick="divPopupInst.destroi();" class="fonte_botao"></p>';
	conteudo += '</form>';
	
	divPopupInst.div_conteudo.innerHTML = conteudo;
}

//Cria div popup de envio de arquivos ao Arquivo T�cnico
function popupEnvia(id_os)
{
	//alert(id_os);
	
	//Instancia as classes
	divPopupInst = new divPopup();
	elementosInst = new elementos();
	
	divPopupInst.largura = 1000;
	divPopupInst.altura = 550;
	
	divPopupInst.inserir();
	
	xajax_preencheArquivosSol(id_os);
}

//Cria div popup de visualiza��o/edi��o de vers�es de arquivos
//obsoleto - 13/02/2014
/*
function popupVersoes()
{
	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();
	
	divPopupInst.altura = 600;
	
	divPopupInst.largura = 550;
	
	divPopupInst.inserir();
	
	conteudo = '<div id="div_titulo" class="fonte_descricao_campos">&nbsp;</div>';
	conteudo += '<div>&nbsp;</div>';
	conteudo += '<div id="a_tabbar" mode="top" class="dhtmlxTabBar" imgpath="../includes/dhtmlx/dhtmlxTabbar/codebase/imgs/" margin="5" style="width:100%; height:520px; margin-top:5px; margin-right:5px;" tabstyle="modern" skinColors="#F1F4F5,#F1F4F5">';
	conteudo += '</div>';
		conteudo += '<div id="a1_tab" name="Propriedades" style="margin-left:3px">';
			conteudo += '<form action="ged_pacotes_v2.php" onSubmit="xajax.upload(\'atualizaPropriedades\',\'arquivos\');" method="post" enctype="multipart/form-data" name="arquivos">';
			conteudo += '<div>&nbsp;</div>';
			conteudo += '<div id="div_complemento" name="div_complemento">&nbsp;</div>';		
			conteudo += '<table border="0" width="100%">';
			conteudo += '<tr><td width="30%" class="td_sp"><label class="label_descricao_campos">Data devolu��o</label><BR>';
			conteudo += '<input name="data_devolucao" type="text" class="caixa" id="data_devolucao" size="8" onKeyPress="transformaData(this, event);"></td>';
			conteudo += '<td width="30%" class="td_sp"><label class="label_descricao_campos">Status devolu��o</label><BR>';
			conteudo += '<select name="status_devolucao" id="status_devolucao" class="caixa">';
			conteudo += '<option value="">SELECIONE</option>';
			conteudo += '<option value="A">APROVADO</option>';
			conteudo += '<option value="AC">APROVADO / COMENT�RIOS</option>';
			conteudo += '<option value="C">CANCELADO</option>';
			conteudo += '<option value="N">N�O APROVADO</option>';
			conteudo += '<option value="PI">PARA INFORMA��O</option>';
			conteudo += '<option value="NP">COMENT�RIO N�O PROCEDENTE</option>';
			conteudo += '</select></td>';
			conteudo += '<td width="40%">&nbsp;</td></tr>';
			conteudo += '<tr><td colspan="2">&nbsp;</td></tr>';
			conteudo += '<tr><td colspan="2"><input type="checkbox" name="chk_comentarios" id="chk_comentarios" value="1" onclick="if(this.checked){xajax.$(\'div_comentario\').style.display=\'inline\';}else{xajax.$(\'div_comentario\').style.display=\'none\';}"><span class="fonte_descricao_campos">Incluir coment�rios</span></td></tr>'; //if(this.checked){xajax.$(\'div_comentario\').style.visibility=\'visible\';}else{xajax.$(\'div_comentario\').style.visibility=\'hidden\';}
			conteudo += '<tr><td colspan="2"><div id="div_comentario" name="div_comentario" style="width:100%; height:180px; padding:10px; display:none; border: solid #CCCCCC 1px;overflow:auto;"><div id="div_comentarios_existentes">&nbsp;</div><input type="file" name="input_1" id="input_1"><img name="img_1" id="img_1" src="../images/silk/add.gif" style="cursor:pointer; margin-left:2px; " alt="Adicionar outro coment�rio" onclick="adiciona_input_file(\'div_comentario\');"></div></td></tr>';
			conteudo += '<tr><td><input type="submit" class="fonte_botao" value="Alterar Propriedades"></td>';
			conteudo += '<td><input type="hidden" name="id_ged_versao" id="id_ged_versao" value=""><input type="button" value="Voltar" onclick="divPopupInst.destroi();" class="fonte_botao"></td>';
			conteudo += '</tr></table>';		
			conteudo += '</form>';
		conteudo += '</div>';
	
		conteudo += '<div id="a2_tab" name="Vers�es" style="margin-left:3px">';
			conteudo += '<form action="" method="post" name="frm_versoes">';	
			conteudo += '<div>&nbsp;</div>';
			conteudo += '<label class="label_descricao_campos">Vers�es:</label>';
			conteudo += '<div id="div_cabecalho" style="background:#E6E6E6; width:100%;">';
			conteudo +=	'<span id="cabecalho_espacamento" class="cell1" style="width:47%;border-style:outset; border-right-color:#999999; border-bottom-color: #999999; border-width:2px;padding:2px;">Nome do Arquivo</span>';
			conteudo +=	'<span id="cabecalho_espacamento" class="cell1" style="width:5%;border-style:outset; border-right-color:#999999; border-bottom-color: #999999; border-width:2px;padding:2px;">Rev</span>';
			conteudo +=	'<span id="cabecalho_espacamento" class="cell1" style="width:5%;border-style:outset; border-right-color:#999999; border-bottom-color: #999999; border-width:2px;padding:2px;">Ver</span>';
			conteudo +=	'<span id="cabecalho_espacamento" class="cell1" style="width:20%;border-style:outset; border-right-color:#999999; border-bottom-color: #999999; border-width:2px;padding:2px;">Autor</span>';
			conteudo +=	'<span id="cabecalho_espacamento" class="cell1" style="width:20%;border-style:outset; border-right-color:#999999; border-bottom-color: #999999; border-width:2px;padding:2px;">Editor</span>';
			conteudo += '</div>';		
			conteudo += '<div id="conteudo_versoes" style="height:150px; overflow:auto; border-style:solid; border-color:#999999;border-width:1px;">&nbsp;</div>';
			conteudo += '<input type="hidden" name="id_ged_arquivo" id="id_ged_arquivo" value="">';
			conteudo += '<p><input type="button" onclick="if(confirm(\'Confirma as altera��es feitas nas vers�es?\')) { xajax_atualizaVersoes(xajax.getFormValues(\'frm_versoes\')); }" value="Alterar vers�es" class="fonte_botao"><input type="button" value="Voltar" onclick="divPopupInst.destroi();" class="fonte_botao"></p>';
			conteudo += '</form>';	
		conteudo += '</div>';
	
	divPopupInst.div_conteudo.innerHTML = conteudo;
	
	tabbar=new dhtmlXTabBar("a_tabbar");
	
	tabbar.setImagePath("../includes/dhtmlx/dhtmlxTabbar/codebase/imgs/");
	
	tabbar.addTab("a1","Propriedades","100px");
	tabbar.addTab("a2","Vers�es","100px");
	
	tabbar.setContent("a1","a1_tab");
	tabbar.setContent("a2","a2_tab");
	tabbar.setStyle("modern");
	
	tabbar.setTabActive("a1");
}
*/

function popupVersoes_comentarios(id_ged_versao)
{
	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();
	
	divPopupInst.altura = 600;
	
	divPopupInst.largura = 800;
	
	divPopupInst.inserir();
	
	conteudo = '<div id="div_titulo" class="fonte_descricao_campos">&nbsp;</div>';
	conteudo += '<div>&nbsp;</div>';
	conteudo += '<div id="a_tabbar" mode="top" class="dhtmlxTabBar" imgpath="../includes/dhtmlx/dhtmlxTabbar/codebase/imgs/" margin="5" style="width:100%; height:520px; margin-top:5px; margin-right:5px;" tabstyle="modern" skinColors="#F1F4F5,#F1F4F5">';
	conteudo += '</div>';
		conteudo += '<div id="a1_tab" name="Propriedades" style="margin-left:3px">';
			conteudo += '<form name="frm_prop" id="frm_prop" action="" method="post">';
			conteudo += '<div id="div_propriedades" name="div_propriedades">&nbsp;</div>';
			conteudo += '<table border="0" width="100%">';			
			conteudo += '<tr><td colspan="2">&nbsp;</td></tr>';
			conteudo += '<tr><td><input type="button" class="fonte_botao" value="Alterar Propriedades" onclick="xajax_atualizaPropriedades(xajax.getFormValues(\'frm_prop\'));">&nbsp;';
			conteudo += '<input type="button" value="Voltar" onclick="divPopupInst.destroi();" class="fonte_botao"></td>';
			conteudo += '</tr></table>';
			conteudo += '<input type="hidden" name="id_ged_versao" id="id_ged_versao" value="'+id_ged_versao+'">';
		conteudo += '</form></div>';
		
		conteudo += '<div id="a2_tab" name="Vers�es" style="margin-left:3px">';
			//conteudo += '<div id="div_versoes" name="div_versoes">&nbsp;</div>';
			conteudo += '<form name="frm_ver" id="frm_ver" action="" method="post">';
			conteudo += '<label class="label_descricao_campos">Vers�es:</label>';
			conteudo += '<div id="div_cabecalho" style="background:#E6E6E6; width:100%;">';
			conteudo +=	'<span id="cabecalho_espacamento" class="cell1" style="width:47%;border-style:outset; border-right-color:#999999; border-bottom-color: #999999; border-width:2px;padding:2px;">Nome do Arquivo</span>';
			conteudo +=	'<span id="cabecalho_espacamento" class="cell1" style="width:5%;border-style:outset; border-right-color:#999999; border-bottom-color: #999999; border-width:2px;padding:2px;">Rev</span>';
			conteudo +=	'<span id="cabecalho_espacamento" class="cell1" style="width:5%;border-style:outset; border-right-color:#999999; border-bottom-color: #999999; border-width:2px;padding:2px;">Ver</span>';
			conteudo +=	'<span id="cabecalho_espacamento" class="cell1" style="width:20%;border-style:outset; border-right-color:#999999; border-bottom-color: #999999; border-width:2px;padding:2px;">Autor</span>';
			conteudo +=	'<span id="cabecalho_espacamento" class="cell1" style="width:20%;border-style:outset; border-right-color:#999999; border-bottom-color: #999999; border-width:2px;padding:2px;">Editor</span>';
			conteudo += '</div>';		
			conteudo += '<div id="conteudo_versoes" style="height:150px; overflow:auto; border-style:solid; border-color:#999999;border-width:1px;">&nbsp;</div>';
			conteudo += '<input type="hidden" name="id_ged_arquivo" id="id_ged_arquivo" value="">';
			conteudo += '<p><input type="button" onclick="if(confirm(\'Confirma as altera��es feitas nas vers�es?\')) { xajax_atualizaVersoes(xajax.getFormValues(\'frm_ver\')); }" value="Alterar vers�es" class="fonte_botao"><input type="button" value="Voltar" onclick="divPopupInst.destroi();" class="fonte_botao"></p>';
				
		conteudo += '</form></div>';
		
		conteudo += '<div id="a3_tab" name="Coment�rios" style="margin-left:3px">';
			conteudo += '<table border="0" width="100%">';			
			conteudo += '<tr><td colspan="2"><input type="checkbox" name="chk_comentarios" id="chk_comentarios" value="1" onclick="if(this.checked){xajax.$(\'div_comentario\').style.display=\'inline\';xajax.$(\'upload_1\').style.display=\'inline\';}else{xajax.$(\'div_comentario\').style.display=\'none\';xajax.$(\'upload_1\').style.display=\'none\';}"><span class="fonte_descricao_campos">Incluir coment�rios</span></td></tr>';
			conteudo += '<tr><td colspan="2">';
			conteudo += '<form name="frm_teste" id="frm_teste" action="upload_comentarios.php?id_ged_versao='+id_ged_versao+'" target="upload_target" method="post" enctype="multipart/form-data" onsubmit="startUpload_comentarios();">';
			conteudo += '<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;display:none;"></iframe>';
			conteudo += '<div id="div_comentario" name="div_comentario" style="width:100%; height:180px; padding:10px; display:none; border: solid #CCCCCC 1px;overflow:auto;">';
			conteudo += '<div id="div_comentarios_existentes">&nbsp;</div>';

			conteudo += '<input type="file" name="input_1" id="input_1"><img name="img_1" id="img_1" src="../images/silk/add.gif" style="cursor:pointer; margin-left:2px;" alt="Adicionar outro coment�rio" onclick="adiciona_input_file(\'div_comentario\');"><br>';
			conteudo += '</div><input type="submit" name="upload_1" class="caixa" style="display:none;" value="Upload"><input type="button" value="Voltar" onclick="divPopupInst.destroi();" class="fonte_botao"><br><p style="visibility:hidden;" id="inf_upload">&nbsp;</p></form></td></tr>';
			conteudo += '</tr></table>';
		conteudo += '</div>';
		
		conteudo += '<div id="a4_tab" name="Desbloqueios" style="margin-left:3px">';
			conteudo += '<table border="0" width="100%">';			
			//conteudo += '<tr><td colspan="2"><input type="checkbox" name="chk_comentarios" id="chk_comentarios" value="1" onclick="if(this.checked){xajax.$(\'div_comentario\').style.display=\'inline\';xajax.$(\'upload_1\').style.display=\'inline\';}else{xajax.$(\'div_comentario\').style.display=\'none\';xajax.$(\'upload_1\').style.display=\'none\';}"><span class="fonte_descricao_campos">Incluir coment�rios</span></td></tr>';
			conteudo += '<tr><td colspan="2">';
			//conteudo += '<form name="frm_des" id="frm_des">';
			//conteudo += '<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;display:none;"></iframe>';
			//conteudo += '<div id="div_comentario" name="div_comentario" style="width:100%; height:180px; padding:10px; display:none; border: solid #CCCCCC 1px;overflow:auto;">';
			conteudo += '<div id="div_desbloqueios">&nbsp;</div>';

			//conteudo += '<input type="file" name="input_1" id="input_1"><img name="img_1" id="img_1" src="../images/silk/add.gif" style="cursor:pointer; margin-left:2px;" alt="Adicionar outro coment�rio" onclick="adiciona_input_file(\'div_comentario\');"><br>';
			conteudo += '<input type="button" value="Voltar" onclick="divPopupInst.destroi();" class="fonte_botao"><br></td></tr>';
			conteudo += '</tr></table>';
		conteudo += '</div>';
	
	divPopupInst.div_conteudo.innerHTML = conteudo;
	
	tabbar=new dhtmlXTabBar("a_tabbar");
	
	tabbar.setImagePath("../includes/dhtmlx/dhtmlxTabbar/codebase/imgs/");
	
	tabbar.addTab("a1","Propriedades","100px");
	tabbar.addTab("a2","Vers�es","100px");
	tabbar.addTab("a3","Coment�rios","100px");
	tabbar.addTab("a4","Desbloqueios","100px");	
	
	tabbar.setContent("a1","a1_tab");
	tabbar.setContent("a2","a2_tab");
	tabbar.setContent("a3","a3_tab");
	tabbar.setContent("a4","a4_tab");
	tabbar.setStyle("modern");
	
	tabbar.setTabActive("a1");
	
	xajax_preencheVersoes_comentarios(id_ged_versao);
	
}


//criado em 23/07/2013 - carlos abreu
//Cria div popup de titulos de arquivos
function popupTitulos(id_ged_arquivo)
{	
	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();
	
	divPopupInst.altura = 250;
	
	divPopupInst.largura = 550;
	
	divPopupInst.inserir();
	
	conteudo = '<div id="div_tit">&nbsp;</div>';
	
	//conteudo += '<tr><td><input type="button" name="btn_checkout_voltar" id="btn_checkout_voltar" value="Voltar" onclick="divPopupInst.destroi();dir_up();" class="fonte_botao"></td><td>&nbsp;</td></tr>';
		
	divPopupInst.div_conteudo.innerHTML = conteudo;	
	
	xajax_preencheTitulos(id_ged_arquivo);
	
}


//criado em 10/02/2014 - carlos abreu
//Cria div popup de titulos de arquivos
function popupSolDesBloq(id_ged_arquivo)
{	
	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();
	
	divPopupInst.altura = 250;
	
	divPopupInst.largura = 550;
	
	divPopupInst.inserir();
	
	conteudo = '<div id="div_desbloq">&nbsp;</div>';
	
	divPopupInst.div_conteudo.innerHTML = conteudo;	
	
	xajax_sol_desbloquear(id_ged_arquivo);
	
}


//Cria div popup de visualiza��o das GRDs
function popupGRDs()
{	
	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();	
	
	divPopupInst.altura = 300;
	
	divPopupInst.largura = 500;
	
	divPopupInst.inserir();
	
	conteudo = '<form action="" method="post" name="frm_grds">';
	conteudo += '<div id="div_titulo" class="fonte_descricao_campos">&nbsp;</div>';
	conteudo += '<div id="div_ordem" style="width:100%; text-align:right"><span class="fonte_descricao_campos">Ordenar por: </span><select name="ordem" id="ordem" class="caixa"><option value="" selected>NUMDVM (padr�o)</option><option value="1">NUMCLI</option></select></div>';
	conteudo += '<div>&nbsp;</div>';	
	conteudo += '<div id="div_cabecalho" style="background:#E6E6E6; width:100%;">';
	conteudo +=	'<span id="cabecalho_espacamento" class="cell1" style="width:47%;border-style:outset; border-right-color:#999999; border-bottom-color: #999999; border-width:2px;padding:2px;">N� GRD</span>';
	conteudo +=	'<span id="cabecalho_espacamento" class="cell1" style="width:20%;border-style:outset; border-right-color:#999999; border-bottom-color: #999999; border-width:2px;padding:2px;">Data</span>';
	conteudo +=	'<span id="cabecalho_espacamento" class="cell1" style="width:10%;border-style:outset; border-right-color:#999999; border-bottom-color: #999999; border-width:2px;padding:2px;">Cancelar</span>';
	conteudo +=	'<span id="cabecalho_espacamento" class="cell1" style="width:10%;border-style:outset; border-right-color:#999999; border-bottom-color: #999999; border-width:2px;padding:2px;">Excluir</span>';
	conteudo += '</div>';	
	conteudo += '<div id="conteudo_grds" style="height:150px; overflow:auto; border-style:solid; border-color:#999999;border-width:1px;">&nbsp;</div>';
	conteudo += '<input type="hidden" name="id_ged_pacote" id="id_ged_pacote" value="">';
	conteudo += '<p><input type="button" value="Voltar" onclick="divPopupInst.destroi();" class="fonte_botao"></p>';
	
	divPopupInst.div_conteudo.innerHTML = conteudo;
}

function popupBuscaAvancada()
{	
	divPopupInst1 = new divPopupAv();
	
	elementosInst = new elementos();
	
	divPopupInst1.altura = screen.availHeight/1.5;
	
	divPopupInst1.largura = screen.availWidth/1.1;
	
	divPopupInst1.inserir();
	
	conteudo = '<form action="" method="post" name="frm_buscaavancada">';
	conteudo += '<div id="div_titulo" class="label_descricao_campos">&nbsp;</div>';
	conteudo += '<div id="div_tipo_busca" style="float:left;padding:2px;">';
	conteudo += '<div class="label_descricao_campos">Tipo de Busca</div><select name="tipo_busca" class="caixa" onchange="xajax_preencheBuscaAvancada(this.options[this.selectedIndex].value);"><option value="">SELECIONE</option><option value="1">PROJETO</option><option value="2">REFER�NCIA</option></select>';
	conteudo += '</div>';
	
	conteudo += '<div id="div_id_cliente" style="float:left;padding:2px;">';
	conteudo += '<div class="label_descricao_campos">Cliente</div><select name="busca_id_cliente" id="busca_id_cliente" class="caixa" onchange="xajax_preenche_os_BuscaAvancada(xajax.getFormValues(\'frm_buscaavancada\'));xajax.$(\'id_os\').options[xajax.$(\'id_os\').selectedIndex].value=this.value;xajax.$(\'id_os\').options[xajax.$(\'id_os\').selectedIndex].value=this.value;xajax.$(\'btn_rel\').disabled=false;" ><option value="">TODOS</option></select>';
	conteudo += '</div>';
	
	conteudo += '<div id="div_id_os" style="float:left;padding:2px;">';
	conteudo += '<div class="label_descricao_campos">OS</div><select name="busca_id_os" id="busca_id_os" class="caixa" onchange="xajax_preenchedisciplina(this.options[this.selectedIndex].value,\'\');xajax.$(\'id_os\').options[xajax.$(\'id_os\').selectedIndex].value=this.value;xajax.$(\'id_os\').options[xajax.$(\'id_os\').selectedIndex].value=this.value;xajax.$(\'btn_rel\').disabled=false;" ><option value="">TODAS</option></select>';
	conteudo += '</div>';
	conteudo += '<div id="div_id_disciplina" style="float:left;">';
	conteudo += '<div class="label_descricao_campos">Disciplina</div><select name="busca_id_disciplina" id="busca_id_disciplina" class="caixa" onchange="xajax_preenchedocumentos(this.options[this.selectedIndex].value,xajax.$(\'busca_id_os\').options[xajax.$(\'busca_id_os\').selectedIndex].value,\'true\');xajax.$(\'disciplina\').options[xajax.$(\'disciplina\').selectedIndex].value=this.value"><option value="">TODAS</option></select>';
	conteudo += '</div>';
	conteudo += '<div id="div_CodAtividade">';
	conteudo += '<div class="label_descricao_campos">Atividade</div><select name="busca_CodAtividade" class="caixa"><option value="">TODAS</option></select>';
	conteudo += '</div>';
	conteudo += '<div id="div_titulo1" style="clear:left; float:left;">';
	conteudo += '<div class="label_descricao_campos">Busca</div><input type="text" name="busca_texto" class="caixa" size="70">';
	conteudo += '</div>';	
	conteudo += '<div>&nbsp;</div>';	
	conteudo += '</div>';	
	conteudo += '<div id="div_busca_resultados" style="width:1150px; height:400px; clear:left;">&nbsp;</div>';
	conteudo += '<p><input type="button" value="Buscar" class="fonte_botao" onclick="xajax_buscaArquivosAvancada(xajax.getFormValues(\'frm_buscaavancada\'))"><input name="btn_relatorios" id="btn_rel" type="button" class="fonte_botao" value="Relat&oacute;rios" onClick="popupRel()" disabled="disabled" /><input type="button" value="Voltar" onclick="divPopupInst1.destroi();" class="fonte_botao"></p>';
	
	divPopupInst1.div_conteudo.innerHTML = conteudo;
		
	//xajax_preencheBuscaAvancada();		
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
	
	if(div.caminho)
	{
		//document.getElementById('caminho').value = div.caminho;
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

//Cria o menu que � ativado com um "right click"
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
		this.div_fundo.style.height = this.altura;
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

function popupPropriedades(id_ged_arquivo)
{
	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();
	
	divPopupInst.inserir(520,460);
	
	RCmenuInst.destroi();
	
	xajax_preenchePropriedades(id_ged_arquivo);	
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

/*
function popupZIP(caminho)
{
	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();	
	
	divPopupInst.inserir(500,420);

	conteudo = '';
	conteudo += '<div id="tree2" setImagePath="../includes/dhtmlx/dhtmlxTree/codebase/imgs/" class="dhtmlxTree" oncontextmenu="return false" style="width:470px; height:370px;"></div>'; //setOnClickHandler="tonclick"
	conteudo += '<input type="button" class="fonte_botao" onclick="divPopupInst.destroi();" value="Voltar">';
	
	divPopupInst.div_conteudo.innerHTML = conteudo;
	
	xajax_listaZIP(caminho);	
}
*/

//cria popup dos bot�es de relatorio
function popupRel()
{
	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();	
	
	divPopupInst.inserir(150,300);

	conteudo = '';
	conteudo += '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
	conteudo += '<tr>';
	conteudo += '<td valign="middle" class="fundo_cinza" ><input name="btn_listadocs_dvm" id="btn_listadocs_dvm" type="button" class="botao_chanfrado" value="Lista docs. DVM" onClick="xajax.$(\'ordem_lista_documentos\').value=\'numdvm\';xajax.$(\'frm_ged\').target = \'_blank\'; xajax.$(\'frm_ged\').action=\'ged_lista_documentos.php\';xajax.$(\'frm_ged\').submit();" /></td>';
	conteudo += '</tr>';
	conteudo += '<tr>';
	conteudo += '<td valign="middle" class="fundo_cinza" ><input name="btn_listadocs_cli" id="btn_listadocs_cli" type="button" class="botao_chanfrado" value="Lista docs. Cliente" onClick="xajax.$(\'ordem_lista_documentos\').value=\'numcliente\';xajax.$(\'frm_ged\').target = \'_blank\'; xajax.$(\'frm_ged\').action=\'ged_lista_documentos.php\';xajax.$(\'frm_ged\').submit();" /></td>';
	conteudo += '</tr>';
	conteudo += '<tr>';
	conteudo += '<td valign="middle" class="fundo_cinza" ><input name="btn_listadocs_xl" id="btn_listadocs_xl" type="button" class="botao_chanfrado" value="Lista docs. Excel" onClick="xajax.$(\'chk_excel\').value=\'1\';xajax.$(\'frm_ged\').target = \'_blank\'; xajax.$(\'frm_ged\').action=\'ged_lista_documentos.php\';xajax.$(\'frm_ged\').submit();xajax.$(\'chk_excel\').value=\'\';" /></td>';
	conteudo += '</tr>';
	conteudo += '<tr>';
	conteudo += '<td valign="middle" class="fundo_cinza" ><input name="btn_listadocsref" id="btn_listadocsref" type="button" class="botao_chanfrado" value="Lista docs. Ref." onClick="xajax.$(\'frm_ged\').target = \'_blank\'; xajax.$(\'frm_ged\').action=\'../relatorios/ged_lista_documentos_ref.php\';xajax.$(\'frm_ged\').submit();" /></td>';
	conteudo += '</tr>';
	//conteudo += '<tr>';
	//conteudo += '<td valign="middle" class="fundo_cinza" ><input name="btn_escopo" id="btn_escopo" type="button" class="botao_chanfrado" value="Escopo" onClick="window.open(\'../propostas/visualizar_os.php?id_proposta=\'+xajax.$(\'id_os\').options[xajax.$(\'id_os\').selectedIndex].text.substr(1,4));" /></td>';
	//conteudo += '</tr>';
	//conteudo += '<tr>';
	//conteudo += '<td valign="middle" class="fundo_cinza" ><input name="btn_escopo_xl" id="btn_escopo_xl" type="button" class="botao_chanfrado" value="Escopo (Excel)" onClick="window.open(\'../relatorios/rel_proposta_quantificacao.php?id_proposta=\'+xajax.$(\'id_os\').options[xajax.$(\'id_os\').selectedIndex].text.substr(1,4));" /></td>';
	//conteudo += '</tr>';
	conteudo += '<tr>';
	conteudo += '<td valign="middle" class="fundo_cinza" ><input name="btn_grd" id="btn_grd" type="button" class="botao_chanfrado" value="GRD" onClick="xajax.$(\'frm_ged\').target = \'_blank\'; xajax.$(\'frm_ged\').action=\'../arquivotec/ged_grd.php\';xajax.$(\'frm_ged\').submit();" /></td>';
	conteudo += '</tr>';
	conteudo += '<tr>';
	conteudo += '<td valign="middle" class="fundo_cinza" ><input name="btn_voltar" id="btn_voltar" type="button" class="botao_chanfrado" value="Voltar" onClick="divPopupInst.destroi();" /></td>';
	conteudo += '</tr>';
	conteudo += '<tr>';
	conteudo += '<td valign="middle" class="fundo_cinza" >&nbsp;</td>';
	conteudo += '</tr>';

	divPopupInst.div_conteudo.innerHTML = conteudo;
}

/*
function popupVisualizarDWG(caminho)
{
	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();
	
	divPopupInst.inserir(900,700);

	conteudo = '<APPLET id="id_appletDWG" code="de.escape.quincunx.dxf.DxfViewer" width="800" height="600" archive="../includes/dxfapplet/dxfapplet.jar">';
	conteudo += '<param name="file" value="'+caminho+'">';
	conteudo += '<param name="withStatusBar" value="false" />';
	conteudo += '� necess�rio ter Java instalado para visualizar.';
	conteudo += '</APPLET>';	
	conteudo += '<input type="button" value="Voltar" class="fonte_botao" onclick="divPopupInst.destroi();">';

	divPopupInst.div_conteudo.innerHTML = conteudo;
}
*/

/*
function popupVisualizarDOCXLS(caminho)
{

	//Instancia as classes
	divPopupInst = new divPopup();
	
	elementosInst = new elementos();
	
	divPopupInst.inserir(900,700);

	conteudo = '<textarea name="txt_documento" cols="155" rows="40" class="caixa" id="txt_documento"></textarea>';
	conteudo += '<input type="button" value="Voltar" class="fonte_botao" onclick="divPopupInst.destroi();">';

	divPopupInst.div_conteudo.innerHTML = conteudo;

	tinyMCE.init({
		mode : "exact",
		elements : "txt_documento", 
		theme : "advanced",
		language : "pt",
		plugins : "save,paste,searchreplace,fullscreen,preview,searchreplace,print,directionality,insertdatetime,table,xhtmlxtras,nonbreaking",
		plugin_insertdate_dateFormat : "%d/%m/%Y",
		plugin_insertdate_timeFormat : "%H:%M:%S",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_buttons1 : "fontselect,separator,cut,copy,paste,pastetext,pasteword,separator,bold,italic,underline,separator,justifyleft,justifycenter,justifyright,separator,numlist,bullist,separator,indent,outdent,separator,search,replace,separator,forecolor,backcolor,separator,fullscreen",
		theme_advanced_buttons2 : "print,tablecontrols,search,separator,insertdate,inserttime,nonbreaking,code",
		theme_advanced_buttons3 : "",
		apply_source_formatting : false,
		setup : function (ed) {
			ed.onLoadContent.add(function (ed) {
				xajax_preencheVisDocXls(caminho);
			});
		}
	});
}
*/

function popupComentarios(id_ged_arquivo, id_ged_versao)
{
	divPopupInst.destroi();
	
	//Instancia as classes
	divPopupInst_2 = new divPopup();

	divPopupInst_2.inserir(500,420);
	
	conteudo = '<div id="div_cabecalho_comentarios">&nbsp;</div>';
	conteudo += '<div id="rotulo_comentarios" class="fonte_descricao_campos">Arquivos de coment�rios:</div>';
	conteudo += '<div id="div_comentarios_existentes" style="width:100%; height:300px; border: solid #CCCCCC 1px; overflow:auto;">&nbsp;</div>';
	conteudo += '<input type="button" class="fonte_botao" value="Voltar" onclick="divPopupInst_2.destroi();popupPropriedades('+id_ged_arquivo+');">';
	
	divPopupInst_2.div_conteudo.innerHTML = conteudo;
	
	xajax_preencheComentarios(id_ged_versao);
}

function redimensiona_paineis(mouse_x)
{	
	painel_e = document.getElementById('tbl1');
	painel_d = document.getElementById('div_arquivos');

	if(((mouse_x*100)/screen.width)>50)
	{
		painel_e.style.width = ((mouse_x * 100) / screen.width)-2 + '%';
		painel_d.style.width = 98-((mouse_x * 100) / screen.width) + '%';
	}
	else
	{
		painel_d.style.width = 98-((mouse_x * 100) / screen.width) + '%';
		painel_e.style.width = ((mouse_x * 100) / screen.width)-2 + '%';
	}

	document.body.removeChild(document.getElementById('div_linha_trac'));
	
	document.getElementById('div_painel').onmousemove = '';
}

function painel_linhatrac(mouse_x)
{
	painel_principal = document.getElementById('div_painel');

	if(!document.getElementById('div_linha_trac'))
	{
		linha_trac = document.createElement('div');
		linha_trac.id = 'div_linha_trac';
		linha_trac.style.position = 'absolute';
		linha_trac.style.height = '250px';
		linha_trac.style.width = '1px';
		linha_trac.style.border = '1px dotted #000000';
		linha_trac.style.top = painel_principal.offsetTop;

		document.body.appendChild(linha_trac);
	}
	else
	{
		linha_trac = document.getElementById('div_linha_trac');
		linha_trac.style.left = mouse_x;
	}
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


