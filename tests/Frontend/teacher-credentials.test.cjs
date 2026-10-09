const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function teacherForm() {
    const source = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/users/create.blade.php'), 'utf8');
    const teacherStart = source.indexOf('    async function updateTeacherIDForUser()');
    const declarationsStart = source.lastIndexOf('    let teacherIdRequest', teacherStart);
    const scriptStart = declarationsStart >= 0 ? declarationsStart : teacherStart;
    const teacherScript = source.slice(scriptStart, source.indexOf('    // Attach listeners', teacherStart));
    const credentialStart = source.indexOf('    function generateCredentials()');
    const credentialScript = source.slice(credentialStart, source.indexOf('    function updateSections()', credentialStart));
    const field = value => ({ value });
    const department = field('');
    const room = field('');
    const requests = [];
    const context = vm.createContext({
        roleSelect: field('ครู'), firstNameEN: field('Somchai'), citizenIdInput: field('1234567899012'),
        usernameInput: field(''), passwordInput: field(''), teacherIDInput: field(''), studentIDInput: field(''),
        phoneInput: field(''), autoGenBadge: { style: {} },
        nextIds: { 'ครู': { username: '20001', role_id: '20001' } }, console,
        document: { getElementById: () => department, querySelector: () => room },
        fetch: url => new Promise(resolve => requests.push({ url, resolve })),
    });
    vm.runInContext(teacherScript + '\n' + credentialScript, context);
    return { context, department, room, requests, generate: () => vm.runInContext('generateCredentials()', context),
        update: () => vm.runInContext('updateTeacherIDForUser()', context) };
}

test('selecting teacher immediately displays the generated ID and username without department or room', () => {
    const form = teacherForm();
    form.generate();
    assert.equal(form.context.teacherIDInput.value, '20001');
    assert.equal(form.context.usernameInput.value, 'somchai9012');
    assert.equal(form.context.passwordInput.value, '1234567899012');
    assert.equal(form.requests.length, 0);
});

test('entering a room before choosing a department keeps both generated identifiers visible', async () => {
    const form = teacherForm();
    form.generate();
    form.room.value = '4/8';
    await form.update();
    assert.equal(form.context.teacherIDInput.value, '20001');
    assert.equal(form.context.usernameInput.value, 'somchai9012');
});

test('department and room update only the teacher ID', async () => {
    const form = teacherForm();
    form.generate();
    form.department.value = '5';
    form.room.value = '4/8';
    const pending = form.update();
    form.requests[0].resolve({ ok: true, json: async () => ({ TeacherID: '0540801' }) });
    await pending;
    assert.equal(form.context.teacherIDInput.value, '0540801');
    assert.equal(form.context.usernameInput.value, 'somchai9012');
});

test('clearing the department restores the default ID and ignores an older response', async () => {
    const form = teacherForm();
    form.generate();
    form.department.value = '5';
    form.room.value = '4/8';
    const old = form.update();
    form.department.value = '';
    await form.update();
    form.requests[0].resolve({ ok: true, json: async () => ({ TeacherID: '0540801' }) });
    await old;
    assert.equal(form.context.teacherIDInput.value, '20001');
    assert.equal(form.context.usernameInput.value, 'somchai9012');
});

test('out of order replies cannot replace the ID for the current classroom', async () => {
    const form = teacherForm();
    form.generate();
    form.department.value = '5';
    form.room.value = '4/8';
    const old = form.update();
    form.room.value = '4/9';
    const current = form.update();
    form.requests[1].resolve({ ok: true, json: async () => ({ TeacherID: '0540901' }) });
    await current;
    form.requests[0].resolve({ ok: true, json: async () => ({ TeacherID: '0540801' }) });
    await old;
    assert.equal(form.context.teacherIDInput.value, '0540901');
});

test('failed generation request retains the automatic fallback and the login username', async () => {
    const form = teacherForm();
    form.generate();
    form.department.value = '5';
    form.room.value = '4/8';
    const pending = form.update();
    form.requests[0].resolve({ ok: false, json: async () => ({}) });
    await pending;
    assert.equal(form.context.teacherIDInput.value, '20001');
    assert.equal(form.context.usernameInput.value, 'somchai9012');
});
