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
	require_once ("funciones/fxCobrosEmpresa.php");
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
		$PermisoUsuario = fxPermisoUsuario("procCobrosEmpresa", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
			if (isset($_POST["mOperacion"]))
            {
				$mnOperacion = $_POST["mOperacion"];
				$msCodCobro = $_POST["mCobro"];

				if ($mnOperacion == 0)
				{
					fxExonerarCobroEmpresa($msCodCobro);
					fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA052A", $msCodCobro, "", "Exonerar");
				}
				else
				{
                	fxAnularCobroEmpresa($msCodCobro);
					fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA052A", $msCodCobro, "", "Anular");
				}
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
							
						if ($mbModificar == 1 or $Administrador == 1)
						{
							echo('<button id="edit" type="button" class="btn btn-warning">Editar</button>');
							echo('<button id="exonerate" type="button" class="btn btn-warning">Exonerar</button>');
						}
						else
						{
							echo('<button id="edit" type="button" class="btn btn-warning" disabled>Editar</button>');
							echo('<button id="exonerate" type="button" class="btn btn-warning" disabled>Exonerar</button>');
						}
						
						if ($mbAnular == 1 or $Administrador == 1)
							echo('<button id="remove" type="button" class="btn btn-warning">Anular</button>');
						else
							echo('<button id="remove" type="button" class="btn btn-warning" disabled>Anular</button>');
					?>
                    
                    <table id="grid" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true">
                    	<thead>
                            <tr>
                                <th data-column-id="COBRO_REL" data-identifier="true" data-align="left" data-header-align="left" data-width="10%">Cobro</th>
                                <th data-column-id="DEUDOR_052" data-order="asc" data-align="left" data-header-align="left">Deudor</th>
                                <th data-column-id="NOMBRE_020" data-order="asc" data-align="left" data-header-align="left">Curso</th>
                                <th data-column-id="MONTO_050" data-order="asc" data-align="right" data-header-align="right" data-width="15%">Monto</th>
                                <th data-column-id="PAGADO_052" data-order="asc" data-align="center" data-header-align="center" data-width="10%">Pagado</th>
                                <th data-column-id="EXONERADO_052" data-order="asc" data-align="center" data-header-align="center" data-width="10%">Exonerado</th>
                                <th data-column-id="ANULADO_052" data-order="asc" data-align="center" data-header-align="center" data-width="10%">Anulado</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
							$mDatos = fxDevuelveCobroEmpresa(1);

							while ($Fila = $mDatos->fetch())
							{
								echo ("<tr>");
								echo ("<td>" . $Fila["COBRO_REL"] . "</td>");
								echo ("<td>" . $Fila["DEUDOR_052"] . "</td>");
								echo ("<td>" . $Fila["NOMBRE_020"] . "</td>");
								echo ("<td>" . $Fila["MONTO_050"] . "</td>");
								echo ("<td>" . $Fila["PAGADO_052"] . "</td>");
								echo ("<td>" . $Fila["EXONERADO_052"] . "</td>");
								echo ("<td>" . $Fila["ANULADO_052"] . "</td>");
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
				$.redirect("procCobrosEmpresa.php", "POST");
			});
			
			$("#exonerate").on("click", function() {
				if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
				{
					var codCobro = $.trim($("#grid").bootgrid("getSelectedRows"));
					$.redirect("gridCobrosEmpresa.php", {mOperacion: 0, mCobro: codCobro}, "POST");
				}
			});
			
			$("#remove").on("click", function() {
				if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
				{
					var codCobro = $.trim($("#grid").bootgrid("getSelectedRows"));
					$.redirect("gridCobrosEmpresa.php", {mOperacion: 1, mCobro: codCobro}, "POST");
				}
			});
      			
            $("#edit").on("click", function() {
				if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
				{
					var codCobro = $.trim($("#grid").bootgrid("getSelectedRows"));
					$.redirect("procCobrosEmpresa.php", {KDSA: codCobro}, "POST");
				}
            });
        });
    </script>
</body>
</html>
