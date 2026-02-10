<?php
session_start();

require_once ("funciones/fxGeneral.php");
require_once ("funciones/fxTomosCertificacion.php");
require_once ("funciones/fxActaCertificacion.php");
require_once ("funciones/fxRegConsecutivos.php");
require_once ("tcpdf/tcpdf.php");

$m_cnx_MySQL = fxAbrirConexion();

function DevuelveFecha($mdFecha)
{
	$FechaDividida = explode("-", $mdFecha);
	
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
	return ($Dia . " días del mes de " . $NombreMes . " de " . $Anno);
}

function DevuelveFechaActa($Fecha)
{
	$FechaDividida = explode("-", $Fecha);
	
	$Anno = $FechaDividida[0];
	$Mes = $FechaDividida[1];
	$Dia = $FechaDividida[2];

	return ($Dia . "/" . $Mes . "/" . $Anno);
}

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set default monospaced font
//$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(PDF_MARGIN_LEFT, 16, PDF_MARGIN_RIGHT);

// set auto page breaks
$pdf->SetAutoPageBreak(FALSE, PDF_MARGIN_BOTTOM); //LHVG 20260122 Anteriormente estaba TRUE

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/spa.php')) {
	require_once(dirname(__FILE__).'/lang/spa.php');
	$pdf->setLanguageArray($l);
}

$m_cnx_MySQL = fxAbrirConexion();

$msConsulta = "select distinct TOMO_REL from KDSA190A";
$mTomos = $m_cnx_MySQL->prepare($msConsulta);
$mTomos->execute();

while ($rowTomo = $mTomos->fetch())
{
	$pdf->AddPage();
	$msTomo = $rowTomo["TOMO_REL"];

	$msConsulta = "select ACTA_REL, ACTA_190, YEAR(FECHA_190) as ANNO, FECHA_190, NOMBRE_020, FECHAINI_020, FECHAFIN_020, CONVOCATORIA_020, GRUPO_020 ";
	$msConsulta .= "from KDSA190A join KDSA020A on KDSA190A.CURSO_REL = KDSA020A.CURSO_REL where TOMO_REL = ? order by ACTA_REL";
	$mActas = $m_cnx_MySQL->prepare($msConsulta);
	$mActas->execute([$msTomo]);

	$mnFolio = 2;
	$mnLinea = 8;

	while ($rowActa = $mActas->fetch())
	{
		$msActa = $rowActa["ACTA_REL"];
		$mnActa = $rowActa["ACTA_190"];
		$mnAnno = $rowActa["ANNO"];
		$mdFecha = $rowActa["FECHA_190"];
		$mdFechaIni = $rowActa["FECHAINI_020"];
		$mdFechaFin = $rowActa["FECHAFIN_020"];
		$msNombreCurso = $rowActa["NOMBRE_020"];
		$msConvocatoria = $rowActa["CONVOCATORIA_020"];
		$mnGrupo = $rowActa["GRUPO_020"];
		$mnLineaIni = $mnLinea;
		$mnFolioIni = $mnFolio;

		$pdf->SetY($mnLinea);
		$pdf->SetFont('dejavusansmono', 'B', 15);
		$pdf->Cell(180, 20, 'ACTA '. $mnActa, 0, 0, 'C', 0, '');
		$mnLinea += 8;

		if ($mnLinea >= 240) //240 líneas máximo (3O líneas de texto con 8 de altura) --LHVG 20230915
		{
			$mnLinea = 8;
			$mnFolio ++;
			$pdf->AddPage();
		}

		$pdf->SetFont('dejavusansmono', '', 15);
		$msTexto = 'En la ciudad de Managua a los ' . DevuelveFecha($mdFecha) . ', se procede a registrar la certificación ';
		$msTexto .= 'otorgada a los egresados del curso ' . $msNombreCurso . ', convocatoria ' . $msConvocatoria;
		$msTexto .= ', grupo ' . $mnGrupo . '; impartido por KDSA en el período del ' . DevuelveFechaActa($mdFechaIni);
		$msTexto .= ' al ' . DevuelveFechaActa($mdFechaFin);

		$mArregloTexto = explode(" ", $msTexto);
		$msTextoCelda = "";

		foreach($mArregloTexto as $index=>$mTexto)
		{
			$mnLongitudTexto = strlen($msTextoCelda);

			if ($mnLongitudTexto + strlen($mTexto) + 1 < 60)
			{
				if ($msTextoCelda == "")
					$msTextoCelda = $mTexto;
				else
					$msTextoCelda .= ' ' . $mTexto;
			}
			else
			{
				$pdf->SetY($mnLinea);
				$pdf->Cell(180, 20, $msTextoCelda, 0, 0, 'J', 0, '');
				$msTextoCelda = $mTexto;
				$mnLinea += 8;

				if ($mnLinea >= 240) //240 líneas máximo (3O líneas de texto con 8 de altura) --LHVG 20230915
				{
					$mnLinea = 8;
					$mnFolio ++;
					$pdf->AddPage();
				}
			}

			if ($index == count($mArregloTexto) - 1)
			{
				$pdf->SetY($mnLinea);
				$pdf->Cell(180, 20, $msTextoCelda . '.', 0, 0, 'J', 0, '');
			}
		}

		$msConsulta = "SELECT KDSA191A.MATRICULA_REL, REGISTRO_191, VERIFICACION_191, NOMBRES_010, APELLIDOS_010 from ";
		$msConsulta .= "KDSA191A, KDSA030A, KDSA010A where KDSA191A.MATRICULA_REL = KDSA030A.MATRICULA_REL ";
		$msConsulta .= "and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and ACTA_REL = ? order by REGISTRO_191";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$msActa]);

		while ($mAuxFila = $mAuxiliar->fetch())
		{
			$msMatricula = $mAuxFila["MATRICULA_REL"];
			$msNombres = $mAuxFila["NOMBRES_010"];
			$msApellidos = $mAuxFila["APELLIDOS_010"];
			$mnRegistro = $mAuxFila["REGISTRO_191"];
			$msVerificacion = $mAuxFila["VERIFICACION_191"];
			$msNombreCompleto = $msApellidos . ", " . $msNombres;
			
			$msTextoCelda = $mnRegistro . '-' . $mnAnno . ' ' . html_entity_decode($msNombreCompleto);
			
			$mnLinea += 8;
			if ($mnLinea >= 240) //240 líneas máximo (3O líneas de texto con 8 de altura) --LHVG 20230915
			{
				$mnLinea = 8;
				$mnFolio ++;
				$pdf->AddPage();
			}

			$pdf->SetY($mnLinea);
			$pdf->Cell(180, 20, $msTextoCelda, 0, 0, 'L', 0, '');

			$msConsulta = "update KDSA191A set FOLIO_191 = ? where ACTA_REL = ? and MATRICULA_REL = ?";
			$mDetalle = $m_cnx_MySQL->prepare($msConsulta);
			$mDetalle->execute([$mnFolio, $msActa, $msMatricula]);
			fxModificarUltimos($msTomo, $mnFolio, $mnActa);
		}

		$mnLinea += 8;
		if ($mnLinea >= 240) //240 líneas máximo (3O líneas de texto con 8 de altura) --LHVG 20231227
		{
			$mnLinea = 8;
			$mnFolio ++;
			$pdf->AddPage();
		}
		$pdf->SetY($mnLinea);
		$msTextoCelda = '****** FINAL DEL ACTA ' . $mnActa . ' ******';
		$pdf->Cell(180, 20, $msTextoCelda, 0, 0, 'C', 0, '');
		
		$msConsulta = "update KDSA190A set LINEAINI_190 = ?, LINEAFIN_190 = ?, FOLIOINI_190 = ? where ACTA_REL = ?";
		$mNumeracion = $m_cnx_MySQL->prepare($msConsulta);
		$mNumeracion->execute([$mnLineaIni, $mnLinea, $mnFolioIni, $msActa]);

		$mnLinea += 16;
	}
}
$pdf->Output();

function Tipo($mnTipo)
{
	switch ($mnTipo)
	{
		case 0:
			$msResultado = "Seminario";
			break;
		case 1:
			$msResultado = "Curso";
			break;
		case 2:
			$msResultado = "Carrera";
			break;
		case 3:
			$msResultado = "Taller";
			break;
		case 4:
			$msResultado = "Diplomado";
			break;
		case 5:
			$msResultado = "Webinar";
			break;
		case 6:
			$msResultado = "Workshop";
			break;
		case 7:
			$msResultado = "Teambuilding";
			break;
		case 8:
            $msResultado = "Bootcamp";
            break;
        case 9:
            $msResultado = "Programa";
            break;
        case 10:
            $msResultado = "Masterclass";
            break;
	}

	return $msResultado;
}
?>