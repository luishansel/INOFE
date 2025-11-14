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
	require_once ("funciones/fxCobrosIndividuales.php");
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
		$PermisoUsuario = fxPermisoUsuario("procCobroIndividual", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
			if (isset($_POST["cboAnno"]))
				$mnAnno = $_POST["cboAnno"];
			else
				$mnAnno = date('Y');
			
			if (isset($_POST["cboEstudiante"]))
				$codMatricula = $_POST["cboEstudiante"];
			else
				$codMatricula = "";
				
			if (isset($_POST["cboCobro"]))
				$codCobro = $_POST["cboCobro"];
			else
				$codCobro = "";

			if (isset($_POST["mAccion"]))
            {
				$codCobro = $_POST["mCobro"];
				$codMatricula = $_POST["mMatricula"];
				$Accion = $_POST["mAccion"];

				switch($Accion)
				{
					case 0:
						fxGuardarCobroIndividual ($codCobro, $codMatricula);
						fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA051A", $codCobro, $codMatricula, "Agregar");
						break;
						
					case 1:
						fxExonerarCobroIndividual ($codCobro, $codMatricula);
						fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA051A", $codCobro, $codMatricula, "Exonerar");
						break;
						
					case 2:
						fxAnularCobroIndividual ($codCobro, $codMatricula);
						fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA051A", $codCobro, $codMatricula, "Anular");
						break;
				}
            }
		?>
		<style>
			.bootgrid-table td {
				white-space: normal; /* Allows text to wrap */
				word-wrap: break-word; /* Breaks long words if necessary */
			}
		</style>
    	<div class="container">
        	<div id="DivContenido">
			<div class = "row">
				<div class="col-xs-12 col-md-11">
					<div class="degradado"><strong>Cobros individuales</strong></div>
				</div>
			</div>

        	<div class="row">
            	<div class="col-md-12">
					<?php
						if ($mbAgregar == 1 or $Administrador == 1)
							echo('<button id="append" type="button" class="btn btn-warning">Agregar</button>');
						else
							echo('<button id="append" type="button" class="btn btn-warning" disabled>Agregar</button>');

						if ($mbModificar == 1 or $Administrador == 1)
							echo('<button id="edit" type="button" class="btn btn-warning">Exonerar</button>');
						else
							echo('<button id="edit" type="button" class="btn btn-warning" disabled>Exonerar</button>');

						if ($mbAnular == 1 or $Administrador == 1)
							echo('<button id="remove" type="button" class="btn btn-warning">Anular</button>');
						else
							echo('<button id="remove" type="button" class="btn btn-warning" disabled>Anular</button>');
					?>
                </div>
            </div>
            <div class="row">
            	<div class="col-md-12">
                    <form id="gridCobros" name="gridCobros" action="procCobrosIndividuales.php" method="post">
                        <div class="form-group row" style="margin-top:1%">
							<div class="col-md-12">
								<label for="cboAnno" class="col-sm-12 col-md-2 col-form-label">Año</label>
								<select class="form-control col-sm-12 col-md-2" id="cboAnno" name="cboAnno" onchange="this.form.submit()">
									<?php
										$msConsulta = "select distinct year(FECHA_030) as ANNO from KDSA030A order by year(FECHA_030) desc";
										$mDatos = $m_cnx_MySQL->prepare($msConsulta);
										$mDatos->execute();
										
										while ($mFila = $mDatos->fetch())
										{
											$Valor = trim($mFila["ANNO"]);
											$Texto = trim($mFila["ANNO"]);

											if ($Valor == $mnAnno)
												echo("<option value='" . $Valor . "' selected>" . $Texto . "</option>");
											else
												echo("<option value='" . $Valor . "'>" . $Texto . "</option>");										
										}
									?>
								</select>
							</div>
                        </div>

						<div class="form-group row" style="margin-top:1%">
							<div class="col-md-12">
								<label for="cboEstudiante" class="col-sm-12 col-md-2 col-form-label">Estudiante</label>
								<select class="form-control col-sm-12 col-md-7" id="cboEstudiante" name="cboEstudiante" onchange="this.form.submit()">
									<?php
										$msConsulta = "select MATRICULA_REL, APELLIDOS_010, NOMBRES_010, NOMBRE_020, CONVOCATORIA_020, GRUPO_020 from KDSA030A, KDSA010A, KDSA020A ";
										$msConsulta .= "where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and ESTADO_030 <> 4 and year(FECHA_030) = ? ";
										$msConsulta .= "order by APELLIDOS_010, NOMBRES_010";
										$mDatos = $m_cnx_MySQL->prepare($msConsulta);
										$mDatos->execute([$mnAnno]);
										
										while ($Fila = $mDatos->fetch())
										{
											$msNombreCurso = trim($Fila["NOMBRE_020"]) . " " . trim($Fila["CONVOCATORIA_020"]) . " / G" . trim($Fila["GRUPO_020"]);
											$Matricula = trim($Fila["MATRICULA_REL"]);
											$Texto = trim($Fila["APELLIDOS_010"]) . ", " . trim($Fila["NOMBRES_010"]) . " (" . trim($Fila["NOMBRE_020"]) . " " . trim($Fila["CONVOCATORIA_020"]) . " / G" . trim($Fila["GRUPO_020"]) . ")";
											
											if ($codMatricula == "")
												$codMatricula = $Matricula;
											
											if ($codMatricula == $Matricula)
												echo("<option value='" . $Matricula . "' selected>" . $Texto . "</option>");
											else
												echo("<option value='" . $Matricula . "'>" . $Texto . "</option>");
										}
									?>
								</select>
							</div>
                        </div>

                        <div class="form-group row">
							<div class="col-md-12">
								<label for="cboCobro" class="col-sm-12 col-md-2 col-form-label">Cobros</label>
								<select class="form-control col-sm-12 col-md-7" id="cboCobro" name="cboCobro">
									<?php
										$msConsulta = "select COBRO_REL, CONCEPTO_050, ACTIVO_050, ANULADO_050 from KDSA050A, KDSA030A where KDSA050A.CURSO_REL = KDSA030A.CURSO_REL and ESTADO_030 <> 4 and TIPO_050 in (0, 2, 5, 6) and KDSA030A.MATRICULA_REL = ? order by COBRO_REL desc";
										$mDatos = $m_cnx_MySQL->prepare($msConsulta);
										$mDatos->execute([$codMatricula]);

										while ($Fila = $mDatos->fetch())
										{
											$Cobro = rtrim($Fila["COBRO_REL"]);
											$Texto = rtrim($Fila["CONCEPTO_050"]);
											$Activo = rtrim($Fila["ACTIVO_050"]);
											$Anulado = rtrim($Fila["ANULADO_050"]);
											
											if ($codCobro == "")
											{
												if ($Activo == 1 and $Anulado == 0)
													echo("<option value='" . $Cobro . "'>" . $Texto . "</option>");
											}
											else
											{
												if ($Activo == 1 and $Anulado == 0)
												{
													if ($codCobro == $Cobro)
														echo("<option value='" . $Cobro . "' selected>" . $Texto . "</option>");
													else
														echo("<option value='" . $Cobro . "'>" . $Texto . "</option>");
												}
											}
										}
									?>
								</select>
							</div>
                        </div>

						<div class="form-group row">
							<div class="col-md-12">
								<label for="txtNomCurso" class="col-sm-12 col-md-2 col-form-label">Curso</label>
								<?php 
									echo('<input type="text" class="form-control col-sm-12 col-md-7" id="txtNomCurso" name="txtNomCurso" value="' . $msNombreCurso . '" readonly />');
								?>
							</div>
						</div>
                    </form>
                    <table id="grid" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true" style="font-size:small">
                    	<thead>
                            <tr>
                                <th data-column-id="COBRO_REL" data-identifier="true" data-align="left" data-header-align="left" data-width="10%">Cobro</th>
                                <th data-column-id="CONCEPTO_050" data-order="asc" data-align="left" data-header-align="left" data-width="40%">Concepto</th>
                                <th data-column-id="MONTO" data-order="asc" data-align="right" data-header-align="right" data-width="10%">Monto</th>
								<th data-column-id="ADEUDADO_051" data-order="asc" data-align="right" data-header-align="right" data-width="10%">Deuda</th>
                                <th data-column-id="PAGADO_051" data-order="asc" data-align="center" data-header-align="center" data-width="10%">Pagado</th>
                                <th data-column-id="EXONERADO_051" data-order="asc" data-align="center" data-header-align="center" data-width="10%">Exonerado</th>
                                <th data-column-id="ANULADO_051" data-order="asc" data-align="center" data-header-align="center" data-width="10%">Anulado</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
							$mDatos = fxDevuelveCobroIndividual($codMatricula);
							
							while ($Fila = $mDatos->fetch())
							{
								echo ("<tr>");
								echo ("<td>" . $Fila["COBRO_REL"] . "</td>");
								echo ("<td>" . $Fila["CONCEPTO_050"] . "</td>");
								echo ("<td>" . $Fila["MONTO"] . "</td>");
								echo ("<td>" . $Fila["ADEUDADO_051"] . "</td>");
								echo ("<td>" . $Fila["PAGADO_051"] . "</td>");
								echo ("<td>" . $Fila["EXONERADO_051"] . "</td>");
								echo ("<td>" . $Fila["ANULADO_051"] . "</td>");
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

			$("#append").on("click", function() {
				var codCobro = $('#cboCobro option:selected').val();
				var codMatricula = $('#cboEstudiante option:selected').val();
				$.redirect("procCobrosIndividuales.php", {mCobro: codCobro, mMatricula: codMatricula, mAccion: 0}, "POST");
			});
			
            $("#edit").on("click", function() {
				var codCobro = $.trim($("#grid").bootgrid("getSelectedRows"));
				var codMatricula = $('#cboEstudiante option:selected').val();
				$.redirect("procCobrosIndividuales.php", {mCobro: codCobro, mMatricula: codMatricula, mAccion: 1}, "POST");
			});
						
			$("#remove").on("click", function() {
				var codCobro = $.trim($("#grid").bootgrid("getSelectedRows"));
				var codMatricula = $('#cboEstudiante option:selected').val();
				$.redirect("procCobrosIndividuales.php", {mCobro: codCobro, mMatricula: codMatricula, mAccion: 2}, "POST");
			});
        });
    </script>
</body>
</html>