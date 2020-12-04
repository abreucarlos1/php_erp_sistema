#include 'Protheus.ch'
#Include "TbiConn.Ch"

/*
ÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜ
±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±
±±ÉÍÍÍÍÍÍÍÍÍÍÑÍÍÍÍÍÍÍÍÍÍËÍÍÍÍÍÍÍÑÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍËÍÍÍÍÍÍÑÍÍÍÍÍÍÍÍÍÍÍÍÍ»±±
±±ºPrograma  ³NOVO2     ºAutor  ³Microsiga           º Data ³  09/25/09   º±±
±±ÌÍÍÍÍÍÍÍÍÍÍØÍÍÍÍÍÍÍÍÍÍÊÍÍÍÍÍÍÍÏÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÊÍÍÍÍÍÍÏÍÍÍÍÍÍÍÍÍÍÍÍÍ¹±±
±±ºDesc.     ³                                                            º±±
±±º          ³                                                            º±±
±±ÌÍÍÍÍÍÍÍÍÍÍØÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍ¹±±
±±ºUso       ³ AP                                                        º±±
±±ÈÍÍÍÍÍÍÍÍÍÍÏÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍ¼±±
±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±
ßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßß
*/
USER FUNCTION JOB_INTPMS()

Conout( repl("*",30)                                             )
Conout( "Inicio de JOB de Integracao de Recursos e Apontamentos ")
Conout( "Data: " + dtoc( Msdate() ) + "  -  Hora: "+ Time()      )

PREPARE ENVIRONMENT EMPRESA "01" FILIAL "01" MODULO "SIGAPMS"

	conout("________Inicio de chamada de funcao U_DV_ImpRec()")
	//Executa a rotina de importacao de Recursos
	U_DVImpRec()

	conout("________Inicio de chamada de funcao U_ImpApont()")
	//Executa a rotina de importacao de Apontamentos
    U_ImpApont()
    
    EmailJob()

RESET ENVIRONMENT

Conout( repl("*",30)                                             )
Conout( "Fim de JOB de Integracao de Recursos e Apontamentos "   )

RETURN





Static Function EmailJob()
Local cQuery 
Local aDVM001 := {}
Local aDVM002 := {}
Local nRegs   := 0


Conout( "____Enviando e-mail."   )

cQuery := " SELECT * FROM DVM001 "
IF SELECT("QRY") > 0
	QRY->( DBCLOSEAREA() )
ENDIF
dbUseArea(.T., "TOPCONN", TcGenQry(,, cQuery), "QRY", .F., .F.)

AADD( aDVM001, { 'RECURSO', 'DESCRICAO', 'TIPO', 'ESPECIE', 'UMAX', 'PRODUTO', 'CALENDARIO', 'ERRO', 'FLAG', 'ID' } )
while !Eof()
	
	AADD( aDVM001, { QRY->AE8_RECURS, QRY->AE8_DESCRI, QRY->AE8_TIPO, QRY->AE8_ESPEC, QRY->AE8_UMAX, QRY->AE8_PRODUT, QRY->AE8_CALEND, QRY->MSG_ERROR, QRY->FLAG, STR(QRY->ID,10) } )
	QRY->( DbSkip() )
	
Enddo


cQuery := " SELECT * FROM DVM002 "
IF SELECT("QRY") > 0
	QRY->( DBCLOSEAREA() )
ENDIF
dbUseArea(.T., "TOPCONN", TcGenQry(,, cQuery), "QRY", .F., .F.)

AADD( aDVM002, { 'PROJETO', 'VERSAO', 'TAREFA', 'RECURSO', 'DATA', 'HORA INICIAL', 'HORA FINAL', 'QUANTIDADE', 'FLAG', 'OPERACAO', 'ERRO', 'ID' } )
while !Eof()
	
	AADD( aDVM002, { QRY->AFU_PROJET, QRY->AFU_VERSAO, QRY->AFU_TAREFA, QRY->AFU_RECURS, QRY->AFU_DATA, QRY->AFU_HORAI, QRY->AFU_HORAF, QRY->AFU_HQUANT, QRY->FLAG, QRY->OPERACAO, QRY->MSG_ERROR, STR(QRY->ID,10) } )
	QRY->( DbSkip() )
	
Enddo


//ENVIO DE E-MAILS
U_MD_MHtmlmail('carlos.abreu@devemada.com.br;gelson@sigaservices.com.br','[INTEGRACAO PMS RECURSOS    ] - DVM001 ', aDVM001 )
U_MD_MHtmlmail('carlos.abreu@devemada.com.br;gelson@sigaservices.com.br','[INTEGRACAO PMS APONTAMENTOS] - DVM002 ', aDVM002 )

Conout( "____Fim Envio e-mail."   )

return