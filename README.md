# Membership Area

Sistema de área de membros com cursos digitais e pagamentos via Mercado Pago.

## Configuração do Mercado Pago

### Para Produção:

1. **Criar conta no Mercado Pago**:
   - Acesse: https://www.mercadopago.com.br/developers
   - Crie uma conta e configure sua aplicação

2. **Obter credenciais**:
   - Access Token (produção)
   - Public Key (produção)

3. **Configurar no sistema**:
```bash
php artisan mercadopago:set-credentials "SEU_ACCESS_TOKEN_AQUI" "SUA_PUBLIC_KEY_AQUI"
```

### Para Testes:

1. **Obter credenciais de teste**:
   - Acesse: https://www.mercadopago.com.br/developers/panel/credentials
   - Use as credenciais de TEST

2. **Configurar no sistema**:
```bash
php artisan mercadopago:set-credentials "TEST-SEU_ACCESS_TOKEN" "TEST-SUA_PUBLIC_KEY"
```

### Verificar configuração:

```bash
php artisan mercadopago:test-sdk
```

## Instalação

1. Clone o repositório
2. Execute `composer install`
3. Configure o arquivo `.env`
4. Execute as migrações: `php artisan migrate`
5. Execute os seeders: `php artisan db:seed`
6. Configure o Mercado Pago (veja acima)

## Uso

- Acesse `/admin` para o painel administrativo
- Acesse `/dashboard` para a área de membros
- Configure produtos e cursos no painel admin
- Os usuários podem comprar produtos e acessar cursos
