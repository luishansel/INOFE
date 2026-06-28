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
	require_once ("funciones/fxSeguimiento.php");
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
		$PermisoUsuario = fxPermisoUsuario("procSeguimiento", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
					?>
                    
                    <table id="grid" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true">
                    	<thead>
                            <tr>
                                <th data-column-id="SEGUIMIENTO_REL" data-identifier="true" data-align="left" data-header-align="left" data-width="12%">Seguimiento</th>
                                <th data-column-id="FECHA_080" data-align="center" data-header-align="center" data-width="10%">Fecha</th>
                                <th data-column-id="NOMBRE_060" data-align="left" data-header-align="left">Prospecto</th>
                                <th data-column-id="PROXIMOCONTACTO_080" data-align="center" data-header-align="center" data-width="15%">Próximo contacto</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
							$mDatos = fxDevuelveSeguimiento(1);
							
							while ($Fila = $mDatos->fetch())
							{
								echo ("<tr>");
								echo ("<td>" . $Fila["SEGUIMIENTO_REL"] . "</td>");
								$fecha = date_create_from_format('Y-m-d', $Fila["FECHA_080"]);
								echo ("<td>" . date_format($fecha, 'd-m-Y') . "</td>");
								echo ("<td>" . $Fila["NOMBRE_060"] . "</td>");
								$proximo = date_create_from_format('Y-m-d', $Fila["PROXIMOCONTACTO_080"]);
								echo ("<td>" . date_format($proximo, 'd-m-Y') . "</td>");
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
                $.redirect("procSeguimiento.php", {mnOperacion: 0}, "POST");
			});
			
            $("#edit").on("click", function() {
                if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
				{
                    var codSeguimiento = $.trim($("#grid").bootgrid("getSelectedRows"));
                    $.redirect("procSeguimiento.php", {mOperacion:1, mCodigo: codSeguimiento}, "POST");
                }
            });
        });
    </script>
</body>
</html>