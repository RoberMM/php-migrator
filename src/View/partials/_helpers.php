<?php
// Geralmente em src/View/partials/_helpers.php

if (!function_exists('formatRuleForDisplay')) {
    /**
     * Formata uma regra de mapeamento para exibição amigável no formulário.
     * VERSÃO FINAL COMPLETA.
     */
    function formatRuleForDisplay($rule) {
        // Tratamento de segurança para dados antigos ou malformados
        if (!is_array($rule) || empty($rule['type'])) {
            return is_string($rule) ? $rule : '';
        }
        
        switch ($rule['type']) {
            case 'source_column': 
                return $rule['value'] ?? '';
            
            case 'sub_query': 
                return '[Sub-Consulta]';

            case 'de_para_lookup': 
                return '[Busca De-Para]';

            case 'sub_query_de_para_lookup':
                return '[Busca De-Para c/ Sub-Query]';

            case 'generate_acronym': 
                return '[Gerar Sigla]';
            
            case 'auto_increment':
                return '[ID Automático a partir de: '.($rule['start_value'] ?? 'N/D').']';

            case 'datetime': 
                return '[Data e Hora Atuais]';
            
            case 'default':
                $value = $rule['value'] ?? null;
                if ($value === null) return '[Valor Nulo]';
                if ($value === '') return '[Texto Vazio]';
                return "[Valor Fixo: $value]";
            
            default: 
                return '[Regra Desconhecida]';
        }
    }
}