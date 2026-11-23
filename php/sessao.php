<?php
declare(strict_types=1);
require __DIR__ . '/util.php';
iniciar();
verificarLogin();

// o JS chama esse arquivo quando abre cada página para saber quem está logado
responder(true, [], ['usuario' => $_SESSION['usuario']]);
