<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;

class UpdateOrderPaymentMethods extends Command
{
    protected $signature = 'orders:update-payment-methods {--force : Forçar atualização sem confirmação}';
    protected $description = 'Atualizar métodos de pagamento dos pedidos existentes';

    public function handle()
    {
        $force = $this->option('force');
        
        $this->info('=== ATUALIZAÇÃO DE MÉTODOS DE PAGAMENTO ===');
        
        // Pedidos aprovados sem método definido
        $approvedOrders = Order::where('status', 'approved')
            ->whereNull('payment_method')
            ->get();
            
        $this->info('Pedidos aprovados sem método: ' . $approvedOrders->count());
        
        // Pedidos pendentes sem método definido
        $pendingOrders = Order::where('status', 'pending')
            ->whereNull('payment_method')
            ->get();
            
        $this->info('Pedidos pendentes sem método: ' . $pendingOrders->count());
        
        $totalToUpdate = $approvedOrders->count() + $pendingOrders->count();
        
        if ($totalToUpdate === 0) {
            $this->info('Nenhum pedido precisa ser atualizado.');
            return 0;
        }
        
        if (!$force) {
            if (!$this->confirm("Deseja atualizar {$totalToUpdate} pedidos?")) {
                $this->info('Operação cancelada.');
                return 0;
            }
        }
        
        $updatedCount = 0;
        
        // Atualizar pedidos aprovados (assumir cartão como padrão)
        foreach ($approvedOrders as $order) {
            $order->update(['payment_method' => 'credit_card']);
            $updatedCount++;
            $this->info("Pedido {$order->id} atualizado para credit_card");
        }
        
        // Atualizar pedidos pendentes (assumir método misto)
        foreach ($pendingOrders as $order) {
            // Determinar método baseado em outros indicadores
            $method = $this->determinePaymentMethod($order);
            $order->update(['payment_method' => $method]);
            $updatedCount++;
            $this->info("Pedido {$order->id} atualizado para {$method}");
        }
        
        $this->info("✅ {$updatedCount} pedidos foram atualizados com sucesso!");
        
        // Mostrar estatísticas finais
        $this->info('');
        $this->info('=== ESTATÍSTICAS FINAIS ===');
        $this->info('Total de pedidos: ' . Order::count());
        $this->info('Pedidos com método definido: ' . Order::whereNotNull('payment_method')->count());
        $this->info('Pedidos sem método: ' . Order::whereNull('payment_method')->count());
        
        return 0;
    }
    
    private function determinePaymentMethod($order)
    {
        // Se tem preference_id, pode ser PIX
        if ($order->mercadopago_preference_id) {
            return 'pix'; // Assumir PIX para pedidos pendentes com preference
        }
        
        // Se tem payment_id, pode ser cartão
        if ($order->mercadopago_payment_id) {
            return 'credit_card';
        }
        
        // Padrão para pedidos pendentes
        return 'credit_card';
    }
} 