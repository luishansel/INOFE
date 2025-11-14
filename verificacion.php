<?php
	#Inicia la sesión antes de la respuesta HTML
	session_start();
	$_SESSION["gnVerifica"] = 0;
	require_once ("funciones/fxGeneral.php");
?>
<!DOCTYPE html>
<html lang="ES-NI" class="no-js">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="description" content="Control Administrativo y Académico de INOFE."/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<link rel="icon" href="imagenes/favicon.png" />
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="css/easyui.css" />
<link rel="stylesheet" type="text/css" href="css/icon.css" />
<link rel="stylesheet" type="text/css" href="css/StyleKDSA.css"/>

<script src="js/jquery.min.js"></script>
<script src="js/jquery-3.4.1.js"></script>
<script src="js/jquery.easyui.min.js"></script>
<script src="bootstrap/js/bootstrap.js"></script>
<title>Aplicación web INOFE</title>

<style>
p
{
	font-size: 5vh;
	font-weight: bolder;
	color: rgb(0,0,255);
}
.fondoTH
{
	background-color: rgb(0,0,255);
	color: rgb(255,255,255);
	width: 25%;
	border: 3px solid;
	border-color: rgb(255,255,255);
	padding-left: 0.5%;
}
</style>
</head>

<body>
	<div id="cabecera">
        <div class="container-fluid">
            <img src="imagenes/header.png" width="100%" />
        </div>
    </div>
    
<?php
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
		return ($Dia . " de " . $NombreMes . " de " . $Anno);
	}

	if (isset($_GET['INOFE']))
	{
		$mnVerificacion = $_GET['INOFE'];
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select FOLIO_191, REGISTRO_191, TOMO_REL, ACTA_190, FECHA_190, NOMBRE_020, FECHAINI_020, FECHAFIN_020, TIPO_020, NOMBRES_010, APELLIDOS_010 ";
		$msConsulta .= "from KDSA191A, KDSA190A, KDSA030A, KDSA020A, KDSA010A ";
		$msConsulta .= "where KDSA191A.ACTA_REL = KDSA190A.ACTA_REL and ";
		$msConsulta .= "KDSA190A.CURSO_REL = KDSA020A.CURSO_REL and ";
		$msConsulta .= "KDSA191A.MATRICULA_REL = KDSA030A.MATRICULA_REL and ";
		$msConsulta .= "KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and VERIFICACION_191 = ?";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$mnVerificacion]);
		$mnRegistros = $mAuxiliar->rowCount();

		if ($mnRegistros == 0)
		{
?>
	<div class="container">
		<div id="DivContenido">
			<section class="row">
				<div class="col-xs-6 col-xs-offset-1 col-md-6 col-md-offset-2">
					<img src="imagenes/errorVerificacion.png" class="img-responsive" height="180vh" alt="">
				</div>
			</div>
		</div>
	</div>
<?php
		}
		else
		{
			$mAuxFila = $mAuxiliar->fetch();
			$mnFolio = $mAuxFila["FOLIO_191"];
			$mnRegistro = $mAuxFila["REGISTRO_191"];
			$msTomo = $mAuxFila["TOMO_REL"];
			$mnActa = $mAuxFila["ACTA_190"];
			$mnTomo = $mAuxFila["TOMO_190"];
			$msFecha = $mAuxFila["FECHA_190"];
			$msNombreCurso = html_entity_decode($mAuxFila["NOMBRE_020"]);
			$msFechaIni = $mAuxFila["FECHAINI_020"];
			$msFechaFin = $mAuxFila["FECHAFIN_020"];
			$mnTipoEstudio = $mAuxFila["TIPO_020"];
			$msNombreEstudiante = $mAuxFila["NOMBRES_010"];
			$msApellidoEstudiante = $mAuxFila["APELLIDOS_010"];

			if ($msFechaIni == $msFechaFin)
				$msFechaEstudio = "Día " . DevuelveFecha($msFechaIni);
			else
				$msFechaEstudio = "Desde el " . DevuelveFecha($msFechaIni) . " hasta el " . DevuelveFecha($msFechaFin);

			$msFechaEmision = DevuelveFecha($msFecha);

			switch($mnTipoEstudio)
			{
				case 0:
					$msTipoEstudio = "Seminario";
					break;
				case 1:
					$msTipoEstudio = "Curso";
					break;
				case 2:
					$msTipoEstudio = "Carrera";
					break;
				case 3:
					$msTipoEstudio = "Taller";
					break;
				case 4:
					$msTipoEstudio = "Diplomado";
					break;
			}
?>
    <div class="container">
		<div id="DivContenido">
			<section class="row">
				<div class="col-xs-6 col-xs-offset-1 col-md-6 col-md-offset-2">
					<img src="imagenes/INOFEribbon.png" class="img-responsive" height="150vh" alt="">
				</div>
			</section>
			<section class="row">
				<div class="col-xs-10 col-xs-offset-1 col-md-8 col-md-offset-2">
					<table class="table-responsive">
						<tr>
							<td class="fondoTH">Estudiante</td>
							<?php echo('<td>' . $msNombreEstudiante . ' ' . $msApellidoEstudiante . '</td>'); ?>
						</tr>
						<tr>
							<td class="fondoTH">Tipo de estudio</td>
							<?php echo('<td>' . $msTipoEstudio . '</td>'); ?>
						</tr>
						<tr>
							<td class="fondoTH">Estudio realizado</td>
							<?php echo('<td>' . $msNombreCurso . '</td>'); ?>
						</tr>
						<tr>
							<td class="fondoTH">Período de estudio</td>
							<?php echo('<td>' . $msFechaEstudio . '</td>'); ?>
						</tr>
						<tr>
							<td class="fondoTH">Registro</td>
							<?php echo('<td>No. ' . $mnRegistro . ' / Tomo ' . $mnTomo . "-" . $msTomo . ' / Folio ' . $mnFolio .  ' / Acta ' . $mnActa . '</td>'); ?>
						</tr>
						<tr>
							<td class="fondoTH">Fecha de emision</td>
							<?php echo('<td>' . $msFechaEmision . '</td>'); ?>
						</tr>
					</table>
				</div>
			</section>
		</div>
    </div>
		<?php } ?>
</body>
</html>
<?php
	}
?>