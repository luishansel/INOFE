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
		$PermisoUsuario = fxPermisoUsuario("procPagosInatec", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
                fxAnularPagosInatec($_POST["KDSA"]);
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
                    </div>
                 </div>
                 
                 <div class="row">
                    <div class="col-md-12">
                        <div class="form-group" style="margin-top:1%">
                            <label for="cboCobros" class="col-sm-12 col-md-2 col-form-label">Cobros disponibles</label>
                            <select class="form-control col-sm-12 col-md-7" id="cboCobros" name="cboCobros">
                                <?php
                                    $m_cnx_MySQL = fxAbrirConexion();
									$msConsulta = "select COBROINATEC_REL, DESC_054 from KDSA054A where PAGADO_054 = 0 and EXONERADO_054 = 0 and ANULADO_054 = 0";
                                    $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                            $mDatos->execute();
                                    
                                    while ($Fila = $mDatos->fetch())
                                    {
                                        $Cobro = rtrim($Fila["COBROINATEC_REL"]);
                                        $Texto = rtrim($Fila["DESC_054"]);
                                        
                                        echo("<option value='" . $Cobro . "'>" . $Texto . "</option>");
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                 </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <table id="grid" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true">
                            <thead>
                                <tr>
                                    <th data-column-id="PAGO_REL" data-identifier="true" data-align="left" data-header-align="left">Pago</th>
                                    <th data-column-id="FECHA_040" data-order="asc" data-align="left" data-header-align="left">Fecha</th>
                                    <th data-column-id="RECIBO_040" data-order="asc" data-align="left" data-header-align="left">Recibo</th>
                                    <th data-column-id="CONCEPTO_040" data-order="asc" data-align="left" data-header-align="left">Concepto</th>
                                    <th data-column-id="MONTO_040" data-order="asc" data-align="right" data-header-align="right">Monto</th>
                                    <th data-column-id="MONEDA_040" data-order="asc" data-align="center" data-header-align="center">Moneda</th>
                                    <th data-column-id="ANULADO_040" data-order="asc" data-align="center" data-header-align="center">Anulado</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                $mDatos = fxDevuelvePagosInatec();
                                
                                while ($Fila = $mDatos->fetch())
                                {
                                    echo ("<tr>");
                                    echo ("<td>" . $Fila["PAGO_REL"] . "</td>");
                                    $fecha = date_create_from_format('Y-m-d', $Fila["FECHA_040"]);
                                    echo ("<td>" . date_format($fecha, 'd-m-Y') . "</td>");
                                    echo ("<td>" . $Fila["RECIBO_040"] . "</td>");
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
                var msCodigo = document.getElementById("cboCobros").value;
                $.redirect("procPagosInatec.php", {mAccion: 0, mCobro: msCodigo}, "POST");
			});
			
			$("#remove").on("click", function() {
                if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
                {
                    var msCodigo = $.trim($("#grid").bootgrid("getSelectedRows"));
                    $.redirect("gridPagosInatec.php", {KDSA: msCodigo}, "POST");
                }
			});
      			
            $("#view").on("click", function() {
				if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
                {
                    var msCodigo = $.trim($("#grid").bootgrid("getSelectedRows"));
                    $.redirect("procPagosInatec.php", {mAccion: 0, mCobro: msCodigo}, "POST");
                }
            });
        });
    </script>
</body>
</html>
