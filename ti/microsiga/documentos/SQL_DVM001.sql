USE [DADOSOFI]
GO

/****** Object:  Table [dbo].[DVM001]    Script Date: 06/23/2009 09:44:34 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

SET ANSI_PADDING ON
GO

DROP TABLE DVM001
GO

CREATE TABLE [dbo].[DVM001](
	[AE8_RECURS] [varchar](15) NULL,
	[AE8_DESCRI] [varchar](30) NULL,
	[AE8_TIPO] [varchar](1) NULL,
	[AE8_UMAX] [float] NULL,
	[AE8_PRODUT] [varchar](15) NULL,
	[AE8_CALEND] [varchar](3) NULL,
	[AE8_TPREAL] [varchar](1) NULL,
	[AE8_EMAIL] [varchar](50) NULL,
	[AE8_CUSFIX] [float] NULL,
	[AE8_PRDREA] [varchar](15) NULL,
	[AE8_ATIVO1] [varchar](1) NULL,
	[AE8_CODFUN] [varchar](6) NULL,
	[AE8_EQUIP] [varchar](10) NULL,
	[A2_NOME] [varchar](40) NULL,
	[A2_NREDUZ] [varchar](20) NULL,
	[A2_END] [varchar](40) NULL,
	[A2_MUN] [varchar](20) NULL,
	[A2_TIPO] [varchar](1) NULL,
	[RA_MAT] [varchar](6) NULL,
	[RA_NOME] [varchar](30) NULL,
	[RA_NATURAL] [varchar](2) NULL,
	[RA_NACIONA] [varchar](2) NULL,
	[RA_SEXO] [varchar](1) NULL,
	[RA_ESTCIVI] [varchar](1) NULL,
	[RA_NASC] [varchar](8) NULL,
	[RA_CC] [varchar](9) NULL,
	[RA_ADMISSA] [varchar](8) NULL,
	[RA_OPCAO] [varchar](8) NULL,
	[RA_BCDPFGT] [varchar](8) NULL,
	[RA_CTDPFGT] [varchar](12) NULL,
	[RA_HRSMES] [float] NULL,
	[RA_HRSEMAN] [float] NULL,
	[RA_CODFUNC] [varchar](5) NULL,
	[RA_CATFUNC] [varchar](1) NULL,
	[RA_TIPOPGT] [varchar](1) NULL,
	[RA_TIPOADM] [varchar](2) NULL,
	[RA_VIEMRAI] [varchar](2) NULL,
	[RA_GRINRAI] [varchar](2) NULL,
	[RA_NUMCP] [varchar](7) NULL,
	[RA_SERCP] [varchar](5) NULL,
	[RA_ADTPOSE] [varchar](6) NULL,
	[RA_TNOTRAB] [varchar](3) NULL,
	[ID] [int] NOT NULL,
	[ID_CARGO] [int] NOT NULL,
	[D_E_L_E_T_] [varchar](1) NULL,
	[R_E_C_N_O_] [int] NULL
) ON [PRIMARY]

GO

SET ANSI_PADDING OFF
GO


