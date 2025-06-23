document.addEventListener("DOMContentLoaded", function () {
  // Hamburger menu
  const navToggle = document.getElementById("navToggle");
  const navLinks = document.getElementById("navLinks");
  if (navToggle && navLinks) {
    navToggle.addEventListener("click", () => {
      navLinks.classList.toggle("active");
    });
  }

  // Category click scroll
  const catItems = document.querySelectorAll(".cat-item, .category-link");
  catItems.forEach((item) => {
    item.addEventListener("click", function (e) {
      const hash = this.getAttribute("href") || this.dataset.category;
      if (hash && hash.startsWith("#category-")) {
        e.preventDefault();
        catItems.forEach((i) => i.classList.remove("active"));
        this.classList.add("active");
        const section = document.querySelector(hash);
        if (section)
          section.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    });
  });

  // Helper: product name to file slug in /products/
  function productNameToFile(name) {
    return (
      "products/" +
      name
        .replace(/[^a-zA-Z0-9 ]/g, "")
        .replace(/\s+/g, "-")
        .trim() +
      ".html"
    );
  }

  // Product card data (sample)
  const featuredProducts = [
    {
      name: "Flagship Ultrabook 2025",
      price: 1399,
      rating: 4.9,
      badge: "HOT",
      badgeClass: "hot",
      img: "images/8BHQFHBNDTS7hwEbVbnMGZ.jpg",
      desc: "Ultra-thin. All-day battery. 2025's best for work & travel.",
    },
    {
      name: "Smartphone Z Pro",
      price: 899,
      rating: 4.8,
      badge: "NEW",
      badgeClass: "new",
      img: "images/umidigi_x_pro00.webp",
      desc: "Flagship Android phone. AMOLED, 5G, AI Camera.",
    },
    {
      name: "Wireless Earbuds 2",
      price: 199,
      rating: 4.7,
      badge: "FEATURED",
      badgeClass: "featured",
      img: "images/Wireless Earbuds 2.webp",
      desc: "Crystal clear sound, deep bass, 36h battery.",
    },
    {
      name: "Smart Fitness Band",
      price: 79,
      rating: 4.6,
      badge: "DEAL",
      badgeClass: "deal",
      img: "images/1-2.jpg",
      desc: "Track workouts, sleep, heart rate. Stylish & light.",
    },

    {
      name: "Wrist Watches",
      price: 79,
      rating: 4.6,
      badge: "DEAL",
      badgeClass: "deal",
      img: "images/711NXCmUfbL._SX679_.jpg",
      desc: "Watches for Men Analog Quartz Chronograph Watch Moon Phase.",
    },

    {
      name: "Wazdorf Sealing",
      price: 79,
      rating: 4.6,
      badge: "DEAL",
      badgeClass: "deal",
      img: "images/51hf5q7g13L._SX569_.jpg",
      desc: "Wazdorf Sealing Clip - Portable Mini Sealing Machine",
    },
  ];
  const bestDeals = [
    {
      name: "Cetaphil Paraben",
      price: 1129,
      rating: 4.7,
      badge: "DEAL",
      badgeClass: "deal",
      img: "images/51O+J5jnXcL._SY450_.jpg ",
      desc: "Cetaphil Paraben, Sulphate-Free Gentle Skin.",
    },
    {
      name: "Noise Cancelling Headphones",
      price: 249,
      rating: 4.8,
      badge: "HOT",
      badgeClass: "hot",
      img: "https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=420&q=80",
      desc: "Premium ANC, wireless, 30h battery.",
    },
    {
      name: "Smartwatch X3",
      price: 299,
      rating: 4.8,
      badge: "NEW",
      badgeClass: "new",
      img: "https://images.unsplash.com/photo-1516574187841-cb9cc2ca948b?auto=format&fit=crop&w=420&q=80",
      desc: "Track health, calls, notifications.",
    },
    {
      name: "Gear Classic 20L Small Faux Leather",
      price: 49,
      rating: 4.5,
      badge: "DEAL",
      badgeClass: "deal",
      img: "images/71Ae6bbA+0L._SX679_.jpg",
      desc: "Gear Classic 20L Small Faux Leather Water Resistant Anti Theft 3",
    },
    {
      name: "Digital Scales",
      price: 1129,
      rating: 4.7,
      badge: "DEAL",
      badgeClass: "deal",
      img: "images/71775fRr+gL._SX466_.jpg",
      desc: "Atom 10Kg Kitchen Weight Machine 6 Months Warranty,",
    },
    {
      name: "Presto! Garbage Bags",
      price: 1129,
      rating: 4.7,
      badge: "DEAL",
      badgeClass: "deal",
      img: "images/71T2M3bz77L._SX679_.jpg",
      desc: "Amazon Brand - Presto! Garbage Bags | Medium | 180 Count.",
    },
  ];

  function renderProductGrid(products, gridId) {
    const grid = document.getElementById(gridId);
    if (!grid) return;
    grid.innerHTML = "";
    products.forEach((prod) => {
      const card = document.createElement("div");
      card.className = "product-news-card";
      card.tabIndex = 0;
      // Card click goes to product page
      card.onclick = (e) => {
        // Only allow navigation if not clicking a button
        if (
          e.target.classList.contains("btn-sm") ||
          e.target.classList.contains("btn-buy")
        )
          return;
        window.location.href = productNameToFile(prod.name);
      };
      card.innerHTML = `
        <div class="news-img">
          <img src="${prod.img}" alt="${prod.name}">
          <span class="badge ${prod.badgeClass}">${prod.badge}</span>
        </div>
        <div class="news-content">
          <h3>${prod.name}</h3>
          <div class="news-meta">
            <span class="product-price">$${prod.price}</span>
          </div>
          <div class="news-actions-row">
            <span class="news-desc">${prod.desc}</span>
            <button class="btn-sm btn-add-cart" title="Add to cart">Add to Cart</button>
            <button class="btn-sm btn-buy" title="Buy now">Buy Now</button>
            <span class="product-rating"><i class="fas fa-star"></i> ${prod.rating}</span>
          </div>
        </div>
      `;
      // Image fallback
      card.querySelector(".news-img img").onerror = function () {
        this.src = "images/placeholder.png";
      };
      // Add to cart button
      card.querySelector(".btn-add-cart").onclick = function (e) {
        e.stopPropagation();
        addToCart(prod);
        showToast("Added to cart!");
      };
      // Buy now button
      card.querySelector(".btn-buy").onclick = function (e) {
        e.stopPropagation();
        addToCart(prod);
        window.location.href = "cart.html";
      };
      grid.appendChild(card);
    });
  }
  renderProductGrid(featuredProducts, "featuredProductsGrid");
  renderProductGrid(bestDeals, "bestDealsGrid");

  function addToCart(product) {
    let cart = [];
    try {
      cart = JSON.parse(localStorage.getItem("cart")) || [];
    } catch {}
    const idx = cart.findIndex((item) => item.name === product.name);
    if (idx > -1) cart[idx].qty += 1;
    else cart.push({ ...product, qty: 1 });
    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartCounter();
  }
  function updateCartCounter() {
    let cart = [];
    try {
      cart = JSON.parse(localStorage.getItem("cart")) || [];
    } catch {}
    let total = cart.reduce((sum, item) => sum + (item.qty || 1), 0);
    const cartCounter = document.getElementById("cartCounter");
    if (cartCounter) cartCounter.textContent = total;
    const cartMobile = document.getElementById("cartCounterMobile");
    if (cartMobile) cartMobile.textContent = total;
  }
  updateCartCounter();

  function showToast(msg) {
    const t = document.getElementById("toast");
    if (!t) return;
    t.textContent = msg;
    t.style.display = "block";
    setTimeout(() => (t.style.display = "none"), 1800);
  }

  // Social links animation
  document.querySelectorAll(".social-links a.social").forEach((link) => {
    link.addEventListener("click", function () {
      document
        .querySelectorAll(".social-links a.social")
        .forEach((l) => l.classList.remove("active"));
      this.classList.add("active");
      setTimeout(() => this.classList.remove("active"), 1200);
    });
  });

  // Navbar search
  const navSearchForm = document.getElementById("navSearchForm");
  if (navSearchForm) {
    navSearchForm.onsubmit = function (e) {
      e.preventDefault();
      const q = document.getElementById("navSearchInput").value.trim();
      if (!q) return;
      showToast(`Searching for "${q}"...`);
    };
  }

  // Mobile bottom nav actions
  const mbnavProfile = document.getElementById("mbnavProfile");
  if (mbnavProfile) {
    mbnavProfile.onclick = (e) => {
      e.preventDefault();
      window.location.href = "profile.html";
    };
  }
});
