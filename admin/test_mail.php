<?php

require_once __DIR__ . '/../includes/email.php';

$email="TU_EMAIL@gmail.com";

if(
    enviar_email(
        $email,
        "Prueba SMTP",
        "Correo de prueba",
        "<h2>SMTP funcionando correctamente</h2>"
    )
){
    echo "OK";
}else{
    echo "ERROR";
}