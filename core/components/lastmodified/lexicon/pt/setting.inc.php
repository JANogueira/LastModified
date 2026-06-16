<?php
$_lang['area_lastmodified.main'] = 'Principal';

$_lang['setting_lastmodified.response'] = 'Resposta Cache-control';
$_lang['setting_lastmodified.response_desc'] = 'Define o valor da directiva de resposta Cache-control. Valores disponíveis: "private", "public".';
$_lang['setting_lastmodified.maxage'] = 'Cache-control max-age';
$_lang['setting_lastmodified.maxage_desc'] = 'Define o valor da directiva max-age do Cache-control em segundos. Predefinição: 3600.';
$_lang['setting_lastmodified.expires'] = 'Desvio do Expires';
$_lang['setting_lastmodified.expires_desc'] = 'Define o desvio em segundos relativamente à hora actual para o cabeçalho Expires. Predefinição: 3600.';
$_lang['setting_lastmodified.update_parent'] = 'Actualizar recurso-pai';
$_lang['setting_lastmodified.update_parent_desc'] = 'Actualiza a data de última edição do recurso-pai para indicar que também foi actualizado. Predefinição: não.';
$_lang['setting_lastmodified.update_level'] = 'Nível de aninhamento a actualizar';
$_lang['setting_lastmodified.update_level_desc'] = 'Define o número de níveis acima do recurso actual cujas datas de edição devem ser actualizadas. Predefinição: 1.';
$_lang['setting_lastmodified.update_start'] = 'Actualizar página inicial';
$_lang['setting_lastmodified.update_start_desc'] = 'Actualiza a data de última edição da página inicial quando um recurso é alterado. Predefinição: não.';
$_lang['setting_lastmodified.prevent_authorized'] = 'Ignorar utilizadores autenticados';
$_lang['setting_lastmodified.prevent_authorized_desc'] = 'Desactiva o tratamento do cabeçalho If-Modified-Since para utilizadores autenticados. Predefinição: sim.';
$_lang['setting_lastmodified.prevent_session'] = 'Ignorar se variável de sessão presente';
$_lang['setting_lastmodified.prevent_session_desc'] = 'Desactiva o tratamento do cabeçalho If-Modified-Since se qualquer dos valores (lista separada por vírgulas) existir nos nomes de variáveis de sessão. Predefinição: minishop2.';
$_lang['setting_lastmodified.exclude'] = 'Excluir por id';
$_lang['setting_lastmodified.exclude_desc'] = 'Desactiva o tratamento do cabeçalho If-Modified-Since para os ids de documento listados (separados por vírgulas). Vazio por predefinição.';
