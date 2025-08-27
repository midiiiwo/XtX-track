<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact Us - Athena Security</title>
    <link rel="stylesheet" href="styles.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap"
      rel="stylesheet"
    />
  </head>
  <body>
    <nav class="navbar">
      <div class="nav-container">
        <div class="nav-logo">
          <img alt="gg" src="./ll.png" />
          <!-- <span class="logo-text">Athena</span> -->
        </div>
        <ul class="nav-menu">
          <li><a href="index.php" class="nav-link">Home</a></li>
          <li><a href="about.php" class="nav-link">About</a></li>
          <li><a href="services.php" class="nav-link">Services</a></li>
          <li><a href="track.php" class="nav-link">Track</a></li>
          <li><a href="contact.php" class="nav-link active">Contact</a></li>
        </ul>
        <div class="nav-toggle">
          <span></span>
          <span></span>
          <span></span>
        </div>
      </div>
    </nav>

    <main>
      <!-- Page Header -->
      <section class="page-header">
        <div class="container">
          <h1>Contact Us</h1>
          <p>Get in touch with our experts for inquiries, quotes, or support</p>
        </div>
      </section>

      <!-- Contact Content -->
      <section class="contact-content">
        <div class="container">
          <div class="contact-grid">
            <!-- Contact Form -->
            <div class="contact-form-section">
              <h2>Send us a Message</h2>
              <form class="contact-form" onsubmit="submitContactForm(event)">
                <div class="form-group">
                  <label for="name">Full Name *</label>
                  <input type="text" id="name" name="name" required />
                </div>

                <div class="form-group">
                  <label for="email">Email Address *</label>
                  <input type="email" id="email" name="email" required />
                </div>

                <div class="form-group">
                  <label for="phone">Phone Number</label>
                  <input type="tel" id="phone" name="phone" />
                </div>

                <div class="form-group">
                  <label for="subject">Subject *</label>
                  <select id="subject" name="subject" required>
                    <option value="">Select a subject</option>
                    <option value="general">General Inquiry</option>
                    <option value="quote">Request a Quote</option>
                    <option value="tracking">Tracking Support</option>
                    <option value="storage">Storage Services</option>
                    <option value="shipping">Shipping Services</option>
                    <option value="technical">Technical Support</option>
                    <option value="partnership">
                      Partnership Opportunities
                    </option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="message">Message *</label>
                  <textarea
                    id="message"
                    name="message"
                    rows="6"
                    required
                    placeholder="Please provide details about your inquiry..."
                  ></textarea>
                </div>

                <div class="form-group checkbox-group">
                  <input type="checkbox" id="newsletter" name="newsletter" />
                  <label for="newsletter"
                    >Subscribe to our newsletter for updates and industry
                    insights</label
                  >
                </div>

                <button type="submit" class="btn btn-primary">
                  Send Message
                </button>
              </form>
            </div>

            <!-- Contact Information -->
            <!-- Contact Information -->
            <div class="contact-info-section">
              <h2>Get in Touch</h2>

              <div class="contact-info">
                <div class="info-item">
                  <div class="info-icon">📍</div>
                  <div class="info-content">
                    <h3>Dubai Office</h3>
                    <p>
                      Amana Exchange<br />
                      Aleppo, Faisal St., Second 282/4<br />
                      Eltal, Ground Floor
                    </p>
                  </div>
                </div>

                <div class="info-item">
                  <div class="info-icon">📞</div>
                  <div class="info-content">
                    <h3>Phone</h3>
                    <p>+233537386484</p>
                  </div>
                </div>

                <div class="info-item">
                  <div class="info-icon">✉️</div>
                  <div class="info-content">
                    <h3>Email</h3>
                    <p>dubai@athenaalamanavaultservices.org</p>
                  </div>
                </div>

                <div class="info-item">
                  <div class="info-icon">📍</div>
                  <div class="info-content">
                    <h3>Switzerland Office</h3>
                    <p>
                      Bahnhofstrasse 45<br />
                      Zürich, Switzerland
                    </p>
                  </div>
                </div>

                <div class="info-item">
                  <div class="info-icon">📞</div>
                  <div class="info-content">
                    <h3>Phone</h3>
                    <p>+41 44 1234567</p>
                  </div>
                </div>

                <div class="info-item">
                  <div class="info-icon">✉️</div>
                  <div class="info-content">
                    <h3>Email</h3>
                    <p>switzerland@athenaalamanavaultservices.org</p>
                  </div>
                </div>
              </div>

              <!-- Live Chat -->
              <div class="live-chat">
                <h3>💬 Live Chat Available</h3>
                <p>Chat with our support team during business hours</p>
                <button class="btn btn-secondary" onclick="openLiveChat()">
                  Start Live Chat
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Map Section -->
      <section class="map-section">
        <div class="container">
          <h2>Our Locations</h2>

          <!-- Dubai Office -->
          <div class="map-container">
            <!-- <h3>Dubai Office</h3> -->
            <iframe
              src="https://www.google.com/maps?q=Amana%20Exchange,%20Aleppo,%20Faisal%20St.,%20Second%20282/4,%20Eltal,%20Dubai&output=embed"
              width="100%"
              height="300"
              frameborder="0"
              style="border: 0"
              allowfullscreen=""
              aria-hidden="false"
              tabindex="0"
            ></iframe>
          </div>

          <!-- Switzerland Office -->
          <div class="map-container" style="margin-top: 40px">
            <!-- <h3>Switzerland Office</h3> -->
            <iframe
              src="https://www.google.com/maps?q=Bahnhofstrasse%2045,%20Zürich,%20Switzerland&output=embed"
              width="100%"
              height="300"
              frameborder="0"
              style="border: 0"
              allowfullscreen=""
              aria-hidden="false"
              tabindex="0"
            ></iframe>
          </div>
        </div>
      </section>

      <!-- Success Message -->
      <div class="success-message" id="successMessage" style="display: none">
        <div class="success-content">
          <div class="success-icon">✅</div>
          <h3>Message Sent Successfully!</h3>
          <p>
            Thank you for contacting us. We'll get back to you within 24 hours.
          </p>
          <button onclick="closeSuccessMessage()" class="btn btn-secondary">
            Close
          </button>
        </div>
      </div>
    </main>

    <footer class="footer">
      <div class="container">
        <div class="footer-content">
          <div class="footer-section">
            <div class="footer-logo">
              <span class="logo-text">Athena Security</span>
            </div>
            <p>
              Dubai Freeport - Supergate Facility Mohammed bin Rashid Aerospace
              Hub Dubai South, UAE
            </p>
          </div>
          <div class="footer-section">
            <h4>Services</h4>
            <ul>
              <li><a href="services.php">Secure Storage</a></li>
              <li><a href="services.php">Global Shipping</a></li>
              <li><a href="services.php">Inventory Management</a></li>
              <li><a href="services.php">Custom Insurance</a></li>
            </ul>
          </div>
          <div class="footer-section">
            <h4>Company</h4>
            <ul>
              <li><a href="about.php">About Us</a></li>
              <li><a href="contact.php">Contact</a></li>
              <li><a href="track.php">Track Shipment</a></li>
              <li>
                <a href="#" onclick="window.open('admin/login.php', '_blank')"
                  >Admin Portal</a
                >
              </li>
            </ul>
          </div>
          <div class="footer-section">
            <h4>Legal</h4>
            <ul>
              <li><a href="#">Terms & Conditions</a></li>
              <li><a href="#">Privacy Policy</a></li>
              <li><a href="#">Security Standards</a></li>
              <li><a href="#">Compliance</a></li>
            </ul>
          </div>
        </div>
        <div class="footer-bottom">
          <p>
            &copy; 2025 Athena Security. All rights reserved. | Licensed and
            Insured by Lloyd's of London
          </p>
        </div>
      </div>
    </footer>

    <script src="script.js"></script>
  </body>
</html>
