<?php
require_once ("fxGeneral.php");
require_once ("fxDocCursos.php");

/*Obtiene los documentos obligatorios de un curso y los escribe en el grid del catálogo*/
if (isset($_POST["codDocumento"])) {
	$m_cnx_MySQL = fxAbrirConexion();
	$codDocumento = $_POST["codDocumento"];

	$mDatos = fxDevuelveDetDocCurso($codDocumento);
	$mnRegistros = $mDatos->rowCount();
	$i = 1;

	if ($mnRegistros > 0)
	{
		$msResultado = "[";
		while ($mFila = $mDatos->fetch()){
			$mnConsecutivo = $mFila["DOCCURSOCONS_REL"];
			$msArchivo = $mFila["ARCHIVO_201"];
			$msRuta = $mFila["RUTA_201"];

			$msResultado .= '{"documento":"' . $mnConsecutivo . '", "archivo":"' . $msArchivo . '","ruta":"' . $msRuta . '"}';
			
			if ($i != $mnRegistros)
            	$msResultado .= ',';

        	$i++;
		}
		$msResultado .= "]";
		echo $msResultado;
	} else {
		echo "";
	};
} else {
	echo "";
}
?>