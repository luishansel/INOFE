<?php
	session_start();
	if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
	{
		echo('<meta http-equiv="Refresh" content="0;url=index.php">');
		exit('');
    }
	
	include ("MasterWeb.php");
	require_once ("funciones/fxGeneral.php");
	require_once ("funciones/fxUsuarios.php");
	require_once ("funciones/fxCobros.php");
	require_once ("funciones/fxCursos.php");
	$m_cnx_MySQL = fxAbrirConexion();
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
		$Administrador = fxVerificaAdministrador();
		$PermisoUsuario = fxPermisoUsuario("catCursos", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
		if ($Administrador == 0 and $PermisoUsuario == 0)
		{ ?>
        <div class="container text-center">
        	<div id="DivContenido">
				<img src="imagenes/errordeacceso.png"/>
            </div>
        </div>
		<?php }
		else
		{
			if (isset($_POST["KDSA"]))
            {
				$mCurso = $_POST["KDSA"];
				$msConsulta = "select MATRICULA_REL from KDSA030A where CURSO_REL = ?";
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute([$mCurso]);
				if ($mDatos->rowCount() > 0)
				{
					?><script>$.messager.alert('KDSA','El Curso no puede ser borrado porque tiene Estudiantes matriculados.','warning');</script><?php
				}
                else
				{
					$msConsulta = "select PLANIFICACION_REL from KDSA120A, KDSA021A where KDSA120A.MODULO_REL = KDSA021A.MODULO_REL and CURSO_REL = ?";
					$mDatos = $m_cnx_MySQL->prepare($msConsulta);
					$mDatos->execute([$mCurso]);
					if ($mDatos->rowCount() > 0)
					{
						?><script>$.messager.alert('KDSA','El Curso no puede ser borrado porque tiene Planificaciones programáticas.','warning');</script><?php
					}
					else
					{
						$msConsulta = "select COBRO_REL from KDSA050A where CURSO_REL = ?";
						$mDatos = $m_cnx_MySQL->prepare($msConsulta);
						$mDatos->execute([$mCurso]);
						while ($Fila = $mDatos->fetch())
						{
							fxBorrarCobros($Fila["COBRO_REL"]);
							fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA050A", $mCurso, "", "Borrar");
						}
						fxBorrarCursos($mCurso);
						fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA020A", $mCurso, "", "Borrar");
					}
				}
            }
		?>
    	<div class="container">
        	<div id="DivContenido">
				<div id="lateral">
					<?php
						if ($mbAgregar == 1 or $Administrador == 1)
							echo('<label id="agregar" data-toggle="tooltip" data-placement="top" title="Agregar"><img src="imagenes/btnLateralAgregar.png" height="80%" style="cursor:pointer" /></label>');
						else
							echo('<label id="agregarDis" data-toggle="tooltip" data-placement="top" title="Agregar"><img src="imagenes/btnLateralAgregarDis.png" height="80%" style="cursor:default" /></label>');
							
						if ($mbModificar == 1 or $Administrador == 1)
							echo('<label id="modificar" data-toggle="tooltip" data-placement="top" title="Editar"><img src="imagenes/btnLateralEditar.png" height="80%" style="cursor:pointer" /></label>');
						else
							echo('<label id="modificarDis" data-toggle="tooltip" data-placement="top" title="Editar"><img src="imagenes/btnLateralEditarDis.png" height="80%" style="cursor:default" /></label>');
						
						if ($mbBorrar == 1 or $Administrador == 1)
							echo('<label id="borrar" data-toggle="tooltip" data-placement="top" title="Borrar"><img src="imagenes/btnLateralBorrar.png" height="80%" style="cursor:pointer" /></label>');
						else
							echo('<label id="borrarDis" data-toggle="tooltip" data-placement="top" title="Borrar"><img src="imagenes/btnLateralBorrarDis.png" height="80%" style="cursor:default" /></label>');
						
					?>
				</div>

				<div class="row">
					<div class="col-md-12">
						<?php
							if ($mbAgregar == 1 or $Administrador == 1)
								echo('<button id="append" type="button" class="btn btn-warning">Agregar</button>');
							else
								echo('<button id="append" type="button" class="btn btn-warning" disabled>Agregar</button>');
								
							if ($mbModificar == 1 or $Administrador == 1)
								echo('<button id="edit" type="button" class="btn btn-warning">Editar</button>');
							else
								echo('<button id="edit" type="button" class="btn btn-warning" disabled>Editar</button>');
								
							if ($mbBorrar == 1 or $Administrador == 1)
								echo('<button id="remove" type="button" class="btn btn-warning">Borrar</button>');
							else
								echo('<button id="remove" type="button" class="btn btn-warning" disabled>Borrar</button>');						
						?>
						
						<div id="tbCursos" class="easyui-tabs tabs-narrow" style="width:100%;height:auto">
							<div title="Activos" style="padding:10px">
								<table id="grid1" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true" style="font-size:small">
									<thead>
										<tr>
											<th data-column-id="CURSO_REL" data-identifier="true" data-order="desc"  data-align="left" data-header-align="left" data-width="10%">Curso</th>
											<th data-column-id="TIPO_020" data-align="left" data-header-align="left" data-width="10%">Tipo</th>
											<th data-column-id="NOMBRE_020" data-align="left" data-header-align="left">Nombre del Curso</th>
											<th data-column-id="FECHAINI_020" data-align="center" data-header-align="center" data-width="10%">Apertura</th>
											<th data-column-id="FECHAFIN_020" data-align="center" data-header-align="center" data-width="10%">Cierre</th>
											<th data-column-id="HORARIO" data-align="left" data-header-align="left" data-width="16%">Horario</th>
										</tr>
									</thead>
									<tbody>
									<?php
										$msConsulta = "select CURSO_REL, TIPO_020, NOMBRE_020, CONVOCATORIA_020, GRUPO_020, FECHAINI_020, FECHAFIN_020, HORAINI_020, HORAFIN_020 from KDSA020A where ACTIVO_020 = 1 and FECHAINI_020 < CURDATE()";
										$mDatos = $m_cnx_MySQL->prepare($msConsulta);
										$mDatos->execute();

										while ($Fila = $mDatos->fetch())
										{
											echo ("<tr>");
											echo ("<td>" . $Fila["CURSO_REL"] . "</td>");
											echo ("<td>" . Tipo($Fila["TIPO_020"]) . "</td>");
											echo ("<td>" . $Fila["NOMBRE_020"] . " (" . trim($Fila["CONVOCATORIA_020"]) . " / G" . trim($Fila["GRUPO_020"]) . ")</td>");
											$fecha = date_create_from_format('Y-m-d', $Fila["FECHAINI_020"]);
											echo ("<td>" . date_format($fecha, 'd-m-Y') . "</td>");
											$fecha = date_create_from_format('Y-m-d', $Fila["FECHAFIN_020"]);
											echo ("<td>" . date_format($fecha, 'd-m-Y') . "</td>");
											$horaIni = date_create($Fila["HORAINI_020"]);
											$horaFin = date_create($Fila["HORAFIN_020"]);
											echo ("<td>De " . date_format($horaIni, 'h:i a') . " a " . date_format($horaFin, 'h:i a') . "</td>");
											echo ("</tr>");
										}
									?>
									</tbody>
								</table>
							</div>

							<div title="En matrícula" style="padding:10px">
								<table id="grid2" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true" style="font-size:small">
									<thead>
										<tr>
											<th data-column-id="CURSO_REL" data-identifier="true" data-order="desc"  data-align="left" data-header-align="left" data-width="10%">Curso</th>
											<th data-column-id="TIPO_020" data-align="left" data-header-align="left" data-width="10%">Tipo</th>
											<th data-column-id="NOMBRE_020" data-align="left" data-header-align="left">Nombre del Curso</th>
											<th data-column-id="FECHAINI_020" data-align="center" data-header-align="center" data-width="10%">Apertura</th>
											<th data-column-id="FECHAFIN_020" data-align="center" data-header-align="center" data-width="10%">Cierre</th>
											<th data-column-id="HORARIO" data-align="left" data-header-align="left" data-width="16%">Horario</th>
										</tr>
									</thead>
									<tbody>
									<?php
										$msConsulta = "select CURSO_REL, TIPO_020, NOMBRE_020, CONVOCATORIA_020, GRUPO_020, FECHAINI_020, FECHAFIN_020, HORAINI_020, HORAFIN_020 from KDSA020A where ACTIVO_020 = 1 and FECHAINI_020 >= CURDATE()";
										$mDatos = $m_cnx_MySQL->prepare($msConsulta);
										$mDatos->execute();
										
										while ($Fila = $mDatos->fetch())
										{
											echo ("<tr>");
											echo ("<td>" . $Fila["CURSO_REL"] . "</td>");
											echo ("<td>" . Tipo($Fila["TIPO_020"]) . "</td>");
											echo ("<td>" . $Fila["NOMBRE_020"] . " (" . trim($Fila["CONVOCATORIA_020"]) . " / G" . trim($Fila["GRUPO_020"]) . ")</td>");
											$fecha = date_create_from_format('Y-m-d', $Fila["FECHAINI_020"]);
											echo ("<td>" . date_format($fecha, 'd-m-Y') . "</td>");
											$fecha = date_create_from_format('Y-m-d', $Fila["FECHAFIN_020"]);
											echo ("<td>" . date_format($fecha, 'd-m-Y') . "</td>");
											$horaIni = date_create($Fila["HORAINI_020"]);
											$horaFin = date_create($Fila["HORAFIN_020"]);
											echo ("<td>De " . date_format($horaIni, 'h:i a') . " a " . date_format($horaFin, 'h:i a') . "</td>");
											echo ("</tr>");
										}
									?>
									</tbody>
								</table>
							</div>

							<div title="Inactivos" style="padding:10px">
								<table id="grid3" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true" style="font-size:small">
									<thead>
										<tr>
											<th data-column-id="CURSO_REL" data-identifier="true" data-order="desc"  data-align="left" data-header-align="left" data-width="10%">Curso</th>
											<th data-column-id="TIPO_020" data-align="left" data-header-align="left" data-width="10%">Tipo</th>
											<th data-column-id="NOMBRE_020" data-align="left" data-header-align="left">Nombre del Curso</th>
											<th data-column-id="FECHAINI_020" data-align="center" data-header-align="center" data-width="10%">Apertura</th>
											<th data-column-id="FECHAFIN_020" data-align="center" data-header-align="center" data-width="10%">Cierre</th>
											<th data-column-id="HORARIO" data-align="left" data-header-align="left" data-width="16%">Horario</th>
										</tr>
									</thead>
									<tbody>
									<?php
										$msConsulta = "select CURSO_REL, TIPO_020, NOMBRE_020, CONVOCATORIA_020, GRUPO_020, FECHAINI_020, FECHAFIN_020, HORAINI_020, HORAFIN_020 from KDSA020A where ACTIVO_020 = 0";
										$mDatos = $m_cnx_MySQL->prepare($msConsulta);
										$mDatos->execute();
										
										while ($Fila = $mDatos->fetch())
										{
											echo ("<tr>");
											echo ("<td>" . $Fila["CURSO_REL"] . "</td>");
											echo ("<td>" . Tipo($Fila["TIPO_020"]) . "</td>");
											echo ("<td>" . $Fila["NOMBRE_020"] . " (" . trim($Fila["CONVOCATORIA_020"]) . " / G" . trim($Fila["GRUPO_020"]) . ")</td>");
											$fecha = date_create_from_format('Y-m-d', $Fila["FECHAINI_020"]);
											echo ("<td>" . date_format($fecha, 'd-m-Y') . "</td>");
											$fecha = date_create_from_format('Y-m-d', $Fila["FECHAFIN_020"]);
											echo ("<td>" . date_format($fecha, 'd-m-Y') . "</td>");
											$horaIni = date_create($Fila["HORAINI_020"]);
											$horaFin = date_create($Fila["HORAFIN_020"]);
											echo ("<td>De " . date_format($horaIni, 'h:i a') . " a " . date_format($horaFin, 'h:i a') . "</td>");
											echo ("</tr>");
										}
									?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
    	</div>
<?php }}?>
<script src="bootstrap/lib/jquery-1.11.1.min.js"></script>
<script src="bootstrap/js/bootstrap.js"></script>
<script src="bootstrap/dist/jquery.bootgrid.js"></script>
<script src="bootstrap/dist/jquery.bootgrid.fa.js"></script>
<script src="js/jquery.redirect.js"></script>
<script>
	$(window).scroll(function() {
		var scroll = $(window).scrollTop();
		if (scroll >= 100) {
		$("#lateral").addClass("entra");
		} else {
		$("#lateral").removeClass("entra");
		}
	});

	$("#append").on("click", function() {
		$.redirect("catCursos.php", "POST");
	});

	$("#agregar").on("click", function() {
		$.redirect("catCursos.php", "POST");
	});

	$(function() {
		function init() {
			$("#grid1").bootgrid({
				formatters: {
					"link": function(column, row) {
						return "<a href=\"#\">" + column.id + ": " + row.id + "</a>";
					}
				},
				rowCount: [-1, 10, 50, 75]
			});
		}

		init();

		$("#grid1").bootgrid().on("click.rs.jquery.bootgrid", function() {
            $("#grid2").bootgrid("deselect");
            $("#grid3").bootgrid("deselect");
        });

		$("#edit").on("click", function() {
			if ($.trim($("#grid1").bootgrid("getSelectedRows")) != "")
			{
				var codCurso = $.trim($("#grid1").bootgrid("getSelectedRows"));
				$.redirect("catCursos.php", {KDSA: codCurso}, "POST");
			}
		});
			
		$("#remove").on("click", function() {
			if ($.trim($("#grid1").bootgrid("getSelectedRows")) != "")
			{
				var codCurso = $.trim($("#grid1").bootgrid("getSelectedRows"));
				$.redirect("gridCursos.php", {KDSA: codCurso}, "POST");
			}
		});

		$("#modificar").on("click", function() {
			if ($.trim($("#grid1").bootgrid("getSelectedRows")) != "")
			{
				var codCurso = $.trim($("#grid1").bootgrid("getSelectedRows"));
				$.redirect("catCursos.php", {KDSA: codCurso}, "POST");
			}
		});
			
		$("#borrar").on("click", function() {
			if ($.trim($("#grid1").bootgrid("getSelectedRows")) != "")
			{
				var codCurso = $.trim($("#grid1").bootgrid("getSelectedRows"));
				$.redirect("gridCursos.php", {KDSA: codCurso}, "POST");
			}
		});
	});

	$(function() {
		function init() {
			$("#grid2").bootgrid({
				formatters: {
					"link": function(column, row) {
						return "<a href=\"#\">" + column.id + ": " + row.id + "</a>";
					}
				},
				rowCount: [-1, 10, 50, 75]
			});
		}

		init();

		$("#grid2").bootgrid().on("click.rs.jquery.bootgrid", function() {
            $("#grid1").bootgrid("deselect");
            $("#grid3").bootgrid("deselect");
        });

		$("#edit").on("click", function() {
			if ($.trim($("#grid2").bootgrid("getSelectedRows")) != "")
			{
				var codCurso = $.trim($("#grid2").bootgrid("getSelectedRows"));
				$.redirect("catCursos.php", {KDSA: codCurso}, "POST");
			}
		});
			
		$("#remove").on("click", function() {
			if ($.trim($("#grid2").bootgrid("getSelectedRows")) != "")
			{
				var codCurso = $.trim($("#grid2").bootgrid("getSelectedRows"));
				$.redirect("gridCursos.php", {KDSA: codCurso}, "POST");
			}
		});
			
		$("#modificar").on("click", function() {
			if ($.trim($("#grid2").bootgrid("getSelectedRows")) != "")
			{
				var codCurso = $.trim($("#grid2").bootgrid("getSelectedRows"));
				$.redirect("catCursos.php", {KDSA: codCurso}, "POST");
			}
		});
			
		$("#borrar").on("click", function() {
			if ($.trim($("#grid2").bootgrid("getSelectedRows")) != "")
			{
				var codCurso = $.trim($("#grid2").bootgrid("getSelectedRows"));
				$.redirect("gridCursos.php", {KDSA: codCurso}, "POST");
			}
		});
	});

	$(function() {
		function init() {
			$("#grid3").bootgrid({
				formatters: {
					"link": function(column, row) {
						return "<a href=\"#\">" + column.id + ": " + row.id + "</a>";
					}
				},
				rowCount: [-1, 10, 50, 75]
			});
		}

		init();

		$("#grid3").bootgrid().on("click.rs.jquery.bootgrid", function() {
            $("#grid1").bootgrid("deselect");
            $("#grid2").bootgrid("deselect");
        });

		$("#edit").on("click", function() {
			if ($.trim($("#grid3").bootgrid("getSelectedRows")) != "")
			{
				var codCurso = $.trim($("#grid3").bootgrid("getSelectedRows"));
				$.redirect("catCursos.php", {KDSA: codCurso}, "POST");
			}
		});
			
		$("#remove").on("click", function() {
			if ($.trim($("#grid3").bootgrid("getSelectedRows")) != "")
			{
				var codCurso = $.trim($("#grid3").bootgrid("getSelectedRows"));
				$.redirect("gridCursos.php", {KDSA: codCurso}, "POST");
			}
		});
			
		$("#modificar").on("click", function() {
			if ($.trim($("#grid3").bootgrid("getSelectedRows")) != "")
			{
				var codCurso = $.trim($("#grid3").bootgrid("getSelectedRows"));
				$.redirect("catCursos.php", {KDSA: codCurso}, "POST");
			}
		});
			
		$("#borrar").on("click", function() {
			if ($.trim($("#grid3").bootgrid("getSelectedRows")) != "")
			{
				var codCurso = $.trim($("#grid3").bootgrid("getSelectedRows"));
				$.redirect("gridCursos.php", {KDSA: codCurso}, "POST");
			}
		});
	});
</script>
</body>
</html>
<?php
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