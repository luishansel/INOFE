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
	require_once ("funciones/fxProformas.php");
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
		$PermisoUsuario = fxPermisoUsuario("procProformas", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
					
					echo('<label id="imprimir" data-toggle="tooltip" data-placement="top" title="Imprimir"><img src="imagenes/btnLateralImprimir.png" height="80%" style="cursor:pointer" /></label>');				
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
						
						echo('<button id="print" type="button" class="btn btn-warning">Imprimir</button>');
					?>
                    
                    <table id="grid" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true">
                    	<thead>
                            <tr>
                                <th data-column-id="PROFORMA_REL" data-identifier="true" data-align="left" data-header-align="left" data-width="10%">Proforma</th>
                                <th data-column-id="FECHA_090" data-align="center" data-header-align="center" data-width="10%">Fecha</th>
                                <th data-column-id="NOMBRE_060" data-align="left" data-header-align="left">Prospecto</th>
                                <th data-column-id="INATEC_090" data-align="center" data-header-align="center" data-width="10%">Inatec</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
							$mDatos = fxDevuelveProformas(1);
							
							while ($Fila = $mDatos->fetch())
							{
								echo ("<tr>");
								echo ("<td>" . $Fila["PROFORMA_REL"] . "</td>");
								$fecha = date_create_from_format('Y-m-d', $Fila["FECHA_090"]);
								echo ("<td>" . date_format($fecha, 'd-m-Y') . "</td>");
								echo ("<td>" . $Fila["NOMBRE_060"] . "</td>");
								echo ("<td>" . $Fila["INATEC_090"] . "</td>");
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
<script src="js/prmenu.min.js"></script>
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
                rowCount: [-1, 10, 50, 75]
            });
        }

        init();

        $("#append").on("click", function() {
			$.redirect("procProformas.php", {mOperacion: 0}, "POST");
        });
        
        $("#edit").on("click", function() {
            if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var msCodigo = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("procProformas.php", {mOperacion: 1, mCodigo: msCodigo}, "POST");
			}
        });
                
        $("#print").on("click", function() {
            if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var msCodigo = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("repProformas.php", {KDSA: msCodigo}, "POST", "_blank");
			}
        });

        $("#agregar").on("click", function() {
            $.redirect("procProformas.php", {mOperacion: 0}, "POST");
        });
        
        $("#modificar").on("click", function() {
            if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var msCodigo = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("procProformas.php", {mOperacion: 1, mCodigo: msCodigo}, "POST");
			}
        });
                
        $("#imprimir").on("click", function() {
            if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var msCodigo = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("repProformas.php", {KDSA: msCodigo}, "POST", "_blank");
			}
        });
    });
</script>
</body>
</html>
