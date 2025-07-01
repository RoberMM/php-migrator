<?php
/**
 * _query_and_preview_panel.php
 * Mini-componente que renderiza APENAS a área da query e da tabela de preview.
 * Espera receber:
 * @var string $prefix
 * @var string $query
 */
?>
<div class="row mb-4">
    <div class="col-md-4">
        <label>Query para migração:</label>
        <div class="textarea-wrap position-relative">
            <textarea class="form-control bg-dark text-white query-textarea" name="<?= $prefix ?>[query]" placeholder="Escreva sua query aqui" rows="4"><?= htmlspecialchars($query) ?></textarea>
            <button type="button" class="btn btn-info btn-sm position-absolute btn-preview-query" style="bottom:8px; right:8px">
                <i class="fas fa-search"></i> Consultar
            </button>
        </div>
    </div>
    <div class="col-md-8">
        <label>Preview (máx. 20 linhas):</label>
        <div class="table-responsive preview-wrap">
            <table class="table table-sm table-dark table-hover mb-0 preview-table">
                <thead><tr><th class="text-center">Pré-visualização</th></tr></thead>
                <tbody><tr class="placeholder"><td>Clique em “Consultar” para visualizar.</td></tr></tbody>
            </table>
        </div>
    </div>
</div>