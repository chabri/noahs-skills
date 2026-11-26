// Real default values of every widget, computed the way the editor does when a widget is dropped on the canvas:
// the widget form HTML is serialized with the module's own serializejson.js ($(html).find(':input').serializeJSON()).
// Usage: node defaults.js <widget-forms.json> <noahs module dir> <out.json>
const fs = require('fs');
const path = require('path');
const { JSDOM } = require('jsdom');

const [, , formsPath, moduleDir, outPath] = process.argv;
const w = new JSDOM('<!doctype html><html><body></body></html>', { runScripts: 'outside-only' }).window;
w.eval(fs.readFileSync(require.resolve('jquery/dist/jquery.min.js'), 'utf8'));
w.eval(fs.readFileSync(path.join(moduleDir, 'assets/js/builder/libs/serializejson.js'), 'utf8'));

const forms = JSON.parse(fs.readFileSync(formsPath, 'utf8'));
const out = {};
for (const [id, html] of Object.entries(forms)) {
  out[id] = w.jQuery(html).find(':input').serializeJSON().element || {};
}
fs.writeFileSync(outPath, JSON.stringify(out, null, 1));
console.log(`${Object.keys(out).length} widgets -> ${outPath}`);
