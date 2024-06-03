// Function to handle "Add to Cart" button click
function handleAddToCart() {
    // Get product information from the modal
    const itemName = document.getElementById("modalName").textContent;
    const itemPrice = document.getElementById("modalPrice").textContent;
    const itemImage = document.getElementById("modalImage").src;

    // Add product to cart
    addToCart(itemName, itemPrice, itemImage);
}

// Add event listener for "Add to Cart" button
const addToCartBtn = document.getElementById("addToCartBtn");
addToCartBtn.addEventListener("click", handleAddToCart);

// Function to add item information to the cart
function addToCart(name, price, image) {
    // Find existing cart item with the same name
    const existingCartItem = document.querySelector(`.cart-item[data-name="${name}"]`);

    if (existingCartItem) {
        // Increment quantity if item already exists in cart
        const quantityElement = existingCartItem.querySelector('.cart-item-quantity');
        let quantity = parseInt(quantityElement.textContent);
        quantity++;
        quantityElement.textContent = quantity;
    } else {
        // Create a new cart item element
        const cartItem = document.createElement('div');
        cartItem.classList.add('cart-item');
        cartItem.setAttribute('data-name', name);
        
        // Construct the HTML for the cart item
        cartItem.innerHTML = `
            <div class="cart-item-details">
                <img class="cart-item-image" src="${image}" alt="${name}">
            </div>
            <div class="cart-item-name">${name}</div>
            <div class="cart-item-price">${price}</div>
            <div class="cart-item-quantity">1</div>
            <i class="remove-item-btn">X</i>
        `;
        
        // Append the cart item to the cart menu
        const cartMenu = document.querySelector('.cart-menu');
        cartMenu.appendChild(cartItem);
    }

    // Update total price
    updateTotal();
}

// Function to remove item from cart
function removeFromCart(icon) {
    const cartItem = icon.closest('.cart-item');
    cartItem.remove();

    // Update total price
    updateTotal();
}

// Event delegation to handle remove icon clicks
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('remove-item-btn')) {
        removeFromCart(event.target);
    }
});

// Function to update the total price of items in the cart
function updateTotal() {
    let total = 0;
    const cartItems = document.querySelectorAll('.cart-item');

    cartItems.forEach(cartItem => {
        const priceElement = cartItem.querySelector('.cart-item-price');
        const quantityElement = cartItem.querySelector('.cart-item-quantity');
        const price = parseFloat(priceElement.textContent.replace('$', ''));
        const quantity = parseInt(quantityElement.textContent);
        total += price * quantity;
    });

    // Update total price in the footer
    const cartTotalElement = document.querySelector('.cart-total');
    cartTotalElement.textContent = `$${total.toFixed(2)}`;
}






