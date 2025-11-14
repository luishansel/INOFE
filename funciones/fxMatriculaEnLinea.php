<?php
	function fxGuardarMatriculaEnLinea($msCurso, $msDestinatario)
	{
		$msEnlace = date('Ymd') . substr($msCurso, 3) . date('His');
		$mdFecha = date('Y-m-d H:i');
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA007A (ENLACE_REL, CURSO_REL, FECHA_007, DESTINATARIO_007, ESTADO_007) values(?, ?, ?, ?, 0)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msEnlace, $msCurso, $mdFecha, $msDestinatario]);
	}
	
	function fxModificarMatriculaEnLinea($msEnlace, $mnEstado)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA007A set ESTADO_007 = ? where ENLACE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$mnEstado, $msEnlace]);
	}

	function fxBorrarMatriculaEnLinea($msEnlace)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA007A where ENLACE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msEnlace]);
	}
	
	function fxEstadoMatriculaEnLinea($msEnlace)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select ESTADO_007 from KDSA007A where ENLACE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msEnlace]);
		$Registros = $mDatos->rowCount();

		if ($Registros == 0)
		{
			$mnResultado = -1;
		}
		else
		{
			$fila = $mDatos->fetch();
			$mnResultado = $fila["ESTADO_007"];
		}
		return $mnResultado;
	}

	function fxDevuelveMatriculaEnLinea()
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select ENLACE_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE_020, FECHA_007, DESTINATARIO_007, (case ESTADO_007 when 0 then 'Activo' when 1 then 'Registrado' else 'Completado' end) as ESTADO_007 from KDSA007A join KDSA020A on KDSA007A.CURSO_REL = KDSA020A.CURSO_REL order by FECHA_007 desc";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		return $mDatos;
	}
?>