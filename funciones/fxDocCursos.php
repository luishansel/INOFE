<?php
	function fxGuardarDocCurso($msCurso)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(DOCCURSO_REL), 3), 0) as Ultimo from KDSA200A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "DC" . str_repeat("0", 7 - $Longitud) . trim($Numero);
		
		$msConsulta = "insert into KDSA200A (DOCCURSO_REL, CURSO_200) values(?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msCurso]);

		return ($msCodigo);
	}
	
	function fxModificarDocCurso($msCodigo, $msCurso)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA200A set CURSO_200 = ? where DOCCURSO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCurso, $msCodigo]);
	}
	
	function fxBorrarDocCurso($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA200A where DOCCURSO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveDocCurso($mbLlenaGrid, $msCodigo = "")
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		if ($mbLlenaGrid == 1)
		{
			$msConsulta = "select DOCCURSO_REL, CURSO_200 from KDSA200A order by DOCCURSO_REL desc";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute();
		}
		else
		{
			$msConsulta = "select DOCCURSO_REL, CURSO_200 from KDSA200A where DOCCURSO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}

		return $mDatos;
	}

	function fxGuardarDetDocCurso($msCodigo, $msArchivo, $msRuta)
	{
		$m_cnx_MySQL = fxAbrirConexion();

		$msConsulta = "Select ifnull(max(DOCCURSOCONS_REL), 0) as Ultimo from KDSA201A where DOCCURSO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		$mFila = $mDatos->fetch();
		$mnDocCursoCons = intval($mFila["Ultimo"]);
		$mnDocCursoCons += 1;

		$msConsulta = "insert into KDSA201A (DOCCURSO_REL, DOCCURSOCONS_REL, ARCHIVO_201, RUTA_201) values (?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $mnDocCursoCons, $msArchivo, $msRuta]);
	}

	function fxBorrarDetDocCurso($msCodigo, $mnConsecutivo = 0)
	{
		$m_cnx_MySQL = fxAbrirConexion();

		if ($mnConsecutivo == 0)
		{
			$msConsulta = "delete from KDSA201A where DOCCURSO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}
		else
		{
			$msConsulta = "delete from KDSA201A where DOCCURSO_REL = ? and DOCCURSOCONS_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo, $mnConsecutivo]);
		}
	}

	function fxDevuelveDetDocCurso($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select DOCCURSO_REL, DOCCURSOCONS_REL, ARCHIVO_201, RUTA_201 from KDSA201A where DOCCURSO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
?>