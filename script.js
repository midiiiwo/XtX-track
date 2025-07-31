document.addEventListener("DOMContentLoaded", function () {
  const navToggle = document.querySelector(".nav-toggle");
  const navMenu = document.querySelector(".nav-menu");
  if (navToggle && navMenu) {
    navToggle.addEventListener("click", function () {
      navMenu.classList.toggle("active");
    });
  }
  const navLinks = document.querySelectorAll(".nav-link");
  navLinks.forEach((link) => {
    link.addEventListener("click", function () {
      navMenu.classList.remove("active");
    });
  });
  initTestimonialsSlider();
  const anchorLinks = document.querySelectorAll('a[href^="#"]');
  anchorLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    });
  });
});
function initTestimonialsSlider() {
  const testimonials = document.querySelectorAll(".testimonial");
  const dots = document.querySelectorAll(".dot");
  let currentSlide = 0;
  if (testimonials.length === 0) return;
  function showSlide(index) {
    testimonials.forEach((testimonial) => {
      testimonial.classList.remove("active");
    });
    dots.forEach((dot) => {
      dot.classList.remove("active");
    });
    if (testimonials[index]) {
      testimonials[index].classList.add("active");
    }
    if (dots[index]) {
      dots[index].classList.add("active");
    }
  }
  function nextSlide() {
    currentSlide = (currentSlide + 1) % testimonials.length;
    showSlide(currentSlide);
  }
  setInterval(nextSlide, 5000);
  dots.forEach((dot, index) => {
    dot.addEventListener("click", function () {
      currentSlide = index;
      showSlide(currentSlide);
    });
  });
}
const sampleTrackingData = {
  "GS-2025-001234": {
    trackingNumber: "GS-2025-001234",
    description: "24k Gold Bars (5kg)",
    origin: "Accra Vault Facility, Ghana",
    destination: "London Secure Vault, UK",
    status: "In Transit",
    eta: "July 27, 2025 - 9:00 AM GMT",
    value: "$325,000",
    service: "Express International Shipping",
    client: "Marcus Chen",
  },
  "GS-2025-005678": {
    trackingNumber: "GS-2025-005678",
    description: "Gold Coins Collection (500 pieces)",
    origin: "Dubai Secure Storage, UAE",
    destination: "New York Vault, USA",
    status: "Preparing for Shipment",
    eta: "July 30, 2025 - 2:00 PM EST",
    value: "$180,000",
    service: "Standard International Shipping",
    client: "Sarah Williams",
  },
  "GS-2025-009876": {
    trackingNumber: "GS-2025-009876",
    description: "Investment Gold Bars (10kg)",
    origin: "Singapore Vault, Singapore",
    destination: "Zurich Storage Facility, Switzerland",
    status: "Delivered",
    eta: "Delivered on July 20, 2025",
    value: "$650,000",
    service: "Premium Secure Transport",
    client: "James Rodriguez",
  },
};
async function trackShipment() {
  const trackingNumber = document.getElementById("trackingNumber").value.trim();
  if (!trackingNumber) {
    alert("Please enter a tracking number");
    return;
  }

  try {
    // Try PHP API first
    const response = await fetch(
      `api/shipments.php?path=track&tracking=${encodeURIComponent(
        trackingNumber
      )}`
    );
    const result = await response.json();

    if (result.success && result.shipment) {
      const trackingData = convertShipmentToTrackingData(result.shipment);
      displayTrackingResults(trackingData);
      return;
    }
  } catch (error) {
    console.warn("PHP API not available, trying fallback:", error);
  }

  // Fallback to JavaScript backend if available
  if (window.backend) {
    while (!window.backend) {
      await new Promise((resolve) => setTimeout(resolve, 100));
    }

    const shipment = window.backend.getShipmentByTracking(trackingNumber);
    if (shipment) {
      const trackingData = convertShipmentToTrackingData(shipment);
      displayTrackingResults(trackingData);
      return;
    }
  }

  // Final fallback to sample data
  const trackingData = sampleTrackingData[trackingNumber];
  if (trackingData) {
    displayTrackingResults(trackingData);
  } else {
    displayTrackingError();
  }
}

function convertShipmentToTrackingData(shipment) {
  const statusMap = {
    storage: "In Storage",
    preparing: "Preparing for Shipment",
    transit: "In Transit",
    delivered: "Delivered",
  };

  return {
    trackingNumber: shipment.trackingNumber,
    description: shipment.description,
    origin: shipment.origin,
    destination: shipment.destination,
    status: statusMap[shipment.status] || shipment.status,
    eta:
      shipment.status === "delivered"
        ? "Delivered"
        : new Date(shipment.eta).toLocaleDateString(),
    value: `${shipment.value.toLocaleString()}`,
    service: shipment.serviceType,
    client: shipment.client,
    timeline: shipment.timeline,
  };
}
function demoTrack(trackingNumber) {
  document.getElementById("trackingNumber").value = trackingNumber;
  trackShipment();
}
function displayTrackingResults(data) {
  const errorSection = document.getElementById("trackingError");
  if (errorSection) {
    errorSection.style.display = "none";
  }
  document.getElementById("orderTrackingNumber").textContent =
    data.trackingNumber;
  document.getElementById("orderDescription").textContent = data.description;
  document.getElementById("orderOrigin").textContent = data.origin;
  document.getElementById("orderDestination").textContent = data.destination;
  document.getElementById("orderETA").textContent = data.eta;
  document.getElementById("orderValue").textContent = data.value;
  document.getElementById("orderService").textContent = data.service;
  const statusElement = document.getElementById("orderStatus");
  statusElement.textContent = data.status;
  statusElement.className = "status-badge";
  switch (data.status) {
    case "In Transit":
      statusElement.classList.add("in-transit");
      break;
    case "Preparing for Shipment":
      statusElement.classList.add("preparing");
      break;
    case "Delivered":
      statusElement.classList.add("delivered");
      break;
    default:
      statusElement.classList.add("storage");
  }
  updateTimeline(data.status);
  const resultsSection = document.getElementById("trackingResults");
  if (resultsSection) {
    resultsSection.style.display = "block";
    resultsSection.scrollIntoView({ behavior: "smooth" });
  }
}
function updateTimeline(currentStatus) {
  const timelineItems = document.querySelectorAll(".timeline-item");
  timelineItems.forEach((item) => {
    item.classList.remove("current", "completed", "pending");
    item.classList.add("pending");
  });
  let foundCurrent = false;
  timelineItems.forEach((item) => {
    const status = item.getAttribute("data-status");
    if (!foundCurrent) {
      if (
        status === "storage" ||
        (status === "preparing" &&
          ["Preparing for Shipment", "In Transit", "Delivered"].includes(
            currentStatus
          )) ||
        (status === "transit" &&
          ["In Transit", "Delivered"].includes(currentStatus)) ||
        (status === "delivered" && currentStatus === "Delivered")
      ) {
        if (
          status === "storage" ||
          (status === "preparing" &&
            currentStatus !== "Preparing for Shipment") ||
          (status === "transit" && currentStatus !== "In Transit") ||
          (status === "delivered" && currentStatus === "Delivered")
        ) {
          item.classList.remove("pending");
          item.classList.add("completed");
        } else {
          item.classList.remove("pending");
          item.classList.add("current");
          foundCurrent = true;
        }
      }
    }
  });
}
function displayTrackingError() {
  const resultsSection = document.getElementById("trackingResults");
  if (resultsSection) {
    resultsSection.style.display = "none";
  }
  const errorSection = document.getElementById("trackingError");
  if (errorSection) {
    errorSection.style.display = "block";
    errorSection.scrollIntoView({ behavior: "smooth" });
  }
}
function submitContactForm(event) {
  event.preventDefault();
  const formData = new FormData(event.target);
  const name = formData.get("name");
  const email = formData.get("email");
  const subject = formData.get("subject");
  const message = formData.get("message");
  setTimeout(() => {
    document.getElementById("successMessage").style.display = "flex";
    event.target.reset();
  }, 1000);
  console.log("Form submitted:", { name, email, subject, message });
}
function closeSuccessMessage() {
  document.getElementById("successMessage").style.display = "none";
}
function openLiveChat() {
  alert(
    "Our live chat support is temporarily unavailable. For urgent inquiries please send us an email at dubai@athenaalamanavaultservices.org or switzerland@athenaalamanavaultservices.org"
  );
}
document.addEventListener("DOMContentLoaded", function () {
  const quickTrackForm = document.querySelector(".quick-track .track-form");
  if (quickTrackForm) {
    const trackButton = quickTrackForm.querySelector(".btn");
    const trackInput = quickTrackForm.querySelector(".track-input");
    if (trackButton && trackInput) {
      trackButton.addEventListener("click", function () {
        const trackingNumber = trackInput.value.trim();
        if (trackingNumber) {
          window.location.href = `track.html?tracking=${encodeURIComponent(
            trackingNumber
          )}`;
        } else {
          alert("Please enter a tracking number");
        }
      });
      trackInput.addEventListener("keypress", function (e) {
        if (e.key === "Enter") {
          trackButton.click();
        }
      });
    }
  }
  if (window.location.pathname.includes("track.html")) {
    const urlParams = new URLSearchParams(window.location.search);
    const trackingNumber = urlParams.get("tracking");
    if (trackingNumber) {
      const trackingInput = document.getElementById("trackingNumber");
      if (trackingInput) {
        trackingInput.value = trackingNumber;
        setTimeout(() => {
          trackShipment();
        }, 500);
      }
    }
  }
});
window.addEventListener("scroll", function () {
  const navbar = document.querySelector(".navbar");
  if (navbar) {
    if (window.scrollY > 100) {
      navbar.style.background = "rgba(26, 26, 26, 0.98)";
    } else {
      navbar.style.background = "rgba(26, 26, 26, 0.95)";
    }
  }
});
function initScrollAnimations() {
  const animatedElements = document.querySelectorAll(
    ".feature-card, .service-card, .team-member, .standard-item"
  );
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = "1";
          entry.target.style.transform = "translateY(0)";
        }
      });
    },
    {
      threshold: 0.1,
      rootMargin: "0px 0px -50px 0px",
    }
  );
  animatedElements.forEach((el) => {
    el.style.opacity = "0";
    el.style.transform = "translateY(30px)";
    el.style.transition = "opacity 0.6s ease, transform 0.6s ease";
    observer.observe(el);
  });
}
document.addEventListener("DOMContentLoaded", function () {
  initScrollAnimations();
});
function formatCurrency(amount) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
  }).format(amount);
}
function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}
