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
		$PermisoUsuario = fxPermisoUsuario("procCertificacion", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
                <div class = "row">
                    <div class="col-xs-12 col-md-11">
                        <div class="degradado"><strong>Control de certificación</strong></div>
                    </div>
                </div>

                <div id="lateral">
                    <?php
                        echo('<label id="registros" data-toggle="tooltip" data-placement="top" title="Agregar o Editar"><img src="imagenes/btnLateralEditar.png" height="80%" style="cursor:pointer" /></label>');
                        echo('<label id="imprimir" data-toggle="tooltip" data-placement="top" title="Imprimir certificados"><img src="imagenes/btnLateralImprimir.png" height="80%" style="cursor:pointer" /></label>');
                    ?>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button id="records" type="button" class="btn btn-warning" >Agregar o Editar</button>
                        <button id="print" type="button" class="btn btn-warning" >Imprimir certificados</button>
                        
                        <div id="tbRegulacion" class="easyui-tabs tabs-narrow" style="width:100%;height:auto">
                            <div title="Activos" style="padding:10px">
                                <table id="grid1" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true">
                                    <thead>
                                        <tr>
                                            <th data-column-id="CURSO_REL" data-identifier="true" data-align="left" data-order="desc" data-header-align="left" data-width="10%">Curso</th>
                                            <th data-column-id="TIPO_020" data-align="left" data-header-align="left" data-width="10%">Tipo</th>
                                            <th data-column-id="NOMBRE_020" data-align="left" data-header-align="left" data-width="58%">Nombre del Curso</th>
                                            <th data-column-id="ESTUDIANTES" data-align="center" data-header-align="center" data-width="11%">Estudiantes</th>
                                            <th data-column-id="CERTIFICADOS" data-align="center" data-header-align="center" data-width="11%">Certificados</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $msConsulta = "select CURSO_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020, TIPO_020, ";
                                        $msConsulta .= "(select count(MATRICULA_REL) from KDSA030A where KDSA020A.CURSO_REL = KDSA030A.CURSO_REL and KDSA030A.ESTADO_030 <> 4) as ESTUDIANTES, ";
                                        $msConsulta .= "(select count(MATRICULA_REL) from KDSA030A where KDSA020A.CURSO_REL = KDSA030A.CURSO_REL and KDSA030A.ESTADO_030 = 3) as CERTIFICADOS ";
                                        $msConsulta .= "from KDSA020A where ACTIVO_020 = 1 and FECHAINI_020 < CURDATE()";
                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
			                            $mDatos->execute();
                                        
                                        while ($Fila = $mDatos->fetch())
                                        {
                                            echo ("<tr>");
                                            echo ("<td>" . $Fila["CURSO_REL"] . "</td>");
                                            echo ("<td>" . Tipo($Fila["TIPO_020"]) . "</td>");
                                            echo ("<td>" . $Fila["NOMBRE_020"] . " (" . trim($Fila["CONVOCATORIA_020"]) . " / G" . trim($Fila["GRUPO_020"]) . ")</td>");
                                            echo ("<td>" . $Fila["ESTUDIANTES"] . "</td>");
                                            echo ("<td>" . $Fila["CERTIFICADOS"] . "</td>");
                                            echo ("</tr>");
                                        }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                            <div title="Inactivos" style="padding:10px">
                                <table id="grid2" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true">
                                    <thead>
                                        <tr>
                                            <th data-column-id="CURSO_REL" data-identifier="true" data-align="left" data-order="desc" data-header-align="left" data-width="10%">Curso</th>
                                            <th data-column-id="TIPO_020" data-align="left" data-header-align="left" data-width="10%">Tipo</th>
                                            <th data-column-id="NOMBRE_020" data-order="asc" data-align="left" data-header-align="left" data-width="58%">Nombre del Curso</th>
                                            <th data-column-id="ESTUDIANTES" data-align="center" data-header-align="center" data-width="11%">Estudiantes</th>
                                            <th data-column-id="CERTIFICADOS" data-align="center" data-header-align="center" data-width="11%">Certificados</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $msConsulta = "select KDSA020A.CURSO_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020, TIPO_020, ";
                                        $msConsulta .= "(select count(MATRICULA_REL) from KDSA030A where KDSA020A.CURSO_REL = KDSA030A.CURSO_REL and KDSA030A.ESTADO_030 <> 4) as ESTUDIANTES, ";
                                        $msConsulta .= "(select count(MATRICULA_REL) from KDSA030A where KDSA020A.CURSO_REL = KDSA030A.CURSO_REL and KDSA030A.ESTADO_030 = 3) as CERTIFICADOS ";
                                        $msConsulta .= "from KDSA020A, KDSA021A, KDSA140A where ACTIVO_020 = 0 and KDSA020A.CURSO_REL = KDSA021A.CURSO_REL and KDSA021A.MODULO_REL = KDSA140A.MODULO_REL";
                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
			                            $mDatos->execute();
                                        
                                        while ($Fila = $mDatos->fetch())
                                        {
                                            echo ("<tr>");
                                            echo ("<td>" . $Fila["CURSO_REL"] . "</td>");
                                            echo ("<td>" . Tipo($Fila["TIPO_020"]) . "</td>");
                                            echo ("<td>" . $Fila["NOMBRE_020"] . " (" . trim($Fila["CONVOCATORIA_020"]) . " / G" . trim($Fila["GRUPO_020"]) . ")</td>");
                                            echo ("<td>" . $Fila["ESTUDIANTES"] . "</td>");
                                            echo ("<td>" . $Fila["CERTIFICADOS"] . "</td>");
                                            echo ("</tr>");
                                        }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    	</div>
<?php }} ?>
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
            $("#grid1").bootgrid({
                formatters: {
                    "link": function(column, row) {
                        return "<a href=\"#\">" + column.id + ": " + row.id + "</a>";
                    }
                },
                rowCount: [-1, 10, 50, 75]
            });
        }

        init();

        $("#grid1").bootgrid().on("click.rs.jquery.bootgrid", function() {
            $("#grid2").bootgrid("deselect");
        });

        $("#records").on("click", function() {
            var codCurso = $("#grid1").bootgrid("getSelectedRows");
            if ($.trim($("#grid1").bootgrid("getSelectedRows")) != "")
                $.redirect("procCertificacion.php", {msCurso: codCurso[0]}, "POST");
        });

        $("#registros").on("click", function() {
            var codCurso = $("#grid1").bootgrid("getSelectedRows");
            if ($.trim($("#grid1").bootgrid("getSelectedRows")) != "")
                $.redirect("procCertificacion.php", {msCurso: codCurso[0]}, "POST");
        });

        $("#print").on("click", function() {
            var codCurso = $("#grid1").bootgrid("getSelectedRows");
            if ($.trim($("#grid1").bootgrid("getSelectedRows")) != "")
                $.redirect("repCertificado.php", {msCurso: codCurso[0]}, "POST", "_blank");
        });

        $("#imprimir").on("click", function() {
            var codCurso = $("#grid1").bootgrid("getSelectedRows");
            if ($.trim($("#grid1").bootgrid("getSelectedRows")) != "")
                $.redirect("repCertificado.php", {msCurso: codCurso[0]}, "POST", "_blank");
        });
    });

     $(function() {
        function init() {
            $("#grid2").bootgrid({
                formatters: {
                    "link": function(column, row) {
                        return "<a href=\"#\">" + column.id + ": " + row.id + "</a>";
                    }
                },
                rowCount: [-1, 10, 50, 75]
            });
        }

        init();

        $("#grid2").bootgrid().on("click.rs.jquery.bootgrid", function() {
            $("#grid1").bootgrid("deselect");
        });

        $("#records").on("click", function() {
            var codCurso = $("#grid2").bootgrid("getSelectedRows");
            if ($.trim($("#grid2").bootgrid("getSelectedRows")) != "")
                $.redirect("procCertificacion.php", {msCurso: codCurso[0]}, "POST");
        });

        $("#registros").on("click", function() {
            var codCurso = $("#grid2").bootgrid("getSelectedRows");
            if ($.trim($("#grid2").bootgrid("getSelectedRows")) != "")
                $.redirect("procCertificacion.php", {msCurso: codCurso[0]}, "POST");
        });

        $("#print").on("click", function() {
            var codCurso = $("#grid2").bootgrid("getSelectedRows");
            if ($.trim($("#grid2").bootgrid("getSelectedRows")) != "")
                $.redirect("repCertificado.php", {msCurso: codCurso[0]}, "POST", "_blank");
        });

        $("#imprimir").on("click", function() {
            var codCurso = $("#grid2").bootgrid("getSelectedRows");
            if ($.trim($("#grid2").bootgrid("getSelectedRows")) != "")
                $.redirect("repCertificado.php", {msCurso: codCurso[0]}, "POST", "_blank");
        });
    });
</script>
</body>
</html>
<?php
function Tipo($mnTipo)
{
	switch ($mnTipo)
	{
		case 0:
			$msResultado = "Seminario";
			break;
		case 1:
			$msResultado = "Curso";
			break;
		case 2:
			$msResultado = "Carrera";
			break;
		case 3:
			$msResultado = "Taller";
			break;
		case 4:
			$msResultado = "Diplomado";
			break;
		case 5:
			$msResultado = "Webinar";
			break;
		case 6:
			$msResultado = "Workshop";
			break;
		case 7:
			$msResultado = "Teambuilding";
	}

	return $msResultado;
}
?>