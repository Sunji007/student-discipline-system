const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function reportForm() {
    const source = fs.readFileSync(path.join(__dirname, '../../resources/views/student/informant-reports/create.blade.php'), 'utf8');
    const start = source.indexOf('window.confirmAndSubmitForm = function()');
    const script = source.slice(start, source.indexOf('</script>', start))
        .replace('{{ $dailyLimit }}', '3').replace('{{ $reportsToday ?? 0 }}', '0');
    let submissions = 0;
    let confirmations = 0;
    const alerts = [];
    const evidence = { files: [], classList: { contains: () => false }, focus() {} };
    const elements = {
        informantReportForm: { submit() { submissions++; } },
        TitleSelect: { value: 'Test' }, Title: { value: 'Test' },
        Category: { value: 'Test' }, Description: { value: 'Incident details' },
        evidence, AcknowledgeTruth: { checked: true },
    };
    const context = vm.createContext({
        window: {}, document: { getElementById: id => elements[id] || null },
        alert: message => alerts.push(message), confirm: () => { confirmations++; return true; },
    });
    vm.runInContext(script, context);
    return {
        evidence, alerts, context,
        send: () => context.window.confirmAndSubmitForm(),
        submitted: () => submissions, confirmed: () => confirmations,
    };
}

test('missing image blocks confirmation and submission', () => {
    const form = reportForm();
    assert.equal(form.send(), false);
    assert.equal(form.submitted(), 0);
    assert.equal(form.confirmed(), 0);
    assert.match(form.alerts[0], /อย่างน้อย 1 ภาพ/);
});

test('an attached image allows confirmed submission; removing it blocks the next send', () => {
    const form = reportForm();
    form.evidence.files = [{}];
    form.send();
    assert.equal(form.submitted(), 1);
    form.evidence.files = [];
    assert.equal(form.send(), false);
    assert.equal(form.submitted(), 1);
});

test('missing image displays the image warning when SweetAlert is available', () => {
    const form = reportForm();
    const messages = [];
    form.context.Swal = { fire: options => { messages.push(options); return Promise.resolve(); } };
    assert.equal(form.send(), false);
    assert.match(messages[0].text, /อย่างน้อย 1 ภาพ/);
    assert.equal(form.submitted(), 0);
});
