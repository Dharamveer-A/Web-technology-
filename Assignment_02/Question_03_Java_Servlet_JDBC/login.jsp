<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Portal Login - KPRIET</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">

  <div class="login-card">
    <div class="login-header">
      <h2>KPRIET Student Portal</h2>
      <p>Secure Academic Administration</p>
    </div>

    <!-- Error & Info Banners -->
    <% 
      String error = request.getParameter("error");
      if (error != null && !error.isEmpty()) { 
    %>
      <div class="alert alert-error"><%= error %></div>
    <% } %>

    <% 
      String msg = request.getParameter("msg");
      if (msg != null && !msg.isEmpty()) { 
    %>
      <div class="alert alert-success"><%= msg %></div>
    <% } %>

    <form action="login" method="POST">
      <div class="form-group">
        <label for="username">Admin Username</label>
        <input type="text" id="username" name="username" placeholder="Enter username (e.g., admin)" required autofocus>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter password" required>
      </div>

      <div class="login-hints">
        <small>Demo Credentials: <b>admin</b> / <b>admin123</b></small>
      </div>

      <button type="submit" class="btn btn-primary btn-block">Sign In to Portal</button>
    </form>
  </div>

</body>
</html>
