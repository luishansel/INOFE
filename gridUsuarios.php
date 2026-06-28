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
		$PermisoUsuario = fxPermisoUsuario("catUsuarios", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
                fxDesactivarUsuario($_POST["KDSA"]);
				fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA002A", $_POST["KDSA"], "", "Desactivar");
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
							echo('<button id="edit" type="button" class="btn btn-warning">Editar</button>');
						else
							echo('<button id="edit" type="button" class="btn btn-warning" disabled>Editar</button>');
							
						if ($mbBorrar == 1 or $Administrador == 1)
							echo('<button id="remove" type="button" class="btn btn-warning">Borrar</button>');
						else
							echo('<button id="remove" type="button" class="btn btn-warning" disabled>Borrar</button>');
					?>
                    
                    <table id="grid" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true">
                    	<thead>
                            <tr>
                                <th data-column-id="USUARIO_REL" data-identifier="true" data-align="left" data-width="20%">Usuario</th>
                                <th data-column-id="NOMBRE_002" data-order="asc" data-align="left" data-header-align="left" data-width="50%">Nombre completo</th>
                                <th data-column-id="SUPERVISOR_002" data-type="boolean" data-align="center" data-header-align="center" data-width="15%">Supervisor</th>
                                <th data-column-id="ACTIVO_002" data-visible="boolean" data-align="center" data-header-align="center" data-width="15%">Activo</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
							$mDatos = fxDevuelveUsuario(1);
							
							while ($Fila = $mDatos->fetch())
							{
								echo ("<tr>");
								echo ("<td>" . $Fila["USUARIO_REL"] . "</td>");
								echo ("<td>" . $Fila["NOMBRE_002"] . "</td>");
								echo ("<td>" . $Fila["SUPERVISOR_002"] . "</td>");
								echo ("<td>" . $Fila["ACTIVO_002"] . "</td>");
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
                $.redirect("catUsuarios.php", "POST");
            });
    
            $("#remove").on("click", function() {
                if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
                {
                    var codUsuario = $.trim($("#grid").bootgrid("getSelectedRows"));
                    $.redirect("gridUsuarios.php", {KDSA: codUsuario}, "POST");
                }
            });
      			
            $("#edit").on("click", function() {
				if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
                {
                    var codUsuario = $.trim($("#grid").bootgrid("getSelectedRows"));
                    $.redirect("catUsuarios.php", {KDSA: codUsuario}, "POST");
                }
            });
        });
    </script>
</body>
</html>
