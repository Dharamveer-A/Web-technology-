import java.io.IOException;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;

/**
 * Web Technologies (U21CS501) - Assignment 02: Question 3
 * LogoutServlet: Cleans up user session and redirects to login interface
 */
public class LogoutServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        
        HttpSession session = request.getSession(false); // Do not create if doesn't exist
        if (session != null) {
            session.removeAttribute("user");
            session.removeAttribute("fullName");
            session.invalidate(); // Destroy session
        }

        response.sendRedirect("login.jsp?msg=You+have+been+logged+out+securely.");
    }
}
