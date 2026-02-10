<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
	{
		echo('<meta http-equiv="Refresh" content="0;url=index.php"/>');
		exit('');
	}

require_once ("funciones/fxGeneral.php");
require_once ("funciones/fxUsuarios.php");
require_once ("funciones/fxTomosCertificacion.php");
require_once ("funciones/fxActaCertificacion.php");
require_once ("funciones/fxRegConsecutivos.php");
require_once ("tcpdf/tcpdf.php");

$m_cnx_MySQL = fxAbrirConexion();
$Registro = fxVerificaUsuario();
$Administrador = fxVerificaAdministrador();

function DevuelveFecha()
{
	$FechaDividida = explode("-", date("Y-m-d"));
	
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

$msCertificacion = $_POST["codigo"];
$msTomo = $_POST["tomo"];

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set default monospaced font
//$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

// set auto page breaks
//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM); LHVG20260130
$pdf->SetAutoPageBreak(FALSE, PDF_MARGIN_BOTTOM);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/spa.php')) {
	require_once(dirname(__FILE__).'/lang/spa.php');
	$pdf->setLanguageArray($l);
}

$pdf->AddPage();

$m_cnx_MySQL = fxAbrirConexion();

$msConsulta = "select KDSA030A.MATRICULA_REL, NOMBRES_010, APELLIDOS_010 ";
$msConsulta .= "from KDSA171A, KDSA030A, KDSA010A where ESTADO_171 = ? and CEDULA_171 = ? and ASISTENCIA_171 > ? and ARANCELCOMPLETO_171 = ? and ";
$msConsulta .= "CERTIFICACION_REL = ? and KDSA171A.MATRICULA_REL = KDSA030A.MATRICULA_REL and ";
$msConsulta .= "KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL";
$mDatos = $m_cnx_MySQL->prepare($msConsulta);
$mDatos->execute(['Activo', 1, 70, 1, $msCertificacion]);
$mnRegistros = $mDatos->rowCount();

if ($mnRegistros > 0) //Existen estudiantes para certificar
{
	$msConsulta = "select KDSA170A.CURSO_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020, FECHAINI_020, FECHAFIN_020, TIPO_020, CERTIFICAR_020 ";
	$msConsulta .= "from KDSA170A join KDSA020A on KDSA170A.CURSO_REL = KDSA020A.CURSO_REL where CERTIFICACION_REL = ?";
	$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
	$mAuxiliar->execute([$msCertificacion]);
	$mAuxFila = $mAuxiliar->fetch();
	$msCurso = $mAuxFila["CURSO_REL"];
	$msNombreCurso = $mAuxFila["NOMBRE_020"];
	$msConvocatoria = $mAuxFila["CONVOCATORIA_020"];
	$mnGrupo = $mAuxFila["GRUPO_020"];
	$msFechaIni = $mAuxFila["FECHAINI_020"];
	$msFechaFin = $mAuxFila["FECHAFIN_020"];
	$mnTipoEstudio = $mAuxFila["TIPO_020"];
	$mbCertificar = $mAuxFila["CERTIFICAR_020"];

	$mAuxiliar = fxDevuelveTomo(0, $msTomo);
	$mAuxFila = $mAuxiliar->fetch();
	$mnNumeroTomo = intval($mAuxFila["NUMERO_180"]);
	$mnUltimoFolio = intval($mAuxFila["ULTIMOFOLIO_180"]);
	$mnUltimaActa = intval($mAuxFila["ULTIMAACTA_180"]);
	$mnTipoTomo = intval($mAuxFila["TIPO_180"]);

	if ($mnTipoEstudio == $mnTipoTomo) //Verifica la concordancia entre el tipo de estudio y el tipo de tomo
	{
		$mnUltimoRegistro = fxObtenerRegistro();

		$msConsulta = 'SELECT fxUltimaLinea(?) as LINEAFIN_190';
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$msTomo]);
		$mAuxFila = $mAuxiliar->fetch();
		$mnUltimaLinea = intval($mAuxFila["LINEAFIN_190"]);

		//Cuando el tomo está recién abierto se inicia en el folio 2. El folio 1 es el Acta de apertura.
		if ($mnUltimoFolio < 2)
			$mnFolio = 2;
		else
			$mnFolio = $mnUltimoFolio;
		
		if ($mnUltimaLinea + 16 >= 240) //240 líneas máximo (3O líneas de texto con 8 de altura) --LHVG 20230915
		{
			$mnLinea = 8;
			$mnFolio ++;
		}
		else
		{
			if ($mnUltimaLinea != 8)
				$mnLinea = $mnUltimaLinea + 16; //Una linea vacía entre actas
			else
				$mnLinea = 8;
		}
		
		$mnActa = $mnUltimaActa + 1;
		$mnRegistro = $mnUltimoRegistro; //Se incrementa al guardar el detalle del acta

		if ($mbCertificar == 1)
			$mdFechaActa = $msFechaFin;
		else
			$mdFechaActa = date("Y-m-d");
		
		$msActa = fxGuardarActaCertificacion($msCurso, $msTomo, $mdFechaActa, $mnNumeroTomo, $mnActa, $mnFolio, $mnLinea);

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
		$msTexto = 'En la ciudad de Managua a los ' . DevuelveFecha() . ', se procede a registrar la certificación ';
		$msTexto .= 'otorgada a los egresados del curso ' . $msNombreCurso . ', convocatoria ' . $msConvocatoria;
		$msTexto .= ', grupo ' . $mnGrupo . '; impartido por INOFE en el período del ' . DevuelveFechaActa($msFechaIni);
		$msTexto .= ' al ' . DevuelveFechaActa($msFechaFin);

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

		while ($mFila = $mDatos->fetch())
		{
			$msMatricula = $mFila["MATRICULA_REL"];
			$msNombres = $mFila["NOMBRES_010"];
			$msApellidos = $mFila["APELLIDOS_010"];
			$msNombreCompleto = $msApellidos . ", " . $msNombres;
			$mnAnno = date('Y');
			$mnRegistro++;
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

			$msVerificacion = Verificacion($msMatricula, $mnRegistro, $msCurso);
			
			fxGuardarDetalleActa($msActa, $msMatricula, $mnFolio, $mnRegistro, $msVerificacion);

			//Cambia el estado en KDSA171A
			$msConsulta = "update KDSA171A set ESTADO_171 = 'Certificado', TOMO_KDSA_171 = ?, FOLIO_KDSA_171 = ?, ACTA_KDSA_171 = ? where MATRICULA_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$mnNumeroTomo, $mnFolio, $mnActa, $msMatricula]);

			//Cambia el estado en KDSA030A
			$msConsulta = "update KDSA030A set ESTADO_030 = 3 where MATRICULA_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msMatricula]);
		}

		//Actualiza el número del registro en KDSA181A
		fxModificarRegistro($mnRegistro);

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
		fxActualizarLineaFinal($msActa, $mnLinea);
		fxModificarUltimos($msTomo, $mnFolio, $mnActa);

		fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA190A", $msActa, "", "Agregar");

		$pdf->Output();
	}
	else
	{
		include ("MasterWeb.php");
		echo ('<div class="container text-center">');
		echo ('<div id="DivContenido">');
		echo ('<div class = "row">');
		echo ('<div class="col-xs-12 col-md-12">');
		echo ('<h2>El tipo de Tomo y el tipo de Estudio no coinciden.</h2>');
		echo ('<p>' . $msNombreCurso . ' (' . $msConvocatoria . '/G' . $mnGrupo . ')</p>');
		echo ('<p>Tipo de Estudio : ' . Tipo($mnTipoEstudio) . '</p>');
		echo ('<p>Tipo de Tomo : ' . Tipo($mnTipoTomo) . '</p>');
		echo ('</div></div></div></div></body></html>');
	}
}
else
{
	include ("MasterWeb.php");
	echo ('<div class="container text-center">');
	echo ('<div id="DivContenido">');
	echo ('<div class = "row">');
	echo ('<div class="col-xs-12 col-md-12">');
	echo ('<h1>No existen estudiantes que puedan ser certificados.</h1>');
	echo ('</div></div></div></div></body></html>');
}


function Verificacion($msMatricula, $mnRegistro, $msCurso)
{
	$mnMatricula = substr($msMatricula, -8);
	$mnCurso = substr($msCurso, -7);

	$msCadena = $mnMatricula . date("YmdHis") . $mnRegistro . $mnCurso . rand(1,99999);
	return $msCadena;
}

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
	}

	return $msResultado;
}
?>