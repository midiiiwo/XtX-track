<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login - Athena Security</title>
    <link rel="stylesheet" href="../styles.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap"
      rel="stylesheet"
    />
  </head>
  <body class="admin-body">
    <div class="admin-login">
      <div class="login-container">
        <div class="login-header">
          <div class="admin-logo">
            <img alt="gg" src="./ll.png" />
            <!-- <span class="logo-text">Athena</span> -->
          </div>
          <h1>Admin Portal</h1>
          <p>Secure access for authorized personnel only</p>
        </div>

        <form class="login-form" onsubmit="adminLogin(event)">
          <div class="form-group">
            <label for="username">Username</label>
            <input
              type="text"
              id="username"
              name="username"
              required
              placeholder="Enter your username"
            />
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input
              type="password"
              id="password"
              name="password"
              required
              placeholder="Enter your password"
            />
          </div>

          <div class="form-group checkbox-group">
            <input type="checkbox" id="remember" name="remember" />
            <label for="remember">Remember me</label>
          </div>

          <button type="submit" class="btn btn-primary btn-full">Login</button>

          <div class="login-help">
            <a href="#" onclick="showForgotPassword()">Forgot Password?</a>
          </div>
        </form>

        <!-- <div class="demo-credentials">
          <div class="demo-info">
            <h3>Demo Credentials</h3>
            <p>For demonstration purposes, use:</p>
            <div class="demo-creds">
              <p><strong>Username:</strong> admin</p>
              <p><strong>Password:</strong> goldsecure2025</p>
            </div>
          </div>
        </div> -->

        <div class="login-footer">
          <p>© 2025 Athena Security. All rights reserved.</p>
          <p><a href="../index.html">← Back to Main Site</a></p>
        </div>
      </div>
    </div>

    <div class="modal" id="forgotPasswordModal" style="display: none">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Reset Password</h2>
          <button class="modal-close" onclick="closeForgotPassword()">
            &times;
          </button>
        </div>
        <div class="modal-body">
          <p>
            Enter your email address to receive password reset instructions:
          </p>
          <form onsubmit="resetPassword(event)">
            <div class="form-group">
              <label for="resetEmail">Email Address</label>
              <input
                type="email"
                id="resetEmail"
                required
                placeholder="Enter your email"
              />
            </div>
            <button type="submit" class="btn btn-primary">
              Send Reset Link
            </button>
          </form>
        </div>
      </div>
    </div>

    <script src="../script.js"></script>
    <script>
      async function adminLogin(event) {
        event.preventDefault();
        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;

        try {
          const response = await fetch("../api/admin.php?path=login", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({ username, password }),
          });

          const result = await response.json();

          if (result.success) {
            window.location.href = "dashboard.html";
            return;
          } else {
            alert("Invalid credentials. Please try again.");
          }
        } catch (error) {
          console.error("Login error:", error);
          alert("Login failed. Please check your connection and try again.");
        }
      }

      function showForgotPassword() {
        document.getElementById("forgotPasswordModal").style.display = "flex";
      }

      function closeForgotPassword() {
        document.getElementById("forgotPasswordModal").style.display = "none";
      }

      function resetPassword(event) {
        event.preventDefault();
        alert("Password reset link sent to your email address.");
        closeForgotPassword();
      }
    </script>
  </body>
</html>
