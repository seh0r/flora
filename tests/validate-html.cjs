const fs = require('node:fs');
const path = require('node:path');
const { HtmlValidate } = require('../tmp/quality/node_modules/html-validate');
const validator = new HtmlValidate({ extends: ['html-validate:recommended'], rules: { 'long-title':'off', 'no-trailing-whitespace':'off' } });
(async () => {
    const folder = path.join(__dirname, '../tmp/html');
    const reports = [];
    for (const name of fs.readdirSync(folder).filter(n => n.endsWith('.html'))) {
        const result = await validator.validateString(fs.readFileSync(path.join(folder, name), 'utf8'), name);
        reports.push({page:name, valid:result.valid, messages:result.results.flatMap(r=>r.messages)});
    }
    fs.writeFileSync(path.join(__dirname, '../docs/html-validation.json'), JSON.stringify(reports,null,2));
    console.log(JSON.stringify(reports.map(r=>({page:r.page,valid:r.valid,errors:r.messages.map(m=>({rule:m.ruleId,message:m.message,line:m.line}))})),null,2));
    if (reports.some(r=>!r.valid)) process.exitCode=1;
})();
