<?php
session_start();

if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1) {
	echo ('<meta http-equiv="Refresh" content="0;url=index.php"/>');
	exit('');
}

include("MasterWeb.php");
require_once("funciones/fxGeneral.php");
require_once("funciones/fxUsuarios.php");
require_once("funciones/fxCobros.php");
require_once("funciones/fxCursos.php");
$m_cnx_MySQL = fxAbrirConexion();
$Registro = fxVerificaUsuario();

if ($Registro == 0) {
?>

	<div class="container text-center">
		<div id="DivContenido">
			<img src="imagenes/errordeacceso.png" />
		</div>
	</div>
	<?php } else {
	$Administrador = fxVerificaAdministrador();
	$PermisoUsuario = fxPermisoUsuario("catCobros");

	if ($Administrador == 0 and $PermisoUsuario == 0) { ?>
		<div class="container text-center">
			<div id="DivContenido">
				<img src="imagenes/errordeacceso.png" />
			</div>
		</div>
		<?php } else {
		if (isset($_POST["Guardar"])) {
			$Codigo = $_POST["txtCodCobro"];
			$Curso = $_POST["cboCurso"];
			$Fecha = $_POST["dtpFecha"];
			$Concepto = $_POST["txtConcepto"];
			$Monto = floatval($_POST["txnMonto"]);
			$Tipo = $_POST["optTipo"];
			$Moneda = $_POST["optMoneda"];

			if ($Tipo == 1) //Moratorio
				$Cobro = $_POST["cboCobro"];
			else
				$Cobro = "";
			$Activo = $_POST["optActivo"];

			if ($Codigo == "") {
				$Codigo = fxGuardarCobros($Curso, $Fecha, $Concepto, $Monto, $Moneda, $Tipo, $Activo, $Cobro);
				fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA050A", $Codigo, "", "Agregar");

				if ($Tipo == 0 or $Tipo == 6) {
					//Ingresa automáticamente un Cobro por Mora cuando el Tipo es Cuota
					$NvaFecha = date("Y-m-d", strtotime($Fecha . "+1 days"));
					$msConsulta = "select MORA_020, CUOTA_020 from KDSA020A where CURSO_REL = ?";
					$mDatos = $m_cnx_MySQL->prepare($msConsulta);
					$mDatos->execute([$Curso]);
					$Fila = $mDatos->fetch();
					$Mora = $Fila["MORA_020"];
					$Cuota = $Fila["CUOTA_020"];
					$NvoMonto = $Cuota * ($Mora / 100);
					$NvoConcepto = "Cobro por Mora de " . $Concepto;
					$CodigoMora = fxGuardarCobros($Curso, $NvaFecha, $NvoConcepto, $NvoMonto, $Moneda, 1, 1, $Codigo);
					fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA050A", $CodigoMora, "", "Agregar");
				}
			} else {
				if ($Tipo == 1)
					fxModificarCobros($Codigo, $Curso, $Fecha, $Concepto, $Monto, $Moneda, $Tipo, $Activo, $Cobro);
				else
					fxModificarCobros($Codigo, $Curso, $Fecha, $Concepto, $Monto, $Moneda, $Tipo, $Activo);

				fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA050A", $Codigo, "", "Modificar");
			}
		?>
			<meta http-equiv="Refresh" content="0;url=gridCobros.php" />
		<?php
		} else {
			if (isset($_POST["KDSA"]))
				$Codigo = $_POST["KDSA"];
			else
				$Codigo = "";
			
			if ($Codigo != "")
			{
				$RecordSet = fxDevuelveCobros(0, $Codigo);
				$Fila = $RecordSet->fetch();
				$Curso = $Fila["CURSO_REL"];
				$Fecha = $Fila["FECHAPREVISTA_050"];
				$Concepto = $Fila["CONCEPTO_050"];
				$Monto = $Fila["MONTO_050"];
				$Moneda = $Fila["MONEDA_050"];
				$Tipo = $Fila["TIPO_050"];
				$Activo = $Fila["ACTIVO_050"];
				$Cobro = $Fila["KDS_COBRO_REL"];
				$Anulado = $Fila["ANULADO_050"];
			}
			else
			{
				$Curso = "";
				$Fecha = "";
				$Concepto = "";
				$Monto = 0;
				$Moneda = 0;
				$Tipo = 0;
				$Activo = 0;
				$Cobro = "";
				$Anulado = 0;
			}
		?>
			<div class="container text-left">
				<div id="DivContenido">
					<div class = "row">
						<div class="col-xs-12 col-md-11">
							<div class="degradado"><strong>Catálogo de cobros</strong></div>
						</div>
					</div>

					<div class="row">
						<div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-2">
							<form id="catCobros" name="catCobros" action="catCobros.php" method="post" onsubmit="return verificarFormulario()">
								<div class="form-group row">
									<label for="txtCodCobro" class="col-sm-12 col-md-2 col-form-label">Código del Cobro</label>
									<div class="col-sm-12 col-md-3">
										<?php echo ('<input type="text" class="form-control" id="txtCodCobro" name="txtCodCobro" value="' . $Codigo . '" readonly />'); ?>
									</div>
									<div class="col-auto">
									</div>
								</div>

								<div class="form-group row">
									<label for="cboCurso" class="col-sm-12 col-md-2 col-form-label">Curso</label>
									<div class="col-sm-12 col-md-7">
										<select class="form-control" id="cboCurso" name="cboCurso" onchange="escribeMora()">
											<?php
											$mDatos = fxDevuelveCursos(1);
											while ($Fila = $mDatos->fetch()) {
												$Valor = rtrim($Fila["CURSO_REL"]);
												$Texto = rtrim($Fila["NOMBRE_020"]);
												$CursoAct = $Fila["ACTIVO_020"];
												if ($Codigo == "") {
													if ($CursoAct == "x")
														echo ("<option value='" . $Valor . "'>" . $Texto . "</option>");
												} else {
													if ($Curso == "") {
														echo ("<option value='" . $Valor . "'>" . $Texto . "</option>");
														$Curso = $Valor;
													} else {
														if ($Curso == $Valor)
															echo ("<option value='" . $Valor . "' selected='selected'>" . $Texto . "</option>");
														else
															echo ("<option value='" . $Valor . "'>" . $Texto . "</option>");
													}
												}
											}
											?>
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label for="dtpFecha" class="col-sm-12 col-md-2 col-form-label">Fecha prevista</label>
									<div class="col-sm-12 col-md-3">
										<?php
										if ($Codigo == "")
											echo ('<input type="date" class="form-control" id="dtpFecha" name="dtpFecha" value="' . date("Y-m-d") . '" />');
										else
											echo ('<input type="date" class="form-control" id="dtpFecha" name="dtpFecha" value="' . $Fecha . '" />');
										?>
									</div>
									<div class="col-auto">
									</div>
								</div>

								<div class="form-group row">
									<label for="txtConcepto" class="col-sm-12 col-md-2 col-form-label">Concepto</label>
									<div class="col-sm-12 col-md-7">
										<?php echo ('<input type="text" class="form-control" id="txtConcepto" name="txtConcepto" value="' . $Concepto . '" />'); ?>
									</div>
									<div class="col-auto">
									</div>
								</div>

								<div class="form-group row">
									<label for="txnMonto" class="col-sm-12 col-md-2 col-form-label">Monto del cobro</label>
									<div class="col-sm-12 col-md-3">
										<?php
										if ($Codigo == "")
											echo ('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnMonto" name="txnMonto" value="0" />');
										else
											echo ('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnMonto" name="txnMonto" value="' . $Monto . '" />');
										?>
									</div>
									<div class="col-auto">
									</div>
								</div>

								<div class="form-group row">
									<label for="optMoneda" class="col-sm-12 col-md-2 form-label">Moneda</label>
									<div class="col-sm-12 col-md-4">
										<div class="radio">
											<?php
											if ($Moneda == 0 or $Codigo == "")
												echo ('<input type="radio" id="optMoneda1" name="optMoneda" value="0" checked /> Córdobas ');
											else
												echo ('<input type="radio" id="optMoneda1" name="optMoneda" value="0" /> Córdobas ');
												
											if ($Moneda == 1)
												echo ('<input type="radio" id="optMoneda2" name="optMoneda" value="1" checked /> Dólares');
											else
												echo ('<input type="radio" id="optMoneda2" name="optMoneda" value="1" /> Dólares');
											?>
										</div>
									</div>
								</div>

								<div class="form-group row">
									<label for="optTipo" class="col-sm-12 col-md-2 form-label">Tipo</label>
									<div class="col-sm-12 col-md-8">
										<div class="radio">
											<?php
											if ($Tipo == 0)
												echo ('<input type="radio" id="optTipo1" name="optTipo" value="0" onchange="activaCombo()" checked /> Cuota ');
											else
												echo ('<input type="radio" id="optTipo1" name="optTipo" value="0" onchange="activaCombo()" /> Cuota ');

											if ($Tipo == 1)
												echo ('<input type="radio" id="optTipo2" name="optTipo" value="1" onchange="activaCombo()" checked /> Moratorio ');
											else
												echo ('<input type="radio" id="optTipo2" name="optTipo" value="1" onchange="activaCombo()" /> Moratorio ');

											if ($Tipo == 2)
												echo ('<input type="radio" id="optTipo3" name="optTipo" value="2" onchange="activaCombo()" checked /> Matrícula ');
											else
												echo ('<input type="radio" id="optTipo3" name="optTipo" value="2" onchange="activaCombo()" /> Matrícula ');

											if ($Tipo == 3)
												echo ('<input type="radio" id="optTipo4" name="optTipo" value="3" onchange="activaCombo()" checked /> Empresarial ');
											else
												echo ('<input type="radio" id="optTipo4" name="optTipo" value="3" onchange="activaCombo()" /> Empresarial ');

											if ($Tipo == 4)
												echo ('<input type="radio" id="optTipo5" name="optTipo" value="4" onchange="activaCombo()" checked /> INATEC ');
											else
												echo ('<input type="radio" id="optTipo5" name="optTipo" value="4" onchange="activaCombo()" /> INATEC ');

											if ($Tipo == 5)
												echo ('<input type="radio" id="optTipo6" name="optTipo" value="5" onchange="activaCombo()" checked /> Certificado ');
											else
												echo ('<input type="radio" id="optTipo6" name="optTipo" value="5" onchange="activaCombo()" /> Certificado ');

											if ($Tipo == 6)
												echo ('<input type="radio" id="optTipo7" name="optTipo" value="6" onchange="activaCombo()" checked /> Cuota especial');
											else
												echo ('<input type="radio" id="optTipo7" name="optTipo" value="6" onchange="activaCombo()" /> Cuota especial');

											?>
										</div>
									</div>
								</div>

								<?php
								if ($Codigo == "") {
									echo ('<div id="divMora" hidden="true">');
									echo ('<div class="form-group row" >');
								} else {
									if ($Tipo == 1) {
										echo ('<div id="divMora">');
										echo ('<div class="form-group row">');
									} else {
										echo ('<div id="divMora" hidden="true">');
										echo ('<div class="form-group row">');
									}
								}
								?>
								<label for="cboCobro" class="col-sm-12 col-md-2 col-form-label">Cuota asociada a la Mora</label>
								<div class="col-sm-12 col-md-7">
									<?php
									echo ('<select class="form-control" id="cboCobro" name="cboCobro">');

									if ($Codigo == "")
										$msConsulta = "select COBRO_REL, CONCEPTO_050 from KDSA050A where TIPO_050 in (0, 6) and ACTIVO_050 = 1 and ANULADO_050 = 0 and CURSO_REL = ?";
									else
										$msConsulta = "select COBRO_REL, CONCEPTO_050 from KDSA050A where TIPO_050 in (0, 6) and CURSO_REL = ?";

									$mDatos = $m_cnx_MySQL->prepare($msConsulta);
									$mDatos->execute([$Curso]);

									while ($Fila = $mDatos->fetch()) {
										$Valor = rtrim($Fila["COBRO_REL"]);
										$Texto = rtrim($Fila["CONCEPTO_050"]);

										if ($Codigo == "") {
											echo ("<option value='" . $Valor . "'>" . $Texto . "</option>");
										} else {
											if ($Cobro == $Valor)
												echo ("<option value='" . $Valor . "' selected='selected'>" . $Texto . "</option>");
											else
												echo ("<option value='" . $Valor . "'>" . $Texto . "</option>");
										}
									}
									echo ('</select>');
									?>
								</div>
							</div>
						</div>

						<div class="form-group row">
							<label for="optActivo" class="col-sm-12 col-md-2 form-label">Activo</label>
							<div class="col-sm-12 col-md-4">
								<div class="radio">
									<?php
									if ($Activo == 1 or $Codigo == "") {
										echo ('<input type="radio" id="OptAct1" name="optActivo" value="0" /> No <input type="radio" id="OptAct2" name="optActivo" value="1" checked="checked" /> Si');
									} else {
										echo ('<input type="radio" id="OptAct1" name="optActivo" value="0" checked="checked" /> No <input type="radio" id="OptAct2" name="optActivo" value="1" /> Si');
									}
									?>
								</div>
							</div>
						</div>

						<div class="form-group row">
							<label for="optAnulado" class="col-sm-12 col-md-2 form-label">Anulado</label>
							<div class="col-sm-12 col-md-4">
								<div class="radio">
									<?php
									if ($Anulado == 1) {
										echo ('<input type="radio" id="Opcion1" name="optAnulado" value="0" disabled /> No <input type="radio" id="Opcion2" name="optAnulado" value="1" checked="checked" disabled /> Si');
									} else {
										echo ('<input type="radio" id="Opcion1" name="optAnulado" value="0" checked="checked" disabled /> No <input type="radio" id="Opcion2" name="optAnulado" value="1" disabled /> Si');
									}
									?>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-auto col-xs-offset-none col-md-12 col-md-offset-2">
								<?php
								if ($Anulado == 1)
									echo ('<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" disabled />');
								else
									echo ('<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />');
								?>
								<input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridCobros.php';" />
							</div>
						</div>
					</form>
				</div>
	<?php	}
	}
}
	?>
			</div>
		</div>
	</div>
</body>
</html>

			<script>
				function verificarFormulario() {
					if (document.getElementById('txtConcepto').value == "") {
						$.messager.alert('INOFE', 'Falta el Concepto del cobro.', 'warning');
						return false;
					}

					if (document.getElementById('txnMonto').value <= 0) {
						$.messager.alert('INOFE', 'Falta el Monto del cobro.', 'warning');
						return false;
					}

					return true;
				}

				function escribeMora() {
					var datos = new FormData();
					var cobro = $('#txtCodCobro').val();
					var curso = $('#cboCurso').val();
					var mora = $('#cboCobro').val();
					datos.append('CodCobro', cobro);
					datos.append('CodCurso', curso);
					datos.append('CodMora', mora);

		$.ajax({
			url: 'funciones/fxCobrosDivMora.php',
			type: 'post',
			data: datos,
			contentType: false,
			processData: false,
			success: function(response) {
				if (response != 0) {
					document.getElementById('divMora').innerHTML = response;
				}
			}
		});
		return false;
	}

	function activaCombo() {
		if (document.getElementById('optTipo2').checked) {
			document.getElementById('divMora').hidden = false;
			escribeMora();
		} else {
			document.getElementById('divMora').hidden = true;
		}
		//cambiaMoneda();
	}

	function cambiaMoneda() {
		if (document.getElementById('optTipo5').checked)
			document.getElementById('optMoneda1').checked = true;
		else
			document.getElementById('optMoneda2').checked = true;
	}
</script>