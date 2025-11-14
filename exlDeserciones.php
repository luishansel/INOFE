<?php
session_start();
if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
{
	echo('<meta http-equiv="Refresh" content="0;url=index.php"/>');
	exit('');
}
set_time_limit(600); //600 segundos

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
<?php
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

$mbActivos = $_POST["activos"];
$mdFechaIni = $_POST["fechaIni"];
$mdFechaFin = $_POST["fechaFin"];
header('Content-type:application/xls; charset=UTF-8');
header('Content-Disposition: attachment; filename=Deserciones' . date('YmdHis') . '.xls');
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
$msHTML1 = "";

$msHTML = "<table>";
$msHTML .= '<thead>';
$msHTML .= '<tr>';
$msHTML .= '<th width="40%" style="text-align: left">Curso</th>';
$msHTML .= '<th width="30%" style="text-align: left">Docente</th>';
$msHTML .= '<th width="10%" style="text-align: right">Matrícula inicial</th>';
$msHTML .= '<th width="10%" style="text-align: right">Deserciones</th>';
$msHTML .= '<th width="10%" style="text-align: right">Porcentaje de deserción</th>';
$msHTML .= '</tr>';
$msHTML .= '</thead>';

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
			$msHTML1 .= fxDetalle($msCurso, $msNombre, $mnMatriculaInicial, $deserciones);
		}
	}
}

$msHTML .= $msHTML1;
$msHTML .= "</table>";

echo($msHTML);

function fxDetalle($msCurso, $msDocente, $mnMatriculaInicial, $mnDeserciones)
{
	$mnPorcentaje = ($mnDeserciones / $mnMatriculaInicial) * 100;

	$mHTML = '<tr>';
	$mHTML .= '<td>' . mb_convert_encoding($msCurso, "UTF-8") . '</td>';
	$mHTML .= '<td>' . mb_convert_encoding($msDocente, "UTF-8") . '</td>';
	$mHTML .= '<td>' . $mnMatriculaInicial . '</td>';
	$mHTML .= '<td>' . $mnDeserciones . '</td>';
	$mHTML .= '<td>' . number_format($mnPorcentaje,2,'.',',') . '</td>';
	$mHTML .= '</tr>';

	return $mHTML;
}
?>