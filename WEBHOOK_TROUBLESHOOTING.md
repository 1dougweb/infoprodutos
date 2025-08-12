# 🔧 Solução para Erro 503 do Webhook Mercado Pago

## 📋 Problema Identificado

O webhook do Mercado Pago está retornando erro **503 - Service Unavailable** com a mensagem "Com erro payment.created".

## ✅ Soluções Implementadas

### 1. Rotas Duplicadas Removidas
- **Antes**: Havia rotas de webhook tanto em `routes/web.php` quanto em `routes/api.php`
- **Depois**: Rotas centralizadas apenas em `routes/api.php` para evitar conflitos

### 2. Middleware Específico Criado
- Criado `WebhookMiddleware` para tratar requisições de webhook sem verificações desnecessárias
- Registrado no `bootstrap/app.php` como alias `webhook`

### 3. Logs Aprimorados
- Adicionados logs detalhados no método `webhook()` do `PaymentController`
- Logs específicos para capturar códigos PIX gerados pelo Mercado Pago
- Timestamps únicos para cada requisição de webhook

### 4. Comandos de Teste
- `php artisan mercadopago:test-webhook` - Testa o webhook enviando notificação simulada
- `php artisan webhook:check-status` - Verifica o status e conectividade do webhook

### 5. Página de Teste HTML
- Criada página `public/test-webhook.html` para testes via navegador

## 🚀 Como Testar

### Via Comando Artisan
```bash
# Testar webhook com dados simulados
php artisan mercadopago:test-webhook

# Verificar status do webhook
php artisan webhook:check-status

# Testar com URL específica
php artisan mercadopago:test-webhook --url=https://seu-dominio.com/api/webhooks/mercadopago
```

### Via Navegador
1. Acesse: `https://seu-dominio.com/test-webhook.html`
2. Configure os parâmetros desejados
3. Clique em "Testar Webhook"

### Via cURL
```bash
curl -X POST https://seu-dominio.com/api/webhooks/mercadopago \
  -H "Content-Type: application/json" \
  -H "X-Test-Webhook: true" \
  -d '{
    "action": "payment.created",
    "data": {
      "id": "123456789",
      "status": "pending"
    }
  }'
```

## 📍 URLs do Webhook

- **Principal**: `/api/webhooks/mercadopago`
- **Alternativo**: `/api/webhook`
- **Teste**: `/api/test-webhook`
- **Específico MP**: `/api/mp-webhook`

## 🔍 Verificações Importantes

### 1. Configurações do Mercado Pago
- ✅ Token de acesso configurado
- ✅ URL do webhook configurada corretamente
- ✅ Webhook ativo no painel do Mercado Pago

### 2. Servidor
- ✅ Servidor respondendo na porta correta
- ✅ Firewall permitindo requisições POST
- ✅ SSL válido (para produção)

### 3. Logs
- ✅ Verificar `storage/logs/laravel.log`
- ✅ Logs específicos de webhook com prefixo "Webhook -"

## 🐛 Debug do Erro 503

### Possíveis Causas
1. **Servidor indisponível**: Verificar se o servidor está rodando
2. **Timeout**: Mercado Pago aguarda 22 segundos por resposta
3. **Erro interno**: Exceção não tratada no código
4. **Middleware**: Conflito de middleware

### Logs para Verificar
```bash
# Últimas linhas do log
tail -f storage/logs/laravel.log | grep -i webhook

# Logs específicos de erro
tail -f storage/logs/laravel.log | grep -i "error\|exception"
```

## 📱 Códigos PIX no Log

O sistema agora registra automaticamente no log:
- ✅ QR Code gerado
- ✅ QR Code em Base64
- ✅ URL do ticket
- ✅ Dados da transação

### Exemplo de Log
```
[2024-01-15 10:30:45] local.INFO: === CÓDIGO PIX GERADO ===
[2024-01-15 10:30:45] local.INFO: QR Code: 00020101021226800014br.gov.bcb.pix...
[2024-01-15 10:30:45] local.INFO: QR Code Base64: disponível
[2024-01-15 10:30:45] local.INFO: Ticket URL: https://www.mercadopago.com.br/pix/123456789
[2024-01-15 10:30:45] local.INFO: === FIM CÓDIGO PIX ===
```

## 🔄 Próximos Passos

1. **Testar localmente** com os comandos Artisan
2. **Verificar logs** para identificar erros específicos
3. **Testar em produção** com a página HTML
4. **Configurar webhook** no painel do Mercado Pago
5. **Monitorar logs** para capturar códigos PIX reais

## 📞 Suporte

Se o problema persistir:
1. Execute `php artisan webhook:check-status`
2. Verifique os logs detalhados
3. Teste com dados simulados
4. Verifique configurações do servidor

---

**Nota**: O webhook sempre retorna HTTP 200 OK para o Mercado Pago, mesmo em caso de erro, para evitar reenvios desnecessários.
