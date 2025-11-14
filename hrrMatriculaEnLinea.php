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
	require_once ("funciones/fxMatriculaEnLinea.php");
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
		$PermisoUsuario = fxPermisoUsuario("hrrMatriculaEnLinea", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
			if (isset($_POST["mnOperacion"]))
			{
				if ($_POST["mnOperacion"] == 0)
				{
					$msCurso = $_POST["cboCurso"];
					$msDestinatario = $_POST["txtDestinatario"];
					fxGuardarMatriculaEnLinea($msCurso, $msDestinatario);
				}
				else
				{
					$msEnlace = $_POST["txtEnlace"];
					fxBorrarMatriculaEnLinea($msEnlace);
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

					if ($mbBorrar == 1 or $Administrador == 1)
						echo('<label id="borrar" data-toggle="tooltip" data-placement="top" title="Borrar"><img src="imagenes/btnLateralBorrar.png" height="80%" style="cursor:pointer" /></label>');
					else
						echo('<label id="borrarDis" data-toggle="tooltip" data-placement="top" title="Borrar"><img src="imagenes/btnLateralBorrarDis.png" height="80%" style="cursor:default" /></label>');
					?>
					<label id="enlace" data-toggle="tooltip" data-placement="top" title="Mostrar enlace"><img src="imagenes/btnLateralImprimir.png" height="80%" style="cursor:pointer" /></label>
				</div>

				<div class="row">
					<div class="col-md-12">
						<?php
							if ($mbAgregar == 1 or $Administrador == 1)
								echo('<button id="append" type="button" class="btn btn-warning">Agregar</button>');
							else
								echo('<button id="append" type="button" class="btn btn-warning" disabled>Agregar</button>');

							if ($mbBorrar == 1 or $Administrador == 1)
								echo('<button id="remove" type="button" class="btn btn-warning">Borrar</button>');
							else
								echo('<button id="remove" type="button" class="btn btn-warning" disabled>Borrar</button>');
							
							echo('<button id="link" type="button" class="btn btn-warning">Mostrar enlace</button>');
						?>
					</div>
				</div>

				<div class="row" style="margin-top:1%">
					<div class="col-md-12">
						<label for="cboCurso" class="col-sm-12 col-md-2 form-label">Curso solicitado</label></th>
						<select class="form-control col-sm-12 col-md-10" id="cboCurso" name="cboCurso">
						<?php
							$msConsulta = "select CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE ";
							$msConsulta .= "from KDSA020A where ACTIVO_020 = 1  and DATEDIFF(CURRENT_DATE, FECHAINI_020) <= 8 ";
							$msConsulta .= "order by NOMBRE_020, FECHAINI_020, GRUPO_020";
							$mDatos = $m_cnx_MySQL->prepare($msConsulta);
							$mDatos->execute();
							
							while ($Fila = $mDatos->fetch())
							{
								$Curso = rtrim($Fila["CURSO_REL"]);
								$Texto = rtrim($Fila["NOMBRE"]);
								
								echo("<option value='" . $Curso . "'>" . $Texto . "</option>");
							}
						?>
						</select>
					</div>
				</div>

				<div class="row">
					<div class = "col-md-12">
						<label for="txtDestinatario" class="col-sm-12 col-md-2 col-form-label">Destinatario</label>
						<input type="text" class="form-control col-sm-12 col-md-10" id="txtDestinatario" name="txtDestinatario" value="" />
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						<table id="grid" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true" style="font-size:small">
							<thead>
								<tr>
									<th data-column-id="ENLACE_REL" data-identifier="true" data-align="left" data-header-align="left" data-width="15%">Código</th>
									<th data-column-id="FECHA_007" data-align="center" data-header-align="center" data-width="12%">Fecha</th>
									<th data-column-id="NOMBRE_020" data-align="left" data-header-align="left">Curso</th>
									<th data-column-id="DESTINATARIO_007" data-align="left" data-header-align="left" data-width="25%">Destinatario</th>
									<th data-column-id="ESTADO_007" data-align="center" data-header-align="center" data-width="10%">Estado</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$mDatos = fxDevuelveMatriculaEnLinea();
								
								while ($Fila = $mDatos->fetch())
								{
									echo ("<tr>");
									echo ("<td>" . $Fila["ENLACE_REL"] . "</td>");
									$fecha = date_create_from_format('Y-m-d H:i:s', $Fila["FECHA_007"]);
									echo ("<td>" . date_format($fecha, 'd-m-Y H:i') . "</td>");
									echo ("<td>" . $Fila["NOMBRE_020"] . "</td>");
									echo ("<td>" . $Fila["DESTINATARIO_007"] . "</td>");
									echo ("<td>" . $Fila["ESTADO_007"] . "</td>");
									echo ("</tr>");
								}
							}
							?>
							</tbody>
						</table>
					</div>
				</div>
            </div>
    	</div>
<?php }?>
<script src="bootstrap/lib/jquery-1.11.1.min.js"></script>
<script src="bootstrap/js/bootstrap.js"></script>
<script src="bootstrap/dist/jquery.bootgrid.js"></script>
<script src="bootstrap/dist/jquery.bootgrid.fa.js"></script>
<script src="js/jquery.easyui.min.js"></script>
<script src="js/jquery.redirect.js"></script>
<script src="js/prmenu.min.js"></script>
<script>
	function verificarFormulario() {
		if (document.getElementById('txtDestinatario').value == "") {
			$.messager.alert('KDSA', 'Falta el Nombre del Destinatario.', 'warning');
			return false;
		}

		return true;
	}

    $(function() {
        $(window).scroll(function() {
			var scroll = $(window).scrollTop();
			if (scroll >= 100) {
			$("#lateral").addClass("entra");
			} else {
			$("#lateral").removeClass("entra");
			}
		});

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
			var msCurso = document.getElementById("cboCurso").value;
			var msDestinatario = document.getElementById("txtDestinatario").value;
			if (verificarFormulario() == true) {
				$.redirect("hrrMatriculaEnLinea.php", {mnOperacion: 0, cboCurso: msCurso, txtDestinatario: msDestinatario}, "POST");
			}
        });

		$("#remove").on("click", function() {
			var msEnlace = $("#grid").bootgrid("getSelectedRows");
			$.redirect("hrrMatriculaEnLinea.php", {mnOperacion: 1, txtEnlace: msEnlace[0]}, "POST");
        });
        
        $("#link").on("click", function() {
            if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var msCodigo = $("#grid").bootgrid("getSelectedRows");
				alert('https://matricula.institutoinofe.com/frmMatriculaEnLinea.php?KDSA=' + msCodigo[0]);
			}
        });

		$("#agregar").on("click", function() {
			var msCurso = document.getElementById("cboCurso").value;
			var msDestinatario = document.getElementById("txtDestinatario").value;
			if (verificarFormulario() == true) {
				$.redirect("hrrMatriculaEnLinea.php", {mnOperacion: 0, cboCurso: msCurso}, "POST");
			}
        });

		$("#borrar").on("click", function() {
			var msEnlace = $("#grid").bootgrid("getSelectedRows");
			$.redirect("hrrMatriculaEnLinea.php", {mnOperacion: 1, txtEnlace: msEnlace[0], txtDestinatario: msDestinatario}, "POST");
        });

        $("#enlace").on("click", function() {
            if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var msCodigo = $("#grid").bootgrid("getSelectedRows");
				alert('https://matricula.institutoinofe.com/frmMatriculaEnLinea.php?KDSA=' + msCodigo[0]);
			}
        });
    });	
</script>
</body>
</html>