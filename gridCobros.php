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
		$PermisoUsuario = fxPermisoUsuario("catCobros", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
                fxAnularCobros($_POST["KDSA"]);
				fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA050A", $_POST["KDSA"], "", "Anular");
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
						echo('<label id="anular" data-toggle="tooltip" data-placement="top" title="Anular"><img src="imagenes/btnLateralAnular.png" height="80%" style="cursor:pointer" /></label>');
					else
						echo('<label id="anularDis" data-toggle="tooltip" data-placement="top" title="Anular"><img src="imagenes/btnLateralAnularDis.png" height="80%" style="cursor:default" /></label>');
					
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
						
						if ($mbAnular == 1 or $Administrador == 1)
							echo('<button id="remove" type="button" class="btn btn-warning">Anular</button>');
						else
							echo('<button id="remove" type="button" class="btn btn-warning" disabled>Anular</button>');
					?>
                    
                    <table id="grid" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true" style="font-size:small">
                    	<thead>
                            <tr>
                                <th data-column-id="COBRO_REL" data-identifier="true" data-align="left" data-header-align="left" data-width="10%">Cobro</th>
                                <th data-column-id="NOMBRE_020" data-align="left" data-header-align="left">Curso</th>
                                <th data-column-id="CONCEPTO_050" data-align="left" data-header-align="left" data-width="25%">Concepto</th>
                                <th data-column-id="TIPO_050" data-align="left" data-header-align="left" data-width="10%">Tipo</th>
                                <th data-column-id="FECHAPREVISTA_050" data-align="center" data-header-align="center" data-width="10%">Fecha prevista</th>
                                <th data-column-id="ACTIVO_050" data-align="center" data-header-align="center" data-width="8%">Activo</th>
                                <th data-column-id="ANULADO_050" data-align="center" data-header-align="center" data-width="8%">Anulado</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
							$mDatos = fxDevuelveCobros(1);

							while ($Fila = $mDatos->fetch())
							{
								echo ("<tr>");
								echo ("<td>" . $Fila["COBRO_REL"] . "</td>");
								echo ("<td>" . $Fila["NOMBRE_020"] . " (" . $Fila["CONVOCATORIA_020"] . " / G" . $Fila["GRUPO_020"] . ")</td>");
								echo ("<td>" . $Fila["CONCEPTO_050"] . "</td>");
								echo ("<td>" . $Fila["TIPO_050"] . "</td>");
								$fecha = date_create_from_format('Y-m-d', $Fila["FECHAPREVISTA_050"]);
								echo ("<td>" . date_format($fecha, 'd-m-Y') . "</td>");
								echo ("<td>" . $Fila["ACTIVO_050"] . "</td>");
								echo ("<td>" . $Fila["ANULADO_050"] . "</td>");
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
        
        function init() {
            $("#grid").bootgrid({
                formatters: {
                    "link": function(column, row) {
                        return "<a href=\"#\">" + column.id + ": " + row.id + "</a>";
                    }
                },
                //rowCount: [-1, 10, 50, 75]
				rowCount: [50, 75]
            });
        }

        init();

        $("#append").on("click", function() {
			$.redirect("catCobros.php", "POST");
        });
        
        $("#remove").on("click", function() {
			if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var codCobro = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("gridCobros.php", {KDSA: codCobro}, "POST");
			}
        });
            
        $("#edit").on("click", function() {
            if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var codCobro = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("catCobros.php", {KDSA: codCobro}, "POST");
			}
		});

        $("#agregar").on("click", function() {
            $.redirect("catCursos.php", "POST");
        });
        
        $("#anular").on("click", function() {
            if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var codCobro = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("gridCobros.php", {KDSA: codCobro}, "POST");
			}
		});

        $("#modificar").on("click", function() {
            if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var codCobro = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("catCobros.php", {KDSA: codCobro}, "POST");
			}
		});
    });
</script>
</body>
</html>