<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Report Found Item</title>
  <link rel="stylesheet" href="home1.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- Navigation Bar -->
<nav>
  <a href="signup.html">Sign Up</a>
  <a href="login.html">Login</a>
  <a href="about.html">About Us</a>
  <a href="contact.html">Contact Us</a>
  <a href="dashboard.html"><i class="fa-solid fa-circle-user"></i></a>
</nav>
<img src="images/download (8).jpeg" class="logo" width="50" height="50">
<div class="main-container">
  <header>
    <h1>Lost&nbsp;&nbsp; And &nbsp;&nbsp;Found</h1>
  </header>

  <h2 class="c1">Search Items</h2>
  <div class="search-container">
    <input type="text" placeholder="Search..." class="search-input">
    <button class="search-button">Search</button>
  </div>

  <div class="view-section">
    <h2 class="c2">View Items</h2>
    <!-- Report an Item Dropdown -->
<div class="dropdown">
  <button onclick="toggleDropdown('reportDropdown')" class="dropbtn">Report an Item ▼</button>
  <div id="reportDropdown" class="dropdown-content">
    <form action="lost.html" method="get">
      <input type="submit" value="Report Lost Item">
    </form>
    <form action="found.html" method="get">
      <input type="submit" value="Report Found Item">
    </form>
  </div>
</div>

<!-- Select Category Dropdown -->
<div class="dropdown">
  <button onclick="toggleDropdown('categoryDropdown')" class="dropbtn">Select Category ▼</button>
  <div id="categoryDropdown" class="dropdown-content">
    <button onclick="loadItems('lost_items')">Lost Items</button>
    <button onclick="loadItems('found_items')">Found Items</button>
  </div>
</div>

  </div>
</div>
  
<!-- Items Display Section -->
<div class="items-section">
  <?php
  // DB Connection
  $servername = "localhost";
  $username = "root";
  $password = "";
  $dbname = "lostfound";

  $conn = new mysqli($servername, $username, $password, $dbname);
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

 function displayItems($conn, $tableName) {
    if ($tableName === 'lost_items') {
        $sql = "SELECT item_name, item_image, lost_id FROM $tableName";
    } else if ($tableName === 'found_items') {
        $sql = "SELECT item_name, item_image, found_id FROM $tableName";
    } else {
        return;
    }
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $item_image = htmlspecialchars($row['item_image']);
            $itemName = htmlspecialchars($row['item_name']);
            $itemId = ($tableName === 'lost_items') ? htmlspecialchars($row['lost_id']) : htmlspecialchars($row['found_id']);

            echo "
                <div class='item-box' onclick=\"window.location.href='connect.php?id=$itemId&table=$tableName'\">
                    <img src='" . $item_image . "' alt='$itemName'>
                    <div class='item-name'>$itemName</div>
                </div>
            ";
        }
    }
}

  echo "<div class='item-container'>";
  // Display both lost and found items
  displayItems($conn, 'lost_items');
  displayItems($conn, 'found_items');
  echo "</div>";

  $conn->close();
  ?>
</div>

<script>
function toggleDropdown(id) {
  const allDropdowns = document.querySelectorAll('.dropdown-content');
  allDropdowns.forEach(d => {
    if (d.id !== id) d.style.display = 'none'; // close others
  });

  const dropdown = document.getElementById(id);
  if (dropdown.style.display === 'block') {
    dropdown.style.display = 'none';
  } else {
    dropdown.style.display = 'block';
  }
}

function loadItems(table) {
  const xhr = new XMLHttpRequest();
  xhr.open("GET", "fetch_items.php?table=" + table, true);
  xhr.onreadystatechange = function() {
    if (xhr.readyState == 4 && xhr.status == 200) {
      const container = document.querySelector(".item-container");
      container.innerHTML = xhr.responseText;
    }
  };
  xhr.send();
}
</script>


</body>
</html>