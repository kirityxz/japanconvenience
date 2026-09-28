<?php
session_start();

function isLogged() { return isset($_SESSION['usuario_id']); }
function isAdmin() { return isLogged() && ($_SESSION['usuario_tipo'] ?? '') === 'admin'; }
function requireLogin() {
    if (!isLogged()) { header('Location: login.php'); exit; }
}
function requireAdmin() {
    if (!isAdmin()) { header('Location: ../login.php?erro=restrito'); exit; }
}
function precoBR($v) { return 'R$ ' . number_format((float)$v, 2, ',', '.'); }
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Desconto PIX global (vale para TODOS os produtos, inclusive novos).
// A % fica em `configuracoes.pix_desconto` e pode ser alterada no admin.
function pixTaxa() {
    global $pdo;
    static $t = null;
    if ($t !== null) return $t;
    $t = 0.10;
    try {
        if (isset($pdo)) {
            $v = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'pix_desconto'")->fetchColumn();
            if ($v !== false) $t = max(0, min(90, (float)$v)) / 100;
        }
    } catch (Exception $e) {}
    return $t;
}
function pctPix() { return (int)round(pixTaxa() * 100); }
function precoPix($v) { return (float)$v * (1 - pixTaxa()); }

// E-mail válido de verdade: formato rígido + domínio existente (anti e-mail falso)
function emailValido($email) {
    $email = trim((string)$email);
    if ($email === '' || strlen($email) > 254) return false;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
    if (!preg_match('/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/', $email)) return false;
    $dom = substr(strrchr($email, '@'), 1);
    if ($dom === false || strlen($dom) > 253) return false;
    if (function_exists('checkdnsrr')) {
        if (!checkdnsrr($dom, 'MX') && !checkdnsrr($dom, 'A')) return false;
    }
    return true;
}

// Nome: só letras (com acento), espaços, hífen e apóstrofo — sem números
function nomeValido($nome) {
    $nome = trim((string)$nome);
    if (mb_strlen($nome) < 3) return false;
    return (bool)preg_match("/^[\p{L}\p{M}]+(?:[ '\-][\p{L}\p{M}]+)*$/u", $nome);
}

// Senha forte: mín. 8, com maiúscula, minúscula, número e símbolo
function senhaForte($s) {
    $s = (string)$s;
    if (strlen($s) < 8) return false;
    return preg_match('/[A-Z]/', $s) && preg_match('/[a-z]/', $s)
        && preg_match('/[0-9]/', $s) && preg_match('/[^A-Za-z0-9]/', $s);
}

// Carrinho fica na sessao: [produto_id => qtd]
if (!isset($_SESSION['carrinho'])) $_SESSION['carrinho'] = [];
function cartCount() { return array_sum($_SESSION['carrinho'] ?? []); }

// Foto + subtitulo de cada categoria (fotos escolhidas pela loja).
// Compartilhado entre index.php e categorias.php
function catFoto($nome) {
    $base = __DIR__ . '/../assets/img/';
    $n = mb_strtolower($nome);
    if (str_contains($n, 'conveni')) {
        $arq = file_exists($base . 'cat-conveniencia.jpg') ? 'cat-conveniencia.jpg' : 'cat-salgados.jpg';
        return [$arq, 'Snacks, biscoitos, bebidas e muito mais'];
    }
    if (str_contains($n, 'doce')) return ['cat-doces.jpg', 'Wagashi, chocolates, sobremesas'];
    if (str_contains($n, 'salg')) return [file_exists($base . 'cat-salgados.webp') ? 'cat-salgados.webp' : 'cat-salgados.jpg', 'Batatas, algas, castanhas e mais'];
    if (str_contains($n, 'lamen') || str_contains($n, 'ramen') || str_contains($n, 'râmen')) return ['prod-ramen.jpg', 'Râmen, udon, soba e macarrão'];
    if (str_contains($n, 'beb')) return ['cat-bebidas.jpg', 'Chás, energéticos, refrigerantes e sucos'];
    return ['cat-salgados.jpg', 'Onigiri & Prontos'];
}

// Ícone discreto por categoria (SVG, sem emoji)
function catIcon($nome) {
    $n = mb_strtolower($nome);
    $open = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">';
    if (str_contains($n, 'conveni')) {
        return $open . '<path d="m9.5 7.5-2 2a4.95 4.95 0 1 0 7 7l2-2a4.95 4.95 0 1 0-7-7Z"/><path d="M14 6.5v10"/><path d="M10 7.5v10"/><path d="m16 7 1-5 1.37.68A3 3 0 0 0 19.7 3H21v1.3c0 .46.1.92.32 1.33L22 8l-5 .5"/><path d="m8 17-1 5-1.37-.68A3 3 0 0 0 4.3 21H3v-1.3a3 3 0 0 0-.32-1.33L2 16l5-.5"/></svg>';
    }
    if (str_contains($n, 'doce')) {
        return $open . '<path d="M20 21v-8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8"/><path d="M4 16s.5-1 2-1 2.5 2 4 2 2.5-2 4-2 2.5 2 4 2 2-1 2-1"/><path d="M2 21h20"/><path d="M7 8v3"/><path d="M12 8v3"/><path d="M17 8v3"/><path d="M7 4h.01"/><path d="M12 4h.01"/><path d="M17 4h.01"/></svg>';
    }
    if (str_contains($n, 'salg')) {
        return $open . '<path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/><path d="M12 22V12"/><path d="m3.3 7 8.7 5 8.7-5"/></svg>';
    }
    if (str_contains($n, 'lamen') || str_contains($n, 'ramen') || str_contains($n, 'râmen')) {
        return $open . '<path d="M4 13h16c0 4.2-3.6 7-8 7s-8-2.8-8-7Z"/><path d="M9 9.5c0-1.8 1.2-2 1.2-3.8"/><path d="M14.5 9.5c0-1.8 1.2-2 1.2-3.8"/><path d="M2 13h20"/></svg>';
    }
    return $open . '<path d="m6 8 1.75 12.28a2 2 0 0 0 2 1.72h4.54a2 2 0 0 0 2-1.72L18 8"/><path d="M5 8h14"/><path d="M7 15a6.47 6.47 0 0 1 5 0 6.47 6.47 0 0 0 5 0"/><path d="m12 8 1-6h2"/></svg>';
}

// Todas as fotos do produto (para a galeria com setas)
function imgGaleria($p) {
    static $mapa = [
        2 => ['prod-pocky-1.webp', 'prod-pocky-2.webp'],       // Pocky Chocolate
        3 => ['prod-dorayaki.webp', 'prod-dorayaki-2.webp'],   // Dorayaki Azuki
        7 => ['prod-lamen-1.jpg', 'prod-lamen-2.jpg'],         // Lamen Cup Nissin Shoyu
    ];
    $id = (int)($p['id'] ?? 0);
    if (isset($mapa[$id])) {
        $out = [];
        foreach ($mapa[$id] as $f) $out[] = '/JapanConvenience/assets/img/' . $f;
        return $out;
    }
    $foto = imgProduto($p);
    return $foto ? [$foto] : [];
}

// Foto local do produto (mapeada pelo ID do seed em database.sql)
function imgProduto($p) {
    static $mapa = [
        1 => 'prod-morango.jpg',  // Mochi de Morango
        2 => 'prod-pocky.jpg',    // Pocky Chocolate
        3 => 'prod-dorayaki.webp', // Dorayaki Azuki
        4 => 'prod-matcha.webp',   // KitKat Matcha
        7 => 'prod-lamen-1.jpg',    // Lamen Cup
        8 => 'prod-ramune.jpg',   // Ramune
        9 => 'prod-onigiri.jpg',  // Onigiri Salmao
    ];
    $id = (int)($p['id'] ?? 0);
    if (isset($mapa[$id])) return '/JapanConvenience/assets/img/' . $mapa[$id];
    return null; // usar emoji de $p['imagem']
}

// Total em R$ do carrinho (para mostrar no topo, como no layout)
function cartTotal($pdo = null) {
    if (empty($_SESSION['carrinho']) || !$pdo) return 0;
    try {
        $ids = array_keys($_SESSION['carrinho']);
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id, preco FROM produtos WHERE id IN ($ph)");
        $stmt->execute($ids);
        $t = 0;
        foreach ($stmt->fetchAll() as $p) $t += $p['preco'] * $_SESSION['carrinho'][$p['id']];
        return $t;
    } catch (Exception $e) { return 0; }
}
function flash($key = 'msg') {
    if (isset($_SESSION[$key])) { $m = $_SESSION[$key]; unset($_SESSION[$key]); return $m; }
    return null;
}
