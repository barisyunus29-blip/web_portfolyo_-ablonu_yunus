document.getElementById('register-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password-confirm').value;
    
    if (!name || !email || !password || !passwordConfirm) {
        showNotification('Lütfen tüm alanları doldurun', 'error');
        return;
    }
    
    if (password !== passwordConfirm) {
        showNotification('Şifreler eşleşmiyor', 'error');
        return;
    }
    
    if (password.length < 6) {
        showNotification('Şifre en az 6 karakter olmalıdır', 'error');
        return;
    }
    
    const success = await register(email, password, passwordConfirm, name);
    
    if (success) {
        setTimeout(() => {
            window.location.href = '/login.html';
        }, 2000);
    }
});
