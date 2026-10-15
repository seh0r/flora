const fs = require('node:fs');
const path = require('node:path');
const root = path.join(__dirname, '..');
const { minify } = require(path.join(root, 'tmp/quality/node_modules/terser'));
const CleanCSS = require(path.join(root, 'tmp/quality/node_modules/clean-css'));
(async () => {
    const js = await minify(fs.readFileSync(path.join(root, 'assets/app.js'), 'utf8'));
    const css = new CleanCSS({ level: 1 }).minify(fs.readFileSync(path.join(root, 'assets/style.css'), 'utf8'));
    if (css.errors.length) throw new Error(css.errors.join('\n'));
    fs.writeFileSync(path.join(root, 'assets/app.min.js'), js.code);
    fs.writeFileSync(path.join(root, 'assets/style.min.css'), css.styles);
    console.log('Minified CSS and JS generated');
})();
