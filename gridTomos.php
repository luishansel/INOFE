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
	require_once ("funciones/fxTomosCertificacion.php");
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
		$PermisoUsuario = fxPermisoUsuario("procTomos", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
                fxBorrarTomos($_POST["KDSA"]);
				fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA180A", $_POST["KDSA"], "", "Borrar");
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
						
						if ($mbAnular == 1 or $Administrador == 1)
							echo('<button id="remove" type="button" class="btn btn-warning">Borrar</button>');
						else
							echo('<button id="remove" type="button" class="btn btn-warning" disabled>Borrar</button>');
					?>
                    
                    <table id="grid" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true" style="font-size:small">
                    	<thead>
                            <tr>
                                <th data-column-id="TOMO_REL" data-identifier="true" data-align="left" data-header-align="left" data-width="10%">Tomo</th>
								<th data-column-id="DESCRIPCION_180" data-align="left" data-header-align="left">Descripción</th>
                                <th data-column-id="APERTURA_180" data-align="left" data-header-align="left">Fecha de apertura</th>
								<th data-column-id="TIPO_180" data-align="left" data-header-align="left">Tipo</th>
								<th data-column-id="NUMERO_180" data-align="left" data-header-align="left">Número de tomo</th>
								<th data-column-id="ULTIMOFOLIO_180" data-align="left" data-header-align="left">Ultimo folio</th>
                                <th data-column-id="ULTIMAACTA_180" data-align="left" data-header-align="left">Ultima  acta</th>
                                <th data-column-id="CERRADO_180" data-align="left" data-header-align="left">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
							$mDatos = fxDevuelveTomo(1);

							while ($Fila = $mDatos->fetch())
							{
								echo ("<tr>");
								echo ("<td>" . $Fila["TOMO_REL"] . "</td>");
								echo ("<td>" . $Fila["DESCRIPCION_180"] . "</td>");
								$fecha = date_create_from_format('Y-m-d', $Fila["APERTURA_180"]);
								echo ("<td>" . date_format($fecha, 'd-m-Y') . "</td>");
								echo ("<td>" . $Fila["TIPO_180"] . "</td>");
								echo ("<td>" . $Fila["NUMERO_180"] . "</td>");
								echo ("<td>" . $Fila["ULTIMOFOLIO_180"] . "</td>");
								echo ("<td>" . $Fila["ULTIMAACTA_180"] . "</td>");
								echo ("<td>" . $Fila["ESTADO"] . "</td>");
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
                rowCount: [-1, 10, 50, 75]
            });
        }

        init();

        $("#append").on("click", function() {
			$.redirect("procTomos.php", "POST");
        });
        
        $("#remove").on("click", function() {
			if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var codTomo = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("gridTomos.php", {KDSA: codTomo}, "POST");
			}
        });
            
        $("#edit").on("click", function() {
            if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var codTomo = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("procTomos.php", {KDSA: codTomo}, "POST");
			}
		});

        $("#agregar").on("click", function() {
            $.redirect("procTomos.php", "POST");
        });
        
        $("#borrar").on("click", function() {
            if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var codTomo = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("gridTomos.php", {KDSA: codTomo}, "POST");
			}
		});

        $("#modificar").on("click", function() {
            if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var codTomo = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("procTomos.php", {KDSA: codTomo}, "POST");
			}
		});
    });
</script>
</body>
</html>