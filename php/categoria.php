<?php
declare(strict_types=1);
require __DIR__ . '/util.php';
iniciar();
verificarLogin();

// só lista as categorias, usadas no select da tela de produtos
$pdo = conectar();
$sql = $pdo->query('SELECT * FROM categorias ORDER BY nome');
responder(true, [], ['lista' => $sql->fetchAll()]);
