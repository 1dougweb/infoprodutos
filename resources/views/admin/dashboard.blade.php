@extends('membership.layout')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-white">Dashboard</h1>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Novo Produto
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Produtos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-white">{{ $stats['total_products'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-box-seam fs-1 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Usuários
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-white">{{ $stats['total_users'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people-fill fs-1 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Pedidos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-white">{{ $stats['total_orders'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cart3 fs-1 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Receita Total
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-white">R$ {{ number_format($stats['total_revenue'], 2, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-currency-dollar fs-1 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Sales Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4 h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-white">Vendas dos Últimos 7 Dias</h6>
                    <div class="dropdown no-arrow">
                        <button class="btn btn-link p-0 text-decoration-none" type="button" id="salesChartDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical text-gray-400"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="salesChartDropdown">
                            <div class="dropdown-header">Ações:</div>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="exportChart('sales')">
                                <i class="bi bi-download me-2"></i>Exportar
                            </a>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="printChart('sales')">
                                <i class="bi bi-printer me-2"></i>Imprimir
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="showChartDetails('sales')">
                                <i class="bi bi-info-circle me-2"></i>Detalhes
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="chart-area flex-grow-1">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bar Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4 h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-white">Vendas Mensais</h6>
                    <div class="dropdown no-arrow">
                        <button class="btn btn-link p-0 text-decoration-none" type="button" id="monthlyChartDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical text-gray-400"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="monthlyChartDropdown">
                            <div class="dropdown-header">Ações:</div>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="exportChart('monthly')">
                                <i class="bi bi-download me-2"></i>Exportar
                            </a>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="printChart('monthly')">
                                <i class="bi bi-printer me-2"></i>Imprimir
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="showChartDetails('monthly')">
                                <i class="bi bi-info-circle me-2"></i>Detalhes
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="chart-bar flex-grow-1">
                        <canvas id="monthlyBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-fill"></i> Pedidos Recentes
                    </h5>
                </div>
                <div class="card-body">
                    @if($recent_orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuário</th>
                                        <th>Produto</th>
                                        <th>Valor</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_orders as $order)
                                        <tr>
                                            <td>#{{ $order->id }}</td>
                                            <td>{{ $order->user->name }}</td>
                                            <td>{{ $order->digitalProduct->title }}</td>
                                            <td>R$ {{ number_format($order->amount, 2, ',', '.') }}</td>
                                            <td>
                                                @switch($order->status)
                                                    @case('approved')
                                                        <span class="badge bg-success">Aprovado</span>
                                                        @break
                                                    @case('pending')
                                                        <span class="badge bg-warning">Pendente</span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="badge bg-danger">Rejeitado</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                                @endswitch
                                            </td>
                                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: var(--text-muted);"></i>
                            <p class="text-muted">Nenhum pedido encontrado.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning-fill"></i> Ações Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle"></i> Novo Produto
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.products') }}" class="btn btn-info w-100">
                                <i class="bi bi-box-seam"></i> Gerenciar Produtos
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.orders') }}" class="btn btn-warning w-100">
                                <i class="bi bi-cart3"></i> Ver Pedidos
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.users') }}" class="btn btn-success w-100">
                                <i class="bi bi-people-fill"></i> Ver Usuários
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DEBUG DASHBOARD ===');
    console.log('DOM carregado, verificando Chart.js...');
    
    // Verificar se Chart.js está carregado
    if (typeof Chart === 'undefined') {
        console.error('❌ Chart.js não está carregado!');
        return;
    }
    
    console.log('✅ Chart.js carregado com sucesso!');
    console.log('Chart version:', Chart.version);

    // Sales Chart
    const salesCtx = document.getElementById('salesChart');
    console.log('🔍 Procurando elemento salesChart:', salesCtx);
    
    if (salesCtx) {
        console.log('✅ Elemento salesChart encontrado!');
        
        // Pegar a cor primária das configurações
        const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-blue').trim();
        console.log('🎨 Cor primária:', primaryColor);
        
        // Criar gradiente para o chart
        const gradient = salesCtx.getContext('2d').createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, primaryColor + '80'); // Mais opaco no topo
        gradient.addColorStop(1, primaryColor + '10'); // Mais transparente na base
        
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: @json($salesLabels),
                datasets: [{
                    label: 'Vendas (R$)',
                    data: @json($salesData),
                    borderColor: primaryColor,
                    backgroundColor: gradient,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)',
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        },
                        border: {
                            color: primaryColor + '40'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        },
                        border: {
                            color: primaryColor + '40'
                        }
                    }
                }
            }
        });
        console.log('Sales Chart criado com sucesso!');
    } else {
        console.error('Elemento salesChart não encontrado!');
    }

    // Monthly Bar Chart
    const monthlyCtx = document.getElementById('monthlyBarChart');
    console.log('🔍 Procurando elemento monthlyBarChart:', monthlyCtx);
    
    if (monthlyCtx) {
        console.log('✅ Elemento monthlyBarChart encontrado!');
        
        // Pegar a cor primária das configurações
        const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-blue').trim();
        console.log('🎨 Cor primária para bar chart:', primaryColor);
        
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: @json($monthlyLabels),
                datasets: [{
                    label: 'Vendas {{ $currentYear }}',
                    data: @json($monthlyData),
                    backgroundColor: primaryColor,
                    borderColor: primaryColor,
                    borderWidth: 1
                }, {
                    label: 'Vendas {{ $previousYear }}',
                    data: @json($monthlyDataPreviousYear),
                    backgroundColor: 'rgba(255, 255, 255, 0.8)',
                    borderColor: 'rgba(255, 255, 255, 0.9)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)',
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    }
                }
            }
        });
        console.log('Monthly Bar Chart criado com sucesso!');
    } else {
        console.error('Elemento monthlyBarChart não encontrado!');
    }
    
    // Variável global para armazenar o tipo do chart atual
    window.currentChartType = '';
    
    // Funções para as ações dos menus de contexto
    window.exportChart = function(chartType) {
        console.log('Exportando chart:', chartType);
        window.currentChartType = chartType;
        const exportModal = new bootstrap.Modal(document.getElementById('exportModal'));
        exportModal.show();
    };
    
    window.printChart = function(chartType) {
        console.log('Imprimindo chart:', chartType);
        window.currentChartType = chartType;
        const printModal = new bootstrap.Modal(document.getElementById('printModal'));
        printModal.show();
    };
    
    window.showChartDetails = function(chartType) {
        console.log('Mostrando detalhes do chart:', chartType);
        window.currentChartType = chartType;
        
        // Carregar conteúdo dinâmico baseado no tipo do chart
        const detailsContent = document.getElementById('chartDetailsContent');
        if (chartType === 'sales') {
            const salesData = @json($salesData);
            const salesLabels = @json($salesLabels);
            
            const totalSales = salesData.reduce((a, b) => a + b, 0);
            const avgSales = totalSales / 7;
            const maxSales = Math.max(...salesData);
            const minSales = Math.min(...salesData);
            const maxIndex = salesData.indexOf(maxSales);
            const minIndex = salesData.indexOf(minSales);
            const maxDay = salesLabels[maxIndex];
            const minDay = salesLabels[minIndex];
            const daysWithSales = salesData.filter(v => v > 0).length;
            const variation = avgSales > 0 ? ((maxSales - minSales) / avgSales * 100) : 0;
            
            detailsContent.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="bi bi-graph-up text-primary"></i> Vendas dos Últimos 7 Dias</h6>
                        <ul class="list-unstyled">
                            <li><strong>Total de Vendas:</strong> R$ ${totalSales.toFixed(2)}</li>
                            <li><strong>Média Diária:</strong> R$ ${avgSales.toFixed(2)}</li>
                            <li><strong>Melhor Dia:</strong> ${maxDay} (R$ ${maxSales.toFixed(2)})</li>
                            <li><strong>Pior Dia:</strong> ${minDay} (R$ ${minSales.toFixed(2)})</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-calendar text-success"></i> Análise Temporal</h6>
                        <ul class="list-unstyled">
                            <li><strong>Tendência:</strong> ${maxSales > avgSales ? 'Crescente' : 'Decrescente'}</li>
                            <li><strong>Variação:</strong> ${variation.toFixed(1)}%</li>
                            <li><strong>Dias com Vendas:</strong> ${daysWithSales}/7</li>
                        </ul>
                    </div>
                </div>
            `;
        } else if (chartType === 'monthly') {
            const monthlyData = @json($monthlyData);
            const monthlyDataPreviousYear = @json($monthlyDataPreviousYear);
            const monthlyLabels = @json($monthlyLabels);
            const currentYear = @json($currentYear);
            const previousYear = @json($previousYear);
            
            const totalCurrent = monthlyData.reduce((a, b) => a + b, 0);
            const totalPrevious = monthlyDataPreviousYear.reduce((a, b) => a + b, 0);
            const growth = totalCurrent > 0 && totalPrevious > 0 ? ((totalCurrent - totalPrevious) / totalPrevious * 100) : 0;
            const avgCurrent = totalCurrent / 6;
            const avgPrevious = totalPrevious / 6;
            const bestMonthIndex = monthlyData.indexOf(Math.max(...monthlyData));
            const bestMonth = monthlyLabels[bestMonthIndex];
            
            detailsContent.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="bi bi-bar-chart text-primary"></i> Vendas Anuais</h6>
                        <ul class="list-unstyled">
                            <li><strong>Total ${currentYear}:</strong> R$ ${totalCurrent.toFixed(2)}</li>
                            <li><strong>Total ${previousYear}:</strong> R$ ${totalPrevious.toFixed(2)}</li>
                            <li><strong>Crescimento:</strong> ${growth.toFixed(1)}%</li>
                            <li><strong>Melhor Mês:</strong> ${bestMonth} ${currentYear}</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-trending-up text-success"></i> Comparação Anual</h6>
                        <ul class="list-unstyled">
                            <li><strong>${currentYear} vs ${previousYear}:</strong> ${growth.toFixed(1)}%</li>
                            <li><strong>Média Mensal ${currentYear}:</strong> R$ ${avgCurrent.toFixed(2)}</li>
                            <li><strong>Média Mensal ${previousYear}:</strong> R$ ${avgPrevious.toFixed(2)}</li>
                        </ul>
                    </div>
                </div>
            `;
        }
        
        const detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'));
        detailsModal.show();
    };
    
    // Funções para as ações dos modais
    window.exportAsPNG = function() {
        console.log('Exportando como PNG:', window.currentChartType);
        // Implementar lógica de exportação PNG
        alert('Exportando ' + window.currentChartType + ' como PNG...');
        bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
    };
    
    window.exportAsPDF = function() {
        console.log('Exportando como PDF:', window.currentChartType);
        // Implementar lógica de exportação PDF
        alert('Exportando ' + window.currentChartType + ' como PDF...');
        bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
    };
    
    window.exportAsCSV = function() {
        console.log('Exportando como CSV:', window.currentChartType);
        // Implementar lógica de exportação CSV
        alert('Exportando ' + window.currentChartType + ' como CSV...');
        bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
    };
    
    window.printChartNow = function() {
        console.log('Gerando relatório do chart:', window.currentChartType);
        const orientation = document.getElementById('printOrientation').value;
        const size = document.getElementById('printSize').value;
        
        // Criar dados do relatório
        let reportData = {
            chartType: window.currentChartType,
            orientation: orientation,
            size: size,
            timestamp: new Date().toLocaleString('pt-BR'),
            data: {}
        };
        
        if (window.currentChartType === 'sales') {
            const salesData = @json($salesData);
            const salesLabels = @json($salesLabels);
            
            reportData.data = {
                title: 'Relatório de Vendas dos Últimos 7 Dias',
                labels: salesLabels,
                values: salesData,
                total: salesData.reduce((a, b) => a + b, 0),
                average: salesData.reduce((a, b) => a + b, 0) / 7,
                maxValue: Math.max(...salesData),
                minValue: Math.min(...salesData),
                maxDay: salesLabels[salesData.indexOf(Math.max(...salesData))],
                minDay: salesLabels[salesData.indexOf(Math.min(...salesData))]
            };
        } else if (window.currentChartType === 'monthly') {
            const monthlyData = @json($monthlyData);
            const monthlyDataPreviousYear = @json($monthlyDataPreviousYear);
            const monthlyLabels = @json($monthlyLabels);
            const currentYear = @json($currentYear);
            const previousYear = @json($previousYear);
            
            reportData.data = {
                title: 'Relatório de Vendas Mensais',
                labels: monthlyLabels,
                currentYear: currentYear,
                previousYear: previousYear,
                currentValues: monthlyData,
                previousValues: monthlyDataPreviousYear,
                totalCurrent: monthlyData.reduce((a, b) => a + b, 0),
                totalPrevious: monthlyDataPreviousYear.reduce((a, b) => a + b, 0),
                growth: monthlyData.reduce((a, b) => a + b, 0) > 0 && monthlyDataPreviousYear.reduce((a, b) => a + b, 0) > 0 ? 
                    ((monthlyData.reduce((a, b) => a + b, 0) - monthlyDataPreviousYear.reduce((a, b) => a + b, 0)) / monthlyDataPreviousYear.reduce((a, b) => a + b, 0) * 100) : 0
            };
        }
        
        // Gerar relatório HTML
        generateReport(reportData);
        
        bootstrap.Modal.getInstance(document.getElementById('printModal')).hide();
    };
    
    function generateReport(data) {
        // Criar janela de impressão com relatório formatado
        const reportWindow = window.open('', '_blank', 'width=800,height=600');
        
        let reportHTML = `
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Relatório - ${data.data.title}</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    margin: 0;
                    padding: 20px;
                    background-color: #f8f9fa;
                    color: #333;
                }
                .report-header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #007bff;
                    padding-bottom: 20px;
                }
                .report-title {
                    font-size: 24px;
                    font-weight: bold;
                    color: #007bff;
                    margin-bottom: 10px;
                }
                .report-subtitle {
                    font-size: 14px;
                    color: #666;
                }
                .report-content {
                    max-width: 800px;
                    margin: 0 auto;
                }
                .data-section {
                    margin-bottom: 30px;
                    background: white;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .data-title {
                    font-size: 18px;
                    font-weight: bold;
                    color: #333;
                    margin-bottom: 15px;
                    border-bottom: 1px solid #eee;
                    padding-bottom: 10px;
                }
                .data-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 15px;
                    margin-bottom: 20px;
                }
                .data-item {
                    background: #f8f9fa;
                    padding: 15px;
                    border-radius: 6px;
                    border-left: 4px solid #007bff;
                }
                .data-label {
                    font-size: 12px;
                    color: #666;
                    text-transform: uppercase;
                    font-weight: bold;
                    margin-bottom: 5px;
                }
                .data-value {
                    font-size: 18px;
                    font-weight: bold;
                    color: #333;
                }
                .data-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 15px;
                }
                .data-table th,
                .data-table td {
                    padding: 10px;
                    text-align: left;
                    border-bottom: 1px solid #eee;
                }
                .data-table th {
                    background-color: #f8f9fa;
                    font-weight: bold;
                    color: #333;
                }
                .positive { color: #28a745; }
                .negative { color: #dc3545; }
                .neutral { color: #6c757d; }
                @media print {
                    body { background: white; }
                    .report-content { max-width: none; }
                }
            </style>
        </head>
        <body>
            <div class="report-content">
                <div class="report-header">
                    <div class="report-title">${data.data.title}</div>
                    <div class="report-subtitle">Gerado em ${data.timestamp}</div>
                </div>
        `;
        
        if (data.chartType === 'sales') {
            reportHTML += `
                <div class="data-section">
                    <div class="data-title">Resumo das Vendas</div>
                    <div class="data-grid">
                        <div class="data-item">
                            <div class="data-label">Total de Vendas</div>
                            <div class="data-value">R$ ${data.data.total.toFixed(2)}</div>
                        </div>
                        <div class="data-item">
                            <div class="data-label">Média Diária</div>
                            <div class="data-value">R$ ${data.data.average.toFixed(2)}</div>
                        </div>
                        <div class="data-item">
                            <div class="data-label">Melhor Dia</div>
                            <div class="data-value">${data.data.maxDay} (R$ ${data.data.maxValue.toFixed(2)})</div>
                        </div>
                        <div class="data-item">
                            <div class="data-label">Pior Dia</div>
                            <div class="data-value">${data.data.minDay} (R$ ${data.data.minValue.toFixed(2)})</div>
                        </div>
                    </div>
                    
                    <div class="data-title">Vendas por Dia</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Dia</th>
                                <th>Vendas (R$)</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            for (let i = 0; i < data.data.labels.length; i++) {
                reportHTML += `
                    <tr>
                        <td>${data.data.labels[i]}</td>
                        <td>R$ ${data.data.values[i].toFixed(2)}</td>
                    </tr>
                `;
            }
            
            reportHTML += `
                        </tbody>
                    </table>
                </div>
            `;
        } else if (data.chartType === 'monthly') {
            reportHTML += `
                <div class="data-section">
                    <div class="data-title">Comparação Anual</div>
                    <div class="data-grid">
                        <div class="data-item">
                            <div class="data-label">Total ${data.data.currentYear}</div>
                            <div class="data-value">R$ ${data.data.totalCurrent.toFixed(2)}</div>
                        </div>
                        <div class="data-item">
                            <div class="data-label">Total ${data.data.previousYear}</div>
                            <div class="data-value">R$ ${data.data.totalPrevious.toFixed(2)}</div>
                        </div>
                        <div class="data-item">
                            <div class="data-label">Crescimento</div>
                            <div class="data-value ${data.data.growth >= 0 ? 'positive' : 'negative'}">${data.data.growth.toFixed(1)}%</div>
                        </div>
                    </div>
                    
                    <div class="data-title">Vendas Mensais</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mês</th>
                                <th>${data.data.currentYear} (R$)</th>
                                <th>${data.data.previousYear} (R$)</th>
                                <th>Diferença</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            for (let i = 0; i < data.data.labels.length; i++) {
                const difference = data.data.currentValues[i] - data.data.previousValues[i];
                const differencePercent = data.data.previousValues[i] > 0 ? (difference / data.data.previousValues[i] * 100) : 0;
                
                reportHTML += `
                    <tr>
                        <td>${data.data.labels[i]}</td>
                        <td>R$ ${data.data.currentValues[i].toFixed(2)}</td>
                        <td>R$ ${data.data.previousValues[i].toFixed(2)}</td>
                        <td class="${difference >= 0 ? 'positive' : 'negative'}">
                            ${difference >= 0 ? '+' : ''}R$ ${difference.toFixed(2)} (${differencePercent.toFixed(1)}%)
                        </td>
                    </tr>
                `;
            }
            
            reportHTML += `
                        </tbody>
                    </table>
                </div>
            `;
        }
        
        reportHTML += `
            </div>
        </body>
        </html>
        `;
        
        reportWindow.document.write(reportHTML);
        reportWindow.document.close();
        
        // Aguardar carregamento e imprimir
        reportWindow.onload = function() {
            reportWindow.print();
        };
    }
});
</script>

<!-- Modais para as ações dos charts -->
<!-- Modal de Exportação -->
<div class="modal fade centered-modal" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true" style="backdrop-filter: blur(10px); background-color: rgba(0, 0, 0, 0.7);">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Exportar Chart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selecione o formato de exportação:</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" onclick="exportAsPNG()">
                        <i class="bi bi-image me-2"></i>Exportar como PNG
                    </button>
                    <button type="button" class="btn btn-success" onclick="exportAsPDF()">
                        <i class="bi bi-file-pdf me-2"></i>Exportar como PDF
                    </button>
                    <button type="button" class="btn btn-info" onclick="exportAsCSV()">
                        <i class="bi bi-file-text me-2"></i>Exportar como CSV
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Impressão -->
<div class="modal fade centered-modal" id="printModal" tabindex="-1" aria-labelledby="printModalLabel" aria-hidden="true" style="backdrop-filter: blur(10px); background-color: rgba(0, 0, 0, 0.7);">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printModalLabel">Imprimir Chart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Configurações de impressão:</p>
                <div class="mb-3">
                    <label class="form-label">Orientação:</label>
                    <select class="form-select" id="printOrientation">
                        <option value="portrait">Retrato</option>
                        <option value="landscape">Paisagem</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tamanho:</label>
                    <select class="form-select" id="printSize">
                        <option value="a4">A4</option>
                        <option value="letter">Carta</option>
                        <option value="legal">Legal</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="printChartNow()">
                    <i class="bi bi-printer me-2"></i>Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalhes -->
<div class="modal fade centered-modal" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true" style="backdrop-filter: blur(10px); background-color: rgba(0, 0, 0, 0.7);">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Detalhes do Chart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="chartDetailsContent">
                    <!-- Conteúdo será carregado dinamicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>


@endsection 