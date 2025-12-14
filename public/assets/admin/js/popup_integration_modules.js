function submitIntegrationForm() {
    // Здесь обработка данных формы
    const formData = {
        apiKey: document.getElementById('apiKey').value,
        serviceUrl: document.getElementById('serviceUrl').value,
        enableNotifications: document.getElementById('enableNotifications').checked
    };

    console.log('Данные формы:', formData);

    // Закрыть модальное окно после сохранения
    const modal = bootstrap.Modal.getInstance(document.getElementById('integrationModal'));
    modal.hide();

    // Дополнительно: можно показать уведомление об успешном сохранении
    alert('Данные успешно сохранены!');
}