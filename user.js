document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('.login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const rememberCheckbox = document.getElementById('remember');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordGroup = document.querySelector('.password-group');

    // Fonction pour basculer la visibilité du mot de passe
    togglePasswordBtn.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Changer l'icône et l'apparence
        if (type === 'text') {
            passwordGroup.classList.add('password-visible');
            togglePasswordBtn.innerHTML = '<span class="eye-icon">🔒</span>';
        } else {
            passwordGroup.classList.remove('password-visible');
            togglePasswordBtn.innerHTML = '<span class="eye-icon">👁️</span>';
        }
    });

    // Charger les données sauvegardées si "Remember me" était coché
    if (localStorage.getItem('rememberMe') === 'true') {
        emailInput.value = localStorage.getItem('savedEmail') || '';
        passwordInput.value = localStorage.getItem('savedPassword') || '';
        rememberCheckbox.checked = true;
    }

    // Gestion de la soumission du formulaire
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = emailInput.value;
        const password = passwordInput.value;
        const rememberMe = rememberCheckbox.checked;

        // Validation basique
        if (!email || !password) {
            showError('Veuillez remplir tous les champs');
            return;
        }

        // Validation email
        if (!isValidEmail(email)) {
            showError('Veuillez entrer une adresse email valide');
            return;
        }

        // Sauvegarder les données si "Remember me" est coché
        if (rememberMe) {
            localStorage.setItem('savedEmail', email);
            localStorage.setItem('savedPassword', password);
            localStorage.setItem('rememberMe', 'true');
        } else {
            localStorage.removeItem('savedEmail');
            localStorage.removeItem('savedPassword');
            localStorage.setItem('rememberMe', 'false');
        }

        // Simulation de connexion
        console.log('Tentative de connexion:', { email, password, rememberMe });
        
        // Ici vous ajouteriez votre logique de connexion réelle
        showSuccess('Connexion réussie!');
    });

    // Animation d'entrée des champs
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });

    // Fonction de validation d'email
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Fonction pour afficher les messages d'erreur
    function showError(message) {
        // Supprimer les anciens messages d'erreur
        const existingError = document.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }

        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.cssText = `
            color: #e74c3c;
            background-color: #fdf2f2;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 0.9rem;
        `;
        errorDiv.textContent = message;
        
        loginForm.insertBefore(errorDiv, loginForm.firstChild);
        
        // Supprimer le message après 5 secondes
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
    }
);

    