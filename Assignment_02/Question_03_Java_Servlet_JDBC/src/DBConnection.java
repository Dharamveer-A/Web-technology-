import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

/**
 * Web Technologies (U21CS501) - Assignment 02: Question 3
 * Database Connection Utility using JDBC for MySQL
 */
public class DBConnection {

    private static final String URL = "jdbc:mysql://localhost:3306/college_portal?useSSL=false&allowPublicKeyRetrieval=true&serverTimezone=UTC";
    private static final String USER = "root";
    private static final String PASSWORD = ""; // Adjust password to match local MySQL setup

    // Load JDBC Driver class
    static {
        try {
            Class.forName("com.mysql.cj.jdbc.Driver");
        } catch (ClassNotFoundException e) {
            System.err.println("MySQL JDBC Driver not found in classpath: " + e.getMessage());
        }
    }

    /**
     * Returns an active Connection instance to MySQL
     */
    public static Connection getConnection() throws SQLException {
        return DriverManager.getConnection(URL, USER, PASSWORD);
    }
}
