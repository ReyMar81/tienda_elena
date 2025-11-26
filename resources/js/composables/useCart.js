import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const cartItems = ref([]);
const isLoading = ref(false);
const isAddingToCart = ref(false);

export function useCart() {
    const isAuthenticated = computed(() => {
        return !!document.querySelector('meta[name="user-authenticated"]');
    });

    const cartCount = computed(() => {
        return cartItems.value.reduce((sum, item) => sum + item.cantidad, 0);
    });

    const cartTotal = computed(() => {
        return cartItems.value.reduce((sum, item) => sum + item.subtotal, 0);
    });

    // Cargar carrito desde backend o localStorage
    const loadCart = async () => {
        isLoading.value = true;

        if (isAuthenticated.value) {
            try {
                const response = await fetch('/carrito');
                const data = await response.json();
                cartItems.value = data.items || [];
            } catch (error) {
                console.error('Error cargando carrito:', error);
            }
        } else {
            const localCart = localStorage.getItem('cart');
            cartItems.value = localCart ? JSON.parse(localCart) : [];
        }

        isLoading.value = false;
    };

    // Guardar en localStorage
    const saveLocalCart = () => {
        if (!isAuthenticated.value) {
            localStorage.setItem('cart', JSON.stringify(cartItems.value));
        }
    };

    // Agregar producto al carrito
    const addToCart = async (productoId, cantidad = 1) => {
        isAddingToCart.value = true;

        try {
            if (isAuthenticated.value) {
                const response = await fetch('/carrito/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ producto_id: productoId, cantidad })
                });

                const data = await response.json();

                if (response.ok) {
                    cartItems.value = data.items || [];
                    showNotification('Producto agregado al carrito');
                } else {
                    alert(data.error || 'Error al agregar producto');
                }
            } else {
                // Modo localStorage
                const existingItem = cartItems.value.find(item => item.producto_id === productoId);
                
                if (existingItem) {
                    existingItem.cantidad += cantidad;
                } else {
                    // Necesitarías obtener info del producto desde la página
                    cartItems.value.push({
                        id: Date.now(),
                        producto_id: productoId,
                        cantidad: cantidad
                    });
                }

                saveLocalCart();
                showNotification('Producto agregado al carrito');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al agregar producto');
        } finally {
            isAddingToCart.value = false;
        }
    };

    // Actualizar cantidad
    const updateQuantity = async (itemId, nuevaCantidad) => {
        const cantidad = parseInt(nuevaCantidad);
        
        if (cantidad < 1) return;

        if (isAuthenticated.value) {
            try {
                const response = await fetch(`/carrito/${itemId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ cantidad })
                });

                const data = await response.json();

                if (response.ok) {
                    cartItems.value = data.items || [];
                } else {
                    alert(data.error || 'Error al actualizar cantidad');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        } else {
            const item = cartItems.value.find(i => i.id === itemId);
            if (item) {
                item.cantidad = cantidad;
                saveLocalCart();
            }
        }
    };

    // Eliminar producto del carrito
    const removeFromCart = async (itemId) => {
        if (isAuthenticated.value) {
            try {
                const response = await fetch(`/carrito/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    cartItems.value = data.items || [];
                }
            } catch (error) {
                console.error('Error:', error);
            }
        } else {
            cartItems.value = cartItems.value.filter(item => item.id !== itemId);
            saveLocalCart();
        }
    };

    // Vaciar carrito
    const clearCart = async () => {
        if (!confirm('¿Estás seguro de vaciar el carrito?')) return;

        if (isAuthenticated.value) {
            try {
                await fetch('/carrito', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                cartItems.value = [];
            } catch (error) {
                console.error('Error:', error);
            }
        } else {
            cartItems.value = [];
            localStorage.removeItem('cart');
        }
    };

    // Sincronizar carrito al iniciar sesión
    const syncCart = async () => {
        const localCart = localStorage.getItem('cart');
        
        if (!localCart || !isAuthenticated.value) return;

        try {
            const items = JSON.parse(localCart);
            
            await fetch('/carrito/sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ items })
            });

            localStorage.removeItem('cart');
            await loadCart();
        } catch (error) {
            console.error('Error sincronizando carrito:', error);
        }
    };

    // Mostrar notificación
    const showNotification = (message) => {
        // Podrías usar un toast más sofisticado
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '11';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-body bg-success text-white rounded">
                    ${message}
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    };

    onMounted(() => {
        loadCart();
    });

    return {
        cartItems,
        cartCount,
        cartTotal,
        isLoading,
        isAddingToCart,
        loadCart,
        addToCart,
        updateQuantity,
        removeFromCart,
        clearCart,
        syncCart
    };
}
