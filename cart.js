const products = [
    {
        id: 1,
        image: 'img/Apple/samsungfeat.png',
        title: 'Apple iPhone 15 Pro',
        price: 'From ₱',
        description: '',
        nature: {
            type: 'Apple'
        }
    },
    {
        id: 2,
        image: 'img/Apple/iphone15blue.png',
        title: 'Apple iphone 15',
        price: 'From ₱',
        description: '',
        nature: {
            type: 'Apple'
        }
    },
    {
        id: 3,
        image: 'img/Apple/iphone14propurple.png',
        title: 'Apple iphone 14 Pro',
        price: 'From ₱',
        nature: {
            type: 'Apple'
        }
    },
    {
        id: 4,
        image: 'img/Apple/iphone14ared.png',
        title: 'Apple iPhone 14',
        price: 'From ₱',
        nature: {
            type: 'Apple'
        }
    },
    {
        id: 5,
        image: 'img/Apple/iphone13proasilver.png',
        title: 'Apple iPhone 13 Pro',
        price: 'From ₱',
        nature: {
            type: 'Apple'
        }
    },
    {
        id: 6,
        image: 'img/Apple/iphone13apink.png',
        title: 'Apple iPhone 13',
        price: 'From ₱',
        nature: {
            type: 'Apple'
        }
    },
    {
        id: 7,
        image: 'img/samsung/samsungs24ultrapurple.png',
        title: 'Samsung s24 Ultra',
        price: 'From ₱',
        nature: {
            type: 'Samsung'
        }
    },
    {
        id: 8,
        image: 'img/samsung/samsungzflip5borapurple.png',
        title: 'Samsung Z Flip 5',
        price: 'From ₱',
        nature: {
            type: 'Samsung'
        }
    },
    {
        id: 9,
        image: 'img/samsung/samsungzfoldphantomblack.png',
        title: 'Samsung Z Fold',
        price: 'From ₱',
        nature: {
            type: 'Samsung'
        }
    },
    {
        id: 10,
        image: 'img/huawei/huaweip60prorococopearl.png',
        title: 'Huawei p60 Pro',
        price: 'From ₱',
        nature: {
            type: 'Huawei'
        }
    },
    {
        id: 11,
        image: 'img/huawei/huaweimatex3green.png',
        title: 'Huawei Mate x3',
        price: '$$$',
        nature: {
            type: 'Huawei'
        }
    },
    {
        id: 12,
        image: 'img/oppo/oppofindn3gold.png',
        title: 'Oppo Find n3',
        price: '$$$',
        nature: {
            type: 'Oppo'
        }
    },
    {
        id: 13,
        image: 'img/oppo/oppofindx6progreen.png',
        title: 'Oppo Find x6 Pro',
        price: '$$$',
        nature: {
            type: 'Oppo'
        }
    },
    {
        id: 14,
        image: 'img/oppo/oppofindx7ultrabrown.png',
        title: 'Oppo Find x7 Ultra',
        price: '$$$',
        nature: {
            type: 'Oppo'
        }
    }, 
    {
        id: 15,
        image: 'img/vivo/vivov29majesticred.png',
        title: 'Vivo v29',
        price: '$$$',
        nature: {
            type: 'Vivo'
        }
    },
    {
        id: 16,
        image: 'img/vivo/vivox100startrailblue.png',
        title: 'Vivo x100',
        price: '$$$',
        nature: {
            type: 'Vivo'
        }
    }
]
const categories = [...new Set(products.map((item) =>
    {return item}))];

let cart = document.getElementById('root')
cart.innerHTML = categories.map((item) =>
{
    var {image, title, price} = item;
    return(
        `<div class="box">
            <div class="img-box">
                <img src=${image}></img>
            </div>
            <div class="left">
                <p>${title}</p>
                <h2>${price}</h2>
                <button class="button-style">SEE PREVIEW<span class="span-effect"></button>
            </div>
        </div>`
    )
}).join('')

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











let filter = document.querySelector('.dropdown');
let productFilter = products;
function showProduct(productFilter){
    
}
console.log(products);




