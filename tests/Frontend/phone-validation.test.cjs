const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function phoneForm(view) {
    const source = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/users', `${view}.blade.php`), 'utf8');
    const start = source.indexOf('let isPhoneDuplicate = false;');
    const end = source.indexOf("phoneInput.addEventListener('input', checkPhone);", start);
    const script = source.slice(start, end).replace(/@json\(\\App\\Rules\\ThaiMobilePhone::MESSAGE\)/g, '"Invalid mobile format"');
    const requests = [];
    const phoneInput = { value: '', style: {}, setCustomValidity(message) { this.error = message; } };
    const phoneFeedback = { style: {} };
    const context = vm.createContext({
        phoneInput, phoneFeedback, console,
        fetch: () => new Promise(resolve => requests.push(data => resolve({ json: async () => data }))),
    });
    vm.runInContext(script, context);
    return {
        input: phoneInput, requests,
        check: value => { phoneInput.value = value; vm.runInContext('checkPhone()', context); },
    };
}

const flush = () => new Promise(resolve => setImmediate(resolve));

for (const view of ['create', 'edit']) {
    test(`${view}: rejects invalid format immediately and clears when empty`, () => {
        const form = phoneForm(view);
        form.check('000-000-0000');
        assert.equal(form.input.error, 'Invalid mobile format');
        assert.equal(form.requests.length, 0);
        form.check('');
        assert.equal(form.input.error, '');
    });

    test(`${view}: shows duplicate and server validation errors`, async () => {
        const form = phoneForm(view);
        form.check('081-234-5678');
        form.requests[0]({ exists: true, message: 'Phone already used' });
        await flush();
        assert.equal(form.input.error, 'Phone already used');
        form.check('091-234-5678');
        form.requests[1]({ errors: { Phone: ['Invalid phone from server'] } });
        await flush();
        assert.equal(form.input.error, 'Invalid phone from server');
    });

    test(`${view}: an old response cannot clear the current duplicate or invalid error`, async () => {
        const form = phoneForm(view);
        form.check('081-234-5678');
        form.check('091-234-5678');
        form.requests[1]({ exists: true, message: 'Current phone already used' });
        await flush();
        form.requests[0]({ exists: false });
        await flush();
        assert.equal(form.input.error, 'Current phone already used');
        form.check('081-234-5678');
        form.check('111-111-1111');
        form.requests[2]({ exists: false });
        await flush();
        assert.equal(form.input.error, 'Invalid mobile format');
    });
}
