// XtX Track File-Based Backend System
// Handles all data management using JSON files for cross-PC compatibility

class XtXBackend {
  constructor() {
    this.data = {
      admins: {},
      shipments: {},
      trackingCounter: 1000,
      lastSaved: null,
    };
    this.initializeData();
  }

  // Initialize default data
  initializeData() {
    // Initialize with super admin
    this.data.admins = {
      midiiwo: {
        id: "admin_001",
        username: "midiiwo",
        password: this.hashPassword("kwansa"),
        role: "supaadmin",
        createdAt: new Date().toISOString(),
        createdBy: "system",
      },
    };

    this.data.shipments = {};
    this.data.trackingCounter = 1000;
    this.data.lastSaved = new Date().toISOString();
  }

  // Simple password hashing (for local use)
  hashPassword(password) {
    // Simple hash for local storage - not cryptographically secure
    let hash = 0;
    for (let i = 0; i < password.length; i++) {
      const char = password.charCodeAt(i);
      hash = (hash << 5) - hash + char;
      hash = hash & hash; // Convert to 32bit integer
    }
    return hash.toString();
  }

  // File Operations
  saveDataToFile() {
    this.data.lastSaved = new Date().toISOString();
    const dataToSave = {
      ...this.data,
      exportDate: new Date().toISOString(),
      version: "1.0",
    };

    const blob = new Blob([JSON.stringify(dataToSave, null, 2)], {
      type: "application/json",
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `xtx-data-${new Date().toISOString().split("T")[0]}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }

  loadDataFromFile(fileContent) {
    try {
      const loadedData = JSON.parse(fileContent);

      // Validate data structure
      if (
        loadedData.admins &&
        loadedData.shipments &&
        loadedData.trackingCounter
      ) {
        this.data = {
          admins: loadedData.admins,
          shipments: loadedData.shipments,
          trackingCounter: loadedData.trackingCounter,
          lastSaved: new Date().toISOString(),
        };
        return true;
      } else {
        throw new Error("Invalid data file format");
      }
    } catch (error) {
      throw new Error("Failed to load data file: " + error.message);
    }
  }

  // Admin Management
  authenticateAdmin(username, password) {
    const admin = this.data.admins[username];

    if (admin && admin.password === this.hashPassword(password)) {
      return {
        id: admin.id,
        username: admin.username,
        role: admin.role,
      };
    }
    return null;
  }

  addAdmin(adminData, creatorRole) {
    if (creatorRole !== "supaadmin") {
      throw new Error("Only super admin can create new admins");
    }

    if (this.data.admins[adminData.username]) {
      throw new Error("Username already exists");
    }

    const newAdmin = {
      id: "admin_" + Date.now(),
      username: adminData.username,
      password: this.hashPassword(adminData.password),
      role: "admin",
      createdAt: new Date().toISOString(),
      createdBy: adminData.createdBy,
    };

    this.data.admins[adminData.username] = newAdmin;
    return newAdmin;
  }

  getAllAdmins() {
    return Object.values(this.data.admins).map((admin) => ({
      id: admin.id,
      username: admin.username,
      role: admin.role,
      createdAt: admin.createdAt,
      createdBy: admin.createdBy,
    }));
  }

  deleteAdmin(username, currentUserRole) {
    if (currentUserRole !== "supaadmin") {
      throw new Error("Only super admin can delete admins");
    }

    if (username === "midiiwo") {
      throw new Error("Cannot delete super admin account");
    }

    delete this.data.admins[username];
    return true;
  }

  // Tracking ID Generation
  generateTrackingNumber() {
    const newCounter = this.data.trackingCounter + 1;
    this.data.trackingCounter = newCounter;

    const year = new Date().getFullYear();
    const paddedNumber = newCounter.toString().padStart(6, "0");
    return `XTX-${year}-${paddedNumber}`;
  }

  // Shipment Management
  createShipment(shipmentData) {
    const trackingNumber = this.generateTrackingNumber();

    const newShipment = {
      trackingNumber: trackingNumber,
      client: shipmentData.client,
      description: shipmentData.description,
      origin: shipmentData.origin,
      destination: shipmentData.destination,
      status: shipmentData.status || "storage",
      value: parseFloat(shipmentData.value) || 0,
      eta: shipmentData.eta,
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
      statusHistory: [
        {
          status: shipmentData.status || "storage",
          location: shipmentData.origin || "",
          notes: "Shipment created",
          timestamp: new Date().toISOString(),
        },
      ],
    };

    this.data.shipments[trackingNumber] = newShipment;
    return newShipment;
  }

  getShipment(trackingNumber) {
    return this.data.shipments[trackingNumber] || null;
  }

  getAllShipments() {
    return Object.values(this.data.shipments);
  }

  updateShipment(trackingNumber, updateData) {
    const shipment = this.data.shipments[trackingNumber];

    if (!shipment) {
      throw new Error("Shipment not found");
    }

    // Update shipment data
    const updatedShipment = {
      ...shipment,
      ...updateData,
      updatedAt: new Date().toISOString(),
    };

    this.data.shipments[trackingNumber] = updatedShipment;
    return updatedShipment;
  }

  updateShipmentStatus(trackingNumber, statusData) {
    const shipment = this.data.shipments[trackingNumber];

    if (!shipment) {
      throw new Error("Shipment not found");
    }

    // Add to status history
    const statusUpdate = {
      status: statusData.status,
      location: statusData.location || "",
      notes: statusData.notes || "",
      timestamp: new Date().toISOString(),
    };

    shipment.status = statusData.status;
    shipment.statusHistory.push(statusUpdate);
    shipment.updatedAt = new Date().toISOString();

    this.data.shipments[trackingNumber] = shipment;
    return shipment;
  }

  deleteShipment(trackingNumber) {
    if (!this.data.shipments[trackingNumber]) {
      throw new Error("Shipment not found");
    }

    delete this.data.shipments[trackingNumber];
    return true;
  }

  // Search and Filter
  searchShipments(query, filterStatus = "") {
    const allShipments = this.getAllShipments();
    let filteredShipments = allShipments;

    // Filter by status if provided
    if (filterStatus) {
      filteredShipments = filteredShipments.filter(
        (shipment) => shipment.status === filterStatus
      );
    }

    // Search by query if provided
    if (query) {
      const searchTerm = query.toLowerCase();
      filteredShipments = filteredShipments.filter(
        (shipment) =>
          shipment.trackingNumber.toLowerCase().includes(searchTerm) ||
          shipment.client.toLowerCase().includes(searchTerm) ||
          shipment.description.toLowerCase().includes(searchTerm) ||
          shipment.origin.toLowerCase().includes(searchTerm) ||
          shipment.destination.toLowerCase().includes(searchTerm)
      );
    }

    return filteredShipments;
  }

  // Statistics
  getShipmentStats() {
    const shipments = this.getAllShipments();
    const stats = {
      total: shipments.length,
      storage: 0,
      preparing: 0,
      transit: 0,
      delivered: 0,
      totalValue: 0,
    };

    shipments.forEach((shipment) => {
      stats.totalValue += shipment.value;

      switch (shipment.status) {
        case "storage":
          stats.storage++;
          break;
        case "preparing":
          stats.preparing++;
          break;
        case "transit":
          stats.transit++;
          break;
        case "delivered":
          stats.delivered++;
          break;
      }
    });

    return stats;
  }

  // Data Export/Import
  exportData() {
    return {
      ...this.data,
      exportDate: new Date().toISOString(),
      version: "1.0",
    };
  }

  importData(data) {
    if (data.admins && data.shipments && data.trackingCounter) {
      this.data = {
        admins: data.admins,
        shipments: data.shipments,
        trackingCounter: data.trackingCounter,
        lastSaved: new Date().toISOString(),
      };
      return true;
    } else {
      throw new Error("Invalid data format");
    }
  }

  // Clear all data (for testing)
  clearAllData() {
    this.initializeData();
  }
}

// Initialize backend
const xtxBackend = new XtXBackend();
