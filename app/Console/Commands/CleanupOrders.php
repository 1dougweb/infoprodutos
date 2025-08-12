<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;

class CleanupOrders extends Command
{
    protected $signature = 'orders:cleanup {--days=7 : Número de dias para considerar pedidos antigos} {--force : Forçar limpeza sem confirmação}';
    protected $description = 'Limpar pedidos antigos e pendentes';

    public function handle()
    {
        $days = $this->option('days');
        $force = $this->option('force');
        
        $this->info('=== LIMPEZA DE PEDIDOS ===');
        $this->info('Removendo pedidos pendentes com mais de ' . $days . ' dias...');
        
        // Pedidos pendentes antigos
        $oldPendingOrders = Order::where('status', 'pending')
            ->where('created_at', '<', Carbon::now()->subDays($days))
            ->get();
            
        $this->info('Pedidos pendentes antigos encontrados: ' . $oldPendingOrders->count());
        
        // Pedidos cancelados
        $cancelledOrders = Order::where('status', 'cancelled')->get();
        $this->info('Pedidos cancelados encontrados: ' . $cancelledOrders->count());
        
        // Pedidos com falha
        $failedOrders = Order::where('status', 'failed')->get();
        $this->info('Pedidos com falha encontrados: ' . $failedOrders->count());
        
        $totalToDelete = $oldPendingOrders->count() + $cancelledOrders->count() + $failedOrders->count();
        
        if ($totalToDelete === 0) {
            $this->info('Nenhum pedido para limpar.');
            return 0;
        }
        
        if (!$force) {
            if (!$this->confirm('Deseja realmente deletar ' . $totalToDelete . ' pedidos?')) {
                $this->info('Operação cancelada.');
                return 0;
            }
        }
        
        // Deletar pedidos
        $deletedCount = 0;
        
        foreach ($oldPendingOrders as $order) {
            $order->delete();
            $deletedCount++;
        }
        
        foreach ($cancelledOrders as $order) {
            $order->delete();
            $deletedCount++;
        }
        
        foreach ($failedOrders as $order) {
            $order->delete();
            $deletedCount++;
        }
        
        $this->info('✅ ' . $deletedCount . ' pedidos foram removidos com sucesso!');
        
        // Mostrar estatísticas atuais
        $this->info('');
        $this->info('=== ESTATÍSTICAS ATUAIS ===');
        $this->info('Total de pedidos: ' . Order::count());
        $this->info('Pedidos aprovados: ' . Order::where('status', 'approved')->count());
        $this->info('Pedidos pendentes: ' . Order::where('status', 'pending')->count());
        
        return 0;
    }
} 