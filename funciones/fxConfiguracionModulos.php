<?php
    function fxGuardarConfModulos($msCurso)
    {
        $m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(CFGMODULO_REL), 3), 0) as Ultimo from KDSA110A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
        $msCodigo = "CM" . str_repeat("0", 5 - $Longitud) . trim($Numero);
        
        $msConsulta = "insert into KDSA110A (CFGMODULO_REL, CURSO_110) values(?, ?)";
        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msCurso]);
        return ($msCodigo);
    }

    function fxModificarConfModulos($msCodigo, $msCurso)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA110A set CURSO_110 = ? where CFGMODULO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCurso, $msCodigo]);
    }

    function fxBorrarConfModulos($msCodigo)
	{
        $m_cnx_MySQL = fxAbrirConexion();
        //Borra el Detalle
		$msConsulta = "delete from KDSA111A where CFGMODULO_REL = ?";
        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
        //Borra la Cabecera
        $msConsulta = "delete from KDSA110A where CFGMODULO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
    }
    
    function fxDevuelveConfModulos($mbLlenaGrid, $msCodigo = "")
	{
        $m_cnx_MySQL = fxAbrirConexion();
        if ($mbLlenaGrid == 1)
        {
            $msConsulta = "select CFGMODULO_REL, CURSO_110 from KDSA110A order by CFGMODULO_REL desc";
            $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		    $mDatos->execute();
        }
        else
        {
            $msConsulta = "select CFGMODULO_REL, CURSO_110 from KDSA110A where CFGMODULO_REL = ?";
            $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		    $mDatos->execute([$msCodigo]);
        }
		return $mDatos;
    }
    
    function fxGuardarDetConfModulos($msCodigo, $mnDetConfModulos, $msDescripcion)
    {
        $m_cnx_MySQL = fxAbrirConexion();
        $msConsulta = "insert into KDSA111A (CFGMODULO_REL, DETCFGMODULO_REL, DESC_111) values(?, ?, ?)";
        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $mnDetConfModulos, $msDescripcion]);
    }

    function fxBorrarDetConfModulos($msCodigo)
    {
        $m_cnx_MySQL = fxAbrirConexion();
        $msConsulta = "delete from KDSA111A where CFGMODULO_REL = ?";
        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
    }

    function fxDevuelveDetConfModulos($msCodigo)
    {
        $m_cnx_MySQL = fxAbrirConexion();
        $msConsulta = "select CFGMODULO_REL, DETCFGMODULO_REL, DESC_111 from KDSA111A where CFGMODULO_REL = ?";
        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
    }
?>