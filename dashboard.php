<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Dashboard</title>
   <style>
       @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600&display=swap');

       :root{
           --green:#27ae60;
           --black:#333;
           --white:#fff;
           --bg-color:#eee;
           --box-shadow:0 .5rem 1rem rgba(0,0,0,.1);
           --border:.1rem solid var(--black);
       }

       *{
           font-family: 'Poppins', sans-serif;
           margin:0; padding:0;
           box-sizing: border-box;
           outline: none; border:none;
           text-decoration: none;
           text-transform: none;
       }

       html{
           font-size: 62.5%;
           overflow-x: hidden;
       }

       body{
           display: flex;
       }

       .sidebar{
           width: 250px;
           height: 100vh;
           background: var(--black);
           color: var(--white);
           position: fixed;
           top: 0;
           left: 0;
           display: flex;
           flex-direction: column;
           align-items: center;
           padding: 2rem 0;
       }

       .sidebar nav ul{
           list-style: none;
           width: 100%;
           padding: 0;
       }

       .sidebar nav ul li{
           width: 100%;
           padding: 1rem 0;
           text-align: center;
       }

       .sidebar nav ul li a{
           color: var(--white);
           font-size: 1.6rem;
           display: block;
           width: 100%;
           text-decoration: none;
           transition: background 0.3s;
       }

       .sidebar .btn{
           background: var(--black);
           color: var(--white);
           border: none;
           width: 100%;
           padding: 1rem;
           font-size: 1.6rem;
           text-align: center;
           cursor: pointer;
           transition: background 0.3s;
       }

       .sidebar .btn:hover{
           background: var(--green);
       }

       .main-content{
           margin-left: 250px;
           padding: 2rem;
           width: calc(100% - 250px);
       }

       .btn{
           display: block;
           width: 100%;
           cursor: pointer;
           border-radius: .5rem;
           margin-top: 1rem;
           font-size: 1.6rem;
           padding: 1rem 3rem;
           background: var(--green);
           color: var(--white);
           text-align: center;
       }

       .btn:hover{
           background: var(--black);
       }

       .message{
           display: block;
           padding: 3rem 3rem;
           font-size: 2rem;
           color: var(--black);
           margin-bottom: 10rem;
           text-align: row;    
       }

       .container{
           max-width: 1200px;
           padding: 20rem;
           margin: 0 auto;
           margin-right: 2rem;
           display: flex;
           justify-content: center;
           align-items: center;
           height: 100vh;
           flex-direction: column;
       }

       #about-section {
           display: flex;
           flex-direction: column;
           align-items: center;
           justify-content: center; /* Center vertically */
           text-align: center;
           padding: 0rem;
           border-radius: .5rem;
       }

       #welcome-button {
           background-color: var(--green);
           color: var(--black);
           box-shadow: var(--box-shadow);
           padding: 1rem 2rem;
           font-size: 1.6rem;
           border: none;
           border-radius: .5rem;
           cursor: pointer;
           transition: background 0.3s;
           margin-bottom: 0rem;
       }

       #welcome-button:hover {
           background: var(--black);
           color: var(--white);
       }
   </style>
</head>
<body>
<div class="container">
    <div id="about-section">
        <button id="welcome-button">Welcome, Admin!</button>   
        <div class="message">   
            We're glad to have you back. Your insights and management help keep our platform running smoothly. Let's continue creating amazing shopping experiences for our customers!
        </div>
        <aside class="sidebar">
            <nav>
                <ul>
                    <li><button class="btn" onclick="location.href='dashboard.php'">Dashboard</button></li>
                    <li><button class="btn" onclick="location.href='view_items.php'">View Items</button></li>
                    <li><button class="btn" onclick="location.href='view_orders.php'">View Orders</button></li>
                    <li><button class="btn" onclick="location.href='add_items.php'">Manage Items</button></li>
                    <li><button class="btn" onclick="location.href='reports.php'">Reports</button></li><br><br><br>
                    <li><button class="btn" onclick="location.href='logout.php'">Logout</button></li>
                </ul>
            </nav>
        </aside>
    </div>
</div>
</body>
</html>
