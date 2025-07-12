import axios from "axios";
import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";

const Goals = () => {
    const [goals, setGoals] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");
    const [showCreateForm, setShowCreateForm] = useState(false);
    const [newGoal, setNewGoal] = useState({
        title: "",
        description: "",
        target_date: "",
        category: "personal",
    });

    useEffect(() => {
        fetchGoals();
    }, []);

    const fetchGoals = async () => {
        try {
            const response = await axios.get("/api/goals");
            setGoals(response.data.data || []);
        } catch (error) {
            console.error("Error fetching goals:", error);
            setError("Failed to load goals");
        } finally {
            setLoading(false);
        }
    };

    const handleCreateGoal = async (e) => {
        e.preventDefault();
        try {
            const response = await axios.post("/api/goals", newGoal);
            setGoals([...goals, response.data.data]);
            setNewGoal({
                title: "",
                description: "",
                target_date: "",
                category: "personal",
            });
            setShowCreateForm(false);
        } catch (error) {
            console.error("Error creating goal:", error);
            setError("Failed to create goal");
        }
    };

    const handleInputChange = (e) => {
        setNewGoal({
            ...newGoal,
            [e.target.name]: e.target.value,
        });
    };

    const getStatusColor = (status) => {
        switch (status) {
            case "completed":
                return "bg-green-100 text-green-800";
            case "in_progress":
                return "bg-blue-100 text-blue-800";
            case "not_started":
                return "bg-gray-100 text-gray-800";
            default:
                return "bg-gray-100 text-gray-800";
        }
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center min-h-screen">
                <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    return (
        <div className="max-w-6xl mx-auto">
            <div className="flex justify-between items-center mb-8">
                <h1 className="text-3xl font-bold text-gray-900">My Goals</h1>
                <button
                    onClick={() => setShowCreateForm(true)}
                    className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium"
                >
                    Create New Goal
                </button>
            </div>

            {error && (
                <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {error}
                </div>
            )}

            {showCreateForm && (
                <div className="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <h2 className="text-xl font-semibold mb-4">
                        Create New Goal
                    </h2>
                    <form onSubmit={handleCreateGoal} className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Title
                            </label>
                            <input
                                type="text"
                                name="title"
                                value={newGoal.title}
                                onChange={handleInputChange}
                                required
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter goal title"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea
                                name="description"
                                value={newGoal.description}
                                onChange={handleInputChange}
                                rows="3"
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter goal description"
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Target Date
                                </label>
                                <input
                                    type="date"
                                    name="target_date"
                                    value={newGoal.target_date}
                                    onChange={handleInputChange}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Category
                                </label>
                                <select
                                    name="category"
                                    value={newGoal.category}
                                    onChange={handleInputChange}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="personal">Personal</option>
                                    <option value="professional">
                                        Professional
                                    </option>
                                    <option value="health">Health</option>
                                    <option value="financial">Financial</option>
                                    <option value="education">Education</option>
                                </select>
                            </div>
                        </div>
                        <div className="flex space-x-3">
                            <button
                                type="submit"
                                className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium"
                            >
                                Create Goal
                            </button>
                            <button
                                type="button"
                                onClick={() => setShowCreateForm(false)}
                                className="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {goals.length === 0 ? (
                <div className="text-center py-12">
                    <div className="text-6xl mb-4">🎯</div>
                    <h3 className="text-xl font-semibold text-gray-900 mb-2">
                        No goals yet
                    </h3>
                    <p className="text-gray-600 mb-6">
                        Start your journey by creating your first goal!
                    </p>
                    <button
                        onClick={() => setShowCreateForm(true)}
                        className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md font-medium"
                    >
                        Create Your First Goal
                    </button>
                </div>
            ) : (
                <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {goals.map((goal) => (
                        <div
                            key={goal.id}
                            className="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
                        >
                            <div className="flex justify-between items-start mb-4">
                                <h3 className="text-lg font-semibold text-gray-900">
                                    {goal.title}
                                </h3>
                                <span
                                    className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(
                                        goal.status
                                    )}`}
                                >
                                    {goal.status.replace("_", " ")}
                                </span>
                            </div>
                            <p className="text-gray-600 mb-4 line-clamp-3">
                                {goal.description}
                            </p>
                            <div className="space-y-2 text-sm text-gray-500">
                                <div>
                                    <span className="font-medium">
                                        Category:
                                    </span>{" "}
                                    {goal.category}
                                </div>
                                {goal.target_date && (
                                    <div>
                                        <span className="font-medium">
                                            Target:
                                        </span>{" "}
                                        {new Date(
                                            goal.target_date
                                        ).toLocaleDateString()}
                                    </div>
                                )}
                                <div>
                                    <span className="font-medium">
                                        Created:
                                    </span>{" "}
                                    {new Date(
                                        goal.created_at
                                    ).toLocaleDateString()}
                                </div>
                            </div>
                            <div className="mt-4 pt-4 border-t border-gray-200">
                                <Link
                                    to={`/goals/${goal.id}`}
                                    className="text-blue-600 hover:text-blue-700 font-medium text-sm"
                                >
                                    View Details →
                                </Link>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default Goals;
