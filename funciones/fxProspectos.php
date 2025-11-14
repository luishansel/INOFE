<?php
	function fxGuardarProspectos($msNombre, $mnTipo, $msTelefono, $msCorreo, $msFechaIngreso, $msFechaVenc, $msCedulaRuc, $msNombreContacto, $msTelContacto, $msPatronal, $msUsuario, $mbActivo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(PROSPECTO_REL), 3), 0) as Ultimo from KDSA060A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "PT" . str_repeat("0", 8 - $Longitud) . trim($Numero);
		$msConsulta = "insert into KDSA060A (PROSPECTO_REL, NOMBRE_060, TIPO_060, TELEFONO_060, CORREO_060, FECHAINGRESO_060, FECHAVENC_060, CEDULARUC_060, NOMBRECONTACTO_060, TELEFONOCONTACTO_060, PATRONAL_060, USUARIO_060, ACTIVO_060) ";
		$msConsulta .= "values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msNombre, $mnTipo, $msTelefono, $msCorreo, $msFechaIngreso, $msFechaVenc, $msCedulaRuc, $msNombreContacto, $msTelContacto, $msPatronal, $msUsuario, $mbActivo]);
		return ($msCodigo);
	}

	function fxModificarProspectos($msCodigo, $msNombre, $mnTipo, $msTelefono, $msCorreo, $msFechaIngreso, $msFechaVenc, $msCedulaRuc, $msNombreContacto, $msTelContacto, $msPatronal, $mbActivo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA060A set NOMBRE_060 = ?, TIPO_060 = ?, TELEFONO_060 = ?, CORREO_060 = ?, FECHAINGRESO_060 = ?, ";
		$msConsulta .= "FECHAVENC_060 = ?, CEDULARUC_060 = ?, NOMBRECONTACTO_060 = ?, TELEFONOCONTACTO_060 = ?, PATRONAL_060 = ?, ";
		$msConsulta .= "ACTIVO_060 = ? where PROSPECTO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msNombre, $mnTipo, $msTelefono, $msCorreo, $msFechaIngreso, $msFechaVenc, $msCedulaRuc, $msNombreContacto, $msTelContacto, $msPatronal, $mbActivo, $msCodigo]);
		return ($msCodigo);
	}
	
	function fxBorrarProspectos($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA060A where PROSPECTO_REL = '" .trim($msCodigo). "'";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
		
	function fxDevuelveProspectos($mbLlenaGrid, $msCodigo = "")
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		if ($mbLlenaGrid == 1)
		{
			$msConsulta = "select PROSPECTO_REL, NOMBRE_060, FECHAINGRESO_060, NOMBRECONTACTO_060, TELEFONOCONTACTO_060, (case ACTIVO_060 when 0 then '' else 'X' end) as ACTIVO_060 from KDSA060A order by PROSPECTO_REL desc";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute();
		}
		else
		{
			$msConsulta = "select PROSPECTO_REL, NOMBRE_060, TIPO_060, TELEFONO_060, CORREO_060, FECHAINGRESO_060, FECHAVENC_060, CEDULARUC_060, NOMBRECONTACTO_060, TELEFONOCONTACTO_060, PATRONAL_060, USUARIO_060, ACTIVO_060 from KDSA060A where PROSPECTO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}

		return $mDatos;
	}
	
	function fxDevuelveDetProspectos($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select KDSA061A.MATRICULA_REL, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE, NOMBRE_020 from KDSA061A, KDSA030A, KDSA020A, KDSA010A where KDSA061A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and KDSA061A.PROSPECTO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
?>