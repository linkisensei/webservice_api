<?php

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'REST API';


$string['event:api_auth_failed'] = 'Falha na autenticação da API';
$string['event:api_route_requested'] = 'Rota da API requisitado';
$string['event:oauth2_credentials_created'] = 'Credenciais OAuth2 criadas';
$string['event:oauth2_credentials_updated'] = 'Credenciais OAuth2 atualizadas';
$string['event:oauth2_credentials_deleted'] = 'Credenciais OAuth2 excluídas';


$string['settings:manage_title'] = 'Gerenciar REST API';
$string['settings:jwt_ttl'] = 'Expiração do token';
$string['settings:jwt_refresh_ttl'] = 'Expiração do token de atualização';


$string['api:use'] = 'acessar esta API';
$string['api:managecredentials'] = 'gerenciar credenciais de cliente';
$string['api:manageselfcredentials'] = 'gerenciar suas próprias credenciais de cliente';
$string['api:config'] = 'configurar as configurações da API';


$string['docs:post_oauth2_token_summary'] = "Gerar um token de acesso";
$string['docs:post_oauth2_token_description'] = "Lida com a autenticação OAuth2 e retorna um token de acesso.";
$string['docs:password_grant_request'] = "Requisição de concessão por senha";
$string['docs:client_credentials_grant_request'] = "Requisição de concessão por credenciais de cliente";
$string['docs:refresh_token_grant_request'] = "Requisição de concessão por token de atualização";
$string['docs:access_token_generated_success'] = "Token de acesso gerado com sucesso";
$string['docs:invalid_request'] = "Requisição inválida";
$string['docs:invalid_credentials'] = "Credenciais inválidas";
$string['docs:bearer_auth_jwt_description'] = 'O token de acesso JWT deve ser incluído no cabeçalho Authorization';
$string['docs:oauth2_credentials_description'] = 'Endpoints para gerenciamento de credenciais OAuth2';
$string['docs:create_credentials_summary'] = 'Criar novas credenciais OAuth2';
$string['docs:create_credentials_description'] = 'Gera credenciais para um usuário específico.';
$string['docs:create_credentials_success'] = 'Credenciais criadas com sucesso';
$string['docs:update_credentials_summary'] = 'Atualizar credenciais OAuth2';
$string['docs:update_credentials_description'] = 'Regenera a chave secreta para um client_id específico.';
$string['docs:update_credentials_success'] = 'Credenciais atualizadas com sucesso';
$string['docs:delete_credentials_summary'] = 'Revogar credenciais OAuth2';
$string['docs:delete_credentials_description'] = 'Remove as credenciais OAuth2 com base no client_id.';
$string['docs:delete_credentials_success'] = 'Credenciais revogadas com sucesso';
$string['docs:credentials_not_found'] = 'Credenciais não encontradas';
$string['docs:invalid_parameters'] = 'Parâmetros inválidos';
$string['docs:expires_at_description'] = 'Timestamp opcional para expiração das credenciais';


$string['exception:invalid_access_token_secret'] = 'Segredo do token de acesso não configurado';
$string['exception:missing_grant_type'] = "Faltando grant_type";
$string['exception:invalid_user_credentials'] = "Credenciais de usuário inválidas";
$string['exception:missing_required_key'] = 'Faltando chave obrigatória \"{$a}\"';
$string['exception:empty_key'] = 'Chave \"{$a}\" vazia';
$string['exception:invalid_key'] = 'Chave {$a} inválida';
$string['exception:invalid_client_user'] = 'Usuário do cliente inválido';
$string['exception:user_not_found'] = 'Usuário não encontrado';
$string['exception:user_not_confirmed'] = 'Usuário não confirmado';
$string['exception:policy_not_agreed'] = 'Política não aceita';
$string['exception:client_credentials_not_found'] = 'Credenciais do cliente não encontradas!';
$string['exception:invalid_client_credentials'] = "Credenciais do cliente inválidas";
$string['exception:expired_client_credentials'] = "Credenciais do cliente expiradas";
$string['exception:invalid_credentials_expiration'] = "A expiração das credenciais deve ser um timestamp futuro";
$string['exception:credentials_already_exist'] = 'Credenciais já existentes para este usuário';
$string['exception:pagination_limit_violation'] = 'O tamanho máximo permitido para a página é {$a}';