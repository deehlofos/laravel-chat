@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Чат комнаты</h4>
                        <span class="badge bg-light text-dark" id="user-info">Загрузка...</span>
                    </div>

                    <div class="card-body">
                        <div id="messages-container"
                             style="height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; background: #f8f9fa; margin-bottom: 15px;">
                            <div class="text-center text-muted" id="loading-messages">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Загрузка...</span>
                                </div>
                                Загрузка сообщений...
                            </div>
                        </div>

                        <div class="input-group">
                            <input type="text"
                                   id="message-input"
                                   class="form-control form-control-lg"
                                   placeholder="Введите сообщение..."
                                   autocomplete="off">
                            <button class="btn btn-primary btn-lg d-flex"
                                    id="send-button"
                                    type="button">
                                <span class="me-2">Отправить</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11zM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07zm6.787-8.201L1.591 6.602l4.291 2.335 7.541-7.068z"/>
                                </svg>
                            </button>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            <kbd>Enter</kbd> для отправки сообщения
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            console.log('chat.blade.php → чистый Pusher');

            // Элементы DOM
            const messagesContainer = document.getElementById('messages-container');
            const messageInput      = document.getElementById('message-input');
            const sendButton        = document.getElementById('send-button');
            const loadingMessages   = document.getElementById('loading-messages');
            const userInfo          = document.getElementById('user-info');

            // Состояние
            let currentUser = null;
            let messages = [];
            let pusher = null;
            let channel = null;
            let reconnectAttempts = 0;
            const maxReconnect = 5;

            // Ключ приложения (из .env через blade)
            const appKey = '{{ env('REVERB_APP_KEY') }}';

            if (!appKey) {
                console.error('REVERB_APP_KEY не найден в blade');
                showError('Ошибка конфигурации ключа');
                return;
            }

            // Инициализация
            init();

            async function init() {
                await loadCurrentUser();
                await loadMessages();
                connectPusher();
                setupEventListeners();
            }
            function connectPusher() {
                console.log('Подключаемся к Pusher с ключом:', appKey);

                pusher = new Pusher(appKey, {
                    wsHost: '127.0.0.1',           // или 'localhost' или 'host.docker.internal'
                    wsPort: 8082,
                    forceTLS: false,
                    encrypted: false,
                    disableStats: true,
                    enabledTransports: ['ws'],
                });

                pusher.connection.bind('connected', function () {
                    console.log('Pusher успешно подключён');
                    reconnectAttempts = 0;

                    // Подписка на канал
                    channel = pusher.subscribe('chat');

                    channel.bind('pusher:subscription_succeeded', () => {
                        console.log('Подписка на канал chat успешна');
                    });

                    channel.bind('message.sent', function (data) {
                        console.log('Получено сообщение:', data);
                        messages.push(data);
                        appendMessage(data);
                        scrollToBottom();
                    });

                    channel.bind('pusher:subscription_error', function (err) {
                        console.error('Ошибка подписки:', err);
                        reconnectPusher();
                    });
                });

                pusher.connection.bind('error', function (err) {
                    console.error('Pusher ошибка соединения:', err);
                    reconnectPusher();
                });

                pusher.connection.bind('disconnected', function () {
                    console.warn('Pusher отключён');
                    reconnectPusher();
                });
            }

            function reconnectPusher() {
                if (reconnectAttempts >= maxReconnect) {
                    showError('Не удалось восстановить соединение с чатом. Обновите страницу.');
                    return;
                }

                reconnectAttempts++;
                console.log(`Попытка переподключения ${reconnectAttempts}/${maxReconnect}...`);
                setTimeout(connectPusher, 2000 * reconnectAttempts);
            }
            async function loadCurrentUser() {
                try {
                    const response = await fetch('/api/user');
                    if (response.ok) {
                        currentUser = await response.json();
                        userInfo.textContent = `Вы: ${currentUser.name}`;
                    }
                } catch (error) {
                    console.error('Ошибка загрузки пользователя:', error);
                    userInfo.textContent = 'Ошибка';
                }
            }

            async function loadMessages() {
                try {
                    const response = await fetch('/chat/messages');
                    if (response.ok) {
                        messages = await response.json();
                        renderMessages();
                    }
                } catch (error) {
                    console.error('Ошибка загрузки сообщений:', error);
                } finally {
                    loadingMessages.style.display = 'none';
                }
            }

            function setupEventListeners() {
                sendButton.addEventListener('click', sendMessage);
                messageInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        sendMessage();
                    }
                });
                messageInput.focus();
            }

            async function sendMessage() {
                const messageText = messageInput.value.trim();
                if (!messageText || sendButton.disabled) return;

                setSendingState(true);

                try {
                    const response = await fetch('/chat/send', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ message: messageText })
                    });

                    if (!response.ok) throw new Error('Ошибка отправки');

                    messageInput.value = '';
                } catch (error) {
                    console.error('Ошибка отправки:', error);
                    showError('Не удалось отправить сообщение');
                } finally {
                    setSendingState(false);
                }
            }

            function setSendingState(isSending) {
                sendButton.disabled = isSending;
                messageInput.disabled = isSending;
                sendButton.innerHTML = isSending
                    ? '<span class="spinner-border spinner-border-sm me-2"></span>Отправка...'
                    : '<span class="me-2">Отправить</span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11zM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07zm6.787-8.201L1.591 6.602l4.291 2.335 7.541-7.068z"/></svg>';
            }

            function renderMessages() {
                messagesContainer.innerHTML = '';
                if (messages.length === 0) {
                    messagesContainer.innerHTML = '<div class="text-center text-muted py-5">Пока нет сообщений. Будьте первым!</div>';
                    return;
                }
                messages.forEach(appendMessage);
                scrollToBottom();
            }

            function appendMessage(message) {
                const isOwn = currentUser && message.user?.id === currentUser.id;
                const div = document.createElement('div');
                div.className = `message ${isOwn ? 'message-own' : 'message-other'}`;

                const time = new Date(message.created_at).toLocaleTimeString('ru-RU', {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                div.innerHTML = `
                    <div class="message-header">
                        <strong>${escapeHtml(message.user?.name || 'Гость')}</strong>
                        <small>${time}</small>
                    </div>
                    <div class="message-body">
                        ${escapeHtml(message.message || '')}
                    </div>
                `;
                messagesContainer.appendChild(div);
            }

            function scrollToBottom() {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            function showError(text) {
                const div = document.createElement('div');
                div.className = 'alert alert-danger alert-dismissible fade show';
                div.innerHTML = `${text}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                messagesContainer.prepend(div);
                setTimeout(() => div.remove(), 7000);
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        });
    </script>
@endpush
