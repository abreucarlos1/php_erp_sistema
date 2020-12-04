USE [DADOSOFI]
GO

/****** Object:  Table [dbo].[DVM002]    Script Date: 02/09/2010 09:33:29 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[DVM002]') AND type in (N'U'))
DROP TABLE [dbo].[DVM002]
GO

USE [DADOSOFI]
GO

/****** Object:  Table [dbo].[DVM002]    Script Date: 02/09/2010 09:33:29 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

SET ANSI_PADDING ON
GO

CREATE TABLE [dbo].[DVM002](
	[AFU_PROJET] [varchar](10) NULL,
	[AFU_VERSAO] [varchar](4) NULL,
	[AFU_TAREFA] [varchar](30) NULL,
	[AFU_RECURS] [varchar](15) NULL,
	[AFU_DATA] [varchar](8) NULL,
	[AFU_HORAI] [varchar](5) NULL,
	[AFU_HORAF] [varchar](5) NULL,
	[AFU_OBS] [varchar](255) NULL,
	[AFU_ADIC] [float] NULL,
	[AFU_HQUANT] [float] NULL,
	[AFU_CTRRVS] [varchar](1) NULL,
	[AFU_CUSTO1] [float] NULL,
	[AFU_TPREAL] [varchar](1) NULL,
	[OPERACAO] [varchar](1) NULL,
	[ID] [int] NULL,
	[FLAG] [varchar](1) NULL,
	[MSG_ERROR] [varchar](255) NULL,
	[D_E_L_E_T_] [varchar](1) NULL,
	[R_E_C_N_O_] [int] NULL
) ON [PRIMARY]

GO

SET ANSI_PADDING OFF
GO


