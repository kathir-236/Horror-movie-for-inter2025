<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Internet Speed Test</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #121212;
      color: #fff;
      text-align: center;
      padding: 50px;
    }
    h1 {
      margin-bottom: 40px;
    }
    .speed-result {
      font-size: 2em;
      margin-top: 30px;
    }
    button {
      padding: 15px 30px;
      background-color: #e50914;
      color: white;
      border: none;
      font-size: 18px;
      border-radius: 8px;
      cursor: pointer;
    }
    button:hover {
      background-color: #b0070f;
    }
  </style>
</head>
<body>

  <h1>Internet Speed Test</h1>
  <button onclick="startSpeedTest()">Start Test</button>
  <div class="speed-result" id="result"></div>

  <script>
    function startSpeedTest() {
      const imageAddr = "https://upload.wikimedia.org/wikipedia/commons/3/3f/Fronalpstock_big.jpg"; // ~3MB file
      const downloadSize = 3 * 1024 * 1024; // in bytes (~3MB)
      const result = document.getElementById("result");

      result.innerHTML = "Testing...";

      const startTime = (new Date()).getTime();
      const download = new Image();
      download.onload = function () {
        const endTime = (new Date()).getTime();
        const duration = (endTime - startTime) / 1000; // in seconds
        const bitsLoaded = downloadSize * 8;
        const speedMbps = (bitsLoaded / duration / 1024 / 1024).toFixed(2);
        result.innerHTML = `Your internet speed is <strong>${speedMbps} Mbps</strong>`;
      };

      download.onerror = function () {
        result.innerHTML = "Error testing speed. Try again.";
      };

      const cacheBuster = "?nnn=" + startTime;
      download.src = imageAddr + cacheBuster;
    }
  </script>

</body>
</html>
