var header_code = document.getElementById("header_code");
var editor = CodeMirror.fromTextArea(header_code, {
    lineNumbers: true,
    mode: "text/css",
    theme: "blackboard"
});

editor.setSize("70%", 300);

var footer_code = document.getElementById("footer_code");
var editor2 = CodeMirror.fromTextArea(footer_code, {
    lineNumbers: true,
    mode: "text/css",
    theme: "blackboard"
});

editor2.setSize("70%", 300);