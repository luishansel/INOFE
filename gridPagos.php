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
	require_once ("funciones/fxPagos.php");
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
		$PermisoUsuario = fxPermisoUsuario("procPagosEstudiantes", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
				$msPago = $_POST["KDSA"];
				fxAnularPagos($msPago);
				fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA040A", $msPago, "", "Anular");
			}
			
			if (isset($_POST["mnOpcion"]) and isset($_POST["mnAnno"])){
				$mnOpcion = $_POST["mnOpcion"];
				$mnAnno = $_POST["mnAnno"];
			}
			else{
				$mnOpcion = 0;
				$mnAnno = 0;
			}
		?>
    	<div class="container">
        	<div id="DivContenido">
                
                <div class="row">
                    <div class="col-md-12">
                        <form id="gridEstudiante" name="gridEstudiante" action="gridPagos.php" method="post">
							<div class="easyui-tabs tabs-narrow" style="width:100%;height:auto">
								<div title="Estudiantes" style="padding:10px">
									<div class="row">
										<div class="col-md-12">
											<?php
												if ($mbAgregar == 1 or $Administrador == 1)
													echo('<button id="append" type="button" class="btn btn-warning">Agregar pago</button>');
												else
													echo('<button id="append" type="button" class="btn btn-warning" disabled>Agregar pago</button>');
											?>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<table id="estudiantes" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true" style="font-size:small">
												<thead>
													<tr>
														<th data-column-id="MATRICULA_REL" data-identifier="true" data-align="left" data-header-align="left" data-width="10%">Matrícula</th>
														<th data-column-id="ESTUDIANTE" data-align="left" data-header-align="left" data-width="45%">Estudiante</th>
														<th data-column-id="CURSO" data-align="left" data-header-align="left" data-width="45%">Curso</th>
													</tr>
												</thead>
												<tbody>
												<?php
													$texto = "";
													$msConsulta = "select distinct KDSA030A.MATRICULA_REL, concat_ws(', ', trim(APELLIDOS_010), trim(NOMBRES_010)) as ESTUDIANTE, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as CURSO ";
													$msConsulta .= "from KDSA030A, KDSA020A, KDSA010A, KDSA051A where KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and ";
													$msConsulta .= "KDSA030A.MATRICULA_REL = KDSA051A.MATRICULA_REL and ANULADO_051 = 0 and EXONERADO_051 = 0 and PAGADO_051 = 0 and ESTADO_030 <> 4 order by ESTUDIANTE";
													$mEstudiantes = $m_cnx_MySQL->prepare($msConsulta);
													$mEstudiantes->execute();

													while ($Row = $mEstudiantes->fetch())
													{
														$texto .= '<tr>';
														$texto .= '<td>' . $Row["MATRICULA_REL"] . '</td>';
														$texto .= '<td>' . $Row["ESTUDIANTE"] . '</td>';
														$texto .= '<td>' . $Row["CURSO"] . '</td>';
														$texto .= '</tr>';
													}
													echo($texto);
												?>
											</table>
										</div>
									</div>
								</div>

								<div title="Pagos realizados" style="padding:10px">
									<div class="row">
										<div class="col-md-12">
											<?php
												if ($mbAnular == 1 or $Administrador == 1)
													echo('<button id="remove" type="button" class="btn btn-warning">Anular</button>');
												else
													echo('<button id="remove" type="button" class="btn btn-warning" disabled>Anular</button>');
													
												echo('<button id="view" type="button" class="btn btn-warning">Ver</button>');
												echo('<button id="print" type="button" class="btn btn-warning">Imprimir recibo</button>');
											?>

											<div style="float:right; margin-right:1%; display:inline-block">
												<?php
													if ($mnOpcion == 0)
														echo('<input type="radio" name="optFiltro" id="optFiltro1" onchange="fxCambiaOpcion()" checked>Filtrar por año &nbsp;');
													else
														echo('<input type="radio" name="optFiltro" id="optFiltro1" onchange="fxCambiaOpcion()">Filtrar por año &nbsp;');

													$msConsulta = "select distinct year(FECHA_040) as ANNO from KDSA040A order by year(FECHA_040) desc";
													$mDatos = $m_cnx_MySQL->prepare($msConsulta);
													$mDatos->execute();

													echo('<select style="background-color: white" id="cboAnno" name="cboAnno" onchange="fxCambiaOpcion()">');
													while ($mFila = $mDatos->fetch())
													{
														$Valor = trim($mFila["ANNO"]);
														$Texto = trim($mFila["ANNO"]);
														
														if ($mnAnno == 0)
														{
															$mnAnno = $Valor;
															echo("<option value='" . $Valor . "' selected>" . $Texto . "</option>");
														}
														else{
															if ($Valor == $mnAnno)
																echo("<option value='" . $Valor . "' selected>" . $Texto . "</option>");
															else
																echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
														}
														
													}
													echo('</select> &nbsp;');
													
													if ($mnOpcion == 1)
														echo('<input type="radio" name="optFiltro" id="optFiltro2" onchange="fxCambiaOpcion()" checked>Todos los registros');
													else
														echo('<input type="radio" name="optFiltro" id="optFiltro2" onchange="fxCambiaOpcion()">Todos los registros');
												?>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-12">
											<table id="grid" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true" style="font-size:small">
												<thead>
													<tr>
														<th data-column-id="PAGO_REL" data-identifier="true" data-align="left" data-header-align="left" data-width="10%">Pago</th>
														<th data-column-id="FECHA_040" data-align="left" data-header-align="left" data-width="8%">Fecha</th>
														<th data-column-id="RECIBO_040" data-align="left" data-header-align="left" data-width="7%">Recibo</th>
														<th data-column-id="NOMBRE_040" data-align="left" data-header-align="left">Nombre</th>
														<th data-column-id="CONCEPTO_040" data-align="left" data-header-align="left">Concepto</th>
														<th data-column-id="MONTO_040" data-align="right" data-header-align="right" data-width="10%">Monto</th>
														<th data-column-id="MONEDA_040" data-align="center" data-header-align="center" data-width="8%">Moneda</th>
														<th data-column-id="ANULADO_040" data-align="center" data-header-align="center" data-width="8%">Anulado</th>
													</tr>
												</thead>
												<tbody>
												<?php
													$texto = "";
													if ($mnOpcion == 0)
													{
														$msConsulta = "select PAGO_REL, FECHA_040, concat(SERIE_040, ' ', RECIBO_040) as RECIBO_040, NOMBRE_040, CONCEPTO_040, MONTO_040, (case MONEDA_040 when 0 then 'Córdobas' else 'Dólares' end) as MONEDA_040, ";
														$msConsulta .= "(case ANULADO_040 when 1 then 'x' else '' end) as ANULADO_040 from KDSA040A where OTROINGRESO_040 = 0 and year(FECHA_040) = ? order by PAGO_REL desc";
														$mPagos = $m_cnx_MySQL->prepare($msConsulta);
														$mPagos->execute([$mnAnno]);
													}
													else
													{
														$msConsulta = "select PAGO_REL, FECHA_040, concat(SERIE_040, ' ', RECIBO_040) as RECIBO_040, NOMBRE_040, CONCEPTO_040, MONTO_040, (case MONEDA_040 when 0 then 'Córdobas' else 'Dólares' end) as MONEDA_040, ";
														$msConsulta .= "(case ANULADO_040 when 1 then 'x' else '' end) as ANULADO_040 from KDSA040A where OTROINGRESO_040 = 0 order by PAGO_REL desc";
														$mPagos = $m_cnx_MySQL->prepare($msConsulta);
														$mPagos->execute();
													}

													while ($Fila = $mPagos->fetch())
													{
														$texto .= '<tr>';
														$texto .= '<td>' . $Fila["PAGO_REL"] . '</td>';
														$fecha = date_create_from_format('Y-m-d', $Fila["FECHA_040"]);
														$texto .= '<td>' . date_format($fecha, 'd-m-Y') . '</td>';
														$texto .= '<td>' . $Fila["RECIBO_040"] . '</td>';
														$texto .= '<td>' . $Fila["NOMBRE_040"] . '</td>';
														$texto .= '<td>' . $Fila["CONCEPTO_040"] . '</td>';
														$texto .= '<td>' . $Fila["MONTO_040"] . '</td>';
														$texto .= '<td>' . $Fila["MONEDA_040"] . '</td>';
														$texto .= '<td>' . $Fila["ANULADO_040"] . '</td>';
														$texto .= '</tr>';
													}
													echo($texto);
												?>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
                        </form>
                    </div>
                </div>
				
            </div>
    	</div>
<?php }} ?>
</body>
</html>

<script src="bootstrap/lib/jquery-1.11.1.min.js"></script>
<script src="bootstrap/js/bootstrap.js"></script>
<script src="bootstrap/dist/jquery.bootgrid.js"></script>
<script src="bootstrap/dist/jquery.bootgrid.fa.js"></script>
<script src="js/jquery.redirect.js"></script>
<script type='text/javascript'>
	$(function() {
		var msRecibo = "";

		function init(){
			$("#estudiantes").bootgrid({
				formatters: {
					"link": function(column, row) {
						return "<a href=\"#\">" + column.id + ": " + row.id + "</a>";
					}
				},
				rowCount: [-1, 10, 50, 75]
			});

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
			if ($.trim($("#estudiantes").bootgrid("getSelectedRows")) != "")
			{
				var msCodigo = $.trim($("#estudiantes").bootgrid("getSelectedRows"));
				$.redirect("procPagosEstudiantes.php", {mAccion: 0, mMatricula: msCodigo}, "POST");
			}
		});

		$("#view").on("click", function() {
			if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var msCodigo = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("procPagosEstudiantes.php", {mAccion: 1, mCodigo: msCodigo}, "POST");
			}
		});

		$("#remove").on("click", function() {
			if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var msCodigo = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("gridPagos.php", {KDSA: msCodigo}, "POST");
			}
		});

		$("#grid").bootgrid().on("selected.rs.jquery.bootgrid", function(e, rows)
		{
			msRecibo = rows[0]['RECIBO_040'];
		})

		$("#print").on("click", function() {
			if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var msCodigo = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("repRecibo.php", {KDSA: msCodigo}, "POST", "_blank");
			}
		});
	});

	function fxCambiaOpcion(){
		var mnOpcion;
		var mnAnno;

		if (document.getElementById("optFiltro1").checked == true){
			mnOpcion = 0;
			mnAnno = $("#cboAnno").val();
		}
		else{
			mnOpcion = 1;
			mnAnno = 0;
		}

		$.redirect("gridPagos.php", {mnOpcion: mnOpcion, mnAnno: mnAnno}, "POST");
	}
</script>