// Show cart items or empty
document.addEventListener("DOMContentLoaded", function () {
  let cart = JSON.parse(localStorage.getItem("cart") || "[]");
  let cartItemsDiv = document.getElementById("cartItems");
  if (cartItemsDiv) {
    if (!cart.length) {
      cartItemsDiv.innerHTML = "<p>Your cart is empty.</p>";
    } else {
      cartItemsDiv.innerHTML = cart
        .map(
          (item) =>
            `<div style="margin-bottom:8px;">
          <img src="${item.img}" alt="${
              item.name
            }" style="width:40px;height:40px;vertical-align:middle;border-radius:5px;margin-right:8px;">
          <b>${item.name}</b> &times; ${item.qty} &mdash; $${
              item.price * item.qty
            }
        </div>`
        )
        .join("");
    }
  }
});

// Place Order logic for the whole cart
function placeOrder() {
  // 1. Validate cart and address
  let cart = JSON.parse(localStorage.getItem("cart") || "[]");
  if (!cart.length) return alert("Your cart is empty!");
  let addresses = JSON.parse(localStorage.getItem("userAddresses") || "[]");
  let selectedIdx = parseInt(
    localStorage.getItem("selectedAddressIdx") || "0",
    10
  );
  if (!addresses[selectedIdx])
    return alert("Please select or add a delivery address.");
  let payment =
    document.querySelector('input[name="payment"]:checked')?.value || "cod";

  // 2. Calculate total
  let total = cart.reduce((sum, item) => sum + item.price * item.qty, 0);

  // 3. Save the order (append to orders array)
  let orders = JSON.parse(localStorage.getItem("orders") || "[]");
  let orderObj = {
    id: orders.length + 1,
    cart: cart,
    total: total,
    address: addresses[selectedIdx],
    paymentMethod: payment,
    time: new Date().toLocaleString(),
  };
  orders.push(orderObj);
  localStorage.setItem("orders", JSON.stringify(orders));

  // 4. Clear cart
  localStorage.removeItem("cart");

  // 5. Redirect to My Orders page
  window.location.href = "myorders.html";
}
