<?php
declare(strict_types=1);

// Funções compartilhadas pelos endpoints. Este arquivo não gera HTML: toda
// resposta é JSON e quem desenha a tela é o JavaScript (js/app.js).

function iniciar(): void
{
    header('Content-Type: application/json; charset=utf-8');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responder(false, ['_geral' => 'Use o método POST.'], [], 405);
    }
}

function responder(bool $ok, array $erros = [], array $dados = [], int $status = 200): void
{
    http_response_code($status);
    echo json_encode(
        ['ok' => $ok, 'erros' => $erros, 'dados' => $dados],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

function campo(string $nome): string
{
    return trim((string) ($_POST[$nome] ?? ''));
}

function obrigatorios(array $nomes, array &$erros): void
{
    foreach ($nomes as $nome) {
        if (campo($nome) === '') {
            $erros[$nome] = 'Campo obrigatório.';
        }
    }
}

function soDigitos(string $valor): string
{
    return preg_replace('/\D/', '', $valor) ?? '';
}

function validarCpf(string $cpf): bool
{
    $cpf = soDigitos($cpf);
    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    for ($t = 9; $t < 11; $t++) {
        $soma = 0;
        for ($i = 0; $i < $t; $i++) {
            $soma += (int) $cpf[$i] * ($t + 1 - $i);
        }
        $digito = ((10 * $soma) % 11) % 10;
        if ((int) $cpf[$t] !== $digito) {
            return false;
        }
    }
    return true;
}

function validarCnpj(string $cnpj): bool
{
    $cnpj = soDigitos($cnpj);
    if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
        return false;
    }
    foreach ([12, 13] as $t) {
        $pesos = $t === 12
            ? [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
            : [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;
        for ($i = 0; $i < $t; $i++) {
            $soma += (int) $cnpj[$i] * $pesos[$i];
        }
        $resto = $soma % 11;
        $digito = $resto < 2 ? 0 : 11 - $resto;
        if ((int) $cnpj[$t] !== $digito) {
            return false;
        }
    }
    return true;
}

function validarEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validarTelefone(string $telefone): bool
{
    $n = strlen(soDigitos($telefone));
    return $n === 10 || $n === 11;
}

function numero(string $valor): ?float
{
    $valor = str_replace(',', '.', $valor);
    return is_numeric($valor) ? (float) $valor : null;
}

function dataValida(string $data): ?DateTime
{
    $d = DateTime::createFromFormat('Y-m-d', $data);
    return ($d && $d->format('Y-m-d') === $data) ? $d : null;
}
