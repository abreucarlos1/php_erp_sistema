#INCLUDE "PROTHEUS.CH"
/*
ÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜÜ
ฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑ
ฑฑษออออออออออัออออออออออหอออออออัออออออออออออออออออออหออออออัอออออออออออออปฑฑ
ฑฑบPrograma  ณPM        บAutor  ณGelson Carmo        บ Data ณ  10/23/09   บฑฑ
ฑฑฬออออออออออุออออออออออสอออออออฯออออออออออออออออออออสออออออฯอออออออออออออนฑฑ
ฑฑบDesc.     ณPONTO DE ENTRADA PARA GRAVACAO DA CLASSE DE VALOR DE FORMA  บฑฑ
ฑฑบ          ณAUTOMATICA.                                                 บฑฑ
ฑฑฬออออออออออุออออออออออออออออออออออออออออออออออออออออออออออออออออออออออออนฑฑ
ฑฑบUso       ณ AP                                                        บฑฑ
ฑฑศออออออออออฯออออออออออออออออออออออออออออออออออออออออออออออออออออออออออออผฑฑ
ฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑฑ
฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿฿
*/
USER FUNCTION PMA200SA() 
Local aArea := GetArea()
Local nOpcx := PARAMIXB[1]
Local cCV	:= AF8->AF8_CLIENT+AF8->AF8_LOJA+AF8->AF8_PROJET+SUBS(AF8->AF8_FASE,1,1)
Local crotina :=funname()

if crotina = "PMSA200"
//PE PARA INCLUSAO E EXCLUSAO DE CLASSE DE VALOR

	IF nOpcx == 3

		//INCLUI A CLASSE DE VALOR
		DbSelectArea("CTH")
		DbSetOrder(1)
		If !DbSeek( xFilial("CTH") + alltrim( cCV ) )
			RecLock("CTH",.T.)
				CTH->CTH_FILIAL	:= xFilial("CTH")
				CTH->CTH_CLVL	:= alltrim( cCV )
				CTH->CTH_CLASSE	:= "2"
				CTH->CTH_DESC01	:= AF8->AF8_DESCRI 
				CTH->CTH_BLOQ	:= "2"
				CTH->CTH_DTEXIS	:= DDATABASE		
			MsUnlock()
		Endif

	Elseif nOpcx == 8
			
		lRet:=.T.
		//EXCLUI A CLASSE DE VALOR
		DbSelectArea("CTH")
		DbSetOrder(1)
		If DbSeek( xFilial("CTH") + alltrim( cCV ) )
				
				cAlias 	:= Alias()
				nReg	:= RECNO()				
		
				//ฺฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฟ
				//ณ Verifica se a CLValor x conta tem valores  ณ
				//ภฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤู
				dbSelectArea("CTI")
			   	dbSetOrder(4)
				If dbSeek(xFilial()+CTH->CTH_CLVL)
					lRet:=.F.
					Help(" ",1,"CLVLMOVIM")
				EndIF
				IF lRet  
					dbSelectARea("CTH")
					dbSetOrder(2)
						IF CTH->CTH_CLASSE == "2"			// ANALITICA
								dbSkip( )
								If !Eof() .And. CTH->CTH_FILIAL == xFilial("CTH") .AND. Alltrim(CTH->CTH_CLVL) == ALLTRIM(CTH->CTH_CLSUP)
									Help(" ",1,"CLVLSINT")
										lRet:=.F.
								EndIf
						EndIf
					dbSetOrder(1)
				EndIf
				IF lRet
					BEGIN TRANSACTION
						//ฺฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฟ
						//ณ Deleta todos os itens	                    ณ
						//ภฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤู
						dbSelectArea( "CTI" )
						While !Eof() .And. 	CTI->CTI_FILIAL == xFilial("CTI") .And. ;
													CTI->CTI_CLVL == CTH->CTH_CLVL
							Reclock("CTI",.F.,.T.)
							dbDelete()
							MsUnlock()
							dbSkip()
						EndDo
						//ฺฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฟ
						//ณ Deleta a Classe de Valor do Cadastro       ณ
						//ภฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤฤู
						dbSelectArea(cAlias)
						MsGoTo(nReg)
						RecLock(cAlias,.F.,.T.)
						dbDelete()
						MsUnlock()
					END TRANSACTION
				Endif
		EndIf	
	
	Endif
endif	
RestArea( aArea )
RETURN