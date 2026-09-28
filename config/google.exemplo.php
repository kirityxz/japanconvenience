<?php
// ============================================================
// Login com Google (OAuth 2.0) — ARQUIVO DE EXEMPLO
// Copie para `google.php` na mesma pasta e preencha:
//   cp config/google.exemplo.php config/google.php
// O `google.php` real está no .gitignore e NÃO vai pro GitHub.
// ============================================================

define('GOOGLE_CLIENT_ID', 'COLE_SEU_CLIENT_ID_AQUI');
define('GOOGLE_CLIENT_SECRET', 'COLE_SEU_CLIENT_SECRET_AQUI');
define('GOOGLE_REDIRECT_URI', 'http://localhost/JapanConvenience/google-callback.php');

function googleConfigurado() {
    return defined('GOOGLE_CLIENT_ID')
        && GOOGLE_CLIENT_ID !== '' && GOOGLE_CLIENT_ID !== 'COLE_SEU_CLIENT_ID_AQUI'
        && defined('GOOGLE_CLIENT_SECRET')
        && GOOGLE_CLIENT_SECRET !== '' && GOOGLE_CLIENT_SECRET !== 'COLE_SEU_CLIENT_SECRET_AQUI';
}
