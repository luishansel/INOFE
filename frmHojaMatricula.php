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
	require_once ("funciones/fxEstudiantes.php");
    $m_cnx_MySQL = fxAbrirConexion();
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
		$PermisoUsuario = fxPermisoUsuario("repHojaMatricula", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
			if (isset($_POST["cboEstudiante"]))
				$codEstudiante = $_POST["cboEstudiante"];
			else
				$codEstudiante = "";
		?>
    	<div class="container">
        	<div id="DivContenido">
                <div class = "row">
                    <div class="col-xs-12 col-md-11">
                        <div class="degradado"><strong>Hoja de matrícula</strong></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-5 col-md-5">
                        <button id="print" type="button" class="btn btn-warning">Imprimir</button>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-xs-12 col-md-12">
                        <form id="gridReporte" name="gridReporte" action="frmHojaMatricula.php" method="post">
                            <div style="margin-top:1%">
                                <label for="cboEstudiante" class="col-sm-12 col-md-2 col-form-label">Estudiante</label>
                                <select class="col-sm-12 col-md-5 form-control" id="cboEstudiante" name="cboEstudiante" onchange="this.form.submit()">
                                    <?php
										$msConsulta = "select distinct KDSA030A.ESTUDIANTE_REL, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE from KDSA030A, KDSA010A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL order by concat(APELLIDOS_010, ', ', NOMBRES_010)";
                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
			                            $mDatos->execute();
                                        
                                        while ($Fila = $mDatos->fetch())
                                        {
                                            $Estudiante = rtrim($Fila["ESTUDIANTE_REL"]);
                                            $Texto = rtrim($Fila["ESTUDIANTE"]);
                                            
                                            if ($codEstudiante == "")
                                                $codEstudiante = $Estudiante;

                                            if ($codEstudiante == $Estudiante)
                                                echo("<option value='" . $Estudiante . "' selected>" . $Texto . "</option>");
                                            else
                                                echo("<option value='" . $Estudiante . "'>" . $Texto . "</option>");
                                        }
                                    ?>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                	<div class="col-md-12">
                        <table id="grid" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true">
                            <thead>
                                <tr>
                                    <th data-column-id="MATRICULA_REL" data-identifier="true" data-align="left" data-header-align="left">Matrícula</th>
                                    <th data-column-id="NOMBRE_020" data-order="asc" data-align="left" data-header-align="left">Curso matriculado</th>
                                    <th data-column-id="FECHA_030" data-order="asc" data-align="center" data-header-align="center">Fecha</th>
                                    <th data-column-id="ESTADO_030" data-order="asc" data-align="center" data-header-align="center">Anulado</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                $msConsulta = "select MATRICULA_REL, NOMBRE_020, FECHA_030, (case ESTADO_030 when 0 then 'Activo' when 1 then 'Inactivo' when 2 then 'Deserción' when 3 then 'Certificado' else 'Anulado' end) as ESTADO_030 from KDSA030A, KDSA020A where KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and ESTUDIANTE_REL = ?";
                                $mDatos = $m_cnx_MySQL->prepare($msConsulta);
                                $mDatos->execute([$codEstudiante]);
                                
                                while ($Fila = $mDatos->fetch())
                                {
                                    echo ("<tr>");
                                    echo ("<td>" . $Fila["MATRICULA_REL"] . "</td>");
                                    echo ("<td>" . $Fila["NOMBRE_020"] . "</td>");
                                    $fecha = date_create_from_format('Y-m-d', $Fila["FECHA_030"]);
                                    echo ("<td>" . date_format($fecha, 'd-m-Y') . "</td>");
                                    echo ("<td>" . $Fila["ESTADO_030"] . "</td>");
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

			$("#print").on("click", function() {
                if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
                {
                    var codMatricula = $.trim($("#grid").bootgrid("getSelectedRows"));
                    $.redirect("repHojaMatricula.php", {KDSA: codMatricula}, "POST", "_blank");
                }
			});
        });
    </script>
</body>
</html>