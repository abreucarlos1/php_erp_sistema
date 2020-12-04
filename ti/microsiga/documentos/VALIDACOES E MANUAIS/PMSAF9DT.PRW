#INCLUDE "PROTHEUS.CH"
/*
ÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜ
±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±
±±ÉÍÍÍÍÍÍÍÍÍÍÑÍÍÍÍÍÍÍÍÍÍËÍÍÍÍÍÍÍÑÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍËÍÍÍÍÍÍÑÍÍÍÍÍÍÍÍÍÍÍÍÍ»±±
±±ºPrograma  ³PMSAF9DT  ºAutor  ³Microsiga           º Data ³  11/25/09   º±±
±±ÌÍÍÍÍÍÍÍÍÍÍØÍÍÍÍÍÍÍÍÍÍÊÍÍÍÍÍÍÍÏÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÊÍÍÍÍÍÍÏÍÍÍÍÍÍÍÍÍÍÍÍÍ¹±±
±±ºDesc.     ³PONTO DE ENTRADA PARA GRAVAR O TOTAL DE HORAS DO RCURSO NO  º±±
±±º          ³CAMPO AF9_HUTEIS                                            º±±
±±ÌÍÍÍÍÍÍÍÍÍÍØÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍ¹±±
±±ºUso       ³ AP                                                        º±±
±±ÈÍÍÍÍÍÍÍÍÍÍÏÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍÍ¼±±
±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±±
ßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßßß
*/

USER FUNCTION PMSAF9DT()
Local aArea 	:= GetArea()
Local aArAF8 	:= AF8->(GetArea())
Local nTotHor	:= 0 
Local nPeralc  := 0

if 	AF9->AF9_HDURAC == 0

dbSelectArea("AFA")
dbSetOrder(1)
MsSeek(xFilial("AFA")+AF9->AF9_PROJETO + AF9->AF9_REVISA + AF9->AF9_TAREFA )
While !Eof() .And. ;
xFilial("AF9")+AF9->AF9_PROJETO + AF9->AF9_REVISA + AF9->AF9_TAREFA==AFA_FILIAL+AFA_PROJET+AFA_REVISA+AFA_TAREFA
        			
		nTotHor := AFA->AFA_QUANT+nTotHor
       
        dbSkip()   
        
EndDo        

DbSelectArea("AF9")
RecLock("AF9",.F.)
	AF9->AF9_HDURAC := nTotHor
MsUnlock()

endif

dbSelectArea("AFA")
dbselectarea("AF9")
dbgotop ("AFA")
DBGOTOP ("AF9")
dbSetOrder(1)
MsSeek(xFilial("AFA")+AF9->AF9_PROJETO + AF9->AF9_REVISA + AF9->AF9_TAREFA )
While !Eof() .And. ;
xFilial("AF9")+AF9->AF9_PROJETO + AF9->AF9_REVISA + AF9->AF9_TAREFA==AFA_FILIAL+AFA_PROJET+AFA_REVISA+AFA_TAREFA
        			
		nPeralc := AFA->AFA_QUANT/nTotHor*100
       	RecLock("AFA",.F.)
			AFA->AFA_ALOC := nPeralc
		MsUnlock()
dbSkip()           
EndDo        

RestArea( aArAF8 )
RestArea( aArea )


RETURN