<!-- Единое модальное окно для всех переводов -->
<div class="modalTranslationPropert modal fade mycustom" id="universalTranslationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="universalModalTitle">Переводы</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="translationContext">
                <input type="hidden" id="translationIndex">
                <input type="hidden" id="translationCode">
                
                <div class="table-responsive">
                    <table class="table table-vcenter">
                        <thead>
                            <tr><th>Язык</th><th>Перевод</th></tr>
                        </thead>
                        <tbody id="universalTranslationsTableBody">
                            <!-- Динамически заполняется -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="saveUniversalTranslationsBtn">Сохранить переводы</button>
            </div>
        </div>
    </div>
</div>