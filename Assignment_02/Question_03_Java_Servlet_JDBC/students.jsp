<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ page import="java.util.List, java.util.Map" %>
<%
    // 5. Ensure session handling so that only logged in users can access the portal securely
    if (session == null || session.getAttribute("user") == null) {
        response.sendRedirect("login.jsp?error=Unauthorized+access.+Please+login.");
        return;
    }

    String currentUser = (String) session.getAttribute("fullName");
    if (currentUser == null) currentUser = (String) session.getAttribute("user");

    // Fetch student list forwarded from StudentListServlet
    List<Map<String, Object>> studentList = (List<Map<String, Object>>) request.getAttribute("studentList");
%>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Directory - Dynamic Profiles</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Top Navigation Header -->
  <header>
    <div class="header-inner">
      <h1>KPRIET - Student Profiles Portal</h1>
      <div class="user-session">
        <span>Logged in as: <strong><%= currentUser %></strong></span>
        <a href="logout" class="btn btn-logout">Sign Out</a>
      </div>
    </div>
  </header>

  <main class="container">
    <div class="top-bar">
      <div>
        <h2>Verified Student Profiles</h2>
        <p>Dynamic records fetched via JDBC and rendered with Servlet image streaming.</p>
      </div>
      <div class="portal-badge">Servlet &amp; JDBC Powered</div>
    </div>

    <!-- Error message if any -->
    <% 
      String errorMessage = (String) request.getAttribute("errorMessage");
      if (errorMessage != null) {
    %>
      <div class="alert alert-error"><%= errorMessage %></div>
    <% } %>

    <!-- 4. Looping & Conditional Logic to display multiple students -->
    <% if (studentList == null || studentList.isEmpty()) { %>
      <div class="empty-state">
        <p>No student profiles currently registered in the database.</p>
      </div>
    <% } else { %>

      <div class="profile-grid">
        <% 
          // Looping over each student retrieved from the database
          for (Map<String, Object> student : studentList) { 
            Object photoId = student.get("photo_doc_id");
            Object certId = student.get("cert_doc_id");
            String studentName = (String) student.get("name");
            String rollNo = (String) student.get("roll_no");
            String course = (String) student.get("course");
            String email = (String) student.get("email");
        %>

          <div class="student-card">
            <div class="card-avatar-wrapper">
              <!-- 3. Dynamic <img> tag pointing directly to the streaming Servlet -->
              <% if (photoId != null) { %>
                <img src="streamDocument?id=<%= photoId %>" 
                     alt="<%= studentName %>" 
                     class="profile-img" 
                     loading="lazy">
              <% } else { %>
                <!-- Conditional Fallback: Initial avatar if no photo uploaded -->
                <div class="profile-placeholder">
                  <%= studentName != null && !studentName.isEmpty() ? studentName.substring(0, 1).toUpperCase() : "S" %>
                </div>
              <% } %>
            </div>

            <div class="card-content">
              <h3><%= studentName %></h3>
              <span class="roll-badge"><%= rollNo %></span>
              <p class="course-text"><%= course %></p>
              <p class="email-text">&#9993; <%= email %></p>

              <!-- Conditional Logic: Render Document stream button if file exists -->
              <div class="card-footer">
                <% if (certId != null) { %>
                  <a href="streamDocument?id=<%= certId %>" target="_blank" class="btn btn-doc">
                    &#128196; View Uploaded Document
                  </a>
                <% } else { %>
                  <span class="no-doc">&#10006; No Document Attached</span>
                <% } %>
              </div>
            </div>
          </div>

        <% } /* End for loop */ %>
      </div>

    <% } /* End else */ %>

  </main>

  <footer>
    <p>&copy; 2026-2027 KPR Institute of Engineering and Technology | Department of Computer Science & Engineering</p>
    <p>Course: U21CS501 Web Technology &bull; Assignment 02 &bull; Question 03</p>
  </footer>

</body>
</html>
