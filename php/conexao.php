<?php
declare(strict_types=1);

// Dados de acesso ao MySQL do XAMPP (usuário root sem senha)
function conectar(): PDO
{
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=giro_estoque;charset=utf8mb4', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        responder(false, ['_geral' => 'Erro ao conectar com o banco de dados.'], [], 500);
    }
}
