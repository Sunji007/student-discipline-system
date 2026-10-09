const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function parentForm() {
    const source = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/users/create.blade.php'), 'utf8');
    const start = source.indexOf('function generateCredentials()');
    const script = source.slice(start, source.indexOf('function updateSections()', start));
    const field = value => ({ value });
    const context = vm.createContext({
        roleSelect: field('ผู้ปกครอง'), firstNameEN: field(''), citizenIdInput: field(''),
        usernameInput: field(''), passwordInput: field(''), teacherIDInput: field(''), studentIDInput: field(''),
        phoneInput: field(''), autoGenBadge: { style: {} }, nextIds: {},
    });
    vm.runInContext(script, context);
    return {
        context,
        generate: (name, id) => {
            context.firstNameEN.value = name;
            context.citizenIdInput.value = id;
            vm.runInContext('generateCredentials()', context);
        },
    };
}

for (const [name, expected] of [['Somchai', 'Somchai9012'], [' Som Chai ', 'SomChai9012'], ['Li', 'Li9012']]) {
    test(`parent form derives the displayed password for ${name}`, () => {
        const form = parentForm();
        form.generate(name, '1234567899012');
        assert.equal(form.context.usernameInput.value, '1234567899012');
        assert.equal(form.context.passwordInput.value, expected);
    });
}

test('incomplete data clears the previously generated parent password', () => {
    const form = parentForm();
    form.generate('Somchai', '1234567899012');
    form.generate('Somchai', '123');
    assert.equal(form.context.passwordInput.value, '');
    form.generate('', '1234567899012');
    assert.equal(form.context.passwordInput.value, '');
});
