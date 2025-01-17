<?php

session_start();
// Gerar token CSRF se ainda não estiver definido
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include_once('vendor/sonata-project/google-authenticator/src/FixedBitNotation.php');
include_once('vendor/sonata-project/google-authenticator/src/GoogleAuthenticatorInterface.php');
include_once('vendor/sonata-project/google-authenticator/src/GoogleAuthenticator.php');
include_once('vendor/sonata-project/google-authenticator/src/GoogleQrUrl.php');

// Utilize o autoloader do Composer se a biblioteca for instalada via Composer
require 'vendor/autoload.php';


// Gerar a chave secreta de maneira segura (preferencialmente da variável de ambiente ou banco de dados)
$secret = getenv('GOOGLE_AUTH_SECRET') ?: 'XVG2XZ3ZGK7ZGK7Z';  // Exemplo de fallback

$g = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
$qrCodeUrl = \Sonata\GoogleAuthenticator\GoogleQrUrl::generate('School Library', $secret, 'SchoolLibrary');

// Gerar token CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar se o token foi enviado e validar o CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Token CSRF inválido');
    }

    $token = $_POST['token'];

    // Validar se o token é numérico
    if (!ctype_digit($token)) {
        $message = 'Token inválido';
    } else if ($g->checkCode($secret, $token)) {
        $message = 'Autenticação Aprovada!';
    } else {
        $message = 'Token inválido';
    }

    echo "<script>
            alert('$message');
            window.location.href = window.location.href;
          </script>";
    die();
}



?>