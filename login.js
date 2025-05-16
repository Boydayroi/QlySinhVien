document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.form');
    const emailInput = document.querySelector('input[name="uemail"]');
    const passwordInput = document.querySelector('input[name="password"]');
    const togglePassword = document.querySelector('.inputForm svg:last-child');
    const messageDiv = document.querySelector('.message');

    // Hàm validate email
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Hàm hiển thị thông báo
    function showMessage(text, isError = true) {
        if (messageDiv) {
            messageDiv.textContent = text;
            messageDiv.style.color = isError ? 'red' : 'green';
            messageDiv.style.display = 'block';
        } else {
            alert(text);
        }
    }

    // Toggle hiển thị mật khẩu
    if (togglePassword) {
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.style.opacity = type === 'password' ? '0.5' : '1';
        });
    }

    // Xử lý submit form
    form.addEventListener('submit', (e) => {
        e.preventDefault(); // Ngăn gửi form để kiểm tra

        const email = emailInput.value.trim();
        const password = passwordInput.value;

        // Validate email
        if (!validateEmail(email)) {
            showMessage('Invalid email format');
            return;
        }

        // Validate password
        if (password.length < 6) {
            showMessage('Password must be at least 6 characters');
            return;
        }

        // Giả lập gửi form thành công
        showMessage(`Form submitted! Email: ${email}`, false);

        // Nếu muốn gửi form thực sự, uncomment dòng sau
        // form.submit();
    });

    // Xử lý nút Google/Apple (placeholder)
    document.querySelectorAll('.btn.google, .btn.apple').forEach(btn => {
        btn.addEventListener('click', () => {
            showMessage('Google/Apple login not implemented yet', false);
        });
    });
});