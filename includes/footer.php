</main>
<footer class="footer site-footer">
  <div class="container footer-grid">
    <div>
      <a class="brand footer-brand" href="/JapanConvenience/index.php">
        <span class="brand-mark" aria-hidden="true"><span class="brand-mountain"></span></span>
        <span class="brand-text"><strong><span>Japan</span>Convenience</strong><small>Loja Japonesa Online</small></span>
      </a>
      <p>Doces, salgados, lamen e bebidas japonesas com entrega rápida. Loja física na Liberdade, São Paulo.</p>
      <p class="mt-2">Rua dos Estudantes, 142 – Liberdade<br>São Paulo - SP, 01505-001 · Tel (11) 3224-0908</p>
    </div>
    <div>
      <h4>Categorias</h4>
      <?php if (!empty($navCats)): ?>
        <?php foreach ($navCats as $c): ?>
          <a href="/JapanConvenience/categoria.php?id=<?= $c['id'] ?>"><?= e($c['nome']) ?></a>
        <?php endforeach; ?>
      <?php else: ?>
        <a href="/JapanConvenience/index.php#categorias">Doces Japoneses</a>
        <a href="/JapanConvenience/index.php#categorias">Salgadinhos &amp; Snacks</a>
        <a href="/JapanConvenience/index.php#categorias">Lamen &amp; Bebidas</a>
        <a href="/JapanConvenience/index.php#categorias">Conveniência</a>
      <?php endif; ?>
    </div>
    <div>
      <h4>Atendimento</h4>
      <a href="/JapanConvenience/index.php#sobre">Central de Ajuda</a>
      <a href="/JapanConvenience/meus-pedidos.php">Meus pedidos</a>
      <a href="/JapanConvenience/carrinho.php">Trocas e Devoluções</a>
      <a href="/JapanConvenience/index.php#sobre">Fale Conosco</a>
    </div>
    <div>
      <h4>Institucional</h4>
      <a href="/JapanConvenience/index.php#sobre">Sobre Nós</a>
      <a href="/JapanConvenience/login.php">Entrar ou Cadastrar</a>
      <a href="/JapanConvenience/meus-pedidos.php">Acompanhar pedido</a>
    </div>
  </div>
  <div class="container d-flex justify-content-between flex-wrap gap-2 mt-4 pt-3" style="border-top:1px solid rgba(50,40,35,.08);font-size:10px;color:#8b8989">
    <span>© 2026 JapanConvenience Ltda. CNPJ: 0.345.678/0001-90 - Todos os direitos reservados.</span>
    <span>Liberdade, São Paulo - SP</span>
  </div>
</footer>
<div class="toast" id="toast" role="status" aria-live="polite"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/JapanConvenience/assets/js/main.js?v=12"></script>
</body>
</html>








