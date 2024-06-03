// Function to search and display products based on the user's input
function searchAndDisplayProducts(query) {
    // Filter the products array from cart.js based on the user's search query
    const searchedProducts = products.filter(product => {
        // Convert product title and user's query to lowercase for case-insensitive search
        const productTitle = product.title.toLowerCase();
        const lowerCaseQuery = query.toLowerCase();
        
        // Check if the product title contains the search query
        return productTitle.includes(lowerCaseQuery);
    });

    // Get the root container
    const root = document.getElementById('root');

    // Display the searched products in the root container
    root.innerHTML = searchedProducts.map(product => {
        const { image, title, price } = product;
        return (
            `<div class="box">
                <div class="img-box">
                    <img src="${image}" alt="${title}">
                </div>
                <div class="left">
                    <p>${title}</p>
                    <h2>${price}</h2>
                    <button class="button-style">SEE PREVIEW<span class="span-effect"></span></button>
                </div>
            </div>`
        );
    }).join('');

    // Reapply hover effects to the new buttons
    document.querySelectorAll('.button-style').forEach(button => {
        button.addEventListener('mouseenter', function() {
            const span = button.querySelector('.span-effect');
            span.style.width = '100%';
        });

        button.addEventListener('mouseleave', function() {
            const span = button.querySelector('.span-effect');
            span.style.width = '0%';
        });
    });
}

// Event listener for search input
document.getElementById('search-item').addEventListener('input', function() {
    // Get the search query from the input field
    const query = this.value;

    // Call the function to search and display products
    searchAndDisplayProducts(query);
});

// Initial display of all products
// Display all products when the page loads by calling the function with an empty query
searchAndDisplayProducts('');
