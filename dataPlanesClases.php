<?php
    if (isset($_GET["KDSA"]))
    {
        require_once ("funciones/fxGeneral.php");
        $msCurso = $_GET["KDSA"];

        //Escribe el Json
        $msConsulta = "select MODULO_REL as modulo, NOMBRE_021 as nombreModulo, NOMBRE_100 as docente from KDSA021A join KDSA100A on KDSA021A.DOCENTE_REL = KDSA100A.DOCENTE_REL where CURSO_REL = ?";
        $m_cnx_MySQL = fxAbrirConexion();
        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCurso]);
        $data = array();

        while ($Fila = $mDatos->fetch())
        {
            array_push($data, $Fila);
        }
        echo(json_encode($data));
    }
?>