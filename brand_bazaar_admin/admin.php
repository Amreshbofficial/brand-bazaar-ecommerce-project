<?php
require_once 'auth.php';

$auth = new Auth();
if(!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$current_user = $auth->getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Brand Bazaar - Admin Panel</title>
    <link rel="icon" type="image/x-icon" href="/icons/favicon.ico" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <!-- Your existing CSS styles -->
    <style>
      /* Your existing CSS styles */
    </style>
  </head>
  <body class="admin-panel">
    <!-- Admin Header -->
    <header class="admin-header">
      <div class="admin-brand">
        <i class="fas fa-gem"></i>
        <span>Brand Bazaar Admin</span>
      </div>

      <div class="admin-header-controls">
        <div class="search-container">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search..." id="globalSearch" />
        </div>

        <div class="notification-icon">
          <i class="fas fa-bell"></i>
          <span class="notification-badge">5</span>
          <div class="notification-dropdown">
            <div class="notification-item unread">
              <div class="notification-icon">
                <i class="fas fa-shopping-cart text-primary"></i>
              </div>
              <div class="notification-content">
                <h5>New Order Received</h5>
                <p>Order #ORD-2025-105 from John Smith</p>
                <div class="notification-time">10 min ago</div>
              </div>
            </div>
            <!-- More notification items -->
          </div>
        </div>

        <button class="dark-mode-toggle" id="darkModeToggle">
          <i class="fas fa-moon"></i>
        </button>

        <div class="admin-user" id="userDropdownToggle">
          <span><?php echo htmlspecialchars($current_user['full_name']); ?></span>
          <img src="<?php echo htmlspecialchars($current_user['avatar']); ?>" alt="Admin" />
          <div class="user-dropdown">
            <a href="#"><i class="fas fa-user-circle"></i> My Profile</a>
            <a href="#"><i class="fas fa-cog"></i> Account Settings</a>
            <a href="#"><i class="fas fa-question-circle"></i> Help Center</a>
            <a href="#" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
          </div>
        </div>
      </div>
    </header>

    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">
      <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
      </button>
      <ul class="admin-menu">
        <li>
          <a href="#" class="active" data-section="dashboard">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
          </a>
        </li>
        <!-- Rest of your sidebar menu -->
      </ul>
    </aside>

    <!-- Admin Main Content -->
    <main class="admin-main">
      <!-- Dashboard Section -->
      <section class="content-section" id="dashboard-section">
        <h1>Dashboard</h1>

        <!-- Stats Cards - Will be populated via JavaScript -->
        <div class="stats-grid" id="statsGrid">
          <!-- JavaScript will populate this -->
        </div>

        <!-- Charts -->
        <div class="charts-container">
          <div class="chart-card">
            <div class="chart-header">
              <div class="chart-title">Revenue Overview</div>
              <div class="chart-actions">
                <select id="revenueTimeRange">
                  <option value="7">Last 7 days</option>
                  <option value="30" selected>Last 30 days</option>
                  <option value="90">Last 90 days</option>
                </select>
              </div>
            </div>
            <div class="chart-placeholder" id="revenueChart">
              <!-- Chart will be rendered here -->
            </div>
          </div>

          <div class="chart-card">
            <div class="chart-header">
              <div class="chart-title">Top Categories</div>
            </div>
            <div class="chart-placeholder" id="categoryChart">
              <!-- Chart will be rendered here -->
            </div>
          </div>
        </div>

        <!-- Recent Orders -->
        <div class="admin-card">
          <div class="admin-card-header">
            <span>Recent Orders</span>
            <a href="#orders" class="btn btn-sm btn-primary">View All</a>
          </div>
          <div class="admin-card-body">
            <table class="admin-table" id="recentOrdersTable">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Customer</th>
                  <th>Date</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="recentOrdersBody">
                <!-- Will be populated via JavaScript -->
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- Other sections (Products, Orders, Customers) -->
      <!-- ... -->
    </main>

    <!-- Modals -->
    <!-- ... -->

    <script>
      // Global variables
      let currentDeleteItem = null;
      
      // DOM Content Loaded
      document.addEventListener("DOMContentLoaded", function () {
        // Initialize the dashboard
        loadDashboardStats();
        loadSalesChart();
        loadCategoryChart();
        loadRecentOrders();
        
        // Your existing JavaScript code
        // ...
        
        // Add logout functionality
        document.getElementById('logoutBtn').addEventListener('click', function(e) {
          e.preventDefault();
          logout();
        });
      });
      
      // Load dashboard stats
      function loadDashboardStats() {
        fetch('dashboard_actions.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'action=get_stats'
        })
        .then(response => response.json())
        .then(data => {
          if(data.success) {
            const stats = data.stats;
            const statsGrid = document.getElementById('statsGrid');
            
            statsGrid.innerHTML = `
              <div class="stat-card">
                <div class="stat-icon">
                  <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                  <h3 id="totalOrders">${stats.total_orders}</h3>
                  <p>Total Orders</p>
                  <small class="text-success">+12% from last month</small>
                </div>
              </div>
              <div class="stat-card">
                <div class="stat-icon">
                  <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                  <h3 id="totalRevenue">$${stats.total_revenue.toFixed(2)}</h3>
                  <p>Total Revenue</p>
                  <small class="text-success">+8% from last month</small>
                </div>
              </div>
              <div class="stat-card">
                <div class="stat-icon">
                  <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                  <h3 id="totalCustomers">${stats.total_customers}</h3>
                  <p>Total Customers</p>
                  <small class="text-success">+5.2% from last month</small>
                </div>
              </div>
              <div class="stat-card">
                <div class="stat-icon">
                  <i class="fas fa-box-open"></i>
                </div>
                <div class="stat-info">
                  <h3 id="totalProducts">${stats.total_products}</h3>
                  <p>Total Products</p>
                  <small class="text-danger">${stats.out_of_stock} out of stock</small>
                </div>
              </div>
            `;
          }
        })
        .catch(error => {
          console.error('Error loading dashboard stats:', error);
        });
      }
      
      // Load sales chart data
      function loadSalesChart() {
        const range = document.getElementById('revenueTimeRange').value;
        
        fetch('dashboard_actions.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `action=get_sales_data&range=${range}`
        })
        .then(response => response.json())
        .then(data => {
          if(data.success) {
            renderSalesChart(data.sales_data);
          }
        })
        .catch(error => {
          console.error('Error loading sales data:', error);
        });
      }
      
      // Render sales chart (using Chart.js - you'll need to include the library)
      function renderSalesChart(data) {
        const ctx = document.getElementById('revenueChart');
        
        // Clear previous chart if exists
        if(ctx.chart) {
          ctx.chart.destroy();
        }
        
        // Prepare data
        const labels = data.map(item => item.date);
        const revenueData = data.map(item => item.revenue);
        
        ctx.chart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: labels,
            datasets: [{
              label: 'Revenue',
              data: revenueData,
              backgroundColor: 'rgba(52, 152, 219, 0.2)',
              borderColor: 'rgba(52, 152, 219, 1)',
              borderWidth: 2,
              tension: 0.4,
              fill: true
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  callback: function(value) {
                    return '$' + value;
                  }
                }
              }
            }
          }
        });
      }
      
      // Load category chart data
      function loadCategoryChart() {
        fetch('dashboard_actions.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'action=get_category_data'
        })
        .then(response => response.json())
        .then(data => {
          if(data.success) {
            renderCategoryChart(data.category_data);
          }
        })
        .catch(error => {
          console.error('Error loading category data:', error);
        });
      }
      
      // Render category chart
      function renderCategoryChart(data) {
        const ctx = document.getElementById('categoryChart');
        
        // Clear previous chart if exists
        if(ctx.chart) {
          ctx.chart.destroy();
        }
        
        // Prepare data
        const labels = data.map(item => item.category);
        const itemsSold = data.map(item => item.items_sold);
        
        ctx.chart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              label: 'Items Sold',
              data: itemsSold,
              backgroundColor: 'rgba(46, 204, 113, 0.7)',
              borderColor: 'rgba(46, 204, 113, 1)',
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              y: {
                beginAtZero: true
              }
            }
          }
        });
      }
      
      // Load recent orders
      function loadRecentOrders() {
        fetch('dashboard_actions.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'action=get_stats'
        })
        .then(response => response.json())
        .then(data => {
          if(data.success) {
            const ordersBody = document.getElementById('recentOrdersBody');
            ordersBody.innerHTML = '';
            
            data.recent_orders.forEach(order => {
              const row = document.createElement('tr');
              
              row.innerHTML = `
                <td>${order.order_number}</td>
                <td>${order.customer_name}</td>
                <td>${order.order_date}</td>
                <td>$${order.total_amount.toFixed(2)}</td>
                <td>
                  <span class="badge ${
                    order.status === 'completed' ? 'badge-success' : 
                    order.status === 'processing' ? 'badge-primary' : 
                    order.status === 'cancelled' ? 'badge-danger' : 'badge-warning'
                  }">
                    ${order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                  </span>
                </td>
                <td>
                  <button class="btn btn-sm btn-primary view-order-btn" data-order-id="${order.order_number}">
                    <i class="fas fa-eye"></i> View
                  </button>
                </td>
              `;
              
              ordersBody.appendChild(row);
            });
            
            // Re-attach event listeners to view buttons
            document.querySelectorAll('.view-order-btn').forEach(button => {
              button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                viewOrderDetails(orderId);
              });
            });
          }
        })
        .catch(error => {
          console.error('Error loading recent orders:', error);
        });
      }
      
      // View order details
      function viewOrderDetails(orderId) {
        fetch('order_actions.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `action=get_order&order_id=${orderId}`
        })
        .then(response => response.json())
        .then(data => {
          if(data.success) {
            const order = data.order;
            const modal = document.getElementById('orderDetailModal');
            
            // Populate modal
            document.getElementById('modalOrderId').textContent = order.order_number;
            document.getElementById('modalCustomerName').textContent = `${order.first_name} ${order.last_name}`;
            document.getElementById('modalOrderDate').textContent = order.order_date;
            document.getElementById('modalCustomerEmail').textContent = order.email;
            document.getElementById('modalCustomerPhone').textContent = order.phone;
            document.getElementById('modalShippingAddress').textContent = order.shipping_address;
            document.getElementById('modalOrderStatus').value = order.status;
            
            // Populate order items
            const orderItemsContainer = document.getElementById('modalOrderItems');
            orderItemsContainer.innerHTML = '';
            
            order.items.forEach(item => {
              const row = document.createElement('tr');
              row.innerHTML = `
                <td>${item.product_name}</td>
                <td>$${item.unit_price.toFixed(2)}</td>
                <td>${item.quantity}</td>
                <td>$${item.total_price.toFixed(2)}</td>
              `;
              orderItemsContainer.appendChild(row);
            });
            
            // Set totals
            document.getElementById('modalSubtotal').textContent = `$${order.total_amount.toFixed(2)}`;
            document.getElementById('modalShipping').textContent = '$0.00'; // You would calculate this
            document.getElementById('modalTax').textContent = '$0.00'; // You would calculate this
            document.getElementById('modalTotal').textContent = `$${order.total_amount.toFixed(2)}`;
            
            // Show modal
            modal.style.display = 'flex';
          }
        })
        .catch(error => {
          console.error('Error loading order details:', error);
        });
      }
      
      // Logout function
      function logout() {
        fetch('auth.php?action=logout', {
          method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
          if(data.success) {
            window.location.href = 'login.php';
          }
        })
        .catch(error => {
          console.error('Error logging out:', error);
        });
      }
      
      // Event listeners for chart controls
      document.getElementById('revenueTimeRange').addEventListener('change', function() {
        loadSalesChart();
      });
    </script>
    
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </body>
</html>