// Données de démonstration

let filteredInvoices = [...invoices];
let currentTab = 'invoices';

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadInvoicesGrid();
    setupEventListeners();
    setupModal();
});

// Charger la grille des factures
function loadInvoicesGrid() {
    const grid = document.getElementById('invoicesGrid');
    const emptyState = document.getElementById('emptyState');
    
    if (filteredInvoices.length === 0) {
        grid.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }
    
    grid.style.display = 'grid';
    emptyState.style.display = 'none';
    grid.innerHTML = '';

    filteredInvoices.forEach(invoice => {
        const card = createInvoiceCard(invoice);
        grid.appendChild(card);
    });
}

// Créer une carte de facture
function createInvoiceCard(invoice) {
    const card = document.createElement('div');
    card.className = `invoice-card ${invoice.status}`;
    
    // Formater les dates
    const issueDate = formatDate(invoice.issueDate);
    const dueDate = formatDate(invoice.dueDate);
    
    // Déterminer le statut et la classe CSS
    let statusText = '';
    let statusClass = '';
    switch(invoice.status) {
        case 'payée':
            statusText = 'Payée';
            statusClass = 'status-paid';
            break;
        case 'en_attente':
            statusText = 'En attente';
            statusClass = 'status-pending';
            break;
        case 'en_retard':
            statusText = 'En retard';
            statusClass = 'status-overdue';
            break;
        case 'brouillon':
            statusText = 'Brouillon';
            statusClass = 'status-draft';
            break;
    }

    card.innerHTML = `
        <div class="invoice-header">
            <div class="invoice-number">${invoice.number}</div>
            <div class="invoice-status ${statusClass}">${statusText}</div>
        </div>
        <div class="invoice-client">${invoice.client}</div>
        <div class="invoice-details">
            <div class="invoice-detail">
                <div class="detail-label">Date d'émission</div>
                <div class="detail-value">${issueDate}</div>
            </div>
            <div class="invoice-detail">
                <div class="detail-label">Échéance</div>
                <div class="detail-value">${dueDate}</div>
            </div>
        </div>
        <div class="invoice-amount">${formatPrice(invoice.amount)}</div>
        <div class="invoice-actions">
            <button class="action-btn secondary" onclick="viewInvoice(${invoice.id})" title="Voir">
                <i class="fas fa-eye"></i>
                Voir
            </button>
            <button class="action-btn primary" onclick="editInvoice(${invoice.id})" title="Modifier">
                <i class="fas fa-edit"></i>
                Modifier
            </button>
            <button class="action-btn secondary" onclick="downloadInvoice(${invoice.id})" title="Télécharger">
                <i class="fas fa-download"></i>
            </button>
        </div>
    `;
    
    return card;
}

// Configuration des événements
function setupEventListeners() {
    // Tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Retirer la classe active de tous les boutons
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');
            currentTab = this.dataset.tab;
            filterInvoices();
        });
    });

    // Recherche
    document.getElementById('invoiceSearch').addEventListener('input', filterInvoices);

    // Filtres
    document.getElementById('statusFilter').addEventListener('change', filterInvoices);
    document.getElementById('periodFilter').addEventListener('change', filterInvoices);

    // Bouton de création
    document.getElementById('createFirstInvoice').addEventListener('click', openInvoiceModal);
}

// Filtrer les factures
function filterInvoices() {
    const searchTerm = document.getElementById('invoiceSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const periodFilter = document.getElementById('periodFilter').value;

    filteredInvoices = invoices.filter(invoice => {
        // Filtre par recherche
        const matchesSearch = invoice.number.toLowerCase().includes(searchTerm) ||
                             invoice.client.toLowerCase().includes(searchTerm) ||
                             invoice.amount.toString().includes(searchTerm);
        
        // Filtre par statut
        const matchesStatus = !statusFilter || invoice.status === statusFilter;
        
        // Filtre par période (simplifié)
        const matchesPeriod = true; // Implémentation plus complexe pour les périodes
        
        return matchesSearch && matchesStatus && matchesPeriod;
    });

    loadInvoicesGrid();
}

// Configuration du modal
function setupModal() {
    const modal = document.getElementById('invoiceModal');
    const closeBtn = document.querySelector('.close');
    const cancelBtn = document.getElementById('cancelInvoiceBtn');

    // Ouvrir le modal
    document.getElementById('newInvoiceBtn').addEventListener('click', openInvoiceModal);

    // Fermer le modal
    closeBtn.addEventListener('click', closeInvoiceModal);
    cancelBtn.addEventListener('click', closeInvoiceModal);

    // Fermer en cliquant à l'extérieur
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeInvoiceModal();
        }
    });

    // Soumission du formulaire
    document.getElementById('invoiceForm').addEventListener('submit', handleInvoiceSubmit);

    // Définir la date d'aujourd'hui par défaut
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('invoiceDate').value = today;
    
    // Définir la date d'échéance à 30 jours par défaut
    const dueDate = new Date();
    dueDate.setDate(dueDate.getDate() + 30);
    document.getElementById('dueDate').value = dueDate.toISOString().split('T')[0];
}

// Ouvrir le modal de facture
function openInvoiceModal() {
    document.getElementById('invoiceModal').style.display = 'block';
}

// Fermer le modal de facture
function closeInvoiceModal() {
    document.getElementById('invoiceModal').style.display = 'none';
    document.getElementById('invoiceForm').reset();
}

// Gérer la soumission du formulaire
function handleInvoiceSubmit(e) {
    e.preventDefault();

    const formData = {
        client: document.getElementById('clientSelect').selectedOptions[0].text,
        issueDate: document.getElementById('invoiceDate').value,
        dueDate: document.getElementById('dueDate').value,
        status: document.getElementById('invoiceStatus').value
    };

    // Générer un nouveau numéro de facture
    const nextId = Math.max(...invoices.map(i => i.id)) + 1;
    const newInvoice = {
        id: nextId,
        number: `FAC-2025-${String(nextId).padStart(3, '0')}`,
        client: formData.client,
        amount: 0, // Montant à définir plus tard
        issueDate: formData.issueDate,
        dueDate: formData.dueDate,
        status: formData.status,
        clientEmail: `${formData.client.toLowerCase().replace(/\s+/g, '')}@example.fr`
    };

    invoices.push(newInvoice);
    filterInvoices();
    closeInvoiceModal();
    showNotification('Facture créée avec succès!', 'success');
}

// Actions sur les factures
function viewInvoice(id) {
    const invoice = invoices.find(i => i.id === id);
    if (invoice) {
        alert(`Détails de la facture:\n\nNuméro: ${invoice.number}\nClient: ${invoice.client}\nMontant: ${formatPrice(invoice.amount)}\nStatut: ${invoice.status}\nDate d'émission: ${formatDate(invoice.issueDate)}\nÉchéance: ${formatDate(invoice.dueDate)}`);
    }
}

function editInvoice(id) {
    const invoice = invoices.find(i => i.id === id);
    if (invoice) {
        alert(`Modification de la facture: ${invoice.number}\n\nCette fonctionnalité sera implémentée dans la version complète.`);
    }
}

function downloadInvoice(id) {
    const invoice = invoices.find(i => i.id === id);
    if (invoice) {
        showNotification(`Téléchargement de ${invoice.number}...`, 'success');
        // Simulation de téléchargement
        setTimeout(() => {
            showNotification('Facture téléchargée avec succès!', 'success');
        }, 1000);
    }
}

// Fonctions utilitaires
function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2
    }).format(price);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    }).format(date);
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