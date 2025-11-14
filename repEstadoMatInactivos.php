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
		var $top_margin = 35;
		public $periodo;

		// Page header
		function Header()
		{
			// Logos
			$this->Image('imagenes/headerLogin.jpg',12,10,0,18);
			// Title
			$mid_x = 278; // width of the "PDF screen", fixed by now.

			$this->SetFont('helvetica','B',14);
			$Titulo = 'ESTADO DE LAS MATRICULAS';
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 10, $Titulo);
			$this->SetFont('helvetica','',11);
			$Titulo = $this->periodo;
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 17, $Titulo);

			$msHTML = '<table>';
			$msHTML .= '<tr>';
			$msHTML .= '<th style="text-align: left;background-color:rgb(0,0,255);color: white; width: 24%;"><strong>Curso</strong></th>';
			$msHTML .= '<th style="text-align: left;background-color:rgb(0,0,255);color: white; width: 14%;"><strong>Docente</strong></th>';
			$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 14%;"><strong>Período de clases</strong></th>';
			$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 7%;"><strong>Inicial</strong></th>';
			$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 9%;"><strong>Certificado</strong></th>';
			$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 10%;"><strong>Sin certificar</strong></th>';
			$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 8%;"><strong>Deserción</strong></th>';
			$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 6%;"><strong>Baja</strong></th>';
			$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 8%;"><strong>Retención</strong></th>';
			$msHTML .= '</tr>';
			$msHTML .= '</table>';
			$this->SetY(30);
			$this->writeHTML($msHTML);

			$this->top_margin = $this->GetY() + 5; // padding for second page
		}

		// Page footer
		function Footer()
		{
			// Position at 1.5 cm from bottom
			$this->SetY(-15);
			$this->SetFont('helvetica','I',8);
			// Page number
			$this->Cell(0,10,'Página '.$this->PageNo().'/'.$this->getAliasNbPages(),0,0,'L');
			$this->Cell(0,10,'Emitido: ' . date("d/m/Y h:i:s a") . '',0,0,'R');
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

	$pdf = new PDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, $pdf->top_margin, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	// set some language-dependent strings (optional)
	if (@file_exists(dirname(__FILE__).'/lang/spa.php')) {
		require_once(dirname(__FILE__).'/lang/spa.php');
		$pdf->setLanguageArray($l);
	}

	$pdf->setFontSize(9);
	
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

	$pdf->periodo=$msPeriodo;
	$pdf->AddPage();

	$msHTML = '<table>';

	$mbColorea = 0;
	$mnTotalInicial = 0;
	$mnTotalActivo = 0;
	$mnTotalInactivo = 0;
	$mnTotalCertificado = 0;
	$mnTotalDesercion = 0;
	$mnTotalBaja = 0;

	while ($Fila = $mDatos->fetch())
	{
		$msCurso = $Fila["CURSO_REL"];
		$msNomCurso = html_entity_decode($Fila["NOMBRE_020"]);
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
				$msDocente = $fAux["NOMBRE_100"];
			else
				$msDocente .= ", " . $fAux["NOMBRE_100"];
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

		if ($mnActivo > 0 or $mnCertificado > 0)
			$mnRetencion = (($mnActivo + $mnCertificado) * 100) / $mnInicial;
		else
			$mnRetencion = 0;

		$msHTML .= '<tr nobr="true">';

		if ($mbColorea == 0)
		{
			$msHTML .= '<td style="text-align: left; width: 24%;">' . trim($msNomCurso) . ' (' . trim($msConvocatoria) . ')</td>';
			$msHTML .= '<td style="text-align: left; width: 14%;">' . trim($msDocente) . '</td>';
			$msHTML .= '<td style="text-align: center; width: 14%;">' . trim($msPeriodo) . '</td>';
			$msHTML .= '<td style="text-align: center; width: 8%;">' . trim($mnInicial) . '</td>';
			$msHTML .= '<td style="text-align: center; width: 9%;">' . trim($mnCertificado) . '</td>';
			$msHTML .= '<td style="text-align: center; width: 8%;">' . trim($mnActivo) . '</td>';
			$msHTML .= '<td style="text-align: center; width: 8%;">' . trim($mnDesercion) . '</td>';
			$msHTML .= '<td style="text-align: center; width: 7%;">' . trim($mnBaja) . '</td>';
			$msHTML .= '<td style="text-align: center; width: 8%;">' . number_format($mnRetencion, 2) . '</td>';
		}
		else
		{
			$msHTML .= '<td style="background-color: #f2f2f2; text-align: left; width: 24%;">' . trim($msNomCurso) . ' (' . trim($msConvocatoria) . ')</td>';
			$msHTML .= '<td style="background-color: #f2f2f2; text-align: left; width: 14%;">' . trim($msDocente) . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 14%;">' . trim($msPeriodo) . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 8%;">' . trim($mnInicial) . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 9%;">' . trim($mnCertificado) . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 8%;">' . trim($mnActivo) . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 8%;">' . trim($mnDesercion) . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 7%;">' . trim($mnBaja) . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 8%;">' . number_format($mnRetencion, 2) . '</td>';
		}

		$msHTML .= '</tr>';
		
		if ($mbColorea == 0)
			$mbColorea = 1;
		else
			$mbColorea = 0;
	}

	if ($mnTotalInicial == 0)
		$mnPromedioRetencion = 0;
	else
		$mnPromedioRetencion = (($mnTotalActivo + $mnTotalCertificado) / $mnTotalInicial) * 100;

	$msHTML .= '<tr nobr="true">';
	$msHTML .= '<td style="text-align: left;background-color:rgb(0,0,255);color: white; width: 24%;">&nbsp;</td>';
	$msHTML .= '<td style="text-align: left;background-color:rgb(0,0,255);color: white; width: 14%;">&nbsp;</td>';
	$msHTML .= '<td style="text-align: center;background-color:rgb(0,0,255);color: white; width: 14%;">&nbsp;</td>';
	$msHTML .= '<td style="text-align: center;background-color:rgb(0,0,255);color: white; width: 8%;"><strong>' . trim($mnTotalInicial) . '</strong></td>';
	$msHTML .= '<td style="text-align: center;background-color:rgb(0,0,255);color: white; width: 9%;"><strong>' . trim($mnTotalCertificado) . '</strong></td>';
	$msHTML .= '<td style="text-align: center;background-color:rgb(0,0,255);color: white; width: 8%;"><strong>' . trim($mnTotalActivo) . '</strong></td>';
	$msHTML .= '<td style="text-align: center;background-color:rgb(0,0,255);color: white; width: 8%;"><strong>' . trim($mnTotalDesercion) . '</strong></td>';
	$msHTML .= '<td style="text-align: center;background-color:rgb(0,0,255);color: white; width: 7%;"><strong>' . trim($mnTotalBaja) . '</strong></td>';
	$msHTML .= '<td style="text-align: center;background-color:rgb(0,0,255);color: white; width: 8%;"><strong>' . number_format($mnPromedioRetencion, 2) . '</strong></td>';
	$msHTML .= '</tr>';
	$msHTML .= '</table>';
	$pdf->SetY(35);
	$pdf->writeHTML($msHTML);
	$pdf->Output();
}
?>