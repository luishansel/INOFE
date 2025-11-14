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
		function Header()
		{
			// get the current page break margin
			$bMargin = $this->getBreakMargin();
			// get current auto-page-break mode
			$auto_page_break = $this->AutoPageBreak;
			// disable auto-page-break
			$this->SetAutoPageBreak(false, 0);

			// set image scale factor
			$this->setImageScale(PDF_IMAGE_SCALE_RATIO);

			// set background image
			$img_file = 'imagenes/diplomainofe.jpg';
			$this->setJPEGQuality(100);
			$this->Image($img_file, 0, 0, 280, 0, 'jpg', '', '', false, 300, '', false, false, 0);
			
			$this->SetFont('times', '', 25);
			$msRotulo = '<b>Instituto de Oficios y Formación para el Empleo</b>';
			$this->writeHTMLCell(200,10,54,25,$msRotulo,0,0,false,true,'C');

			$msRotulo = '<b>INOFE</b>';
			$this->writeHTMLCell(200,10,54,35,$msRotulo,0,0,false,true,'C');

			$this->SetFont('times', '', 22);
			$msRotulo = '<b>Otorga el presente</b>';
			$this->writeHTMLCell(200,10,42,54,$msRotulo,0,0,false,true,'C');

			$this->SetFont('times', 'B', 38);
			$this->SetTextColor(187, 112, 34);
			$msRotulo = '<b>CERTIFICADO</b>';
			$this->writeHTMLCell(200,20,42,64,$msRotulo,0,0,false,true,'C');

			$this->SetTextColor(0, 0, 0);
			$this->SetFont('times', '', 22);
			$this->writeHTMLCell(200,20,42,78,'a',0,0,false,true,'C');
			
			// set the starting point for the page content
			$this->setPageMark();
		}
		function Footer(){}
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

	$msCurso = $_POST["msCurso"];

	$pdf = new PDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(0);
	$pdf->SetFooterMargin(0);

	// remove default footer
	$pdf->setPrintFooter(false);

	// set auto page breaks
	//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	// set some language-dependent strings (optional)
	if (@file_exists(dirname(__FILE__).'/lang/spa.php')) {
		require_once(dirname(__FILE__).'/lang/spa.php');
		$pdf->setLanguageArray($l);
	}

	$msConsulta = "select KDSA190A.ACTA_REL, TOMO_REL, FECHA_190, TOMO_190, ACTA_190, FOLIO_191, REGISTRO_191, VERIFICACION_191, NOMBRES_010, APELLIDOS_010, NOMBRE_020, FECHAINI_020, FECHAFIN_020, TIPO_020, CERTDIGITAL_030, CERTDIGITAL_020 ";
	$msConsulta .= "from KDSA190A, KDSA191A, KDSA030A, KDSA010A, KDSA020A ";
	$msConsulta .= "where KDSA190A.CURSO_REL = ? and KDSA190A.ACTA_REL = KDSA191A.ACTA_REL and ";
	$msConsulta .= "KDSA191A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and ";
	$msConsulta .= "KDSA030A.CURSO_REL = KDSA020A.CURSO_REL";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msCurso]);

	while ($fila = $mDatos->fetch())
	{
		$pdf->AddPage();

		//Nombre del estudiante
		$pdf->SetFont('times','B',26);
		$msNombre = html_entity_decode($fila["NOMBRES_010"]) . ' ' . html_entity_decode($fila["APELLIDOS_010"]);
		$pdf->writeHTMLCell(200,10,40,88,$msNombre,0,0,false,true,'C');

		$pdf->SetFont('times','B',13);
		$pdf->MultiCell(200,10,'Por haber culminado con éxito las hora académicas correspondientes',0,'C',false,0,40,101);

		//Periodo de estudio
		$mdFechaIni = $fila["FECHAINI_020"];
		$mdFechaFin = $fila["FECHAFIN_020"];
		if ($mdFechaIni == $mdFechaFin)
			$msFechaEstudio = "al día " . DevuelveFecha($mdFechaIni);
		else
			$msFechaEstudio = "al período de " . DevuelveFecha($mdFechaIni) . " al " . DevuelveFecha($mdFechaFin);
		$pdf->setXY(140,180);
		$pdf->MultiCell(200,10,$msFechaEstudio,0,'C',false,0,40,106);

		//Tipo de estudio
		$mnTipo = intval($fila["TIPO_020"]);
		switch($mnTipo)
		{
			case 0:
				$msTipo = '<span>SEMINARIO</span>';
				break;
			case 1:
				$msTipo = '<span>CURSO</span>';
				break;
			case 2:
				$msTipo = '<span>CARRERA</span>';
				break;
			case 3:
				$msTipo = '<span>TALLER</span>';
				break;
			case 4:
				$msTipo = '<span>DIPLOMADO</span>';
				break;
			case 5:
				$msTipo = '<span>WEBINAR</span>';
				break;
			case 6:
				$msTipo = '<span>WORKSHOP</span>';
				break;
			case 7:
				$msTipo = '<span>TEAMBUILDING</span>';
				break;
		}
		$pdf->SetFont('times','B',31);
		$pdf->SetTextColor(187, 112, 34);
		$pdf->writeHTMLCell(200,10,40,116,$msTipo,0,0,false,true,'C');

		//Nombre del estudio
		$pdf->SetFont('times','B',22);
		$pdf->SetTextColor(0, 0, 0);
		$msEstudio = html_entity_decode($fila["NOMBRE_020"]);
		$pdf->MultiCell(250,10,$msEstudio,0,'C',false,0,15,129);

		//Fecha acta
		$pdf->SetFont('times','B',14);
		$mdFecha = $fila["FECHA_190"];
		$msTexto = "Dado en la ciudad de Managua el " . DevuelveFecha($mdFecha);
		$pdf->setXY(75,140);
		$pdf->Cell(130,10,$msTexto,0,0,'L');

		//Registro
		$msTomo = $fila["TOMO_REL"];
		$mnTomo = $fila["TOMO_190"];
		$msFolio = $fila["FOLIO_191"];
		$msRegistro = $fila["REGISTRO_191"];
		$msTexto = "Registrado con el No. " . $msRegistro . ", en el folio " . $msFolio . ", tomo " . $mnTomo . "-" . $msTomo;
		$pdf->setXY(75,147);
		$pdf->Cell(130,10,$msTexto,0,0,'L');

		$img_file = 'imagenes/firmalesvin.jpg';
		$pdf->Image($img_file, 70, 165, 40, 0, '', '', '', false, 300, '', false, false, 0);

		$img_file = 'imagenes/firmajovania.jpg';
		$pdf->StartTransform();
		$pdf->Rotate(-18, 158, 160);
		$pdf->Image($img_file, 158, 160, 35, 0, '', '', '', false, 300, '', false, false, 0);
		$pdf->StopTransform();

		if ($fila["CERTDIGITAL_030"] == 1 or $fila["CERTDIGITAL_020"] == 1)
		{
			$img_file = 'imagenes/selloDireccion.jpg';
			$pdf->Image($img_file, 110, 158, 34, 0, '', '', '', false, 300, '', false, false, 0);

			$img_file = 'imagenes/selloRegistro.jpg';
			$pdf->Image($img_file, 189, 158, 34, 0, '', '', '', false, 300, '', false, false, 0);
		}

		$pdf->SetFont('times', 'B', 12);
		$msRotulo = 'Lic. Lesvin Castillo H.';
		$pdf->setXY(63,186);
		$pdf->Cell(50,5,$msRotulo,0,0,'C');

		$msRotulo = 'Lic. Jovania Cárdenas B.';
		$pdf->setXY(145,186);
		$pdf->Cell(50,5,$msRotulo,0,0,'C');

		$msRotulo = 'Dirección Académica';
		$pdf->setXY(63,192);
		$pdf->Cell(50,5,$msRotulo,0,0,'C');

		$msRotulo = 'Registro Académico';
		$pdf->setXY(145,192);
		$pdf->Cell(50,5,$msRotulo,0,0,'C');

		//Código QR
		$style = array(
			'border' => false,
			'vpadding' => 0,
			'hpadding' => 0,
			'fgcolor' => array(0,0,0),
			'bgcolor' => false, //array(255,255,255)
		);
		//$msCodigoQR = "https://demoAdmin.institutoinofe.com/verificacion.php?INOFE=" . $fila["VERIFICACION_191"];
		$msCodigoQR = "https://certificacion.institutoinofe.com/verificacion.php?INOFE=" . $fila["VERIFICACION_191"];
		$pdf->write2DBarcode($msCodigoQR, 'QRCODE,H', 26, 172, 30, 30, $style, 'N');
	}
	
	$pdf->Output();
}
?>