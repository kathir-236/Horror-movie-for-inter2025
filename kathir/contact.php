<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contact Us</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f7f7f7;
      background-image:url(.\image\monkey.jpg)
    }

    .container {
      max-width: 1200px;
      margin: auto;
      padding: 40px 20px;
    }

    h1 {
      text-align: center;
      margin-bottom: 40px;
      color: #222;
    }

    .contact-wrapper {
      display: flex;
      flex-wrap: wrap;
      gap: 40px;
      background-color: #fff;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .contact-info {
      flex: 1;
      min-width: 250px;
    }

    .contact-info h2 {
      margin-bottom: 20px;
      font-size: 22px;
      color: #e50914;
    }

    .contact-info p {
      margin-bottom: 10px;
    }

    .contact-info p strong {
      display: inline-block;
      width: 80px;
    }

    .contact-form {
      flex: 2;
      min-width: 300px;
    }

    .contact-form form {
      display: flex;
      flex-direction: column;
    }

    .contact-form input,
    .contact-form textarea {
      padding: 12px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 16px;
    }

    .contact-form textarea {
      resize: vertical;
      min-height: 120px;
    }

    .contact-form button {
      background-color: #e50914;
      color: #fff;
      border: none;
      padding: 12px;
      font-size: 16px;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .contact-form button:hover {
      background-color: #b20710;
    }

    @media (max-width: 768px) {
      .contact-wrapper {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <h1>Contact Us</h1>
    <div class="contact-wrapper">
      <div class="contact-info">
        <h2>Get in Touch</h2>
        <p><strong>Email:</strong>kathirmurugan236@ gamil.com</p>
        <p><strong>Phone:</strong>+91 8754634825</p>
        <p><strong>Address:</strong><br>
           17 Pallivasal Street,<br>
           Pinnathur west,Chidambaram<br>
           Cuddalore-608102<br>
           Tamil Nadu</p>
      </div>
      <div class="contact-form">
        <form action="#" method="post">
          <input type="text" name="name" placeholder="Your Name" required />
          <input type="email" name="email" placeholder="Your Email" required />
          <input type="text" name="subject" placeholder="Subject" />
          <textarea name="message" placeholder="Your Message" required></textarea>
          <button type="submit">Send Message</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
