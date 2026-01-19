import { ref, computed } from 'vue'

// Global cart state
const cartItems = ref([])
const cartCount = ref(0)
const cartSubtotal = ref(0)
const isLoading = ref(false)
const isLoaded = ref(false)

export function useCart() {
  // Fetch cart from API
  const loadCart = async () => {
    isLoading.value = true

    try {
      const response = await fetch('/api/cart')
      if (response.ok) {
        const data = await response.json()
        cartItems.value = data.items || []
        cartCount.value = data.totals?.itemCount || 0
        cartSubtotal.value = data.totals?.subtotal || 0
        isLoaded.value = true
      }
    } catch (error) {
      console.error('Error loading cart:', error)
    } finally {
      isLoading.value = false
    }
  }

  // Add item to cart
  const addToCart = async (articleId, quantity = 1) => {
    try {
      const response = await fetch('/api/cart/add', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          article_id: articleId,
          quantity: quantity
        })
      })

      if (response.ok) {
        const data = await response.json()
        if (data.success && data.cart) {
          cartItems.value = data.cart.items || []
          cartCount.value = data.cart.totals?.itemCount || 0
          cartSubtotal.value = data.cart.totals?.subtotal || 0
          return { success: true, message: data.message }
        }
      } else {
        const error = await response.json()
        return { success: false, message: error.error || 'Failed to add item' }
      }
    } catch (error) {
      console.error('Error adding to cart:', error)
      return { success: false, message: 'Failed to add item to cart' }
    }
  }

  // Update item quantity
  const updateQuantity = async (itemId, quantity) => {
    try {
      const response = await fetch(`/api/cart/update/${itemId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ quantity })
      })

      if (response.ok) {
        const data = await response.json()
        if (data.success && data.cart) {
          cartItems.value = data.cart.items || []
          cartCount.value = data.cart.totals?.itemCount || 0
          cartSubtotal.value = data.cart.totals?.subtotal || 0
          return { success: true, message: data.message }
        }
      }
    } catch (error) {
      console.error('Error updating cart:', error)
      return { success: false, message: 'Failed to update quantity' }
    }
  }

  // Remove item from cart
  const removeItem = async (itemId) => {
    try {
      const response = await fetch(`/api/cart/remove/${itemId}`, {
        method: 'DELETE'
      })

      if (response.ok) {
        const data = await response.json()
        if (data.success && data.cart) {
          cartItems.value = data.cart.items || []
          cartCount.value = data.cart.totals?.itemCount || 0
          cartSubtotal.value = data.cart.totals?.subtotal || 0
          return { success: true, message: data.message }
        }
      }
    } catch (error) {
      console.error('Error removing item:', error)
      return { success: false, message: 'Failed to remove item' }
    }
  }

  // Refresh cart (useful after page navigation)
  const refreshCart = async () => {
    await loadCart()
  }

  return {
    // State
    cartItems: computed(() => cartItems.value),
    cartCount: computed(() => cartCount.value),
    cartSubtotal: computed(() => cartSubtotal.value),
    isLoading: computed(() => isLoading.value),
    isLoaded: computed(() => isLoaded.value),

    // Actions
    loadCart,
    addToCart,
    updateQuantity,
    removeItem,
    refreshCart
  }
}
