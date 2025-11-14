<?php
	session_start();
	if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
	{
		echo('<meta http-equiv="Refresh" content="0;url=index.php"/>');
		exit('');
	}
	
	include ("MasterWeb.php");
	require_once ("funciones/fxGeneral.php");
	require_once ("funciones/fxUsuarios.php");
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
		$PermisoUsuario = fxPermisoUsuario("repCtasPorCobrar", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
		if ($Administrador == 0 and $PermisoUsuario == 0)
		{?>
        <div class="container text-center">
            <div id="DivContenido">
                <img src="imagenes/errordeacceso.png"/>
            </div>
        </div>
		<?php }
		else
			{
				$FechaFin = date("Y-m-d", time());
			?>
			<div class="container">
            <div id="DivContenido">
			<div class = "row">
				<div class="col-xs-12 col-md-11">
					<div class="degradado"><strong>Cuentas por cobrar</strong></div>
				</div>
			</div>
			
            <div class="row">
            	<div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-1">
                    <form id="frmCtasPorCobrar" name="frmCtasPorCobrar">
						<div class = "form-group row">
                            <label for="optTipo" class="col-sm-12 col-md-2 form-label"></label>
                            <div class="col-sm-12 col-md-7">
                                <div class = "radio">
 									<input type="radio" id="optTipo1" name="optTipo" value="0" onchange="activaCombo()" checked /> General &ensp; <input type="radio" id="optTipo2" name="optTipo" value="1" onchange="activaCombo()" /> Todos los Cursos activos &ensp; <input type="radio" id="optTipo3" name="optTipo" value="2" onchange="activaCombo()" /> Un Curso activo &ensp; <input type="radio" id="optTipo4" name="optTipo" value="3" onchange="activaCombo()" /> Un Curso inactivo
                                </div>
                            </div>
                        </div>

                        <div class = "form-group row">
							<label for="dtpFechaFin" class="col-sm-12 col-md-2 col-form-label">Hasta la fecha</label>
                            <div class="col-sm-12 col-md-3">
                            	<?php echo('<input type="date" class="form-control" id="dtpFechaFin" name="dtpFechaFin" value="' . $FechaFin . '" />');?>
                            </div>
                        </div>

						<div id="divCursoActivo" hidden = "true">
							<div class = "form-group row">
								<label for="cboCursoActivo" class="col-sm-12 col-md-2 col-form-label">Curso</label>
								<div class="col-sm-12 col-md-10">
								<select class="col-sm-12 col-md-5 form-control" id="cboCursoActivo" name="cboCursoActivo">
									<?php
										$msConsulta = "select distinct KDSA020A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE ";
										$msConsulta .= "from KDSA051A, KDSA030A, KDSA020A ";
										$msConsulta .= "where PAGADO_051 = 0 and EXONERADO_051 = 0 and ANULADO_051 = 0 and KDSA051A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA030A.CURSO_REL = KDSA020A.CURSO_REL ";
										$msConsulta .= "and ACTIVO_020 = 1 order by KDSA020A.CURSO_REL desc";
										$m_cnx_MySQL = fxAbrirConexion();
                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
			                            $mDatos->execute();
										$mbPrimeraLinea = true;
										
										while ($Fila = $mDatos->fetch())
										{
											$Curso = rtrim($Fila["CURSO_REL"]);
											$Texto = rtrim($Fila["NOMBRE"]);
											
											if ($mbPrimeraLinea == true) {
												echo("<option value='" . $Curso . "' selected>" . $Texto . "</option>");
												$mbPrimeraLinea = false;
											}
											else {
												echo("<option value='" . $Curso . "'>" . $Texto . "</option>");
											}
										}
									?>
								</select>
								</div>
							</div>
						</div>

						<div id="divCursoInactivo" hidden = "true">
							<div class = "form-group row">
								<label for="cboCursoInactivo" class="col-sm-12 col-md-2 col-form-label">Curso</label>
								<div class="col-sm-12 col-md-10">
								<select class="col-sm-12 col-md-5 form-control" id="cboCursoInactivo" name="cboCursoInactivo">
									<?php
										$msConsulta = "select distinct KDSA020A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE ";
										$msConsulta .= "from KDSA051A, KDSA030A, KDSA020A ";
										$msConsulta .= "where PAGADO_051 = 0 and EXONERADO_051 = 0 and ANULADO_051 = 0 and KDSA051A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA030A.CURSO_REL = KDSA020A.CURSO_REL ";
										$msConsulta .= "and ACTIVO_020 = 0 order by KDSA020A.CURSO_REL desc";
										$m_cnx_MySQL = fxAbrirConexion();
                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
			                            $mDatos->execute();
										$mbPrimeraLinea = true;
										
										while ($Fila = $mDatos->fetch())
										{
											$Curso = rtrim($Fila["CURSO_REL"]);
											$Texto = rtrim($Fila["NOMBRE"]);
											
											if ($mbPrimeraLinea == true) {
												echo("<option value='" . $Curso . "' selected>" . $Texto . "</option>");
												$mbPrimeraLinea = false;
											}
											else {
												echo("<option value='" . $Curso . "'>" . $Texto . "</option>");
											}
										}
									?>
								</select>
								</div>
							</div>
						</div>
                        
                        <div class = "row">
                            <div class="col-auto col-xs-offset-none col-md-2 col-md-offset-2">
                                <input type="submit" id="Imprimir" name="Imprimir" value="Imprimir" class="btn btn-warning" />
                            </div>
                        </div>
                    </form>	
				</div>
			</div>
		</div>
        </div>	
		<?php	}
		}
	?>
</body>
</html>
<script>
	function activaCombo()
	{
		if (document.getElementById('optTipo1').checked || document.getElementById('optTipo2').checked)
		{
			document.getElementById('divCursoActivo').hidden = true;
			document.getElementById('divCursoInactivo').hidden = true;
		}

		if (document.getElementById('optTipo3').checked)
		{
			document.getElementById('divCursoActivo').hidden = false;
			document.getElementById('divCursoInactivo').hidden = true;
		}

		if (document.getElementById('optTipo4').checked)
		{
			document.getElementById('divCursoActivo').hidden = true;
			document.getElementById('divCursoInactivo').hidden = false;
		}
	}

	$('form').submit(function(e){
		e.preventDefault();
		
		var fecha;
		var curso;
		var nombreCurso;
		var comboActivo = document.getElementById("cboCursoActivo");
		var comboInactivo = document.getElementById("cboCursoInactivo");

		fecha = document.getElementById("dtpFechaFin").value;
		cursoActivo = document.getElementById("cboCursoActivo").value;
		cursoInactivo = document.getElementById("cboCursoInactivo").value;
		nombreCursoActivo = comboActivo.options[comboActivo.selectedIndex].text;
		nombreCursoInactivo = comboInactivo.options[comboInactivo.selectedIndex].text;

		if (document.getElementById('optTipo1').checked) 
			$.redirect("repCtasPorCobrar.php", {dtpFechaFin: fecha, blActivo: 0}, "POST", "_blank");

		if (document.getElementById('optTipo2').checked)
				$.redirect("repCtasPorCobrar.php", {dtpFechaFin: fecha, blActivo: 1}, "POST", "_blank");
	
		if (document.getElementById('optTipo3').checked)
				$.redirect("repCtasPorCobrarCurso.php", {dtpFechaFin: fecha, curso: cursoActivo, nombreCurso: nombreCursoActivo}, "POST", "_blank");

		if (document.getElementById('optTipo4').checked)
				$.redirect("repCtasPorCobrarCurso.php", {dtpFechaFin: fecha, curso: cursoInactivo, nombreCurso: nombreCursoInactivo}, "POST", "_blank");
	})
</script>