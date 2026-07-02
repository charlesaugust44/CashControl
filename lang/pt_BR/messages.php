<?php

return [
    'success' => [
        'created' => ':item criado com sucesso',
        'updated' => ':item atualizado com sucesso',
        'deleted' => ':item excluído com sucesso',
        'consolidated' => 'Evento consolidado com sucesso',
        'reverted' => 'Evento revertido com sucesso',
        'saved' => ':item salvo com sucesso',
        'month_closed' => 'Mês :month fechado com sucesso',
        'month_reopened' => 'Mês anterior reaberto com sucesso',
    ],
    'error' => [
        'generic' => 'Ocorreu um erro',
        'not_found' => ':item não encontrado',
        'validation' => 'Por favor, verifique os dados informados',
    ],
    'confirm' => [
        'delete' => 'Tem certeza que deseja excluir este :item?',
        'close_month' => 'Fechar este mês? Todos os eventos devem estar consolidados.',
        'reopen_month' => 'Reabrir este mês? Isso irá desconsolidar todos os eventos.',
        'delete_event' => 'Tem certeza que deseja excluir este evento?',
    ],
];
