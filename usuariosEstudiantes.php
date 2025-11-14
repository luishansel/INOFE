<?php
    require_once ("funciones/fxGeneral.php");
    set_time_limit(1200);
    $m_cnx_MySQL = fxAbrirConexion();
    
    $msConsulta = "select MATRICULA_REL, CORREO_010, CONCAT_WS(' ',NOMBRES_010, APELLIDOS_010) as ESTUDIANTE from KDSA030A, KDSA010A, KDSA020A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA030A.CURSO_REL = KDSA020A.CURSO_REL AND ACTIVO_020 = 1 AND ESTADO_030 = 0 order by MATRICULA_REL";
    $mDatos = $m_cnx_MySQL->prepare($msConsulta);
    $mDatos->execute();
    
    while($mFila=$mDatos->fetch())
    {
        $msMatricula = $mFila["MATRICULA_REL"];
        $msCorreo = $mFila["CORREO_010"];
        $msNombre = $mFila["ESTUDIANTE"];
        $msEncriptado = crypt($msMatricula, '_appwKDSA');

        $msConsulta = "select * from KDSA002A where USUARIO_REL = ?";
        $mResultado = $m_cnx_MySQL->prepare($msConsulta);
        $mResultado->execute([$msMatricula]);
        $mnRegistros = $mResultado->rowCount();

        if ($mnRegistros == 0)
        {
		    $msConsulta = "insert into KDSA002A (USUARIO_REL, NOMBRE_002, CORREO_002, CLAVE_002, ACADEMICO_002, SUPERVISOR_002, ACTIVO_002) values(?, ?, ?, ?, ?, ?, ?)";
		    $mUsuario = $m_cnx_MySQL->prepare($msConsulta);
            $mUsuario->execute([$msMatricula, $msNombre, $msCorreo, $msEncriptado, 0, 0, 1]);
        }
    }
    echo("Finalizado");
?>