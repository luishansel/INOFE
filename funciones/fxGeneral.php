<?php
	require_once ("datos.php");
	date_default_timezone_set("America/Managua");

	function depurar($cadena){echo('<script>alert("' . rtrim($cadena) . '")</script>');}
	
	function fxAbrirConexion()
	{
		$msUsuario = $_SESSION["gsUSR"];
		$msClave = $_SESSION["gsPWD"];
		$msBase = $_SESSION["gsBD"];
		$conexion = new PDO('mysql:host=localhost;dbname='.$msBase, $msUsuario, $msClave);
		$conexion->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		$conexion->exec("set names utf8");
		return $conexion;
	}

	//*****BITACORA**********************************************************//
	
	function fxAgregarBitacora($msUsuario, $msTabla, $msLlave1, $msLlave2, $msOperacion)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$mdFechaHoy = date('Y-m-d H:i:s');
		$msConsulta = "insert into KDSA000A (USUARIO_000, FECHA_000, TABLA_000, LLAVE1_000, LLAVE2_000, OPERACION_000) values (?,?,?,?,?,?)";
		$mResultado = $m_cnx_MySQL->prepare($msConsulta);
		$mResultado->execute([$msUsuario, $mdFechaHoy, $msTabla, $msLlave1, $msLlave2, $msOperacion]);
	}
	
	//*****PAGINAS DE LA APLICACION*****************************************//
	
	function fxDevuelvePaginas()
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select PAGINA_REL, DESC_004 from KDSA004A order by DESC_004";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		return $mDatos;
	}
?>