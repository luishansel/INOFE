<?php
	function fxGuardarDocentes($msUsuario, $msNombre, $msCedula, $msCorreo, $msTelefonos, $msDireccion, $mbActivo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(DOCENTE_REL), 3), 0) as Ultimo from KDSA100A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "DC" . str_repeat("0", 6 - $Longitud) . trim($Numero);
		$msConsulta = "insert into KDSA100A (DOCENTE_REL, USUARIO_REL, NOMBRE_100, CEDULA_100, CORREO_100, TELEFONOS_100, DIRECCION_100, ACTIVO_100) ";
		$msConsulta .= "values(?, ?, ?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msUsuario, $msNombre, $msCedula, $msCorreo, $msTelefonos, $msDireccion, $mbActivo]);
		return ($msCodigo);
	}
	
	function fxModificarDocentes($msCodigo, $msUsuario, $msNombre, $msCedula, $msCorreo, $msTelefonos, $msDireccion, $mbActivo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA100A set USUARIO_REL = ?, NOMBRE_100 = ?, CEDULA_100 = ?";
		$msConsulta .= ", CORREO_100 = ?, TELEFONOS_100 = ?, DIRECCION_100 = ?";
		$msConsulta .= ", ACTIVO_100 = ? where DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msUsuario, $msNombre, $msCedula, $msCorreo, $msTelefonos, $msDireccion, $mbActivo, $msCodigo]);
	}
	
	function fxBorrarDocentes($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA100A where DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveDocentes($mbLlenaGrid, $msCodigo = "")
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		if ($mbLlenaGrid == 1)
		{
			$msConsulta = "select DOCENTE_REL, NOMBRE_100, (case ACTIVO_100 when 1 then 'x' else '' end) as ACTIVO_100 from KDSA100A order by DOCENTE_REL desc";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute();
		}
		else
		{
			$msConsulta = "select DOCENTE_REL, USUARIO_REL, NOMBRE_100, CEDULA_100, CORREO_100, TELEFONOS_100, DIRECCION_100, ACTIVO_100 from KDSA100A where DOCENTE_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}

		return $mDatos;
	}
	
	/*****Detalle Cursos (KDSA101A)**************/
	
	function fxGuardarDetCurso($msCodigo, $mnDetCurso, $msCurso)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA101A (DOCENTE_REL, DETCURSO_REL, DESC_101) values (?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $mnDetCurso, $msCurso]);
	}
	
	function fxBorrarDetCurso($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA101A where DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveDetCurso($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select DOCENTE_REL, DETCURSO_REL, DESC_101 from KDSA101A where DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
	
	/*****Detalle Académica (KDSA102A)***********/
	
	function fxGuardarDetAcademica($msCodigo, $mnDetAcademica, $msGrado, $msTitulo, $msCentro, $msAnno)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA102A (DOCENTE_REL, DETACADEMICO_REL, GRADO_102, TITULO_102, CENTRO_102, ANNO_102) ";
		$msConsulta .= "values (?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $mnDetAcademica, $msGrado, $msTitulo, $msCentro, $msAnno]);
	}
	
	function fxBorrarDetAcademica($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA102A where DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveDetAcademica($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select DOCENTE_REL, DETACADEMICO_REL, GRADO_102, TITULO_102, CENTRO_102, ANNO_102 from KDSA102A where DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
	
	/*****Detalle Especialización (KDSA103A)*****/
	
	function fxGuardarDetEspecializacion($msCodigo, $mnDetEspecializacion, $msTitulo, $msCentro, $msAnno)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA103A (DOCENTE_REL, DETESPECIALIZACION_REL, TITULO_103, CENTRO_103, ANNO_103) ";
		$msConsulta .= "values (?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $mnDetEspecializacion, $msTitulo, $msCentro, $msAnno]);
	}
	
	function fxBorrarDetEspecializacion($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA103A where DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveDetEspecializacion($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select DOCENTE_REL, DETESPECIALIZACION_REL, TITULO_103, CENTRO_103, ANNO_103 from KDSA103A where DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
	
	/*****Detalle Laboral (KDSA104A)*************/
	
	function fxGuardarDetLaboral($msCodigo, $mnDetLaboral, $msEmpresa, $msCargo, $msFunciones, $msPeriodo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA104A (DOCENTE_REL, DETLABORAL_REL, EMPRESA_104, CARGO_104, FUNCIONES_104, PERIODO_104) ";
		$msConsulta .= "values (?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $mnDetLaboral, $msEmpresa, $msCargo, $msFunciones, $msPeriodo]);
	}
	
	function fxBorrarDetLaboral($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA104A where DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveDetLaboral($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select DOCENTE_REL, DETLABORAL_REL, EMPRESA_104, CARGO_104, FUNCIONES_104, PERIODO_104 from KDSA104A where DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
	
	/*****Detalle Docente (KDSA105A)*************/
	
	function fxGuardarDetDocente($msCodigo, $mnDetDocente, $msCentro, $msClases, $msPeriodo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA105A (DOCENTE_REL, DETDOCENTE_REL, CENTRO_105, CLASES_105, PERIODO_105) ";
		$msConsulta .= "values (?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $mnDetDocente, $msCentro, $msClases, $msPeriodo]);
	}
	
	function fxBorrarDetDocente($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA105A where DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveDetDocente($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select DOCENTE_REL, DETDOCENTE_REL, CENTRO_105, CLASES_105, PERIODO_105 from KDSA105A where DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
	
	/*****Detalle Referencias (KDSA106A)*********/
	
	function fxGuardarDetReferencias($msCodigo, $mnDetReferencias, $msNombre, $msOcupacion, $msTelefono, $msCedula)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA106A (DOCENTE_REL, DETREFERENCIA_REL, NOMBRE_106, OCUPACION_106, TELEFONO_106, CEDULA_106) ";
		$msConsulta .= "values (?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $mnDetReferencias, $msNombre, $msOcupacion, $msTelefono, $msCedula]);
	}
	
	function fxBorrarDetReferencias($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA106A where DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveDetReferencias($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select DOCENTE_REL, DETREFERENCIA_REL, NOMBRE_106, OCUPACION_106, TELEFONO_106, CEDULA_106 from KDSA106A where DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
	
	/*****Detalle Adicional (KDSA107A)***********/
	
	function fxGuardarDetAdicional($msCodigo, $mnDetAdicional, $msInformacion)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA107A (DOCENTE_REL, DETADICIONAL_REL, INFORMACION_107) values (?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $mnDetAdicional, $msInformacion]);
	}
	
	function fxBorrarDetAdicional($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA107A where DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveDetAdicional($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select DOCENTE_REL, DETADICIONAL_REL, INFORMACION_107 from KDSA107A where DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
	
	/*****Detalle Documento (KDSA108A)***********/

	function fxGuardarDetDocumento($msCodigo, $msImagen, $msDescripcion, $msRuta)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA108A (DOCENTE_REL, IMAGEN_REL, DESC_108, RUTA_108) values (?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msImagen, $msDescripcion, $msRuta]);
	}
	
	function fxBorrarDetDocumento($msCodigo, $msImagen)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA108A where DOCENTE_REL = '" . trim($msCodigo) . "' and IMAGEN_REL = '" . trim($msImagen) . "'";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msImagen]);
	}
	
	function fxDevuelveDetDocumento($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select DOCENTE_REL, IMAGEN_REL, DESC_108, RUTA_108 from KDSA108A where DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
?>