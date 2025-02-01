class Dashboard {
    constructor() {
        this.initializeEventListeners();
        this.loadDashboardData();
        this.initializeCharts();
    }

    initializeEventListeners() {
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            document.getElementById('wrapper').classList.toggle('toggled');
        });

        // Navigation
        document.querySelectorAll('[data-page]').forEach(element => {
            element.addEventListener('click', (e) => {
                e.preventDefault();
                this.loadPage(e.target.dataset.page);
            });
        });
    }

    async loadDashboardData() {
        try {
            const response = await fetch('/api/dashboard/stats');
            const data = await response.json();
            this.updateDashboardStats(data);
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            this.showNotification('Error loading dashboard data', 'error');
        }
    }

    updateDashboardStats(data) {
        // Update statistics cards
        document.getElementById('totalSales').textContent = `$${data.totalSales.toLocaleString()}`;
        document.getElementById('totalOrders').textContent = data.totalOrders.toLocaleString();
        document.getElementById('totalProducts').textContent = data.totalProducts.toLocaleString();
        document.getElementById('totalCustomers').textContent = data.totalCustomers.toLocaleString();
    }

    initializeCharts() {
        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        this.salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Sales',
                    data: [12, 19, 3, 5, 2, 3],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Products Chart
        const productsCtx = document.getElementById('productsChart').getContext('2d');
        this.productsChart = new Chart(productsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Electronics', 'Clothing', 'Furniture'],
                datasets: [{
                    data: [300, 50, 100],
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)'
                    ]
                }]
            }
        });
    }

    showNotification(message, type = 'success') {
        const toast = document.createElement('div');
        toast.classList.add('toast', `bg-${type}`, 'text-white');
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="toast-header">
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;

        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    async loadPage(pageName) {
        try {
            const response = await fetch(`/components/${pageName}.html`);
            const content = await response.text();
            document.getElementById('mainContent').innerHTML = content;
            
            // Initialize page-specific features
            switch(pageName) {
                case 'products':
                    new ProductsManager();
                    break;
                case 'analytics':
                    new AnalyticsManager();
                    break;
                // Add other page initializations as needed
            }
        } catch (error) {
            console.error(`Error loading ${pageName} page:`, error);
            this.showNotification(`Error loading ${pageName} page`, 'error');
        }
    }
}

// Initialize Dashboard
document.addEventListener('DOMContentLoaded', () => {
    window.dashboardApp = new Dashboard();
}); 