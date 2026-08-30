(() => {
	const sidebar = document.getElementById('adminSidebar');
	const menuBtn = document.getElementById('adminMenuBtn');

	if (sidebar && menuBtn) {
		menuBtn.addEventListener('click', () => {
			sidebar.classList.toggle('open');
		});
	}

	const buscarInput = document.getElementById('productoBuscar');
	const categoriaSelect = document.getElementById('productoCategoria');
	const filasProductos = Array.from(document.querySelectorAll('.js-product-row'));

	if (buscarInput && categoriaSelect && filasProductos.length > 0) {
		const filtrarProductos = () => {
			const texto = buscarInput.value.trim().toLowerCase();
			const categoria = categoriaSelect.value;

			filasProductos.forEach((fila) => {
				const nombre = fila.dataset.nombre || '';
				const filaCategoria = fila.dataset.categoria || '';

				const coincideTexto = nombre.includes(texto);
				const coincideCategoria = !categoria || filaCategoria === categoria;

				fila.style.display = (coincideTexto && coincideCategoria) ? '' : 'none';
			});
		};

		buscarInput.addEventListener('input', filtrarProductos);
		categoriaSelect.addEventListener('change', filtrarProductos);
	}

	const buscarServicioInput = document.getElementById('servicioBuscar');
	const categoriaServicioSelect = document.getElementById('servicioCategoria');
	const filasServicios = Array.from(document.querySelectorAll('.js-service-row'));

	if (buscarServicioInput && categoriaServicioSelect && filasServicios.length > 0) {
		const filtrarServicios = () => {
			const texto = buscarServicioInput.value.trim().toLowerCase();
			const categoria = categoriaServicioSelect.value;

			filasServicios.forEach((fila) => {
				const nombre = fila.dataset.nombre || '';
				const filaCategoria = fila.dataset.categoria || '';

				const coincideTexto = nombre.includes(texto);
				const coincideCategoria = !categoria || filaCategoria === categoria;

				fila.style.display = (coincideTexto && coincideCategoria) ? '' : 'none';
			});
		};

		buscarServicioInput.addEventListener('input', filtrarServicios);
		categoriaServicioSelect.addEventListener('change', filtrarServicios);
	}

	const buscarCategoriaInput = document.getElementById('categoriaBuscar');
	const tipoCategoriaSelect = document.getElementById('categoriaTipo');
	const filasCategorias = Array.from(document.querySelectorAll('.js-category-row'));

	if (buscarCategoriaInput && tipoCategoriaSelect && filasCategorias.length > 0) {
		const filtrarCategorias = () => {
			const texto = buscarCategoriaInput.value.trim().toLowerCase();
			const tipo = tipoCategoriaSelect.value;

			filasCategorias.forEach((fila) => {
				const nombre = fila.dataset.nombre || '';
				const filaTipo = fila.dataset.tipo || '';

				const coincideTexto = nombre.includes(texto);
				const coincideTipo = !tipo || filaTipo === tipo;

				fila.style.display = (coincideTexto && coincideTipo) ? '' : 'none';
			});
		};

		buscarCategoriaInput.addEventListener('input', filtrarCategorias);
		tipoCategoriaSelect.addEventListener('change', filtrarCategorias);
	}

	const citaBuscarInput = document.getElementById('citaBuscar');
	const citaEstadoSelect = document.getElementById('citaEstado');
	const citaFechaInput = document.getElementById('citaFecha');
	const filasCitas = Array.from(document.querySelectorAll('.js-cita-row'));

	if (citaBuscarInput && citaEstadoSelect && citaFechaInput && filasCitas.length > 0) {
		const filtrarCitas = () => {
			const texto = citaBuscarInput.value.trim().toLowerCase();
			const estado = citaEstadoSelect.value;
			const fecha = citaFechaInput.value;

			filasCitas.forEach((fila) => {
				const busqueda = fila.dataset.busqueda || '';
				const filaEstado = fila.dataset.estado || '';
				const filaFecha = fila.dataset.fecha || '';

				const coincideTexto = busqueda.includes(texto);
				const coincideEstado = !estado || filaEstado === estado;
				const coincideFecha = !fecha || filaFecha === fecha;

				fila.style.display = (coincideTexto && coincideEstado && coincideFecha) ? '' : 'none';
			});
		};

		citaBuscarInput.addEventListener('input', filtrarCitas);
		citaEstadoSelect.addEventListener('change', filtrarCitas);
		citaFechaInput.addEventListener('change', filtrarCitas);
	}

	const pedidoBuscarInput = document.getElementById('pedidoBuscar');
	const pedidoEstadoSelect = document.getElementById('pedidoEstado');
	const pedidoFechaInput = document.getElementById('pedidoFecha');
	const filasPedidos = Array.from(document.querySelectorAll('.js-order-row'));

	if (pedidoBuscarInput && pedidoEstadoSelect && pedidoFechaInput && filasPedidos.length > 0) {
		const filtrarPedidos = () => {
			const texto = pedidoBuscarInput.value.trim().toLowerCase();
			const estado = pedidoEstadoSelect.value;
			const fecha = pedidoFechaInput.value;

			filasPedidos.forEach((fila) => {
				const busqueda = fila.dataset.busqueda || '';
				const filaEstado = fila.dataset.estado || '';
				const filaFecha = fila.dataset.fecha || '';

				const coincideTexto = busqueda.includes(texto);
				const coincideEstado = !estado || filaEstado === estado;
				const coincideFecha = !fecha || filaFecha === fecha;

				fila.style.display = (coincideTexto && coincideEstado && coincideFecha) ? '' : 'none';
			});
		};

		pedidoBuscarInput.addEventListener('input', filtrarPedidos);
		pedidoEstadoSelect.addEventListener('change', filtrarPedidos);
		pedidoFechaInput.addEventListener('change', filtrarPedidos);
	}

	const pagoBuscarInput = document.getElementById('pagoBuscar');
	const pagoEstadoSelect = document.getElementById('pagoEstado');
	const pagoMetodoSelect = document.getElementById('pagoMetodo');
	const pagoFechaInput = document.getElementById('pagoFecha');
	const filasPagos = Array.from(document.querySelectorAll('.js-payment-row'));

	if (pagoBuscarInput && pagoEstadoSelect && pagoMetodoSelect && pagoFechaInput && filasPagos.length > 0) {
		const filtrarPagos = () => {
			const texto = pagoBuscarInput.value.trim().toLowerCase();
			const estado = pagoEstadoSelect.value;
			const metodo = pagoMetodoSelect.value;
			const fecha = pagoFechaInput.value;

			filasPagos.forEach((fila) => {
				const busqueda = fila.dataset.busqueda || '';
				const filaEstado = fila.dataset.estado || '';
				const filaMetodo = fila.dataset.metodo || '';
				const filaFecha = fila.dataset.fecha || '';

				const coincideTexto = busqueda.includes(texto);
				const coincideEstado = !estado || filaEstado === estado;
				const coincideMetodo = !metodo || filaMetodo === metodo;
				const coincideFecha = !fecha || filaFecha === fecha;

				fila.style.display = (coincideTexto && coincideEstado && coincideMetodo && coincideFecha) ? '' : 'none';
			});
		};

		pagoBuscarInput.addEventListener('input', filtrarPagos);
		pagoEstadoSelect.addEventListener('change', filtrarPagos);
		pagoMetodoSelect.addEventListener('change', filtrarPagos);
		pagoFechaInput.addEventListener('change', filtrarPagos);
	}
})();
