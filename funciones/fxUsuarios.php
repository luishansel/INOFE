<?php
	function fxVerificaUsuario()
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msUsuario = $_SESSION["gsUsuario"];
		$msClave = $_SESSION["gsClave"];
		
		$msConsulta = "Select * from KDSA002A where USUARIO_REL =? and CLAVE_002 =?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msUsuario, $msClave]);
		$mnRegistros = $mDatos->rowCount();
		return $mnRegistros;
	}
	
	function fxVerificaAdministrador()
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msUsuario = $_SESSION["gsUsuario"];
		
		$msConsulta = "Select * from KDSA002A where USUARIO_REL =? and SUPERVISOR_002 = 1";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msUsuario]);
		$mnRegistros = $mDatos->rowCount();
		return $mnRegistros;
	}

	function fxVerificaAcademico()
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msUsuario = $_SESSION["gsUsuario"];
		
		$msConsulta = "Select * from KDSA002A where USUARIO_REL =? and ACADEMICO_002 = 1";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msUsuario]);
		$mnRegistros = $mDatos->rowCount();
		return $mnRegistros;
	}
	
	function fxExisteUsuario($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		$msConsulta = "select NOMBRE_002 from KDSA002A where USUARIO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		$mnRegistros = $mDatos->rowCount();
		return $mnRegistros;
	}
	
	function fxGuardarUsuario($msCodigo, $msNombre, $msCorreo, $msClave, $mbAcademico, $mbSupervisor)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msEncriptado = crypt($msClave, '_appwKDSA');
		$msConsulta = "insert into KDSA002A (USUARIO_REL, NOMBRE_002, CORREO_002, CLAVE_002, ACADEMICO_002, SUPERVISOR_002, ACTIVO_002) values(?, ?, ?, ?, ?, ?, ?)";
		$mResultado = $m_cnx_MySQL->prepare($msConsulta);
		$mResultado->execute([$msCodigo, $msNombre, $msCorreo, $msEncriptado, $mbAcademico, $mbSupervisor, 1]);
	}
	
	function fxModificarUsuario($msCodigo, $msNombre, $msCorreo, $msClave, $mbAcademico, $mbSupervisor, $mnActivo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		$msEncriptado = crypt($msClave, '_appwKDSA');
		$msConsulta = "update KDSA002A set NOMBRE_002=?, CORREO_002=?, CLAVE_002=?, ACADEMICO_002 = ?, SUPERVISOR_002=?, ACTIVO_002=? where USUARIO_REL = ?";
		$mResultado = $m_cnx_MySQL->prepare($msConsulta);
		$mResultado->execute([$msNombre, $msCorreo, $msEncriptado, $mbAcademico, $mbSupervisor, $mnActivo, $msCodigo]);
	}
	
	function fxClaveUsuario($msCodigo, $msNombre, $msClave)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		$msEncriptado = crypt($msClave, '_appwKDSA');
		$msConsulta = "update KDSA002A set NOMBRE_002=?, CLAVE_002=? where USUARIO_REL = ?";
		$mResultado = $m_cnx_MySQL->prepare($msConsulta);
		$mResultado->execute([$msNombre, $msEncriptado, $msCodigo]);
	}
	
	function fxDesactivarUsuario($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		$msConsulta = "update KDSA002A set ACTIVO_002 = 0 where USUARIO_REL = ?";
		$mResultado = $m_cnx_MySQL->prepare($msConsulta);
		$mResultado->execute([$msCodigo]);
	}
	
	function fxDevuelveUsuario($mbLlenaGrid, $msCodigo = "")
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		if ($mbLlenaGrid == 1)
		{
			$msConsulta = "select USUARIO_REL, NOMBRE_002, CORREO_002, SUPERVISOR_002, ACTIVO_002 from KDSA002A";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute();
		}
		else
		{
			$msConsulta = "select USUARIO_REL, NOMBRE_002, CORREO_002, CLAVE_002, ACADEMICO_002, SUPERVISOR_002, ACTIVO_002 from KDSA002A where USUARIO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}
		
		return $mDatos;
	}
	
	function fxPermisoUsuario($msPagina, &$mbAgregar = 0, &$mbModificar = 0, &$mbBorrar = 0, &$mbAnular = 0)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msUsuario = $_SESSION["gsUsuario"];
		
		$msConsulta = "select KDSA005A.PAGINA_REL, KDSA005A.GRUPO_REL, INCLUIR_005, MODIFICAR_005, BORRAR_005, ANULAR_005 ";
		$msConsulta .= "from KDSA005A, KDSA006A where KDSA006A.GRUPO_REL = KDSA005A.GRUPO_REL and KDSA006A.USUARIO_REL = ? and PAGINA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msUsuario, $msPagina]);
		$mnRegistros = $mDatos->rowCount();
		if ($mnRegistros > 0)
		{
			while($Fila = $mDatos->fetch())
			{
				if ($mbAgregar == 0)
					$mbAgregar = $Fila["INCLUIR_005"];

				if ($mbModificar == 0)
					$mbModificar = $Fila["MODIFICAR_005"];

				if ($mbBorrar == 0)
					$mbBorrar = $Fila["BORRAR_005"];

				if ($mbAnular == 0)
					$mbAnular = $Fila["ANULAR_005"];
			}
		}
		return $mnRegistros;
	}
?>