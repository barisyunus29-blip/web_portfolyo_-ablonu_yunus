/**
 * Araba Parçaları E-Ticaret Web Sitesi - Global JavaScript
 * GitHub Pages için düzeltilmiş versiyon - localStorage ile sepet yönetimi
 */

const API_URL = '/Web-Projesi2/';

let currentUser = null;

document.addEventListener('DOMContentLoaded', function() {
    loadUserInfo();
    initializeEventListeners();
});

function loadUserInfo() {
    const userStr = localStorage.getItem('user');
    if (userStr) {
        currentUser = JSON.parse(userStr);
        updateUserUI();
    }
}

function updateUserUI() {
    const loginBtn = document.getElementById('login-btn');
    const userMenu = document.getElementById('user-menu');
    
    if (currentUser) {
        if (loginBtn) loginBtn.style.display = 'none';
        if (userMenu) {
            userMenu.style.display = 'block';
            const userName = document.getElementById('user-name');
            if (userName) userName.textContent = currentUser.name;
        }
    } else {
        if (loginBtn) loginBtn.style.display = 'inline-block';
        if (userMenu) userMenu.style.display = 'none';
    }
}

function initializeEventListeners() {
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', logout);
    }
    
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', handleSearch);
    }
}

async function apiCall(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(API_URL + endpoint, options);
        const result = await response.json();
        
        if (!result.success && result.message === 'Oturum süresi doldu') {
            logout();
            return null;
        }
        
        return result;
    } catch (error) {
        console.error('API Hatası:', error);
        showNotification('Bir hata oluştu. Lütfen daha sonra tekrar deneyin.', 'error');
        return null;
    }
}

function showNotification(message, type = 'info') {
    const notificationDiv = document.createElement('div');
    notificationDiv.className = `alert alert-${type}`;
    notificationDiv.textContent = message;
    notificationDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#00ff00' : type === 'error' ? '#ff0000' : '#ffff00'};
        color: #000;
        padding: 1rem;
        border: 2px solid #fff;
        z-index: 9999;
        font-weight: bold;
    `;
    
    document.body.appendChild(notificationDiv);
    
    setTimeout(() => {
        notificationDiv.remove();
    }, 5000);
}

function handleSearch(e) {
    e.preventDefault();
    const query = document.getElementById('search-input').value.trim();
    
    if (query) {
        window.location.href = `/Web-Projesi2/products.html?search=${encodeURIComponent(query)}`;
    }
}

async function login(email, password) {
    // Demo giriş - gerçek uygulamada API çağrısı yapılır
    if (email === 'admin@arabaparçalari.com' && password === 'admin123') {
        currentUser = {
            id: 1,
            name: 'Admin',
            email: email
        };
        localStorage.setItem('user', JSON.stringify(currentUser));
        updateUserUI();
        showNotification('Giriş başarılı!', 'success');
        return true;
    } else if (email && password) {
        // Demo kullanıcı oluştur
        currentUser = {
            id: Math.random(),
            name: email.split('@')[0],
            email: email
        };
        localStorage.setItem('user', JSON.stringify(currentUser));
        updateUserUI();
        showNotification('Giriş başarılı!', 'success');
        return true;
    }
    
    showNotification('Giriş başarısız', 'error');
    return false;
}

async function register(email, password, passwordConfirm, name) {
    if (password !== passwordConfirm) {
        showNotification('Şifreler eşleşmiyor!', 'error');
        return false;
    }
    
    currentUser = {
        id: Math.random(),
        name: name,
        email: email
    };
    localStorage.setItem('user', JSON.stringify(currentUser));
    updateUserUI();
    showNotification('Kayıt başarılı!', 'success');
    return true;
}

async function logout() {
    currentUser = null;
    localStorage.removeItem('user');
    updateUserUI();
    showNotification('Çıkış başarılı', 'success');
    setTimeout(() => {
        window.location.href = '/Web-Projesi2/index.html';
    }, 1000);
}

// Örnek ürünler
const DEMO_PRODUCTS = [
    {
        id: 1,
        name: 'Motor Yağı 5W-30',
        price: 150,
        category_id: 1,
        description: 'Yüksek kaliteli sentetik motor yağı',
        stock: 50,
        image_url: 'https://via.placeholder.com/300x300?text=Motor+Yağı'
    },
    {
        id: 2,
        name: 'Hava Filtresi',
        price: 75,
        category_id: 1,
        description: 'Orijinal hava filtresi',
        stock: 100,
        image_url: 'https://via.placeholder.com/300x300?text=Hava+Filtresi'
    },
    {
        id: 3,
        name: 'Fren Pedi Seti',
        price: 250,
        category_id: 2,
        description: 'Ön fren pedi seti',
        stock: 30,
        image_url: 'https://via.placeholder.com/300x300?text=Fren+Pedi'
    },
    {
        id: 4,
        name: 'Oto Ampülü H7',
        price: 45,
        category_id: 3,
        description: 'LED oto ampülü',
        stock: 200,
        image_url: 'https://via.placeholder.com/300x300?text=Ampül'
    },
    {
        id: 5,
        name: 'Amortisör Seti',
        price: 800,
        category_id: 4,
        description: 'Ön amortisör seti',
        stock: 15,
        image_url: 'https://via.placeholder.com/300x300?text=Amortisör'
    },
    {
        id: 6,
        name: 'Kaporta Boyası',
        price: 120,
        category_id: 5,
        description: 'Siyah kaporta boyası',
        stock: 40,
        image_url: 'https://via.placeholder.com/300x300?text=Kaporta'
    }
];

const DEMO_CATEGORIES = [
    { id: 1, name: 'Motor Parçaları' },
    { id: 2, name: 'Fren Sistemi' },
    { id: 3, name: 'Elektrik Sistemi' },
    { id: 4, name: 'Süspansiyon' },
    { id: 5, name: 'Kaporta ve Aksesuar' }
];

async function loadProducts(page = 1, categoryId = null, search = null) {
    let filtered = DEMO_PRODUCTS;
    
    if (categoryId) {
        filtered = filtered.filter(p => p.category_id === categoryId);
    }
    
    if (search) {
        filtered = filtered.filter(p => 
            p.name.toLowerCase().includes(search.toLowerCase()) ||
            p.description.toLowerCase().includes(search.toLowerCase())
        );
    }
    
    return filtered;
}

async function loadProductDetail(productId) {
    return DEMO_PRODUCTS.find(p => p.id === parseInt(productId));
}

async function loadCategories() {
    return DEMO_CATEGORIES;
}

async function loadFeaturedProducts() {
    return DEMO_PRODUCTS.slice(0, 3);
}

// Sepet yönetimi - localStorage kullanarak
function getCart() {
    const cartStr = localStorage.getItem('cart');
    return cartStr ? JSON.parse(cartStr) : [];
}

function saveCart(cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
}

async function addToCart(productId, quantity = 1) {
    if (!currentUser) {
        showNotification('Lütfen önce giriş yapın', 'warning');
        window.location.href = '/Web-Projesi2/login.html';
        return false;
    }
    
    const product = await loadProductDetail(productId);
    if (!product) {
        showNotification('Ürün bulunamadı', 'error');
        return false;
    }
    
    const cart = getCart();
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            id: productId,
            name: product.name,
            price: product.price,
            quantity: quantity,
            image_url: product.image_url
        });
    }
    
    saveCart(cart);
    showNotification('Ürün sepete eklendi!', 'success');
    return true;
}

async function loadCart() {
    if (!currentUser) {
        return null;
    }
    
    const cart = getCart();
    let total = 0;
    
    cart.forEach(item => {
        total += item.price * item.quantity;
    });
    
    return {
        items: cart,
        total: total
    };
}

async function removeFromCart(productId) {
    const cart = getCart();
    const filtered = cart.filter(item => item.id !== productId);
    saveCart(filtered);
    showNotification('Ürün sepetten çıkarıldı', 'success');
    return true;
}

async function updateCartItem(productId, quantity) {
    if (quantity <= 0) {
        return removeFromCart(productId);
    }
    
    const cart = getCart();
    const item = cart.find(i => i.id === productId);
    
    if (item) {
        item.quantity = parseInt(quantity);
        saveCart(cart);
        return true;
    }
    
    return false;
}

async function createOrder(shippingAddress, shippingCity, shippingPostalCode, paymentMethod = 'credit_card', notes = '') {
    if (!currentUser) {
        showNotification('Lütfen giriş yapın', 'warning');
        return false;
    }
    
    const cart = getCart();
    if (cart.length === 0) {
        showNotification('Sepetiniz boş', 'error');
        return false;
    }
    
    // Sipariş oluştur
    const order = {
        id: Math.random(),
        user_id: currentUser.id,
        items: cart,
        shipping_address: shippingAddress,
        shipping_city: shippingCity,
        shipping_postal_code: shippingPostalCode,
        payment_method: paymentMethod,
        notes: notes,
        created_at: new Date().toISOString(),
        status: 'pending'
    };
    
    // Siparişleri localStorage'a kaydet
    const orders = JSON.parse(localStorage.getItem('orders') || '[]');
    orders.push(order);
    localStorage.setItem('orders', JSON.stringify(orders));
    
    // Sepeti temizle
    localStorage.removeItem('cart');
    
    showNotification('Sipariş başarıyla oluşturuldu!', 'success');
    return order;
}

async function loadOrders(page = 1) {
    if (!currentUser) {
        return null;
    }
    
    const orders = JSON.parse(localStorage.getItem('orders') || '[]');
    const userOrders = orders.filter(o => o.user_id === currentUser.id);
    
    return {
        orders: userOrders,
        total: userOrders.length
    };
}

async function submitContactForm(name, email, phone, subject, message) {
    const messages = JSON.parse(localStorage.getItem('contact_messages') || '[]');
    
    messages.push({
        id: Math.random(),
        name: name,
        email: email,
        phone: phone,
        subject: subject,
        message: message,
        created_at: new Date().toISOString()
    });
    
    localStorage.setItem('contact_messages', JSON.stringify(messages));
    showNotification('Mesajınız başarıyla gönderildi!', 'success');
    return true;
}

function formatPrice(price) {
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY'
    }).format(price);
}

function createProductCard(product) {
    return `
        <div class="product-card">
            <div class="product-image">
                <img src="${product.image_url || '/Web-Projesi2/placeholder.jpg'}" alt="${product.name}" style="width: 100%; height: 200px; object-fit: cover;">
            </div>
            <div class="product-info">
                <div class="product-name">${product.name}</div>
                <div class="product-price">${formatPrice(product.price)}</div>
                <div class="product-stock">
                    ${product.stock > 0 ? `Stokta: ${product.stock}` : 'Tükendi'}
                </div>
                <p class="product-description">${product.description?.substring(0, 100) || ''}...</p>
                <button class="btn btn-small btn-block" onclick="addToCart(${product.id})" style="width: 100%; padding: 0.75rem; background: #000; color: #fff; border: 2px solid #ff0000; font-weight: bold; cursor: pointer;">SEPETE EKLE</button>
            </div>
        </div>
    `;
}

function getPageNumber() {
    const params = new URLSearchParams(window.location.search);
    return parseInt(params.get('page')) || 1;
}

function getCategoryId() {
    const params = new URLSearchParams(window.location.search);
    return parseInt(params.get('category')) || null;
}

function getSearchQuery() {
    const params = new URLSearchParams(window.location.search);
    return params.get('search') || null;
}
