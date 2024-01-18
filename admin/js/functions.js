document.addEventListener('keydown', function (event) {
    if (event.key === 'D') {
        alert('D key was pressed');
    }
});

function wordcount_update() {
    if (tinyMCE.activeEditor) {
        var content = tinyMCE.activeEditor.getContent({format: 'text'});
        var wordCount = content.split(/\s+/).filter(function (word) {
            return word.length > 0;
        }).length;
        document.getElementById('wordcount_value').innerText = wordCount + ' words';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    setInterval(wordcount_update, 10000);
});

document.addEventListener('DOMContentLoaded', function () {
    if (typeof tinyMCE !== 'undefined') {
        tinyMCE.on('AddEditor', function(e) {
            e.editor.on('keydown', function(event) {
                // Check if CTRL and Spacebar are pressed
                if (event.ctrlKey && event.keyCode === 32) {
                    event.preventDefault();
                    wrapTextWithCodeTag(e.editor);
                }
            });
        });
    }
});

function wrapTextWithCodeTag() {
    var editor = tinyMCE.activeEditor;
    var selectedText = editor.selection.getContent({format: 'html'});

    if (selectedText.startsWith('<code>') && selectedText.endsWith('</code>')) {
        editor.selection.setContent(selectedText.slice(6, -7));
    } else {
        editor.selection.setContent('<code>' + selectedText + '</code>');
    }
}
