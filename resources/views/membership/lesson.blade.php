@extends('membership.course-layout')

@section('title', $product->title)

@section('content')
<div class="lesson-container">
    <div class="lesson-content">
        <!-- Área de Conteúdo -->
        <div class="content-area">
            @if($lesson->content_type === 'video')
                <div class="video-container">
                    <video 
                        id="lesson-video" 
                        controls 
                        class="lesson-video"
                        data-lesson-id="{{ $lesson->id }}"
                        ontimeupdate="updateVideoProgress()"
                        onended="markLessonCompleted()">
                        <source src="{{ $lesson->content_url }}" type="video/mp4">
                        Seu navegador não suporta vídeos.
                    </video>
                </div>
            @elseif($lesson->content_type === 'iframe')
                <div class="iframe-container">
                    <iframe 
                        src="{{ $lesson->content_url }}" 
                        frameborder="0" 
                        allowfullscreen
                        class="lesson-iframe">
                    </iframe>
                </div>
            @elseif($lesson->content_type === 'file')
                <div class="file-container">
                    <div class="file-info">
                        <div class="file-icon">
                            <i class="bi bi-file-earmark-arrow-down"></i>
                        </div>
                        <div class="file-details">
                            <h3>{{ $lesson->title }}</h3>
                            <p>{{ $lesson->description }}</p>
                            <div class="file-meta">
                                <span class="file-size">
                                    <i class="bi bi-hdd"></i>
                                    {{ $lesson->getFormattedFileSize() }}
                                </span>
                                <span class="file-type">
                                    <i class="bi bi-file-earmark"></i>
                                    {{ $lesson->getFileExtension() }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="file-actions">
                        <a href="{{ route('membership.download', $lesson->id) }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-download"></i>
                            Baixar Arquivo
                        </a>
                        <button onclick="{{ $userProgress && $userProgress->is_completed ? 'markLessonUncompleted()' : 'markLessonCompleted()' }}" class="btn {{ $userProgress && $userProgress->is_completed ? 'btn-secondary' : 'btn-success' }} btn-lg" id="markCompletedBtn">
                            <i class="bi {{ $userProgress && $userProgress->is_completed ? 'bi-check-circle-fill' : 'bi-check-circle' }}"></i>
                            <span id="markCompletedText">{{ $userProgress && $userProgress->is_completed ? 'Desmarcar como Concluída' : 'Marcar como Concluída' }}</span>
                        </button>
                    </div>
                </div>
            @else
                <div class="text-content">
                    {!! nl2br(e($lesson->content_text)) !!}
                    <div class="text-actions">
                        <button onclick="{{ $userProgress && $userProgress->is_completed ? 'markLessonUncompleted()' : 'markLessonCompleted()' }}" class="btn {{ $userProgress && $userProgress->is_completed ? 'btn-secondary' : 'btn-success' }}" id="markCompletedBtn">
                            <i class="bi {{ $userProgress && $userProgress->is_completed ? 'bi-check-circle-fill' : 'bi-check-circle' }}"></i>
                            <span id="markCompletedText">{{ $userProgress && $userProgress->is_completed ? 'Desmarcar como Concluída' : 'Marcar como Concluída' }}</span>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Seção de Comentários -->
            <div class="comments-section">
                <div class="comments-header">
                    <h3>Comentários ({{ $comments->count() }})</h3>
                </div>
                
                <!-- Formulário de Novo Comentário -->
                <div class="comment-form">
                    <div class="comment-input">
                        <textarea id="commentContent" placeholder="Adicione um comentário..." rows="3" maxlength="1000"></textarea>
                        <div class="comment-actions">
                            <span class="char-count">0/1000</span>
                            <button onclick="submitComment()" class="btn" style="background-color: var(--primary-blue); border-color: var(--primary-blue); color: white;">
                                <i class="bi bi-send"></i>
                                Comentar
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de Comentários -->
                <div class="comments-list" id="commentsList">
                    @foreach($comments as $comment)
                        <div class="comment-item" data-comment-id="{{ $comment->id }}">
                            <div class="comment-header">
                                <div class="comment-author">
                                    <div class="author-avatar">
                                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                    </div>
                                    <div class="author-info">
                                        <span class="author-name">{{ $comment->user->name }}</span>
                                        <span class="comment-date">{{ $comment->formatted_date }}</span>
                                    </div>
                                </div>
                                @if($comment->canBeEditedBy($user))
                                    <div class="comment-actions">
                                        <button onclick="editComment({{ $comment->id }})" class="btn-edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button onclick="deleteComment({{ $comment->id }})" class="btn-delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <div class="comment-content">
                                <p>{{ $comment->content }}</p>
                            </div>
                            <div class="comment-footer">
                                <button onclick="showReplyForm({{ $comment->id }})" class="btn-reply">
                                    <i class="bi bi-reply"></i>
                                    Responder
                                </button>
                            </div>
                            
                            <!-- Formulário de Resposta (oculto) -->
                            <div class="reply-form" id="replyForm{{ $comment->id }}" style="display: none;">
                                <div class="reply-input">
                                    <textarea placeholder="Escreva sua resposta..." rows="2" maxlength="1000"></textarea>
                                    <div class="reply-actions">
                                        <button onclick="submitReply({{ $comment->id }})" class="btn btn-sm" style="background-color: var(--primary-blue); border-color: var(--primary-blue); color: white;">
                                            <i class="bi bi-send"></i>
                                            Responder
                                        </button>
                                        <button onclick="hideReplyForm({{ $comment->id }})" class="btn btn-secondary btn-sm">
                                            Cancelar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Respostas -->
                            @if($comment->replies->count() > 0)
                                <div class="replies-list">
                                    @foreach($comment->replies as $reply)
                                        <div class="reply-item">
                                            <div class="reply-header">
                                                <div class="reply-author">
                                                    <div class="author-avatar small">
                                                        {{ strtoupper(substr($reply->user->name, 0, 1)) }}
                                                    </div>
                                                    <div class="author-info">
                                                        <span class="author-name">{{ $reply->user->name }}</span>
                                                        <span class="reply-date">{{ $reply->formatted_date }}</span>
                                                    </div>
                                                </div>
                                                @if($reply->canBeEditedBy($user))
                                                    <div class="reply-actions">
                                                        <button onclick="editReply({{ $reply->id }})" class="btn-edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button onclick="deleteComment({{ $reply->id }})" class="btn-delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="reply-content">
                                                <p>{{ $reply->content }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar com Lista de Aulas -->
        <div class="lessons-sidebar">
            <div class="sidebar-header">
                <h3>Aulas</h3>
            </div>

            <div class="lessons-list">
                @foreach($modules as $module)
                    <div class="module-section">
                        <div class="module-header">
                            <h4>{{ $module->title }}</h4>
                        </div>
                        
                        <div class="module-lessons">
                            @foreach($module->activeLessons as $moduleLesson)
                                <a href="{{ route('membership.lesson', [$product->id, $moduleLesson->id]) }}" 
                                   class="lesson-item {{ $moduleLesson->id === $lesson->id ? 'active' : '' }} {{ $moduleLesson->isCompleted($user->id) ? 'completed' : '' }}">
                                    <div class="lesson-info">
                                        <div class="lesson-title">{{ $moduleLesson->title }}</div>
                                        <div class="lesson-progress-bar">
                                            <div class="lesson-progress-fill" style="width: {{ $moduleLesson->getProgressPercentage($user->id) }}%"></div>
                                        </div>
                                        <div class="lesson-meta">
                                            <span class="lesson-duration">{{ $moduleLesson->getFormattedDuration() }}</span>
                                            <span class="lesson-progress-text">{{ $moduleLesson->getProgressPercentage($user->id) }}%</span>
                                            @if($moduleLesson->isCompleted($user->id))
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
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão de Comentário -->
<div class="modal fade" id="deleteCommentModal" tabindex="-1" aria-labelledby="deleteCommentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCommentModalLabel">
                    <i class="bi bi-exclamation-triangle text-warning"></i>
                    Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este comentário?</p>
                <p class="text-muted small">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteComment" onclick="confirmDeleteComment()">
                    <i class="bi bi-trash"></i>
                    Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.lesson-container {
    max-width: 100%;
    margin: 0;
    height: calc(100vh - 70px);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
}





.lesson-meta {
    display: flex;
    gap: 15px;
    font-size: 12px;
    color: var(--text-muted);
    flex-wrap: wrap;
}

.lesson-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.lesson-progress {
    min-width: 200px;
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

.lesson-content {
    display: grid;
    grid-template-columns: 3fr 1fr;
    gap: 0;
    flex: 1;
    height: 100%;
    overflow: hidden;
}

.content-area {
    background-color: var(--card-bg);
    padding: 20px;
    border: none;
    height: 100%;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    overflow-x: hidden;
}

.video-container {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.lesson-video {
    width: 100%;
    max-width: 800px;
    border-radius: 10px;
    background-color: #000;
}

.iframe-container {
    width: 100%;
    height: 600px;
    border-radius: 10px;
    overflow: hidden;
}

.lesson-iframe {
    width: 100%;
    height: 100%;
    border-radius: 10px;
}

.text-content {
    padding: 20px;
    line-height: 1.6;
    color: var(--text-light);
    font-size: 16px;
}

.text-actions {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: flex-end;
}

.file-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 400px;
    text-align: center;
    padding: 40px 20px;
}

.file-info {
    margin-bottom: 40px;
}

.file-icon {
    font-size: 80px;
    color: var(--primary-blue);
    margin-bottom: 20px;
}

.file-details h3 {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 10px;
    color: var(--text-light);
}

.file-details p {
    font-size: 16px;
    color: var(--text-muted);
    margin-bottom: 20px;
}

.file-meta {
    display: flex;
    justify-content: center;
    gap: 20px;
    font-size: 14px;
    color: var(--text-muted);
}

.file-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.file-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    justify-content: flex-end;
    align-items: center;
}

.file-actions .btn {
    min-width: 180px;
}

/* Estilos dos Comentários */
.comments-section {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.comments-header h3 {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 20px;
    color: var(--text-light);
}

.comment-form {
    margin-bottom: 30px;
}

.comment-input {
    background-color: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    padding: 15px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.comment-input textarea {
    width: 100%;
    background: transparent;
    border: none;
    color: var(--text-light);
    font-size: 14px;
    resize: vertical;
    min-height: 80px;
}

.comment-input textarea:focus {
    outline: none;
    box-shadow: none;
}

.comment-input textarea::placeholder {
    color: var(--text-muted);
}

.comment-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
}

.char-count {
    font-size: 12px;
    color: var(--text-muted);
}

.comments-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.comment-item {
    background-color: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    padding: 15px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.comment-author {
    display: flex;
    align-items: center;
    gap: 10px;
}

.author-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: var(--primary-blue);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
    font-size: 14px;
}

.author-avatar.small {
    width: 24px;
    height: 24px;
    font-size: 12px;
}

.author-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.author-name {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-light);
}

.comment-date, .reply-date {
    font-size: 12px;
    color: var(--text-muted);
}

.comment-actions {
    display: flex;
    gap: 5px;
}

.btn-edit, .btn-delete {
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 5px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.btn-edit:hover {
    color: var(--primary-blue);
    background-color: rgba(0, 123, 255, 0.1);
}

.btn-delete:hover {
    color: #dc3545;
    background-color: rgba(220, 53, 69, 0.1);
}

.comment-content p {
    font-size: 14px;
    line-height: 1.5;
    color: var(--text-light);
    margin: 0;
}

.comment-footer {
    margin-top: 10px;
}

.btn-reply {
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: color 0.3s ease;
}

.btn-reply:hover {
    color: var(--primary-blue);
}

.reply-form {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.reply-input textarea {
    width: 100%;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    color: var(--text-light);
    font-size: 13px;
    padding: 10px;
    resize: vertical;
    min-height: 60px;
}

.reply-input textarea:focus {
    outline: none;
    border-color: var(--primary-blue);
}

.reply-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.replies-list {
    margin-top: 15px;
    padding-left: 20px;
    border-left: 2px solid rgba(255, 255, 255, 0.1);
}

.reply-item {
    background-color: rgba(255, 255, 255, 0.03);
    border-radius: 6px;
    padding: 10px;
    margin-bottom: 10px;
}

.reply-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.reply-author {
    display: flex;
    align-items: center;
    gap: 8px;
}

.reply-content p {
    font-size: 13px;
    line-height: 1.4;
    color: var(--text-light);
    margin: 0;
}

/* Estilos do Modal de Exclusão */
.modal-backdrop {
    backdrop-filter: blur(10px);
    background-color: rgba(0, 0, 0, 0.7) !important;
}

.modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100% - 1rem);
}

.modal-content {
    background-color: var(--card-bg);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

.modal-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 20px;
}

.modal-title {
    color: var(--text-light);
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-body {
    padding: 20px;
    color: var(--text-light);
}

.modal-body p {
    margin-bottom: 10px;
}

.modal-footer {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding: 20px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.modal .btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 5px;
}

.modal .btn-secondary {
    background-color: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: var(--text-light);
}

.modal .btn-secondary:hover {
    background-color: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.3);
}

.modal .btn-danger {
    background-color: #dc3545;
    border: 1px solid #dc3545;
    color: white;
}

.modal .btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

.btn-close {
    filter: invert(1);
}

/* Botões com cor primária personalizada */
.btn[style*="--primary-blue"] {
    transition: all 0.3s ease;
}

.btn[style*="--primary-blue"]:hover {
    background-color: var(--primary-blue) !important;
    border-color: var(--primary-blue) !important;
    filter: brightness(1.1);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.btn[style*="--primary-blue"]:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.lessons-sidebar {
    background-color: var(--card-bg);
    border-left: 1px solid rgba(255, 255, 255, 0.1);
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow-x: hidden;
}

.sidebar-header {
    padding: 12px 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    background-color: rgba(255, 255, 255, 0.05);
}

.sidebar-header h3 {
    font-size: 14px;
    font-weight: bold;
    margin: 0;
    color: var(--text-light);
}

.lessons-list {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 0;
    min-height: 0;
}

.module-section {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.module-section:last-child {
    border-bottom: none;
}

.module-header {
    padding: 10px 15px;
    background-color: rgba(255, 255, 255, 0.02);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.module-header h4 {
    font-size: 13px;
    font-weight: bold;
    color: var(--text-light);
    margin: 0;
}

.module-lessons {
    padding: 0;
}

.lesson-item {
    display: block;
    padding: 8px 15px;
    color: var(--text-light);
    text-decoration: none;
    transition: all 0.3s ease;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    overflow: hidden;
    word-wrap: break-word;
}

.lesson-item:hover {
    background-color: rgba(255, 255, 255, 0.05);
    color: var(--primary-blue);
    text-decoration: none;
}

.lesson-item.active {
    background-color: rgba(0, 123, 255, 0.1);
    color: var(--primary-blue);
}

.lesson-item.completed {
    color: #28a745;
}

.lesson-item.completed:hover {
    color: #28a745;
}

.lesson-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.lesson-title {
    font-size: 12px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.7);
}

.lesson-progress-bar {
    width: 100%;
    height: 3px;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
    overflow: hidden;
}

.lesson-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-blue), #0056b3);
    border-radius: 2px;
    transition: width 0.3s ease;
}

.lesson-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 10px;
    color: var(--text-muted);
}

.lesson-progress-text {
    color: var(--primary-blue);
    font-weight: 500;
}

.lesson-status.completed {
    color: #28a745;
}

@media (max-width: 1024px) {
    .lesson-content {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .lesson-header {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }
    
    .lesson-progress {
        min-width: auto;
        width: 100%;
    }
    
    .lessons-sidebar {
        max-height: 400px;
    }
}

@media (max-width: 768px) {
    .lesson-title {
        font-size: 24px;
    }
    
    .lesson-meta {
        flex-direction: column;
        gap: 10px;
    }
    
    .iframe-container {
        height: 400px;
    }
}
</style>

<script>
let videoElement = null;
let progressUpdateInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    videoElement = document.getElementById('lesson-video');
    
    if (videoElement) {
        // Carregar progresso salvo
        loadVideoProgress();
        
        // Salvar progresso a cada 5 segundos
        progressUpdateInterval = setInterval(saveVideoProgress, 5000);
    }
});

function updateVideoProgress() {
    if (videoElement) {
        const currentTime = videoElement.currentTime;
        const duration = videoElement.duration;
        
        if (duration > 0) {
            const percentage = (currentTime / duration) * 100;
            updateProgressBar(percentage);
        }
    }
}

function saveVideoProgress() {
    if (videoElement) {
        const currentTime = Math.floor(videoElement.currentTime);
        
        fetch('{{ route("membership.progress.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                lesson_id: {{ $lesson->id }},
                watched_seconds: currentTime,
                is_completed: false
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Progresso salvo:', currentTime);
            }
        })
        .catch(error => {
            console.error('Erro ao salvar progresso:', error);
        });
    }
}

function markLessonUncompleted() {
    fetch('{{ route("membership.progress.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            lesson_id: {{ $lesson->id }},
            is_completed: false
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualizar barra de progresso da aula atual
            updateProgressBar(0);
            
            // Atualizar item da aula na sidebar
            const lessonItem = document.querySelector('.lesson-item.active');
            if (lessonItem) {
                lessonItem.classList.remove('completed');
                
                // Atualizar ícone de status
                const statusIcon = lessonItem.querySelector('.lesson-status i');
                if (statusIcon) {
                    statusIcon.className = 'bi bi-circle';
                }
                
                // Atualizar barra de progresso na sidebar
                const progressFill = lessonItem.querySelector('.lesson-progress-fill');
                if (progressFill) {
                    progressFill.style.width = '0%';
                }
                
                // Atualizar texto de progresso
                const progressText = lessonItem.querySelector('.lesson-progress-text');
                if (progressText) {
                    progressText.textContent = '0%';
                }
            }
            
            // Mostrar feedback visual
            const button = document.getElementById('markCompletedBtn');
            const buttonText = document.getElementById('markCompletedText');
            if (button && buttonText) {
                button.classList.remove('btn-secondary');
                button.classList.add('btn-success');
                buttonText.textContent = 'Marcar como Concluída';
                button.onclick = function() {
                    markLessonCompleted();
                };
            }
            
            // Recarregar a página após 2 segundos para atualizar todas as informações
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Erro ao desmarcar como completo:', error);
        alert('Erro ao desmarcar aula como concluída. Tente novamente.');
    });
}

function markLessonCompleted() {
    fetch('{{ route("membership.progress.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            lesson_id: {{ $lesson->id }},
            is_completed: true
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualizar barra de progresso da aula atual
            updateProgressBar(100);
            
            // Atualizar item da aula na sidebar
            const lessonItem = document.querySelector('.lesson-item.active');
            if (lessonItem) {
                lessonItem.classList.add('completed');
                
                // Atualizar ícone de status
                const statusIcon = lessonItem.querySelector('.lesson-status i');
                if (statusIcon) {
                    statusIcon.className = 'bi bi-check-circle-fill';
                }
                
                // Atualizar barra de progresso na sidebar
                const progressFill = lessonItem.querySelector('.lesson-progress-fill');
                if (progressFill) {
                    progressFill.style.width = '100%';
                }
                
                // Atualizar texto de progresso
                const progressText = lessonItem.querySelector('.lesson-progress-text');
                if (progressText) {
                    progressText.textContent = '100%';
                }
            }
            
            // Mostrar feedback visual
            const button = document.getElementById('markCompletedBtn');
            const buttonText = document.getElementById('markCompletedText');
            if (button && buttonText) {
                button.classList.remove('btn-success');
                button.classList.add('btn-secondary');
                buttonText.textContent = 'Desmarcar como Concluída';
                button.onclick = function() {
                    markLessonUncompleted();
                };
            }
            
            // Recarregar a página após 2 segundos para atualizar todas as informações
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Erro ao marcar como completo:', error);
        alert('Erro ao marcar aula como concluída. Tente novamente.');
    });
}

function loadVideoProgress() {
    // Carregar progresso do localStorage ou fazer requisição para buscar progresso salvo
    const savedProgress = localStorage.getItem(`lesson_${lessonId}_progress`);
    if (savedProgress && videoElement) {
        const progress = JSON.parse(savedProgress);
        if (progress.watched_seconds > 0) {
            videoElement.currentTime = progress.watched_seconds;
        }
    }
}

function updateProgressBar(percentage) {
    const progressFill = document.querySelector('.progress-fill');
    if (progressFill) {
        progressFill.style.width = percentage + '%';
    }
    
    const progressPercentage = document.querySelector('.progress-percentage');
    if (progressPercentage) {
        progressPercentage.textContent = Math.round(percentage) + '%';
    }
}

// Limpar intervalo quando a página for fechada
window.addEventListener('beforeunload', function() {
    if (progressUpdateInterval) {
        clearInterval(progressUpdateInterval);
    }
});

// Funcionalidades dos Comentários
document.addEventListener('DOMContentLoaded', function() {
    // Contador de caracteres
    const commentTextarea = document.getElementById('commentContent');
    const charCount = document.querySelector('.char-count');
    
    if (commentTextarea && charCount) {
        commentTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = `${length}/1000`;
            
            if (length > 900) {
                charCount.style.color = '#dc3545';
            } else if (length > 800) {
                charCount.style.color = '#ffc107';
            } else {
                charCount.style.color = 'var(--text-muted)';
            }
        });
    }
    
    // Limpar variável quando modal for fechado
    const deleteModal = document.getElementById('deleteCommentModal');
    if (deleteModal) {
        deleteModal.addEventListener('hidden.bs.modal', function() {
            commentToDelete = null;
        });
    }
});

function submitComment() {
    const content = document.getElementById('commentContent').value.trim();
    
    if (!content) {
        alert('Por favor, escreva um comentário.');
        return;
    }
    
    fetch('{{ route("membership.comments.store", $lesson->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Erro ao adicionar comentário.');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao adicionar comentário.');
    });
}

function showReplyForm(commentId) {
    const replyForm = document.getElementById(`replyForm${commentId}`);
    replyForm.style.display = 'block';
    replyForm.querySelector('textarea').focus();
}

function hideReplyForm(commentId) {
    const replyForm = document.getElementById(`replyForm${commentId}`);
    replyForm.style.display = 'none';
    replyForm.querySelector('textarea').value = '';
}

function submitReply(commentId) {
    const replyForm = document.getElementById(`replyForm${commentId}`);
    const content = replyForm.querySelector('textarea').value.trim();
    
    if (!content) {
        alert('Por favor, escreva uma resposta.');
        return;
    }
    
    fetch('{{ route("membership.comments.store", $lesson->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            content: content,
            parent_id: commentId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Erro ao adicionar resposta.');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao adicionar resposta.');
    });
}

function editComment(commentId) {
    const commentItem = document.querySelector(`[data-comment-id="${commentId}"]`);
    const contentDiv = commentItem.querySelector('.comment-content p');
    const currentContent = contentDiv.textContent;
    
    contentDiv.innerHTML = `
        <textarea class="edit-textarea" style="width: 100%; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-radius: 4px; padding: 8px; color: var(--text-light); font-size: 14px; min-height: 60px;">${currentContent}</textarea>
        <div style="margin-top: 10px;">
            <button onclick="saveCommentEdit(${commentId})" class="btn btn-primary btn-sm">Salvar</button>
            <button onclick="cancelCommentEdit(${commentId})" class="btn btn-secondary btn-sm">Cancelar</button>
        </div>
    `;
}

function saveCommentEdit(commentId) {
    const commentItem = document.querySelector(`[data-comment-id="${commentId}"]`);
    const textarea = commentItem.querySelector('.edit-textarea');
    const content = textarea.value.trim();
    
    if (!content) {
        alert('O comentário não pode estar vazio.');
        return;
    }
    
    fetch('/comments/' + commentId, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Erro ao atualizar comentário.');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao atualizar comentário.');
    });
}

function cancelCommentEdit(commentId) {
    window.location.reload();
}

let commentToDelete = null;

function deleteComment(commentId) {
    commentToDelete = commentId;
    
    // Mostrar modal de confirmação
    const modal = new bootstrap.Modal(document.getElementById('deleteCommentModal'));
    modal.show();
}

function confirmDeleteComment() {
    if (!commentToDelete) return;
    
    fetch('/comments/' + commentToDelete, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteCommentModal'));
            modal.hide();
            
            // Recarregar página
            window.location.reload();
        } else {
            alert(data.message || 'Erro ao excluir comentário.');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao excluir comentário.');
    });
}
</script>
@endsection 