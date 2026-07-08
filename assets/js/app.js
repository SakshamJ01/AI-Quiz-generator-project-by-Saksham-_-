$(function () {
    $(document).on('click', '.menu-toggle', function () {
        $('body').toggleClass('sidebar-open');
    });

    $(document).on('click', '.flash', function () {
        $(this).fadeOut(180);
    });

    $(document).on('click', '.password-toggle', function () {
        const input = $(this).closest('.password-field').find('input');
        const icon = $(this).find('.material-symbols-outlined');
        const isPassword = input.attr('type') === 'password';
        input.attr('type', isPassword ? 'text' : 'password');
        icon.text(isPassword ? 'visibility' : 'visibility_off');
    });

    $(document).on('click', '#add-question', function () {
        const list = $('#question-list');
        const index = list.children('.quiz-question').length;
        const questionHtml = `
            <section class="quiz-question">
                <div class="field-row" style="justify-content: space-between; align-items: center;">
                    <strong>Question ${index + 1}</strong>
                    <button type="button" class="ghost-button remove-question">Remove</button>
                </div>
                <div class="field">
                    <label>Question text</label>
                    <textarea name="questions[${index}][text]" required placeholder="Enter question"></textarea>
                </div>
                <div class="field-row">
                    <div class="field" style="flex: 1; min-width: 240px;">
                        <label>Option A</label>
                        <input name="questions[${index}][options][]" required placeholder="Option A">
                    </div>
                    <div class="field" style="flex: 1; min-width: 240px;">
                        <label>Option B</label>
                        <input name="questions[${index}][options][]" required placeholder="Option B">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field" style="flex: 1; min-width: 240px;">
                        <label>Option C</label>
                        <input name="questions[${index}][options][]" placeholder="Option C">
                    </div>
                    <div class="field" style="flex: 1; min-width: 240px;">
                        <label>Option D</label>
                        <input name="questions[${index}][options][]" placeholder="Option D">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field" style="flex: 1; min-width: 220px;">
                        <label>Correct option number</label>
                        <select name="questions[${index}][correct]">
                            <option value="0">1</option>
                            <option value="1">2</option>
                            <option value="2">3</option>
                            <option value="3">4</option>
                        </select>
                    </div>
                    <div class="field" style="flex: 1; min-width: 220px;">
                        <label>Points</label>
                        <input type="number" min="1" name="questions[${index}][points]" value="1">
                    </div>
                </div>
            </section>`;
        list.append(questionHtml);
    });

    $(document).on('click', '.remove-question', function () {
        $(this).closest('.quiz-question').remove();
    });

    const flash = $('.flash');
    if (flash.length) {
        setTimeout(function () {
            flash.fadeOut(250);
        }, 4000);
    }
});
$(function () {
    $(document).on('click', '.menu-toggle', function () {
        $('body').toggleClass('sidebar-open');
    });

    $(document).on('click', '.flash', function () {
        $(this).fadeOut(180);
    });

    $(document).on('click', '.password-toggle', function () {
        const input = $(this).closest('.password-field').find('input');
        const icon = $(this).find('.material-symbols-outlined');
        const isPassword = input.attr('type') === 'password';
        input.attr('type', isPassword ? 'text' : 'password');
        icon.text(isPassword ? 'visibility' : 'visibility_off');
    });

    $(document).on('click', '#add-question', function () {
        const list = $('#question-list');
        const index = list.children('.quiz-question').length;
        const questionHtml = `
            <section class="quiz-question">
                <div class="field-row" style="justify-content: space-between; align-items: center;">
                    <strong>Question ${index + 1}</strong>
                    <button type="button" class="ghost-button remove-question">Remove</button>
                </div>
                <div class="field">
                    <label>Question text</label>
                    <textarea name="questions[${index}][text]" required placeholder="Enter question"></textarea>
                </div>
                <div class="field-row">
                    <div class="field" style="flex: 1; min-width: 240px;">
                        <label>Option A</label>
                        <input name="questions[${index}][options][]" required placeholder="Option A">
                    </div>
                    <div class="field" style="flex: 1; min-width: 240px;">
                        <label>Option B</label>
                        <input name="questions[${index}][options][]" required placeholder="Option B">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field" style="flex: 1; min-width: 240px;">
                        <label>Option C</label>
                        <input name="questions[${index}][options][]" placeholder="Option C">
                    </div>
                    <div class="field" style="flex: 1; min-width: 240px;">
                        <label>Option D</label>
                        <input name="questions[${index}][options][]" placeholder="Option D">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field" style="flex: 1; min-width: 220px;">
                        <label>Correct option number</label>
                        <select name="questions[${index}][correct]">
                            <option value="0">1</option>
                            <option value="1">2</option>
                            <option value="2">3</option>
                            <option value="3">4</option>
                        </select>
                    </div>
                    <div class="field" style="flex: 1; min-width: 220px;">
                        <label>Points</label>
                        <input type="number" min="1" name="questions[${index}][points]" value="1">
                    </div>
                </div>
            </section>`;
        list.append(questionHtml);
    });

    $(document).on('click', '.remove-question', function () {
        $(this).closest('.quiz-question').remove();
    });

    const flash = $('.flash');
    if (flash.length) {
        setTimeout(function () {
            flash.fadeOut(250);
        }, 4000);
    }
});
