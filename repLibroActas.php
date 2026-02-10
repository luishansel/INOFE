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
require_once ("tcpdf/tcpdf.php");

$m_cnx_MySQL = fxAbrirConexion();
$Registro = fxVerificaUsuario();
$Administrador = fxVerificaAdministrador();

class PDF extends TCPDF
{
	public $Periodo;
	
	// Page header
	function Header()
	{
		// dejavusansmono bold 10
		$this->SetFont('dejavusansmono','B',12);

		// Page number
		if ($this->PageNo() % 2 == 1)
			$this->SetX(190);
		else
			$this->SetX(15);

		if ($this->PageNo() < 10)
		{
			$this->Cell(0,10,'000' . $this->PageNo(),0,0,'');
		}
		else
		{
			if ($this->PageNo() < 100)
				$this->Cell(0,10,'00' . $this->PageNo(),0,0,'R');
			else
				$this->Cell(0,10,'0' . $this->PageNo(),0,0,'R');
		}
	}
}

function DevuelveFecha($msFecha)
{
	$FechaDividida = explode("-", $msFecha);
	
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

function DevuelveFechaMes($msFecha)
{
	$FechaDividida = explode("-", $msFecha);
	
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

function DevuelveFechaActa($Fecha)
{
	$FechaDividida = explode("-", $Fecha);
	
	$Anno = $FechaDividida[0];
	$Mes = $FechaDividida[1];
	$Dia = $FechaDividida[2];

	return ($Dia . "/" . $Mes . "/" . $Anno);
}

$msTomo = $_POST["msTomo"];

$pdf = new PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set default monospaced font
//$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(PDF_MARGIN_LEFT, 16, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);

// set auto page breaks
//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetAutoPageBreak(FALSE, PDF_MARGIN_BOTTOM);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/spa.php')) {
	require_once(dirname(__FILE__).'/lang/spa.php');
	$pdf->setLanguageArray($l);
}

$pdf->AddPage();

$m_cnx_MySQL = fxAbrirConexion();

//Acta de apertura del libro
$msConsulta = "select FECHA_190, TOMO_190 from KDSA190A where TOMO_REL = ?";
$mDatos = $m_cnx_MySQL->prepare($msConsulta);
$mDatos->execute([$msTomo]);
$mFila = $mDatos->fetch();
$mnTomo = $mFila["TOMO_190"];
$mdApertura = $mFila["FECHA_190"];

$mnLinea = 8;
$pdf->SetY($mnLinea);
$pdf->SetFont('dejavusansmono', 'B', 15);
$pdf->Cell(180, 20, 'ACTA DE APERTURA', 0, 0, 'C', 0, '');
$mnLinea += 8;

$pdf->SetFont('dejavusansmono', '', 15);
$msTexto = 'El suscrito Director Académico del Centro de Capacitación KDSA, en el marco del cumplimiento de las funciones de mi cargo, procedo a abrir el Tomo ';
$msTexto .= $mnTomo . ' del libro de Registro de Certificaciones';

$mnLinea += 8;
$pdf->SetY($mnLinea);
$pdf->SetFont('dejavusansmono', '', 15);
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
	}

	if ($index == count($mArregloTexto) - 1)
	{
		$pdf->SetY($mnLinea);
		$pdf->Cell(180, 20, $msTextoCelda . '.', 0, 0, 'J', 0, '');
	}	
}

$msConsulta = "select count(ACTA_REL) as ACTAS, min(ACTA_190) as PRIMERA, max(ACTA_190) as ULTIMA from KDSA190A where TOMO_REL = ?";
$mDatos = $m_cnx_MySQL->prepare($msConsulta);
$mDatos->execute([$msTomo]);
$mFila = $mDatos->fetch();
$mnActas = $mFila["ACTAS"];
$mnPrimera = $mFila["PRIMERA"];
$mnUltima = $mFila["ULTIMA"];

$msConsulta = "select APERTURA_180, ULTIMOFOLIO_180, CERRADO_180 from KDSA180A where TOMO_REL = ?";
$mDatos = $m_cnx_MySQL->prepare($msConsulta);
$mDatos->execute([$msTomo]);
$mFila = $mDatos->fetch();
$FechaApertura = $mFila["APERTURA_180"];
$TomoCerrado = $mFila["CERRADO_180"];
$mnUltimoFolio = intval($mFila["ULTIMOFOLIO_180"]) + 1; //Se agrega un folio debido al acta de cierre

$msTexto = 'El presente libro consta de ' . $mnUltimoFolio . ' folios, iniciando con el acta ' . $mnPrimera;
$mnLinea += 16;
$pdf->SetY($mnLinea);
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
	}

	if ($index == count($mArregloTexto) - 1)
	{
		$pdf->SetY($mnLinea);
		$pdf->Cell(180, 20, $msTextoCelda . '.', 0, 0, 'J', 0, '');
	}	
}

$mnLinea += 16;
$msTextoCelda = "Managua, " . DevuelveFechaMes($FechaApertura) . ".";
$pdf->SetY($mnLinea);
$pdf->Cell(180, 20, $msTextoCelda, 0, 0, 'L', 0, '');

$mnLinea += 40;
$msTextoCelda = "Msc. Humberto Cárdenas Bermúdez.";
$pdf->SetY($mnLinea);
$pdf->Cell(180, 20, $msTextoCelda, 0, 0, 'C', 0, '');
$mnLinea += 8;
$msTextoCelda = "Director académico.";
$pdf->SetY($mnLinea);
$pdf->Cell(180, 20, $msTextoCelda, 0, 0, 'C', 0, '');

$pdf->AddPage();

$msConsulta = "select ACTA_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020, FECHAINI_020, FECHAFIN_020, FECHA_190, TOMO_190, ACTA_190, FOLIOINI_190, LINEAINI_190, LINEAFIN_190 ";
$msConsulta .= "from KDSA190A, KDSA020A where KDSA190A.CURSO_REL = KDSA020A.CURSO_REL and TOMO_REL = ? order by ACTA_190";
$mDatos = $m_cnx_MySQL->prepare($msConsulta);
$mDatos->execute([$msTomo]);

while($mFila = $mDatos->fetch())
{
	$msActa = $mFila["ACTA_REL"];
	$msNombreCurso = $mFila["NOMBRE_020"];
	$msConvocatoria = $mFila["CONVOCATORIA_020"];
	$mnGrupo = $mFila["GRUPO_020"];
	$msFechaIni = $mFila["FECHAINI_020"];
	$msFechaFin = $mFila["FECHAFIN_020"];
	$msFechaActa = $mFila["FECHA_190"];
	$mnActa = $mFila["ACTA_190"];
	$mnFolioIni = $mFila["FOLIOINI_190"];
	$mnLineaIni = $mFila["LINEAINI_190"];
	$mnLineaFin = $mFila["LINEAFIN_190"];

	$mnLinea = $mnLineaIni;

	if ($mnFolioIni > $pdf->PageNo())
		$pdf->AddPage();

	$pdf->SetY($mnLinea);
	$pdf->SetFont('dejavusansmono', 'B', 15);
	$pdf->Cell(180, 20, 'ACTA '. $mnActa, 0, 0, 'C', 0, '');
	$mnLinea += 8;

	if ($mnLinea >= 240) //240 líneas máximo (3O líneas de texto con 8 de altura) --LHVG 20230915
	{
		$mnLinea = 8;
		$pdf->AddPage();
	}

	$pdf->SetFont('dejavusansmono', '', 15);
	$msTexto = 'En la ciudad de Managua a los ' . DevuelveFecha($msFechaActa) . ', se procede a registrar la certificación ';
	$msTexto .= 'otorgada a los egresados del curso ' . $msNombreCurso . ', convocatoria ' . $msConvocatoria;
	$msTexto .= ', grupo ' . $mnGrupo . '; impartido por KDSA en el período del ' . DevuelveFechaActa($msFechaIni);
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
				$pdf->AddPage();
			}
		}

		if ($index == count($mArregloTexto) - 1)
		{
			$pdf->SetY($mnLinea);
			$pdf->Cell(180, 20, $msTextoCelda . '.', 0, 0, 'J', 0, '');
		}
	}

	$msConsulta = "select ACTA_REL, KDSA191A.MATRICULA_REL, NOMBRES_010, APELLIDOS_010, FOLIO_191, REGISTRO_191 ";
	$msConsulta .= "from KDSA191A, KDSA030A, KDSA010A where KDSA191A.MATRICULA_REL = KDSA030A.MATRICULA_REL and ";
	$msConsulta .= "KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and ACTA_REL = ? ";
	$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
	$mAuxiliar->execute([$msActa]);

	while ($mAuxFila = $mAuxiliar->fetch())
	{
		$msMatricula = $mAuxFila["MATRICULA_REL"];
		$msNombres = $mAuxFila["NOMBRES_010"];
		$msApellidos = $mAuxFila["APELLIDOS_010"];
		$mnRegistro = $mAuxFila["REGISTRO_191"];
		$msNombreCompleto = $msApellidos . ", " . $msNombres;
		$mnAnno = substr($msFechaActa, 0, 4);
		$msTextoCelda = $mnRegistro . '-' . $mnAnno . ' ' . html_entity_decode($msNombreCompleto);
		
		$mnLinea += 8;
		if ($mnLinea >= 240) //240 líneas máximo (3O líneas de texto con 8 de altura) --LHVG 20230915
		{
			$mnLinea = 8;
			$pdf->AddPage();
		}

		$pdf->SetY($mnLinea);
		$pdf->Cell(180, 20, $msTextoCelda, 0, 0, 'L', 0, '');
	}

	$mnLinea += 8;
	if ($mnLinea >= 240) //240 líneas máximo (3O líneas de texto con 8 de altura) --LHVG 20231227
	{
		$mnLinea = 8;
		$pdf->AddPage();
	}
	$pdf->SetY($mnLinea);
	$msTextoCelda = '****** FINAL DEL ACTA ' . $mnActa . ' ******';
	$pdf->Cell(180, 20, $msTextoCelda, 0, 0, 'C', 0, '');
}

$pdf->AddPage();

if ($TomoCerrado == 1)
{
	$mnLinea = 8;
	$pdf->SetY($mnLinea);
	$pdf->SetFont('dejavusansmono', 'B', 15);
	$pdf->Cell(180, 20, 'ACTA DE CIERRE', 0, 0, 'C', 0, '');

	$mnLinea += 16;
	$pdf->SetFont('dejavusansmono', '', 15);

	$msTexto = "En la ciudad de Managua a los " . DevuelveFecha(date("Y-m-d")) . ", en caracter de Director Académico, procedo a realizar el cierre del Tomo ";
	$msTexto .= $mnTomo . " de Certificaciones de KDSA con razón de apertura " . DevuelveFechaMes($FechaApertura) . ", que consta de " . $mnUltima;
	$msTexto .= " actas";

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
		}

		if ($index == count($mArregloTexto) - 1)
		{
			$pdf->SetY($mnLinea);
			$pdf->Cell(180, 20, $msTextoCelda . '.', 0, 0, 'J', 0, '');
		}	
	}

	$mnLinea += 40;
	$msTextoCelda = "Msc. Humberto Cárdenas Bermúdez.";
	$pdf->SetY($mnLinea);
	$pdf->Cell(180, 20, $msTextoCelda, 0, 0, 'C', 0, '');
	$mnLinea += 8;
	$msTextoCelda = "Director académico.";
	$pdf->SetY($mnLinea);
	$pdf->Cell(180, 20, $msTextoCelda, 0, 0, 'C', 0, '');
}

$pdf->Output();
?>