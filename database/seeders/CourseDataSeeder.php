<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DigitalProduct;
use App\Models\Module;
use App\Models\Lesson;

class CourseDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = DigitalProduct::all();

        foreach ($products as $product) {
            // Criar módulos para cada produto
            $modules = [
                [
                    'title' => 'Introdução ao Curso',
                    'description' => 'Fundamentos básicos e conceitos iniciais',
                    'order' => 1,
                    'lessons' => [
                        [
                            'title' => 'Bem-vindo ao curso',
                            'description' => 'Apresentação do curso e objetivos',
                            'content_type' => 'text',
                            'content_text' => "Bem-vindo ao curso de {$product->title}!\n\nNeste curso, você aprenderá os fundamentos essenciais e técnicas avançadas para dominar esta área.\n\nObjetivos do curso:\n• Compreender os conceitos básicos\n• Aplicar técnicas práticas\n• Desenvolver projetos reais\n• Obter certificação",
                            'duration_minutes' => 5,
                            'order' => 1,
                            'is_free' => true
                        ],
                        [
                            'title' => 'Configuração do ambiente',
                            'description' => 'Preparando seu ambiente de trabalho',
                            'content_type' => 'text',
                            'content_text' => "Antes de começar, vamos configurar seu ambiente de trabalho.\n\nVocê precisará:\n• Computador com acesso à internet\n• Software específico (será detalhado)\n• Conta de acesso à plataforma\n\nSiga as instruções passo a passo para garantir que tudo funcione corretamente.",
                            'duration_minutes' => 10,
                            'order' => 2,
                            'is_free' => true
                        ]
                    ]
                ],
                [
                    'title' => 'Conceitos Fundamentais',
                    'description' => 'Base teórica e conceitos essenciais',
                    'order' => 2,
                    'lessons' => [
                        [
                            'title' => 'História e evolução',
                            'description' => 'Contexto histórico e evolução da área',
                            'content_type' => 'text',
                            'content_text' => "Nesta aula, vamos explorar a história e evolução desta área.\n\nTópicos abordados:\n• Origens e desenvolvimento\n• Principais marcos históricos\n• Evolução tecnológica\n• Impacto na sociedade atual\n\nEsta base histórica é fundamental para entender o contexto atual.",
                            'duration_minutes' => 15,
                            'order' => 1,
                            'is_free' => false
                        ],
                        [
                            'title' => 'Princípios básicos',
                            'description' => 'Princípios fundamentais da área',
                            'content_type' => 'text',
                            'content_text' => "Agora vamos estudar os princípios básicos que regem esta área.\n\nPrincípios fundamentais:\n• Princípio 1: Descrição detalhada\n• Princípio 2: Explicação prática\n• Princípio 3: Aplicação real\n• Princípio 4: Boas práticas\n\nEstes princípios serão a base para todo o conhecimento avançado.",
                            'duration_minutes' => 20,
                            'order' => 2,
                            'is_free' => false
                        ]
                    ]
                ],
                [
                    'title' => 'Técnicas Práticas',
                    'description' => 'Aplicação prática dos conceitos',
                    'order' => 3,
                    'lessons' => [
                        [
                            'title' => 'Primeira técnica',
                            'description' => 'Aprendendo a primeira técnica prática',
                            'content_type' => 'text',
                            'content_text' => "Vamos aprender nossa primeira técnica prática.\n\nPasso a passo:\n1. Preparação inicial\n2. Execução da técnica\n3. Verificação dos resultados\n4. Otimização\n\nEsta técnica é fundamental e será usada em projetos futuros.",
                            'duration_minutes' => 25,
                            'order' => 1,
                            'is_free' => false
                        ],
                        [
                            'title' => 'Técnica avançada',
                            'description' => 'Técnica mais complexa e avançada',
                            'content_type' => 'text',
                            'content_text' => "Agora vamos para uma técnica mais avançada.\n\nEsta técnica combina:\n• Conceitos aprendidos anteriormente\n• Novos elementos\n• Otimizações\n• Casos de uso reais\n\nÉ uma técnica poderosa que amplia suas possibilidades.",
                            'duration_minutes' => 30,
                            'order' => 2,
                            'is_free' => false
                        ]
                    ]
                ],
                [
                    'title' => 'Projetos Práticos',
                    'description' => 'Desenvolvimento de projetos reais',
                    'order' => 4,
                    'lessons' => [
                        [
                            'title' => 'Projeto 1: Aplicação básica',
                            'description' => 'Desenvolvendo o primeiro projeto',
                            'content_type' => 'text',
                            'content_text' => "Vamos desenvolver nosso primeiro projeto prático.\n\nObjetivos do projeto:\n• Aplicar técnicas aprendidas\n• Resolver problema real\n• Documentar processo\n• Apresentar resultados\n\nEste projeto consolidará todo o conhecimento básico.",
                            'duration_minutes' => 45,
                            'order' => 1,
                            'is_free' => false
                        ],
                        [
                            'title' => 'Projeto 2: Aplicação avançada',
                            'description' => 'Projeto mais complexo e desafiador',
                            'content_type' => 'text',
                            'content_text' => "Agora vamos para um projeto mais desafiador.\n\nEste projeto inclui:\n• Múltiplas técnicas\n• Integração de conceitos\n• Otimização de performance\n• Considerações de escalabilidade\n\nÉ um projeto completo que demonstra domínio da área.",
                            'duration_minutes' => 60,
                            'order' => 2,
                            'is_free' => false
                        ]
                    ]
                ],
                [
                    'title' => 'Certificação e Próximos Passos',
                    'description' => 'Preparação para certificação e continuidade',
                    'order' => 5,
                    'lessons' => [
                        [
                            'title' => 'Preparação para certificação',
                            'description' => 'Como se preparar para a certificação',
                            'content_type' => 'text',
                            'content_text' => "Vamos preparar você para a certificação.\n\nTópicos de preparação:\n• Revisão dos conceitos principais\n• Simulados de prova\n• Dicas de estudo\n• Estratégias de prova\n\nA certificação valida seu conhecimento e abre novas oportunidades.",
                            'duration_minutes' => 20,
                            'order' => 1,
                            'is_free' => false
                        ],
                        [
                            'title' => 'Próximos passos na carreira',
                            'description' => 'Orientação para continuar o desenvolvimento',
                            'content_type' => 'text',
                            'content_text' => "Parabéns por completar o curso!\n\nPróximos passos sugeridos:\n• Especialização em áreas específicas\n• Participação em comunidades\n• Networking profissional\n• Projetos pessoais\n\nO aprendizado é contínuo e sempre há mais para explorar.",
                            'duration_minutes' => 15,
                            'order' => 2,
                            'is_free' => false
                        ]
                    ]
                ]
            ];

            foreach ($modules as $moduleData) {
                $module = Module::create([
                    'digital_product_id' => $product->id,
                    'title' => $moduleData['title'],
                    'description' => $moduleData['description'],
                    'order' => $moduleData['order'],
                    'is_active' => true
                ]);

                foreach ($moduleData['lessons'] as $lessonData) {
                    Lesson::create([
                        'module_id' => $module->id,
                        'title' => $lessonData['title'],
                        'description' => $lessonData['description'],
                        'content_type' => $lessonData['content_type'],
                        'content_text' => $lessonData['content_text'],
                        'duration_minutes' => $lessonData['duration_minutes'],
                        'order' => $lessonData['order'],
                        'is_active' => true,
                        'is_free' => $lessonData['is_free']
                    ]);
                }
            }
        }
    }
}
