
let currentPage = 1;
const itemsPerPage = 8;
let filteredProducts = [...products];

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadProductsTable();
    setupEventListeners();
    updatePaginationInfo();
});

// Charger le tableau des produits
function loadProductsTable() {
    const tbody = document.getElementById('productsTableBody');
    tbody.innerHTML = '';

    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const productsToShow = filteredProducts.slice(startIndex, endIndex);

    productsToShow.forEach(product => {
        const row = document.createElement('tr');
        
        // Déterminer la classe du badge de statut
        let statusClass = '';
        if (product.status === 'En stock') statusClass = 'status-in-stock';
        else if (product.status === 'Stock faible') statusClass = 'status-low-stock';
        else if (product.status === 'Rupture') statusClass = 'status-out-of-stock';

        row.innerHTML = `
            <td><strong>${product.ref}</strong></td>
            <td>${product.name}</td>
            <td>${product.category}</td>
            <td>${formatPrice(product.price)}</td>
            <td>${product.stock}</td>
            <td><span class="status-badge ${statusClass}">${product.status}</span></td>
            <td>
                <div class="actions">
                    <button class="action-btn edit" onclick="editProduct(${product.id})" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete" onclick="deleteProduct(${product.id})" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div> 
            </td>
        `;
        tbody.appendChild(row);
    });

    updatePaginationInfo();
}

// Configuration des événements
function setupEventListeners() {
    // Recherche
    document.getElementById('productSearch').addEventListener('input', function(e) {
        filterProducts();
    });

    // Filtres
    document.getElementById('categoryFilter').addEventListener('change', filterProducts);
    document.getElementById('statusFilter').addEventListener('change', filterProducts);

    // Pagination
    document.getElementById('prevPage').addEventListener('click', goToPrevPage);
    document.getElementById('nextPage').addEventListener('click', goToNextPage);

    // Modal
    const modal = document.getElementById('productModal');
    const closeBtn = document.querySelector('.close');
    const cancelBtn = document.getElementById('cancelBtn');

    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    // Fermer le modal en cliquant à l'extérieur
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Formulaire
    document.getElementById('productForm').addEventListener('submit', handleFormSubmit);

    // Bouton d'ajout
    document.getElementById('addProductBtn').addEventListener('click', openAddModal);
}

// Filtrer les produits
function filterProducts() {
    const searchTerm = document.getElementById('productSearch').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;

    filteredProducts = products.filter(product => {
        const matchesSearch = product.name.toLowerCase().includes(searchTerm) || 
                             product.ref.toLowerCase().includes(searchTerm);
        const matchesCategory = !categoryFilter || product.category === categoryFilter;
        const matchesStatus = !statusFilter || product.status === statusFilter;

        return matchesSearch && matchesCategory && matchesStatus;
    });

    currentPage = 1;
    loadProductsTable();
}

// Pagination
function goToPrevPage() {
    if (currentPage > 1) {
        currentPage--;
        loadProductsTable();
    }
}

function goToNextPage() {
    const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        loadProductsTable();
    }
}

function updatePaginationInfo() {
    const totalItems = filteredProducts.length;
    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(startItem + itemsPerPage - 1, totalItems);

    document.getElementById('startItem').textContent = startItem;
    document.getElementById('endItem').textContent = endItem;
    document.getElementById('totalItems').textContent = totalItems;
    document.getElementById('currentPage').textContent = currentPage;

    // Désactiver les boutons de pagination si nécessaire
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = endItem >= totalItems;
}

// Modal functions
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Nouveau Produit';
    document.getElementById('productForm').reset();
    document.getElementById('productModal').style.display = 'block';
}

function openEditModal(product) {
    document.getElementById('modalTitle').textContent = 'Modifier le Produit';
    document.getElementById('productRef').value = product.ref;
    document.getElementById('productName').value = product.name;
    document.getElementById('productCategory').value = product.category;
    document.getElementById('productPrice').value = product.price;
    document.getElementById('productStock').value = product.stock;
    document.getElementById('productStatus').value = product.status;
    
    // Stocker l'ID du produit en cours de modification
    document.getElementById('productForm').dataset.editingId = product.id;
    
    document.getElementById('productModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('productModal').style.display = 'none';
    document.getElementById('productForm').reset();
    delete document.getElementById('productForm').dataset.editingId;
}

// Gérer la soumission du formulaire
function handleFormSubmit(e) {
    e.preventDefault();

    const formData = {
        ref: document.getElementById('productRef').value,
        name: document.getElementById('productName').value,
        category: document.getElementById('productCategory').value,
        price: parseFloat(document.getElementById('productPrice').value),
        stock: parseInt(document.getElementById('productStock').value),
        status: document.getElementById('productStatus').value
    }; 
    // Mettre à jour le statut en fonction du stock
    if (formData.stock === 0) {
        formData.status = 'Rupture';
    } else if (formData.stock <= 10) {
        formData.status = 'Stock faible';
    } else {
        formData.status = 'En stock';
    }

    const editingId = document.getElementById('productForm').dataset.editingId;

    if (editingId) {
        // Modification
        updateProduct(parseInt(editingId), formData);
    } else {
        // Ajout
        addProduct(formData);
    }

    closeModal();
}


// Éditer un produit
function editProduct(id) {
    const product = products.find(p => p.id === id);
    if (product) {
        openEditModal(product);
    }
}

// Supprimer un produit
function deleteProduct(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
        products = products.filter(p => p.id !== id);
        filterProducts(); // Recharger avec les filtres actuels
        showNotification('Produit supprimé avec succès!', 'success');
    }
}

// Formater le prix
function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2
    }).format(price);
}

// Afficher une notification
function showNotification(message, type) {
    // Créer l'élément de notification
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
    `;

    if (type === 'success') {
        notification.style.background = '#10b981';
    } else {
        notification.style.background = '#ef4444';
    }

    notification.textContent = message;
    document.body.appendChild(notification);

    // Supprimer après 3 secondes
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