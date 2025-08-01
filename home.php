<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Report Found Item</title>
  <link rel="stylesheet" href="home.css">
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
    <div class="dropdown">
      <button onclick="toggleDropdown()" class="dropbtn">Report an Item ▼</button>
      <div id="dropdownMenu" class="dropdown-content">
        <form action="lost.html" method="get">
          <input type="submit" value="Report Lost Item">
        </form>
        <form action="found.html" method="get">
          <input type="submit" value="Report Found Item">
        </form>
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
function toggleDropdown() {
  document.getElementById("dropdownMenu").classList.toggle("show");
}

window.onclick = function(event) {
  if (!event.target.matches('.dropbtn')) {
    const dropdowns = document.getElementsByClassName("dropdown-content");
    for (let i = 0; i < dropdowns.length; i++) {
      const openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }
};
</script>

</body>
</html>