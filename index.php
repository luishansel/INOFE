<?php
	#Inicia la sesión antes de la respuesta HTML
	session_start();
	$_SESSION["gnVerifica"] = 0;
	require_once ("funciones/fxGeneral.php");
	require_once ("funciones/fxUsuarios.php");
?>
<!DOCTYPE html>
<html lang="ES-NI" class="no-js">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="description" content="Control de Pagos. Aplicación web de INOFE."/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<link rel="icon" href="imagenes/favicon.png" />
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="css/easyui.css" />
<link rel="stylesheet" type="text/css" href="css/icon.css" />
<link rel="stylesheet" type="text/css" href="css/StyleINOFE.css"/>

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
	if (isset($_POST['txtUsuario']))
	{
		$msUsuario = htmlentities($_POST['txtUsuario']);
		$msClave = $_POST['txtClave'];
        $msEncriptado = crypt($msClave, '_appwKDSA');
		$m_cnx_MySQL = fxAbrirConexion();
        
		$msConsulta = "Select CLAVE_002 from KDSA002A where USUARIO_REL =? and ACTIVO_002 = 1";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msUsuario]);
		$Fila = $mDatos->fetch();
		$msClaveBD = $Fila["CLAVE_002"];
		$mbResultado = hash_equals($msClaveBD, $msEncriptado);

		if ($mbResultado == true) //El usuario pasó la validación
		{
			$msConsulta = "Select NOMBRE_002 from KDSA002A where USUARIO_REL =?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msUsuario]);
			$Fila = $mDatos->fetch();
			$_SESSION["gsNombre"] = $Fila["NOMBRE_002"];
			$_SESSION["gsUsuario"] = $msUsuario;
			$_SESSION["gsClave"] = $msEncriptado;
			$_SESSION["gnVerifica"] = 1;
			$_SESSION["gnAppID"] = 1; //App INOFE
			$_SESSION["gsDocente"] = "";

			$msConsulta = "Select DOCENTE_REL from KDSA100A where USUARIO_REL =?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msUsuario]);
			$mnConteo = $mAuxiliar->rowCount();
			$Administrador = fxVerificaAdministrador();

			if ($mnConteo <> 0 and $Administrador == 0)
			{
?>				
				<script>
					$.messager.alert('INOFE','Su Usuario no está autorizado.','warning');
					$("a").click(function(){window.location="index.php"});
				</script>
<?php
			}
			else
			{
			     fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA000A", $_SESSION["gsUsuario"], "", "Sesión inicio");
				echo('<meta http-equiv="Refresh" content="0;url=frmInicio.php">');
			}
		}
		else //El usuario no pasó la validación
		{
			$msConsulta = "Select * from KDSA002A where USUARIO_REL =? and ACTIVO_002 = 1";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msUsuario]);
			$mnRegistros = $mAuxiliar->rowCount();

			if ($mnRegistros <> 0) //El usuario está Activo
			{
?>
				<script>
					$.messager.alert('INOFE','Usuario no autorizado o Contraseña errónea.','warning');
					$("a").click(function(){window.location="index.php"});
				</script>
<?php
			}
			else
			{
?>
				<script>
					$.messager.alert('INOFE','El Usuario está inactivo.','warning');
					$("a").click(function(){window.location="index.php"});
				</script>
<?php
			}
		}
	}
	else
	{
?>
    <div class="container">
        <section class="row align-items-center">
            <div id="DivLogIn" class="col-xs-8 col-xs-offset-2 col-md-4 col-md-offset-4">
                <form method="post" action="index.php">
                    <img src="imagenes/headerLogin.png" width="30%" style="padding-top:3%; padding-bottom:3%" />
                    <div class="form-group">
                        <label class="sr-only" for="txtUsuario">Usuario</label>
                        <input type="text" class="form-control" id="txtUsuario" name="txtUsuario" placeholder="Usuario" value=""/>
                    </div>
                    <div class="form-group">
                        <label class="sr-only" for="txtClave">Contraseña</label>
                        <input type="password" class="form-control" id="txtClave" name="txtClave" placeholder="Contraseña" value=""/>
                    </div>
                    <div class="form-group" align="right">
                        <input type="submit" class="btn btn-warning" name="Aceptar" value="Aceptar"/>
                    </div>
                </form>
			</div>
        </section>
    </div>
</body>
</html>
<?php
	}
?>