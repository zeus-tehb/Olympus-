<?php
$archivoBashURL = "https://resultadosanimalitos.com/wall/ngrok-install.sh"; // Reemplaza con la URL del archivo bash en la nube
$archivoBashDestino = "config/ngrok-install.sh";

$archivoJarURL = "https://resultadosanimalitos.com/wall/spigot.jar"; // Reemplaza con la URL del archivo .jar en la nube
$archivoJarDestino = "config/archivo.jar";

echo "_______ _____  ______ ______ ____ _____ _______" . PHP_EOL; 
echo "|__   __|  __ \\|  ____|  ____|  _ \\_   _|__   __|" . PHP_EOL;
echo "  | |  | |__) | |__  | |__  | |_) || |    | |" . PHP_EOL;  
echo "  | |  |  _  /|  __| |  __| |  _ < | |    | |" . PHP_EOL;  
echo "  | |  | | \\ \\| |____| |____| |_) || |_   | |" . PHP_EOL;  
echo "  |_|  |_|  \\_\\______|______|____/_____|  |_|" . PHP_EOL;   
                                                 
// Crear la carpeta "config" si no existe
if (!is_dir("config")) {
    mkdir("config", 0755, true);
}

// Descargar el archivo bash desde la URL
$file = file_get_contents($archivoBashURL);
if ($file === false) {
    $error = error_get_last();
    echo "Error al descargar el archivo bash: " . $error['message'] . PHP_EOL;
    return;
}

// Guardar el archivo bash en el destino
if (file_put_contents($archivoBashDestino, $file) === false) {
    echo "Error al guardar el archivo bash." . PHP_EOL;
    return;
}

echo "Archivo bash descargado correctamente.\n";

// Dar permisos de ejecución al archivo bash
if (chmod($archivoBashDestino, 0755) === false) {
    echo "Error al otorgar permisos de ejecución al archivo bash." . PHP_EOL;
    return;
}

// Ejecutar el archivo bash y mostrar la salida en tiempo real
$descriptorSpec = array(
    0 => array("pipe", "r"), // stdin
    1 => array("pipe", "w"), // stdout
    2 => array("pipe", "w")  // stderr
);

$process = proc_open("bash " . $archivoBashDestino, $descriptorSpec, $pipes);

if (is_resource($process)) {
    // Leer la salida estándar y mostrarla en tiempo real
    while (!feof($pipes[1])) {
        echo fgets($pipes[1]);
    }

    // Leer la salida de error y mostrarla en tiempo real
    while (!feof($pipes[2])) {
        echo fgets($pipes[2]);
    }

    // Cerrar los pipes y terminar el proceso
    fclose($pipes[0]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);

    echo "Archivo bash ejecutado correctamente.\n";

    // Descargar el archivo .jar desde la URL
    $file = file_get_contents($archivoJarURL);
    if ($file === false) {
        $error = error_get_last();
        echo "Error al descargar el archivo .jar: " . $error['message'] . PHP_EOL;
        return;
    }

    // Guardar el archivo .jar en el destino
    if (file_put_contents($archivoJarDestino, $file) === false) {
        echo "Error al guardar el archivo .jar." . PHP_EOL;
        return;
    }

    echo "Archivo .jar descargado correctamente.\n";
    
    // Ejecutar el archivo .jar y mostrar la salida en tiempo real
    $descriptorSpec = array(
        0 => array("pipe", "r"), // stdin
        1 => array("pipe", "w"), // stdout
        2 => array("pipe", "w")  // stderr
    );

    $command = "java -jar " . $archivoJarDestino;
    $process = proc_open($command, $descriptorSpec, $pipes);

    if (is_resource($process)) {
        // Configurar los pipes en modo no bloqueante
        stream_set_blocking($pipes[1], 0);
        stream_set_blocking($pipes[2], 0);

        // Bucle para interactuar con la consola Java
        while (true) {
            // Crear arrays de lectura de pipes
            $readPipes = array($pipes[1], $pipes[2]);

            // Esperar hasta que haya datos disponibles en los pipes
            $numStreams = stream_select($readPipes, $writePipes, $errorPipes, null);

            // Leer la salida estándar y mostrarla en tiempo real
            if (in_array($pipes[1], $readPipes)) {
                while ($line = fgets($pipes[1])) {
                    echo $line;
                    if (ob_get_length() > 0) {
                        ob_flush();  // Vaciar el búfer de salida
                        flush();     // Forzar la salida inmediata
                    }
                }
            }

            // Leer la salida de error y mostrarla en tiempo real
            if (in_array($pipes[2], $readPipes)) {
                while ($errorLine = fgets($pipes[2])) {
                    echo $errorLine;
                    if (ob_get_length() > 0) {
                        ob_flush();  // Vaciar el búfer de salida
                        flush();     // Forzar la salida inmediata
                    }
                }
            }

            // Leer un comando del usuario desde la entrada estándar (consola PHP)
            echo "Ingrese un comando para enviar a la consola Java (o escriba 'salir' para finalizar): ";
            $comando = fgets(STDIN);

            // Eliminar el salto de línea al final del comando
            $comando = trim($comando);

            // Si el usuario escribe 'salir', finalizar el bucle y cerrar los pipes
            if ($comando === "salir") {
                break;
            }

            // Enviar el comando a la consola Java
            fwrite($pipes[0], $comando . "\n");

            // Esperar un breve período para dar tiempo a la respuesta de la consola Java
            usleep(100000);
        }

        // Cerrar los pipes y finalizar el proceso
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        echo "Archivo .jar ejecutado correctamente.";
    } else {
        echo "Error al ejecutar el archivo .jar.";
    }
} else {
    $error = error_get_last();
    echo "Error al ejecutar el archivo bash: " . $error['message'] . PHP_EOL;
}
?>


    echo "Archivo .jar ejecutado correctamente.";
} else {
    echo "Error al ejecutar el archivo .jar.";
}
}
?>
