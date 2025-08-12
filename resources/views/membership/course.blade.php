@extends('membership.course-layout')

@section('title', $product->title)

@section('content')
<div class="course-container">
    <!-- Header do Curso -->
    <div class="course-header">
        <div class="course-info">
            <h1 class="course-title">{{ $product->title }}</h1>
            <p class="course-description">{{ $product->description }}</p>
            <div class="course-progress">
                <div class="progress-info">
                    <span>Progresso do curso</span>
                    <span class="progress-percentage">{{ $product->getUserProgressPercentage($user->id) }}%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $product->getUserProgressPercentage($user->id) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="course-content">
        <!-- Lista de Módulos -->
        <div class="modules-list">
            @foreach($modules as $module)
                <div class="module-card">
                    <div class="module-header" onclick="toggleModule({{ $module->id }})">
                        <div class="module-info">
                            <h3 class="module-title">{{ $module->title }}</h3>
                            <p class="module-description">{{ $module->description }}</p>
                            <div class="module-progress">
                                <span>{{ $module->getProgressPercentage($user->id) }}% completo</span>
                                <div class="module-progress-bar">
                                    <div class="module-progress-fill" style="width: {{ $module->getProgressPercentage($user->id) }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="module-toggle">
                            <i class="bi bi-chevron-down"></i>
                        </div>
                    </div>
                    
                    <div class="lessons-list" id="module-{{ $module->id }}">
                        @foreach($module->activeLessons as $lesson)
                            <div class="lesson-item {{ $lesson->isCompleted($user->id) ? 'completed' : '' }}">
                                <a href="{{ route('membership.lesson', [$product->id, $lesson->id]) }}" class="lesson-link">
                                    <div class="lesson-info">
                                        <div class="lesson-title">{{ $lesson->title }}</div>
                                        <div class="lesson-meta">
                                            <span class="lesson-duration">{{ $lesson->getFormattedDuration() }}</span>
                                            @if($lesson->isCompleted($user->id))
                                                <span class="lesson-status completed">
                                                    <i class="bi bi-check-circle-fill"></i>
                                                </span>
                                            @else
                                                <span class="lesson-status">
                                                    <i class="bi bi-circle"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Área de Conteúdo -->
        <div class="content-area">
            @if($firstLesson)
                <div class="welcome-content">
                    <div class="welcome-icon">
                        <i class="bi bi-play-circle-fill"></i>
                    </div>
                    <h2>Bem-vindo ao curso!</h2>
                    <p>Clique em uma aula para começar a aprender.</p>
                    <a href="{{ route('membership.lesson', [$product->id, $firstLesson->id]) }}" class="btn btn-primary">
                        <i class="bi bi-play-fill"></i>
                        Começar primeira aula
                    </a>
                </div>
            @else
                <div class="empty-content">
                    <div class="empty-icon">
                        <i class="bi bi-book"></i>
                    </div>
                    <h3>Nenhuma aula disponível</h3>
                    <p>Este curso ainda não possui aulas configuradas.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.course-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.course-header {
    background: linear-gradient(135deg, rgba(0, 123, 255, 0.1), rgba(0, 86, 179, 0.1));
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.course-title {
    font-size: 32px;
    font-weight: bold;
    margin-bottom: 10px;
    color: var(--text-light);
}

.course-description {
    font-size: 16px;
    color: var(--text-muted);
    margin-bottom: 20px;
}

.course-progress {
    margin-top: 20px;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    font-size: 14px;
}

.progress-percentage {
    font-weight: bold;
    color: var(--primary-blue);
}

.progress-bar {
    width: 100%;
    height: 8px;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-blue), #0056b3);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.course-content {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 30px;
    min-height: 600px;
}

.modules-list {
    background-color: var(--card-bg);
    border-radius: 15px;
    padding: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    max-height: 600px;
    overflow-y: auto;
}

.module-card {
    margin-bottom: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    overflow: hidden;
}

.module-header {
    background-color: rgba(255, 255, 255, 0.05);
    padding: 15px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
}

.module-header:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.module-title {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 5px;
    color: var(--text-light);
}

.module-description {
    font-size: 13px;
    color: var(--text-muted);
    margin-bottom: 10px;
}

.module-progress {
    font-size: 12px;
    color: var(--text-muted);
}

.module-progress-bar {
    width: 100%;
    height: 4px;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
    overflow: hidden;
    margin-top: 5px;
}

.module-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-blue), #0056b3);
    border-radius: 2px;
    transition: width 0.3s ease;
}

.module-toggle {
    color: var(--text-muted);
    transition: transform 0.3s ease;
}

.module-toggle.expanded {
    transform: rotate(180deg);
}

.lessons-list {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.lessons-list.expanded {
    max-height: 500px;
}

.lesson-item {
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.lesson-item:last-child {
    border-bottom: none;
}

.lesson-link {
    display: block;
    padding: 12px 15px;
    color: var(--text-light);
    text-decoration: none;
    transition: all 0.3s ease;
}

.lesson-link:hover {
    background-color: rgba(255, 255, 255, 0.05);
    color: var(--primary-blue);
    text-decoration: none;
}

.lesson-item.completed .lesson-link {
    color: #28a745;
}

.lesson-title {
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 5px;
    color: rgba(255, 255, 255, 0.7);
}

.lesson-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: var(--text-muted);
}

.lesson-status.completed {
    color: #28a745;
}

.content-area {
    background-color: var(--card-bg);
    border-radius: 15px;
    padding: 30px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.welcome-content {
    max-width: 400px;
}

.welcome-icon {
    font-size: 64px;
    color: var(--primary-blue);
    margin-bottom: 20px;
}

.welcome-content h2 {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 10px;
    color: var(--text-light);
}

.welcome-content p {
    font-size: 16px;
    color: var(--text-muted);
    margin-bottom: 30px;
}

.empty-content {
    max-width: 400px;
}

.empty-icon {
    font-size: 64px;
    color: var(--text-muted);
    margin-bottom: 20px;
}

.empty-content h3 {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 10px;
    color: var(--text-light);
}

.empty-content p {
    font-size: 16px;
    color: var(--text-muted);
}

@media (max-width: 768px) {
    .course-content {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .course-title {
        font-size: 24px;
    }
    
    .modules-list {
        max-height: 400px;
    }
}
</style>

<script>
function toggleModule(moduleId) {
    const moduleHeader = event.currentTarget;
    const lessonsList = document.getElementById(`module-${moduleId}`);
    const toggle = moduleHeader.querySelector('.module-toggle');
    
    lessonsList.classList.toggle('expanded');
    toggle.classList.toggle('expanded');
}
</script>
@endsection 