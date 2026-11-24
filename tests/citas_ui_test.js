const assert = require('assert');
const fs = require('fs');
const vm = require('vm');

const elements = {};
const listeners = {};
function element(id, value = '') {
	const result = {
		id,
		value,
		options: [],
		selectedIndex: 0,
		children: [],
		addEventListener(event, callback) {
			listeners[`${id}:${event}`] = callback;
		},
		appendChild(child) {
			this.children.push(child);
			if (child.selected) this.value = child.value;
		},
	};
	Object.defineProperty(result, 'innerHTML', {
		set() {
			this.children = [];
			this.value = '';
		},
	});
	elements[id] = result;
	return result;
}

const form = element('citaPublicaForm');
form.dataset = { disponibilidadUrl: '/disponibilidad' };
const servicio = element('servicio_id', '1');
servicio.options = [{ dataset: { duracion: '60' } }];
const fecha = element('fecha', '2026-11-23');
const hora = element('hora');
const pending = [];
const document = {
	getElementById: (id) => elements[id] || null,
	querySelector: () => null,
	querySelectorAll: () => [],
	createElement: () => ({}),
	addEventListener(event, callback) {
		if (event === 'DOMContentLoaded') listeners.ready = callback;
	},
};
const sandbox = {
	document,
	window: { location: { pathname: '/cita.php', search: '' } },
	localStorage: { getItem: () => null, setItem: () => {} },
	URLSearchParams,
	console: { error() {} },
	fetch: (url) => new Promise((resolve) => pending.push({ url, resolve })),
};
vm.runInNewContext(fs.readFileSync('Assets/js/app.js', 'utf8'), sandbox);
listeners.ready();

async function main() {
	const primera = listeners['fecha:change']();
	assert.strictEqual(hora.children[0].textContent, 'Consultando disponibilidad...');
	fecha.value = '2026-11-24';
	const segunda = listeners['fecha:change']();
	pending[1].resolve({ ok: true, json: async () => ({ horas_disponibles: ['10:00', '19:30'] }) });
	await segunda;
	assert.deepStrictEqual(hora.children.map((item) => item.textContent), ['Seleccionar hora', '10:00 AM', '07:30 PM']);
	pending[0].resolve({ ok: true, json: async () => ({ horas_disponibles: ['11:00'] }) });
	await primera;
	assert.deepStrictEqual(hora.children.map((item) => item.value), ['', '10:00', '19:30']);

	const error = listeners['fecha:change']();
	pending[2].resolve({ ok: false });
	await error;
	assert.strictEqual(hora.children[0].textContent, 'No se pudo consultar la disponibilidad');
	assert.strictEqual(hora.children.length, 1);
	console.log('Interfaz de disponibilidad verificada.');
}

main().catch((error) => {
	console.error(error);
	process.exitCode = 1;
});
