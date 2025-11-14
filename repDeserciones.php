<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
{
	echo('<meta http-equiv="Refresh" content="0;url=index.php"/>');
	exit('');
}
set_time_limit(600); //600 segundos

require_once ("funciones/fxGeneral.php");
require_once ("funciones/fxUsuarios.php");
require_once ("tcpdf/tcpdf.php");

$Registro = fxVerificaUsuario();

if ($Registro == 0)
{
?>
	<div class="container text-center">
    	<div id="DivContenido">
        	<img src="imagenes/errordeacceso.png"/>
        </div>
    </div>
<?php
}
else
{
	class PDF extends TCPDF
	{
		public $msPeriodo;

		// Page header
		function Header()
		{
			// Logos
			$this->Image('imagenes/headerLogin.jpg',14,6,0,15);
			// Title
			$mid_x = 210; // width of the "PDF screen", fixed by now.
			// helvetica bold 18
			$this->SetFont('helvetica','B',10);
			$Titulo = 'DESERCIONES';
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 10, $Titulo);
			// helvetica normal 18
			$this->SetFont('helvetica','',8);
			$this->Text(($mid_x - $this->GetStringWidth($this->msPeriodo)) / 2, 15, $this->msPeriodo);
			// Line break
			$this->Ln(20);
		}
		// Page footer
		function Footer()
		{
			// Position at 1.5 cm from bottom
			$this->SetY(-15);
			// helvetica italic 8
			$this->SetFont('helvetica','I',8);
			//$this->SetFillColor(229,50,45);
			$this->SetTextColor(0,0,0);
			// Page number
			$this->Cell(0,10,mb_convert_encoding(html_entity_decode('Página '),"UTF-8").$this->PageNo().'/'.$this->getAliasNbPages(),0,0,'L');
			$this->Cell(0,10,'Emitido: ' . date("d/m/Y h:i:s a") . '',0,0,'R');
		}
	}
}

function DevuelveFecha($Fecha)
{
	$FechaDividida = explode("-", $Fecha);
	
	$Anno = $FechaDividida[0];
	$Mes = $FechaDividida[1];
	$Dia = $FechaDividida[2];
	
	switch ($Mes)
	{
		case "01":
			$NombreMes = "Ene";
			break;
		case "02":
			$NombreMes = "Feb";
			break;
		case "03":
			$NombreMes = "Mar";
			break;
		case "04":
			$NombreMes = "Abr";
			break;
		case "05":
			$NombreMes = "May";
			break;
		case "06":
			$NombreMes = "Jun";
			break;
		case "07":
			$NombreMes = "Jul";
			break;
		case "08":
			$NombreMes = "Ago";
			break;
		case "09":
			$NombreMes = "Sep";
			break;
		case "10":
			$NombreMes = "Oct";
			break;
		case "11":
			$NombreMes = "Nov";
			break;
		case "12":
			$NombreMes = "Dic";
			break;
	}
	return ($Dia . "-" . $NombreMes . "-" . $Anno);
}

function DevuelveFechaLarga($Fecha)
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

$pdf = new PDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/spa.php')) {
	require_once(dirname(__FILE__).'/lang/spa.php');
	$pdf->setLanguageArray($l);
}

$mbActivos = $_POST["activos"];
$mdFechaIni = $_POST["fechaIni"];
$mdFechaFin = $_POST["fechaFin"];
$msFechaIni = DevuelveFechaLarga($mdFechaIni);
$msFechaFin = DevuelveFechaLarga($mdFechaFin);

if ($mbActivos == 1)
	$msPeriodo = "Cursos activos";
else
{
	if ($mdFechaIni == $mdFechaFin)
		$msPeriodo = "Cursos que iniciaron el " . $msFechaIni;
	else
		$msPeriodo = "Cursos que iniciaron entre " . $msFechaIni . " y " . $msFechaFin;
}

$pdf->msPeriodo = $msPeriodo;
$pdf->AddPage();
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('helvetica','',9);

$m_cnx_MySQL = fxAbrirConexion();
//Obtención de datos de los cursos
if ($mbActivos == 1)
{
	$msConsulta = "select CURSO_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020 from KDSA020A where ACTIVO_020 = 1";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute();
}
else
{
	$msConsulta = "select CURSO_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020 from KDSA020A where FECHAINI_020 between ? and ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$mdFechaIni, $mdFechaFin]);
}

$msHTML1 = fxEncabezado();
$mbFondo1 = false;

$msHTML = "<style>";
$msHTML .= "th{";
$msHTML .= "border: 1px solid rgb(229,50,45); background-color: rgb(229,50,45); color: rgb(255,255,255); font-size: small;";
$msHTML .= "}";
$msHTML .= ".fondoGris{";
$msHTML .= "background-color: rgb(240,240,240); color: rgb(0,0,0); font-size: small;";
$msHTML .= "}";
$msHTML .= ".fondoBlanco{";
$msHTML .= "background-color: rgb(255,255,255); color: rgb(0,0,0); font-size: small;";
$msHTML .= "}";
$msHTML .= "</style>";
$msHTML .= "<table>";

while ($mFila = $mDatos->fetch())
{
	$msCodCurso = $mFila["CURSO_REL"];
	$msCurso = $mFila["NOMBRE_020"] . " (" . $mFila["CONVOCATORIA_020"] . " / G" . $mFila["GRUPO_020"] . ")";

	$msConsulta = "select count(MATRICULA_REL) as CONTEO from KDSA030A where ESTADO_030 <> 4 and CURSO_REL = ?";
	$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
	$mAuxiliar->execute([$msCodCurso]);
	$mrAuxiliar = $mAuxiliar->fetch();
	$mnMatriculaInicial = $mrAuxiliar["CONTEO"];

	$arrDocentes = [];

	$msConsulta = "select distinct DOCENTE_REL from KDSA021A where CURSO_REL = ?";
	$mDocentes = $m_cnx_MySQL->prepare($msConsulta);
	$mDocentes->execute([$msCodCurso]);
	while ($mrDocente = $mDocentes->fetch())
	{
		$msDocente = $mrDocente["DOCENTE_REL"];
		$arrDocentes[$msDocente] = 0;
	}

	if (isset($arrDocentes))
	{
		$msConsulta = "select MATRICULA_REL, FECHA_000 from KDSA030A join KDSA000A on MATRICULA_REL = LLAVE1_000 where ";
		$msConsulta .= "ESTADO_030 = 2 and CURSO_REL = ? and OPERACION_000 = 'Estado Deserción'";
		$mEstudiantes = $m_cnx_MySQL->prepare($msConsulta);
		$mEstudiantes->execute([$msCodCurso]);
		$mnRegistros = $mEstudiantes->rowCount();

		if ($mnRegistros>0)
		{
			while ($mrEstudiante = $mEstudiantes->fetch())
			{
				$mdFecha = $mrEstudiante["FECHA_000"];
				$msConsulta = "select DOCENTE_REL from KDSA021A where FECHAINI_021 <= ? AND CURSO_REL = ? order by FECHAFIN_021 desc limit 1";
				$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
				$mAuxiliar->execute([$mdFecha, $msCodCurso]);
				$mrAuxiliar = $mAuxiliar->fetch();
				$msDocente = $mrAuxiliar["DOCENTE_REL"];
				$mnConteo = $arrDocentes[$msDocente];
				$mnConteo = intval($mnConteo) + 1;
				$arrDocentes[$msDocente] = $mnConteo;
			}
		}

		foreach ($arrDocentes as $docente => $deserciones) {
			$msConsulta = "select NOMBRE_100 from KDSA100A where DOCENTE_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$docente]);
			$mrAuxiliar = $mAuxiliar->fetch();
			$msNombre = $mrAuxiliar["NOMBRE_100"];
			$msHTML1 .= fxDetalle($msCurso, $msNombre, $mnMatriculaInicial, $deserciones, $mbFondo1);
			$mbFondo1 = !$mbFondo1;
		}
	}
}

$msHTML .= $msHTML1;
$msHTML .= "</table>";
$pdf->SetY(25);
$pdf->writeHTML($msHTML);
$pdf->Output();

function fxEncabezado()
{
	$mHTML = '<thead>';
	$mHTML .= '<tr>';
	$mHTML .= '<th width="40%" style="text-align: left">Curso</th>';
	$mHTML .= '<th width="30%" style="text-align: left">Docente</th>';
	$mHTML .= '<th width="10%" style="text-align: right">Matrícula inicial</th>';
	$mHTML .= '<th width="10%" style="text-align: right">Deserciones</th>';
	$mHTML .= '<th width="10%" style="text-align: right">Porcentaje de deserción</th>';
	$mHTML .= '</tr>';
	$mHTML .= '</thead>';

	return $mHTML;
}

function fxDetalle($msCurso, $msDocente, $mnMatriculaInicial, $mnDeserciones, $mbFondo)
{
	if ($mbFondo == false)
		$msClase = "fondoBlanco";
	else
		$msClase = "fondoGris";

	$mnPorcentaje = ($mnDeserciones / $mnMatriculaInicial) * 100;

	$mHTML = '<tr>';
	$mHTML .= '<td class="' . $msClase . '" width="40%" style="text-align: left">' . mb_convert_encoding($msCurso, "UTF-8") . '</td>';
	$mHTML .= '<td class="' . $msClase . '" width="30%" style="text-align: left">' . mb_convert_encoding($msDocente, "UTF-8") . '</td>';
	$mHTML .= '<td class="' . $msClase . '" width="10%" style="text-align: right">' . $mnMatriculaInicial . '</td>';
	$mHTML .= '<td class="' . $msClase . '" width="10%" style="text-align: right">' . $mnDeserciones . '</td>';
	$mHTML .= '<td class="' . $msClase . '" width="10%" style="text-align: right">' . number_format($mnPorcentaje,2,'.',',') . '</td>';
	$mHTML .= '</tr>';

	return $mHTML;
}
?>