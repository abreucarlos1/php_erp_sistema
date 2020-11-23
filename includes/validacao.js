//Funções de validação de formulários.
//criação: 19/09/2005
//Ultima atualização: 23/08/2012
//Criado por Carlos Abreu / Otávio Pamplona


/**
 * Função que habilita elementos que possuem determinada classe CSS
 */
function enabledByClass(className){
	var elements = document.querySelectorAll('.'+className);
	
	for(var i=0, l=elements.length; i<l; i++){
		elements[i].removeAttribute('disabled');
	}
}

/*
 * Função que desabilita todos os elementos dentro de formulários que possuem determinada classe CSS 
 */
function disabledByClass(className){
	var elements = document.querySelectorAll('.'+className);
	
	for(var i=0, l=elements.length; i<l; i++){
		elements[i].setAttribute('disabled', 'disabled');
	}
}

//Limpa o campo recriando-o e substituindo
function limpa_file(campo)
{
	var oCampo = document.getElementById(campo);
	var oNovoCampo = oCampo.cloneNode( true );
	oCampo.parentNode.replaceChild( oNovoCampo, oCampo );
}

//colore o background do select option
function color_options(obj,id,color1)
{
	var x = document.getElementById(obj).options.length;
	
	for(i=0;i<=x;i++)
	{
		if(document.getElementById(obj).options[i].value==id)
		{
			document.getElementById(obj).options[i].style.background = color1;
		}
	}
}

/**
 * Função que seleciona algum valor dentro de um select de formulário
 */
function seleciona_combo(valor, id_combo)
{
	var itens = 0;

	combo = document.getElementById(id_combo);
	
	for(x=0;x<combo.options.length;x++)
	{
		if(combo.options[x].value==valor)
		{
			combo.options[x].selected = true;
			itens++;		
		}
	}	

	if(itens>1)
	{
		alert("Há uma inconsistência no banco de dados (valores duplicados).\nNão foi possível selecionar um item no combo: " + id_combo);
		combo.options.length = 0;
	}
}

/**
 * Função que reseta todos os campos de um determinado formulário
 */
function reset_campos(formulario) 
{ 
   for ( i=0; i < document.forms[formulario].elements.length; i++) 
   { 
      if ( ! ( document.forms[formulario].elements[i].readOnly  || document.forms[formulario].elements[i].disabled) ) { 
	   //if (( document.forms[formulario].elements[i].readOnly  || document.forms[formulario].elements[i].disabled) ) { 
         if (( document.forms[formulario].elements[i].type == 'text' ) || ( document.forms[formulario].elements[i].type == 'textarea' ) || ( document.forms[formulario].elements[i].type == 'password' )) { 
            document.forms[formulario].elements[i].value = ''; 
         } 

         if ( document.forms[formulario].elements[i].type == 'checkbox' ) { 
            document.forms[formulario].elements[i].checked = false; 
         }
		 
         if ( document.forms[formulario].elements[i].type == 'radio' ) { 
            document.forms[formulario].elements[i].checked = false; 
         } 

         if ( document.forms[formulario].elements[i].type == 'select-one' && ( document.forms[formulario].elements[i].length != 0 )) { 
            document.forms[formulario].elements[i].options[0].selected = true; 
         } 

      } 
   }  
}

/**
 * Função que verifica se um valor é realmene maior do que o outro
 * posicionando-os em campos específicos
 */
function valor_max_min(campo,valor,minimo,maximo)
{
	 valor = campo.value;
	 valor = parseInt(valor.toString().replace( ".", "," ));
	 minimo = parseInt(minimo.toString().replace( ".", "," ));
	 maximo = parseInt(maximo.toString().replace( ".", "," ));
	 
	if(minimo!='' || maximo!='')
	{
		if(valor<minimo)
		{
			campo.value = minimo.toString().replace( ",", "." );
		}
		
		if(valor>maximo)
		{
			campo.value = maximo.toString().replace( ",", "." );
		}
		
	}

}

/**
 * Verifica se o tamanho de um campo de data é correto
 */
function checaTamanhoData(input,len)
{
	if(input.value.length<len)
	{
		if(!confirm("Formato inválido de data. Favor utilizar: dd/mm/aaaa\n\nDeseja prosseguir?"))
		{
			input.focus();
		}
	}
}

/**
 * Adiciona as barras / nas datas digitadas em campos de formulário
 */
function transformaData(objeto, e)
{
	//Se a tecla apertada não for o Backspace (keyCode 8)
	if(e.keyCode!=8)
	{
		if(objeto.value.length==2)
		{
			objeto.value+="/";
		}
				
		if(objeto.value.length==5)
		{
			objeto.value+="/";
		}
	}
}

/**
 * Verifica se determinado campo dentro de um formulário está preenchido
 */
function validacampo(formulario,campo, msg) 
{
	if (!document.forms[formulario][campo].value)
	{
		alert (msg);
		document.forms[formulario][campo].focus();
	}
}

/**
 * Limpa um determinado select deixando sem options
 */
function limpa_combo(combo)
{
	var i; 
	
	combo_destino = document.getElementById(combo);
	
	for (i=combo_destino.length;i>-1;i--)
	{ 
		combo_destino.options[i] = null; 
	}
}

/**
 * Tira a seleção de qualquer option dentro de um select
 */
function desseleciona_combo(combo)
{
	var i; 
	
	combo_destino = document.getElementById(combo);

	for (i=combo_destino.length-1;i>-1;i--)
	{ 
		combo_destino.options[i].selected = false; 
	}
}

/**
 * Seleciona todos os options de um select
 */
function seleciona_todos(combo)
{
	var i; 
	
	combo_destino = document.getElementById(combo);

	for (i=combo_destino.length-1;i>-1;i--)
	{ 
		combo_destino.options[i].selected = true; 
	}
}

/**
 * Função que cria e adiciona um option a determinado select
 */
function addOption(selectId, txt, val, defaultValue)
{
	defaultValue = defaultValue != undefined ? defaultValue : 0;
	var objOption = new Option(txt, val, defaultValue);
    document.getElementById(selectId).options.add(objOption);
}

/**
 * Função que verifica se dois campos de senha são identicos
 */
function validasenha(formulario, senha, confirmacao, msg) 
{
	if ((document.forms[formulario][senha].value)!=(document.forms[formulario][confirmacao].value))
	{
		alert ("As senhas não são coincidentes, favor digitar novamente");
		document.forms[formulario][senha].value='';
		document.forms[formulario][confirmacao].value='';
		document.forms[formulario][senha].focus();
	}
}

/**
 * Função que verifica se todos os campos contendo um id chamado requerido estão preenchidos
 */
function requer(formulario)
{
	var erro;
	erro = '';
	
	for(i=0;i<=document.forms[formulario].elements.length-1;i++)
	{
		if (document.forms[formulario].elements[i].id=='requerido' && document.forms[formulario].elements[i].value=='')
		{
			alert("Favor preencher o campo "+document.forms[formulario].elements[i].name);
			document.forms[formulario].elements[i].focus();
			erro = 1;
			break;
		}
	}
	
	if (erro=='')
	{
		document.forms[formulario].submit();
	}	
}

/***
* Descrição.: formata um campo do formulário de
* acordo com a máscara informada...
* Parâmetros: - objForm (o Objeto Form)
* - strField (string contendo o nome
* do textbox)
* - sMask (mascara que define o
* formato que o dado será apresentado,
* usando o algarismo "9" para
* definir números e o símbolo "!" para
* qualquer caracter...
* - evtKeyPress (evento)
*
* Uso.......: <input type="textbox"
* name="xxx".....
* onkeypress="return txtBoxFormat(document.rcfDownload, 'str_cep', '99999-999', event);">
* Observação: As máscaras podem ser representadas
* como os exemplos abaixo:
* CEP -> 99999-999
* CPF -> 999.999.999-99
* CNPJ -> 99.999.999/9999-99
* C/C -> 999999-!
* Tel -> (99) 9999-9999
***/
function txtBoxFormat(objForm, strField, sMask, evtKeyPress) 
{
      var i, nCount, sValue, fldLen, mskLen,bolMask, sCod, nTecla;

      if(document.all) { // Internet Explorer
        nTecla = evtKeyPress.keyCode; }
//      else if(document.layers)
	   else	
	  { // Nestcape
        nTecla = evtKeyPress.which;		
      }
	  

      sValue = objForm[strField].value;


      // Limpa todos os caracteres de formatação que
      // já estiverem no campo.
      sValue = sValue.toString().replace( "-", "" );
      sValue = sValue.toString().replace( "-", "" );
      sValue = sValue.toString().replace( ".", "" );
      sValue = sValue.toString().replace( ".", "" );
      sValue = sValue.toString().replace( ",", "" );
      sValue = sValue.toString().replace( ",", "" );
      sValue = sValue.toString().replace( ":", "" );
      sValue = sValue.toString().replace( ":", "" );
      sValue = sValue.toString().replace( "/", "" );
      sValue = sValue.toString().replace( "/", "" );
      sValue = sValue.toString().replace( "(", "" );
      sValue = sValue.toString().replace( "(", "" );
      sValue = sValue.toString().replace( ")", "" );
      sValue = sValue.toString().replace( ")", "" );
      sValue = sValue.toString().replace( " ", "" );
      sValue = sValue.toString().replace( " ", "" );
      fldLen = sValue.length;
      mskLen = sMask.length;

      i = 0;
      nCount = 0;
      sCod = "";
      mskLen = fldLen;


      while (i <= mskLen) {
        bolMask = ((sMask.charAt(i) == "-") || (sMask.charAt(i) == ".") || (sMask.charAt(i) == "/") || (sMask.charAt(i) == ":") || (sMask.charAt(i) == ","))
        bolMask = bolMask || ((sMask.charAt(i) == "(") || (sMask.charAt(i) == ")") || (sMask.charAt(i) == " "))

        if (bolMask) {
          sCod += sMask.charAt(i);
          mskLen++; }
        else {
          sCod += sValue.charAt(nCount);
          nCount++;
        }

        i++;
      }

  
      objForm[strField].value = sCod;

      if (nTecla != 8) { // backspace
        if (sMask.charAt(i-1) == "9") { // apenas números...
          return ((nTecla > 47) && (nTecla < 58)); } // números de 0 a 9
        else { // qualquer caracter...
          return true;
        } }
      else {
        return true;
      }
}

/**
 * Função que permite selecionar, deselecionar todos os checkboxes no formulário
 */
function setcheckbox(formulario,status,prefixo)
{
	var idchk;
	
	// Altera o status dos checkboxes
	with(document.forms[formulario]) 
	{ 
		for(i=0;i<elements.length;i++) 
		{ 
			thiselm = elements[i];
			
			idchk = thiselm.name.split("_");
			
			if(prefixo)
			{
				if(thiselm.type == 'checkbox' && (idchk[0]==prefixo))
				{ 
					if(status=='check')
					{
						thiselm.checked = true;
					}
					else
					{
						thiselm.checked = false;
					}
				}	
			}
			else
			{
				if(thiselm.type == 'checkbox')
				{ 
					if(status=='check')
					{
						thiselm.checked = true;
					}
					else
					{
						thiselm.checked = false;
					}
				}	
			}
		}
	} 
}

/**
 * Função que permite selecionar, deselecionar todos os checkboxes no formulário
 */
function clickcheckbox(formulario)
{
	// Altera o status dos checkboxes
	with(document.forms[formulario]) 
	{ 
		for(i=0;i<elements.length;i++) 
		{ 
			thiselm = elements[i]; 
			if(thiselm.type == 'checkbox')
			{ 
				thiselm.checked = true;
			}
			else
			{
				thiselm.checked = false;
			}
			
		}
	} 
}

// added 2004-05-08 by Michael Keck <mail_at_michaelkeck_dot_de>
//  - this was directly written to each td, so why not a function ;)
//  setCheckboxColumn(\'id_rows_to_delete' . $row_no . ''\');
function setCheckboxColumn(theCheckbox)
{
    if (document.getElementById(theCheckbox)) {
        document.getElementById(theCheckbox).checked = (document.getElementById(theCheckbox).checked ? false : true);
        if (document.getElementById(theCheckbox + 'r')) {
            document.getElementById(theCheckbox + 'r').checked = document.getElementById(theCheckbox).checked;
        }
    } else {
        if (document.getElementById(theCheckbox + 'r')) {
            document.getElementById(theCheckbox + 'r').checked = (document.getElementById(theCheckbox +'r').checked ? false : true);
            if (document.getElementById(theCheckbox)) {
                document.getElementById(theCheckbox).checked = document.getElementById(theCheckbox + 'r').checked;
            }
        }
    }
}

/**
 * Função que retorna apenas os números de um valor passado
 */
function numero( numero )
{
    var nRet = 1;
    var strValidNumber="1234567890";
    for (nCount=0; nCount < numero.length; nCount++){
        strTempChar= numero.substring(nCount,nCount+1);
        if ( strValidNumber.indexOf(strTempChar,0)==-1){
            nRet = 0;
        }
    } 
    return nRet;
} 

/**
 * Formata o valor de acordo com o campo, tamanho e quando determinada tecla form pressionada no input
 */
function FormataValor(campo,tammax,teclapres)
{
	var tecla = teclapres.keyCode;
	vr = campo.value;
	vr = vr.replace( "/", "" );
	vr = vr.replace( "/", "" );
	vr = vr.replace( ",", "" );
	vr = vr.replace( ".", "" );
	vr = vr.replace( ".", "" );
	vr = vr.replace( ".", "" );
	vr = vr.replace( ".", "" );
	tam = vr.length;
	if (tam < tammax && tecla != 8)
	{ 
		tam = vr.length + 1; 
	}
	if (tecla == 8)
	{ 
		tam = tam - 1; 
	}
	if ( tecla == 8 || tecla >= 48 && tecla <= 57 || tecla >= 96 && tecla <= 105 )
	{
		if ( tam <= 2 )
		{ 
			campo.value = vr; 
		}
		if ( (tam > 2) && (tam <= 5) )
		{
			campo.value = vr.substr( 0, tam - 2 ) + ',' + vr.substr( tam - 2, tam ); 
		}
		if ( (tam >= 6) && (tam <= 8) )
		{
			campo.value = vr.substr( 0, tam - 5 ) + '.' + vr.substr( tam - 5, 3 ) + ',' + vr.substr( tam - 2, tam ); 
		}
		if ( (tam >= 9) && (tam <= 11) )
		{
			campo.value = vr.substr( 0, tam - 8 ) + '.' + vr.substr( tam - 8, 3 ) + '.' + vr.substr( tam - 5, 3 ) + ',' + vr.substr( tam - 2, tam ); 
		}
		if ( (tam >= 12) && (tam <= 14) )
		{
			campo.value = vr.substr( 0, tam - 11 ) + '.' + vr.substr( tam - 11, 3 ) + '.' + vr.substr( tam - 8, 3 ) + '.' + vr.substr( tam - 5, 3 ) + ',' + vr.substr( tam - 2, tam ); 
		}
		if ( (tam >= 15) && (tam <= 17) )
		{
			campo.value = vr.substr( 0, tam - 14 ) + '.' + vr.substr( tam - 14, 3 ) + '.' + vr.substr( tam - 11, 3 ) + '.' + vr.substr( tam - 8, 3 ) + '.' + vr.substr( tam - 5, 3 ) + ',' + vr.substr( tam - 2, tam );
		}
	}
}

//Determina qual é o navegador que está aberto
var Navegador = (navigator.appName.indexOf("Netscape")!=-1);

/**
 * Função que dá o foco para o próximo tab baseando-se na quantidade de inputs preenchidos 
 */
function autoTab(input,next,len) 
{
	if(input.value.length==len)
	{
		document.getElementsByName(next)[0].focus();
	}
}

/**
 * Função validadora de emails
 */
function verifica_email(campo)
{
  var obj = eval("document.forms[0]."+campo);
  var txt = obj.value;
  if ((txt.length != 0) && ((txt.indexOf("@") < 1) || (txt.indexOf('.') < 2)))
  {
    alert('Email incorreto');
	obj.focus();
  }
}

/** 
 * Função p/ Validar Data e voltar focalizada nos campos caso ocorra erro 
 */
function valdate(dd, mm, yy, ymax, ymin)
{
	vdd = eval("document.forms[0]."  +  dd  + ".value"); 
	vmm = eval("document.forms[0]."  +  mm  + ".value"); 
	vyy = eval("document.forms[0]."  +  yy  + ".value"); 

	verro = 0; 
	
	if (  vdd < 1  ||  vdd > 31  ||  vdd == ""  ||  isNaN(vdd)  ||  vdd.length <=1  ){
		alert("Campo Dia contém valor inválido");
		eval("document.forms[0]."  +  dd  + ".focus()");

		verro = 1;
		return verro;			
	}
	if ( ( vmm == 4  || vmm == 6 || vmm == 9 || vmm == 11 ) && ( vdd > 30 ) ){
		alert("Campo Dia contém valor inválido");
		eval("document.forms[0]."  +  dd  + ".focus()");
		verro = 1;
		
		return verro;			
	}
	if (vmm == 2 && vdd > 29){
		alert("Campo Dia contém valor inválido");
		eval("document.forms[0]."  +  dd  + ".focus()");
		verro = 1;
		
		return verro;			
	}
	if ( ( vmm == 2  && vdd > 28 ) && ( !func_year_biss(vyy) ) ){
		alert("Campo Dia contém valor inválido");
		eval("document.forms[0]."  +  dd  + ".focus()");
		verro = 1;
		
		return verro;			
	}
	if ( vmm < 1 || vmm > 12 || vmm == "" || isNaN(vmm) || vmm.length <= 1 ){
		alert("Campo Mes contém valor inválido");
		eval("document.forms[0]."  +  mm  + ".focus()");
		verro = 1;
		
		return verro;			
	}
	if ( vyy < ymin ||  vyy == "" ||  vyy > ymax || isNaN(vyy) || vyy.length < 4 ){
		alert("Campo Ano contém valor inválido");
		eval("document.forms[0]."  +  yy  + ".focus()");
		verro = 1;
		
		return verro;			
	}
	return verro;
}

/**
 *  Função p/ Validar Data e voltar focalizada nos campos caso ocorra erro 
 *  Modo de Uso: Crie no php a data atual a ser passada para a função
 *  Essa data terá os parametros Ymd,ou seja, ano, mes e dia.
 */
function NowAdays(dd, mm, yy, ymin, da)
{
	vdd = eval("document.forms[0]."  +  dd  + ".value"); 
	vmm = eval("document.forms[0]."  +  mm  + ".value"); 
	vyy = eval("document.forms[0]."  +  yy  + ".value"); 
	vdata = vyy + vmm + vdd;

	verro = 0; 
	
	if ( vdata <= da ){
		alert("Data informada não pode ser menor que a Data atual");
		eval("document.forms[0]."  +  dd  + ".focus()");
		verro = 1;
			return verro;			
	}
	if (  vdd < 1  ||  vdd > 31  ||  vdd == ""  ||  isNaN(vdd)  ||  vdd.length <=1  ){
			alert("Campo Dia contém valor inválido");
			eval("document.forms[0]."  +  dd  + ".focus()");
			verro = 1;
			return verro;			
	}
	if ( ( vmm == 4  || vmm == 6 || vmm == 9 || vmm == 11 ) && ( vdd > 30 ) ){
		alert("Campo Dia contém valor inválido");
		eval("document.forms[0]."  +  dd  + ".focus()");
		verro = 1;
		return verro;			
	}
	if (vmm == 2 && vdd > 29){
		alert("Campo Dia contém valor inválido");
		eval("document.forms[0]."  +  dd  + ".focus()");
		verro = 1;
		return verro;			
	}
	if ( ( vmm == 2  && vdd > 28 ) && ( !func_year_biss(vyy) ) ){
		alert("Campo Dia contém valor inválido");
		eval("document.forms[0]."  +  dd  + ".focus()");
		verro = 1;
		return verro;			
	}
	if ( vmm < 1 || vmm > 12 || vmm == "" || isNaN(vmm) || vmm.length <= 1 ){
		alert("Campo Mes contém valor inválido");
		eval("document.forms[0]."  +  mm  + ".focus()");
		verro = 1;
		return verro;			
	}
	if ( vyy < ymin ||  vyy == ""  || isNaN(vyy) || vyy.length < 4 ){
		alert("Campo Ano contém valor inválido");
		eval("document.forms[0]."  +  yy  + ".focus()");
		verro = 1;
		return verro;			
	}
	return verro;
}

/* -----------------------------------------------------------------------
/* Remove o espaço em branco do início e fim da string
/*=======================================================================*/
function trim(inputString) 
{
	if (typeof inputString != "string") { return inputString; }
		var retValue = inputString;
		var ch = retValue.substring(0, 1);
		while (ch == " ") { 
			retValue = retValue.substring(1, retValue.length);
			ch = retValue.substring(0, 1);
		}
		ch = retValue.substring(retValue.length-1, retValue.length);
		while (ch == " ") { 
			retValue = retValue.substring(0, retValue.length-1);
			ch = retValue.substring(retValue.length-1, retValue.length);
		}
		while (retValue.indexOf("  ") != -1) { 
			retValue = retValue.substring(0, retValue.indexOf("  ")) + retValue.substring(retValue.indexOf("  ")+1, retValue.length); 
		}
	return retValue; 
}

/*======================================================================*/
/* Escopo: chars_only() ex: OnKeyPress = chars_only();
/* -----------------------------------------------------------------------
/* É permitido digitar apenas caracteres e espaço
/*=======================================================================*/
function chars_only()
{
    if ( (event.keyCode >= 97 && event.keyCode <= 122 ) || (event.keyCode >= 65 && event.keyCode <= 90) || event.keyCode == 32){
        event.returnValue = true;
    } else {
        event.returnValue = false;
    }
}

/*======================================================================*/
/* Escopo: num_only() ex: OnKeyPress = num_only();
/* -----------------------------------------------------------------------
/* É permitido digitar apenas numero,virgula,sinal
/*=======================================================================*/
function num_only()
{
	//alert(event.keyCode);		
    if ( (event.keyCode >= 48 && event.keyCode <= 57 ) || event.keyCode == 44 || event.keyCode == 45)
	{
        event.returnValue = true;
    } else {
        event.returnValue = false;
    }
}

/**
 * Função que ordena um select
 */
function keySort(dropdownlist,caseSensitive) 
{

	// Função para dar um "sort" em um combobox HTML, a partir da tecla pressionada.	
	// Uso: 			  
	// <select name="exemplo" id="exemplo" onkeypress="return keySort(this);">	

	// check the keypressBuffer attribute is defined on the dropdownlist 
	var undefined; 
	if (dropdownlist.keypressBuffer == undefined) { 
		dropdownlist.keypressBuffer = ''; 
	} 
	
	// get the key that was pressed 
	var key = String.fromCharCode(window.event.keyCode); 
	dropdownlist.keypressBuffer += key;
	
	if (!caseSensitive) 
	{
		// convert buffer to lowercase
		dropdownlist.keypressBuffer = dropdownlist.keypressBuffer.toLowerCase();
	}
	
	// find if it is the start of any of the options 
	var optionsLength = dropdownlist.options.length; 
	for (var n=0; n < optionsLength; n++) { 
		var optionText = dropdownlist.options[n].text; 
		if (!caseSensitive) {
			optionText = optionText.toLowerCase();
		}
		if (optionText.indexOf(dropdownlist.keypressBuffer,0) == 0) { 
			dropdownlist.selectedIndex = n; 
			return false; // cancel the default behavior since 
		// we have selected our own value 
		} 
	} 
	
	// reset initial key to be inline with default behavior 
	dropdownlist.keypressBuffer = key; 
	return true; // give default behavior 
}

/**
 * Verifica se uma data e valida ou nao
 * call_back: funcao nova para caso de erro o sistema possa executar uma outra expressao, tipo elemento.value = ''
 */
function verificaDataErro(data, idLimpar)
{
	idLimpar = idLimpar != undefined ? idLimpar : '';
	
	if (data == '')
		return false;
	
	var erro = null;
	erro = data.match(/[&\\\#,+()$~%.'":*?<>{}]/g);
	
	var retorno = false;
	
	if (erro == null)
	{
		data = data.split('/');

		//Verifica se tem mais de 30 quando não deve ter
		if ((data[1] == 4 || data[1] == 6 || data[1] == 9 || data[1] == 11) && data[0] > 30)
		{
			retorno = false;
		}
		else if(data[1] == 2 && data[0] > 29) //Verifica se em fevereiro terá mais de 29 dias
		{
			retorno = false;
		}
		else if(data[0] < 1 || data[1] < 1 || data[2] < 1)
		{
			retorno = false;
		}
		else if(data[0] > 31 || data[1] > 12 || data[2] < 1900 || data[0] < 1)//Evita que outros tipos de erro ocorram
		{
			retorno = false;
		}
		else
			retorno = true;
	}
	else
	{
		retorno = false;
	}
	
	if (idLimpar != '' && !retorno)
	{
		document.getElementById(idLimpar).value = '';
		document.getElementById(idLimpar).focus();
	}
	
	if (!retorno)		
		alert('Data Invalida');
	
	return retorno;
}