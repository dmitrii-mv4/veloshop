$(document).ready(function () {
    console.log('News module loaded');

    // Инициализация Clipboard.js
    new ClipboardJS('.copy-btn');

    // Обработка кнопок копирования
    $('.copy-btn').on('click', function () {
        var $btn = $(this);
        var originalHtml = $btn.html();

        $btn.html('<i class="bi bi-check"></i>');
        $btn.removeClass('btn-outline-secondary').addClass('btn-success');

        setTimeout(function () {
            $btn.html(originalHtml);
            $btn.removeClass('btn-success').addClass('btn-outline-secondary');
        }, 2000);
    });

    // Обработка удаления записей
    $('.delete-btn').on('click', function () {
        var itemId = $(this).data('id');
        var itemTitle = $(this).data('title');
        var deleteUrl = $(this).data('url');

        $('#itemTitleToDelete').text(itemTitle);
        $('#deleteForm').attr('action', deleteUrl);

        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    });

    // Отправка формы удаления
    $('#deleteForm').on('submit', function (e) {
        e.preventDefault();

        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.html();

        // Показываем индикатор загрузки
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="loading-spinner" style="display:inline-block;"></span> Удаление...');

        $.ajax({
            url: form.attr('action'),
            method: 'DELETE',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    showNotification('success', response.message);

                    // Закрываем модальное окно
                    var deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                    deleteModal.hide();

                    // Обновляем страницу через 1 секунду
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification('danger', response.message);
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalText);
                }
            },
            error: function (xhr) {
                var errorMessage = 'Ошибка при удалении записи';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                showNotification('danger', errorMessage);
                submitBtn.prop('disabled', false);
                submitBtn.html(originalText);

                console.error('Delete error:', xhr);
            }
        });
    });

    // Функция показа уведомлений
    function showNotification(type, message) {
        var alertClass = 'alert-' + type;
        var iconClass = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';

        var notification = $(
            '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
            '<i class="bi ' + iconClass + ' me-2"></i>' + message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
            '</div>'
        );

        $('#notificationContainer').html(notification);

        // Автоматическое скрытие через 5 секунд
        setTimeout(function () {
            notification.alert('close');
        }, 5000);
    }

    // Логирование действий пользователя
    $('a, button').on('click', function () {
        var elementText = $(this).text().trim() || $(this).attr('title') || $(this).attr('aria-label');
        if (elementText) {
            console.log('User clicked:', elementText);
        }
    });

    // Адаптивная таблица
    function adjustTableForMobile() {
        if ($(window).width() < 768) {
            $('.table-responsive table').addClass('table-sm');
        } else {
            $('.table-responsive table').removeClass('table-sm');
        }
    }

    // Вызов при загрузке и изменении размера окна
    adjustTableForMobile();
    $(window).on('resize', adjustTableForMobile);

    // Предотвращение отправки формы при нажатии Enter в поле поиска
    $('input[name="search"]').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $(this).closest('form').submit();
        }
    });

    // Плавная прокрутка к верху при пагинации
    $('.pagination a').on('click', function () {
        $('html, body').animate({
            scrollTop: 0
        }, 300);
    });
});