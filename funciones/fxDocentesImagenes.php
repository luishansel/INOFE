<?php
require_once ("fxGeneral.php");
require_once ("fxDocentes.php");

if (is_array($_FILES) && count($_FILES) > 0) {
	$msDocente = $_POST["txtCodDocente"];
	$msDescripcion = $_POST["txtDescripcion"];
	$msArchivo = $_FILES['archivo']['name'];
	$msRuta = 'imagenes/docentes/'.$msDocente."/".$_FILES['archivo']['name'];
	$miCarpeta = '../imagenes/docentes/'.$msDocente;
	if (!file_exists($miCarpeta)) {
		mkdir($miCarpeta, 0777, true);
	}

	if (move_uploaded_file($_FILES["archivo"]["tmp_name"], $miCarpeta."/".$_FILES['archivo']['name'])) {
		fxGuardarDetDocumento ($msDocente, $msArchivo, $msDescripcion, $msRuta);
		
		//Construye el InnerHTHL del DIV contenedor
		$mnCuenta = 0;
		$texto = '<table width="100%">';
		
		$mDatos = fxDevuelveDetDocumento($msDocente);
		while ($Fila = $mDatos->fetch())
		{
			$extensionImg = strtoupper(substr($Fila["IMAGEN_REL"], -3));
			if ($mnCuenta == 0) {
				$texto .= '<tr>';
			}
			$texto .= '<td width="23%" valign="top" style="margin-left:1%; margin-right:1%">';
			$texto .= '<img src="imagenes/imageDel.png"  id="' . trim($Fila["IMAGEN_REL"]) . '" style="cursor:pointer" onclick="borrarImagen(this)"/><label style="font-size: small"> Borrar ' . trim($Fila["IMAGEN_REL"]) . '</label>';
			if ($extensionImg != 'PDF')
				$texto .= '<br/><a href="' . trim($Fila["RUTA_108"]) . '" target="_blank"><img src="' . trim($Fila["RUTA_108"]) . '" style="width:100%"/></a>';
			else
				$texto .= '<br/><a href="' . trim($Fila["RUTA_108"]) . '" target="_blank"><img src="imagenes/pdf.png" style="width:80%"/></a>';
			$texto .= '<br/><div>' . trim($Fila["DESC_108"]) . '</div';
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

if (isset($_POST["CodDocente"]) and isset($_POST["CodImagen"])) {
	$msDocente = $_POST["CodDocente"];
	$msImagen = $_POST["CodImagen"];
	$msRuta = '../imagenes/docentes/'.$msDocente.'/'.$msImagen;

	if (array_map('unlink', glob($msRuta))) {
		fxBorrarDetDocumento ($msDocente, $msImagen);
		
		//Construye el InnerHTHL del DIV contenedor
		$mnCuenta = 0;
		$texto = '<table width="100%">';
		
		$mDatos = fxDevuelveDetDocumento($msDocente);
		while ($Fila = $mDatos->fetch())
		{
			$extensionImg = strtoupper(substr($Fila["IMAGEN_REL"], -3));
			if ($mnCuenta == 0) {
				$texto .= '<tr>';
			}
			$texto .= '<td width="23%" valign="top" style="margin-left:1%; margin-right:1%">';
			$texto .= '<img src="imagenes/imageDel.png"  id="' . trim($Fila["IMAGEN_REL"]) . '" style="cursor:pointer" onclick="borrarImagen(this)"/><label style="font-size: small"> Borrar ' . trim($Fila["IMAGEN_REL"]) . '</label>';
			if ($extensionImg != 'PDF')
				$texto .= '<br/><a href="' . trim($Fila["RUTA_108"]) . '" target="_blank"><img src="' . trim($Fila["RUTA_108"]) . '" style="width:100%"/></a>';
			else
				$texto .= '<br/><a href="' . trim($Fila["RUTA_108"]) . '" target="_blank"><img src="imagenes/pdf.png" style="width:80%"/></a>';
			$texto .= '<br/><div>' . trim($Fila["DESC_108"]) . '</div';
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