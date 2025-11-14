<?php
	function fxGuardarCertificacion($msCurso, $mnMatIniM, $mnMatIniV, $mnMatFinM, $mnMatFinV, $mnDesercionM, $mnDesercionV,  $mnCertificadosM, $mnCertificadosV)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(CERTIFICACION_REL), 3), 0) as Ultimo from KDSA170A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "CT" . str_repeat("0", 8 - $Longitud) . trim($Numero);
		$mdFechaHoy = date('Y-m-d H:i:s');

		$msConsulta = "insert into KDSA170A (CERTIFICACION_REL, CURSO_REL, FECHAELABORACION_170, FECHAACTUALIZACION_170, MATRICULAINI_M_170, ";
		$msConsulta .= "MATRICULAINI_V_170, MATRICULAFIN_M_170, MATRICULAFIN_V_170, DESERCION_M_170, DESERCION_V_170, CERTIFICADOS_M_170, CERTIFICADOS_V_170) ";
		$msConsulta .= "values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msCurso, $mdFechaHoy, $mdFechaHoy, $mnMatIniM, $mnMatIniV, $mnMatFinM, $mnMatFinV, $mnDesercionM, $mnDesercionV,  $mnCertificadosM, $mnCertificadosV]);
		return $msCodigo;
	}

	function fxModificarCertificacion($msCodigo, $mnMatIniM, $mnMatIniV, $mnMatFinM, $mnMatFinV, $mnDesercionM, $mnDesercionV, $mnCertificadosM, $mnCertificadosV)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$mdFechaHoy = date('Y-m-d H:i:s');
		$msConsulta = "update KDSA170A set FECHAACTUALIZACION_170 = ?, MATRICULAINI_M_170 = ?, MATRICULAINI_V_170 = ?, MATRICULAFIN_M_170 = ?, ";
		$msConsulta .= "MATRICULAFIN_V_170 = ?, CERTIFICADOS_M_170 = ?, CERTIFICADOS_V_170 = ?, DESERCION_M_170 = ?, DESERCION_V_170 = ?";
		$msConsulta .= " where CERTIFICACION_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$mdFechaHoy, $mnMatIniM, $mnMatIniV, $mnMatFinM, $mnMatFinV, $mnCertificadosM, $mnCertificadosV, $mnDesercionM, $mnDesercionV, $msCodigo]);
	}

	function fxBorrarCertificacion($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA171A where CERTIFICACION_REL = '" . trim($msCodigo) . "'";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		$msConsulta = "delete from KDSA170A where CERTIFICACION_REL = '" . trim($msCodigo) . "'";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}

	function fxGuardarDetCertificacion($msCertificacion, $msMatricula, $mbCedula, $mbAcademicos, $mbNotas, $mbArancelCompleto, $mnAsistencia, $msEstado, $msTomoKdsa, $msFolioKdsa, $msActaKdsa)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA171A (CERTIFICACION_REL, MATRICULA_REL, CEDULA_171, ACADEMICOS_171, NOTAS_171, ARANCELCOMPLETO_171, ASISTENCIA_171, ESTADO_171, ";
		$msConsulta .= "TOMO_KDSA_171, FOLIO_KDSA_171, ACTA_KDSA_171) ";
		$msConsulta .= "values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCertificacion, $msMatricula, $mbCedula, $mbAcademicos, $mbNotas, $mbArancelCompleto, $mnAsistencia, $msEstado, $msTomoKdsa, $msFolioKdsa, $msActaKdsa]);

		if ($msTomoKdsa != "" and $msFolioKdsa != "" and $msActaKdsa != "")
		{
			$msConsulta = "update KDSA030A set ESTADO_030 = 3 where MATRICULA_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msMatricula]);

			$msConsulta = "update KDSA171A set ESTADO_171 = 'Certificado' where CERTIFICACION_REL = ? and MATRICULA_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCertificacion, $msMatricula]);
		}
		/*LHVG 20240210 Restaura estudiantes anteriores al 2024 que ya han sido certificados
		else
		{
			$msConsulta = "select ESTADO_030 from KDSA030A where MATRICULA_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msMatricula]);
			$Fila = $mDatos->fetch();
			$mnEstado = intval($Fila["ESTADO_030"]);
			if ($mnEstado == 3)
			{
				$msConsulta = "update KDSA030A set ESTADO_030 = 0 where MATRICULA_REL = ?";
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute([$msMatricula]);

				$msConsulta = "update KDSA171A set ESTADO_171 = 'Activo' where CERTIFICACION_REL = ? and MATRICULA_REL = ?";
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute([$msCertificacion, $msMatricula]);
			}
		}
		*/
	}
	
	function fxBorrarDetCertificacion($msCertificacion)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA171A where CERTIFICACION_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCertificacion]);
	}
?>