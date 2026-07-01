<?php

return [
    'title' => 'Modelos',
    'singular' => 'Modelo',
    'new' => 'Novo Modelo',
    'edit' => 'Editar Modelo',
    'delete' => 'Excluir Modelo',
    'show' => 'Detalhes do Modelo',
    'no_templates' => 'Nenhum modelo ainda',
    'no_templates_description' => 'Crie seu primeiro modelo para começar a gerar eventos recorrentes.',
    'create_first' => 'Criar Modelo',

    'fields' => [
        'name' => 'Nome',
        'description' => 'Descrição',
        'description_optional' => 'Descrição (opcional)',
        'type' => 'Tipo',
        'rule' => 'Regra de recorrência',
        'default_amount' => 'Valor Padrão',
        'start_date' => 'Data de Início',
        'end_date' => 'Data de Término',
        'end_date_optional' => 'Data de Término (opcional)',
        'asset' => 'Ativo',
        'source_asset' => 'Ativo de Origem',
        'destination_asset' => 'Ativo de Destino',
    ],

    'placeholders' => [
        'name' => 'ex., Salário Mensal',
        'description' => 'Breve descrição deste modelo',
        'default_amount' => '0,00',
    ],

    'help' => [
        'end_date' => 'Deixe vazio para modelos contínuos.',
        'default_amount' => 'Usado para regra fixa. Para regras de máximo/média, este é o valor padrão quando não há histórico.',
    ],

    'types' => [
        'income' => 'Receita',
        'expense' => 'Despesa',
        'transfer' => 'Transferência',
        'expense_with_transfer' => 'Despesa + Transferência',
        'income_with_transfer' => 'Receita + Transferência',
    ],

    'rules' => [
        'fixed' => 'Fixo',
        'max_last_five_months' => 'Máximo dos últimos 5 meses',
        'mean_last_five_months' => 'Média dos últimos 5 meses',
    ],

    'schedule' => [
        'title' => 'Agendamento',
        'ongoing' => 'Contínuo',
    ],

    'configuration' => [
        'title' => 'Configuração',
    ],

    'sections' => [
        'basic_info' => 'Informações Básicas',
    ],

    'affected_events' => [
        'title' => 'Eventos Futuros Afetados',
        'edit_description' => 'Estes eventos foram salvos com valores personalizados. Escolha se deseja mantê-los como estão ou excluí-los (eles voltarão a ser previsões usando o modelo atualizado).',
        'delete_description' => 'Estes eventos serão afetados por esta exclusão. Escolha se deseja mantê-los como estão ou excluí-los.',
        'will_be_regenerated' => 'Estes eventos futuros serão automaticamente regenerados usando as regras atualizadas do modelo.',
        'keep' => 'Manter',
        'delete' => 'Excluir',
    ],

    'delete_confirmation' => [
        'title' => 'Excluir Modelo',
        'message' => 'Tem certeza que deseja excluir <strong>:name</strong>? Esta ação não pode ser desfeita.',
    ],
];
