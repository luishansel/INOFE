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
	require_once ("funciones/fxProspectos.php");
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
		$PermisoUsuario = fxPermisoUsuario("catProspectos", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
				$mProspecto = $_POST["KDSA"];
				$msSQL = "select PROFORMA_REL as CODIGO from KDSA090A where PROSPECTO_REL = ? ";
				$msSQL .= "union ";
				$msSQL .= "select SEGUIMIENTO_REL as CODIGO from KDSA080A where PROSPECTO_REL = ? ";
				$m_cnx_MySQL = fxAbrirConexion();
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute([$mProspecto, $mProspecto]);
				if ($mDatos->rowCount() > 0)
				{
					?><script>$.messager.alert('INOFE','El Prospecto no puede ser borrado porque tiene registros relacionados.','warning');</script><?php
				}
                else
				{
					fxBorrarProspectos($mProspecto);
					fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA060A", $mProspecto, "", "Borrar");
				}
            }
		?>
    	<div class="container">
        	<div id="DivContenido">
        	<div class="row">
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
						
						echo('<label id="seguir" data-toggle="tooltip" data-placement="top" title="Seguimiento"><img src="imagenes/btnLateralSeguir.png" height="80%" style="cursor:pointer" /></label>');
						echo('<label id="proformar" data-toggle="tooltip" data-placement="top" title="Proforma"><img src="imagenes/btnLateralProforma.png" height="80%" style="cursor:pointer" /></label>');
						
					?>
				</div>
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

						echo('<button id="seguimiento" type="button" class="btn btn-warning">Seguimiento</button>');
						echo('<button id="proforma" type="button" class="btn btn-warning">Proforma</button>');
					?>
                    
                    <table id="grid" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true">
                    	<thead>
                            <tr>
                                <th data-column-id="PROSPECTO_REL" data-identifier="true" data-align="left" data-header-align="left" data-width="15%">Prospecto</th>
                                <th data-column-id="NOMBRE_060" data-order="asc" data-align="left" data-header-align="left" data-width="25%">Nombre</th>
                                <th data-column-id="FECHAINGRESO_060" data-order="asc" data-align="left" data-header-align="left" data-width="15%">Fecha de ingreso</th>
                                <th data-column-id="NOMBRECONTACTO_060" data-order="asc" data-align="left" data-header-align="left" data-width="20%">Contacto</th>
                                <th data-column-id="TELEFONOCONTACTO_060" data-order="asc" data-align="left" data-header-align="left" data-width="15%">Tel. Contacto</th>
                                <th data-column-id="ACTIVO_060" data-order="asc" data-align="center" data-header-align="center" data-width="10%">Activo</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
							$mDatos = fxDevuelveProspectos(1);
							
							while ($Fila = $mDatos->fetch())
							{
								echo ("<tr>");
								echo ("<td>" . $Fila["PROSPECTO_REL"] . "</td>");
								echo ("<td>" . $Fila["NOMBRE_060"] . "</td>");
								$fecha = date_create_from_format('Y-m-d', $Fila["FECHAINGRESO_060"]);
								echo ("<td>" . date_format($fecha, 'd-m-Y') . "</td>");
								echo ("<td>" . $Fila["NOMBRECONTACTO_060"] . "</td>");
								echo ("<td>" . $Fila["TELEFONOCONTACTO_060"] . "</td>");
								echo ("<td>" . $Fila["ACTIVO_060"] . "</td>");
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
				$.redirect("catProspectos.php", "POST");
			});
      			
            $("#edit").on("click", function() {
				if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
				{
                    var codProspecto = $.trim($("#grid").bootgrid("getSelectedRows"));
                    $.redirect("catProspectos.php", {KDSA: codProspecto}, "POST");
                }
			});
			  
			$("#remove").on("click", function() {
				if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
				{
                    var codProspecto = $.trim($("#grid").bootgrid("getSelectedRows"));
                    $.redirect("gridProspectos.php", {KDSA: codProspecto}, "POST");
                }
			});

			$("#seguimiento").on("click", function() {
				if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
				{
                    var codProspecto = $.trim($("#grid").bootgrid("getSelectedRows"));
                    $.redirect("procSeguimiento.php", {mOperacion:2, mProspecto: codProspecto}, "POST");
                }
			});

			$("#proforma").on("click", function() {
				if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
				{
                    var codProspecto = $.trim($("#grid").bootgrid("getSelectedRows"));
                    $.redirect("procProformas.php", {mOperacion:2, mProspecto: codProspecto}, "POST");
                }
			});

			$("#agregar").on("click", function() {
				$.redirect("catProspectos.php", "POST");
			});
      			
            $("#modificar").on("click", function() {
				if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
				{
                    var codProspecto = $.trim($("#grid").bootgrid("getSelectedRows"));
                    $.redirect("catProspectos.php", {KDSA: codProspecto}, "POST");
                }
			});
			  
			$("#borrar").on("click", function() {
				if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
				{
                    var codProspecto = $.trim($("#grid").bootgrid("getSelectedRows"));
                    $.redirect("gridProspectos.php", {KDSA: codProspecto}, "POST");
                }
			});

			$("#seguir").on("click", function() {
				if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
				{
                    var codProspecto = $.trim($("#grid").bootgrid("getSelectedRows"));
                    $.redirect("procSeguimiento.php", {mOperacion:2, mProspecto: codProspecto}, "POST");
                }
			});

			$("#proformar").on("click", function() {
				if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
				{
                    var codProspecto = $.trim($("#grid").bootgrid("getSelectedRows"));
                    $.redirect("procProformas.php", {mOperacion:2, mProspecto: codProspecto}, "POST");
                }
			});
        });
    </script>
</body>
</html>
