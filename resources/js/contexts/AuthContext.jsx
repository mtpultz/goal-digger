import React, { createContext, useContext, useEffect, useState } from "react";

const AuthContext = createContext();

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error("useAuth must be used within an AuthProvider");
    }
    return context;
};

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);
    const [isAuthenticated, setIsAuthenticated] = useState(false);

    // Check if user is already authenticated on app load
    useEffect(() => {
        const token = localStorage.getItem("access_token");
        const userData = localStorage.getItem("user");

        if (token && userData) {
            try {
                setUser(JSON.parse(userData));
                setIsAuthenticated(true);
            } catch (error) {
                console.error("Error parsing user data:", error);
                localStorage.removeItem("access_token");
                localStorage.removeItem("user");
            }
        }
        setLoading(false);
    }, []);

    const login = async (credentials) => {
        try {
            const response = await fetch("/api/auth/login", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                body: JSON.stringify(credentials),
            });

            const data = await response.json();

            if (response.ok) {
                // Store token and user data
                localStorage.setItem("access_token", data.access_token);
                localStorage.setItem("user", JSON.stringify(data.user));

                setUser(data.user);
                setIsAuthenticated(true);

                return { success: true };
            } else {
                return {
                    success: false,
                    error: data.message || "Login failed",
                };
            }
        } catch (error) {
            console.error("Login error:", error);
            return {
                success: false,
                error: "Network error. Please try again.",
            };
        }
    };

    const register = async (userData) => {
        try {
            const response = await fetch("/api/auth/register", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                body: JSON.stringify(userData),
            });

            const data = await response.json();

            if (response.ok) {
                return { success: true };
            } else {
                return {
                    success: false,
                    error: data.message || "Registration failed",
                };
            }
        } catch (error) {
            console.error("Registration error:", error);
            return {
                success: false,
                error: "Network error. Please try again.",
            };
        }
    };

    const logout = () => {
        localStorage.removeItem("access_token");
        localStorage.removeItem("user");
        setUser(null);
        setIsAuthenticated(false);
    };

    const getToken = () => {
        return localStorage.getItem("access_token");
    };

    const value = {
        user,
        isAuthenticated,
        loading,
        login,
        register,
        logout,
        getToken,
    };

    return (
        <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
    );
};
