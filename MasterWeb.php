<?php
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');

//Sólo para efectos de depuración
set_time_limit (0);
?>
<!DOCTYPE html>
<html lang="ES-NI">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="description" content="Control Administrativo y Académico de INOFE."/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<link rel="icon" href="imagenes/favicon.png" />
<link rel="stylesheet" type="text/css" href="css/bootstrap441.css" />
<link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="bootstrap/css/jquery.bootgrid.css" />
<link rel="stylesheet" type="text/css" href="css/easyui.css" />
<link rel="stylesheet" type="text/css" href="css/icon.css" />
<link rel="stylesheet" type="text/css" href="css/StyleINOFE.css"/>

<script src="js/jquery.min.js"></script>
<script src="js/jquery-1.9.1.min.js"></script>
<script src="js/jquery.easyui.min.js"></script>
<script src="js/datagrid-detailview.js"></script>
<script src="js/jquery.redirect.js"></script>
<script src="bootstrap/js/bootstrap.bundle.js"></script>
<script src="bootstrap/js/bootstrap.js"></script>
<script src="bootstrap/js/moderniz.2.8.1.js"></script>


<script>
    // jquery ready start
    $(document).ready(function() {
        //////////////////////// Prevent closing from click inside dropdown
        $(document).on('click', '.dropdown-menu', function (e) {
        e.stopPropagation();
        });

        // make it as accordion for smaller screens
        if ($(window).width() < 992) {
            $('.dropdown-menu a').click(function(e){
                e.preventDefault();
                if($(this).next('.submenu').length){
                    $(this).next('.submenu').toggle();
                }
                $('.dropdown').on('hide.bs.dropdown', function () {
                $(this).find('.submenu').hide();
                })
            });
        }
    }); // jquery end
</script>

<style type="text/css">
	@media (min-width: 992px){
		.dropdown-menu .dropdown-toggle:after{
			border-top: .3em solid transparent;
		    border-right: 0;
		    border-bottom: .3em solid transparent;
		    border-left: .3em solid;
		}

		.dropdown-menu .dropdown-menu{
			margin-left:0; margin-right: 0;
		}

		.dropdown-menu li{
			position: relative;
		}
		.nav-item .submenu{ 
			display: none;
			position: absolute;
			left:100%; top:-7px;
		}
		.nav-item .submenu-left{ 
			right:100%; left:auto;
		}

		.dropdown-menu > li:hover{ background-color: #f1f1f1 }
		.dropdown-menu > li:hover > .submenu{
			display: block;
		}
	}
</style>

<title>Aplicación web INOFE</title>
</head>

<body>
<div id="cabecera">
    <div class="container-fluid">
        <div class="row">
            <img src="imagenes/header.png" width="100%" />
        </div>
    
        <div class="row">
            <div class="col-md-9">
                <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main_nav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="main_nav">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a href="frmInicio.php" class="nav-link">Inicio</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">Catálogos</a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item dropdown-toggle" href="#">Académico</a>
                                        <ul class="submenu dropdown-menu">
                                            <li><a class="dropdown-item" href="gridEstudiantes.php">Estudiantes</a></li>
                                            <li><a class="dropdown-item" href="gridCursos.php">Cursos</a></li>
                                            <!--
                                            <li><a href="gridCursosInatec.php">Cursos INATEC</a></li>
                                            -->
                                            <li><a class="dropdown-item" href="gridDocentes.php">Docentes</a></li>
                                        </ul>
                                    </li>
                                    <li><a class="dropdown-item" href="gridCobros.php">Cobros</a></li>
                                    <li><a class="dropdown-item" href="gridProspectos.php">Prospectos</a></li>
                                </ul>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">Procesos</a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item dropdown-toggle" href="#">Financiero</a>
                                        <ul class="submenu dropdown-menu">
                                            <li><a class="dropdown-item" href="procCobrosIndividuales.php">Cobros individuales</a></li>
                                            <li><a class="dropdown-item" href="gridPagos.php">Pagos de los estudiantes</a></li>
                                            <li><a class="dropdown-item" href="gridOtrosIngresos.php">Otros ingresos</a></li>
                                            <li><a class="dropdown-item" href="gridCobrosEmpresa.php">Cobros empresariales</a></li>
                                            <li><a class="dropdown-item" href="gridPagosEmpresa.php">Pagos empresariales</a></li>
                                            <li><a class="dropdown-item" href="procCierreCaja.php">Cierre de caja</a></li>
                                            <!--
                                            <li><a href="gridCobrosInatec.php">Cobros INATEC</a></li>
                                            <li><a href="gridPagosInatec.php">Pagos INATEC</a></li>
                                            -->
                                        </ul>
                                    </li>
                                    <li>
                                        <a class="dropdown-item dropdown-toggle" href="#">Docencia</a>
                                        <ul class="submenu dropdown-menu">
                                            <li><a class="dropdown-item" href="gridPlanClase.php">Planificación de clases</a></li>
                                            <li><a class="dropdown-item" href="gridAsistencia.php">Asistencias</a></li>
                                            <li><a class="dropdown-item" href="gridCalificaciones.php">Calificaciones</a></li>
                                            <li><a class="dropdown-item" href="gridIncidencias.php">Incidencias</a></li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a class="dropdown-item dropdown-toggle" href="#">Certificación</a>
                                        <ul class="submenu dropdown-menu">
                                            <li><a class="dropdown-item" href="gridTomos.php">Administración de tomos</a></li>
                                            <li><a class="dropdown-item" href="frmCertificacion.php">Control de certificaciones</a></li>
                                        </ul>
                                    </li>
                                    <li><a class="dropdown-item" href="gridMatricula.php">Matrícula</a></li>
                                    <li><a class="dropdown-item" href="gridEstadoMatricula.php">Estado de las matrículas</a></li>
                                    <li><a class="dropdown-item" href="gridProformas.php">Proformas</a></li>
                                    <li><a class="dropdown-item" href="gridSeguimiento.php">Seguimiento de prospectos</a></li>
                                    <li><a class="dropdown-item" href="frmRegulacionAsistencia.php">Regulación de asistencias</a></li>
                                    <li><a class="dropdown-item" href="frmCertificacion.php">Certificación</a></li>
                                </ul>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">Reportes y Consultas</a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item dropdown-toggle" href="#">Académico</a>
                                        <ul class="submenu dropdown-menu">
                                            <li><a class="dropdown-item" href="frmHojaMatricula.php">Hoja de matrícula</a></li>
                                            <li><a class="dropdown-item" href="frmMatriculados.php">Estudiantes matriculados</a></li>
                                            <li><a class="dropdown-item" href="frmMatPeriodo.php">Matriculados por período</a></li>
                                            <li><a class="dropdown-item" href="frmAsistencia.php">Asistencia estudiantil</a></li>
                                            <li><a class="dropdown-item" href="frmCalificaciones.php">Calificaciones</a></li>
                                            <li><a class="dropdown-item" href="frmPlanesClases.php">Planes de Clases</a></li>
                                            <li><a class="dropdown-item" href="consCursos.php">Cursos activos</a></li>
                                            <li><a class="dropdown-item" href="frmAsistenciaSemanal.php">Asistencia por período</a></li>
                                            <li><a class="dropdown-item" href="frmMatGeneral.php">Matrícula general</a></li>
                                            <li><a class="dropdown-item" href="frmAlumnoActivo.php">Constancia de alumno activo</a></li>
                                            <li><a class="dropdown-item" href="frmEstadoMatricula.php">Estado de las matrículas</a></li>
                                            <li><a class="dropdown-item" href="frmDeserciones.php">Deserciones</a></li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a class="dropdown-item dropdown-toggle" href="#">Financiero</a>
                                        <ul class="submenu dropdown-menu">
                                            <li><a class="dropdown-item" href="frmEstadoCuentas.php">Estado de cuentas del estudiante</a></li>
                                            <li><a class="dropdown-item" href="frmIngresos.php">Ingresos</a></li>
                                            <li><a class="dropdown-item" href="frmProyecciones.php">Proyecciones</a></li>
                                            <li><a class="dropdown-item" href="frmCtasPorCobrar.php">Cuentas por cobrar</a></li>
                                            <li><a class="dropdown-item" href="frmSolventes.php">Estudiantes solventes</a></li>
                                            <li><a class="dropdown-item" href="frmProximosPagos.php">Próximos Pagos de Estudiantes</a></li>
                                            <li><a class="dropdown-item" href="frmAsistenciaPeriodo.php">Asistencias de los Docentes</a></li>
                                            <li><a class="dropdown-item" href="frmPagosDocentes.php">Soporte de Pagos a Docentes</a></li>
                                            <li><a class="dropdown-item" href="frmCobrosCurso.php">Cobros de los cursos</a></li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a class="dropdown-item dropdown-toggle" href="#">Certificación</a>
                                        <ul class="submenu dropdown-menu">
                                            <li><a class="dropdown-item" href="frmLibroActas.php">Libro de actas</a></li>
                                        </ul>
                                    </li>
                                    <li><a class="dropdown-item" href="frmBitacora.php">Bitácora</a></li>
                                </ul>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">Herramientas</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="gridUsuarios.php">Usuarios</a></li>
                                    <li><a class="dropdown-item" href="gridGrupos.php">Grupos</a></li>
                                    <li><a class="dropdown-item" href="gridCfgModulo.php">Módulos de los Cursos</a></li>
                                    <li><a class="dropdown-item" href="gridFeriados.php">Días no hábiles</a></li>
                                    <li><a class="dropdown-item" href="hrrMatriculaEnLinea.php">Enlace para Matrícula en Linea</a></li>
                                    <!--li><a href="hrrCobroIndividual.php">Ajuste de Cobros individuales</a></li-->
                                    <li><a class="dropdown-item" href="gridFirmas.php">Firmas para constancia de alumno</a></li>
                                    <li><a class="dropdown-item" href="hrrCobros.php">Envío masivo de cobros</a></li>
                                    <li><a class="dropdown-item" href="gridDocCurso.php">Documentos obligatorios de los cursos</a></li>
                                </ul>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="index.php">Cerrar sesión</a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
            <div class="col-md-3 text-right">
                <div style="display:inline-block; vertical-align:middle; margin-left:1%; margin-top:4%">
                    <img src="imagenes/user.png" width="90%" />
                </div>
                <div style="display:inline-block; vertical-align:middle; margin-top:3%; color: rgb(255, 255, 255)">
                    <?php echo($_SESSION["gsNombre"]) ?>
                </div>
            </div>
        </div>
	</div>
</div>