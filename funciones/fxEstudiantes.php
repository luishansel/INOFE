<?php
	function fxGuardarEstudiantes($msNombres, $msApellidos, $msSexo, $msCedula, $msFechaNac, $msDomicilio, $msDireccion, $msTelefono, $msCelular, $msCorreo, $msEmergencia, $msParentesco, $msNivAcademico, $mbPostGrado, $mbMaestria, $msLugarTrabajo, $msPuesto, $msTelEmpresa)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(ESTUDIANTE_REL), 3), 0) as Ultimo from KDSA010A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "ES" . str_repeat("0", 8 - $Longitud) . trim($Numero);
		$msConsulta = "insert into KDSA010A (ESTUDIANTE_REL, NOMBRES_010, APELLIDOS_010, SEXO_010, CEDULA_010, FECHANAC_010, DOMICILIO_010, DIRECCION_010, TELEFONO_010, CELULAR_010, CORREO_010, EMERGENCIA_010, PARENTESCO_010, NIVELACADEMICO_010, POSTGRADO_010, MAESTRIA_010, LUGARTRABAJO_010, PUESTO_010, TELEFONOEMPRESA_010) ";
		$msConsulta .= "values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msNombres, $msApellidos, $msSexo, $msCedula, $msFechaNac, $msDomicilio, $msDireccion, $msTelefono, $msCelular, $msCorreo, $msEmergencia, $msParentesco, $msNivAcademico, $mbPostGrado, $mbMaestria, $msLugarTrabajo, $msPuesto, $msTelEmpresa]);
		return ($msCodigo);
	}
	
	function fxModificarEstudiantes($msCodigo, $msNombres, $msApellidos, $msSexo, $msCedula, $msFechaNac, $msDomicilio, $msDireccion, $msTelefono, $msCelular, $msCorreo, $msEmergencia, $msParentesco, $msNivAcademico, $mbPostGrado, $mbMaestria, $msLugarTrabajo, $msPuesto, $msTelEmpresa)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA010A set NOMBRES_010 = ?, APELLIDOS_010 = ?, SEXO_010 = ?, CEDULA_010 = ?, FECHANAC_010 = ?, DOMICILIO_010 = ?, DIRECCION_010 = ?, ";
		$msConsulta .= "TELEFONO_010 = ?, CELULAR_010 = ?, CORREO_010 = ?, EMERGENCIA_010 = ?, PARENTESCO_010 = ?, NIVELACADEMICO_010 = ?, POSTGRADO_010 = ?, ";
		$msConsulta .= "MAESTRIA_010 = ?, LUGARTRABAJO_010 = ?, PUESTO_010 = ?, TELEFONOEMPRESA_010 = ? where ESTUDIANTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msNombres, $msApellidos, $msSexo, $msCedula, $msFechaNac, $msDomicilio, $msDireccion, $msTelefono, $msCelular, $msCorreo, $msEmergencia, $msParentesco, $msNivAcademico, $mbPostGrado, $mbMaestria, $msLugarTrabajo, $msPuesto, $msTelEmpresa, $msCodigo]);
	}
	
	function fxDevuelveEstudiantes($mbLlenaGrid, $msCodigo = "")
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		if ($mbLlenaGrid == 1)
		{
			$msConsulta = "select ESTUDIANTE_REL, NOMBRES_010, APELLIDOS_010, TELEFONO_010, CELULAR_010, CORREO_010, LUGARTRABAJO_010 from KDSA010A order by ESTUDIANTE_REL desc";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute();
		}
		else
		{
			$msConsulta = "select ESTUDIANTE_REL, NOMBRES_010, APELLIDOS_010, SEXO_010, CEDULA_010, FECHANAC_010, DOMICILIO_010, DIRECCION_010, TELEFONO_010, CELULAR_010, CORREO_010, EMERGENCIA_010, PARENTESCO_010, NIVELACADEMICO_010, POSTGRADO_010, MAESTRIA_010, LUGARTRABAJO_010, PUESTO_010, TELEFONOEMPRESA_010 from KDSA010A where ESTUDIANTE_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}
		
		return $mDatos;
	}

	/*****Detalle Documento (KDSA011A)***********/

	function fxGuardarDetDocumento($msCodigo, $msArchivo, $msDescripcion, $msRuta)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA011A (ESTUDIANTE_REL, ARCHIVO_REL, DESC_011, RUTA_011) values (?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msArchivo, $msDescripcion, $msRuta]);
	}
	
	function fxBorrarDetDocumento($msCodigo, $msImagen)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA011A where ESTUDIANTE_REL = ? and ARCHIVO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msImagen]);
	}
	
	function fxDevuelveDetDocumento($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select ESTUDIANTE_REL, ARCHIVO_REL, DESC_011, RUTA_011 from KDSA011A where ESTUDIANTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
?>