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
	require_once ("funciones/fxPagos.php");
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
		$PermisoUsuario = fxPermisoUsuario("procPagosEmpresa", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
                fxAnularPagosEmpresa($_POST["KDSA"]);
				fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA040A", $_POST["KDSA"], "", "Anular");
            }
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
							
						if ($mbAnular == 1 or $Administrador == 1)
							echo('<button id="remove" type="button" class="btn btn-warning">Anular</button>');
						else
							echo('<button id="remove" type="button" class="btn btn-warning" disabled>Anular</button>');
							
						echo('<button id="view" type="button" class="btn btn-warning">Ver</button>');
					?>
                    
                    <table id="grid" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true" style="font-size:small">
                    	<thead>
                            <tr>
                                <th data-column-id="PAGO_REL" data-identifier="true" data-align="left" data-header-align="left" data-width="9%">Pago</th>
                                <th data-column-id="FECHA_040" data-order="asc" data-align="left" data-header-align="left" data-width="9%">Fecha</th>
                                <th data-column-id="CONCEPTO_040" data-order="asc" data-align="left" data-header-align="left">Concepto</th>
                                <th data-column-id="MONTO_040" data-order="asc" data-align="right" data-header-align="right" data-width="9%">Monto</th>
                                <th data-column-id="MONEDA_040" data-order="asc" data-align="center" data-header-align="center" data-width="9%">Moneda</th>
                                <th data-column-id="ANULADO_040" data-order="asc" data-align="center" data-header-align="center" data-width="8%">Anulado</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
							$mDatos = fxDevuelvePagosEmpresa();
							
							while ($Fila = $mDatos->fetch())
							{
								echo ("<tr>");
								echo ("<td>" . $Fila["PAGO_REL"] . "</td>");
								$fecha = date_create_from_format('Y-m-d', $Fila["FECHA_040"]);
								echo ("<td>" . date_format($fecha, 'd-m-Y') . "</td>");
								echo ("<td>" . $Fila["CONCEPTO_040"] . "</td>");
								echo ("<td>" . $Fila["MONTO_040"] . "</td>");
								echo ("<td>" . $Fila["MONEDA_040"] . "</td>");
								echo ("<td>" . $Fila["ANULADO_040"] . "</td>");
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
                $.redirect("procPagosEmpresa.php", "POST");
			});
			
			$("#remove").on("click", function() {
				if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
                {
                    var msCodigo = $.trim($("#grid").bootgrid("getSelectedRows"));
                    $.redirect("gridPagos.php", {KDSA: msCodigo}, "POST");
                }
			});
      			
            $("#view").on("click", function() {
				if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
                {
                    var msCodigo = $.trim($("#grid").bootgrid("getSelectedRows"));
                    $.redirect("procPagosEmpresa.php", {mAccion: 1, mCodigo: msCodigo}, "POST");
                }
            });
        });
    </script>
</body>
</html>
