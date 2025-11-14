<?php
	require_once ("funciones/fxGeneral.php");
	$m_cnx_MySQL = fxAbrirConexion();
	
	//Desactiva los Cursos que ya concluyeron
	$msConsulta = "update KDSA020A set ACTIVO_020 = 0 where FECHAFIN_020 < CURDATE()";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute();
		
	//Desactiva los Cobros que ya han sido pagados
	$msConsulta = "select COBRO_REL, KDSA020A.CURSO_REL, ACTIVO_020 from KDSA050A, KDSA020A where KDSA050A.CURSO_REL = KDSA020A.CURSO_REL and ACTIVO_050 = 1 and ANULADO_050 = 0";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute();
	
	while ($Fila = $mDatos->fetch())
	{
		$Cobro = $Fila["COBRO_REL"];
		$CursoActivo = $Fila["ACTIVO_020"];
		
		if ($CursoActivo == 0)
		{
			$msConsulta = "Select MATRICULA_REL from KDSA051A where COBRO_REL = ? and PAGADO_051 = 0 and ANULADO_051 = 0 and EXONERADO_051 = 0";
			$mDatosAux = $m_cnx_MySQL->prepare($msConsulta);
			$mDatosAux->execute([$Cobro]);
			$mnRegistros = $mDatosAux->rowCount();
			
			if ($mnRegistros == 0)
			{
				$msConsulta = "update KDSA050A set ACTIVO_050 = 0 where COBRO_REL = ?";
				$mDatosAux = $m_cnx_MySQL->prepare($msConsulta);
				$mDatosAux->execute([$Cobro]);
			}
		}
	}

	//Elimina los usuarios de estudiantes que iniciaron hace 8 meses y finalizaron hace 7 días
	$msConsulta = "select CURSO_REL from KDSA020A where ";
	$msConsulta .= "DATE_SUB(CURRENT_DATE, INTERVAL 8 MONTH) <= FECHAINI_020 and ";
	$msConsulta .= "DATE_ADD(FECHAFIN_020, INTERVAL 7 DAY) <= CURRENT_DATE  order by FECHAINI_020";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute();
	
	while ($Fila = $mDatos->fetch())
	{
		$msCurso = $Fila["CURSO_REL"];
		$msConsulta = "select MATRICULA_REL from KDSA030A where CURSO_REL = ?";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$msCurso]);

		while ($mFilaAux = $mAuxiliar->fetch())
		{
			$msMatricula = $mFilaAux["MATRICULA_REL"];
			$msConsulta = "delete from KDSA002A where USUARIO_REL = ?";
			$mBorrar = $m_cnx_MySQL->prepare($msConsulta);
			$mBorrar->execute([$msMatricula]);
		}
	}
?>