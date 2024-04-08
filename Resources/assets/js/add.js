let editor = ace.edit('editor');

let unformattedContent = editor.getSession().getValue();
let formattedContent = js_beautify(unformattedContent, {
    indent_size: 4,
    space_in_empty_paren: true,
});
editor.getSession().setValue(formattedContent);

editor.setTheme('ace/theme/solarized_dark');
editor.session.setMode('ace/mode/json');

$(document).on('submit', '#add, #edit', (ev) => {
    let $form = $(ev.currentTarget);

    ev.preventDefault();
    let path = $form.attr('id'),
        form = serializeForm($form);

    let url = `admin/api/module_stats/${path}`,
        method = 'POST';

    if (path === 'edit') {
        url = `admin/api/module_stats/${form.id}`;
        method = 'PUT';
    }

    if (ev.target.checkValidity()) {
        sendRequest(
            {
                ...form,
                ...{
                    additional: editor.getValue(),
                },
            },
            url,
            method,
        );
    }
});
