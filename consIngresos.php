<?php
	session_start();
	if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
	{
		echo('<meta http-equiv="Refresh" content="0;url=index.php"/>');
		exit('');
	}

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
	else
	{
		$Administrador = fxVerificaAdministrador();
		$PermisoUsuario = fxPermisoUsuario("repIngresos", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
		if ($Administrador == 0 and $PermisoUsuario == 0)
		{?>
		<div class="container text-center">
            <div id="DivContenido">
                <img src="imagenes/errordeacceso.png"/>
            </div>
        </div>
		<?php }
		else
		{
			$FechaIni = date("Y-m-d", strtotime($_POST["dtpFechaIni"]));
			$FechaFin = date("Y-m-d", strtotime($_POST["dtpFechaFin"]));
			$Serie = $_POST["optSerie"];
			$MonedaRep = $_POST["optMoneda"];

			$fechaHoy = date('YmdHis');
			$nombreArchivo = "Ingresos" . $fechaHoy . ".json";
			$archivo = fopen($nombreArchivo, "w");
			
			function NombreMes($Mes)
			{
				switch($Mes)
				{
					case 1:
						$Nombre = "01 (Enero)";
						break;
					case 2:
						$Nombre = "02 (Febrero)";
						break;			
					case 3:
						$Nombre = "03 (Marzo)";
						break;
					case 4:
						$Nombre = "04 (Abril)";
						break;
					case 5:
						$Nombre = "05 (Mayo)";
						break;
					case 6:
						$Nombre = "06 (Junio)";
						break;
					case 7:
						$Nombre = "07 (Julio)";
						break;
					case 8:
						$Nombre = "08 (Agosto)";
						break;
					case 9:
						$Nombre = "09 (Septiembre)";
						break;
					case 10:
						$Nombre = "10 (Octubre)";
						break;
					case 11:
						$Nombre = "11 (Noviembre)";
						break;
					case 12:
						$Nombre = "12 (Diciembre)";
						break;
				}
				return($Nombre);
			}
			
			function TipoPago($Tipo)
			{
				switch($Tipo)
				{
					case 0:
						$TipoPago = "Efectivo";
						break;
					case 1:
						$TipoPago = "Tarjeta";
						break;			
					case 2:
						$TipoPago = "Cheque";
						break;
					case 3:
						$TipoPago = "Depósito";
						break;
					case 4:
						$TipoPago = "Depósito";
						break;
					case 5:
						$TipoPago = "eCommerce";
						break;
				}
				return($TipoPago);
			}

			function TipoEstudio($Tipo)
			{
				switch($Tipo)
				{
					case -1:
						$TipoEstudio = "Sin tipo";
						break;
					case 0:
						$TipoEstudio = "Seminario";
						break;
					case 1:
						$TipoEstudio = "Curso";
						break;			
					case 2:
						$TipoEstudio = "Carrera";
						break;
					case 3:
						$TipoEstudio = "Taller";
						break;
					case 4:
						$TipoEstudio = "Diplomado";
						break;
				}
				return($TipoEstudio);
			}

			function TipoCobro($Tipo)
			{
				switch($Tipo)
				{
					case -1:
						$TipoCobro = "Otros ingresos";
						break;
					case 0:
						$TipoCobro = "Cuota";
						break;
					case 1:
						$TipoCobro = "Moratorio";
						break;			
					case 2:
						$TipoCobro = "Matrícula";
						break;
					case 3:
						$TipoCobro = "Empresarial";
						break;
					case 4:
						$TipoCobro = "INATEC";
						break;
					case 5:
						$TipoCobro = "Otros";
						break;
					case 6:
						$TipoCobro = "Cuota especial";
						break;
				}
				return($TipoCobro);
			}
			
			function DevuelveMonto($MonedaRep, $Moneda, $Monto, $TipoCambio)
			{
				if ($MonedaRep == 0)
				{
					if ($Moneda == 0)
						$MontoRep = $Monto;
					else
						$MontoRep = $Monto * $TipoCambio;
				}
				else
				{
					if ($Moneda == 0)
						$MontoRep = $Monto / $TipoCambio;
					else
						$MontoRep = $Monto;
				}
				
				return($MontoRep);
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
			
			$m_cnx_MySQL = fxAbrirConexion();
			//Obtención de datos
			$msConsulta = "select FECHA_040, year(FECHA_040) as ANNO, month(FECHA_040) as MES, NOMBRE_040, RECIBO_040, TIPOPAGO_040, TIPOCAMBIO_040, MONEDA_040, sum(MONTO_041) as MONTO_041, ";
			$msConsulta .= "KDSA020A.CURSO_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020, TIPO_020, TIPO_050, SERIE_040 from KDSA040A, KDSA041A, KDSA050A, KDSA020A ";
			$msConsulta .= "where KDSA040A.PAGO_REL = KDSA041A.PAGO_REL and KDSA041A.COBRO_REL = KDSA050A.COBRO_REL and ";
			$msConsulta .= "KDSA050A.CURSO_REL = KDSA020A.CURSO_REL and ANULADO_040 = 0 and ";
			$msConsulta .= "FECHA_040 between ? and ? group by KDSA041A.PAGO_REL ";
			$msConsulta .= "union ";
			$msConsulta .= "select FECHA_040, year(FECHA_040) as ANNO, month(FECHA_040) as MES, NOMBRE_040, RECIBO_040, TIPOPAGO_040, TIPOCAMBIO_040, MONEDA_040, MONTO_040 as MONTO, ";
			$msConsulta .= "'' as CURSO_REL, 'Sin Curso' as NOMBRE_020, '' as CONVOCATORIA_020, '' as GRUPO_020, -1 as TIPO_020, -1 as TIPO_050, SERIE_040 from KDSA040A ";
			$msConsulta .= "where ANULADO_040 = 0 and FECHA_040 between ? and ? and OTROINGRESO_040 = 1";
			
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		    $mDatos->execute([$FechaIni, $FechaFin, $FechaIni, $FechaFin]);
			$numRegistros = $mDatos->rowCount();
			if ($MonedaRep == 0)
				$nombreCampo = "MontoEnCordobas";
			else
				$nombreCampo = "MontoEnDolares";

			fwrite($archivo, "[" . PHP_EOL);
			
			for ($i = 1; $i <= $numRegistros; $i++)
			{
				$Fila = $mDatos->fetch();
				fwrite($archivo, "{");
				fwrite($archivo, '"Fecha":"' . trim(DevuelveFecha($Fila['FECHA_040'])) . '", ');
				fwrite($archivo, '"Anno":"' . trim($Fila['ANNO']) . '", ');
				fwrite($archivo, '"Mes":"' . trim(NombreMes($Fila['MES'])) . '", ');
				fwrite($archivo, '"Beneficiario":"' . trim($Fila['NOMBRE_040']) . '", ');
				fwrite($archivo, '"Recibo":"' . trim($Fila['RECIBO_040']) . '", ');
				fwrite($archivo, '"Serie":"' . trim($Fila['SERIE_040']) . '", ');
				fwrite($archivo, '"TipoEstudio":"' . trim(TipoEstudio($Fila['TIPO_020'])) . '", ');
				fwrite($archivo, '"TipoPago":"' . trim(TipoPago($Fila['TIPOPAGO_040'])) . '", ');
				fwrite($archivo, '"TipoCobro":"' . trim(TipoCobro($Fila['TIPO_050'])) . '", ');
				fwrite($archivo, '"Curso":"' . trim($Fila['NOMBRE_020']) . ' (' . trim($Fila['CONVOCATORIA_020']) . '/G' . trim($Fila['GRUPO_020']) . ')", ');
				fwrite($archivo, '"Monto":"' . trim(DevuelveMonto($MonedaRep, $Fila['MONEDA_040'], $Fila['MONTO_041'], $Fila['TIPOCAMBIO_040'])) . '"');
				
				if ($i == $numRegistros)
					fwrite($archivo, "}" . PHP_EOL);
				else
					fwrite($archivo, "}," . PHP_EOL);
			}
			
			fwrite($archivo, "]");
			fclose($archivo);

			/* cerrar el resulset */
			$mDatos->closeCursor();
		}
?>
<!DOCTYPE html>
<html lang="ES-NI">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="description" content="Control de Pagos. Aplicación web de KDSA."/>
<meta name="keywords" content="KDSA, Managua, Nicaragua, educacion, capacitacion, cursos, contabilidad, enfermeria, computacion"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<link rel="icon" href="imagenes/favicon.png" />
<link rel="stylesheet" type="text/css" href="css/easyui.css" />
<link rel="stylesheet" type="text/css" href="css/icon.css" />

<script src="js/jquery.min.js"></script>
<script src="js/jquery.easyui.min.js"></script>
<script src="js/jquery.pivotgrid.js"></script>
<style>
body
{
	background-image:url(imagenes/fondo.png);
	background-repeat:repeat;
	font-family: Arial, Helvetica, sans-serif;
}
.icon-layout{
	background:url('imagenes/layout.png') no-repeat center center;
}
.icon-excel{
	background:url('imagenes/excel.png') no-repeat center center;
}
</style>
<title>Control de Ingresos</title>
</head>
<body>
	<div style="float:left">
		<div>
			<a href="javascript:void(0)" class="easyui-linkbutton" style="width:70px;height:78px;" data-options="size:'large',iconCls:'icon-layout',iconAlign:'top',plain:false" onclick="javascript:$('#pgIngresos').pivotgrid('layout')">Diseño</a>
		</div>
		<div>
			<a href="javascript:void(0)" class="easyui-linkbutton" style="width:70px;height:78px;" data-options="size:'large',iconCls:'icon-excel',iconAlign:'top',plain:false" onclick="exportar()">Exportar</a>
		</div>
	</div>
    <table id="pgIngresos" style="width:90%; height:600px"></table>
	<script>
		$('#pgIngresos').pivotgrid({
			url:'<?php echo($nombreArchivo) ?>',
			method:'get',
			columns:[[
				{field:'Fecha',title:'Fecha',width:100},
				{field:'Anno',title:'Anno',width:100},
				{field:'Mes',title:'Mes',width:100},
				{field:'Beneficiario',title:'Beneficiario',width:100},
				{field:'Recibo',title:'Recibo',width:100},
				{field:'Serie',title:'Serie',width:100},
				{field:'TipoEstudio',title:'TipoEstudio',width:100},
				{field:'TipoPago',title:'TipoPago',width:100},
				{field:'TipoCobro',title:'TipoCobro',width:100},
				{field:'Curso',title:'Curso',width:100},
				{field:'Monto',title:'MontoEnCordobas',width:100,align:'right'}
			]],
			pivot:{
				rows:['Anno','TipoEstudio'],
				columns:['Mes'],
				values:[
					{field:'Monto',op:'sum', resizable: true}
				],
				aggregate:
				{
					column: {
						field: '_total',
						title: 'Total',
						width: 100,
						align: 'right',
						valuePrecision: 2,
						resizable: true
					}
				}
			},
			frozenColumnTitle:'<span style="font-weight:bold">Consulta de Ingresos</span>',
			valuePrecision:2
		});

		function exportar()
		{
			$('#pgIngresos').pivotgrid('toExcel', {
			filename: 'Ingresos.xls',
			worksheet: 'Ingresos'
			})
		}
	</script>
</body>
</html>
<?php } ?>