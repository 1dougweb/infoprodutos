<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;

class ManageOrderStatus extends Command
{
    protected $signature = 'orders:manage-status {order_id} {status} {--reason= : Motivo da mudança de status}';
    protected $description = 'Gerenciar status de um pedido específico';

    public function handle()
    {
        $orderId = $this->argument('order_id');
        $status = $this->argument('status');
        $reason = $this->option('reason');
        
        $order = Order::find($orderId);
        
        if (!$order) {
            $this->error('Pedido não encontrado com ID: ' . $orderId);
            return 1;
        }
        
        $this->info('=== GERENCIAMENTO DE STATUS ===');
        $this->info('Pedido ID: ' . $order->id);
        $this->info('Produto: ' . $order->digitalProduct->title);
        $this->info('Usuário: ' . $order->user->email);
        $this->info('Status atual: ' . $order->status);
        $this->info('Status novo: ' . $status);
        
        if ($reason) {
            $this->info('Motivo: ' . $reason);
        }
        
        if (!$this->confirm('Deseja realmente alterar o status do pedido?')) {
            $this->info('Operação cancelada.');
            return 0;
        }
        
        // Validar status
        $validStatuses = ['pending', 'approved', 'cancelled', 'failed', 'refunded'];
        if (!in_array($status, $validStatuses)) {
            $this->error('Status inválido. Status válidos: ' . implode(', ', $validStatuses));
            return 1;
        }
        
        // Atualizar status
        $order->update([
            'status' => $status,
            'paid_at' => $status === 'approved' ? now() : null
        ]);
        
        $this->info('✅ Status do pedido alterado com sucesso!');
        $this->info('Novo status: ' . $status);
        
        // Se foi aprovado, criar compra do usuário
        if ($status === 'approved') {
            $this->info('Criando compra do usuário...');
            $this->createUserPurchase($order->user, $order->digitalProduct);
            $this->info('✅ Compra do usuário criada!');
        }
        
        return 0;
    }
    
    private function createUserPurchase($user, $product)
    {
        // Verificar se já não possui o produto
        if (!$user->hasPurchased($product->id)) {
            $user->purchases()->create([
                'digital_product_id' => $product->id,
                'purchased_at' => now()
            ]);
        }
    }
} 