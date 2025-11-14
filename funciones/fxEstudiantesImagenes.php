<?php
require_once ("fxGeneral.php");
require_once ("fxEstudiantes.php");

if (is_array($_FILES) && count($_FILES) > 0) {
	$msEstudiante = $_POST["txtEstudiante"];
	$msDescripcion = $_POST["txtDescripcion"];
	$msArchivo = $_FILES['archivo']['name'];
	$msRuta = 'imagenes/estudiantes/'.$msEstudiante."/".$_FILES['archivo']['name'];
	$miCarpeta = '../imagenes/estudiantes/'.$msEstudiante;
	if (!file_exists($miCarpeta)) {
		mkdir($miCarpeta, 0777, true);
	}

	if (move_uploaded_file($_FILES["archivo"]["tmp_name"], $miCarpeta."/".$_FILES['archivo']['name'])) {
		fxGuardarDetDocumento ($msEstudiante, $msArchivo, $msDescripcion, $msRuta);
		
		//Construye el InnerHTHL del DIV contenedor
		$mnCuenta = 0;
		$texto = '<table width="100%">';
		
		$mDatos = fxDevuelveDetDocumento($msEstudiante);
		while ($Fila = $mDatos->fetch())
		{
			$extensionImg = strtoupper(substr($Fila["ARCHIVO_REL"], -3));
			if ($mnCuenta == 0) {
				$texto .= '<tr>';
			}
			$texto .= '<td width="23%" valign="top" style="margin-left:1%; margin-right:1%">';
			$texto .= '<img src="imagenes/imageDel.png"  id="' . trim($Fila["ARCHIVO_REL"]) . '" style="cursor:pointer" onclick="borrarImagen(this)"/><label style="font-size: small"> Borrar ' . trim($Fila["ARCHIVO_REL"]) . '</label>';
			if ($extensionImg != 'PDF')
				$texto .= '<br/><a href="' . trim($Fila["RUTA_011"]) . '" target="_blank"><img src="' . trim($Fila["RUTA_011"]) . '" style="width:100%"/></a>';
			else
				$texto .= '<br/><a href="' . trim($Fila["RUTA_011"]) . '" target="_blank"><img src="imagenes/pdf.png" style="width:80%"/></a>';
			$texto .= '<br/><div>' . trim($Fila["DESC_011"]) . '</div';
			$texto .= '</td>';
			$mnCuenta++;
			if ($mnCuenta == 4) {
				$texto .= '</tr>';
				$mnCuenta = 0;
			}
		}
		if ($mnCuenta == 1) {
			$texto .= '<td></td><td></td><td></td></tr>';
		}
		if ($mnCuenta == 2) {
			$texto .= '<td></td><td></td></tr>';
		}
		if ($mnCuenta == 3) {
			$texto .= '<td></td></tr>';
		}
		
		$texto .= '</table>';
		
		echo $texto;
	} else {
		echo 0;
	}
} else {
    echo 0;
}

if (isset($_POST["CodEstudiante"]) and isset($_POST["CodImagen"])) {
	$msEstudiante = $_POST["CodEstudiante"];
	$msImagen = $_POST["CodImagen"];
	$msRuta = '../imagenes/estudiantes/'.$msEstudiante.'/'.$msImagen;

	if (array_map('unlink', glob($msRuta))) {
		fxBorrarDetDocumento ($msEstudiante, $msImagen);
		
		//Construye el InnerHTHL del DIV contenedor
		$mnCuenta = 0;
		$texto = '<table width="100%">';
		
		$mDatos = fxDevuelveDetDocumento($msEstudiante);
		while ($Fila = $mDatos->fetch())
		{
			$extensionImg = strtoupper(substr($Fila["ARCHIVO_REL"], -3));
			if ($mnCuenta == 0) {
				$texto .= '<tr>';
			}
			$texto .= '<td width="23%" valign="top" style="margin-left:1%; margin-right:1%">';
			$texto .= '<img src="imagenes/imageDel.png"  id="' . trim($Fila["ARCHIVO_REL"]) . '" style="cursor:pointer" onclick="borrarImagen(this)"/><label style="font-size: small"> Borrar ' . trim($Fila["ARCHIVO_REL"]) . '</label>';
			if ($extensionImg != 'PDF')
				$texto .= '<br/><a href="' . trim($Fila["RUTA_011"]) . '" target="_blank"><img src="' . trim($Fila["RUTA_011"]) . '" style="width:100%"/></a>';
			else
				$texto .= '<br/><a href="' . trim($Fila["RUTA_011"]) . '" target="_blank"><img src="imagenes/pdf.png" style="width:80%"/></a>';
			$texto .= '<br/><div>' . trim($Fila["DESC_011"]) . '</div';
			$texto .= '</td>';
			$mnCuenta++;
			if ($mnCuenta == 4) {
				$texto .= '</tr>';
				$mnCuenta = 0;
			}
		}
		if ($mnCuenta == 1) {
			$texto .= '<td></td><td></td><td></td></tr>';
		}
		if ($mnCuenta == 2) {
			$texto .= '<td></td><td></td></tr>';
		}
		if ($mnCuenta == 3) {
			$texto .= '<td></td></tr>';
		}
		
		$texto .= '</table>';
		
		echo $texto;
	} else {
		echo 0;
	};
} else {
	echo 0;
}
?>