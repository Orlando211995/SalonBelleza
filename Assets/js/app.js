(() => {
	const CART_KEY = 'salon_belleza_carrito';

	const formatearMoneda = (monto) => new Intl.NumberFormat('es-CR', {
		style: 'currency',
		currency: 'CRC',
		minimumFractionDigits: 2,
	}).format(monto);

	const precioTextoANumero = (precioTexto) => {
		if (!precioTexto) return 0;
		const limpio = precioTexto
			.replace(/[₡\s]/g, '')
			.replace(/\./g, '')
			.replace(',', '.');
		const valor = Number.parseFloat(limpio);
		return Number.isFinite(valor) ? valor : 0;
	};

	const obtenerCarrito = () => {
		try {
			const guardado = localStorage.getItem(CART_KEY);
			const carrito = guardado ? JSON.parse(guardado) : [];
			return Array.isArray(carrito) ? carrito : [];
		} catch (_) {
			return [];
		}
	};

	const guardarCarrito = (carrito) => {
		localStorage.setItem(CART_KEY, JSON.stringify(carrito));
	};

	const agregarAlCarrito = (producto, cantidad = 1) => {
		const qty = Math.max(1, Number.parseInt(cantidad, 10) || 1);
		const carrito = obtenerCarrito();
		const existente = carrito.find((item) => item.id === producto.id);

		if (existente) {
			existente.cantidad += qty;
		} else {
			carrito.push({ ...producto, cantidad: qty });
		}

		guardarCarrito(carrito);
		actualizarBadgeCarrito();
	};

	const eliminarProducto = (productoId) => {
		const carrito = obtenerCarrito().filter((item) => item.id !== productoId);
		guardarCarrito(carrito);
	};

	const cambiarCantidad = (productoId, nuevaCantidad) => {
		const qty = Math.max(1, Number.parseInt(nuevaCantidad, 10) || 1);
		const carrito = obtenerCarrito();
		const producto = carrito.find((item) => item.id === productoId);
		if (!producto) return;
		producto.cantidad = qty;
		guardarCarrito(carrito);
	};

	const mostrarConfirmacion = (nombreProducto) => {
		alert(`${nombreProducto} se agrego al carrito.`);
	};

	const obtenerProductoDesdeCard = (card) => {
		const enlace = card.querySelector('.card-producto-link');
		const imagen = card.querySelector('img');
		const nombre = card.querySelector('h3')?.textContent?.trim() || 'Producto';
		const precioTexto = card.querySelector('p')?.textContent?.trim() || '₡0,00';
		const url = enlace?.getAttribute('href') || 'producto.php';
		const params = new URL(url, window.location.origin).searchParams;
		const id = params.get('producto') || nombre.toLowerCase().replace(/\s+/g, '-');

		return {
			id,
			nombre,
			precio: precioTextoANumero(precioTexto),
			precioTexto,
			imagen: imagen?.getAttribute('src') || '',
			url,
		};
	};

	const actualizarBadgeCarrito = () => {
		const enlace = document.querySelector('a[href="carrito.php"]');
		if (!enlace) return;

		const totalUnidades = obtenerCarrito().reduce((acc, item) => acc + item.cantidad, 0);
		let badge = enlace.querySelector('.cart-badge');

		if (totalUnidades <= 0) {
			if (badge) badge.remove();
			return;
		}

		if (!badge) {
			badge = document.createElement('span');
			badge.className = 'cart-badge';
			enlace.appendChild(badge);
		}

		badge.textContent = String(totalUnidades);
	};

	const inicializarBotonesCatalogo = () => {
		document.querySelectorAll('.btn-agregar-carrito-producto').forEach((btn) => {
			btn.addEventListener('click', (event) => {
				event.preventDefault();
				const card = btn.closest('.card-producto');
				if (!card) return;

				const producto = obtenerProductoDesdeCard(card);
				agregarAlCarrito(producto, 1);
				mostrarConfirmacion(producto.nombre);
			});
		});
	};

	const inicializarBotonDetalle = () => {
		const btn = document.querySelector('.btn-agregar[data-producto-id]');
		if (!btn) return;

		btn.addEventListener('click', (event) => {
			event.preventDefault();

			const cantidadInput = document.querySelector('.cantidad-input');
			const cantidad = cantidadInput ? cantidadInput.value : 1;

			const producto = {
				id: btn.dataset.productoId,
				nombre: btn.dataset.productoNombre || 'Producto',
				precioTexto: btn.dataset.productoPrecio || '₡0,00',
				precio: precioTextoANumero(btn.dataset.productoPrecio || '₡0,00'),
				imagen: btn.dataset.productoImagen || '',
				url: btn.dataset.productoUrl || 'producto.php',
			};

			agregarAlCarrito(producto, cantidad);
			mostrarConfirmacion(producto.nombre);
		});
	};

	const renderizarCarrito = () => {
		const contenedor = document.getElementById('carrito-contenido');
		const totalTexto = document.getElementById('carrito-total');
		if (!contenedor || !totalTexto) return;

		const carrito = obtenerCarrito();

		if (carrito.length === 0) {
			contenedor.innerHTML = '<p class="carrito-vacio">Tu carrito esta vacio.</p>';
			totalTexto.textContent = formatearMoneda(0);
			return;
		}

		const filas = carrito.map((item) => {
			const subtotal = item.precio * item.cantidad;
			return `
				<div class="carrito-item" data-id="${item.id}">
					<img src="${item.imagen}" alt="${item.nombre}" class="carrito-item-img">
					<div class="carrito-item-info">
						<a href="${item.url}" class="carrito-item-nombre">${item.nombre}</a>
						<p>${formatearMoneda(item.precio)}</p>
					</div>
					<input type="number" min="1" value="${item.cantidad}" class="carrito-cantidad" data-id="${item.id}">
					<p class="carrito-item-subtotal">${formatearMoneda(subtotal)}</p>
					<button class="carrito-eliminar" data-id="${item.id}">Eliminar</button>
				</div>
			`;
		}).join('');

		contenedor.innerHTML = filas;

		const total = carrito.reduce((acc, item) => acc + item.precio * item.cantidad, 0);
		totalTexto.textContent = formatearMoneda(total);

		contenedor.querySelectorAll('.carrito-eliminar').forEach((btn) => {
			btn.addEventListener('click', () => {
				eliminarProducto(btn.dataset.id);
				renderizarCarrito();
				actualizarBadgeCarrito();
			});
		});

		contenedor.querySelectorAll('.carrito-cantidad').forEach((input) => {
			input.addEventListener('change', () => {
				cambiarCantidad(input.dataset.id, input.value);
				renderizarCarrito();
				actualizarBadgeCarrito();
			});
		});
	};

	const renderizarCheckout = () => {
		const itemsContenedor = document.getElementById('checkout-items');
		const subtotalNodo = document.getElementById('checkout-subtotal');
		const envioNodo = document.getElementById('checkout-envio');
		const totalNodo = document.getElementById('checkout-total');
		const formulario = document.getElementById('checkout-form');
		const carritoJsonInput = document.getElementById('checkout-carrito-json');
		const tipoEntregaSelect = document.getElementById('tipo_entrega');
		const direccionInput = document.getElementById('direccion');

		if (!itemsContenedor || !subtotalNodo || !envioNodo || !totalNodo) return;

		const carrito = obtenerCarrito();
		const tipoEntrega = tipoEntregaSelect ? tipoEntregaSelect.value : 'envio';
		const envio = (carrito.length > 0 && tipoEntrega === 'envio') ? 3500 : 0;

		if (direccionInput && tipoEntregaSelect) {
			if (tipoEntrega === 'envio') {
				direccionInput.required = true;
				direccionInput.placeholder = '';
			} else {
				direccionInput.required = false;
				direccionInput.placeholder = 'No aplica para retiro en salon';
			}
		}

		if (carrito.length === 0) {
			itemsContenedor.innerHTML = '<p class="checkout-vacio">No hay productos en tu carrito.</p>';
			subtotalNodo.textContent = formatearMoneda(0);
			envioNodo.textContent = formatearMoneda(0);
			totalNodo.textContent = formatearMoneda(0);
			if (carritoJsonInput) {
				carritoJsonInput.value = '[]';
			}
			if (formulario) {
				const btn = formulario.querySelector('button[type="submit"]');
				if (btn) btn.disabled = true;
			}
			return;
		}

		itemsContenedor.innerHTML = carrito.map((item) => `
			<div class="checkout-item">
				<img src="${item.imagen}" alt="${item.nombre}">
				<div>
					<p>${item.nombre}</p>
					<small>Cantidad: ${item.cantidad}</small>
				</div>
				<strong>${formatearMoneda(item.precio * item.cantidad)}</strong>
			</div>
		`).join('');

		const subtotal = carrito.reduce((acc, item) => acc + (item.precio * item.cantidad), 0);
		const total = subtotal + envio;

		if (carritoJsonInput) {
			carritoJsonInput.value = JSON.stringify(carrito);
		}

		subtotalNodo.textContent = formatearMoneda(subtotal);
		envioNodo.textContent = formatearMoneda(envio);
		totalNodo.textContent = formatearMoneda(total);

		if (formulario) {
			const btn = formulario.querySelector('button[type="submit"]');
			if (btn) btn.disabled = false;

			if (!formulario.dataset.checkoutBound) {
				formulario.dataset.checkoutBound = '1';
				formulario.addEventListener('submit', () => {
					if (carritoJsonInput) {
						carritoJsonInput.value = JSON.stringify(obtenerCarrito());
					}
				});
			}

			if (tipoEntregaSelect && !tipoEntregaSelect.dataset.boundEntrega) {
				tipoEntregaSelect.dataset.boundEntrega = '1';
				tipoEntregaSelect.addEventListener('change', renderizarCheckout);
			}
		}
	};

	const inicializarRecorteVideoNosotros = () => {
		const video = document.querySelector('.nosotros-video');
		if (!video) return;

		let puntoCorte = null;

		video.addEventListener('loadedmetadata', () => {
			if (!Number.isFinite(video.duration) || video.duration <= 1) {
				puntoCorte = null;
				return;
			}
			puntoCorte = Math.max(0, video.duration - 1);
		});

		video.addEventListener('timeupdate', () => {
			if (puntoCorte === null) return;
			if (video.currentTime < puntoCorte) return;

			if (video.loop) {
				video.currentTime = 0;
				if (video.paused) {
					video.play().catch(() => {
						/* Ignorado: el navegador puede bloquear autoplay en algunos contextos. */
					});
				}
				return;
			}

			video.pause();
		});
	};

	const inicializarCitasPublicas = () => {
		const form = document.getElementById('citaPublicaForm');
		if (!form) return;

		const servicioSelect = document.getElementById('servicio_id');
		const fechaInput = document.getElementById('fecha');
		const horaSelect = document.getElementById('hora');
		const duracionTexto = document.getElementById('citaDuracionTexto');
		const disponibilidadUrl = form.dataset.disponibilidadUrl || '/Admin/citas/disponibilidad.php';

		if (!servicioSelect || !fechaInput || !horaSelect) return;

		const pintarDuracion = () => {
			const selected = servicioSelect.options[servicioSelect.selectedIndex];
			const duracion = selected ? (selected.dataset.duracion || '') : '';
			if (duracionTexto) {
				duracionTexto.textContent = duracion ? `${duracion} min` : '-';
			}
		};

		const poblarHoras = (horas, horaActual = '') => {
			horaSelect.innerHTML = '<option value="">Seleccionar hora</option>';

			horas.forEach((hora) => {
				const option = document.createElement('option');
				option.value = hora;
				option.textContent = hora;
				if (horaActual && horaActual === hora) {
					option.selected = true;
				}
				horaSelect.appendChild(option);
			});
		};

		const actualizarDisponibilidad = async () => {
			const servicioId = servicioSelect.value;
			const fecha = fechaInput.value;

			if (!servicioId || !fecha) {
				poblarHoras([]);
				return;
			}

			try {
				const url = `${disponibilidadUrl}?format=json&servicio_id=${encodeURIComponent(servicioId)}&fecha=${encodeURIComponent(fecha)}`;
				const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
				if (!response.ok) {
					return;
				}

				const payload = await response.json();
				const horas = Array.isArray(payload.horas_disponibles) ? payload.horas_disponibles : [];
				poblarHoras(horas, horaSelect.value);
			} catch (_) {
				/* Si falla el fetch, mantenemos las horas ya renderizadas por el servidor. */
			}
		};

		servicioSelect.addEventListener('change', () => {
			pintarDuracion();
			actualizarDisponibilidad();
		});

		fechaInput.addEventListener('change', actualizarDisponibilidad);

		pintarDuracion();
	};

	document.addEventListener('DOMContentLoaded', () => {
		if (window.location.pathname.toLowerCase().includes('/salonbelleza/checkout.php')) {
			const searchParams = new URLSearchParams(window.location.search);
			if (searchParams.get('pedido_ok') === '1') {
				guardarCarrito([]);
			}
		}

		inicializarBotonesCatalogo();
		inicializarBotonDetalle();
		inicializarRecorteVideoNosotros();
		inicializarCitasPublicas();
		renderizarCarrito();
		renderizarCheckout();
		actualizarBadgeCarrito();
	});
})();
