import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;

/**
 * Web Technologies (U21CS501) - Assignment 02: Question 3
 * LoginServlet: Handles user authentication and establishes secure HTTP Sessions
 */
public class LoginServlet extends HttpServlet {

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        
        String username = request.getParameter("username");
        String password = request.getParameter("password");

        // Basic sanity check
        if (username == null || password == null || username.trim().isEmpty() || password.trim().isEmpty()) {
            response.sendRedirect("login.jsp?error=Please+enter+both+username+and+password");
            return;
        }

        Connection conn = null;
        PreparedStatement stmt = null;
        ResultSet rs = null;

        try {
            conn = DBConnection.getConnection();
            String sql = "SELECT * FROM users WHERE username = ? AND password = ?";
            stmt = conn.prepareStatement(sql);
            stmt.setString(1, username.trim());
            stmt.setString(2, password.trim());

            rs = stmt.executeQuery();

            if (rs.next()) {
                // Authentication Success: Create/Retrieve Session
                HttpSession session = request.getSession(true);
                session.setAttribute("user", rs.getString("username"));
                session.setAttribute("fullName", rs.getString("full_name"));
                session.setAttribute("role", rs.getString("role"));

                // Redirect to protected student profiles portal
                response.sendRedirect("students");
            } else {
                // Authentication Failure
                response.sendRedirect("login.jsp?error=Invalid+username+or+password");
            }
        } catch (Exception e) {
            e.printStackTrace();
            response.sendRedirect("login.jsp?error=Database+error:+" + e.getMessage());
        } finally {
            try { if (rs != null) rs.close(); } catch (Exception ignored) {}
            try { if (stmt != null) stmt.close(); } catch (Exception ignored) {}
            try { if (conn != null) conn.close(); } catch (Exception ignored) {}
        }
    }

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        // Redirect GET requests directly to login page
        response.sendRedirect("login.jsp");
    }
}
