// Initialize DataTables
$(document).ready(function() {
    // Sidebar Toggle
    $('#sidebarCollapse').on('click', function() {
        $('#sidebar').toggleClass('active');
    });

    // Initialize DataTable
    const productsTable = $('#productsTable').DataTable({
        responsive: true,
        ajax: {
            url: 'api/products', // Replace with your API endpoint
            dataSrc: ''
        },
        columns: [
            {
                data: null,
                defaultContent: '<input type="checkbox" class="product-checkbox">',
                orderable: false
            },
            { data: 'id' },
            {
                data: 'image',
                render: function(data) {
                    return `<img src="${data}" alt="Product" style="width: 50px; height: 50px; object-fit: cover;">`;
                }
            },
            { data: 'name' },
            { data: 'category' },
            {
                data: 'price',
                render: function(data) {
                    return `$${parseFloat(data).toFixed(2)}`;
                }
            },
            { data: 'stock' },
            {
                data: 'status',
                render: function(data) {
                    const statusClasses = {
                        'active': 'success',
                        'inactive': 'danger',
                        'low_stock': 'warning'
                    };
                    return `<span class="badge bg-${statusClasses[data]}">${data}</span>`;
                }
            },
            {
                data: null,
                defaultContent: `
                    <button class="btn btn-sm btn-primary edit-btn"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger delete-btn"><i class="fas fa-trash"></i></button>
                `
            }
        ]
    });

    // Initialize Charts
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Sales',
                data: [12, 19, 3, 5, 2, 3],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        }
    });

    const productsCtx = document.getElementById('productsChart').getContext('2d');
    const productsChart = new Chart(productsCtx, {
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

    // Image Preview
    $('input[type="file"]').on('change', function(e) {
        const files = e.target.files;
        const preview = $('#imagePreview');
        preview.empty();

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.append(`
                    <img src="${e.target.result}" class="img-thumbnail">
                `);
            }

            reader.readAsDataURL(file);
        }
    });

    // Export CSV
    $('#exportBtn').on('click', function() {
        const csvContent = "data:text/csv;charset=utf-8,";
        // Add export logic here
    });

    // Select All Checkbox
    $('#selectAll').on('change', function() {
        $('.product-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Form Validation
    $('#productForm').on('submit', function(e) {
        e.preventDefault();
        // Add form submission logic here
    });

    // Real-time Notifications (Example using WebSocket)
    const ws = new WebSocket('ws://your-websocket-server');
    ws.onmessage = function(event) {
        const notification = JSON.parse(event.data);
        // Handle notification
    };
});

// Helper Functions
function showNotification(message, type = 'success') {
    // Add notification logic here
}

function confirmDelete(id) {
    // Add delete confirmation logic here
}

// Error Handling
window.onerror = function(message, source, lineno, colno, error) {
    console.error('An error occurred:', error);
    showNotification('An error occurred. Please try again.', 'error');
}; 