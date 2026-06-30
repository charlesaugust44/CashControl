<?php

return [
    'title' => 'Lançamentos',
    'singular' => 'Lançamento',
    'create' => 'Novo Lançamento',
    'no_entries' => 'Nenhum evento para este mês',
    'balance' => 'Saldo',
    'forecasted' => 'Previsto',
    'consolidated' => 'Consolidado',

    'status' => [
        'forecast' => 'Previsão',
        'consolidated' => 'Consolidado',
        'pending' => 'Pendente',
        'closed' => 'Fechado',
        'month_closed' => 'Mês Fechado',
    ],

    'actions' => [
        'consolidate' => 'Consolidar',
        'unconsolidate' => 'Desconsolidar',
        'close_month' => 'Fechar Mês',
        'reopen_month' => 'Reabrir Mês',
    ],

    'fields' => [
        'name' => 'Nome',
        'type' => 'Tipo',
        'asset' => 'Ativo',
        'amount' => 'Valor',
        'note' => 'Observação',
        'note_optional' => 'Observação (opcional)',
        'note_placeholder' => 'Adicionar uma observação...',
        'date' => 'Data',
        'transfer_amount' => 'Valor da Transferência',
        'from' => 'De',
        'to' => 'Para',
        'from_source' => 'De (Origem)',
        'to_expense_asset' => 'Para / Ativo de Despesa',
    ],

    'select_asset' => 'Selecionar ativo',
    'select_source_asset' => 'Selecionar ativo de origem',
    'select_destination_asset' => 'Selecionar ativo de destino',
    'add_entry' => 'Adicionar Lançamento',

    'transfer' => [
        'info' => 'Transferências movem dinheiro entre dois ativos. O valor total é o mesmo para ambos.',
        'from' => 'De',
        'to' => 'Para',
    ],

    'entries_section' => [
        'title' => 'Lançamentos',
        'add_entry' => 'Adicionar Lançamento',
        'select_asset' => 'Selecionar ativo',
    ],

    'event_types' => [
        'income' => 'receita',
        'expense' => 'despesa',
        'transfer' => 'transferência',
    ],

    'delete_confirmation' => [
        'title' => 'Excluir Lançamento',
        'message' => 'Tem certeza que deseja excluir <strong>:name</strong>? Esta ação não pode ser desfeita.',
    ],
];
