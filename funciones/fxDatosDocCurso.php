<?php
require_once ("fxGeneral.php");
require_once ("fxDocCursos.php");

if (is_array($_FILES) && count($_FILES) > 0) {
	$msDocumentos = $_POST["txtCodDocumentos"];
	$msArchivo = $_FILES['archivo']['name'];
	$msRuta = "docCurso/" . $msDocumentos. "/" . $_FILES['archivo']['name'];
	$miCarpeta = '../docCurso/'.$msDocumentos;
	if (!file_exists($miCarpeta)) {
		mkdir($miCarpeta, 0777, true);
	}

	if (move_uploaded_file($_FILES["archivo"]["tmp_name"], $miCarpeta."/".$_FILES['archivo']['name'])) {
		fxGuardarDetDocCurso ($msDocumentos, $msArchivo, $msRuta);
		
		//Construye el contenido del grid
		$mDatos = fxDevuelveDetDocCurso($msDocumentos);
		$mnRegistros = $mDatos->rowCount();
		$msResultado = "[";
		$i = 1;

		while ($mFila = $mDatos->fetch()){
			$mnConsecutivo = $mFila["DOCCURSOCONS_REL"];
			$msDocArchivo = $mFila["ARCHIVO_201"];
			$msRuta = $mFila["RUTA_201"];

			$msResultado .= '{"consecutivo":"' . $mnConsecutivo . '", "archivo":"' . $msDocArchivo . '","ruta":"' . $msRuta . '"}';
			
			if ($i != $mnRegistros)
            	$msResultado .= ',';

        	$i++;
		}
		$msResultado .= "]";
		echo $msResultado;
	} else {
		echo "";
	}
} else {
    echo "";
}

if (isset($_POST["codDocumento"]) and isset($_POST["codConsecutivo"]) and isset($_POST["msRuta"])) {
	$m_cnx_MySQL = fxAbrirConexion();
	$msDocCurso = $_POST["codDocumento"];
	$mnConsecutivo = intval($_POST["codConsecutivo"]);
	$pRuta = $_POST["msRuta"];

	$msConsulta = "select RUTA_201 from KDSA201A where DOCCURSO_REL = ? and DOCCURSOCONS_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msDocCurso, $mnConsecutivo]);
	$mFila = $mDatos->fetch();
	$msArchivo = $mFila["RUTA_201"];
	$msRuta = '../' . $msArchivo;

	if (array_map('unlink', glob($msRuta))) {
		fxBorrarDetDocCurso ($msDocCurso, $mnConsecutivo);
		
		//Construye el contenido del grid
		$mDatos = fxDevuelveDetDocCurso($msDocCurso);
		$mnRegistros = $mDatos->rowCount();
		$msResultado = "[";
		$i = 1;

		while ($mFila = $mDatos->fetch()){
			$mnConsecutivo = $mFila["DOCCURSOCONS_REL"];
			$msDocArchivo = $mFila["ARCHIVO_201"];
			$msRuta = $mFila["RUTA_201"];

			$msResultado .= '{"consecutivo":"' . $mnConsecutivo . '", "archivo":"' . $msDocArchivo . '","ruta":"' . $msRuta . '"}';
			
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