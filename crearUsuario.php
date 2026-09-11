<?php 
	require_once ("Funciones/fxGeneral.php");
	require_once ("Funciones/fxUsuarios.php");
?>
<!DOCTYPE html>
<html lang="ES-NI">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="description" content="Control de Pagos. Aplicación web de KDSA."/>
<meta name="keywords" content="KDSA, Managua, Nicaragua, educacion, capacitacion, cursos, contabilidad, enfermeria, computacion"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<link rel="icon" href="imagenes/favicon.png"/>
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet" type="text/css" href="css/easyui.css" />
<link rel="stylesheet" type="text/css" href="css/icon.css" />
<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="css/StyleKDSA.css"/>

<script src="js/jquery.min.js"></script>
<script src="js/jquery-3.4.1.js"></script>
<script src="js/jquery.easyui.min.js"></script>
<script src="bootstrap/js/bootstrap.js"></script>
<title>Aplicación web INOFE</title>
</head>

<body>
	<div id="cabecera">
        <div class="container-fluid">
            <img src="imagenes/header.png" width="100%" />
        </div>
    </div>
 <?php   
	if (isset($_POST["Guardar"]))
	{
		$Codigo = $_POST["CodUsuario"];
		$Nombre = $_POST["NomUsuario"];
		$Clave = $_POST["Clave"];
		if ($_POST["Supervisor"]="on")
			$SupervisorDB = 1;
		else
			$SupervisorDB = 0;

		if (fxExisteUsuario($Codigo) == 0)
		{
			fxGuardarUsuario ($Codigo, $Nombre, "", $Clave, 0, $SupervisorDB);
            ?>
    			<script>
    				$.messager.alert('INOFE','El Usuario se guardó.','warning');
    				$("a").click(function(){window.location="crearUsuario.php"});
    			</script>
    		<?php
		}
        else
        {
        ?>
			<script>
				$.messager.alert('INOFE','El Usuario ya existe.','warning');
				$("a").click(function(){window.location="crearUsuario.php"});
			</script>
		<?php  
        }
	}
	else
	{
?>  
    <div class="container">
        <section class="row align-items-center">
            <div class="col-4">
                &nbsp;
            </div>
            <div id="DivLogIn" class="col-4">
                <form method="post" action="crearUsuario.php">
                    <img src="imagenes/header.png" width="100%" style="padding-top:3%; padding-bottom:3%" />
                    <div class="form-group">
                        <label class="sr-only" for="CodUsuario">Usuario</label>
                        <input type="text" class="form-control" id="CodUsuario" name="CodUsuario" placeholder="Usuario" value=""/>
                    </div>
                    <div class="form-group">
                        <label class="sr-only" for="NomUsuario">Nombre</label>
                        <input type="text" class="form-control" id="NomUsuario" name="NomUsuario" placeholder="Nombre del usuario" value=""/>
                    </div>
                    <div class="form-group">
                        <label class="sr-only" for="Clave">Contraseña</label>
                        <input type="text" class="form-control" id="Clave" name="Clave" placeholder="Contraseña" value=""/>
                    </div>
                    <div class="checkbox">
                        <label>
                        	<input type="checkbox" id="Supervisor" name="Supervisor"/> Supervisor
                        </label>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-warning" name="Guardar" value="Guardar"/>
                    </div>
                </form>
			</div>
            <div class="col-4">
                &nbsp;
            </div>
        </section>
    </div>
</body>
</html>
<?php
	}
?>