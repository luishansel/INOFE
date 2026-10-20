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
	require_once ("funciones/fxEstudiantes.php");
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
		if (isset($_POST["mnOpcion"]) and isset($_POST["mnApellido"]))
		{
			$mnOpcion = $_POST["mnOpcion"];
			$mnApellido = $_POST["mnApellido"];
		}
		else{
			$mnOpcion = 0;
			$mnApellido = 0;
		}
		$Administrador = fxVerificaAdministrador();
		$PermisoUsuario = fxPermisoUsuario("catEstudiantes", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
						
						echo('<label id="matricular" data-toggle="tooltip" data-placement="top" title="Matricular"><img src="imagenes/btnLateralMatricular.png" height="80%" style="cursor:pointer" /></label>');
						echo('<label id="ctasxcobrar" data-toggle="tooltip" data-placement="top" title="Ctas x cobrar"><img src="imagenes/btnLateralCtas.png" height="80%" style="cursor:pointer" /></label>');
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
							
							echo('<button id="matricula" type="button" class="btn btn-warning">Matricular</button>');
							echo('<button id="ctaxcobrar" type="button" class="btn btn-warning">Cuentas por Cobrar</button>');
						?>

						<div style="float:right; margin-right:1%; display:inline-block">
							<?php
								if ($mnOpcion == 0)
									echo('<input type="radio" name="optFiltro" id="optFiltro1" onchange="fxCambiaOpcion()" checked>Filtrar por apellido &nbsp;');
								else
									echo('<input type="radio" name="optFiltro" id="optFiltro1" onchange="fxCambiaOpcion()">Filtrar por apellido &nbsp;');

								echo('<select style="background-color: white" id="cboApellido" name="cboApellido" onchange="fxCambiaOpcion()">');
								if ($mnApellido == 0)
									echo("<option value='0' selected>A - E</option>");
								else
									echo("<option value='0'>A - E</option>");

								if ($mnApellido == 1)
									echo("<option value='1' selected>F - J</option>");
								else
									echo("<option value='1'>F - J</option>");

								if ($mnApellido == 2)
									echo("<option value='2' selected>K - Ñ</option>");
								else
									echo("<option value='2'>K - Ñ</option>");

								if ($mnApellido == 3)
									echo("<option value='3' selected>O - S</option>");
								else
									echo("<option value='3'>O - S</option>");

								if ($mnApellido == 4)
									echo("<option value='4' selected>T - Z</option>");
								else
									echo("<option value='4'>T - Z</option>");
								echo('</select> &nbsp;');
								
								if ($mnOpcion == 1)
									echo('<input type="radio" name="optFiltro" id="optFiltro2" onchange="fxCambiaOpcion()" checked>Todos los registros');
								else
									echo('<input type="radio" name="optFiltro" id="optFiltro2" onchange="fxCambiaOpcion()">Todos los registros');
							?>
						</div>
						
						<div class="table-responsive">
							<table id="grid" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true">
								<thead>
									<tr>
										<th data-column-id="ESTUDIANTE_REL" data-identifier="true" data-align="left" data-width="15%">Estudiante</th>
										<th data-column-id="APELLIDOS_010" data-header-align="left" data-width="25%">Apellidos</th>
										<th data-column-id="NOMBRES_010" data-align="left" data-header-align="left" data-width="25%">Nombres</th>
										<th data-column-id="CELULAR_010" data-align="left" data-header-align="left" data-width="15%">Celular</th>
										<th data-column-id="CORREO_010" data-align="left" data-header-align="left" data-width="20%">eMail</th>
									</tr>
								</thead>
								<tbody>
								<?php
									$mDatos = fxDevuelveEstudiantes(1, "", $mnApellido);
									while ($Fila = $mDatos->fetch())
									{
										echo ("<tr>");
										echo ("<td>" . $Fila["ESTUDIANTE_REL"] . "</td>");
										echo ("<td>" . $Fila["APELLIDOS_010"] . "</td>");
										echo ("<td>" . $Fila["NOMBRES_010"] . "</td>");
										echo ("<td>" . $Fila["CELULAR_010"] . "</td>");
										echo ("<td>" . $Fila["CORREO_010"] . "</td>");
									}
								}
								?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
            </div>
    	</div>
<?php }?>
<script src="bootstrap/lib/jquery-1.11.1.min.js"></script>
<script src="bootstrap/js/bootstrap.js"></script>
<script src="bootstrap/dist/jquery.bootgrid.js"></script>
<script src="bootstrap/dist/jquery.bootgrid.fa.js"></script>
<script src="js/jquery.redirect.js"></script>
<script>
	$(function() {
		$(window).scroll(function() {
			var scroll = $(window).scrollTop();
			if (scroll >= 100) {
			$("#lateral").addClass("entra");
			} else {
			$("#lateral").removeClass("entra");
			}
		});
	});

	$(function() {
		function init() {
			$("#grid").bootgrid({
				formatters: {
					"link": function(column, row) {
						return "<a href=\"#\">" + column.id + ": " + row.id + "</a>";
					}
				},
				rowCount: [-1, 10, 50, 75]
			});
		}

		init();

		$("#append").on("click", function() {
			$.redirect("catEstudiantes.php", "POST");
		});

		$("#agregar").on("click", function() {
			$.redirect("catEstudiantes.php", "POST");
		});
			
		$("#edit").on("click", function() {
			if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var codEstudiante = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("catEstudiantes.php", {KDSA: codEstudiante}, "POST");
			}
		});

		$("#modificar").on("click", function() {
			if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var codEstudiante = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("catEstudiantes.php", {KDSA: codEstudiante}, "POST");
			}
		});

		$("#matricula").on("click", function() {
			if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var msEstudiante = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("procMatricula.php", {mAccion: 1, mEstudiante: msEstudiante}, "POST");
			}
		});

		$("#matricular").on("click", function() {
			if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var msEstudiante = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("procMatricula.php", {mAccion: 1, mEstudiante: msEstudiante}, "POST");
			}
		});

		$("#ctaxcobrar").on("click", function() {
			if ($.trim($("#grid").bootgrid("getSelectedRows")) != ""){
				var estudiante = $("#grid").bootgrid("getSelectedRows");
				var fechaHoy = new Date();
				var fechaMySQL;
				var mesMySQL;
				var diaMySQL;

				if (fechaHoy.getMonth()+1 < 10)
					mesMySQL = '0' + (fechaHoy.getMonth()+1);
				else
					mesMySQL = fechaHoy.getMonth()+1;

				if (fechaHoy.getDate() < 10)
					diaMySQL = '0' + (fechaHoy.getDate());
				else
					diaMySQL = fechaHoy.getDate();

				fechaMySQL = fechaHoy.getFullYear() + "-" + mesMySQL + "-" + diaMySQL;
				$.redirect("repCtasPorCobrarEst.php", {codEstudiante: $.trim(estudiante), dtpFechaFin: fechaMySQL}, "POST", "_blank");
			}
		});

		$("#ctasxcobrar").on("click", function() {
			if ($.trim($("#grid").bootgrid("getSelectedRows")) != ""){
				var estudiante = $("#grid").bootgrid("getSelectedRows");
				var fechaHoy = new Date();
				var fechaMySQL;
				var mesMySQL;
				var diaMySQL;

				if (fechaHoy.getMonth()+1 < 10)
					mesMySQL = '0' + (fechaHoy.getMonth()+1);
				else
					mesMySQL = fechaHoy.getMonth()+1;

				if (fechaHoy.getDate() < 10)
					diaMySQL = '0' + (fechaHoy.getDate());
				else
					diaMySQL = fechaHoy.getDate();

				fechaMySQL = fechaHoy.getFullYear() + "-" + mesMySQL + "-" + diaMySQL;
				$.redirect("repCtasPorCobrarEst.php", {codEstudiante: $.trim(estudiante), dtpFechaFin: fechaMySQL}, "POST", "_blank");
			}
		});
	});

	function fxCambiaOpcion(){
		var mnOpcion;
		var mnApellido;

		if (document.getElementById("optFiltro1").checked == true){
			mnOpcion = 0;
			mnApellido = $("#cboApellido").val();
		}
		else{
			mnOpcion = 1;
			mnApellido = 5;
		}

		$.redirect("gridEstudiantes.php", {mnOpcion: mnOpcion, mnApellido: mnApellido}, "POST");
	}
</script>
</body>
</html>