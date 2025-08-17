<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Claim Item</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .form-container {
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      max-width: 400px;
      width: 100%;
      text-align: center;
    }

    h2 {
      margin-bottom: 10px;
      font-size: 22px;
      color: #333;
    }

    p {
      font-size: 14px;
      color: #666;
      margin-bottom: 20px;
    }

    input[type="text"] {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 14px;
      outline: none;
    }

    input[type="text"]:focus {
      border-color: #007BFF;
      box-shadow: 0 0 5px rgba(0, 123, 255, 0.2);
    }

    button {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 8px;
      background-color: #f0f0f0;
      color: #333;
      font-size: 15px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    button:hover {
      background-color: #e0e0e0;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Claim Item</h2>
    <p>Please answer the following questions</p>
    
    <form action="claim_process.php" method="POST">
      <input type="text" name="lost_when_where" placeholder="When and where did you lose the item?" required>
      <input type="text" name="description" placeholder="Describe the item and any identifying features?" required>
      <input type="text" name="brand" placeholder="Is there a specific brand of this item?">
      <input type="text" name="additional_info" placeholder="Do you have any additional information?">
      
      <button type="submit">Send for approval</button>
    </form>
  </div>
</body>
</html>
