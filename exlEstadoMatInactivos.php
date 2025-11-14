<?php
	session_start();
	if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
	{
		echo('<meta http-equiv="Refresh" content="0;url=index.php">');
		exit('');
    }
	
	require_once ("funciones/fxGeneral.php");
	require_once ("funciones/fxUsuarios.php");
	$Registro = fxVerificaUsuario();

    if ($Registro == 0)
	{
	?>
		<div class="container text-center">
			<div id="DivContenido">
				<img src="imagenes/errordeacceso.png"/>
			</div>
		</div>
	<?php }
	else
	{
        function DevuelveFecha($Fecha)
        {
            $FechaDividida = explode("-", $Fecha);
            
            $Anno = $FechaDividida[0];
            $Mes = $FechaDividida[1];
            $Dia = $FechaDividida[2];
            
            switch ($Mes)
            {
                case "01":
                    $NombreMes = "Enero";
                    break;
                case "02":
                    $NombreMes = "Febrero";
                    break;
                case "03":
                    $NombreMes = "Marzo";
                    break;
                case "04":
                    $NombreMes = "Abril";
                    break;
                case "05":
                    $NombreMes = "Mayo";
                    break;
                case "06":
                    $NombreMes = "Junio";
                    break;
                case "07":
                    $NombreMes = "Julio";
                    break;
                case "08":
                    $NombreMes = "Agosto";
                    break;
                case "09":
                    $NombreMes = "Septiembre";
                    break;
                case "10":
                    $NombreMes = "Octubre";
                    break;
                case "11":
                    $NombreMes = "Noviembre";
                    break;
                case "12":
                    $NombreMes = "Diciembre";
                    break;
            }
            return ($Dia . " de " . $NombreMes . " de " . $Anno);
        }

        $mnTipoRep = $_POST["tipoRep"];
        $mdFechaIni = $_POST["fechaIni"];
        $mdFechaFin = $_POST["fechaFin"];
        $mbColorea = 0;
        $mnTotalInicial = 0;
        $mnTotalActivo = 0;
        $mnTotalInactivo = 0;
        $mnTotalCertificado = 0;
        $mnTotalDesercion = 0;
        $mnTotalBaja = 0;
        
        header('Content-type:application/xls; charset=UTF-8');
		header('Content-Disposition: attachment; filename=EstadoMatInactivos' . date("YmdHis") . '.xls');

        $m_cnx_MySQL = fxAbrirConexion();
        if ($mnTipoRep == 0)
        {
            $msConsulta = "select CURSO_REL, NOMBRE_020, FECHAINI_020, FECHAFIN_020, concat(CONVOCATORIA_020, ' / ', 'G', GRUPO_020) as CONVOCATORIA from KDSA020A where ACTIVO_020 = 0 and FECHAINI_020 between ? and ?";
            $msPeriodo = "Cursos inactivos que iniciaron entre " . DevuelveFecha($mdFechaIni) . " y " . DevuelveFecha($mdFechaFin);
        }
        else
        {
            $msConsulta = "select CURSO_REL, NOMBRE_020, FECHAINI_020, FECHAFIN_020, concat(CONVOCATORIA_020, ' / ', 'G', GRUPO_020) as CONVOCATORIA from KDSA020A where ACTIVO_020 = 0 and FECHAFIN_020 between ? and ?";
            $msPeriodo = "Cursos inactivos que finalizaron entre " . DevuelveFecha($mdFechaIni) . " y " . DevuelveFecha($mdFechaFin);
        }
        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
        $mDatos->execute([$mdFechaIni, $mdFechaFin]);

        echo('<table>');
        echo('<tr>');
        echo('<td style="font-size: large; font-weight: bold; color: rgb(0,100,255)">ESTADO DE LAS MATRICULAS</td>');
        echo('</tr>');
        echo('<tr>');
        echo('<td style="font-size: large; font-weight: bold; color: rgb(0,100,255)">' . trim($msPeriodo) .'</td>');
        echo('</tr>');
        echo('</table>');
        echo('<br>');
        echo('<table>');
        echo('<tr>');
        echo('<th align="left" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">Curso</th>');
        echo('<th align="left" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">Docente</th>');
        echo('<th align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">' . utf8_decode("Período de clases") . '</th>');
        echo('<th align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">Inicial</th>');
        echo('<th align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">Certificado</th>');
        echo('<th align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">Sin certificar</th>');
        echo('<th align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">' . utf8_decode("Deserción") . '</th>');
        echo('<th align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">Baja</th>');
        echo('<th align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">' . utf8_decode("Retención") . '</th>');
        echo('</tr>');
            
        while ($Fila = $mDatos->fetch())
        {
            $msCurso = $Fila["CURSO_REL"];
            $msNomCurso = utf8_decode(html_entity_decode($Fila["NOMBRE_020"]));
            $msConvocatoria = $Fila["CONVOCATORIA"];
            $fechaIni = date_create_from_format('Y-m-d', $Fila["FECHAINI_020"]);
            $fechaFin = date_create_from_format('Y-m-d', $Fila["FECHAFIN_020"]);
            $msPeriodo = date_format($fechaIni, 'd-m-Y') . ' / ' . date_format($fechaFin, 'd-m-Y');

            $msDocente = "";
            $msConsulta = "select distinct NOMBRE_100 from KDSA100A join KDSA021A on KDSA100A.DOCENTE_REL = KDSA021A.DOCENTE_REL where CURSO_REL = ?";
            $mAux = $m_cnx_MySQL->prepare($msConsulta);
            $mAux->execute([$msCurso]);
            while ($fAux = $mAux->fetch())
            {
                if ($msDocente == "")
                    $msDocente = utf8_decode(html_entity_decode($fAux["NOMBRE_100"]));
                else
                    $msDocente .= ", " . utf8_decode(html_entity_decode($fAux["NOMBRE_100"]));
            }
            $msConsulta = "select  0 as ESTADO, COUNT(MATRICULA_REL) as CONTEO from KDSA030A where ESTADO_030 = 0 and CURSO_REL = ? union ";
            $msConsulta .= "select  1 as ESTADO, COUNT(MATRICULA_REL) as CONTEO from KDSA030A where ESTADO_030 = 1 and CURSO_REL = ? union ";
            $msConsulta .= "select  2 as ESTADO, COUNT(MATRICULA_REL) as CONTEO from KDSA030A where ESTADO_030 = 2 and CURSO_REL = ? union ";
            $msConsulta .= "select  3 as ESTADO, COUNT(MATRICULA_REL) as CONTEO from KDSA030A where ESTADO_030 = 3 and CURSO_REL = ? union ";
            $msConsulta .= "select  4 as ESTADO, COUNT(MATRICULA_REL) as CONTEO from KDSA030A where ESTADO_030 = 4 and CURSO_REL = ?";
            $mAux = $m_cnx_MySQL->prepare($msConsulta);
            $mAux->execute([$msCurso, $msCurso, $msCurso, $msCurso, $msCurso]);

            $mnInicial = 0;

            while ($fAux = $mAux->fetch())
            {
                $mnEstado = $fAux["ESTADO"];
                switch($mnEstado)
                {
                    case 0:
                        $mnActivo = $fAux["CONTEO"];
                        $mnInicial += $mnActivo;
                        $mnTotalInicial += $mnActivo;
                        $mnTotalActivo += $mnActivo;
                        break;
                    case 1:
                        $mnInactivo = $fAux["CONTEO"];
                        $mnInicial += $mnInactivo;
                        $mnTotalInicial += $mnInactivo;
                        $mnTotalInactivo += $mnInactivo;
                        break;
                    case 2:
                        $mnDesercion = $fAux["CONTEO"];
                        $mnInicial += $mnDesercion;
                        $mnTotalInicial += $mnDesercion;
                        $mnTotalDesercion += $mnDesercion;
                        break;
                    case 3:
                        $mnCertificado = $fAux["CONTEO"];
                        $mnInicial += $mnCertificado;
                        $mnTotalInicial += $mnCertificado;
                        $mnTotalCertificado += $mnCertificado;
                        break;
                    case 4:
                        $mnBaja = $fAux["CONTEO"];
                        $mnTotalBaja += $mnBaja;
                        break;
                }
            }

            if ($mnInicial == 0)
                $mnRetencion = 0;
            else
                $mnRetencion = (($mnActivo + $mnCertificado) * 100) / $mnInicial;
            
            echo('<tr>');
            if ($mbColorea == 0)
            {
                echo('<td align="left">' . trim($msNomCurso) . ' (' . trim($msConvocatoria) . ')</td>');
                echo('<td align="left">' . trim($msDocente) . '</td>');
                echo('<td align="center">' . trim($msPeriodo) . '</td>');
                echo('<td align="center">' . trim($mnInicial) . '</td>');
                echo('<td align="center">' . trim($mnCertificado) . '</td>');
                echo('<td align="center">' . trim($mnActivo) . '</td>');
                echo('<td align="center">' . trim($mnDesercion) . '</td>');
                echo('<td align="center">' . trim($mnBaja) . '</td>');
                echo('<td align="center">' . number_format($mnRetencion, 2) . '</td>');
            }
            else
            {
                echo('<td align="left" style="background-color: #f2f2f2;">' . trim($msNomCurso) . ' (' . trim($msConvocatoria) . ')</td>');
                echo('<td align="left" style="background-color: #f2f2f2;">' . trim($msDocente) . '</td>');
                echo('<td align="center" style="background-color: #f2f2f2;">' . trim($msPeriodo) . '</td>');
                echo('<td align="center" style="background-color: #f2f2f2;">' . trim($mnInicial) . '</td>');
                echo('<td align="center" style="background-color: #f2f2f2;">' . trim($mnCertificado) . '</td>');
                echo('<td align="center" style="background-color: #f2f2f2;">' . trim($mnActivo) . '</td>');
                echo('<td align="center" style="background-color: #f2f2f2;">' . trim($mnDesercion) . '</td>');
                echo('<td align="center" style="background-color: #f2f2f2;">' . trim($mnBaja) . '</td>');
                echo('<td align="center" style="background-color: #f2f2f2;">' . number_format($mnRetencion, 2) . '</td>');
            }
            echo('</tr>');

            if ($mbColorea == 0)
                $mbColorea = 1;
            else
                $mbColorea = 0;
        }

        if ($mnTotalInicial == 0)
            $mnPromedioRetencion = 0;
        else
            $mnPromedioRetencion = (($mnTotalActivo + $mnTotalCertificado) / $mnTotalInicial) * 100;
        
        echo('<tr>');
        echo('<td align="left" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">&nbsp;</td>');
        echo('<td align="left" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">&nbsp;</td>');
        echo('<td align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">&nbsp;</td>');
        echo('<td align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">' . trim($mnTotalInicial) . '</td>');
        echo('<td align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">' . trim($mnTotalActivo) . '</td>');
        echo('<td align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">' . trim($mnTotalCertificado) . '</td>');
        echo('<td align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">' . trim($mnTotalDesercion) . '</td>');
        echo('<td align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">' . trim($mnTotalBaja) . '</td>');
        echo('<td align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">' . number_format($mnPromedioRetencion, 2) . '</td>');
        echo('</tr>');
        echo('</table>');
	}

    function fxTildes($msCadena){
        $msCadena = str_replace("&aacute;", "á", $msCadena);
        $msCadena = str_replace("&eacute;", "é", $msCadena);
        $msCadena = str_replace("&iacute;", "í", $msCadena);
        $msCadena = str_replace("&oacute;", "ó", $msCadena);
        $msCadena = str_replace("&uacute;", "ú", $msCadena);
        $msCadena = str_replace("&nacute;", "ñ", $msCadena);
        $msCadena = str_replace("&Nacute;", "Ñ", $msCadena);

        $msCadena = str_replace("&atilde;", "á", $msCadena);
        $msCadena = str_replace("&etilde;", "é", $msCadena);
        $msCadena = str_replace("&itilde;", "í", $msCadena);
        $msCadena = str_replace("&otilde;", "ó", $msCadena);
        $msCadena = str_replace("&utilde;", "ú", $msCadena);
        $msCadena = str_replace("&ntilde;", "ñ", $msCadena);
        $msCadena = str_replace("&Ntilde;", "Ñ", $msCadena);
        return $msCadena;
    }
?>