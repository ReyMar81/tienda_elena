<script setup>
import FlashNotification from "@/Components/FlashNotification.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

import { computed, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import { Bar, Line, Doughnut } from "vue-chartjs";
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
} from "chart.js";

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement
);

const props = defineProps({
    rol: String,
    kpis: Object,
    graficos: Object,
    indicadores: Object,
    productos: Object,
    promociones: Array,
    carrito: Object,
});

const isCliente = computed(() => props.rol === "cliente");


// Modal para cantidad
const showCantidadModal = ref(false);
const productoSeleccionado = ref(null);
const cantidadAgregar = ref(1);

const abrirModalCantidad = (producto) => {
    productoSeleccionado.value = producto;
    cantidadAgregar.value = 1;
    showCantidadModal.value = true;
};

const agregarAlCarrito = () => {
    if (!productoSeleccionado.value) return;
    router.post(
        route("carritos.store"),
        {
            producto_id: productoSeleccionado.value.id,
            cantidad: cantidadAgregar.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showCantidadModal.value = false;
                productoSeleccionado.value = null;
            },
        }
    );
};

const calcularPrecioConDescuento = (producto) => {
    if (producto.promociones && producto.promociones.length > 0) {
        const descuento = producto.promociones[0].valor_descuento_decimal || 0;
        return parseFloat(producto.precio_venta) * (1 - descuento / 100);
    }
    return parseFloat(producto.precio_venta);
};

const tienePromocion = (producto) => {
    return producto.promociones && producto.promociones.length > 0 && producto.promociones[0].valor_descuento_decimal > 0;
};

// Obtener la URL de la imagen principal del producto
const getImageUrl = (producto) => {
    if (producto.imagenes && producto.imagenes.length > 0) {
        return `/storage/${producto.imagenes[0].url}`;
    }
    return "/images/no-image.png";
};

// Configuración de colores según tema
const chartColors = {
    primary:
        getComputedStyle(document.documentElement).getPropertyValue(
            "--accent"
        ) || "#4f46e5",
    success: "#10b981",
    danger: "#ef4444",
    warning: "#f59e0b",
    info: "#3b82f6",
};

// Datos para gráfico de ventas por día
const ventasPorDiaData = computed(() => {
    if (!props.graficos?.ventas_por_dia) return null;

    return {
        labels: props.graficos.ventas_por_dia.map((v) =>
            new Date(v.fecha).toLocaleDateString("es-ES", {
                day: "2-digit",
                month: "short",
            })
        ),
        datasets: [
            {
                label: "Ventas",
                data: props.graficos.ventas_por_dia.map((v) => v.cantidad),
                backgroundColor: chartColors.primary,
            },
        ],
    };
});

// Datos para gráfico de categorías
const categoriesData = computed(() => {
    if (!props.graficos?.ventas_por_categoria) return null;

    return {
        labels: props.graficos.ventas_por_categoria.map((c) => c.nombre),
        datasets: [
            {
                label: "Productos Vendidos",
                data: props.graficos.ventas_por_categoria.map((c) => c.total),
                backgroundColor: [
                    chartColors.primary,
                    chartColors.success,
                    chartColors.warning,
                    chartColors.info,
                    chartColors.danger,
                ],
            },
        ],
    };
});

const formatMoney = (amount) => parseFloat(amount || 0).toFixed(2);
</script>

<template>
    <AppLayout title="Dashboard">
        <FlashNotification />
        <div class="container py-4">
            <!-- Dashboard para Cliente -->
            <div v-if="isCliente">
                <h2 class="mb-4">Bienvenido a Tienda Elena</h2>

                <!-- Resumen del carrito y cuenta -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h6 class="card-title">Carrito Actual</h6>
                                <h3 class="mb-0">
                                    {{ carrito?.cantidad_items || 0 }}
                                </h3>
                                <small>productos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h6 class="card-title">Total Carrito</h6>
                                <h3 class="mb-0">
                                    Bs. {{ formatMoney(carrito?.total) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h6 class="card-title">Mis Compras</h6>
                                <h3 class="mb-0">
                                    {{ indicadores?.total_compras || 0 }}
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h6 class="card-title">Créditos Activos</h6>
                                <h3 class="mb-0">
                                    {{ indicadores?.creditos_activos || 0 }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Promociones Activas -->
                <div v-if="promociones && promociones.length > 0" class="mb-4">
                    <h4 class="mb-3">🎉 Promociones Activas</h4>
                    <div class="row g-3">
                        <div
                            v-for="promo in promociones"
                            :key="promo.id"
                            class="col-md-6"
                        >
                            <div class="card border-danger">
                                <div class="card-body">
                                    <div
                                        class="d-flex justify-content-between align-items-start"
                                    >
                                        <div>
                                            <h5 class="card-title text-danger">
                                                {{ promo.nombre }}
                                            </h5>
                                            <p class="mb-1">
                                                {{ promo.descripcion }}
                                            </p>
                                            <p class="mb-0">
                                                <strong
                                                    >Descuento:
                                                    {{
                                                        promo.descuento
                                                    }}%</strong
                                                >
                                            </p>
                                        </div>
                                        <span class="badge bg-danger"
                                            >-{{ promo.descuento }}%</span
                                        >
                                    </div>
                                    <small class="text-muted">
                                        Válido hasta:
                                        {{
                                            new Date(
                                                promo.fecha_fin
                                            ).toLocaleDateString("es-ES")
                                        }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos por Categoría -->
                <div v-if="productos">
                    <h4 class="mb-3">🛒 Catálogo de Productos</h4>

                    <div
                        v-for="(items, categoria) in productos"
                        :key="categoria"
                        class="mb-5"
                    >
                        <h5 class="border-bottom pb-2 mb-3">{{ categoria }}</h5>
                        <div class="row g-3">
                            <div
                                v-for="producto in items"
                                :key="producto.id"
                                class="col-md-3"
                            >
                                <div class="card h-100 position-relative">
                                    <!-- Badge de promoción -->
                                    <span
                                        v-if="tienePromocion(producto)"
                                        class="position-absolute top-0 end-0 badge bg-danger m-2"
                                    >
                                        -{{ producto.promociones[0].valor_descuento_decimal }}%
                                    </span>


                                    <!-- Imagen real del producto -->
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                        <img
                                            :src="getImageUrl(producto)"
                                            :alt="producto.nombre"
                                            style="max-height: 140px; max-width: 100%; object-fit: contain;"
                                            class="p-2 w-100"
                                            loading="lazy"
                                        />
                                    </div>

                                    <div class="card-body d-flex flex-column">
                                        <h6 class="card-title">
                                            {{ producto.nombre }}
                                        </h6>
                                        <p
                                            class="card-text text-muted small mb-2"
                                        >
                                            {{
                                                producto.descripcion?.substring(
                                                    0,
                                                    60
                                                )
                                            }}...
                                        </p>

                                        <!-- Precio y stock -->
                                        <div class="mt-auto">
                                            <div v-if="tienePromocion(producto)">
                                                <span class="text-decoration-line-through text-muted">
                                                    {{
                                                        new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(producto.precio_venta)
                                                    }}
                                                </span>
                                                <br />
                                                <strong class="text-danger fs-5">
                                                    {{
                                                        new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(calcularPrecioConDescuento(producto))
                                                    }}
                                                </strong>
                                            </div>
                                            <div v-else>
                                                <strong class="fs-5">
                                                    {{
                                                        new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(producto.precio_venta)
                                                    }}
                                                </strong>
                                            </div>
                                            <p class="mb-2 small">
                                                <span :class="producto.stock_actual < 10 ? 'text-danger' : 'text-success'">
                                                    Stock: {{ producto.stock_actual }}
                                                </span>
                                            </p>
                                            <div class="d-grid gap-2">
                                                <Link
                                                    :href="route('productos.show', producto.id)"
                                                    class="btn btn-outline-info btn-sm"
                                                >
                                                    <i class="bi bi-eye-fill me-1"></i>
                                                    Ver Detalles
                                                </Link>
                                                <button
                                                    @click="abrirModalCantidad(producto)"
                                                    class="btn btn-primary btn-sm"
                                                    :disabled="producto.stock_actual === 0"
                                                >
                                                    <i class="bi bi-cart-plus"></i>
                                                    {{ producto.stock_actual === 0 ? 'Sin Stock' : 'Agregar al Carrito' }}
                                                </button>
                                            </div>
                                                <!-- Modal para elegir cantidad al agregar al carrito -->
                                                <ConfirmationModal :show="showCantidadModal" @close="showCantidadModal = false" max-width="sm">
                                                    <template #title>Agregar al Carrito</template>
                                                    <template #content>
                                                        <div v-if="productoSeleccionado">
                                                            <p class="mb-2">¿Cuántas unidades de <strong>{{ productoSeleccionado.nombre }}</strong> deseas agregar?</p>
                                                            <input
                                                                type="number"
                                                                v-model="cantidadAgregar"
                                                                min="1"
                                                                :max="productoSeleccionado.stock_actual"
                                                                class="form-control w-50"
                                                            />
                                                            <small class="text-muted">Stock disponible: {{ productoSeleccionado.stock_actual }}</small>
                                                        </div>
                                                    </template>
                                                    <template #footer>
                                                        <button type="button" class="btn btn-secondary" @click="showCantidadModal = false">Cancelar</button>
                                                        <button type="button" class="btn btn-primary" @click="agregarAlCarrito" :disabled="cantidadAgregar < 1 || cantidadAgregar > (productoSeleccionado?.stock_actual || 1)">
                                                            <i class="bi bi-cart-plus me-2"></i>Agregar
                                                        </button>
                                                    </template>
                                                </ConfirmationModal>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Si no hay productos -->
                <div v-else class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No hay productos
                    disponibles en este momento.
                </div>
            </div>

            <!-- Dashboard para Propietario/Vendedor -->
            <div v-else>
                <h2 class="mb-4">Panel de Control</h2>

                <!-- KPIs de Ventas -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="text-muted">Ventas Hoy</h6>
                                <h2 class="mb-0">
                                    {{ kpis?.ventas_dia || 0 }}
                                </h2>
                                <small class="text-success"
                                    >Bs.
                                    {{ formatMoney(kpis?.ingresos_dia) }}</small
                                >
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="text-muted">Ventas Semana</h6>
                                <h2 class="mb-0">
                                    {{ kpis?.ventas_semana || 0 }}
                                </h2>
                                <small class="text-success"
                                    >Bs.
                                    {{
                                        formatMoney(kpis?.ingresos_semana)
                                    }}</small
                                >
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="text-muted">Ventas Mes</h6>
                                <h2 class="mb-0">
                                    {{ kpis?.ventas_mes || 0 }}
                                </h2>
                                <small class="text-success"
                                    >Bs.
                                    {{ formatMoney(kpis?.ingresos_mes) }}</small
                                >
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="text-muted">Total Visitas</h6>
                                <h2 class="mb-0">
                                    {{ kpis?.total_visitas || 0 }}
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPIs de Créditos -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h6>Créditos Pendientes</h6>
                                <h3>{{ kpis?.creditos_pendientes || 0 }}</h3>
                                <small
                                    >Monto: Bs.
                                    {{
                                        formatMoney(
                                            kpis?.monto_creditos_activos
                                        )
                                    }}</small
                                >
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h6>Créditos Pagados</h6>
                                <h3>{{ kpis?.creditos_pagados || 0 }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-danger">
                            <div class="card-body">
                                <h6>Cuotas Vencidas</h6>
                                <h3>{{ kpis?.cuotas_vencidas || 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="row g-4">
                    <div class="col-lg-6" v-if="ventasPorDiaData">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    Ventas de los Últimos 7 Días
                                </h5>
                            </div>
                            <div class="card-body">
                                <Bar
                                    :data="ventasPorDiaData"
                                    :options="{
                                        responsive: true,
                                        maintainAspectRatio: true,
                                    }"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6" v-if="categoriesData">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Ventas por Categoría</h5>
                            </div>
                            <div class="card-body">
                                <Doughnut
                                    :data="categoriesData"
                                    :options="{
                                        responsive: true,
                                        maintainAspectRatio: true,
                                    }"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos más vendidos -->
                <div class="row mt-4" v-if="graficos?.productos_mas_vendidos">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    Top 10 Productos Más Vendidos
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Producto</th>
                                                <th>Código</th>
                                                <th>Cantidad</th>
                                                <th>Ingresos</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(
                                                    producto, index
                                                ) in graficos.productos_mas_vendidos"
                                                :key="index"
                                            >
                                                <td>{{ index + 1 }}</td>
                                                <td>{{ producto.nombre }}</td>
                                                <td>{{ producto.codigo }}</td>
                                                <td>
                                                    {{ producto.total_vendido }}
                                                </td>
                                                <td>
                                                    Bs.
                                                    {{
                                                        formatMoney(
                                                            producto.ingresos
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
