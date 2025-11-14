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
class PDF extends TCPDF
{
	public $Periodo;
	
	// Page header
	function Header()
	{
		// Logos
		$this->Image('imagenes/headerLogin.jpg',15,12,0,15);
		// Title
		$mid_x = 210; // width of the "PDF screen", fixed by now.
		// Arial bold 18
		$this->SetFont('helvetica','B',13);
		$Titulo = 'PROYECCIONES';
		$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 13, $Titulo);
		// Arial normal 18
		$this->SetFont('helvetica','',11);
		$Titulo = mb_convert_encoding($this->Periodo, "UTF-8");
		$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 18, $Titulo);
	}
	// Page footer
	function Footer()
	{
		// Position at 1.5 cm from bottom
		$this->SetY(-15);
		// Arial italic 8
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

$FechaIni = date("Y-m-d", strtotime($_POST["dtpFechaIni"]));
$FechaFin = date("Y-m-d", strtotime($_POST["dtpFechaFin"]));

if ($FechaIni == $FechaFin)
	$Rotulo = "Fecha del " . DevuelveFecha($_POST["dtpFechaIni"]);
else
	$Rotulo = "Período del " . DevuelveFecha($_POST["dtpFechaIni"]) . " al " . DevuelveFecha($_POST["dtpFechaFin"]);

$pdf = new PDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, 30, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/spa.php')) {
	require_once(dirname(__FILE__).'/lang/spa.php');
	$pdf->setLanguageArray($l);
}
$pdf->Periodo=$Rotulo;
$pdf->AddPage();
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('helvetica','',8);

//Obtención de datos
$msConsulta = "select KDSA051A.MATRICULA_REL, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE, concat(NOMBRE_020, ' (', CONVOCATORIA_020, ' /G', GRUPO_020, ')') as CURSO, ";
$msConsulta .= "sum(ADEUDADO_051) as ADEUDADO_051 from KDSA051A, KDSA030A, KDSA010A, KDSA020A, KDSA050A where PAGADO_051 = 0 and EXONERADO_051 = 0 and ANULADO_051 = 0 and ";
$msConsulta .= "KDSA051A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and ";
$msConsulta .= "KDSA051A.COBRO_REL = KDSA050A.COBRO_REL and ESTADO_030 <> 4 and FECHAPREVISTA_050 >= ? and FECHAPREVISTA_050 <= ? ";
$msConsulta .= "group by KDSA051A.MATRICULA_REL order by KDSA030A.CURSO_REL, KDSA051A.MATRICULA_REL";

$m_cnx_MySQL = fxAbrirConexion();
$mDatos = $m_cnx_MySQL->prepare($msConsulta);
$mDatos->execute([$FechaIni, $FechaFin]);

$mnLinea = 30;
$Suma = 0;
$mbRelleno = true;

$msHTML = '<style>';
$msHTML .= "th{";
$msHTML .= "background-color:rgb(0,0,255); color: white; font-weight: bolder;";
$msHTML .= "}";
$msHTML .= ".azul{";
$msHTML .= "background-color:rgb(0,0,255); color: white; font-weight: bolder;";
$msHTML .= "}";
$msHTML .= ".anchoMatricula{";
$msHTML .= "width: 15%;";
$msHTML .= "}";
$msHTML .= ".anchoEstudiante{";
$msHTML .= "width: 35%;";
$msHTML .= "}";
$msHTML .= ".anchoCurso{";
$msHTML .= "width: 35%;";
$msHTML .= "}";
$msHTML .= ".anchoMonto{";
$msHTML .= "width: 15%;";
$msHTML .= "}";
$msHTML .= ".relleno{";
$msHTML .= "background-color: rgb(230,230,230);";
$msHTML .= "}";
$msHTML .= ".derecha{";
$msHTML .= "text-align: right;";
$msHTML .= "}";
$msHTML .= '</style>';

$msHTML .= '<table>';
$msHTML .= '<thead><tr>';
$msHTML .= '<th class="anchoMatricula">Matrícula</th>';
$msHTML .= '<th class="anchoEstudiante">Estudiante</th>';
$msHTML .= '<th class="anchoCurso">Curso</th>';
$msHTML .= '<th class="anchoMonto derecha">Monto C$</th>';
$msHTML .= '</tr></thead>';
$msHTML .= '<tbody>';

while ($Fila = $mDatos->fetch())
{
	$Matricula = $Fila["MATRICULA_REL"];
	$Estudiante = mb_convert_encoding($Fila["ESTUDIANTE"], "UTF-8", "latin1");
	$Curso = mb_convert_encoding($Fila["CURSO"], "UTF-8", "latin1");
	$Monto = $Fila["ADEUDADO_051"];
	
	$msHTML .= '<tr>';

	if ($mbRelleno)
	{
		$msHTML .= '<td class="anchoMatricula relleno">' . $Matricula . '</td>';
		$msHTML .= '<td class="anchoEstudiante relleno">' . $Estudiante . '</td>';
		$msHTML .= '<td class="anchoCurso relleno">' . $Curso . '</td>';
		$msHTML .= '<td class="anchoMonto relleno derecha">' . number_format($Monto,2,'.',',') . '</td>';
	}
	else
	{
		$msHTML .= '<td class="anchoMatricula">' . $Matricula . '</td>';
		$msHTML .= '<td class="anchoEstudiante">' . $Estudiante . '</td>';
		$msHTML .= '<td class="anchoCurso">' . $Curso . '</td>';
		$msHTML .= '<td class="anchoMonto derecha">' . number_format($Monto,2,'.',',') . '</td>';
	}
	
	$msHTML .= '</tr>';

	$Suma += $Monto;
	$mbRelleno = !$mbRelleno;
}
$msHTML .= '<tr>';
$msHTML .= '<td class="anchoMatricula azul"></td>';
$msHTML .= '<td class="anchoEstudiante azul"></td>';
$msHTML .= '<td class="anchoCurso azul derecha">Totales (C$)</td>';
$msHTML .= '<td class="anchoMonto azul derecha">' . number_format($Suma,2,'.',',') . '</td>';
$msHTML .= '</tr>';
$msHTML .= '</tbody>';
$msHTML .= '</table>';

$pdf->SetXY(15,$mnLinea);
$pdf->writeHTML($msHTML);
$pdf->Output();
}
?>