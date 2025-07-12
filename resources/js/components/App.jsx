import React from "react";
import { Route, Routes } from "react-router-dom";
import { AuthProvider } from "../contexts/AuthContext";
import Navbar from "../layout/Navbar";
import GoalDetail from "../pages/GoalDetail";
import Goals from "../pages/Goals";
import Home from "../pages/Home";
import Login from "../pages/Login";
import Register from "../pages/Register";
import ProtectedRoute from "./ProtectedRoute";

function App() {
    return (
        <AuthProvider>
            <div className="min-h-screen bg-gray-50">
                <Navbar />
                <main className="container mx-auto px-4 py-8">
                    <Routes>
                        <Route path="/" element={<Home />} />
                        <Route path="/login" element={<Login />} />
                        <Route path="/register" element={<Register />} />
                        <Route
                            path="/goals"
                            element={
                                <ProtectedRoute>
                                    <Goals />
                                </ProtectedRoute>
                            }
                        />
                        <Route
                            path="/goals/:id"
                            element={
                                <ProtectedRoute>
                                    <GoalDetail />
                                </ProtectedRoute>
                            }
                        />
                    </Routes>
                </main>
            </div>
        </AuthProvider>
    );
}

export default App;
