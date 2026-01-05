// Données de démonstration

let filteredClients = [...clients];
let currentPage = 1;
const clientsPerPage = 6;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadClientsGrid();
    setupEventListeners();
    setupModal();
    updatePaginationInfo();
});

// Charger la grille des clients
function loadClientsGrid() {
    const grid = document.getElementById('clientsGrid');
    const emptyState = document.getElementById('emptyState');
    
    if (filteredClients.length === 0) {
        grid.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }
    
    grid.style.display = 'grid';
    emptyState.style.display = 'none';
    grid.innerHTML = '';

    const startIndex = (currentPage - 1) * clientsPerPage;
    const endIndex = startIndex + clientsPerPage;
    const clientsToShow = filteredClients.slice(startIndex, endIndex);

    clientsToShow.forEach(client => {
        const card = createClientCard(client);
        grid.appendChild(card);
    });

    updatePaginationInfo();
}

// Créer une carte client
function createClientCard(client) {
    const card = document.createElement('div');
    card.className = `client-card ${client.type}`;
    
    card.innerHTML = `
        <div class="client-type">${getTypeLabel(client.type)}</div>
        <div class="client-header">
            <div class="client-code">${client.code}</div>
            <div class="client-name">
                <h3>${client.name}</h3>
                <p>${client.contact}</p>
            </div>
        </div>
        <div class="client-contact">
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <span>${client.email}</span>
            </div>
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <span>${client.phone}</span>
            </div>
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>${client.address}</span>
            </div>
        </div>
        <div class="client-stats">
            <div class="stat-item">
                <div class="stat-label">Commandes</div>
                <div class="stat-value orders">${client.orders}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Total Dépensé</div>
                <div class="stat-value amount">${formatPrice(client.totalSpent)}</div>
            </div>
        </div>
        <div class="client-actions">
            <button class="action-btn secondary" onclick="viewClient(${client.id})">
                <i class="fas fa-eye"></i>
                Voir
            </button>
            <button class="action-btn primary" onclick="editClient(${client.id})">
                <i class="fas fa-edit"></i>
                Modifier
            </button>
        </div>
    `;
    
    return card;
}

// Configuration des événements
function setupEventListeners() {
    // Recherche
    document.getElementById('clientSearch').addEventListener('input', filterClients);

    // Filtres
    document.getElementById('typeFilter').addEventListener('change', filterClients);
    document.getElementById('sortFilter').addEventListener('change', sortClients);

    // Pagination
    document.getElementById('prevPage').addEventListener('click', goToPrevPage);
    document.getElementById('nextPage').addEventListener('click', goToNextPage);

    // Bouton créer premier client
    document.getElementById('createFirstClient').addEventListener('click', openClientModal);
    
    // Bouton exporter
    document.getElementById('exportBtn').addEventListener('click', exportClients);
}

// Filtrer les clients
function filterClients() {
    const searchTerm = document.getElementById('clientSearch').value.toLowerCase();
    const typeFilter = document.getElementById('typeFilter').value;

    filteredClients = clients.filter(client => {
        const matchesSearch = client.name.toLowerCase().includes(searchTerm) ||
                             client.contact.toLowerCase().includes(searchTerm) ||
                             client.email.toLowerCase().includes(searchTerm) ||
                             client.code.toLowerCase().includes(searchTerm);
        
        const matchesType = !typeFilter || client.type === typeFilter;
        
        return matchesSearch && matchesType;
    });

    currentPage = 1;
    loadClientsGrid();
}

// Trier les clients
function sortClients() {
    const sortBy = document.getElementById('sortFilter').value;
    
    filteredClients.sort((a, b) => {
        switch(sortBy) {
            case 'recent':
                return new Date(b.joinDate) - new Date(a.joinDate);
            case 'orders':
                return b.orders - a.orders;
            case 'amount':
                return b.totalSpent - a.totalSpent;
            case 'name':
                return a.name.localeCompare(b.name);
            default:
                return 0;
        }
    });

    loadClientsGrid();
}

// Pagination
function goToPrevPage() {
    if (currentPage > 1) {
        currentPage--;
        loadClientsGrid();
    }
}

function goToNextPage() {
    const totalPages = Math.ceil(filteredClients.length / clientsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        loadClientsGrid();
    }
}

function updatePaginationInfo() {
    const totalItems = filteredClients.length;
    const startItem = (currentPage - 1) * clientsPerPage + 1;
    const endItem = Math.min(startItem + clientsPerPage - 1, totalItems);

    document.getElementById('startItem').textContent = startItem;
    document.getElementById('endItem').textContent = endItem;
    document.getElementById('currentPage').textContent = currentPage;

    // Désactiver les boutons de pagination si nécessaire
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = endItem >= totalItems;
}

// Configuration du modal
function setupModal() {
    const modal = document.getElementById('clientModal');
    const closeBtn = document.querySelector('.close');
    const cancelBtn = document.getElementById('cancelBtn');

    // Ouvrir le modal
    document.getElementById('newClientBtn').addEventListener('click', openClientModal);

    // Fermer le modal
    closeBtn.addEventListener('click', closeClientModal);
    cancelBtn.addEventListener('click', closeClientModal);

    // Fermer en cliquant à l'extérieur
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeClientModal();
        }
    });

    // Soumission du formulaire
    document.getElementById('clientForm').addEventListener('submit', handleClientSubmit);

    // Générer un code client
    document.getElementById('clientName').addEventListener('blur', generateClientCode);
}

// Ouvrir le modal client
function openClientModal() {
    document.getElementById('clientModal').style.display = 'block';
}

// Fermer le modal client
function closeClientModal() {
    document.getElementById('clientModal').style.display = 'none';
    document.getElementById('clientForm').reset();
}

// Gérer la soumission du formulaire
function handleClientSubmit(e) {
    e.preventDefault();

    const formData = {
        name: document.getElementById('clientName').value,
        contact: document.getElementById('clientContact').value,
        email: document.getElementById('clientEmail').value,
        phone: document.getElementById('clientPhone').value,
        address: document.getElementById('clientAddress').value,
        city: document.getElementById('clientCity').value,
        country: document.getElementById('clientCountry').value,
        type: document.getElementById('clientType').value,
        code: document.getElementById('clientCode').value || generateClientCodeFromName()
    };

    // Validation
    if (!validateClientForm(formData)) {
        return;
    }

    // Ajouter le client (simulation)
    const newClient = {
        id: clients.length + 1,
        ...formData,
        orders: 0,
        totalSpent: 0,
        status: "actif",
        joinDate: new Date().toISOString().split('T')[0]
    };

    clients.push(newClient);
    filterClients();
    closeClientModal();
    showNotification('Client ajouté avec succès!', 'success');
}

// Valider le formulaire
function validateClientForm(data) {
    if (!data.name || !data.email || !data.type) {
        showNotification('Veuillez remplir les champs obligatoires', 'error');
        return false;
    }

    if (data.email && !isValidEmail(data.email)) {
        showNotification('Veuillez entrer une adresse email valide', 'error');
        return false;
    }

    return true;
}

// Générer un code client
function generateClientCode() {
    const name = document.getElementById('clientName').value;
    if (name && !document.getElementById('clientCode').value) {
        const code = generateClientCodeFromName();
        document.getElementById('clientCode').value = code;
    }
}

function generateClientCodeFromName() {
    const name = document.getElementById('clientName').value;
    if (!name) return '';
    
    // Prendre les 2 premières lettres du premier mot
    const words = name.split(' ');
    let code = '';
    
    if (words.length >= 2) {
        code = words[0].substring(0, 1).toUpperCase() + 
               words[1].substring(0, 1).toUpperCase();
    } else {
        code = name.substring(0, 2).toUpperCase();
    }
    
    // Vérifier si le code existe déjà
    const existingCodes = clients.map(c => c.code);
    if (existingCodes.includes(code)) {
        // Ajouter un chiffre
        let counter = 1;
        while (existingCodes.includes(code + counter)) {
            counter++;
        }
        code = code + counter;
    }
    
    return code;
}

// Actions sur les clients
function viewClient(id) {
    const client = clients.find(c => c.id === id);
    if (client) {
        alert(`Détails du client:\n\nNom: ${client.name}\nContact: ${client.contact}\nEmail: ${client.email}\nTéléphone: ${client.phone}\nAdresse: ${client.address}\nCommandes: ${client.orders}\nTotal dépensé: ${formatPrice(client.totalSpent)}`);
    }
}

function editClient(id) {
    const client = clients.find(c => c.id === id);
    if (client) {
        alert(`Modification du client: ${client.name}\n\nCette fonctionnalité sera implémentée dans la version complète.`);
    }
}

function exportClients() {
    showNotification('Export des clients en cours...', 'success');
    setTimeout(() => {
        showNotification('Export terminé! Fichier téléchargé.', 'success');
    }, 1500);
}

// Fonctions utilitaires
function getTypeLabel(type) {
    const labels = {
        'entreprise': 'Entreprise',
        'particulier': 'Particulier',
        'prospect': 'Prospect'
    };
    return labels[type] || type;
}

function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 0
    }).format(price);
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Afficher une notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        z-index: 1001;
        animation: slideIn 0.3s ease;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
    `;

    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Ajouter les styles d'animation pour la notification
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);