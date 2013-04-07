var page = require('webpage').create(),
    system = require('system'),
    address, output, size;
var defaults = {
    width:  1024,
    height: 768,
    delay: 3
};

if (system.args.length < 3) {
    console.log('Usage: screener.js url=URL filename=FILENAME width=xxx height=yyy');
    phantom.exit(1);
}

var options = getOptions(defaults);

address = options.url;
output = options.filename;
page.viewportSize = { width:options.width, height:options.height };
page.clipRect = { top:0, left:0, width:options.width, height:options.height };
page.open(address, function (status) {
    if (status !== 'success') {
        console.log('Unable to load the address!');
        phantom.exit(1);
    } else {
        window.setTimeout(function () {
            page.render(output);
            phantom.exit();
        }, 1000* parseInt(options.delay, 10));
    }
});


function getOptions (defaults) {
    defaults = defaults || {};
    var args = system.args.slice(1);
    var options = Object.create(defaults), option;
    args.forEach(function(arg) {
        option = arg.split('=');
        if (option.length < 2) { return; }
        options[option[0]] = option[1];
    });
    return options;
}