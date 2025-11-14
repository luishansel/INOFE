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
	require_once ("funciones/fxCursos.php");
	require_once ("funciones/fxCobros.php");
    require_once ("funciones/fxMatricula.php");
    $m_cnx_MySQL = fxAbrirConexion();
	$Registro = fxVerificaUsuario();
	
	if ($Registro == 0)
	{
?>

<div class="container text-center">
    <div id="DivContenido">
        <img src="imagenes/errordeacceso.png" />
    </div>
</div>
<?php }
	else
	{
		$Administrador = fxVerificaAdministrador();
		$PermisoUsuario = fxPermisoUsuario("catCursos", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
		if ($Administrador == 0 and $mbAgregar == 0 and $mbModificar == 0)
		{?>
<div class="container text-center">
    <div id="DivContenido">
        <img src="imagenes/errordeacceso.png" />
    </div>
</div>
<?php }
		else
		{
			if (isset($_POST["txtCodCurso"]))
			{
				$Codigo = $_POST["txtCodCurso"];
				$CursoInatec = $_POST["cboCursoInatec"];
				$Nombre = htmlentities($_POST["txtNomCurso"]);
				$FechaIni = $_POST["dtpFechaIni"];
				$FechaFin = $_POST["dtpFechaFin"];
                $HoraIni = $_POST["dtpHoraIni"];
                $HoraFin = $_POST["dtpHoraFin"];
				$Domingo = $_POST["chkDomingo"];
				$Lunes = $_POST["chkLunes"];
				$Martes = $_POST["chkMartes"];
				$Miercoles = $_POST["chkMiercoles"];
				$Jueves = $_POST["chkJueves"];
				$Viernes = $_POST["chkViernes"];
				$Sabado = $_POST["chkSabado"];
				$Tipo = $_POST["cboTipo"];
				$TipoAsistencia = $_POST["cboTipoAsistencia"];
				$Turno = $_POST["cboTurno"];
				$Convocatoria = $_POST["txtConvocatoria"];
                $Grupo = $_POST["txnGrupo"];
                $Moneda = $_POST["optMoneda"];
				$ValorCurso = $_POST["txnValor"];
				$Matricula = $_POST["txnMatricula"];
				$Cuota = $_POST["txnCuota"];
				$Certificacion = $_POST["txnCertificacion"];
                $Mora = $_POST["txnMora"];
                $Maximo = $_POST["txnMaximo"];
                $Activo = $_POST["optActivo"];
                $Certificar = $_POST["optCertificar"];
                $CertDigital = $_POST["optCertDigital"];
                $CerradoAntes = $_POST["optCerradoAntes"];
                if (isset($_POST["gridModulos"]))
                    $gridModulos = $_POST["gridModulos"];
                if (isset($_POST["gridFeriados"]))
                    $gridFeriados = $_POST["gridFeriados"];

				{
					if ($Codigo == "")
					{
						$Codigo = fxGuardarCursos ($CursoInatec, $Nombre, $FechaIni, $FechaFin, $HoraIni, $HoraFin, $Domingo, $Lunes, $Martes, $Miercoles, $Jueves, $Viernes, $Sabado, $Tipo, $TipoAsistencia, $Turno, $Convocatoria, $Grupo, $Moneda, $ValorCurso, $Matricula, $Cuota, $Certificacion, $Mora, $Maximo, $Activo, $Certificar, $CertDigital);
                        fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA020A", $Codigo, "", "Agregar");

						if ($Matricula > 0) //Cobro de la Matrícula
						{
							switch ($Tipo)
							{
								case 0:
									$Concepto = "Matrícula del Seminario " . trim($Nombre);
									break;
								case 1:
									$Concepto = "Matrícula del Curso " . trim($Nombre);
									break;
								case 2:
									$Concepto = "Matrícula de la Carrera " . trim($Nombre);
									break;
								case 3:
									$Concepto = "Matrícula del Taller " . trim($Nombre);
									break;
								case 4:
									$Concepto = "Matrícula del Diplomado " . trim($Nombre);
									break;
							}

							$Cobro = fxGuardarCobros ($Codigo, $FechaIni, $Concepto, $Matricula, $Moneda, 2, 1);
							fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA050A", $Cobro, "", "Agregar");
						}

						if ($Tipo == 1 || $Tipo == 2 || $Tipo == 4) //Cobro de la Primera Cuota y la Primera Mora
						{
							switch ($Tipo)
							{
								case 1:
									$Concepto = "Cuota N° 1 del Curso " . trim($Nombre);
									break;
								case 2:
									$Concepto = "Cuota N° 1 de la Carrera " . trim($Nombre);
									break;
								case 4:
									$Concepto = "Cuota N° 1 del Diplomado " . trim($Nombre);
									break;
							}
							
							$Cobro = fxGuardarCobros ($Codigo, $FechaIni, $Concepto, $Cuota, $Moneda, 0, 1);
							fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA050A", $Cobro, "", "Agregar");

							switch ($Tipo)
							{
								case 1:
									$Concepto = "Mora de la Cuota N° 1 del Curso " . trim($Nombre);
									break;
								case 2:
									$Concepto = "Mora de la Cuota N° 1 de la Carrera " . trim($Nombre);
									break;
								case 4:
									$Concepto = "Mora de la Cuota N° 1 del Diplomado " . trim($Nombre);
									break;
							}
						
							$CuotaMora = $Cuota * ($Mora / 100);
                            $FechaMora = date("Y-m-d", strtotime($FechaIni . "+1 days"));
							fxGuardarCobros ($Codigo, $FechaMora, $Concepto, $CuotaMora, $Moneda, 1, 1, $Cobro);
							fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA050A", $Cobro, "", "Agregar");
                        }

                        if (isset($_POST["gridModulos"]))
                        {
                            foreach($gridModulos as $Registro)
                            {
                                $numeroModGrid = $Registro['numero'];
                                $docenteModGrid = $Registro['codDocente'];
                                $nombreMod = $Registro['nombre'];
                                $fechaIniMod = $Registro['fechaIni'];
                                $fechaFinMod = $Registro['fechaFin'];
                                $valor = $Registro['valor'];
                                fxGuardarDetModulo ($Codigo, $docenteModGrid, $numeroModGrid, $nombreMod, $fechaIniMod, $fechaFinMod, $valor);
                            }
                        }
					}
					else
					{
						fxModificarCursos ($Codigo, $CursoInatec, $Nombre, $FechaIni, $FechaFin, $HoraIni, $HoraFin, $Domingo, $Lunes, $Martes, $Miercoles, $Jueves, $Viernes, $Sabado, $Tipo, $TipoAsistencia, $Turno, $Convocatoria, $Grupo, $Moneda, $ValorCurso, $Matricula, $Cuota, $Certificacion, $Mora, $Maximo, $Activo, $Certificar, $CertDigital, $CerradoAntes);
                        fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA020A", $Codigo, "", "Modificar");

                        //Modifica el estado de las Matrículas al cambiar el estado del Curso. Funciona para cursos que no alcanzaron el mínimo de estudiantes.
                        $msConsulta = "select MATRICULA_REL, ESTUDIANTE_REL, TIPOASISTENCIA_030, FECHA_030, DESCUENTO_030, MOTIVO_030, MEDIO_030, FUENTEINGRESO_030, ";
                        $msConsulta .= "PRIMERAVEZ_030, BECADO_030, BECADOPOR_030, INATEC_030, DOCIDENTIDAD_030, DOCACADEMICO_030 from KDSA030A where CURSO_REL = ? ";
                        if ($Activo == 0)
                            $msConsulta .= "and ESTADO_030 = 0";
                        else
                            $msConsulta .= "and ESTADO_030 = 5"; //Baja

                        $mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
					    $mAuxiliar->execute([$Codigo]);

                        while ($auxFila = $mAuxiliar->fetch())
                        {
                            $mMatricula = $auxFila["MATRICULA_REL"];
                            $mEstudiante = $auxFila["ESTUDIANTE_REL"];
                            $mTipoAsistencia = $auxFila["TIPOASISTENCIA_030"];
                            $mFecha = $auxFila["FECHA_030"];
                            $mDescuento = $auxFila["DESCUENTO_030"];
                            $mMotivo = $auxFila["MOTIVO_030"];
                            $mMedio = $auxFila["MEDIO_030"];
                            $mFuenteIngreso = $auxFila["FUENTEINGRESO_030"];
                            $mPrimeraVez = $auxFila["PRIMERAVEZ_030"];
                            $mBecado = $auxFila["BECADO_030"];
                            $mBecadoPor = $auxFila["BECADOPOR_030"];
                            $mInatec = $auxFila["INATEC_030"];
                            $mDocIdentidad = $auxFila["DOCIDENTIDAD_030"];
                            $mDocAcademico = $auxFila["DOCACADEMICO_030"];

                            if ($Activo == 0)
                                fxModificarMatricula($mMatricula, $mEstudiante, $Codigo, $mTipoAsistencia, $mFecha, $mDescuento, $mMotivo, $mMedio, $mFuenteIngreso, $mPrimeraVez, $mBecado, $mBecadoPor, $mInatec, $mDocIdentidad, $mDocAcademico, 5);
                            else
                                fxModificarMatricula($mMatricula, $mEstudiante, $Codigo, $mTipoAsistencia, $mFecha, $mDescuento, $mMotivo, $mMedio, $mFuenteIngreso, $mPrimeraVez, $mBecado, $mBecadoPor, $mInatec, $mDocIdentidad, $mDocAcademico, 0);
                        }

                        $mDatos = fxDevuelveDetModulo($Codigo);
                        $numRegistros = $mDatos->rowCount();

                        if (isset($gridModulos))
                            $numArreglo = count($gridModulos);
                        else
                            $numArreglo = 0;

                        //Verifica los registros del Grid en la Base
                        for ($i = 0; $i < $numArreglo; $i++)
                        {
                            $numeroModGrid = $gridModulos[$i]['numero'];
                            $docenteModGrid = $gridModulos[$i]['codDocente'];
                            $codigoModGrid = $gridModulos[$i]['modulo'];
                            $nombreMod = $gridModulos[$i]['nombre'];
                            $fechaIniMod = $gridModulos[$i]['fechaIni'];
                            $fechaFinMod = $gridModulos[$i]['fechaFin'];
                            $valor = $gridModulos[$i]['valor'];

                            $msConsulta = "select MODULO_REL from KDSA021A where DOCENTE_REL = ? and CURSO_REL = ? and NUMERO_021 = ?";
                            $mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
					        $mAuxiliar->execute([$docenteModGrid, $Codigo, $numeroModGrid]);
                            $numAuxiliar = $mAuxiliar->rowCount();
                            
                            if ($numAuxiliar == 0)
                                $msCodModulo = "";
                            else
                            {
                                $filaAux = $mAuxiliar->fetch();
                                $msCodModulo = trim($filaAux['MODULO_REL']);
                            }

                            if ($msCodModulo == "" and $codigoModGrid == "")
                                fxGuardarDetModulo ($Codigo, $docenteModGrid, $numeroModGrid, $nombreMod, $fechaIniMod, $fechaFinMod, $valor);
                            else
                                fxModificarDetModulo ($msCodModulo, $Codigo, $docenteModGrid, $numeroModGrid, $nombreMod, $fechaIniMod, $fechaFinMod, $valor);
                        }
                        
                        //Verifica los registros de la Base en el Grid
                        for ($i = 0; $i < $numRegistros; $i++)
                        {
                            $mbExisteRegistro = 0;
                            $Fila = $mDatos->fetch();
                            $msCodModulo = trim($Fila['MODULO_REL']);
                            $numeroMod = trim($Fila['NUMERO_021']);
                            $docenteMod = trim($Fila['DOCENTE_REL']);

                            for ($j = 0; $j < $numArreglo; $j++)
                            {
                                $numeroModGrid = $gridModulos[$j]['numero'];
                                $docenteModGrid = $gridModulos[$j]['codDocente'];

                                if ($numeroMod == $numeroModGrid and $docenteMod == $docenteModGrid)
                                    $mbExisteRegistro = 1;
                            }

                            if ($mbExisteRegistro == 0)
                                fxBorrarDetModulo ($msCodModulo);
                        }
                        
                        if (isset($_POST["gridFeriados"]))
                        {
                            foreach($gridFeriados as $Registro)
                            {
                                $fechaFer = $Registro['fecha'];
                                $diaFer = $Registro['dia'];
                                fxBorrarDetFeriado ($Codigo, $fechaFer);
                            }
                        }

                        fxVerificarDetFeriado($Codigo);
                    }
                    
                    if (isset($_POST["gridFeriados"]))
                    {
                        $itemId = 1;
                        foreach($gridFeriados as $Registro)
                        {
                            $fechaFer = $Registro['fecha'];
                            $diaFer = $Registro['dia'];
                            $motivoFer = $Registro['motivo'];
                            fxGuardarDetFeriado ($Codigo, $itemId, $fechaFer, $diaFer, $motivoFer);
                            $itemId++;
                        }
                    }
				}
				?>
<meta http-equiv="Refresh" content="0;url=gridCursos.php" /><?php
			}
			else
			{
                if (isset($_POST["KDSA"]))
				    $Codigo = $_POST["KDSA"];
                else
                    $Codigo = "";

				if ($Codigo != "")
                {
                    $RecordSet = fxDevuelveCursos(0, $Codigo);
                    $Fila = $RecordSet->fetch();
                    $CursoInatec = $Fila["CURSOINATEC_REL"];
                    $Nombre = $Fila["NOMBRE_020"];
                    $FechaIni = $Fila["FECHAINI_020"];
                    $FechaFin = $Fila["FECHAFIN_020"];
                    $HoraIni = date_create($Fila["HORAINI_020"]);
                    $HoraFin = date_create($Fila["HORAFIN_020"]);
                    $Domingo = $Fila["DOMINGO_020"];
                    $Lunes = $Fila["LUNES_020"];
                    $Martes = $Fila["MARTES_020"];
                    $Miercoles = $Fila["MIERCOLES_020"];
                    $Jueves = $Fila["JUEVES_020"];
                    $Viernes = $Fila["VIERNES_020"];
                    $Sabado = $Fila["SABADO_020"];
                    $Tipo = $Fila["TIPO_020"];
                    $TipoAsistencia = $Fila["TIPOASISTENCIA_020"];
                    $Turno = $Fila["TURNO_020"];
                    $Convocatoria = $Fila["CONVOCATORIA_020"];
                    $Grupo = $Fila["GRUPO_020"];
                    $Moneda = $Fila["MONEDA_020"];
                    $ValorCurso = $Fila["VALOR_020"];
                    $Matricula = $Fila["MATRICULA_020"];
                    $Cuota = $Fila["CUOTA_020"];
                    $Certificacion = $Fila["CERTIFICACION_020"];
                    $Mora = $Fila["MORA_020"];
                    $Maximo = $Fila["MAXIMO_020"];
                    $Activo = intval($Fila["ACTIVO_020"]);
                    $Certificar = intval($Fila["CERTIFICAR_020"]);
                    $CertDigital = intval($Fila["CERTDIGITAL_020"]);
                    $CerradoAntes = intval($Fila["CERRADOANTES_020"]);
                }
                else
                {
                    $CursoInatec = "";
                    $Nombre = "";
                    $FechaIni = "";
                    $FechaFin = "";
                    $HoraIni = "";
                    $HoraFin = "";
                    $Domingo = 0;
                    $Lunes = 0;
                    $Martes = 0;
                    $Miercoles = 0;
                    $Jueves = 0;
                    $Viernes = 0;
                    $Sabado = 0;
                    $Tipo = 0;
                    $TipoAsistencia = 0;
                    $Turno = 0;
                    $Convocatoria = "";
                    $Grupo = 0;
                    $Moneda = 0;
                    $ValorCurso = 0;
                    $Matricula = 0;
                    $Cuota = 0;
                    $Certificacion = 0;
                    $Mora = 0;
                    $Maximo = 0;
                    $Activo = 0;
                    $Certificar = 0;
                    $CertDigital = 0;
                    $CerradoAntes = 0;
                }
	?>
<div class="container text-left">
    <div id="DivContenido">
        <div class = "row">
            <div class="col-xs-12 col-md-11">
                <div class="degradado"><strong>Catálogo de cursos</strong></div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-12">
                <form id="catCursos" name="catCursos">
                    <div class="row">
                        <div class="col-md-12">
                            <input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridCursos.php';" />
                        </div>
                    </div>

                    <div class="easyui-tabs tabs-narrow" style="width:100%;height:auto">
                        <div title="Generales" style="padding:10px">
                            <!--Inicio del DIV de Generales-->
                            <div class="col-xs-auto col-xs-offset-none col-md-10 col-md-offset-1">
                                <div class="form-group row">
                                    <label for="txtCodCurso" class="col-sm-12 col-md-3 col-form-label">Código del Curso</label>
                                    <div class="col-sm-12 col-md-3">
                                        <?php echo('<input type="text" class="form-control" id="txtCodCurso" name="txtCodCurso" value="' . $Codigo . '" readonly />'); ?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="txtNomCurso" class="col-sm-12 col-md-3 col-form-label">Nombre del Curso</label>
                                    <div class="col-sm-12 col-md-7">
                                        <?php echo('<input type="text" class="form-control" id="txtNomCurso" name="txtNomCurso" value="' . $Nombre . '" />'); ?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="cboCursoInatec" class="col-sm-12 col-md-3 col-form-label">Curso INATEC</label>
                                    <div class="col-sm-12 col-md-7">
                                        <select class="form-control" id="cboCursoInatec" name="cboCursoInatec">
                                            <?php
												$msConsulta = "select CURSOINATEC_REL, NOMBRE_070, ACTIVO_070 from KDSA070A order by NOMBRE_070";
												$mDatos = $m_cnx_MySQL->prepare($msConsulta);
					                            $mDatos->execute();
												while ($Fila = $mDatos->fetch())
												{
													$Valor = rtrim($Fila["CURSOINATEC_REL"]);
													$Texto = rtrim($Fila["NOMBRE_070"]);
													$mbActivo = $Fila["ACTIVO_070"];
													if ($Codigo == "")
													{
														if ($mbActivo == 1)
															echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
													}
													else
													{
														if ($CursoInatec == "")
														{
															echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
															$Curso = $Valor;
														}
														else
														{
															if ($CursoInatec == $Valor)
																echo("<option value='" . $Valor . "' selected>" . $Texto . "</option>");
															else
																echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
														}
													}
												}
											?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="dtpFechaIni" class="col-sm-12 col-md-3 col-form-label">Fecha inicial</label>
                                    <div class="col-sm-12 col-md-3">
                                        <?php
										if ($Codigo == "")
                                            echo('<input type="date" class="form-control" id="dtpFechaIni" name="dtpFechaIni" value="' . date("Y-m-d") . '" onchange="Convocatoria()" />');
										else
											echo('<input type="date" class="form-control" id="dtpFechaIni" name="dtpFechaIni" value="' . $FechaIni . '" onchange="Convocatoria()" />');
									    ?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="dtpFechaFin" class="col-sm-12 col-md-3 col-form-label">Fecha final</label>
                                    <div class="col-sm-12 col-md-3">
                                        <?php
                                            if ($Codigo == "")
                                                echo('<input type="date" class="form-control" id="dtpFechaFin" name="dtpFechaFin" value="' . date("Y-m-d") . '" />');
                                            else
                                                echo('<input type="date" class="form-control" id="dtpFechaFin" name="dtpFechaFin" value="' . $FechaFin . '" />');
                                        ?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="dtpHoraIni" class="col-sm-12 col-md-3 col-form-label">Hora inicial de cada sesión</label>
                                    <div class="col-sm-12 col-md-3">
                                        <?php
                                            if ($Codigo == "")
                                                echo('<input type="time" class="form-control" id="dtpHoraIni" name="dtpHoraIni" value="' . date("H:i") . '" />');
                                            else
                                                echo('<input type="time" class="form-control" id="dtpHoraIni" name="dtpHoraIni" value="' . date_format($HoraIni, 'H:i') . '" />');
                                        ?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="dtpHoraFin" class="col-sm-12 col-md-3 col-form-label">Hora final de cada sesión</label>
                                    <div class="col-sm-12 col-md-3">
                                        <?php
                                            if ($Codigo == "")
                                                echo('<input type="time" class="form-control" id="dtpHoraFin" name="dtpHoraFin" value="' . date("H:i") . '" />');
                                            else
                                                echo('<input type="time" class="form-control" id="dtpHoraFin" name="dtpHoraFin" value="' . date_format($HoraFin, 'H:i') . '" />');
                                        ?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="cboTipo" class="col-sm-12 col-md-3 col-form-label">Tipo de estudio</label>
                                    <div class="col-sm-12 col-md-3">
                                        <select class="form-control" id="cboTipo" name="cboTipo">
                                            <?php
												if ($Tipo == 0 or $Codigo == "")
													echo("<option value='0' selected>Seminario</option>");
												else
													echo("<option value='0'>Seminario</option>");
												
												if ($Tipo == 1)
													echo("<option value='1' selected>Curso</option>");
												else
													echo("<option value='1'>Curso</option>");
												
												if ($Tipo == 2)
													echo("<option value='2' selected>Carrera</option>");
												else
													echo("<option value='2'>Carrera</option>");
												
												if ($Tipo == 3)
													echo("<option value='3' selected>Taller</option>");
												else
													echo("<option value='3'>Taller</option>");
													
												if ($Tipo == 4)
													echo("<option value='4' selected>Diplomado</option>");
												else
													echo("<option value='4'>Diplomado</option>");
											?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="cboTurno" class="col-sm-12 col-md-3 col-form-label">Turno</label>
                                    <div class="col-sm-12 col-md-3">
                                        <select class="form-control" id="cboTurno" name="cboTurno">
                                            <?php
												if ($Turno == 0 or $Codigo == "")
													echo("<option value='0' selected>Nocturno</option>");
												else
													echo("<option value='0'>Nocturno</option>");
												
												if ($Turno == 1)
													echo("<option value='1' selected>Sabatino</option>");
												else
													echo("<option value='1'>Sabatino</option>");
												
												if ($Turno == 2)
													echo("<option value='2' selected>Dominical</option>");
												else
													echo("<option value='2'>Dominical</option>");
												
												if ($Turno == 3)
													echo("<option value='3' selected>Matutino</option>");
												else
													echo("<option value='3'>Matutino</option>");
												
												if ($Turno == 4)
													echo("<option value='4' selected>Vespertino</option>");
												else
													echo("<option value='4'>Vespertino</option>");
											?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="cboTipoAsistencia" class="col-sm-12 col-md-3 col-form-label">Tipo de asistencia</label>
                                    <div class="col-sm-12 col-md-3">
                                        <select class="form-control" id="cboTipoAsistencia" name="cboTipoAsistencia">
                                            <?php
												if ($TipoAsistencia == 0 or $Codigo == "")
													echo("<option value='0' selected>Presencial</option>");
												else
													echo("<option value='0'>Presencial</option>");
												
												if ($TipoAsistencia == 1)
													echo("<option value='1' selected>Virtual</option>");
												else
													echo("<option value='1'>Virtual</option>");

												if ($TipoAsistencia == 2)
													echo("<option value='2' selected>On-line</option>");
												else
													echo("<option value='2'>On-line</option>");
											?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="chkDiasClase" class="col-sm-12 col-md-3 col-form-label">Días de clase</label>
                                    <div class="col-sm-12 col-md-8">
                                        <?php
										if ($Domingo == 0)
											echo('<input type="checkbox" class="letraGris" name="chkDiasClase" id="chkDomingo" value=0><span class="letraGris"> Domingo</span> &nbsp;');
										else
											echo('<input type="checkbox" class="letraGris" name="chkDiasClase" id="chkDomingo" value=0 checked><span class="letraGris"> Domingo</span> &nbsp;');
										
										if ($Lunes == 0)
											echo('<input type="checkbox" class="letraGris" name="chkDiasClase" id="chkLunes"><span class="letraGris"> Lunes</span> &nbsp;');
										else
											echo('<input type="checkbox" class="letraGris" name="chkDiasClase" id="chkLunes" checked><span class="letraGris"> Lunes</span> &nbsp;');
										
										if ($Martes == 0)
											echo('<input type="checkbox" class="letraGris" name="chkDiasClase" id="chkMartes"><span class="letraGris"> Martes</span> &nbsp;');
										else
											echo('<input type="checkbox" class="letraGris" name="chkDiasClase" id="chkMartes" checked><span class="letraGris"> Martes</span> &nbsp;');
										
										if ($Miercoles == 0)
											echo('<input type="checkbox" class="letraGris" name="chkDiasClase" id="chkMiercoles"><span class="letraGris"> Miércoles</span> &nbsp;');
										else
											echo('<input type="checkbox" class="letraGris" name="chkDiasClase" id="chkMiercoles" checked><span class="letraGris"> Miércoles</span> &nbsp;');

										if ($Jueves == 0)
											echo('<input type="checkbox" class="letraGris" name="chkDiasClase" id="chkJueves"><span class="letraGris"> Jueves</span> &nbsp;');
										else
											echo('<input type="checkbox" class="letraGris" name="chkDiasClase" id="chkJueves" checked><span class="letraGris"> Jueves</span> &nbsp;');

										if ($Viernes == 0)
											echo('<input type="checkbox" class="letraGris" name="chkDiasClase" id="chkViernes"><span class="letraGris"> Viernes</span> &nbsp;');
										else
											echo('<input type="checkbox" class="letraGris" name="chkDiasClase" id="chkViernes" checked><span class="letraGris"> Viernes</span> &nbsp;');
										
										if ($Sabado == 0)
											echo('<input type="checkbox" class="letraGris" name="chkDiasClase" id="chkSabado"><span class="letraGris"> Sábado</span>');
										else
											echo('<input type="checkbox" class="letraGris" name="chkDiasClase" id="chkSabado" checked><span class="letraGris"> Sábado</span>');
									?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="txtConvocatoria" class="col-sm-12 col-md-3 col-form-label">Convocatoria</label>
                                    <div class="col-sm-12 col-md-2">
                                        <?php echo('<input type="text" class="form-control" id="txtConvocatoria" name="txtConvocatoria" value="' . $Convocatoria . '" />'); ?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="txnGrupo" class="col-sm-12 col-md-3 col-form-label">Grupo</label>
                                    <div class="col-sm-12 col-md-2">
                                        <?php
										if ($Codigo == "")
											echo('<input type="number" style="text-align:right" class="form-control" id="txnGrupo" name="txnGrupo" value="1" />');
										else
											echo('<input type="number" style="text-align:right" class="form-control" id="txnGrupo" name="txnGrupo" value="' . $Grupo . '" />');
									?>
                                    </div>
                                </div>

                                <div class="form-group row">
									<label for="optMoneda" class="col-sm-12 col-md-3 form-label">Moneda</label>
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
                                    <label for="txnValor" class="col-sm-12 col-md-3 col-form-label">Valor del curso</label>
                                    <div class="col-sm-12 col-md-3">
                                        <?php
										if ($Codigo == "")
											echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnValor" name="txnValor" value="0" />');
										else
											echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnValor" name="txnValor" value="' . $ValorCurso . '" />');
									?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="txnMatricula" class="col-sm-12 col-md-3 col-form-label">Valor de la matrícula</label>
                                    <div class="col-sm-12 col-md-3">
                                        <?php
										if ($Codigo == "")
											echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnMatricula" name="txnMatricula" value="0" />');
										else
											echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnMatricula" name="txnMatricula" value="' . $Matricula . '" />');
									?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="txnCuota" class="col-sm-12 col-md-3 col-form-label">Valor de la cuota</label>
                                    <div class="col-sm-12 col-md-3">
                                        <?php
										if ($Codigo == "")
											echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnCuota" name="txnCuota" value="0" />');
										else
											echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnCuota" name="txnCuota" value="' . $Cuota . '" />');
									?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="txnCertificacion" class="col-sm-12 col-md-3 col-form-label">Valor de la certificación</label>
                                    <div class="col-sm-12 col-md-3">
                                        <?php
										if ($Codigo == "")
											echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnCertificacion" name="txnCertificacion" value="0" />');
										else
											echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnCertificacion" name="txnCertificacion" value="' . $Certificacion . '" />');
									?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="txnMora" class="col-sm-12 col-md-3 col-form-label">Porcentaje por Mora</label>
                                    <div class="col-sm-12 col-md-2">
                                        <?php
										if ($Codigo == "")
											echo('<input type="number" style="text-align:right" class="form-control" id="txnMora" name="txnMora" value="1" />');
										else
											echo('<input type="number" style="text-align:right" class="form-control" id="txnMora" name="txnMora" value="' . $Mora . '" />');
									?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="txnMaximo" class="col-sm-12 col-md-3 col-form-label">Máximo de estudiantes</label>
                                    <div class="col-sm-12 col-md-2">
                                        <?php
										if ($Codigo == "")
											echo('<input type="number" style="text-align:right" class="form-control" id="txnMaximo" name="txnMaximo" value="1" />');
										else
											echo('<input type="number" style="text-align:right" class="form-control" id="txnMaximo" name="txnMaximo" value="' . $Maximo . '" />');
									?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="optCerradoAntes" class="col-sm-12 col-md-3 form-label">Cerrado antes de iniciar</label>
                                    <div class="col-sm-12 col-md-4">
                                        <div class="radio">
                                            <?php
											if ($CerradoAntes == 0 or $Codigo == "")
												echo('<input type="radio" id="optCerradoAntes1" name="optCerradoAntes" value="0" onchange="cambiaValores()" checked="checked" /><span class="letraGris"> No </span><input type="radio" id="optCerradoAntes2" name="optCerradoAntes" value="1" onchange="cerradoDespues()" /><span class="letraGris"> Si</span>');
											else
												echo('<input type="radio" id="optCerradoAntes1" name="optCerradoAntes" value="0" onchange="cambiaValores()" /><span class="letraGris"> No </span><input type="radio" id="optCerradoAntes2" name="optCerradoAntes" value="1" onchange="cerradoDespues()" checked="checked" /><span class="letraGris"> Si</span>');
										?>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="optActivo" class="col-sm-12 col-md-3 form-label">Activo</label>
                                    <div class="col-sm-12 col-md-4">
                                        <div class="radio">
                                            <?php
											if ($Activo == 1 or $Codigo == "")
												echo('<input type="radio" id="optActivo1" name="optActivo" value="0" /><span class="letraGris"> No </span><input type="radio" id="optActivo2" name="optActivo" value="1" onchange="verificarCierre()" checked="checked" /><span class="letraGris"> Si</span>');
											else
												echo('<input type="radio" id="optActivo1" name="optActivo" value="0" checked="checked" /><span class="letraGris"> No </span><input type="radio" id="optActivo2" name="optActivo" value="1" onchange="verificarCierre()" /><span class="letraGris"> Si</span>');
										?>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="optCertificar" class="col-sm-12 col-md-3 form-label">Certificar curso activo</label>
                                    <div class="col-sm-12 col-md-4">
                                        <div class="radio">
                                            <?php
											if ($Certificar == 0 or $Codigo == "")
												echo('<input type="radio" id="optCertificar1" name="optCertificar" value="0" checked="checked" /> No <input type="radio" id="optCertificar2" name="optCertificar" value="1" onchange="verificarCierre()" /> Si');
											else
												echo('<input type="radio" id="optCertificar1" name="optCertificar" value="0" /> No <input type="radio" id="optCertificar2" name="optCertificar" value="1" onchange="verificarCierre()" checked="checked" /> Si');
										?>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="optCertificar" class="col-sm-12 col-md-3 form-label">Certificación digital</label>
                                    <div class="col-sm-12 col-md-4">
                                        <div class="radio">
                                            <?php
											if ($CertDigital == 0 or $Codigo == "")
												echo('<input type="radio" id="optCertDigital1" name="optCertDigital" value="0" checked="checked" /> No <input type="radio" id="optCertDigital2" name="optCertDigital" value="1" /> Si');
											else
												echo('<input type="radio" id="optCertDigital1" name="optCertDigital" value="0" /> No <input type="radio" id="optCertDigital2" name="optCertDigital" value="1" checked="checked" /> Si');
										?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--Final del DIV de Generales-->

                        <div title="Módulos" style="padding:10px">
                            <!--Inicio del DIV de Módulos-->
                            <div class="col-xs-auto col-md-12">
                                <div class="form-group row">
                                    <div class="col-sm-auto col-md-12">
                                        <?php 
										$nombreArchivoMOD = fxEscribeJsonModulo($Codigo);
										?>
                                        <div id="dvMOD">
                                            <table id="dgMOD" class="easyui-datagrid table"
                                                data-options="iconCls:'icon-edit', toolbar:'#tbMOD', footer:'#ftMOD', singleSelect:true, url:'<?php echo(trim($nombreArchivoMOD)); ?>', method:'get', onClickCell:onClickCellMOD">
                                                <thead>
                                                    <tr>
                                                        <th data-options="field:'curso', align:'left', hidden:true">Curso</th>
                                                        <th data-options="field:'modulo', align:'left', hidden:true">CodModulo</th>
                                                        <th data-options="field:'codDocente', align:'left', hidden:true">CodDocente</th>
                                                        <th data-options="field:'plan', align:'left', hidden:true">Planificación</th>
                                                        <th data-options="field:'numero', width:'8%', align:'center'">Módulo</th>
                                                        <th data-options="field:'docente', width:'30%', align:'left'">Docente</th>
                                                        <th data-options="field:'nombre', width:'30%', align:'left'">Nombre del módulo</th>
                                                        <th data-options="field:'fechaIni', width:'11%', align:'left', 
                                                        editor:
                                                         {type:'datebox', 
                                                            options:
                                                            {
                                                                formatter:function(date) {
                                                                    var y = date.getFullYear();
                                                                    var m = date.getMonth() + 1;
                                                                    var d = date.getDate();
                                                                    return y + '/' + (m < 10 ? ('0' + m) : m) + '/' + (d < 10 ? ('0' + d) : d);
                                                                },
                                                                parser:function(s) {
                                                                    if (!s) return new Date();
                                                                    var ss = (s.split('-'));
                                                                    var y = parseInt(ss[0], 10);
                                                                    var m = parseInt(ss[1], 10);
                                                                    var d = parseInt(ss[2], 10);
                                                                    if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
                                                                        return new Date(y, m - 1, d);
                                                                    } else {
                                                                        return new Date();
                                                                    }
                                                                }
                                                            }
                                                        }">Fecha Inicial</th>
                                                        <th data-options="field:'fechaFin', width:'11%', align:'left', 
                                                        editor:
                                                         {type:'datebox', 
                                                            options:
                                                            {
                                                                formatter:function(date) {
                                                                    var y = date.getFullYear();
                                                                    var m = date.getMonth() + 1;
                                                                    var d = date.getDate();
                                                                    return y + '/' + (m < 10 ? ('0' + m) : m) + '/' + (d < 10 ? ('0' + d) : d);
                                                                },
                                                                parser:function(s) {
                                                                    if (!s) return new Date();
                                                                    var ss = (s.split('-'));
                                                                    var y = parseInt(ss[0], 10);
                                                                    var m = parseInt(ss[1], 10);
                                                                    var d = parseInt(ss[2], 10);
                                                                    if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
                                                                        return new Date(y, m - 1, d);
                                                                    } else {
                                                                        return new Date();
                                                                    }
                                                                }
                                                            }
                                                        }">Fecha Final</th>
                                                        <th data-options="field:'valor',align:'right',editor:{type:'numberbox',options:{precision:2}}">Valor de sesión</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div id="tbMOD" style="height:auto; padding-top:1%; padding-bottom:2%">
                                    <table width="100%">
                                        <tr>
                                            <td width="15%">Número de módulo</td>
                                            <td>
                                                <div style="float: left; display: block">
                                                    <input id="txtModNumero" class="form-control easyui-numberbox" style="width:100%">
                                                </div>
                                                <div style="float: right; display: block">
                                                    <label for="chkModulo" class="col-form-label">Usar los Módulos pre-Configurados &nbsp;</label>
                                                    <input type="checkbox" id="chkModulo" onclick="preConfigurados()">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="15%">Docente</td>
                                            <td>
                                                <select class="form-control" id="cboDocente" name="cboDocente" style="width:50%">
                                                    <?php
                                                        $msConsulta = "select DOCENTE_REL, NOMBRE_100 from KDSA100A where ACTIVO_100 = 1 order by NOMBRE_100";
                                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
					                                    $mDatos->execute();
                                                        while ($Fila = $mDatos->fetch())
                                                        {
                                                            $Valor = rtrim($Fila["DOCENTE_REL"]);
                                                            $Texto = rtrim($Fila["NOMBRE_100"]);
                                                            echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                                        }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr id="trModulos1">
                                            <td width="15%">Nombre de módulo</td>
                                            <td><input id="txtModulo" class="form-control easyui-textbox" style="width:100%"></td>
                                        </tr>
                                        <tr id="trModulos2">
                                            <td width="15%">Curso</td>
                                            <td>
                                                <select class="form-control" id="cboPreCursos" name="cboPreCursos" style="width:100%" onchange="llenaModulos()">
                                                    <?php
                                                        $msConsulta = "select CFGMODULO_REL, CURSO_110 from KDSA110A order by CURSO_110";
                                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
					                                    $mDatos->execute();
                                                        while ($Fila = $mDatos->fetch())
                                                        {
                                                            $Valor = rtrim($Fila["CFGMODULO_REL"]);
                                                            $Texto = rtrim($Fila["CURSO_110"]);
                                                            echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                                        }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr id="trModulos3">
                                            <td width="15%">Nombre de módulo</td>
                                            <td id="tdModulos">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="15%">Fecha inicial</td>
                                            <td>
                                                <?php
													echo('<input type="date" class="form-control" id="dtpFechaIniMod" name="dtpFechaIniMod" style="width:20%" value="' . date("Y-m-d") . '" />');
												?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="15%">Fecha final</td>
                                            <td>
                                                <?php
													echo('<input type="date" class="form-control" id="dtpFechaFinMod" name="dtpFechaFinMod" style="width:20%" value="' . date("Y-m-d") . '" />');
												?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Valor de la sesión</td>
                                            <td><input id="txnValorModulo" class="easyui-numberbox" data-options="precision:2" style="width:20%; text-align:right"></td>
                                        </tr>
                                    </table>
                                </div>

                                <div id="ftMOD" style="height:auto">
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="appendMOD()">Agregar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeitMOD()">Borrar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptitMOD()">Aceptar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="rejectMOD()">Deshacer</a>
                                </div>
                            </div>
                        </div>
                        <!--Final del DIV de Módulos-->

                        <div title="Días no hábiles" style="padding:10px">
                            <!--Inicio del DIV de Feriados-->
                            <div class="col-xs-auto col-md-12">
                                <div class="form-group row">
                                    <div class="col-sm-auto col-md-12">
                                        <?php 
										$nombreArchivoFER = fxEscribeJsonFeriado($Codigo);
										?>
                                        <div id="dvFER">
                                            <table id="dgFER" class="easyui-datagrid table"
                                                data-options="iconCls:'icon-edit', toolbar:'#tbFER', footer:'#ftFER', singleSelect:true, url:'<?php echo(trim($nombreArchivoFER)); ?>', method:'get', onClickCell:onClickCellFER">
                                                <thead>
                                                    <tr>
                                                        <th data-options="field:'fecha', width:'15%', align:'center'">Fecha</th>
                                                        <th data-options="field:'dia', width:'20%', align:'left'">Día</th>
                                                        <th data-options="field:'motivo', width:'65%', align:'left'">Motivo</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div id="tbFER" style="height:auto; padding-top:1%; padding-bottom:2%">
                                    <table width="100%">
                                        <tr>
                                            <td width="15%">Fecha</td>
                                            <td>
                                                <div style="float: left; display: block">
                                                <?php
													echo('<input type="date" class="form-control" id="dtpFechaFer" name="dtpFechaFer" style="width:100%" value="' . date("Y-m-d") . '" onchange="diaDeLaSemana()" />');
                                                ?>
                                                </div>
                                                <div style="float: right; display: block">
                                                    <a href="javascript:void(0)" class="easyui-linkbutton" id="cmdFeriado" onclick="preFeriados()">Obtener los Feriados pre-Configurados</a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="15%">Día</td>
                                            <td><input id="txtDiaFer" class="form-control easyui-textbox" style="width:20%" readonly></td>
                                        </tr>
                                        <tr>
                                            <td width="15%">Motivo</td>
                                            <td><input id="txtMotivoFer" class="form-control easyui-textbox" style="width:100%"></td>
                                        </tr>
                                    </table>
                                </div>

                                <div id="ftFER" style="height:auto">
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="appendFER()">Agregar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeitFER()">Borrar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptitFER()">Aceptar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="rejectFER()">Deshacer</a>
                                </div>
                            </div>
                        </div>
                        <!--Final del DIV de Feriados-->
                    </div>
                    <!--Final del DIV de Tabs-->
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
    if (document.getElementById('txtNomCurso').value == "") {
        $.messager.alert('INOFE', 'Falta el Nombre del Curso.', 'warning');
        return false;
    }

    if (document.getElementById('dtpFechaIni').value > document.getElementById('dtpFechaFin').value) {
        $.messager.alert('INOFE', 'La Fecha Inicial es posterior a la Final.', 'warning');
        return false;
    }

    if (document.getElementById('dtpHoraIni').value > document.getElementById('dtpHoraFin').value) {
        $.messager.alert('INOFE', 'La Hora Inicial de la Sesión es posterior a la Final.', 'warning');
        return false;
    }

    if (document.getElementById('txtConvocatoria').value == "") {
        $.messager.alert('INOFE', 'Falta la Convocatoria del Curso.', 'warning');
        return false;
    }

    if (document.getElementById('txnGrupo').value < 1) {
        $.messager.alert('INOFE', 'Falta el Grupo del Curso.', 'warning');
        return false;
    }

    if (document.getElementById('txnValor').value <= 0) {
        $.messager.alert('INOFE', 'Falta el Valor del Curso.', 'warning');
        return false;
    }

    if (document.getElementById('cboTipo').value != 0 && document.getElementById('cboTipo').value != 3) {
        if (document.getElementById('txnMatricula').value <= 0) {
            $.messager.alert('INOFE', 'Falta el Valor la Matrícula.', 'warning');
            return false;
        }

        if (document.getElementById('txnCuota').value <= 0) {
            $.messager.alert('INOFE', 'Falta el Valor de la Cuota.', 'warning');
            return false;
        }
    }

    if (document.getElementById('txnMora').value <= 0) {
        $.messager.alert('INOFE', 'Falta el Porcentaje por Mora.', 'warning');
        return false;
    }

    if (document.getElementById('txnMaximo').value <= 0) {
        $.messager.alert('INOFE', 'Falta la cantidad máxima de estudiantes.', 'warning');
        return false;
    }
/*
    if ($('#dgMOD').datagrid('getRows').length <= 0) {
        $.messager.alert('INOFE', 'Faltan los Módulos del Curso.', 'warning');
        return false;
    }
*/
    return true;
}

function cambiaValores()
{
    var cerrado = document.getElementById("optCerradoAntes2").checked;

    if (cerrado)
    {
        document.getElementById("optActivo1").checked = true;
        document.getElementById("optCertificar1").checked = true;
    }
    else
    {
        document.getElementById("optActivo2").checked = true;
    }
}

function verificarCierre()
{
    var cerrado = document.getElementById("optCerradoAntes2").checked;
    var inactivo = document.getElementById("optActivo1").checked;

    if (cerrado)
    {
        document.getElementById("optActivo1").checked = true;
        document.getElementById("optCertificar1").checked = true;
        $.messager.alert('KDSA', 'El curso fue cerrado antes de iniciar', 'warning');
        return false;
    }

    if (inactivo)
    {
        document.getElementById("optCertificar1").checked = true;
        $.messager.alert('KDSA', 'El curso está inactivo', 'warning');
        return false;
    }
}

function cerradoDespues()
{
    var fechaHoy = new Date();
    var anno = fechaHoy.getFullYear();
    var mes = fechaHoy.getMonth() + 1;
    var dia = fechaHoy.getDate();

    if (dia < 10)
    {
        if (mes < 10)
            var fechaAhora = anno + "-0" + mes + "-0" + dia;
        else
            var fechaAhora = anno + "-" + mes + "-0" + dia;
    }
    else
    {
        if (mes < 10)
            var fechaAhora = anno + "-0" + mes + "-" + dia;
        else
            var fechaAhora = anno + "-" + mes + "-" + dia;
    }

    if (document.getElementById('dtpFechaIni').value <= fechaAhora)
    {
        document.getElementById("optCerradoAntes1").checked = true;
        $.messager.alert('KDSA', 'El curso ya inició', 'warning');
        return false;
    }
    else
        cambiaValores();
}

function Convocatoria() {
    var fechaIni = document.getElementById("dtpFechaIni").value;
    var annoFecha = fechaIni.substr(0, 4);
    var mesFecha = parseInt(fechaIni.substr(5, 2));
    var resultado;

    switch (mesFecha) {
        case 1:
            resultado = "I" + "-" + annoFecha;
            break;

        case 2:
            resultado = "II" + "-" + annoFecha;
            break;

        case 3:
            resultado = "III" + "-" + annoFecha;
            break;

        case 4:
            resultado = "IV" + "-" + annoFecha;
            break;

        case 5:
            resultado = "V" + "-" + annoFecha;
            break;

        case 6:
            resultado = "VI" + "-" + annoFecha;
            break;

        case 7:
            resultado = "VII" + "-" + annoFecha;
            break;

        case 8:
            resultado = "VIII" + "-" + annoFecha;
            break;

        case 9:
            resultado = "IX" + "-" + annoFecha;
            break;

        case 10:
            resultado = "X" + "-" + annoFecha;
            break;

        case 11:
            resultado = "XI" + "-" + annoFecha;
            break;

        case 12:
            resultado = "XII" + "-" + annoFecha;
            break;
    }

    document.getElementById("txtConvocatoria").value = resultado;
}

/*Grid de Módulos*/
var editIndexMOD = undefined;
var lastIndexMOD;

$.extend($.fn.datagrid.defaults.editors, {
    datebox: {
        init: function(container, options){
            var input = $('<input type="date">').appendTo(container);
            return input;
        },
        destroy: function(target){
            $(target).remove();
        },
        getValue: function(target){
            return $(target).val();
        },
        setValue: function(target, value){
            $(target).val(value);
        },
        resize: function(target, width){
            $(target)._outerWidth(width);
        }
    }
});

$('#dgMOD').datagrid({
    onClickRow: function(rowIndex) {
        if (lastIndexMOD != rowIndex) {
            $(this).datagrid('endEdit', lastIndexMOD);
            $(this).datagrid('beginEdit', rowIndex);
        }
        lastIndexMOD = rowIndex;
    }
});

function endEditingMOD() {
    if (editIndexMOD == undefined) {
        return true
    }
    if ($('#dgMOD').datagrid('validateRow', editIndexMOD)) {
        $('#dgMOD').datagrid('endEdit', editIndexMOD);
        editIndexMOD = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCellMOD(index, field) {
    if (editIndexMOD != index) {
        if (endEditingMOD()) {
            $('#dgMOD').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndexMOD = index;
        } else {
            setTimeout(function() {
                $('#dgMOD').datagrid('selectRow', editIndexMOD);
            }, 0);
        }
    }
}

//Esta función appendMOD está modificada para la página catCursos
function appendMOD() {
    if (endEditingMOD()) {
        var indice = $('#txtModNumero').val() - 1
        var gridModulos = $('#dgMOD').datagrid('getData');
        var registros = $('#dgMOD').datagrid('getRows').length - 1;
        var existeModulo = 0;
        var fechaAnterior = 0;
        var nombreModulo;

		if ($('#txtModNumero').val() == "")
        {
            $.messager.alert('INOFE', 'Falta el Número de Módulo.', 'warning');
        }
        else
        {
            if (registros >= 0)
            {
                for (i = 0; i <= registros; i++)
                {
                    if ($('#txtModNumero').val() == gridModulos.rows[i].numero && $('#cboDocente').val() == gridModulos.rows[i].codDocente)
                    {
                        existeModulo = 1;
                    }
                }

                if (existeModulo == 1)
                {
                    $.messager.alert('INOFE', 'El Número de Módulo junto con este Docente ya fueron registrados.', 'warning');
                }
                else
                {
                    if (document.getElementById("chkModulo").checked == true)
                        nombreModulo = $('#cboPreModulos option:selected').text();
                    else
                        nombreModulo = $('#txtModulo').val();

                    if (nombreModulo == "")
                    {
                        $.messager.alert('INOFE', 'Falta el Nombre del Módulo.', 'warning');
                    }
                    else
                    {
                        $('#dgMOD').datagrid('insertRow', {
                            index: indice,
                            row: {
                                curso: $('#txtCodCurso').val(),
                                numero: $('#txtModNumero').val(),
                                codDocente: $('#cboDocente').val(),
                                docente: $('#cboDocente option:selected').text(),
                                nombre: nombreModulo,
                                fechaIni: $('#dtpFechaIniMod').val(),
                                fechaFin: $('#dtpFechaFinMod').val(),
                                valor: $('#txnValorModulo').val(),
                                modulo: "",
                                plan: ""
                            }
                        });
                    }
                }
            }
            else
            {
                if (document.getElementById("chkModulo").checked == true)
                    nombreModulo = $('#cboPreModulos option:selected').text();
                else
                    nombreModulo = $('#txtModulo').val();
                
                if (nombreModulo == "")
                {
                    $.messager.alert('INOFE', 'Falta el Nombre del Módulo.', 'warning');
                }
                else
                {
                    if ($('#dtpFechaIniMod').val() < $('#dtpFechaIni').val())
                    {
                        fechaAnterior = 1;
                    }

                    if (fechaAnterior == 1)
                    {
                        $.messager.alert('INOFE', 'La fecha inicial del Módulo es anterior al inicio del Curso.', 'warning');
                    }
                    else
                    {
                        $('#dgMOD').datagrid('appendRow', {
                            curso: $('#txtCodCurso').val(),
                            numero: $('#txtModNumero').val(),
                            codDocente: $('#cboDocente').val(),
                            docente: $('#cboDocente option:selected').text(),
                            nombre: nombreModulo,
                            fechaIni: $('#dtpFechaIniMod').val(),
                            fechaFin: $('#dtpFechaFinMod').val(),
                            valor: $('#txnValorModulo').val(),
                            modulo: "",
                            plan: ""
                        });
                    }
                }
            }
        }
    }
}

function removeitMOD() {
    filaCur = $('#dgMOD').datagrid('getSelected');
    indice = $('#dgMOD').datagrid('getRowIndex', filaCur);

    if ($('#dgMOD').datagrid('getData').rows[indice].plan == '')
    {
        $('#dgMOD').datagrid('cancelEdit', indice)
            .datagrid('deleteRow', indice);
    }
    else
    {
        $.messager.alert('INOFE', 'El Módulo no puede ser borrado. Ya tiene la Planificación Programática.', 'warning');
    }
    $('#dgMOD').datagrid('cancelEdit', indice)
    indice = undefined;
}

function acceptitMOD() {
    if (endEditingMOD()) {
        $('#dgMOD').datagrid('acceptChanges');
    }
}

function rejectMOD() {
    $('#dgMOD').datagrid('rejectChanges');
    editIndexMOD = undefined;
}

/*Grid de Feriados*/
var editIndexFER = undefined;
var lastIndexFER;

$('#dgFER').datagrid({
    onClickRow: function(rowIndex) {
        if (lastIndexFER != rowIndex) {
            $(this).datagrid('endEdit', lastIndexFER);
            $(this).datagrid('beginEdit', rowIndex);
        }
        lastIndexFER = rowIndex;
    }
});

function endEditingFER() {
    if (editIndexFER == undefined) {
        return true
    }
    if ($('#dgFER').datagrid('validateRow', editIndexFER)) {
        $('#dgFER').datagrid('endEdit', editIndexFER);
        editIndexFER = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCellFER(index, field) {
    if (editIndexFER != index) {
        if (endEditingFER()) {
            $('#dgFER').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndexFER = index;
        } else {
            setTimeout(function() {
                $('#dgFER').datagrid('selectRow', editIndexFER);
            }, 0);
        }
    }
}

//Esta función appendFER está modificada para la página catCursos
function appendFER() {
    if (endEditingFER()) {
        if ($('#dtpFechaFer').val() < $('#dtpFechaIni').val() || $('#dtpFechaFer').val() > $('#dtpFechaFin').val() || $('#txtMotivoFer').val() == "")
        {
            if ($('#dtpFechaFer').val() < $('#dtpFechaIni').val())
                $.messager.alert('INOFE', 'La fecha que desea ingresar está antes del inicio del Curso.', 'info');

            if ($('#dtpFechaFer').val() > $('#dtpFechaFin').val())
                $.messager.alert('INOFE', 'La fecha que desea ingresar está después de la finalización del Curso.', 'info');
            
            if ($('#txtMotivoFer').val() == "")
                $.messager.alert('INOFE', 'Falta el Motivo del Feriado.', 'info');
        }
        else 
        {
            var i;
            var mbInsertarFecha = 0;
            var gridFeriado = $('#dgFER').datagrid('getData');
            var registros = $('#dgFER').datagrid('getRows').length - 1;
            var msDia = $('#_easyui_textbox_input2').val();

            if (msDia == "Domingo" && document.getElementById('chkDomingo').checked == true)
                mbInsertarFecha = 1;
            
            if (msDia == "Lunes" && document.getElementById('chkLunes').checked == true)
                mbInsertarFecha = 1;

            if (msDia == "Martes" && document.getElementById('chkMartes').checked == true)
                mbInsertarFecha = 1;

            if (msDia == "Miércoles" && document.getElementById('chkMiercoles').checked == true)
                mbInsertarFecha = 1;
            
            if (msDia == "Jueves" && document.getElementById('chkJueves').checked == true)
                mbInsertarFecha = 1;

            if (msDia == "Viernes" && document.getElementById('chkViernes').checked == true)
                mbInsertarFecha = 1;
            
            if (msDia == "Sábado" && document.getElementById('chkSabado').checked == true)
                mbInsertarFecha = 1;

            for (i = 0; i <= registros; i++) //Evita la repetición de Fechas
            {
                if ($('#dtpFechaFer').val() == gridFeriado.rows[i].fecha){
                    mbInsertarFecha = 2;
                }
            }

            if (mbInsertarFecha == 1)
            {
                $('#dgFER').datagrid('appendRow', {
                    fecha: $('#dtpFechaFer').val(),
                    dia: msDia,
                    motivo: $('#txtMotivoFer').val()
                });
            }
            else {
                if (mbInsertarFecha == 0)
                    $.messager.alert('INOFE', 'La fecha que desea ingresar no afecta el Curso.', 'info');
                else
                    $.messager.alert('INOFE', 'La fecha que desea ingresar ya está incluida.', 'info');
            }
        }
        editIndexFER = $('#dgFER').datagrid('getRows').length;
        $('#dgFER').datagrid('selectRow', editIndexFER).datagrid('beginEdit', editIndexFER);
    }
}

function removeitFER() {
    if (editIndexFER == undefined) {
        return
    }
    $('#dgFER').datagrid('cancelEdit', editIndexFER)
        .datagrid('deleteRow', editIndexFER);
    editIndexFER = undefined;
}

function acceptitFER() {
    if (endEditingFER()) {
        $('#dgFER').datagrid('acceptChanges');
    }
}

function rejectFER() {
    $('#dgFER').datagrid('rejectChanges');
    editIndexFER = undefined;
}

function preConfigurados(){
    if (document.getElementById("chkModulo").checked == true){
        document.getElementById("trModulos1").hidden = true;
        document.getElementById("trModulos2").hidden = false;
        document.getElementById("trModulos3").hidden = false;
    } else {
        document.getElementById("trModulos1").hidden = false;
        document.getElementById("trModulos2").hidden = true;
        document.getElementById("trModulos3").hidden = true;
    }
}

function llenaModulos(){
    var datos = new FormData();
    var curso = document.getElementById('cboPreCursos').value;
    datos.append('cboPreCurso', curso);

    $.ajax({
        url: 'funciones/fxDatosExternos.php',
        type: 'post',
        data: datos,
        contentType: false,
        processData: false,
        success: function(response) {
            document.getElementById('tdModulos').innerHTML = response;
        }
    });
}

function diaDeLaSemana(){
    var fecha = document.getElementById('dtpFechaFer').value;
    var anno = parseInt(fecha.substr(0,4));
	var mes = parseInt(fecha.substr(5,2));
	var dia = parseInt(fecha.substr(8,2));
    var date = new Date(mes + ', ' + dia + ', ' + anno + ' 12:00:00');
    var dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    document.getElementById('_easyui_textbox_input2').value = dias[date.getUTCDay()];
}

function preFeriados(){
    var datos = new FormData();
    var fechaIni = document.getElementById('dtpFechaIni').value;
    var fechaFin = document.getElementById('dtpFechaFin').value;
    datos.append('cursoFechaIni', fechaIni);
    datos.append('cursoFechaFin', fechaFin);

    $.ajax({
        url: 'funciones/fxDatosExternos.php',
        type: 'post',
        data: datos,
        contentType: false,
        processData: false,
        success: function(response) {
            var i;
            var j;
            var caracter;
            var texto = "";
            var mdFecha;
            var msDesc;
            var msDia;
            var mbInsertarFecha;
            var mbIngresaFecha = 0;
            var gridFeriado = $('#dgFER').datagrid('getData');
            var registros = $('#dgFER').datagrid('getRows').length - 1;

            for (i=0; i<response.length; i++)
            {
                mbInsertarFecha = 0;
                caracter = response.charAt(i);

                switch (caracter)
                {
                    case "%":
                        mdFecha = texto;
                        texto = "";
                        break;
                    case "@":
                        msDesc = texto;
                        texto = "";
                        break;
                    case "#":
                        msDia = texto;
                        texto = "";

                        if (msDia == "Domingo" && document.getElementById('chkDomingo').checked == true)
                            mbInsertarFecha = 1;
                        
                        if (msDia == "Lunes" && document.getElementById('chkLunes').checked == true)
                            mbInsertarFecha = 1;

                        if (msDia == "Martes" && document.getElementById('chkMartes').checked == true)
                            mbInsertarFecha = 1;

                        if (msDia == "Miércoles" && document.getElementById('chkMiercoles').checked == true)
                            mbInsertarFecha = 1;
                        
                        if (msDia == "Jueves" && document.getElementById('chkJueves').checked == true)
                            mbInsertarFecha = 1;

                        if (msDia == "Viernes" && document.getElementById('chkViernes').checked == true)
                            mbInsertarFecha = 1;
                        
                        if (msDia == "Sábado" && document.getElementById('chkSabado').checked == true)
                            mbInsertarFecha = 1;

                        for (j = 0; j <= registros; j++) //Evita la repetición de Fechas
                        {
                            if (mdFecha == gridFeriado.rows[j].fecha){
                                mbInsertarFecha = 0;
                            }
                        }

                        if (mbInsertarFecha == 1){
                            $('#dgFER').datagrid('appendRow', {
                                fecha: mdFecha,
                                dia: msDia,
                                motivo: msDesc
                            });
                            mbIngresaFecha = 1;
                        }
                        break;
                    default:
                        texto += caracter;
                }
            }

            if (mbIngresaFecha == 0){
                if (registros == 0)
                    $.messager.alert('INOFE', 'No hay Feriados que afecten el Curso.', 'info');
                else
                    $.messager.alert('INOFE', 'Ya se ingresaron los Feriados que afectan el Curso.', 'info');
            }
        }
    });
}

$('form').submit(function(e) {
    e.preventDefault();

    if (verificarFormulario() == true) {
        var texto;
        var datos;
        var registros;
        var i;
        var sinModulos = true;
        var gridModulos = $('#dgMOD').datagrid('getData');
        var gridFeriados = $('#dgFER').datagrid('getData');

        texto = '{"txtCodCurso":"' + document.getElementById("txtCodCurso").value + '", ';
        texto += '"cboCursoInatec":"' + document.getElementById("cboCursoInatec").value + '", ';
        texto += '"txtNomCurso":"' + document.getElementById("txtNomCurso").value + '", ';
        texto += '"dtpFechaIni":"' + document.getElementById("dtpFechaIni").value + '", ';
        texto += '"dtpFechaFin":"' + document.getElementById("dtpFechaFin").value + '", ';
        texto += '"dtpHoraIni":"' + document.getElementById("dtpHoraIni").value + '", ';
        texto += '"dtpHoraFin":"' + document.getElementById("dtpHoraFin").value + '", ';

        if (document.getElementById("chkDomingo").checked)
            texto += '"chkDomingo":"1", ';
        else
            texto += '"chkDomingo":"0", ';

        if (document.getElementById("chkLunes").checked)
            texto += '"chkLunes":"1", ';
        else
            texto += '"chkLunes":"0", ';

        if (document.getElementById("chkMartes").checked)
            texto += '"chkMartes":"1", ';
        else
            texto += '"chkMartes":"0", ';

            if (document.getElementById("chkMiercoles").checked)
            texto += '"chkMiercoles":"1", ';
        else
            texto += '"chkMiercoles":"0", ';

        if (document.getElementById("chkJueves").checked)
            texto += '"chkJueves":"1", ';
        else
            texto += '"chkJueves":"0", ';

        if (document.getElementById("chkViernes").checked)
            texto += '"chkViernes":"1", ';
        else
            texto += '"chkViernes":"0", ';

        if (document.getElementById("chkSabado").checked)
            texto += '"chkSabado":"1", ';
        else
            texto += '"chkSabado":"0", ';

        texto += '"cboTipo":"' + document.getElementById("cboTipo").value + '",';
        texto += '"cboTipoAsistencia":"' + document.getElementById("cboTipoAsistencia").value + '",';
        texto += '"cboTurno":"' + document.getElementById("cboTurno").value + '",'; 
        texto += '"txtConvocatoria":"' + document.getElementById("txtConvocatoria").value + '",';
        texto += '"txnGrupo":"' + document.getElementById("txnGrupo").value + '",';

        if (document.getElementById("optMoneda1").checked)
            texto += '"optMoneda":"0", ';
        else
            texto += '"optMoneda":"1", ';
        
        texto += '"txnValor":"' + document.getElementById("txnValor").value + '",';
        texto += '"txnMatricula":"' + document.getElementById("txnMatricula").value + '",';
        texto += '"txnCuota":"' + document.getElementById("txnCuota").value + '", ';
        texto += '"txnCertificacion":"' + document.getElementById("txnCertificacion").value + '",';
        texto += '"txnMora":"' + document.getElementById("txnMora").value + '",';
        texto += '"txnMaximo":"' + document.getElementById("txnMaximo").value + '",';

        if (document.getElementById("optCerradoAntes1").checked)
            texto += '"optCerradoAntes":"0",';
        else
            texto += '"optCerradoAntes":"1",';

        if (document.getElementById("optActivo1").checked)
            texto += '"optActivo":"0",';
        else
            texto += '"optActivo":"1",';

        if (document.getElementById("optCertificar1").checked)
            texto += '"optCertificar":"0",';
        else
            texto += '"optCertificar":"1",';

        if (document.getElementById("optCertDigital1").checked)
            texto += '"optCertDigital":"0",';
        else
            texto += '"optCertDigital":"1",';

        /*MODULOS*/
        registros = $('#dgMOD').datagrid('getRows').length - 1;

        if (registros >= 0) {
            sinModulos = false;
            texto += '"gridModulos": [';
            for (i = 0; i <= registros; i++) {
                texto += '{"curso":"' + gridModulos.rows[i].curso + '", "numero":"' + gridModulos.rows[i].numero + '", "codDocente":"' + gridModulos.rows[i].codDocente + '", "nombre":"' + gridModulos.rows[i].nombre + '", "fechaIni":"' + gridModulos.rows[i].fechaIni + '", "fechaFin":"' + gridModulos.rows[i].fechaFin + '", "valor":"' + gridModulos.rows[i].valor + '", "modulo":"' + gridModulos.rows[i].modulo;
                if (i == registros)
                    texto += '"}],';
                else
                    texto += '"},';
            }
        }

        /*FERIADOS*/
        registros = $('#dgFER').datagrid('getRows').length - 1;

        if (registros >= 0) {
            texto += '"gridFeriados": [';
            for (i = 0; i <= registros; i++) {
                texto += '{"fecha":"' + gridFeriados.rows[i].fecha + '", "dia":"' + gridFeriados.rows[i].dia + '", "motivo":"' + gridFeriados.rows[i].motivo;

                if (i == registros)
                    texto += '"}]}';
                else
                    texto += '"},';
            }
        } 
        else 
        {
            if (texto.slice(-1) == ',')
            {
                texto = texto.substr(0, texto.length - 1) + '}'
            }
        }

        datos = JSON.parse(texto);

        $.ajax({
            url: 'catCursos.php',
            type: 'post',
            data: datos
        })
        .done(function() {
            location.href = "gridCursos.php";
        })
        .fail(function() {
            console.log('Error')
        });
    }
});

window.onload = function() {
    if (document.getElementById("txtCodCurso").value == ""){
        Convocatoria();
    }
    llenaModulos();
    preConfigurados();
    diaDeLaSemana();
}
</script>
<?php
function fxEscribeJsonModulo($Curso)
{
	if ($Curso == "")
		$nombreArchivo = "CUR0000000A.json";
	else
		$nombreArchivo = $Curso . "A.json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$mDatos = fxDevuelveDetModulo($Curso);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");

	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"modulo":"' . rtrim($Fila['MODULO_REL']) . '", ');
		fwrite($archivo, '"curso":"' . rtrim($Fila['CURSO_REL']) . '", ');
        fwrite($archivo, '"codDocente":"' . rtrim($Fila['DOCENTE_REL']) . '", ');
        fwrite($archivo, '"numero":"' . rtrim($Fila['NUMERO_021']) . '", ');
		fwrite($archivo, '"docente":"' . rtrim($Fila['NOMBRE_100']) . '", ');
		fwrite($archivo, '"nombre":"' . rtrim($Fila['NOMBRE_021']) . '", ');
		fwrite($archivo, '"fechaIni":"' . rtrim($Fila['FECHAINI_021']) . '", ');
        fwrite($archivo, '"fechaFin":"' . rtrim($Fila['FECHAFIN_021']) . '", ');
        fwrite($archivo, '"valor":"' . rtrim($Fila['VALOR_021']) . '", ');
        fwrite($archivo, '"plan":"' . rtrim($Fila['PLAN']) . '"');

		if ($i == $numRegistros)
			fwrite($archivo, "}" . PHP_EOL);
		else
			fwrite($archivo, "}," . PHP_EOL);
	}
	fwrite($archivo, "]");
	fclose($archivo);
	
	return($nombreArchivo);
}

function fxEscribeJsonFeriado($Curso)
{
	if ($Curso == "")
		$nombreArchivo = "CUR0000000B.json";
	else
		$nombreArchivo = $Curso . "B.json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
    //Escribe el Json
	$mDatos = fxDevuelveDetFeriado($Curso);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"fecha":"' . trim($Fila['FECHA_022']) . '", ');
		fwrite($archivo, '"dia":"' . trim($Fila['DIA_022']) . '", ');
		fwrite($archivo, '"motivo":"' . trim($Fila['MOTIVO_022']) . '"');

		if ($i == $numRegistros)
			fwrite($archivo, "}" . PHP_EOL);
		else
			fwrite($archivo, "}," . PHP_EOL);
	}
	fwrite($archivo, "]");
	fclose($archivo);

	return($nombreArchivo);
}
?>