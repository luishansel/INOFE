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
	require_once ("funciones/fxCursosInatec.php");
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
		$PermisoUsuario = fxPermisoUsuario("catCursosInatec", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
                                <th data-column-id="CURSOINATEC_REL" data-identifier="true" data-align="left" data-header-align="left" data-width="10%">Curso</th>
                                <th data-column-id="NOMBRE_070" data-order="asc" data-align="left" data-header-align="left" data-width="35%">Nombre del Curso</th>
                                <th data-column-id="CODIGO_070" data-order="asc" data-align="left" data-header-align="left" data-width="15%">Código INATEC</th>
                                <th data-column-id="ACUERDO_070" data-order="asc" data-align="left" data-header-align="left" data-width="15%">Acuerdo</th>
                                <th data-column-id="FECHAVENC_070" data-order="asc" data-align="center" data-header-align="center" data-width="15%">Vencimiento</th>
                                <th data-column-id="ACTIVO_070" data-order="asc" data-align="center" data-header-align="center" data-width="10%">Activo</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                           $mDatos = fxDevuelveCursosInatec(1);
							
                            while ($Fila = $mDatos->fetch())
							{
								echo ("<tr>");
								echo ("<td>" . $Fila["CURSOINATEC_REL"] . "</td>");
								echo ("<td>" . $Fila["NOMBRE_070"] . "</td>");
								echo ("<td>" . $Fila["CODIGO_070"] . "</td>");
								echo ("<td>" . $Fila["ACUERDO_070"] . "</td>");
								$fecha = date_create_from_format('Y-m-d', $Fila["FECHAVENC_070"]);
								echo ("<td>" . date_format($fecha, 'd-m-Y') . "</td>");
								echo ("<td>" . $Fila["ACTIVO_070"] . "</td>");
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
                $.redirect("catCursosInatec.php", "POST");
    		});
      			
            $("#edit").on("click", function() {
                if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
                {
                    var codCurso = $.trim($("#grid").bootgrid("getSelectedRows"));
                    $.redirect("catCursosInatec.php", {KDSA: codCurso}, "POST");
                }
		    });
        });
    </script>
</body>
</html>
