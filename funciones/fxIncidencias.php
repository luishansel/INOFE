<?php
	function fxGuardarIncidencia($msCurso, $msDocente, $mnConsecutivo, $mdFecha, $msTexto)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA023A (CURSO_REL, DOCENTE_REL, DETINCIDENCIA_REL, FECHA_023, TEXTO_023) values (?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCurso, $msDocente, $mnConsecutivo, $mdFecha, $msTexto]);
	}
	
	function fxBorrarTodoIncidencia($msCurso, $msDocente)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA023A where CURSO_REL = ? and DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCurso, $msDocente]);
	}
	
	function fxDevuelveIncidencia($msCurso)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msDocente = $_SESSION["gsDocente"];
		$msUsuario = $_SESSION["gsUsuario"];
		$msConsulta = "Select * from KDSA002A where USUARIO_REL =? and SUPERVISOR_002 = 1";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msUsuario]);
		$Administrador = $mDatos->rowCount();

		if ($Administrador == 1 or $_SESSION["gsDocente"] == "")
		{
			$msConsulta = "select CURSO_REL, DETINCIDENCIA_REL, KDSA023A.DOCENTE_REL, ifnull('', (select NOMBRE_100 from KDSA100A where KDSA023A.DOCENTE_REL = KDSA100A.DOCENTE_REL)) as NOMBRE_100, FECHA_023, TEXTO_023 from KDSA023A where CURSO_REL = ? order by FECHA_023";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCurso]);
		}
		else
		{
			$msConsulta = "select CURSO_REL, DETINCIDENCIA_REL, DOCENTE_REL, FECHA_023, TEXTO_023 from KDSA023A where CURSO_REL = ? and DOCENTE_REL = ? order by FECHA_023";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCurso, $msDocente]);
		}

		return $mDatos;
	}
?>