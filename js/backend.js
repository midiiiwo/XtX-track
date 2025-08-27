/**
 * Local Backend System for XtX Track
 * Handles all data operations using localStorage and JSON files
 */

class LocalBackend {
  constructor() {
    this.storagePrefix = "xtx_track_";
    this.dataFiles = {
      admins: "db/admins.json",
      shipments: "db/shipments.json",
      settings: "db/settings.json",
    };
    this.cache = {};
    this.init();
  }

  /**
   * Initialize the backend system
   */
  async init() {
    try {
      // Load initial data from JSON files if not in localStorage
      await this.loadInitialData();
    } catch (error) {
      console.error("Backend initialization failed:", error);
    }
  }

  /**
   * Load initial data from JSON files
   */
  async loadInitialData() {
    for (const [key, filepath] of Object.entries(this.dataFiles)) {
      const storageKey = this.storagePrefix + key;

      // Check if data exists in localStorage
      if (!localStorage.getItem(storageKey)) {
        try {
          const response = await fetch(filepath);
          if (response.ok) {
            const data = await response.json();
            localStorage.setItem(storageKey, JSON.stringify(data));
            this.cache[key] = data;
          }
        } catch (error) {
          console.warn(`Could not load ${filepath}:`, error);
          // Initialize with empty structure if file doesn't exist
          this.initializeEmptyData(key);
        }
      } else {
        this.cache[key] = JSON.parse(localStorage.getItem(storageKey));
      }
    }
  }

  /**
   * Initialize empty data structure
   */
  initializeEmptyData(type) {
    let emptyData;
    switch (type) {
      case "admins":
        emptyData = {
          admins: [
            {
              id: "1",
              username: "1Qaz2Wsx",
              password: "12321232111",
              role: "supaadmin",
              createdAt: new Date().toISOString(),
              lastLogin: null,
              active: true,
              permissions: [
                "manage_shipments",
                "manage_admins",
                "view_reports",
                "export_data",
                "system_settings",
              ],
            },
          ],
          lastUpdated: new Date().toISOString(),
        };
        break;
      case "shipments":
        emptyData = {
          shipments: [],
          nextTrackingId: 1,
          lastUpdated: new Date().toISOString(),
        };
        break;
      case "settings":
        emptyData = {
          app: {
            name: "Athena Security Tracking System",
            version: "1.0.0",
            trackingIdPrefix: "GS",
            trackingIdFormat: "GS-YYYY-XXXXXX",
          },
          statuses: [
            {
              key: "storage",
              label: "In Storage",
              description: "Item is securely stored in vault",
              color: "#6b7280",
            },
            {
              key: "preparing",
              label: "Preparing for Shipment",
              description: "Documentation and customs clearance in progress",
              color: "#f59e0b",
            },
            {
              key: "transit",
              label: "In Transit",
              description: "Item is being transported to destination",
              color: "#3b82f6",
            },
            {
              key: "delivered",
              label: "Delivered",
              description: "Item has been successfully delivered",
              color: "#10b981",
            },
          ],
          currencies: ["USD", "EUR", "GBP", "CHF", "SGD", "AED"],
          serviceTypes: [
            "Express International Shipping",
            "Standard International Shipping",
            "Premium Secure Transport",
            "Economy Shipping",
            "Same Day Delivery",
          ],
          lastUpdated: new Date().toISOString(),
        };
        break;
    }
    this.saveData(type, emptyData);
  }

  /**
   * Save data to localStorage
   */
  saveData(type, data) {
    const storageKey = this.storagePrefix + type;
    data.lastUpdated = new Date().toISOString();
    localStorage.setItem(storageKey, JSON.stringify(data));
    this.cache[type] = data;
  }

  /**
   * Get data from cache or localStorage
   */
  getData(type) {
    if (this.cache[type]) {
      return this.cache[type];
    }
    const storageKey = this.storagePrefix + type;
    const data = localStorage.getItem(storageKey);
    if (data) {
      this.cache[type] = JSON.parse(data);
      return this.cache[type];
    }
    return null;
  }

  // ====================
  // ADMIN OPERATIONS
  // ====================

  /**
   * Authenticate admin user
   */
  authenticateAdmin(username, password) {
    const adminData = this.getData("admins");
    const admin = adminData.admins.find(
      (a) => a.username === username && a.password === password && a.active
    );

    if (admin) {
      // Update last login
      admin.lastLogin = new Date().toISOString();
      this.saveData("admins", adminData);

      // Store session
      const session = {
        id: admin.id,
        username: admin.username,
        role: admin.role,
        permissions: admin.permissions,
        loginTime: new Date().toISOString(),
      };
      localStorage.setItem(
        this.storagePrefix + "session",
        JSON.stringify(session)
      );
      return session;
    }
    return null;
  }

  /**
   * Get current admin session
   */
  getCurrentSession() {
    const session = localStorage.getItem(this.storagePrefix + "session");
    return session ? JSON.parse(session) : null;
  }

  /**
   * Logout admin
   */
  logout() {
    localStorage.removeItem(this.storagePrefix + "session");
  }

  /**
   * Add new admin (supaadmin only)
   */
  addAdmin(adminData, currentSession) {
    if (!currentSession || currentSession.role !== "supaadmin") {
      throw new Error("Unauthorized: Only super admin can add new admins");
    }

    const data = this.getData("admins");

    // Check if username already exists
    if (data.admins.find((a) => a.username === adminData.username)) {
      throw new Error("Username already exists");
    }

    const newAdmin = {
      id: String(data.admins.length + 1),
      username: adminData.username,
      password: adminData.password,
      role: adminData.role || "admin",
      createdAt: new Date().toISOString(),
      lastLogin: null,
      active: true,
      permissions:
        adminData.role === "supaadmin"
          ? [
              "manage_shipments",
              "manage_admins",
              "view_reports",
              "export_data",
              "system_settings",
            ]
          : ["manage_shipments", "view_reports"],
    };

    data.admins.push(newAdmin);
    this.saveData("admins", data);
    return newAdmin;
  }

  /**
   * Get all admins (supaadmin only)
   */
  getAllAdmins(currentSession) {
    if (!currentSession || currentSession.role !== "supaadmin") {
      throw new Error("Unauthorized: Only super admin can view all admins");
    }
    return this.getData("admins").admins;
  }

  /**
   * Delete admin (supaadmin only)
   */
  deleteAdmin(adminId, currentSession) {
    if (!currentSession || currentSession.role !== "supaadmin") {
      throw new Error("Unauthorized: Only super admin can delete admins");
    }

    const data = this.getData("admins");
    const adminIndex = data.admins.findIndex((a) => a.id === adminId);

    if (adminIndex === -1) {
      throw new Error("Admin not found");
    }

    // Prevent deleting the super admin
    if (data.admins[adminIndex].role === "supaadmin") {
      throw new Error("Cannot delete super admin");
    }

    data.admins.splice(adminIndex, 1);
    this.saveData("admins", data);
    return true;
  }

  // ====================
  // SHIPMENT OPERATIONS
  // ====================

  /**
   * Generate unique tracking ID
   */
  generateTrackingId() {
    const data = this.getData("shipments");
    const year = new Date().getFullYear();
    const nextId = data.nextTrackingId || 1;
    const paddedId = String(nextId).padStart(6, "0");

    data.nextTrackingId = nextId + 1;
    this.saveData("shipments", data);

    return `GS-${year}-${paddedId}`;
  }

  /**
   * Create new shipment
   */
  createShipment(shipmentData, currentSession) {
    if (
      !currentSession ||
      !currentSession.permissions.includes("manage_shipments")
    ) {
      throw new Error("Unauthorized: No permission to manage shipments");
    }

    const data = this.getData("shipments");
    const trackingNumber = this.generateTrackingId();

    const newShipment = {
      id: String(data.shipments.length + 1),
      trackingNumber: trackingNumber,
      client: shipmentData.client,
      description: shipmentData.description,
      origin: shipmentData.origin,
      destination: shipmentData.destination,
      status: shipmentData.status || "storage",
      value: parseFloat(shipmentData.value),
      currency: shipmentData.currency || "USD",
      serviceType: shipmentData.serviceType,
      eta: shipmentData.eta,
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
      timeline: [
        {
          status: shipmentData.status || "storage",
          location: shipmentData.origin,
          timestamp: new Date().toISOString(),
          description: "Shipment created",
          notes: "Initial shipment entry",
        },
      ],
    };

    data.shipments.push(newShipment);
    this.saveData("shipments", data);
    return newShipment;
  }

  /**
   * Get all shipments
   */
  getAllShipments() {
    return this.getData("shipments").shipments;
  }

  /**
   * Get shipment by tracking number
   */
  getShipmentByTracking(trackingNumber) {
    const data = this.getData("shipments");
    return data.shipments.find((s) => s.trackingNumber === trackingNumber);
  }

  /**
   * Update shipment
   */
  updateShipment(trackingNumber, updateData, currentSession) {
    if (
      !currentSession ||
      !currentSession.permissions.includes("manage_shipments")
    ) {
      throw new Error("Unauthorized: No permission to manage shipments");
    }

    const data = this.getData("shipments");
    const shipmentIndex = data.shipments.findIndex(
      (s) => s.trackingNumber === trackingNumber
    );

    if (shipmentIndex === -1) {
      throw new Error("Shipment not found");
    }

    const shipment = data.shipments[shipmentIndex];

    // Update fields
    Object.keys(updateData).forEach((key) => {
      if (key !== "id" && key !== "trackingNumber" && key !== "createdAt") {
        shipment[key] = updateData[key];
      }
    });

    shipment.updatedAt = new Date().toISOString();
    this.saveData("shipments", data);
    return shipment;
  }

  /**
   * Update shipment status
   */
  updateShipmentStatus(trackingNumber, statusData, currentSession) {
    if (
      !currentSession ||
      !currentSession.permissions.includes("manage_shipments")
    ) {
      throw new Error("Unauthorized: No permission to manage shipments");
    }

    const data = this.getData("shipments");
    const shipmentIndex = data.shipments.findIndex(
      (s) => s.trackingNumber === trackingNumber
    );

    if (shipmentIndex === -1) {
      throw new Error("Shipment not found");
    }

    const shipment = data.shipments[shipmentIndex];
    shipment.status = statusData.status;
    shipment.updatedAt = new Date().toISOString();

    // Add to timeline
    shipment.timeline.push({
      status: statusData.status,
      location: statusData.location || "Unknown",
      timestamp: new Date().toISOString(),
      description: statusData.description || "Status updated",
      notes: statusData.notes || "",
    });

    this.saveData("shipments", data);
    return shipment;
  }

  /**
   * Delete shipment
   */
  deleteShipment(trackingNumber, currentSession) {
    if (
      !currentSession ||
      !currentSession.permissions.includes("manage_shipments")
    ) {
      throw new Error("Unauthorized: No permission to manage shipments");
    }

    const data = this.getData("shipments");
    const shipmentIndex = data.shipments.findIndex(
      (s) => s.trackingNumber === trackingNumber
    );

    if (shipmentIndex === -1) {
      throw new Error("Shipment not found");
    }

    data.shipments.splice(shipmentIndex, 1);
    this.saveData("shipments", data);
    return true;
  }

  /**
   * Search shipments
   */
  searchShipments(query, filters = {}) {
    const shipments = this.getAllShipments();
    let filtered = shipments;

    // Text search
    if (query) {
      const searchLower = query.toLowerCase();
      filtered = filtered.filter(
        (s) =>
          s.trackingNumber.toLowerCase().includes(searchLower) ||
          s.client.toLowerCase().includes(searchLower) ||
          s.description.toLowerCase().includes(searchLower) ||
          s.origin.toLowerCase().includes(searchLower) ||
          s.destination.toLowerCase().includes(searchLower)
      );
    }

    // Status filter
    if (filters.status) {
      filtered = filtered.filter((s) => s.status === filters.status);
    }

    // Date range filter
    if (filters.startDate) {
      filtered = filtered.filter(
        (s) => new Date(s.createdAt) >= new Date(filters.startDate)
      );
    }
    if (filters.endDate) {
      filtered = filtered.filter(
        (s) => new Date(s.createdAt) <= new Date(filters.endDate)
      );
    }

    return filtered;
  }

  // ====================
  // UTILITY OPERATIONS
  // ====================

  /**
   * Export data
   */
  exportData(type = "all") {
    const exportData = {};

    if (type === "all" || type === "shipments") {
      exportData.shipments = this.getData("shipments");
    }
    if (type === "all" || type === "admins") {
      exportData.admins = this.getData("admins");
    }
    if (type === "all" || type === "settings") {
      exportData.settings = this.getData("settings");
    }

    exportData.exportedAt = new Date().toISOString();
    return exportData;
  }

  /**
   * Get dashboard statistics
   */
  getDashboardStats() {
    const shipments = this.getAllShipments();
    const total = shipments.length;
    const inTransit = shipments.filter((s) => s.status === "transit").length;
    const delivered = shipments.filter((s) => s.status === "delivered").length;
    const totalValue = shipments.reduce((sum, s) => sum + (s.value || 0), 0);

    return {
      totalShipments: total,
      inTransit: inTransit,
      delivered: delivered,
      totalValue: totalValue,
      statuses: {
        storage: shipments.filter((s) => s.status === "storage").length,
        preparing: shipments.filter((s) => s.status === "preparing").length,
        transit: inTransit,
        delivered: delivered,
      },
    };
  }

  /**
   * Get settings
   */
  getSettings() {
    return this.getData("settings");
  }
}

// Initialize global backend instance
window.backend = new LocalBackend();
