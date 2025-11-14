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
require_once ("funciones/fxNumerosLetras.php");
require_once ("tcpdf/tcpdf.php");

$m_cnx_MySQL = fxAbrirConexion();
$Registro = fxVerificaUsuario();
$Administrador = fxVerificaAdministrador();

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
	class PDF extends TCPDF
	{
		// Page header
		function Header()
		{
			// Logos
			$this->Image('imagenes/inofeLogo.jpg',15,8,0,25);
			// set background image
			$this->Image('imagenes/inofeLogoGris.jpg', 60, 80, 0, 90, '', '', '', false, 300, '', false, false, 0);
		}

		// Page footer
		function Footer()
		{
			// Position at 1.5 cm from bottom
			$this->SetY(-15);
			$this->SetFont('helvetica','I',8);
			$msHTML = '<table style="background-color: rgb(207, 85, 22); color: rgb(255,255,255); text-align:center">';
			$msHTML .= '<tr><td>info@institutoinofe.com</td></tr>';
			$msHTML .= '<tr><td>Altamira de este. Semáforos de Juan Bansbach 1/2 cuadra al oeste. Casa 495.</td></tr>';
			$msHTML .= '</table>';
			$this->writeHTML($msHTML);
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

	function DevuelveFechaParrafo($Fecha)
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
		return (fxNumerosLetras($Dia) . " días del mes de " . $NombreMes . " de " . fxNumerosLetras($Anno));
	}

	$msMatricula = $_POST["msCodigo"];
	$mnIdentificacion = $_POST["mnIdentificacion"];
	$msFirmaRegistro = $_POST["msFirmaRegistro"];

	$msConsulta = "select CONCAT_WS(' ',NOMBRES_010,APELLIDOS_010) as ESTUDIANTE, SEXO_010, CEDULA_010, NOMBRE_020, HORAINI_020, ";
	$msConsulta .= "HORAFIN_020, FECHAINI_020, FECHAFIN_020, fxDevuelveDias(KDSA030A.CURSO_REL) as DIASCURSO ";
	$msConsulta .= "from KDSA030A, KDSA010A, KDSA020A ";
	$msConsulta .= "where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and ";
	$msConsulta .= "KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and MATRICULA_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msMatricula]);
	$Fila = $mDatos->fetch();
	$msNombre = html_entity_decode($Fila["ESTUDIANTE"]);
	$msSexo = $Fila["SEXO_010"];
	$msCedula = $Fila["CEDULA_010"];
	$msCurso = html_entity_decode($Fila["NOMBRE_020"]);
	$mdHoraIni = date_create_from_format('H:i:s', $Fila["HORAINI_020"]);
	$mdHoraFin = date_create_from_format('H:i:s', $Fila["HORAFIN_020"]);
	$mdFechaIni = $Fila["FECHAINI_020"];
	$mdFechaFin = $Fila["FECHAFIN_020"];
	$msDiasCurso = $Fila["DIASCURSO"];

	$msConsulta = "select NOMBRE_008, CARGO_008, SEXO_008 from KDSA008A where FIRMA_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msFirmaRegistro]);
	$Fila = $mDatos->fetch();
	$msNombreFirma = $Fila["NOMBRE_008"];
	$msCargoFirma = $Fila["CARGO_008"];
	$msSexoFirma = $Fila["SEXO_008"];

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

	$pdf->AddPage();

	// Title
	$mid_x = 210; // width of the "PDF screen", fixed by now.
	$pdf->SetFont('helvetica','UB', 24);
	$Titulo = 'CONSTANCIA';
	$pdf->Text(($mid_x - $pdf->GetStringWidth($Titulo)) / 2, 50, $Titulo);

	$pdf->SetFont('helvetica','', 14);

	$mdFechaHoy = date('Y-m-d');

	$msHTML = '<div style="line-height: 20px; text-align: justify;">';

	if ($msSexoFirma == "M")
		$msHTML .= 'El suscrito ';
	else
		$msHTML .= 'La suscrita ';

  $msHTML .= $msCargoFirma;
	$msHTML .= ' del <b>Instituto de Oficios y Formación para el Empleo (INOFE)</b>';
	$msHTML .= ', hace constar que <b>' . $msNombre . '</b>';

	if ($msSexo == 'M')
		$msHTML .= ' identificado ';
	else
		$msHTML .= ' identificada ';

	if ($mnIdentificacion == 0)
		$msHTML .= 'con cédula de identidad nicaragüense número ';
	else
		$msHTML .= 'con cédula de residencia número ';

	$msHTML .= '<b>' . $msCedula . '</b>';
	$msHTML .= ', es estudiante activo de ';
	$msHTML .= '<b>' . $msCurso . '</b>';

	$msHTML .= ', que se desarrolla los días ' . $msDiasCurso;
	$msHTML .= ' de ' . date_format($mdHoraIni, 'h:i a') . ' a ' . date_format($mdHoraFin, 'h:i a');
	$msHTML .= ' con fecha de inicio ' . DevuelveFecha($mdFechaIni) . ' y tentativa finalización el ' . DevuelveFecha($mdFechaFin) . '.';
	$msHTML .= '</div>';

	$msHTML1 = '<div style="line-height: 20px; text-align: justify;">';
	$msHTML1 .= 'Se extiende la presente constancia en la ciudad de Managua a los ' . DevuelveFechaParrafo($mdFechaHoy) . '.';
	$msHTML1 .= '</div>';

	$msHTML2 = '<table width="100%" style="text-align:center">';
	$msHTML2 .= '<tr><td>' . $msNombreFirma . '</td></tr>';
	$msHTML2 .= '<tr><td>' . $msCargoFirma . '</td></tr>';
	$msHTML2 .= '</table>';

	$pdf->SetY(80);
	$pdf->writeHTML($msHTML);

	$pdf->SetY(130);
	$pdf->writeHTML($msHTML1);

	$pdf->SetY(170);
	$pdf->writeHTML($msHTML2);

	$pdf->Output();
}
?>