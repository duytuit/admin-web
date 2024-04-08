function jsView(tpl, data) {
    var re = /<%([^%>]+)?%>/g,
        reExp = /(^( )?(if|for|else|switch|case|break|{|}))(.*)?/g,
        code = 'var r=[];\n',
        cursor = 0,
        match;

    var add = function (line, js) {
        if (js) {
            line = line.replace('$', 'this.');
            code += line.match(reExp) ? line + '\n' : 'r.push(' + line + ');\n';
        } else {
            code += line != '' ? 'r.push("' + line.replace(/"/g, '\\"') + '");\n' : '';
        }
    }

    while (match = re.exec(tpl)) {
        add(tpl.slice(cursor, match.index));
        add(match[1], true);
        cursor = match.index + match[0].length;
    }

    add(tpl.substr(cursor, tpl.length - cursor));

    code += 'return r.join("");';
    code = code.replace(/[\r\t\n]/g, '');
    code = code.replace(/\s+/g, ' ');

    try {
        result = new Function(code).apply(data);
    } catch (err) {
        console.error("'" + err.message + "'", " in \n\nCode:\n", code, "\n");
        result = '';
    }

    return result;
}