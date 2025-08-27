<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Admins - Athena Security</title>
    <link rel="stylesheet" href="../styles.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap"
      rel="stylesheet"
    />
  </head>
  <body class="dashboard-body">
    <!-- Admin Header -->
    <header class="admin-header">
      <div class="admin-nav">
        <div class="admin-logo">
          <img alt="gg" src="../ll.png" />
        </div>
        <div class="admin-user">
          <span>Admin Management</span>
          <button
            onclick="goBackToDashboard()"
            class="btn btn-secondary btn-sm"
          >
            Back to Dashboard
          </button>
          <button onclick="adminLogout()" class="btn btn-secondary btn-sm">
            Logout
          </button>
        </div>
      </div>
    </header>

    <!-- Admin Management Content -->
    <main class="dashboard-main">
      <div class="dashboard-container">
        <!-- Page Header -->
        <section class="page-header">
          <h1>Admin Management</h1>
          <p>Manage administrator accounts and permissions</p>
        </section>

        <!-- Quick Actions -->
        <section class="quick-actions">
          <h2>Quick Actions</h2>
          <div class="action-buttons">
            <button onclick="showAddAdmin()" class="btn btn-primary">
              Add New Admin
            </button>
            <button onclick="exportAdmins()" class="btn btn-secondary">
              Export Admin List
            </button>
          </div>
        </section>

        <!-- Admins Management -->
        <section class="admins-management">
          <div class="section-header">
            <h2>Administrator Accounts</h2>
            <div class="section-controls">
              <input
                type="text"
                placeholder="Search admins..."
                class="search-input"
                oninput="filterAdmins(this.value)"
              />
            </div>
          </div>

          <div class="admins-table">
            <table>
              <thead>
                <tr>
                  <th>Username</th>
                  <th>Role</th>
                  <th>Created</th>
                  <th>Last Login</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="adminsTableBody">
                <!-- Admin data will be populated here -->
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </main>

    <!-- Add Admin Modal -->
    <div class="modal" id="addAdminModal" style="display: none">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Add New Administrator</h2>
          <button class="modal-close" onclick="closeAddAdminModal()">
            &times;
          </button>
        </div>
        <div class="modal-body">
          <form onsubmit="saveNewAdmin(event)">
            <div class="form-group">
              <label for="newAdminUsername">Username</label>
              <input
                type="text"
                id="newAdminUsername"
                required
                minlength="3"
                maxlength="20"
                pattern="[a-zA-Z0-9_]+"
                title="Username must contain only letters, numbers, and underscores"
              />
            </div>
            <div class="form-group">
              <label for="newAdminPassword">Password</label>
              <input
                type="password"
                id="newAdminPassword"
                required
                minlength="6"
              />
            </div>
            <div class="form-group">
              <label for="newAdminRole">Role</label>
              <select id="newAdminRole" required>
                <option value="admin">Regular Admin</option>
                <option value="supaadmin">Super Admin</option>
              </select>
            </div>
            <div class="form-group">
              <label>
                <input type="checkbox" id="confirmAddAdmin" required />
                I confirm that I want to create this administrator account
              </label>
            </div>
            <div class="modal-actions">
              <button
                type="button"
                onclick="closeAddAdminModal()"
                class="btn btn-secondary"
              >
                Cancel
              </button>
              <button type="submit" class="btn btn-primary">
                Create Admin
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div class="modal" id="confirmDeleteModal" style="display: none">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Confirm Delete</h2>
          <button class="modal-close" onclick="closeConfirmDeleteModal()">
            &times;
          </button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete this administrator account?</p>
          <p>
            <strong>Username: <span id="deleteAdminUsername"></span></strong>
          </p>
          <p class="warning-text">This action cannot be undone.</p>
          <div class="modal-actions">
            <button
              type="button"
              onclick="closeConfirmDeleteModal()"
              class="btn btn-secondary"
            >
              Cancel
            </button>
            <button
              type="button"
              onclick="confirmDeleteAdmin()"
              class="btn btn-danger"
            >
              Delete Admin
            </button>
          </div>
        </div>
      </div>
    </div>

    <script src="../script.js"></script>
    <script>
      let currentSession = null;
      let allAdmins = [];
      let adminToDelete = null;

      // Check authentication on page load
      document.addEventListener("DOMContentLoaded", async function () {
        await checkAuth();
        await loadAdminsData();
      });

      async function checkAuth() {
        try {
          const response = await fetch("../api/admin.php?path=session");
          const result = await response.json();

          if (result.success && result.session) {
            currentSession = result.session;

            // Only supaadmin can access this page
            if (currentSession.role !== "supaadmin") {
              alert("Unauthorized: Only super admin can manage administrators");
              window.location.href = "dashboard.php";
              return;
            }
            return;
          }
        } catch (error) {
          console.error("Authentication check failed:", error);
        }

        // Redirect to login if authentication failed
        alert("Authentication failed. Please log in again.");
        window.location.href = "login.php";
      }

      async function loadAdminsData() {
        try {
          const response = await fetch("../api/admin.php?path=all");
          const result = await response.json();

          if (result.success) {
            allAdmins = result.admins;
            displayAdmins(allAdmins);
          } else {
            alert(
              "Error loading administrator data: " +
                (result.message || "Unknown error")
            );
          }
        } catch (error) {
          console.error("Error loading admins data:", error);
          alert("Error loading administrator data: " + error.message);
        }
      }

      function displayAdmins(admins) {
        const tbody = document.getElementById("adminsTableBody");
        tbody.innerHTML = "";

        admins.forEach((admin) => {
          const row = createAdminRow(admin);
          tbody.appendChild(row);
        });
      }

      function createAdminRow(admin) {
        const row = document.createElement("tr");
        const createdDate = new Date(admin.createdAt).toLocaleDateString();
        const lastLogin = admin.lastLogin
          ? new Date(admin.lastLogin).toLocaleDateString()
          : "Never";
        const status = admin.active ? "Active" : "Inactive";
        const statusClass = admin.active ? "status-active" : "status-inactive";

        row.innerHTML = `
          <td><strong>${admin.username}</strong></td>
          <td>
            <span class="role-badge ${admin.role}">${
          admin.role === "supaadmin" ? "Super Admin" : "Admin"
        }</span>
          </td>
          <td>${createdDate}</td>
          <td>${lastLogin}</td>
          <td><span class="status-badge ${statusClass}">${status}</span></td>
          <td>
            ${
              admin.role !== "supaadmin"
                ? `
              <button onclick="deleteAdmin('${admin.id}', '${admin.username}')" class="btn-action btn-danger">Delete</button>
            `
                : '<span class="text-muted">Protected</span>'
            }
          </td>
        `;
        return row;
      }

      function filterAdmins(searchTerm) {
        const filtered = allAdmins.filter(
          (admin) =>
            admin.username.toLowerCase().includes(searchTerm.toLowerCase()) ||
            admin.role.toLowerCase().includes(searchTerm.toLowerCase())
        );
        displayAdmins(filtered);
      }

      function showAddAdmin() {
        document.getElementById("addAdminModal").style.display = "flex";
      }

      function closeAddAdminModal() {
        document.getElementById("addAdminModal").style.display = "none";
        document.getElementById("addAdminModal").querySelector("form").reset();
      }

      async function saveNewAdmin(event) {
        event.preventDefault();

        try {
          const adminData = {
            username: document.getElementById("newAdminUsername").value,
            password: document.getElementById("newAdminPassword").value,
            role: document.getElementById("newAdminRole").value,
          };

          const response = await fetch("../api/admin.php?path=create", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify(adminData),
          });

          const result = await response.json();
          if (result.success) {
            alert(
              `Administrator "${result.admin.username}" created successfully!`
            );
            closeAddAdminModal();
            loadAdminsData();
          } else {
            alert(
              "Error creating admin: " + (result.message || "Unknown error")
            );
          }
        } catch (error) {
          console.error("Error creating admin:", error);
          alert("Error creating admin: " + error.message);
        }
      }

      function deleteAdmin(adminId, username) {
        adminToDelete = adminId;
        document.getElementById("deleteAdminUsername").textContent = username;
        document.getElementById("confirmDeleteModal").style.display = "flex";
      }

      function closeConfirmDeleteModal() {
        document.getElementById("confirmDeleteModal").style.display = "none";
        adminToDelete = null;
      }

      async function confirmDeleteAdmin() {
        if (!adminToDelete) return;

        try {
          const response = await fetch(`../api/admin.php?id=${adminToDelete}`, {
            method: "DELETE",
          });

          const result = await response.json();
          if (result.success) {
            alert("Administrator deleted successfully!");
            closeConfirmDeleteModal();
            loadAdminsData();
          } else {
            alert(
              "Error deleting admin: " + (result.message || "Unknown error")
            );
          }
        } catch (error) {
          console.error("Error deleting admin:", error);
          alert("Error deleting admin: " + error.message);
        }
      }

      function exportAdmins() {
        try {
          const data = {
            admins: allAdmins,
            exportedAt: new Date().toISOString(),
            exportedBy: currentSession.username,
          };

          const blob = new Blob([JSON.stringify(data, null, 2)], {
            type: "application/json",
          });
          const url = URL.createObjectURL(blob);
          const a = document.createElement("a");
          a.href = url;
          a.download = `admins-export-${
            new Date().toISOString().split("T")[0]
          }.json`;
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
          URL.revokeObjectURL(url);
        } catch (error) {
          alert("Error exporting data: " + error.message);
        }
      }

      function goBackToDashboard() {
        window.close();
        // If window.close() doesn't work (some browsers), redirect
        setTimeout(() => {
          window.location.href = "dashboard.php";
        }, 100);
      }

      async function adminLogout() {
        try {
          await fetch("../api/admin.php?path=logout", { method: "POST" });
        } catch (error) {
          console.warn("Logout API call failed:", error);
        }

        window.location.href = "login.php";
      }
    </script>
  </body>
</html>
