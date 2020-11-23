/**
 * Funções utilitárias
 * Este arquivo é carregado no header
 */

/**
 * Função que remove todos os elementos que possuem determinada classe CSS 
 */
function removeElementsByClass(className){
    var elements = document.getElementsByClassName(className);
    while(elements.length > 0){
        elements[0].parentNode.removeChild(elements[0]);
    }
}

/**
 * Função que exibe todos os elementos que possuem determinada classe CSS 
 */
function displayByClass(className, type){
	var elements = document.querySelectorAll('.'+className);
	
	for(var i=0, l=elements.length; i<l; i++){
		elements[i].setAttribute('style', 'display:'+type+';');
	}
}

/**
 * Função que exibe uma mensagem para ser usada em botões de exclusão
 */
function apagar(texto)
{
	if(confirm('Deseja apagar o registro '+texto+'?'))
	{
		return true;
	}
	else
	{
		return false;
	}
}

/**
 * Função padrão que é usada para chamar a função atualizatabela passando o valor contido no campo
 */
var iniciaBusca=
{
	buffer: false,
	tempo: 1000, 

	verifica : function(textbox)
	{
		setTimeout('iniciaBusca.compara("' + textbox.id + '", "' + textbox.value + '")', this.tempo); 
	},
	compara : function(id, valor)
	{
		if(valor == document.getElementById(id).value && valor != this.buffer)
		{
			this.buffer = valor;
			iniciaBusca.chamaXajax(valor);
		}
	},

	chamaXajax : function(valor)
	{
		xajax_atualizatabela(valor);	
	}
}

/**
 * Mudança de cor dos TDS quando passamos o mouse
 */
var marked_row = new Array;

//Função para mudar a cor dos <td>s no onMouseOver
function setPointer(theRow, theRowNum, theAction, theDefaultColor, thePointerColor, theMarkColor)
{
  var theCells = null;

  // 1. Pointer and mark feature are disabled or the browser can't get the
  //    row -> exits
  if ((thePointerColor == '' && theMarkColor == '')
      || typeof(theRow.style) == 'undefined') {
      return false;
  }

  // 2. Gets the current row and exits if the browser can't get it
  if (typeof(document.getElementsByTagName) != 'undefined') {
      theCells = theRow.getElementsByTagName('td');
  }
  else if (typeof(theRow.cells) != 'undefined') {
      theCells = theRow.cells;
  }
  else {
      return false;
  }

  // 3. Gets the current color...
  var rowCellsCnt  = theCells.length;
  var domDetect    = null;
  var currentColor = null;
  var newColor     = null;
  // 3.1 ... with DOM compatible browsers except Opera that does not return
  //         valid values with "getAttribute"
  if (typeof(window.opera) == 'undefined'
      && typeof(theCells[0].getAttribute) != 'undefined') {
      currentColor = theCells[0].getAttribute('bgcolor');
      domDetect    = true;
  }
  // 3.2 ... with other browsers
  else {
      currentColor = theCells[0].style.backgroundColor;
      domDetect    = false;
  } // end 3

  // 3.3 ... Opera changes colors set via HTML to rgb(r,g,b) format so fix it
  if (currentColor.indexOf("rgb") >= 0)
  {
      var rgbStr = currentColor.slice(currentColor.indexOf('(') + 1,
                                   currentColor.indexOf(')'));
      var rgbValues = rgbStr.split(",");
      currentColor = "#";
      var hexChars = "0123456789ABCDEF";
      for (var i = 0; i < 3; i++)
      {
          var v = rgbValues[i].valueOf();
          currentColor += hexChars.charAt(v/16) + hexChars.charAt(v%16);
      }
  }

  // 4. Defines the new color
  // 4.1 Current color is the default one
  if (currentColor == ''
      || currentColor.toLowerCase() == theDefaultColor.toLowerCase()) {
      if (theAction == 'over' && thePointerColor != '') {
          newColor              = thePointerColor;
      }
      else if (theAction == 'click' && theMarkColor != '') {
          newColor              = theMarkColor;
          marked_row[theRowNum] = true;
          // Garvin: deactivated onclick marking of the checkbox because it's also executed
          // when an action (like edit/delete) on a single item is performed. Then the checkbox
          // would get deactived, even though we need it activated. Maybe there is a way
          // to detect if the row was clicked, and not an item therein...
          // document.getElementById('id_rows_to_delete' + theRowNum).checked = true;
      }
  }
  // 4.1.2 Current color is the pointer one
  else if (currentColor.toLowerCase() == thePointerColor.toLowerCase()
           && (typeof(marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])) {
      if (theAction == 'out') {
          newColor              = theDefaultColor;
      }
      else if (theAction == 'click' && theMarkColor != '') {
          newColor              = theMarkColor;
          marked_row[theRowNum] = true;
          // document.getElementById('id_rows_to_delete' + theRowNum).checked = true;
      }
  }
  // 4.1.3 Current color is the marker one
  else if (currentColor.toLowerCase() == theMarkColor.toLowerCase()) {
      if (theAction == 'click') {
          newColor              = (thePointerColor != '')
                                ? thePointerColor
                                : theDefaultColor;
          marked_row[theRowNum] = (typeof(marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])
                                ? true
                                : null;
          // document.getElementById('id_rows_to_delete' + theRowNum).checked = false;
      }
  } // end 4

  // 5. Sets the new color...
  if (newColor) {
      var c = null;
      // 5.1 ... with DOM compatible browsers except Opera
      if (domDetect) {
          for (c = 0; c < rowCellsCnt; c++) {
              theCells[c].setAttribute('bgcolor', newColor, 0);
          } // end for
      }
      // 5.2 ... with other browsers
      else {
          for (c = 0; c < rowCellsCnt; c++) {
              theCells[c].style.backgroundColor = newColor;
          }
      }
  } // end 5

  return true;
} // end of the 'setPointer()' function


//Função para mudar a cor dos <td>s no onMouseOver
function setPointerDiv(theRow, theRowNum, theAction, theDefaultColor, thePointerColor, theMarkColor)
{
  var theCells = null;

  // 1. Pointer and mark feature are disabled or the browser can't get the
  //    row -> exits
  if ((thePointerColor == '' && theMarkColor == '')
      || typeof(theRow.style) == 'undefined') {
      return false;
  }

  // 2. Gets the current row and exits if the browser can't get it
  if (typeof(document.getElementsByTagName) != 'undefined') {
//      theCells = theRow.getElementsByTagName('div');
		theCells = theRow;
  }
  else if (typeof(theRow.cells) != 'undefined') {
//      theCells = theRow.cells;
		theCells = theRow;

}
  else {
      return false;
  }

  // 3. Gets the current color...
  var rowCellsCnt  = theCells.length;
  var domDetect    = null;
  var currentColor = null;
  var newColor     = null;
  // 3.1 ... with DOM compatible browsers except Opera that does not return
  //         valid values with "getAttribute"
  if (typeof(window.opera) == 'undefined'
      && typeof(theCells.getAttribute) != 'undefined') {
      currentColor = theCells.style.backgroundColor;
      domDetect    = true;
  }
  // 3.2 ... with other browsers
  else {
      currentColor = theCells.style.backgroundColor;
      domDetect    = false;
  } // end 3

  // 3.3 ... Opera changes colors set via HTML to rgb(r,g,b) format so fix it
  if (currentColor.indexOf("rgb") >= 0)
  {
      var rgbStr = currentColor.slice(currentColor.indexOf('(') + 1,
                                   currentColor.indexOf(')'));
      var rgbValues = rgbStr.split(",");
      currentColor = "#";
      var hexChars = "0123456789ABCDEF";
      for (var i = 0; i < 3; i++)
      {
          var v = rgbValues[i].valueOf();
          currentColor += hexChars.charAt(v/16) + hexChars.charAt(v%16);
      }
  }

  // 4. Defines the new color
  // 4.1 Current color is the default one
  if (currentColor == ''
      || currentColor.toLowerCase() == theDefaultColor.toLowerCase()) {
      if (theAction == 'over' && thePointerColor != '') {
          newColor              = thePointerColor;
      }
      else if (theAction == 'click' && theMarkColor != '') {
          newColor              = theMarkColor;
          marked_row[theRowNum] = true;
          // Garvin: deactivated onclick marking of the checkbox because it's also executed
          // when an action (like edit/delete) on a single item is performed. Then the checkbox
          // would get deactived, even though we need it activated. Maybe there is a way
          // to detect if the row was clicked, and not an item therein...
          // document.getElementById('id_rows_to_delete' + theRowNum).checked = true;
      }
  }
  // 4.1.2 Current color is the pointer one
  else if (currentColor.toLowerCase() == thePointerColor.toLowerCase()
           && (typeof(marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])) {
      if (theAction == 'out') {
          newColor              = theDefaultColor;
      }
      else if (theAction == 'click' && theMarkColor != '') {
          newColor              = theMarkColor;
          marked_row[theRowNum] = true;
          // document.getElementById('id_rows_to_delete' + theRowNum).checked = true;
      }
  }
  // 4.1.3 Current color is the marker one
  else if (currentColor.toLowerCase() == theMarkColor.toLowerCase()) {
      if (theAction == 'click') {
          newColor              = (thePointerColor != '')
                                ? thePointerColor
                                : theDefaultColor;
          marked_row[theRowNum] = (typeof(marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])
                                ? true
                                : null;
          // document.getElementById('id_rows_to_delete' + theRowNum).checked = false;
      }
  } // end 4

  // 5. Sets the new color...
  if (newColor) {
      var c = null;
      // 5.1 ... with DOM compatible browsers except Opera
      if (domDetect) {

              theCells.style.backgroundColor = newColor;
      }
      // 5.2 ... with other browsers
      else {
              theCells.style.backgroundColor = newColor;
      }
  } // end 5

  return true;
} // end of the 'setPointer()' function

/**
 * Função simplificadore de window.open
 */
function openpage(nome,caminho,largura,altura)
{
  params = "width="+largura+",height="+altura+",resizable=0,status=0,scrollbars=0,toolbar=0,location=0,directories=0,menubar=0, top="+(screen.height/2-altura/2)+", left="+(screen.width/2-largura/2)+" ";
  windows = window.open( caminho, nome , params);
  if(window.focus) 
  {
	setTimeout("windows.focus()",100);
  }  
}

/*========================================================*/
/* Funções Javascript
/* Tipo:
/* => branco
/* => cpf
/* => cpf_branco
/* => cnpj
/* => numero
/* => numero_branco
/* => selecionado
/* => dia
/* => mes
/* => email
/* => trim
/* => chars_only
/*========================================================*/

var arr_janelas = new Array();
var objJanela;
//var indice_jalelas = 0;

/**
 * Função simplificadora de window.open com possibilidade de passar outras propriedades
 */
function window_open(url, nomeJanela, propriedades)
{
	propriedades_default = "toolbar=no,location=no,status=no,menubar=no,resizable=no,scrollbars=yes,top=100,left=100,";
	objJanela = window.open(url, nomeJanela, propriedades_default+propriedades);
	arr_janelas.push(objJanela);
	objJanela.focus();
}

/**
 * Função que fecha todas as janelas abertas
 */
function windows_close()
{
	for(i=0; i<arr_janelas.length; i++){ arr_janelas[i].close(); }
} 

//Faz o div de fundo ficar inabilitado
function elementos()
{
	//Método para criar elementos e retornar
	this.criar = function(tipo)
	{
		//Cria os elementos e retorna
		return document.createElement(tipo);
	
	};
	
	//Método para remover elementos
	this.remover = function (id_pai, id_filho)
	{
		//Se o pai for "document.body"
		if(id_pai=="document.body")
		{
			//remove o objeto à partir do document.body
			document.body.removeChild(this.$(id_filho));
		}
		else
		{	
			//remove o objeto à partir do objeto pai
			this.$(id_pai).removeChild(this.$(id_filho));
		}

	};
	
	//Método para referenciar objetos
	this.$ = function (id)
	{
		//Referencia o objeto
		objeto = document.getElementById(id);
		//Retorna
		return objeto;
	};
}

function trava_div()
{
	elementosInst = new elementos();
	
	div_fundo = elementosInst.criar('div');
	iframe_iebug = elementosInst.criar('iframe');
	div_fundo = elementosInst.criar('div');
	iframe_iebug = elementosInst.criar('iframe');
	
	div_fundo.id = 'div_fundo';
	div_fundo.style.backgroundColor = '#000000';
	div_fundo.style.filter = 'alpha(opacity=20)';
	div_fundo.style.float = 'left';
	div_fundo.style.opacity = '.20';	
	div_fundo.style.left = '0px';
	div_fundo.style.top = '0px';
	div_fundo.style.position = 'absolute';
	div_fundo.style.padding = '150px';
	document.body.scrollTop=0;
	document.body.scroll = "no";
	div_fundo.style.width = document.body.clientWidth;
	div_fundo.style.height = document.body.clientHeight;
	div_fundo.style.zIndex = '1';
	/* Iframe para corrigir um BUG com combos que não respeitam zIndex no IE6 */
	iframe_iebug.id = 'iframe_iebug';
	iframe_iebug.style.display = 'block';
	iframe_iebug.style.left = '0px';
	iframe_iebug.style.top = '0px';
	iframe_iebug.style.position = 'absolute';
	iframe_iebug.style.width = document.body.clientWidth;
	iframe_iebug.style.height = document.body.clientHeight;
	iframe_iebug.style.backgroundColor = '#000000';		
	iframe_iebug.style.filter = 'alpha(opacity=20)';
	iframe_iebug.style.opacity = '.20';
	iframe_iebug.style.zIndex = '0';
	document.body.appendChild(div_fundo);
	document.body.appendChild(iframe_iebug);

}

//Faz o div de fundo ficar habilitado
function habilita_div()
{
	document.body.removeChild(document.getElementById('div_fundo'));	
	document.body.removeChild(document.getElementById('iframe_iebug'));
}


function verifica_cpf(valor,campo) 
{ 
	/*	
	// obtendo o cpf do input // 
	var cpf = valor;
	
	//document.forms[formulario][senha].value='';
	//document.forms[formulario][confirmacao].value='';
	//document.forms[formulario][senha].focus();
	
	cpf = cpf.replace(/[^a-zA-Z 0-9]+/g, '');
	
	// obtendo cada número do cpf // 
	pos1 = cpf.substring(0,1); 
	pos2 = cpf.substring(1,2); 
	pos3 = cpf.substring(2,3); 
	pos4 = cpf.substring(3,4); 
	pos5 = cpf.substring(4,5); 
	pos6 = cpf.substring(5,6); 
	pos7 = cpf.substring(6,7); 
	pos8 = cpf.substring(7,8); 
	pos9 = cpf.substring(8,9); 
	pos10 = cpf.substring(9,10); 
	pos11 = cpf.substring(10,11); 

	// somando todos os números do cpf // 
	var soma = parseFloat(pos1) + parseFloat(pos2) + parseFloat(pos3) + parseFloat(pos4) + parseFloat(pos5) + parseFloat(pos6) + parseFloat(pos7) + parseFloat(pos8) + parseFloat(pos9) + parseFloat(pos10) + parseFloat(pos11); 

	// resto da soma dos números do cpf dividido por 11 // 
	total = soma % 11; 

	// faz verificações para definir validade do cpf // 
	if(total!= 0 || cpf==00000000000 || cpf==11111111111 || cpf==22222222222 || cpf==33333333333 || cpf==44444444444 || cpf==55555555555 || cpf==66666666666 || cpf==77777777777 || cpf==88888888888 || cpf==99999999999) 
	{ 
		alert("cpf inválido");
		document.getElementById(campo).value='';
		//document.getElementById(campo).focus();
		return false; 
	} 
	*/
	
	cpf = valor;
		
	cpf = cpf.replace(/[^a-zA-Z 0-9]+/g, '');
	 
	erro = new String;
	 
	if (cpf.length < 11) 
		erro += "São necessários 11 digitos para verificação do CPF! \n\n"; 
	 
	var nonNumbers = /\D/;
	 
	if (nonNumbers.test(cpf)) 
		erro += "A verificacao de CPF suporta apenas numeros! \n\n"; 
	
	if (cpf == "00000000000" || cpf == "11111111111" || cpf == "22222222222" || cpf == "33333333333" || cpf == "44444444444" || cpf == "55555555555" || cpf == "66666666666" || cpf == "77777777777" || cpf == "88888888888" || cpf == "99999999999")
	{
		erro += "Numero de CPF invalido!"
	}
	 
	var a = [];
	var b = new Number;
	var c = 11;
	for (i=0; i<11; i++)
	{
	   a[i] = cpf.charAt(i);
	   if (i < 9) 
			b += (a[i] * --c);
	}
	if ((x = b % 11) < 2) 
	{ 
		a[9] = 0 
	} 
	else 
	{ 
		a[9] = 11-x 
	}
	
	b = 0;
	
	c = 11;
	for (y=0; y<10; y++) 
		b += (a[y] * c--); 
   if ((x = b % 11) < 2) 
   { 
		a[10] = 0; 
   }
   else
   { 
		a[10] = 11-x; 
   }
   if ((cpf.charAt(9) != a[9]) || (cpf.charAt(10) != a[10]))
   {
		   erro +="Digito verificador com problema!";
   }
   
   if (erro.length > 0)
   {
		   alert(erro);
		   
		   document.getElementById(campo).value='';
		   
		   return false;
   }

}

/*================================================================*/
/* Localizacao do Arquivo: lib\js\validacao.js
/* Escopo: func_calc_age(dia, mes, ano)
/* ---------------------------------------------------------------
/* Função calcula idade à partir de data de nascimento recebida 
/* como parâmetro
/* Parâmetros: inteiros dia, mês e ano 
/* 			   (dia e mês -> 2 digitos, ano -> 4 digitos)
/* Retorno: inteiro positivo correspondendo à idade -> p/ datas válidas
/* 			(-1) -> para datas inválidas
/*===============================================================*/
function func_calc_age(dd, mm, yy)
{
	//var msg_err = "Data Inválida";
	var date_system = new Date(); 	// Obtem data do sistema onde o JavaScript está sendo executado
	var dd_atual = date_system.getDate(); 
	var mm_atual = date_system.getMonth(); 
	mm_atual++;
	var yy_atual = date_system.getFullYear();
	var age;
	if (!func_val_date(dd,mm,yy)){
		age = yy_atual - yy 
		if( (mm_atual<mm) || ((mm_atual==mm) && (dd_atual<dd)) ){
			age--;
		}
		return(age);	
	} else {
		return(-1);
	}
}

/*======================================================================*/
/* Localizacao do Arquivo: lib\js\validacao.js
/* Escopo: func_calc_age(dia, mes, ano)
/* -----------------------------------------------------------------------
/* Função verifica se a data recebida como parâmetro é uma data válida,
/* considera como ano mínimo permitido 1900 e, opcionalmente, o máximo
/* como o ano corrente - neste caso necessario inserir a condição indicada.
/* Testa se mês (entre 1 e 12) dias (válidos para cada mês: 30 ou 31 e 
/* 28 ou 29 para fevereiro - verificando se ano é bissesto.
/* Retorno: TRUE p/ data inválida e FALSE p/ data válida
/* Obs.: campos em branco serão considerados inválidos
/* Utiliza as funções: func_year_biss(yy) e numero(xx) 
/*=======================================================================*/
function func_val_date(dd, mm, yy)
{

	var y_min = 1900; 	// Especifica ano mínimo válido na regra de negócio 
	//	var date_system = new Date();
	// var y_max = date_system.getFullYear(); // Especifica ano corrente como máximo válido na regra de negócio
	var is_err=false ;

	if ( numero(dd) && numero(mm) && numero(yy) ){
		// necessário inserir   ||(yy > y_max) ||  na linha abaixo se utilizar y_max
		// opcionalmente .. || (dd.length <=1) || (dd.length <=1) || ... ==>  se necessária a digitação do zero para meses e dias menores que 10
		if ((mm < 1) || (mm > 12) || (dd < 1) || (dd > 31) || (yy < y_min) || (mm == "") || (dd == "") || (yy == "") || (yy.length <=3)){
			is_err=true;
			//alert("if 1");
		} else if (((mm == 4) || (mm == 6) || (mm == 9) || (mm == 11)) && (dd > 30)){
			is_err=true;
			//alert("if 2");
		} else if ((mm==2) && (dd > 29)){
			is_err=true;
			//alert("if 3");
		} else if( (mm==2) && (dd > 28) && (!func_year_biss(yy)) ){
			is_err=true;
			//alert("if 4");
		}
	} else {
		is_err=true;
	}
	return(is_err);
}

/*======================================================================*/
/* Localizacao do Arquivo: lib\js\validacao.js
/* Escopo: func_val_date(dia, mês, ano, ano_corrente)
/* -----------------------------------------------------------------------
/* Função verifica se ano é bissesto
/* Parâmetro: ano com 4 dígitos
/* Retorno: true p/ bissexto - false caso contrário
/*=======================================================================*/
function func_year_biss(y)
{
	if((y % 4 == 0) && (y % 100 != 0 || y % 400 == 0) ){
		return true;
	} else {
		return false;
	}
}

/**
 * Função que cria um div para usar no modal
 * Cria o fundo cinza da tela
 */
function popupDiv(newId)
{
	idFundo = newId != undefined ? 'div_fundo_'+newId : 'div_fundo';
	idConteudo = newId != undefined ? 'div_conteudo_'+newId : 'div_conteudo';
	idIframe = newId != undefined ? 'iframe_iebug'+newId : 'iframe_iebug';
	//Instancia a classe
	elementosInst = new elementos();
	
	this.largura = 300;
	this.altura = 120;

	this._criaFundo = function ()
	{
		this.div_fundo = elementosInst.criar('div');
		this.iframe_iebug = elementosInst.criar('iframe');
		
		this.div_fundo.id = idFundo;
		this.div_fundo.className = 'css_popupdiv_fundo';
		document.body.scrollTop=0;
		document.body.scroll = "no";
		this.div_fundo.style.width = document.body.clientWidth;
		this.div_fundo.style.height = document.body.clientHeight;
		this.div_fundo.style.zIndex = 99;
		/* Iframe para corrigir um BUG com combos que não respeitam zIndex no IE6 */
		this.iframe_iebug.id = idIframe;
		this.iframe_iebug.style.display = 'block';
		this.iframe_iebug.style.left = '0px';
		this.iframe_iebug.style.top = '0px';
		this.iframe_iebug.style.height = '100%';
		this.iframe_iebug.style.width = '100%';
		this.iframe_iebug.style.position = 'absolute';
		this.iframe_iebug.style.width = document.body.clientWidth;
		this.iframe_iebug.style.height = document.body.clientHeight;
		this.iframe_iebug.style.backgroundColor = '#000000';		
		this.iframe_iebug.style.filter = 'alpha(opacity=20)';
		this.iframe_iebug.style.opacity = '.20';
		this.iframe_iebug.style.zIndex = '99';	
	};	
	
	this._criaConteudo = function ()
	{
		this.div_conteudo = elementosInst.criar('div');
		
		this.div_conteudo.id = idConteudo;
		this.div_conteudo.className = 'css_popupdiv_conteudo classModal';
		this.div_conteudo.style.left = (document.body.clientWidth/2)-(this.largura/2) + 'px';
		this.div_conteudo.style.top = (document.body.clientHeight/2)-(this.altura/2) + 'px';
		this.div_conteudo.style.width = this.largura + 'px';
		this.div_conteudo.style.height = this.altura + 'px';
		
		this.div_conteudo.style.position = 'absolute';
		this.div_conteudo.style.background = 'white';
		this.div_conteudo.style.padding = '10px';
	};	
	
	this._apendaElementos = function ()
	{
		document.body.appendChild(this.div_fundo);
		document.body.appendChild(this.iframe_iebug);
		document.body.appendChild(this.div_conteudo);
	};
	
	this.destroi = function (newId)
	{
		idFundo = newId != undefined ? 'div_fundo_'+newId : 'div_fundo';
		idConteudo = newId != undefined ? 'div_conteudo_'+newId : 'div_conteudo';
		idIframe = newId != undefined ? 'iframe_iebug'+newId : 'iframe_iebug';
		if(elementosInst.$(idFundo))
		{
			elementosInst.remover('document.body',idFundo);	
			elementosInst.remover('document.body',idIframe);
		}

		if(elementosInst.$(idConteudo))
		{
			elementosInst.remover('document.body',idConteudo);				
		}
		
		document.body.scroll = "yes";
	};

	this.inserir = function()
	{
		//Se não existirem os divs
		if(!elementosInst.$(idFundo) && !elementosInst.$(idConteudo))
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

/**
 * Função que cria uma janela modal de acordo com os parametros de tamanho
 * Permite criar várias janelas sobrepostas passando-se um idNew
 * idNew deve ser numérico e nunca string
 */
function modal(html, tamanho, titulo, idNew, dir_imagens, call_back)
{	
	divPopupInst = new popupDiv(idNew);
	
	elementosInst = new elementos();
	
	tamanho = tamanho == undefined ? '' : tamanho;
	
	dir_imagens = dir_imagens == undefined ? '../imagens/' : dir_imagens;
	
	switch(tamanho)
	{
		case 'gg':
			divPopupInst.altura = 650;
			divPopupInst.largura = 1045;
		break;
		case 'ggg':
			divPopupInst.altura = 750;
			divPopupInst.largura = 1045;
		break;
		case 'g':
			divPopupInst.altura = 650;
			divPopupInst.largura = 850;
		break;
		case 'm':
			divPopupInst.altura = 400;
			divPopupInst.largura = 600;
		break;
		case 'p':
			divPopupInst.altura = 300;
			divPopupInst.largura = 500;
		break;
		case 'pp':
			divPopupInst.altura = 200;
			divPopupInst.largura = 300;
		break;
		case 'ppp':
			divPopupInst.altura = 100;
			divPopupInst.largura = 250;
		break;
		case 'mp':
			divPopupInst.altura = 200;
			divPopupInst.largura = 650;
		break;
		case 'gpp':
			divPopupInst.altura = 150;
			divPopupInst.largura = 750;
		break;
		default:
			tam = tamanho.split('_');
			divPopupInst.altura = tam[0];
			divPopupInst.largura = tam[1];
		break;
	}
	
	divPopupInst.top_acrescimo = -100;
	
	divPopupInst.inserir();
	
	titulo = titulo != undefined ? '<label class="labels" style="float: left;color:white;">'+titulo+'&nbsp;</label>' : ''; 
	
	eventClick = 'divPopupInst.destroi('+idNew+');';
	onClick = call_back != '' && call_back != undefined ? 'if('+call_back+'()){'+eventClick+'};' : eventClick;
	
	//conteudo = '<p style="background-color:#647896;border:none;">'+titulo+'<img src="../imagens/cal_close.png" onclick=divPopupInst.destroi('+idNew+'); style="cursor:pointer;" /></p><div id="divConteudoModal">'+html+'</div>';
	
	//conteudo = '<p class="nome_formulario">'+titulo+'<span class="icone icone-fechar cursor" style="position:absolute;left:'+divPopupInst.largura+'" onclick=divPopupInst.destroi('+idNew+');></span></p><div id="divConteudoModal">'+html+'</div>';
	
	conteudo = '<p class="nome_formulario">'+titulo+'<img src="../imagens/cal_close.png" onclick=divPopupInst.destroi('+idNew+'); style="cursor:pointer;" /></p>';
	conteudo += '<div id="divConteudoModal">'+html+'</div>';
	
	divPopupInst.div_conteudo.innerHTML = conteudo;
	
	return true;
}

/**
 * Função que ajuda a criar uma paginação facilmente
 * Paginação htmlPaginacao(recipiente da paginação, pagina, inicio, qtd registro a serem exibidos, total de registros da consulta)
 * Exemplo:
 * 		$page = $_GET['page'];
 * 		$limit = 10;
 * 		$offset = 3;//Número da página que quero mostrar
 * 		$db->numero_registros = quantidade de registros retornados pelo banco de dados
 * 		$resposta->addScript("htmlPaginacao('gridPaginacao', ".$page.", ".$limit.", ".$offset.", ".$db->numero_registros.");");
 */
function htmlPaginacao(recipient, page, limit, offset, total_registros, form, possuiBuscar, loader, funcao)
{
	var funcao = funcao != undefined ? funcao : 'atualizatabela';
	//var showLoader = loader != undefined && loader ? 'showLoader();' : '';
	form = form != undefined ? form : 'frm';
	
	var html = '';
	
	if (total_registros > offset)
	{
		var prev = page - 1;
		var next = page + 1;
		var last = parseInt(total_registros / offset);
		
		offset = limit == 0 ? offset : limit + offset;
		limit  = limit == 0 ? limit = 1 : limit;

		var firstDisabled = page == 0 ? 'disabled="disabled"' : '';
		var prevDisabled = page == 0 ? 'disabled="disabled"' : '';
		var nextDisabled = page == last ? 'disabled="disabled"' : '';
		var lastDisabled = page == last ? 'disabled="disabled"' : '';
		
		if (possuiBuscar == true)
		{
			html = 	'<label class="labels">Pagina</label>'+
					'<select class="caixa" onChange="xajax_'+funcao+'(xajax.getFormValues(\''+form+'\'), this.value);">';
		}
		else
		{
			html = 	'<label class="labels">Pagina</label>'+
					'<select class="caixa" onChange="xajax_'+funcao+'(xajax.getFormValues(\''+form+'\'), this.value);">';
		}
		
		for (i=0;i<=last;i++)
		{
			selected = '';
			if (page == i)
			{
				selected = 'selected="selected"';
			}
			
			html += '<option value="'+i+'" '+selected+'>'+(i+1)+'</option>';
		}
		
		html += '</select>&nbsp;';
		
		if (possuiBuscar == true)
		{
			html += '<input type="button" '+firstDisabled+' class="class_botao" style="width: 40px; cursor:pointer;" onclick="xajax_'+funcao+'(\'\', 0);" value="<<" />'+
		    		'<input type="button" '+prevDisabled+' class="class_botao" style="width: 40px; cursor:pointer;" onclick="xajax_'+funcao+'(\'\', '+(prev)+');" value="<" />'+
		    		'<input type="button" '+nextDisabled+' class="class_botao" style="width: 40px; cursor:pointer;" onclick="xajax_'+funcao+'(\'\', '+(next)+');" value=">" />'+
		    		'<input type="button" '+lastDisabled+' class="class_botao" style="width: 40px; cursor:pointer;" onclick="xajax_'+funcao+'(\'\', '+(last)+');" value=">>" />';
		}
		else
		{
			html += '<input type="button" '+firstDisabled+' class="class_botao" style="width: 40px; cursor:pointer;" onclick="xajax_'+funcao+'(xajax.getFormValues(\''+form+'\'), 0);" value="<<" />'+
		    		'<input type="button" '+prevDisabled+' class="class_botao" style="width: 40px; cursor:pointer;" onclick="xajax_'+funcao+'(xajax.getFormValues(\''+form+'\'), '+(prev)+');" value="<" />'+
		    		'<input type="button" '+nextDisabled+' class="class_botao" style="width: 40px; cursor:pointer;" onclick="xajax_'+funcao+'(xajax.getFormValues(\''+form+'\'), '+(next)+');" value=">" />'+
		    		'<input type="button" '+lastDisabled+' class="class_botao" style="width: 40px; cursor:pointer;" onclick="xajax_'+funcao+'(xajax.getFormValues(\''+form+'\'), '+(last)+');" value=">>" />';
		}
	}
	
	document.getElementById(recipient).innerHTML = html;
}

/**
 * Função padrão para exibir o loader que já está no cabecalho do DVMSYS
 */
function showLoader()
{
	document.getElementById('div_loader').style.display = '';

}

/**
 * Função padrão para esconder o loader que já está no cabecalho do DVMSYS
 */
function hideLoader()
{
	document.getElementById('div_loader').style.display = "none";
}